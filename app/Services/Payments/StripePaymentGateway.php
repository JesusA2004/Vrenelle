<?php

namespace App\Services\Payments;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Talks to the Stripe REST API directly (no SDK dependency) using
 * Checkout Sessions. Requires STRIPE_KEY / STRIPE_SECRET / STRIPE_WEBHOOK_SECRET.
 *
 * Webhook to register in the Stripe dashboard:
 *   POST {APP_URL}/webhooks/stripe
 *   Events: checkout.session.completed, checkout.session.async_payment_failed
 */
class StripePaymentGateway implements PaymentGatewayInterface
{
    protected const API_BASE = 'https://api.stripe.com/v1';

    public function isConfigured(): bool
    {
        return filled(config('services.stripe.secret'));
    }

    public function createCheckoutSession(Order $order, string $successUrl, string $cancelUrl): CheckoutSession
    {
        $response = Http::asForm()
            ->withToken(config('services.stripe.secret'))
            ->post(self::API_BASE.'/checkout/sessions', [
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'customer_email' => $order->email,
                'client_reference_id' => $order->order_number,
                'metadata' => ['order_id' => $order->id],
                'line_items' => [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => strtolower($order->currency),
                        'unit_amount' => $order->total,
                        'product_data' => ['name' => "Pedido {$order->order_number}"],
                    ],
                ]],
            ])
            ->throw();

        $data = $response->json();

        return new CheckoutSession(
            providerReference: $data['id'],
            redirectUrl: $data['url'] ?? null,
        );
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        $secret = config('services.stripe.webhook_secret');
        $header = $request->header('Stripe-Signature');

        if (! $secret || ! $header) {
            return false;
        }

        $parts = collect(explode(',', $header))
            ->mapWithKeys(function (string $part) {
                [$key, $value] = array_pad(explode('=', $part, 2), 2, null);

                return [$key => $value];
            });

        $timestamp = $parts->get('t');
        $signature = $parts->get('v1');

        if (! $timestamp || ! $signature) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }

    public function parseWebhookEvent(Request $request): ?WebhookEvent
    {
        $payload = $request->json()->all();
        $type = $payload['type'] ?? null;
        $object = $payload['data']['object'] ?? [];

        return match ($type) {
            'checkout.session.completed' => new WebhookEvent(
                providerTransactionId: $object['id'] ?? $object['payment_intent'] ?? '',
                status: 'succeeded',
                amountCents: (int) ($object['amount_total'] ?? 0),
                currency: strtoupper($object['currency'] ?? 'MXN'),
                orderId: isset($object['metadata']['order_id']) ? (int) $object['metadata']['order_id'] : null,
                raw: $payload,
            ),
            'checkout.session.async_payment_failed', 'payment_intent.payment_failed' => new WebhookEvent(
                providerTransactionId: $object['id'] ?? '',
                status: 'failed',
                amountCents: (int) ($object['amount_total'] ?? 0),
                currency: strtoupper($object['currency'] ?? 'MXN'),
                orderId: isset($object['metadata']['order_id']) ? (int) $object['metadata']['order_id'] : null,
                raw: $payload,
            ),
            default => tap(null, fn () => Log::info('Stripe webhook ignorado', ['type' => $type])),
        };
    }
}
