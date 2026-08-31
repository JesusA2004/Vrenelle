<?php

namespace App\Http\Resources;

use App\Enums\ProductInventoryMode;
use App\Models\Product;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin Product
 */
class ProductDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'sku' => $this->sku,
            'shortDescription' => $this->short_description,
            'description' => $this->description,
            'collection' => $this->whenLoaded('collection', fn () => $this->collection ? [
                'name' => $this->collection->name,
                'slug' => $this->collection->slug,
            ] : null),
            'category' => $this->whenLoaded('category', fn () => $this->category ? [
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : null),
            'material' => $this->whenLoaded('material', fn () => $this->material?->name),
            'stone' => $this->whenLoaded('stone', fn () => $this->stone?->name),

            'showPrice' => $this->show_price,
            'price' => $this->show_price ? Money::format($this->price) : null,
            'priceCents' => $this->show_price ? $this->price : null,
            'compareAtPrice' => $this->show_price && $this->isOnSale() ? Money::format($this->compare_at_price) : null,
            'discountPercentage' => $this->discountPercentage(),

            'inventoryMode' => $this->inventory_mode->value,
            'hasImmediateStock' => $this->hasImmediateStock(),
            'isCustomizable' => $this->is_customizable,

            'images' => $this->whenLoaded('images', fn () => $this->images
                ->whereNull('product_variant_id')
                ->values()
                ->map(fn ($image) => [
                    'id' => $image->id,
                    'url' => Storage::disk('public')->url($image->path),
                    'alt' => $image->alt,
                    'type' => $image->type->value,
                    'isPrimary' => $image->is_primary,
                ])),

            'variants' => $this->whenLoaded('variants', fn () => $this->variants
                ->where('is_active', true)
                ->values()
                ->map(fn ($variant) => [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'name' => $variant->name,
                    'available' => $variant->isAvailable(),
                    'price' => $variant->price ? Money::format($variant->price) : null,
                    'options' => $variant->options->map(fn ($option) => [
                        'name' => $option->option_name,
                        'value' => $option->option_value,
                    ]),
                    'images' => $this->images
                        ->where('product_variant_id', $variant->id)
                        ->values()
                        ->map(fn ($image) => Storage::disk('public')->url($image->path)),
                ])),

            'attributes' => array_filter([
                'Quilates' => $this->carats,
                'Pureza' => $this->purity,
                'Peso' => $this->weight,
                'Color' => $this->color,
                'Corte' => $this->cut,
                'Talla' => $this->size,
                'Longitud' => $this->length,
                'Ancho' => $this->width,
                'Alto' => $this->height,
                'Acabado' => $this->finish,
                'Certificación' => $this->certification,
                'País de fabricación' => $this->country_of_origin,
            ]),
            'warranty' => $this->warranty,
            'careInstructions' => $this->care_instructions,

            'metaTitle' => $this->meta_title ?? $this->name,
            'metaDescription' => $this->meta_description ?? $this->short_description,

            'inventoryModeEnum' => $this->inventory_mode instanceof ProductInventoryMode ? $this->inventory_mode->value : $this->inventory_mode,
        ];
    }
}
