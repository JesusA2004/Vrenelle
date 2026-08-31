<?php

namespace App\Enums;

enum CouponType: string
{
    case Percentage = 'percentage';
    case Fixed = 'fixed';
    case FreeShipping = 'free_shipping';

    public function label(): string
    {
        return match ($this) {
            self::Percentage => 'Porcentaje',
            self::Fixed => 'Monto fijo',
            self::FreeShipping => 'Envío gratis',
        };
    }
}
