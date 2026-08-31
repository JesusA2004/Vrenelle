<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->isMethod('POST') ? 'products.create' : 'products.edit';

        return $this->user()->can($permission);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'collection_id' => ['nullable', 'integer', 'exists:collections,id'],
            'material_id' => ['nullable', 'integer', 'exists:materials,id'],
            'stone_id' => ['nullable', 'integer', 'exists:stones,id'],

            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($productId)],
            'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($productId)],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:10000'],

            'price' => ['required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],

            'inventory_mode' => ['required', Rule::in(['stock', 'made_to_order', 'inquiry_only'])],
            'stock' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'show_price' => ['boolean'],

            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'is_new' => ['boolean'],
            'is_exclusive' => ['boolean'],
            'is_customizable' => ['boolean'],

            'carats' => ['nullable', 'string', 'max:100'],
            'purity' => ['nullable', 'string', 'max:100'],
            'weight' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:100'],
            'cut' => ['nullable', 'string', 'max:100'],
            'size' => ['nullable', 'string', 'max:100'],
            'length' => ['nullable', 'string', 'max:100'],
            'width' => ['nullable', 'string', 'max:100'],
            'height' => ['nullable', 'string', 'max:100'],
            'finish' => ['nullable', 'string', 'max:100'],
            'certification' => ['nullable', 'string', 'max:255'],
            'country_of_origin' => ['nullable', 'string', 'max:100'],
            'warranty' => ['nullable', 'string', 'max:255'],
            'care_instructions' => ['nullable', 'string', 'max:2000'],

            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'published_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'sku.required' => 'El SKU es obligatorio.',
            'sku.unique' => 'Ya existe un producto con este SKU.',
            'slug.unique' => 'Ya existe un producto con este slug.',
            'price.required' => 'El precio es obligatorio.',
            'price.numeric' => 'El precio debe ser un número.',
            'inventory_mode.required' => 'Selecciona el modo de disponibilidad.',
            'stock.required' => 'Indica el inventario disponible.',
        ];
    }
}
