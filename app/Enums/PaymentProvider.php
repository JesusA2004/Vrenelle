<?php

namespace App\Enums;

enum PaymentProvider: string
{
    case Stripe = 'stripe';
    case PayPal = 'paypal';
    case MercadoPago = 'mercadopago';

    public function label(): string
    {
        return match ($this) {
            self::Stripe => 'Stripe',
            self::PayPal => 'PayPal',
            self::MercadoPago => 'Mercado Pago',
        };
    }
}
