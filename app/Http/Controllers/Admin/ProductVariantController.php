<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductVariantController extends Controller
{
    protected function rules(Product $product, ?int $ignoreId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', Rule::unique('product_variants', 'sku')->ignore($ignoreId)],
            'price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'options' => ['nullable', 'array'],
            'options.*.name' => ['required_with:options', 'string', 'max:100'],
            'options.*.value' => ['required_with:options', 'string', 'max:100'],
        ];
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($request->user()->can('products.edit'), 403);

        $data = $request->validate($this->rules($product), $this->messages());
        $options = $data['options'] ?? [];
        unset($data['options']);

        $data['price'] = isset($data['price']) ? Money::toCents($data['price']) : null;

        $variant = $product->variants()->create($data);

        foreach ($options as $i => $option) {
            $variant->options()->create(['option_name' => $option['name'], 'option_value' => $option['value'], 'sort_order' => $i]);
        }

        return back()->with('success', 'Variante creada correctamente.');
    }

    public function update(Request $request, Product $product, ProductVariant $variant): RedirectResponse
    {
        abort_unless($request->user()->can('products.edit'), 403);
        abort_unless($variant->product_id === $product->id, 404);

        $data = $request->validate($this->rules($product, $variant->id), $this->messages());
        $options = $data['options'] ?? [];
        unset($data['options']);

        $data['price'] = isset($data['price']) ? Money::toCents($data['price']) : null;

        $variant->update($data);
        $variant->options()->delete();

        foreach ($options as $i => $option) {
            $variant->options()->create(['option_name' => $option['name'], 'option_value' => $option['value'], 'sort_order' => $i]);
        }

        return back()->with('success', 'Variante actualizada correctamente.');
    }

    public function destroy(Request $request, Product $product, ProductVariant $variant): RedirectResponse
    {
        abort_unless($request->user()->can('products.edit'), 403);
        abort_unless($variant->product_id === $product->id, 404);

        $variant->delete();

        return back()->with('success', 'Variante eliminada correctamente.');
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'El nombre de la variante es obligatorio.',
            'sku.required' => 'El SKU de la variante es obligatorio.',
            'sku.unique' => 'Ya existe una variante con este SKU.',
            'stock.required' => 'Indica el inventario de la variante.',
        ];
    }
}
