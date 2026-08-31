<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\Money;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $orders = $request->user()->orders()->latest()->paginate(10);

        return Inertia::render('account/Orders', [
            'orders' => $orders->through(fn (Order $order) => [
                'orderNumber' => $order->order_number,
                'status' => $order->status->label(),
                'paymentStatus' => $order->payment_status,
                'total' => Money::format($order->total, $order->currency),
                'createdAt' => $order->created_at->translatedFormat('d M Y'),
            ]),
        ]);
    }

    public function show(Request $request, string $orderNumber): Response
    {
        $order = $request->user()->orders()
            ->where('order_number', $orderNumber)
            ->with(['items', 'shippingMethod', 'statusHistory'])
            ->firstOrFail();

        return Inertia::render('account/OrderShow', [
            'order' => [
                'orderNumber' => $order->order_number,
                'status' => $order->status->label(),
                'paymentStatus' => $order->payment_status,
                'shippingMethod' => $order->shippingMethod?->name,
                'trackingNumber' => $order->tracking_number,
                'trackingUrl' => $order->tracking_url,
                'shippingCompany' => $order->shipping_company,
                'shippingAddress' => $order->shipping_address,
                'items' => $order->items->map(fn ($item) => [
                    'productName' => $item->product_name,
                    'variantName' => $item->variant_name,
                    'quantity' => $item->quantity,
                    'unitPrice' => Money::format($item->unit_price, $order->currency),
                    'total' => Money::format($item->total, $order->currency),
                ]),
                'subtotal' => Money::format($order->subtotal, $order->currency),
                'discountTotal' => Money::format($order->discount_total, $order->currency),
                'shippingTotal' => Money::format($order->shipping_total, $order->currency),
                'total' => Money::format($order->total, $order->currency),
                'history' => $order->statusHistory->map(fn ($h) => [
                    'toStatus' => $h->to_status,
                    'comment' => $h->comment,
                    'createdAt' => $h->created_at->translatedFormat('d M Y, H:i'),
                ]),
                'createdAt' => $order->created_at->translatedFormat('d \d\e F \d\e Y'),
            ],
        ]);
    }
}
