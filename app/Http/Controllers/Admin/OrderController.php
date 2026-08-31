<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orders) {}

    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('orders.view'), 403);

        $orders = Order::query()
            ->when($request->filled('buscar'), fn ($q) => $q->where(function ($q2) use ($request) {
                $term = $request->string('buscar');
                $q2->where('order_number', 'like', "%{$term}%")
                    ->orWhere('customer_name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            }))
            ->when($request->filled('estado'), fn ($q) => $q->where('status', $request->input('estado')))
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('desde')))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('hasta')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/orders/Index', [
            'orders' => $orders->through(fn (Order $order) => [
                'id' => $order->id,
                'orderNumber' => $order->order_number,
                'customerName' => $order->customer_name,
                'email' => $order->email,
                'status' => $order->status->value,
                'statusLabel' => $order->status->label(),
                'paymentStatus' => $order->payment_status,
                'total' => Money::format($order->total, $order->currency),
                'createdAt' => $order->created_at->translatedFormat('d M Y, H:i'),
            ]),
            'statuses' => collect(OrderStatus::cases())->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()]),
            'filters' => $request->only('buscar', 'estado', 'desde', 'hasta'),
        ]);
    }

    public function show(Request $request, Order $order): Response
    {
        abort_unless($request->user()->can('orders.view'), 403);

        $order->load(['items', 'shippingMethod', 'statusHistory.user', 'paymentTransactions', 'coupon', 'user']);

        return Inertia::render('admin/orders/Show', [
            'order' => [
                'id' => $order->id,
                'orderNumber' => $order->order_number,
                'status' => $order->status->value,
                'statusLabel' => $order->status->label(),
                'paymentStatus' => $order->payment_status,
                'fulfillmentStatus' => $order->fulfillment_status,
                'customerName' => $order->customer_name,
                'email' => $order->email,
                'phone' => $order->phone,
                'user' => $order->user ? ['name' => $order->user->name, 'email' => $order->user->email] : null,
                'shippingAddress' => $order->shipping_address,
                'billingAddress' => $order->billing_address,
                'shippingMethod' => $order->shippingMethod?->name,
                'paymentMethod' => $order->payment_method,
                'installmentMonths' => $order->installment_months,
                'trackingNumber' => $order->tracking_number,
                'shippingCompany' => $order->shipping_company,
                'trackingUrl' => $order->tracking_url,
                'notes' => $order->notes,
                'items' => $order->items->map(fn ($item) => [
                    'productName' => $item->product_name,
                    'variantName' => $item->variant_name,
                    'sku' => $item->sku,
                    'quantity' => $item->quantity,
                    'unitPrice' => Money::format($item->unit_price, $order->currency),
                    'total' => Money::format($item->total, $order->currency),
                ]),
                'subtotal' => Money::format($order->subtotal, $order->currency),
                'discountTotal' => Money::format($order->discount_total, $order->currency),
                'shippingTotal' => Money::format($order->shipping_total, $order->currency),
                'taxTotal' => Money::format($order->tax_total, $order->currency),
                'total' => Money::format($order->total, $order->currency),
                'coupon' => $order->coupon?->code,
                'transactions' => $order->paymentTransactions->map(fn ($t) => [
                    'provider' => $t->provider->value,
                    'status' => $t->status,
                    'amount' => Money::format($t->amount, $t->currency),
                    'processedAt' => $t->processed_at?->translatedFormat('d M Y, H:i'),
                ]),
                'history' => $order->statusHistory->map(fn ($h) => [
                    'fromStatus' => $h->from_status,
                    'toStatus' => $h->to_status,
                    'comment' => $h->comment,
                    'user' => $h->user?->name,
                    'createdAt' => $h->created_at->translatedFormat('d M Y, H:i'),
                ]),
                'createdAt' => $order->created_at->translatedFormat('d M Y, H:i'),
            ],
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        abort_unless($request->user()->can('orders.edit'), 403);

        $data = $request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
            'comment' => ['nullable', 'string', 'max:1000'],
        ], ['status.required' => 'Selecciona un estado.']);

        $this->orders->changeStatus($order, OrderStatus::from($data['status']), $request->user(), $data['comment'] ?? null);

        return back()->with('success', 'Estado del pedido actualizado.');
    }

    public function updateTracking(Request $request, Order $order): RedirectResponse
    {
        abort_unless($request->user()->can('orders.edit'), 403);

        $data = $request->validate([
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'shipping_company' => ['nullable', 'string', 'max:100'],
            'tracking_url' => ['nullable', 'url', 'max:500'],
        ]);

        $order->update($data);

        return back()->with('success', 'Información de envío actualizada.');
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        abort_unless($request->user()->can('orders.cancel'), 403);

        $data = $request->validate(['comment' => ['nullable', 'string', 'max:1000']]);

        $this->orders->changeStatus($order, OrderStatus::Cancelled, $request->user(), $data['comment'] ?? 'Cancelado desde administración.');

        return back()->with('success', 'Pedido cancelado. El inventario fue devuelto automáticamente.');
    }
}
