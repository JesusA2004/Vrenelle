<?php

namespace App\Services;

use App\Enums\FulfillmentStatus;
use App\Enums\InventoryMovementType;
use App\Enums\OrderStatus;
use App\Events\OrderCancelled;
use App\Events\OrderShipped;
use App\Events\OrderStatusUpdated;
use App\Events\PaymentConfirmed;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(protected InventoryService $inventory) {}

    public function changeStatus(Order $order, OrderStatus $newStatus, ?User $actor = null, ?string $comment = null): Order
    {
        return DB::transaction(function () use ($order, $newStatus, $actor, $comment) {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);
            $previousStatus = $order->status;

            if ($previousStatus === $newStatus) {
                return $order;
            }

            $wasCancelled = in_array($previousStatus, OrderStatus::terminalCancelled(), true);
            $willBeCancelled = in_array($newStatus, OrderStatus::terminalCancelled(), true);

            // Restock only once, the moment an active order transitions into a cancelled/refunded state.
            if (! $wasCancelled && $willBeCancelled) {
                foreach ($order->items as $item) {
                    if (! $item->product) {
                        continue;
                    }

                    $this->inventory->restock(
                        $item->product,
                        $item->variant,
                        $item->quantity,
                        $newStatus === OrderStatus::Refunded ? InventoryMovementType::ReturnStock : InventoryMovementType::Cancellation,
                        $order,
                    );
                }
            }

            $attributes = ['status' => $newStatus];

            if ($newStatus === OrderStatus::Cancelled || $newStatus === OrderStatus::Refunded) {
                $attributes['cancelled_at'] = now();
            }

            if ($newStatus === OrderStatus::Shipped) {
                $attributes['fulfillment_status'] = FulfillmentStatus::Shipped;
                $attributes['shipped_at'] ??= now();
            }

            if ($newStatus === OrderStatus::Delivered) {
                $attributes['fulfillment_status'] = FulfillmentStatus::Delivered;
                $attributes['delivered_at'] ??= now();
            }

            if ($newStatus === OrderStatus::Preparing) {
                $attributes['fulfillment_status'] = FulfillmentStatus::Preparing;
            }

            $order->update($attributes);

            $order->statusHistory()->create([
                'user_id' => $actor?->id,
                'from_status' => $previousStatus->value,
                'to_status' => $newStatus->value,
                'comment' => $comment,
                'created_at' => now(),
            ]);

            match ($newStatus) {
                OrderStatus::Shipped => OrderShipped::dispatch($order),
                OrderStatus::Cancelled, OrderStatus::Refunded => OrderCancelled::dispatch($order),
                OrderStatus::Confirmed, OrderStatus::Preparing, OrderStatus::Delivered => OrderStatusUpdated::dispatch($order),
                default => null,
            };

            return $order->fresh();
        });
    }

    public function markPaid(Order $order): Order
    {
        $order->update(['payment_status' => 'paid']);

        if ($order->status === OrderStatus::Pending || $order->status === OrderStatus::PaymentPending) {
            $this->changeStatus($order, OrderStatus::Paid, null, 'Pago confirmado por el proveedor de pagos.');
        }

        PaymentConfirmed::dispatch($order->fresh());

        return $order->fresh();
    }
}
