<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCardResource;
use App\Models\Category;
use App\Models\Collection as ProductCollection;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json(['products' => [], 'categories' => [], 'collections' => []]);
        }

        $products = Product::active()
            ->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('sku', 'like', "%{$term}%"))
            ->with(['images'])
            ->take(6)
            ->get();

        $categories = Category::active()->where('name', 'like', "%{$term}%")->take(4)->get(['name', 'slug']);
        $collections = ProductCollection::active()->where('name', 'like', "%{$term}%")->take(4)->get(['name', 'slug', 'image']);

        return response()->json([
            'products' => ProductCardResource::collection($products),
            'categories' => $categories,
            'collections' => $collections->map(fn (ProductCollection $c) => [
                'name' => $c->name,
                'slug' => $c->slug,
                'image' => $c->image ? Storage::disk('public')->url($c->image) : null,
            ]),
        ]);
    }
}
