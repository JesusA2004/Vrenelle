<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Material;
use App\Models\Product;
use App\Models\Stone;
use App\Services\AuditService;
use App\Services\InventoryService;
use App\Support\Money;
use App\Support\SlugGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(protected InventoryService $inventory) {}

    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('products.view'), 403);

        $products = Product::query()
            ->with(['category', 'collection', 'images'])
            ->when($request->filled('buscar'), fn ($q) => $q->where(function ($q2) use ($request) {
                $term = $request->string('buscar');
                $q2->where('name', 'like', "%{$term}%")->orWhere('sku', 'like', "%{$term}%");
            }))
            ->when($request->filled('categoria'), fn ($q) => $q->where('category_id', $request->input('categoria')))
            ->when($request->filled('estado'), fn ($q) => $q->where('is_active', $request->input('estado') === 'activo'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/products/Index', [
            'products' => $products->through(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'image' => $p->images->first() ? Storage::disk('public')->url($p->images->first()->path) : null,
                'category' => $p->category?->name,
                'collection' => $p->collection?->name,
                'price' => Money::format($p->price),
                'stock' => $p->stock,
                'inventoryMode' => $p->inventory_mode->value,
                'isActive' => $p->is_active,
                'isLowStock' => $p->isLowStock(),
            ]),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'filters' => $request->only('buscar', 'categoria', 'estado'),
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()->can('products.create'), 403);

        return Inertia::render('admin/products/Form', $this->formProps());
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $this->prepareData($request);
        $data['slug'] = $data['slug'] ?? SlugGenerator::unique(Product::class, $data['name']);

        $product = Product::create($data);
        $this->syncImages($request, $product);

        AuditService::log('created', $product, [], $product->toArray());

        return redirect()->route('admin.products.edit', $product)->with('success', 'Producto creado correctamente.');
    }

    public function edit(Request $request, Product $product): Response
    {
        abort_unless($request->user()->can('products.view'), 403);

        $product->load(['images', 'variants.options']);

        return Inertia::render('admin/products/Form', [
            ...$this->formProps(),
            'product' => [
                ...$product->toArray(),
                'price' => Money::toDecimal($product->price),
                'compare_at_price' => $product->compare_at_price ? Money::toDecimal($product->compare_at_price) : null,
                'cost' => $product->cost ? Money::toDecimal($product->cost) : null,
                'inventory_mode' => $product->inventory_mode->value,
                'images' => $product->images->map(fn ($img) => [
                    'id' => $img->id,
                    'url' => Storage::disk('public')->url($img->path),
                    'isPrimary' => $img->is_primary,
                    'sortOrder' => $img->sort_order,
                ]),
                'variants' => $product->variants->map(fn ($v) => [
                    'id' => $v->id,
                    'name' => $v->name,
                    'sku' => $v->sku,
                    'price' => $v->price ? Money::toDecimal($v->price) : null,
                    'stock' => $v->stock,
                    'is_active' => $v->is_active,
                    'options' => $v->options->map(fn ($o) => ['name' => $o->option_name, 'value' => $o->option_value]),
                ]),
            ],
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $data = $this->prepareData($request);
        $data['slug'] = $data['slug'] ?? SlugGenerator::unique(Product::class, $data['name'], ignoreId: $product->id);

        $old = $product->toArray();

        // Stock adjustments made through the general edit form are logged as
        // manual inventory movements rather than silently overwritten.
        if ((int) $data['stock'] !== $product->stock) {
            $this->inventory->adjust($product, null, (int) $data['stock'], 'Ajuste manual desde edición de producto');
            unset($data['stock']);
        }

        $product->update($data);
        $this->syncImages($request, $product);

        AuditService::log('updated', $product, $old, $product->fresh()->toArray());

        return back()->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        abort_unless($request->user()->can('products.delete'), 403);

        AuditService::log('deleted', $product, $product->toArray(), []);
        $product->delete();

        return back()->with('success', 'Producto eliminado correctamente.');
    }

    protected function prepareData(ProductRequest $request): array
    {
        $data = $request->validated();
        $data['price'] = Money::toCents($data['price']);
        $data['compare_at_price'] = isset($data['compare_at_price']) ? Money::toCents($data['compare_at_price']) : null;
        $data['cost'] = isset($data['cost']) ? Money::toCents($data['cost']) : null;

        return $data;
    }

    protected function syncImages(Request $request, Product $product): void
    {
        if (! $request->hasFile('new_images')) {
            return;
        }

        $maxSort = (int) $product->images()->max('sort_order');

        foreach ($request->file('new_images') as $i => $file) {
            $path = $file->store('products', 'public');

            $product->images()->create([
                'path' => $path,
                'alt' => $product->name,
                'type' => 'image',
                'sort_order' => $maxSort + $i + 1,
                'is_primary' => $product->images()->count() === 0 && $i === 0,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function formProps(): array
    {
        return [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'collections' => Collection::orderBy('name')->get(['id', 'name']),
            'materials' => Material::orderBy('name')->get(['id', 'name']),
            'stones' => Stone::orderBy('name')->get(['id', 'name']),
        ];
    }
}
