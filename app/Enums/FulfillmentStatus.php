<?php

namespace App\Enums;

enum FulfillmentStatus: string
{
    case Unfulfilled = 'unfulfilled';
    case Preparing = 'preparing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';

    public function label(): string
    {
        return match ($this) {
            self::Unfulfilled => 'Sin surtir',
            self::Preparing => 'Preparando',
            self::Shipped => 'Enviado',
            self::Delivered => 'Entregado',
        };
    }
}
