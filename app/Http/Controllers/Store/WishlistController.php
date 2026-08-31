<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCardResource;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WishlistController extends Controller
{
    public function index(Request $request): Response
    {
        $wishlist = $request->user()->wishlist()->with('items.product.images')->first();

        $products = $wishlist
            ? $wishlist->items->map(fn ($item) => $item->product)->filter()
            : collect();

        return Inertia::render('store/Wishlist', [
            'products' => ProductCardResource::collection($products->values()),
        ]);
    }

    public function toggle(Request $request): RedirectResponse
    {
        $data = $request->validate(['product_id' => ['required', 'integer', 'exists:products,id']]);

        $wishlist = $request->user()->wishlist()->firstOrCreate([]);
        $product = Product::findOrFail($data['product_id']);

        $existing = $wishlist->items()->where('product_id', $product->id)->first();

        if ($existing) {
            $existing->delete();

            return back()->with('success', 'Pieza eliminada de tus favoritos.');
        }

        $wishlist->items()->create(['product_id' => $product->id]);

        return back()->with('success', 'Pieza añadida a tus favoritos.');
    }
}
