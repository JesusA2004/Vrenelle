<?php

namespace App\Services\Payments;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * PayPal Orders v2 REST API (no SDK dependency).
 * Requires PAYPAL_CLIENT_ID / PAYPAL_CLIENT_SECRET / PAYPAL_MODE / PAYPAL_WEBHOOK_ID.
 *
 * Webhook to register in the PayPal developer dashboard:
 *   POST {APP_URL}/webhooks/paypal
 *   Events: CHECKOUT.ORDER.APPROVED, PAYMENT.CAPTURE.COMPLETED, PAYMENT.CAPTURE.DENIED
 */
class PayPalPaymentGateway implements PaymentGatewayInterface
{
    public function isConfigured(): bool
    {
        return filled(config('services.paypal.client_id')) && filled(config('services.paypal.client_secret'));
    }

    protected function baseUrl(): string
    {
        return config('services.paypal.mode') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    protected function accessToken(): string
    {
        return Cache::remember('paypal.access_token', 55 * 60, function () {
            $response = Http::asForm()
                ->withBasicAuth(config('services.paypal.client_id'), config('services.paypal.client_secret'))
                ->post($this->baseUrl().'/v1/oauth2/token', ['grant_type' => 'client_credentials'])
                ->throw();

            return $response->json('access_token');
        });
    }

    public function createCheckoutSession(Order $order, string $successUrl, string $cancelUrl): CheckoutSession
    {
        $response = Http::withToken($this->accessToken())
            ->post($this->baseUrl().'/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $order->order_number,
                    'custom_id' => (string) $order->id,
                    'amount' => [
                        'currency_code' => $order->currency,
                        'value' => number_format($order->total / 100, 2, '.', ''),
                    ],
                ]],
                'application_context' => [
                    'return_url' => $successUrl,
                    'cancel_url' => $cancelUrl,
                    'brand_name' => config('app.name'),
                    'user_action' => 'PAY_NOW',
                ],
            ])
            ->throw();

        $data = $response->json();
        $approveLink = collect($data['links'] ?? [])->firstWhere('rel', 'approve')['href'] ?? null;

        return new CheckoutSession(
            providerReference: $data['id'],
            redirectUrl: $approveLink,
        );
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        $webhookId = config('services.paypal.webhook_id');

        if (! $webhookId) {
            return false;
        }

        $response = Http::withToken($this->accessToken())
            ->post($this->baseUrl().'/v1/notifications/verify-webhook-signature', [
                'auth_algo' => $request->header('Paypal-Auth-Algo'),
                'cert_url' => $request->header('Paypal-Cert-Url'),
                'transmission_id' => $request->header('Paypal-Transmission-Id'),
                'transmission_sig' => $request->header('Paypal-Transmission-Sig'),
                'transmission_time' => $request->header('Paypal-Transmission-Time'),
                'webhook_id' => $webhookId,
                'webhook_event' => $request->json()->all(),
            ]);

        return $response->successful() && $response->json('verification_status') === 'SUCCESS';
    }

    public function parseWebhookEvent(Request $request): ?WebhookEvent
    {
        $payload = $request->json()->all();
        $type = $payload['event_type'] ?? null;
        $resource = $payload['resource'] ?? [];

        return match ($type) {
            'PAYMENT.CAPTURE.COMPLETED' => new WebhookEvent(
                providerTransactionId: $resource['id'] ?? '',
                status: 'succeeded',
                amountCents: (int) round((float) ($resource['amount']['value'] ?? 0) * 100),
                currency: $resource['amount']['currency_code'] ?? 'MXN',
                orderId: isset($resource['custom_id']) ? (int) $resource['custom_id'] : null,
                raw: $payload,
            ),
            'PAYMENT.CAPTURE.DENIED' => new WebhookEvent(
                providerTransactionId: $resource['id'] ?? '',
                status: 'failed',
                amountCents: (int) round((float) ($resource['amount']['value'] ?? 0) * 100),
                currency: $resource['amount']['currency_code'] ?? 'MXN',
                orderId: isset($resource['custom_id']) ? (int) $resource['custom_id'] : null,
                raw: $payload,
            ),
            default => tap(null, fn () => Log::info('Webhook de PayPal ignorado', ['type' => $type])),
        };
    }
}
