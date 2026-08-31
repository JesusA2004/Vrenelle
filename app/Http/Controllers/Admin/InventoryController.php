<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    public function __construct(protected InventoryService $inventory) {}

    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('inventory.view'), 403);

        $lowStock = Product::query()->where('inventory_mode', 'stock')
            ->whereColumn('stock', '<=', 'low_stock_threshold')
            ->where('stock', '>', 0)
            ->orderBy('stock')
            ->get(['id', 'name', 'sku', 'stock', 'low_stock_threshold']);

        $outOfStock = Product::query()->where('inventory_mode', 'stock')->where('stock', 0)
            ->get(['id', 'name', 'sku']);

        $madeToOrder = Product::query()->where('inventory_mode', 'made_to_order')->get(['id', 'name', 'sku']);

        $movements = InventoryMovement::query()
            ->with(['product:id,name,sku', 'variant:id,name', 'user:id,name'])
            ->latest('created_at')
            ->paginate(25);

        return Inertia::render('admin/inventory/Index', [
            'lowStock' => $lowStock,
            'outOfStock' => $outOfStock,
            'madeToOrder' => $madeToOrder,
            'movements' => $movements->through(fn (InventoryMovement $m) => [
                'id' => $m->id,
                'product' => $m->product?->name,
                'sku' => $m->product?->sku,
                'variant' => $m->variant?->name,
                'type' => $m->type->label(),
                'quantity' => $m->quantity,
                'previousStock' => $m->previous_stock,
                'newStock' => $m->new_stock,
                'reason' => $m->reason,
                'user' => $m->user?->name,
                'createdAt' => $m->created_at->translatedFormat('d M Y, H:i'),
            ]),
        ]);
    }

    public function adjust(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('inventory.adjust'), 403);

        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'new_stock' => ['required', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'max:255'],
        ], [
            'new_stock.required' => 'Indica el nuevo inventario.',
            'reason.required' => 'Indica el motivo del ajuste.',
        ]);

        $product = Product::findOrFail($data['product_id']);
        $variant = isset($data['product_variant_id']) ? ProductVariant::find($data['product_variant_id']) : null;

        $this->inventory->adjust($product, $variant, $data['new_stock'], $data['reason']);

        return back()->with('success', 'Inventario ajustado correctamente.');
    }
}
