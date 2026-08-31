<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\WhatsAppHelper;
use Illuminate\Http\Request;

/**
 * Builds a wa.me deep link server-side so the store's WhatsApp number never
 * needs to live in the JS bundle — it stays centralized in admin settings.
 */
class WhatsAppLinkController extends Controller
{
    public function generate(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'reason' => ['required', 'in:out_of_stock,exceeded,made_to_order,inquiry'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $variant = isset($data['product_variant_id']) ? ProductVariant::find($data['product_variant_id']) : null;
        $quantity = $data['quantity'] ?? 1;
        $url = url("/producto/{$product->slug}");

        $link = match ($data['reason']) {
            'out_of_stock', 'exceeded' => WhatsAppHelper::forExceededQuantity($product, $variant, $quantity, $url),
            'made_to_order' => WhatsAppHelper::forMadeToOrder($product, $variant, $url),
            'inquiry' => WhatsAppHelper::forInquiryOnly($product, $url),
        };

        return response()->json(['url' => $link]);
    }
}
