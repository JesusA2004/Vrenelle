<?php

namespace App\Services;

use App\Enums\PaymentProvider;
use App\Models\Order;
use App\Models\PaymentInstallmentRule;
use App\Models\PaymentTransaction;
use App\Services\Payments\CheckoutSession;
use App\Services\Payments\MercadoPagoPaymentGateway;
use App\Services\Payments\PaymentGatewayInterface;
use App\Services\Payments\PayPalPaymentGateway;
use App\Services\Payments\StripePaymentGateway;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PaymentService
{
    public function __construct(protected OrderService $orders) {}

    public function gateway(PaymentProvider $provider): PaymentGatewayInterface
    {
        return match ($provider) {
            PaymentProvider::Stripe => app(StripePaymentGateway::class),
            PaymentProvider::PayPal => app(PayPalPaymentGateway::class),
            PaymentProvider::MercadoPago => app(MercadoPagoPaymentGateway::class),
        };
    }

    /**
     * @return array<int, PaymentProvider>
     */
    public function availableProviders(): array
    {
        return array_values(array_filter(
            PaymentProvider::cases(),
            fn (PaymentProvider $provider) => $this->gateway($provider)->isConfigured(),
        ));
    }

    public function startCheckout(Order $order, PaymentProvider $provider): CheckoutSession
    {
        $gateway = $this->gateway($provider);

        if (! $gateway->isConfigured()) {
            throw new RuntimeException("La pasarela {$provider->label()} no está configurada.");
        }

        $successUrl = url("/pedido/confirmado/{$order->order_number}");
        $cancelUrl = url("/checkout?order={$order->order_number}");

        return $gateway->createCheckoutSession($order, $successUrl, $cancelUrl);
    }

    /**
     * Handles an incoming provider webhook end to end: verifies the
     * signature, parses the event, and — idempotently, thanks to the
     * unique (provider, provider_transaction_id) constraint — records the
     * transaction and advances the order's payment status. The browser
     * returning to a "success" URL is never enough on its own; this is the
     * only path that may mark an order as paid.
     */
    public function handleWebhook(PaymentProvider $provider, Request $request): void
    {
        $gateway = $this->gateway($provider);

        if (! $gateway->verifyWebhookSignature($request)) {
            Log::warning('Firma de webhook de pago inválida', ['provider' => $provider->value]);
            throw new RuntimeException('Firma de webhook inválida.');
        }

        $event = $gateway->parseWebhookEvent($request);

        if (! $event) {
            return;
        }

        DB::transaction(function () use ($provider, $event) {
            $existing = PaymentTransaction::query()
                ->where('provider', $provider->value)
                ->where('provider_transaction_id', $event->providerTransactionId)
                ->lockForUpdate()
                ->first();

            if ($existing && $existing->status === $event->status) {
                return; // already processed — idempotent no-op
            }

            $order = $event->orderId ? Order::find($event->orderId) : null;

            $transaction = PaymentTransaction::updateOrCreate(
                ['provider' => $provider->value, 'provider_transaction_id' => $event->providerTransactionId],
                [
                    'order_id' => $order?->id,
                    'status' => $event->status,
                    'amount' => $event->amountCents,
                    'currency' => $event->currency,
                    'payload' => $event->raw,
                    'processed_at' => now(),
                ]
            );

            if (! $order) {
                Log::warning('Webhook de pago sin pedido asociado', ['provider' => $provider->value, 'transaction' => $transaction->id]);

                return;
            }

            match ($event->status) {
                'succeeded' => $this->orders->markPaid($order),
                'failed' => $order->update(['payment_status' => 'failed']),
                'refunded' => $order->update(['payment_status' => 'refunded']),
                default => null,
            };
        });
    }

    /**
     * Installment plans genuinely available for this provider and amount —
     * never fabricated; comes straight from admin-managed configuration.
     *
     * @return Collection<int, PaymentInstallmentRule>
     */
    public function availableInstallments(PaymentProvider $provider, int $amountCents): Collection
    {
        return PaymentInstallmentRule::active()
            ->where('provider', $provider->value)
            ->get()
            ->filter(fn (PaymentInstallmentRule $rule) => $rule->appliesTo($amountCents))
            ->sortBy('months')
            ->values();
    }
}
