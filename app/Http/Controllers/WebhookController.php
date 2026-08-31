<?php

namespace App\Http\Controllers;

use App\Enums\PaymentProvider;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Payment provider webhook endpoints. These MUST stay excluded from CSRF
 * verification (see bootstrap/app.php) since the caller is the provider's
 * server, not a browser with a Laravel session.
 */
class WebhookController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    public function stripe(Request $request): Response
    {
        return $this->handle(PaymentProvider::Stripe, $request);
    }

    public function paypal(Request $request): Response
    {
        return $this->handle(PaymentProvider::PayPal, $request);
    }

    public function mercadopago(Request $request): Response
    {
        return $this->handle(PaymentProvider::MercadoPago, $request);
    }

    protected function handle(PaymentProvider $provider, Request $request): Response
    {
        try {
            $this->paymentService->handleWebhook($provider, $request);

            return response('', 200);
        } catch (Throwable $e) {
            Log::error('Error procesando webhook de pago', [
                'provider' => $provider->value,
                'message' => $e->getMessage(),
            ]);

            // Respond 200 for signature failures to avoid the provider's
            // retry storm revealing which check failed; log-only otherwise.
            return response('', 200);
        }
    }
}
