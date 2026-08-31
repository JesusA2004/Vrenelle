<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\Money;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderConfirmationController extends Controller
{
    public function show(Request $request, string $orderNumber): Response
    {
        $order = Order::where('order_number', $orderNumber)->with('items', 'shippingMethod')->firstOrFail();

        $accessibleOrders = $request->session()->get('accessible_orders', []);
        $ownsOrder = $request->user() && $order->user_id === $request->user()->id;
        $canManage = $request->user()?->can('orders.view') ?? false;

        abort_unless($ownsOrder || $canManage || in_array($order->id, $accessibleOrders, true), 403);

        return Inertia::render('store/OrderConfirmation', [
            'order' => [
                'orderNumber' => $order->order_number,
                'status' => $order->status->label(),
                'paymentStatus' => $order->payment_status,
                'paymentMethod' => $order->payment_method,
                'customerName' => $order->customer_name,
                'email' => $order->email,
                'shippingMethod' => $order->shippingMethod?->name,
                'items' => $order->items->map(fn ($item) => [
                    'productName' => $item->product_name,
                    'variantName' => $item->variant_name,
                    'quantity' => $item->quantity,
                    'total' => Money::format($item->total, $order->currency),
                ]),
                'subtotal' => Money::format($order->subtotal, $order->currency),
                'discountTotal' => Money::format($order->discount_total, $order->currency),
                'shippingTotal' => Money::format($order->shipping_total, $order->currency),
                'total' => Money::format($order->total, $order->currency),
                'createdAt' => $order->created_at->translatedFormat('d \d\e F \d\e Y'),
            ],
        ]);
    }
}
