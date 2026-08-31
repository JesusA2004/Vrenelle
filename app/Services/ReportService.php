<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * All reports are computed with aggregate SQL queries (never by loading
 * full result sets into PHP) so they stay usable as the store grows.
 */
class ReportService
{
    protected function paidOrders(CarbonInterface $from, CarbonInterface $to)
    {
        return Order::query()
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$from, $to]);
    }

    public function summary(CarbonInterface $from, CarbonInterface $to): array
    {
        $orders = $this->paidOrders($from, $to)->get(['id', 'total', 'subtotal', 'discount_total']);
        $cancelled = Order::query()->whereIn('status', ['cancelled'])->whereBetween('created_at', [$from, $to])->count();
        $refunded = Order::query()->where('status', 'refunded')->whereBetween('created_at', [$from, $to])->count();

        $cost = OrderItem::query()
            ->whereHas('order', fn ($q) => $q->where('payment_status', 'paid')->whereBetween('created_at', [$from, $to]))
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->sum(DB::raw('order_items.quantity * COALESCE(products.cost, 0)'));

        $revenue = (int) $orders->sum('total');
        $grossProfit = $revenue - (int) $cost;

        return [
            'orders_count' => $orders->count(),
            'revenue' => $revenue,
            'discounts' => (int) $orders->sum('discount_total'),
            'average_ticket' => $orders->count() ? (int) round($revenue / $orders->count()) : 0,
            'estimated_cost' => (int) $cost,
            'estimated_gross_profit' => $grossProfit,
            'estimated_margin_percentage' => $revenue > 0 ? round(($grossProfit / $revenue) * 100, 1) : 0,
            'cancelled_orders' => $cancelled,
            'refunded_orders' => $refunded,
        ];
    }

    public function salesByDay(CarbonInterface $from, CarbonInterface $to): array
    {
        return $this->paidOrders($from, $to)
            ->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as orders_count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => ['date' => $row->date, 'total' => (int) $row->total, 'ordersCount' => (int) $row->orders_count])
            ->all();
    }

    public function topProducts(CarbonInterface $from, CarbonInterface $to, int $limit = 10): array
    {
        return OrderItem::query()
            ->whereHas('order', fn ($q) => $q->where('payment_status', 'paid')->whereBetween('created_at', [$from, $to]))
            ->selectRaw('product_name, sku, SUM(quantity) as quantity_sold, SUM(total) as revenue')
            ->groupBy('product_name', 'sku')
            ->orderByDesc('quantity_sold')
            ->take($limit)
            ->get()
            ->map(fn ($row) => [
                'productName' => $row->product_name,
                'sku' => $row->sku,
                'quantitySold' => (int) $row->quantity_sold,
                'revenue' => (int) $row->revenue,
            ])
            ->all();
    }

    public function salesByCategory(CarbonInterface $from, CarbonInterface $to): array
    {
        return OrderItem::query()
            ->whereHas('order', fn ($q) => $q->where('payment_status', 'paid')->whereBetween('created_at', [$from, $to]))
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->selectRaw('categories.name as category, SUM(order_items.total) as revenue')
            ->groupBy('categories.name')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn ($row) => ['category' => $row->category, 'revenue' => (int) $row->revenue])
            ->all();
    }

    public function salesByPaymentMethod(CarbonInterface $from, CarbonInterface $to): array
    {
        return $this->paidOrders($from, $to)
            ->selectRaw('payment_method, COUNT(*) as orders_count, SUM(total) as revenue')
            ->groupBy('payment_method')
            ->get()
            ->map(fn ($row) => [
                'paymentMethod' => $row->payment_method ?? 'No especificado',
                'ordersCount' => (int) $row->orders_count,
                'revenue' => (int) $row->revenue,
            ])
            ->all();
    }

    public function couponsUsed(CarbonInterface $from, CarbonInterface $to): array
    {
        return Coupon::query()
            ->withCount(['usages' => fn ($q) => $q->whereBetween('created_at', [$from, $to])])
            ->withSum(['usages' => fn ($q) => $q->whereBetween('created_at', [$from, $to])], 'discount_amount')
            ->having('usages_count', '>', 0)
            ->get()
            ->map(fn ($coupon) => [
                'code' => $coupon->code,
                'usageCount' => $coupon->usages_count,
                'totalDiscount' => (int) ($coupon->usages_sum_discount_amount ?? 0),
            ])
            ->all();
    }

    public function topCustomers(CarbonInterface $from, CarbonInterface $to, int $limit = 10): array
    {
        return $this->paidOrders($from, $to)
            ->whereNotNull('user_id')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->selectRaw('users.name, users.email, COUNT(*) as orders_count, SUM(orders.total) as total_spent')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total_spent')
            ->take($limit)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->name,
                'email' => $row->email,
                'ordersCount' => (int) $row->orders_count,
                'totalSpent' => (int) $row->total_spent,
            ])
            ->all();
    }
}
