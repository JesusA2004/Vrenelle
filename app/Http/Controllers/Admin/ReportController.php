<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reports) {}

    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('reports.view'), 403);

        [$from, $to, $preset] = $this->resolveRange($request);

        $summary = $this->reports->summary($from, $to);

        return Inertia::render('admin/reports/Index', [
            'filters' => ['desde' => $from->toDateString(), 'hasta' => $to->toDateString(), 'preset' => $preset],
            'summary' => [
                'ordersCount' => $summary['orders_count'],
                'revenue' => Money::format($summary['revenue']),
                'discounts' => Money::format($summary['discounts']),
                'averageTicket' => Money::format($summary['average_ticket']),
                'estimatedCost' => Money::format($summary['estimated_cost']),
                'estimatedGrossProfit' => Money::format($summary['estimated_gross_profit']),
                'estimatedMarginPercentage' => $summary['estimated_margin_percentage'],
                'cancelledOrders' => $summary['cancelled_orders'],
                'refundedOrders' => $summary['refunded_orders'],
            ],
            'salesByDay' => collect($this->reports->salesByDay($from, $to))->map(fn ($row) => [
                'date' => $row['date'], 'total' => Money::toDecimal($row['total']), 'ordersCount' => $row['ordersCount'],
            ]),
            'topProducts' => collect($this->reports->topProducts($from, $to))->map(fn ($row) => [
                ...$row, 'revenue' => Money::format($row['revenue']),
            ]),
            'salesByCategory' => collect($this->reports->salesByCategory($from, $to))->map(fn ($row) => [
                ...$row, 'revenue' => Money::format($row['revenue']),
            ]),
            'salesByPaymentMethod' => collect($this->reports->salesByPaymentMethod($from, $to))->map(fn ($row) => [
                ...$row, 'revenue' => Money::format($row['revenue']),
            ]),
            'couponsUsed' => collect($this->reports->couponsUsed($from, $to))->map(fn ($row) => [
                ...$row, 'totalDiscount' => Money::format($row['totalDiscount']),
            ]),
            'topCustomers' => collect($this->reports->topCustomers($from, $to))->map(fn ($row) => [
                ...$row, 'totalSpent' => Money::format($row['totalSpent']),
            ]),
        ]);
    }

    public function exportCsv(Request $request)
    {
        abort_unless($request->user()->can('reports.view'), 403);

        [$from, $to] = $this->resolveRange($request);
        $rows = $this->reports->topProducts($from, $to, 1000);

        $filename = 'reporte-productos-'.$from->toDateString().'-a-'.$to->toDateString().'.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Producto', 'SKU', 'Cantidad vendida', 'Ingresos (centavos)']);

            foreach ($rows as $row) {
                fputcsv($handle, [$row['productName'], $row['sku'], $row['quantitySold'], $row['revenue']]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    protected function resolveRange(Request $request): array
    {
        $preset = $request->string('preset')->toString() ?: 'mes_actual';

        if ($request->filled('desde') && $request->filled('hasta')) {
            return [Carbon::parse($request->input('desde'))->startOfDay(), Carbon::parse($request->input('hasta'))->endOfDay(), 'personalizado'];
        }

        return match ($preset) {
            'hoy' => [now()->startOfDay(), now()->endOfDay(), $preset],
            'ayer' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay(), $preset],
            '7_dias' => [now()->subDays(6)->startOfDay(), now()->endOfDay(), $preset],
            '30_dias' => [now()->subDays(29)->startOfDay(), now()->endOfDay(), $preset],
            'mes_anterior' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth(), $preset],
            'anio' => [now()->startOfYear(), now()->endOfYear(), $preset],
            default => [now()->startOfMonth(), now()->endOfDay(), 'mes_actual'],
        };
    }
}
