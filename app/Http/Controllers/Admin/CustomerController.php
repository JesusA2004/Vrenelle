<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Money;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('customers.view'), 403);

        $customers = User::query()
            ->withCount('orders')
            ->withSum(['orders as total_spent' => fn ($q) => $q->where('payment_status', 'paid')], 'total')
            ->when($request->filled('buscar'), fn ($q) => $q->where(function ($q2) use ($request) {
                $term = $request->string('buscar');
                $q2->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%");
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/customers/Index', [
            'customers' => $customers->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'createdAt' => $user->created_at->translatedFormat('d M Y'),
                'ordersCount' => $user->orders_count,
                'totalSpent' => Money::format((int) ($user->total_spent ?? 0)),
            ]),
            'filters' => $request->only('buscar'),
        ]);
    }

    public function show(Request $request, User $customer): Response
    {
        abort_unless($request->user()->can('customers.view'), 403);

        $orders = $customer->orders()->latest()->get();
        $paidOrders = $orders->where('payment_status', 'paid');

        return Inertia::render('admin/customers/Show', [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'createdAt' => $customer->created_at->translatedFormat('d M Y'),
            ],
            'stats' => [
                'ordersCount' => $orders->count(),
                'totalSpent' => Money::format((int) $paidOrders->sum('total')),
                'averageTicket' => Money::format($paidOrders->count() ? (int) ($paidOrders->sum('total') / $paidOrders->count()) : 0),
                'lastOrderAt' => $orders->first()?->created_at?->translatedFormat('d M Y'),
            ],
            'addresses' => $customer->addresses,
            'orders' => $orders->map(fn ($order) => [
                'orderNumber' => $order->order_number,
                'status' => $order->status->label(),
                'total' => Money::format($order->total, $order->currency),
                'createdAt' => $order->created_at->translatedFormat('d M Y'),
            ]),
        ]);
    }
}
