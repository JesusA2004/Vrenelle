<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Paid = 'paid';
    case Failed = 'failed';
    case Refunded = 'refunded';
    case PartiallyRefunded = 'partially_refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Processing => 'Procesando',
            self::Paid => 'Pagado',
            self::Failed => 'Fallido',
            self::Refunded => 'Reembolsado',
            self::PartiallyRefunded => 'Reembolsado parcialmente',
        };
    }
}
