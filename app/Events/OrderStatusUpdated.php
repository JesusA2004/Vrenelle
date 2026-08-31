<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired for order status transitions that don't already have a dedicated
 * event (confirmed, preparing, delivered).
 */
class OrderStatusUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Order $order) {}
}
