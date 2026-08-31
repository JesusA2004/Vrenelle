<?php

namespace App\Services\Payments;

use App\Models\Order;
use Illuminate\Http\Request;

/**
 * Contract every payment provider integration must implement. Controllers
 * and CheckoutService talk only to this interface (Strategy pattern) —
 * they never know which concrete gateway is behind it.
 */
interface PaymentGatewayInterface
{
    /**
     * Whether this gateway is fully configured (has credentials) and can
     * be offered to customers right now.
     */
    public function isConfigured(): bool;

    /**
     * Starts a hosted checkout / payment intent for the given order and
     * returns where to send the customer (or the client secret for an
     * embedded flow).
     */
    public function createCheckoutSession(Order $order, string $successUrl, string $cancelUrl): CheckoutSession;

    /**
     * Verifies the authenticity of an incoming webhook request (signature
     * validation). Must return false for anything that isn't provably from
     * the provider — never trust an unverified payload.
     */
    public function verifyWebhookSignature(Request $request): bool;

    /**
     * Parses an already-verified webhook request into a normalized event.
     * Returns null for event types this integration doesn't act on.
     */
    public function parseWebhookEvent(Request $request): ?WebhookEvent;
}
