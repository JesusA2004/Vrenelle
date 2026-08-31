<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function destroy(Request $request, Product $product, ProductImage $image): RedirectResponse
    {
        abort_unless($request->user()->can('products.edit'), 403);
        abort_unless($image->product_id === $product->id, 404);

        Storage::disk('public')->delete($image->path);
        $wasPrimary = $image->is_primary;
        $image->delete();

        if ($wasPrimary) {
            $product->images()->orderBy('sort_order')->first()?->update(['is_primary' => true]);
        }

        return back()->with('success', 'Imagen eliminada.');
    }

    public function setPrimary(Request $request, Product $product, ProductImage $image): RedirectResponse
    {
        abort_unless($request->user()->can('products.edit'), 403);
        abort_unless($image->product_id === $product->id, 404);

        $product->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return back()->with('success', 'Imagen principal actualizada.');
    }

    public function reorder(Request $request, Product $product): RedirectResponse
    {
        abort_unless($request->user()->can('products.edit'), 403);

        $data = $request->validate(['order' => ['required', 'array'], 'order.*' => ['integer']]);

        foreach ($data['order'] as $index => $imageId) {
            $product->images()->where('id', $imageId)->update(['sort_order' => $index]);
        }

        return back();
    }
}
