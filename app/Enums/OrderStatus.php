<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case PaymentPending = 'payment_pending';
    case Paid = 'paid';
    case Confirmed = 'confirmed';
    case Preparing = 'preparing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::PaymentPending => 'Pago pendiente',
            self::Paid => 'Pagado',
            self::Confirmed => 'Confirmado',
            self::Preparing => 'Preparando',
            self::Shipped => 'Enviado',
            self::Delivered => 'Entregado',
            self::Cancelled => 'Cancelado',
            self::Refunded => 'Reembolsado',
        };
    }

    /**
     * Statuses in which reserved inventory should be treated as permanently
     * consumed rather than returned to stock.
     *
     * @return array<int, self>
     */
    public static function terminalCancelled(): array
    {
        return [self::Cancelled, self::Refunded];
    }
}
