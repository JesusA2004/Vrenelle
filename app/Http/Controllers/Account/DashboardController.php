<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Support\Money;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $recentOrders = $user->orders()->latest()->take(3)->get();

        return Inertia::render('account/Dashboard', [
            'stats' => [
                'ordersCount' => $user->orders()->count(),
                'totalSpent' => Money::format($user->totalSpent()),
                'wishlistCount' => $user->wishlist?->items()->count() ?? 0,
            ],
            'recentOrders' => $recentOrders->map(fn ($order) => [
                'orderNumber' => $order->order_number,
                'status' => $order->status->label(),
                'total' => Money::format($order->total, $order->currency),
                'createdAt' => $order->created_at->translatedFormat('d M Y'),
            ]),
        ]);
    }
}
