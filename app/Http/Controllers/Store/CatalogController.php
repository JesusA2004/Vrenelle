<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCardResource;
use App\Models\Category;
use App\Models\Collection as ProductCollection;
use App\Models\Material;
use App\Models\Product;
use App\Models\Stone;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Product::active()->with(['images', 'category', 'collection', 'material', 'stone']);

        $category = null;

        if ($request->filled('categoria')) {
            $category = Category::where('slug', $request->string('categoria'))->first();
            $query->when($category, fn ($q) => $q->where('category_id', $category->id));
        }

        $collection = null;

        if ($request->filled('coleccion')) {
            $collection = ProductCollection::where('slug', $request->string('coleccion'))->first();
            $query->when($collection, fn ($q) => $q->where('collection_id', $collection->id));
        }

        if ($request->filled('material')) {
            $query->whereHas('material', fn ($q) => $q->where('slug', $request->string('material')));
        }

        if ($request->filled('piedra')) {
            $query->whereHas('stone', fn ($q) => $q->where('slug', $request->string('piedra')));
        }

        if ($request->filled('precio_min')) {
            $query->where('price', '>=', (int) $request->input('precio_min') * 100);
        }

        if ($request->filled('precio_max')) {
            $query->where('price', '<=', (int) $request->input('precio_max') * 100);
        }

        if ($request->boolean('novedades')) {
            $query->where('is_new', true);
        }

        if ($request->boolean('oferta')) {
            $query->whereColumn('compare_at_price', '>', 'price')->whereNotNull('compare_at_price');
        }

        if ($request->boolean('disponible')) {
            $query->where(function ($q) {
                $q->where('inventory_mode', '!=', 'stock')->orWhere('stock', '>', 0);
            });
        }

        match ($request->string('orden')->toString()) {
            'precio_asc' => $query->orderBy('price'),
            'precio_desc' => $query->orderByDesc('price'),
            'novedades' => $query->orderByDesc('is_new')->orderByDesc('published_at'),
            default => $query->orderByDesc('is_featured')->orderByDesc('published_at'),
        };

        $products = $query->paginate(12)->withQueryString();

        return Inertia::render('store/Catalog', [
            'title' => $category?->name ?? $collection?->name ?? 'Joyería',
            'description' => $category?->description ?? $collection?->short_description,
            'products' => $products->through(fn (Product $product) => (new ProductCardResource($product))->resolve()),
            'filters' => $request->only([
                'categoria', 'coleccion', 'material', 'piedra', 'precio_min', 'precio_max',
                'novedades', 'oferta', 'disponible', 'orden',
            ]),
            'facets' => [
                'categories' => Category::active()->root()->orderBy('sort_order')->get(['name', 'slug']),
                'collections' => ProductCollection::active()->orderBy('sort_order')->get(['name', 'slug']),
                'materials' => Material::where('is_active', true)->orderBy('sort_order')->get(['name', 'slug']),
                'stones' => Stone::where('is_active', true)->orderBy('sort_order')->get(['name', 'slug']),
            ],
        ]);
    }
}
