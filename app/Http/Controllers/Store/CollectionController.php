<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCardResource;
use App\Models\Collection as ProductCollection;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CollectionController extends Controller
{
    public function index(): Response
    {
        $collections = ProductCollection::active()->orderBy('sort_order')->get();

        return Inertia::render('store/Collections', [
            'collections' => $collections->map(fn (ProductCollection $c) => [
                'name' => $c->name,
                'slug' => $c->slug,
                'shortDescription' => $c->short_description,
                'image' => $c->image ? Storage::disk('public')->url($c->image) : null,
            ]),
        ]);
    }

    public function show(ProductCollection $collection): Response
    {
        abort_unless($collection->is_active, 404);

        $products = $collection->products()->active()->with(['images'])->paginate(12)->withQueryString();

        return Inertia::render('store/CollectionShow', [
            'collection' => [
                'name' => $collection->name,
                'slug' => $collection->slug,
                'shortDescription' => $collection->short_description,
                'description' => $collection->description,
                'story' => $collection->story,
                'image' => $collection->image ? Storage::disk('public')->url($collection->image) : null,
                'mobileImage' => $collection->mobile_image ? Storage::disk('public')->url($collection->mobile_image) : null,
                'banner' => $collection->banner ? Storage::disk('public')->url($collection->banner) : null,
                'metaTitle' => $collection->meta_title ?? $collection->name,
                'metaDescription' => $collection->meta_description ?? $collection->short_description,
            ],
            'products' => $products->through(fn (Product $product) => (new ProductCardResource($product))->resolve()),
        ]);
    }
}
