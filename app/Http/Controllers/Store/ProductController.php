<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCardResource;
use App\Http\Resources\ProductDetailResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function show(Request $request, Product $product): Response
    {
        abort_unless($product->is_active, 404);

        $product->load(['images', 'variants.options', 'variants.images', 'category', 'collection', 'material', 'stone']);

        $related = Product::active()
            ->where('id', '!=', $product->id)
            ->where(function ($q) use ($product) {
                $q->where('collection_id', $product->collection_id)
                    ->orWhere('category_id', $product->category_id);
            })
            ->with(['images', 'collection'])
            ->inRandomOrder()
            ->take(4)
            ->get();

        return Inertia::render('store/ProductShow', [
            'product' => (new ProductDetailResource($product))->resolve(),
            'related' => ProductCardResource::collection($related),
            'breadcrumbs' => array_filter([
                ['label' => 'Joyería', 'href' => '/joyeria'],
                $product->category ? ['label' => $product->category->name, 'href' => "/joyeria?categoria={$product->category->slug}"] : null,
                ['label' => $product->name, 'href' => null],
            ]),
        ]);
    }
}
