<?php

namespace App\Services\Payments;

/**
 * Normalized result of a verified provider webhook, regardless of which
 * gateway produced it.
 */
class WebhookEvent
{
    public function __construct(
        public readonly string $providerTransactionId,
        public readonly string $status, // succeeded | failed | refunded | pending
        public readonly int $amountCents,
        public readonly string $currency,
        public readonly ?int $orderId,
        public readonly array $raw = [],
    ) {}
}
