<?php

namespace App\Services\Payments;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Mercado Pago Checkout Pro REST API (no SDK dependency).
 * Requires MERCADOPAGO_ACCESS_TOKEN / MERCADOPAGO_PUBLIC_KEY / MERCADOPAGO_WEBHOOK_SECRET.
 *
 * Webhook to register in the Mercado Pago developer dashboard:
 *   POST {APP_URL}/webhooks/mercadopago
 *   Events: payment
 *
 * Mercado Pago webhooks only carry a payment id — the actual status must
 * always be re-fetched from the Payments API, never trusted from the
 * notification body alone.
 */
class MercadoPagoPaymentGateway implements PaymentGatewayInterface
{
    protected const API_BASE = 'https://api.mercadopago.com';

    public function isConfigured(): bool
    {
        return filled(config('services.mercadopago.access_token'));
    }

    public function createCheckoutSession(Order $order, string $successUrl, string $cancelUrl): CheckoutSession
    {
        $response = Http::withToken(config('services.mercadopago.access_token'))
            ->post(self::API_BASE.'/checkout/preferences', [
                'external_reference' => (string) $order->id,
                'items' => [[
                    'title' => "Pedido {$order->order_number}",
                    'quantity' => 1,
                    'currency_id' => $order->currency,
                    'unit_price' => (float) ($order->total / 100),
                ]],
                'payer' => ['email' => $order->email],
                'back_urls' => [
                    'success' => $successUrl,
                    'failure' => $cancelUrl,
                    'pending' => $successUrl,
                ],
                'auto_return' => 'approved',
                'notification_url' => url('/webhooks/mercadopago'),
            ])
            ->throw();

        $data = $response->json();

        return new CheckoutSession(
            providerReference: $data['id'],
            redirectUrl: $data['init_point'] ?? null,
        );
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        $secret = config('services.mercadopago.webhook_secret');
        $signatureHeader = $request->header('x-signature');
        $requestId = $request->header('x-request-id');

        if (! $secret || ! $signatureHeader || ! $requestId) {
            // Without a configured secret we cannot cryptographically verify the
            // sender; fall back to treating the notification as an id-only hint
            // and re-fetching the authoritative status from the API below.
            return filled(config('services.mercadopago.access_token'));
        }

        $parts = collect(explode(',', $signatureHeader))->mapWithKeys(function (string $part) {
            [$key, $value] = array_pad(explode('=', $part, 2), 2, null);

            return [trim((string) $key) => trim((string) $value)];
        });

        $ts = $parts->get('ts');
        $hash = $parts->get('v1');
        $dataId = $request->query('data_id') ?? $request->input('data.id');

        if (! $ts || ! $hash || ! $dataId) {
            return false;
        }

        $manifest = "id:{$dataId};request-id:{$requestId};ts:{$ts};";
        $expected = hash_hmac('sha256', $manifest, $secret);

        return hash_equals($expected, $hash);
    }

    public function parseWebhookEvent(Request $request): ?WebhookEvent
    {
        $type = $request->query('type') ?? $request->input('type');
        $paymentId = $request->query('data_id') ?? $request->input('data.id') ?? $request->input('id');

        if ($type !== 'payment' || ! $paymentId) {
            Log::info('Webhook de Mercado Pago ignorado', ['type' => $type]);

            return null;
        }

        // Re-fetch the authoritative payment status — never trust the webhook body.
        $response = Http::withToken(config('services.mercadopago.access_token'))
            ->get(self::API_BASE."/v1/payments/{$paymentId}");

        if (! $response->successful()) {
            return null;
        }

        $payment = $response->json();

        $status = match ($payment['status'] ?? null) {
            'approved' => 'succeeded',
            'rejected', 'cancelled' => 'failed',
            'refunded', 'charged_back' => 'refunded',
            default => 'pending',
        };

        return new WebhookEvent(
            providerTransactionId: (string) ($payment['id'] ?? $paymentId),
            status: $status,
            amountCents: (int) round((float) ($payment['transaction_amount'] ?? 0) * 100),
            currency: $payment['currency_id'] ?? 'MXN',
            orderId: isset($payment['external_reference']) ? (int) $payment['external_reference'] : null,
            raw: $payment,
        );
    }
}
