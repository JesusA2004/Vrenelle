<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\ReportService;
use App\Support\Money;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, ReportService $reports): Response
    {
        abort_unless(
            $request->user()->canAny(['orders.view', 'products.view', 'reports.view', 'inventory.view']),
            403
        );

        $today = now();
        $todaySummary = $reports->summary($today->copy()->startOfDay(), $today->copy()->endOfDay());
        $monthSummary = $reports->summary($today->copy()->startOfMonth(), $today->copy()->endOfDay());

        $pendingOrders = Order::whereIn('status', ['pending', 'payment_pending'])->count();
        $failedPayments = Order::where('payment_status', 'failed')->count();

        $last30 = $reports->salesByDay($today->copy()->subDays(29)->startOfDay(), $today->copy()->endOfDay());
        $byStatus = Order::query()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $byPaymentMethod = $reports->salesByPaymentMethod($today->copy()->startOfMonth(), $today->copy()->endOfDay());
        $topProducts = $reports->topProducts($today->copy()->startOfMonth(), $today->copy()->endOfDay(), 5);

        return Inertia::render('admin/Dashboard', [
            'cards' => [
                'salesToday' => Money::format($todaySummary['revenue']),
                'salesMonth' => Money::format($monthSummary['revenue']),
                'ordersToday' => $todaySummary['orders_count'],
                'pendingOrders' => $pendingOrders,
                'averageTicket' => Money::format($monthSummary['average_ticket']),
                'failedPayments' => $failedPayments,
            ],
            'salesLast30Days' => collect($last30)->map(fn ($row) => ['date' => $row['date'], 'total' => Money::toDecimal($row['total'])]),
            'ordersByStatus' => $byStatus,
            'salesByPaymentMethod' => $byPaymentMethod,
            'topProducts' => collect($topProducts)->map(fn ($row) => [...$row, 'revenue' => Money::format($row['revenue'])]),
            'lowStockCount' => Product::where('inventory_mode', 'stock')->whereColumn('stock', '<=', 'low_stock_threshold')->where('stock', '>', 0)->count(),
            'outOfStockCount' => Product::where('inventory_mode', 'stock')->where('stock', 0)->count(),
        ]);
    }
}
