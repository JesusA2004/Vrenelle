<?php

namespace App\Services\Payments;

/**
 * Normalized result of starting a payment with any provider.
 */
class CheckoutSession
{
    public function __construct(
        public readonly string $providerReference,
        public readonly ?string $redirectUrl,
        public readonly ?string $clientSecret = null,
    ) {}
}
