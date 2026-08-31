<?php

namespace App\Enums;

enum InventoryMovementType: string
{
    case Purchase = 'purchase';
    case Sale = 'sale';
    case Adjustment = 'adjustment';
    case ReturnStock = 'return';
    case Cancellation = 'cancellation';
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'Compra a proveedor',
            self::Sale => 'Venta',
            self::Adjustment => 'Ajuste',
            self::ReturnStock => 'Devolución',
            self::Cancellation => 'Cancelación',
            self::Manual => 'Ajuste manual',
        };
    }
}
