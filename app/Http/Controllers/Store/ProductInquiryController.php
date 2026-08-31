<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductInquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductInquiryController extends Controller
{
    /**
     * Records a customer's request for a piece that is out of stock, made
     * to order, or inquiry-only — even when they ultimately reach out via
     * WhatsApp instead. This is how demand for pieces with no inventory
     * gets measured.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'quantity' => ['required', 'integer', 'min:1', 'max:50'],
            'message' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Tu nombre es obligatorio.',
            'phone.required' => 'Un teléfono de contacto es obligatorio.',
            'quantity.required' => 'Indica la cantidad que te interesa.',
        ]);

        $product = Product::findOrFail($data['product_id']);

        ProductInquiry::create([
            ...$data,
            'status' => 'pending',
            'source' => 'form',
            'created_at' => now(),
        ]);

        return back()->with('success', "Hemos recibido tu solicitud para {$product->name}. Nuestro equipo te contactará pronto.");
    }
}
