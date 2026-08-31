<?php

namespace App\Enums;

enum ProductInventoryMode: string
{
    case Stock = 'stock';
    case MadeToOrder = 'made_to_order';
    case InquiryOnly = 'inquiry_only';

    public function label(): string
    {
        return match ($this) {
            self::Stock => 'Inventario disponible',
            self::MadeToOrder => 'Bajo pedido',
            self::InquiryOnly => 'Solo consulta',
        };
    }
}
