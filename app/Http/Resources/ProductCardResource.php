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
class ProductCardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $images = $this->images;
        $primary = $images->firstWhere('is_primary', true) ?? $images->first();
        $secondary = $images->firstWhere('id', '!=', $primary?->id) ?? $primary;

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'sku' => $this->sku,
            'collection' => $this->whenLoaded('collection', fn () => $this->collection ? [
                'name' => $this->collection->name,
                'slug' => $this->collection->slug,
            ] : null),
            'category' => $this->whenLoaded('category', fn () => $this->category ? [
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : null),
            'material' => $this->whenLoaded('material', fn () => $this->material?->name),
            'image' => $primary ? Storage::disk('public')->url($primary->path) : null,
            'imageHover' => $secondary ? Storage::disk('public')->url($secondary->path) : null,
            'showPrice' => $this->show_price,
            'price' => $this->show_price ? Money::format($this->price) : null,
            'compareAtPrice' => $this->show_price && $this->isOnSale() ? Money::format($this->compare_at_price) : null,
            'discountPercentage' => $this->discountPercentage(),
            'inventoryMode' => $this->inventory_mode->value,
            'available' => $this->inventory_mode !== ProductInventoryMode::Stock || $this->stock > 0,
            'badges' => $this->badges(),
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function badges(): array
    {
        $badges = [];

        if ($this->is_new) {
            $badges[] = 'Nuevo';
        }

        if ($this->is_exclusive) {
            $badges[] = 'Exclusivo';
        }

        if ($this->is_customizable) {
            $badges[] = 'Personalizable';
        }

        if ($this->isOnSale()) {
            $badges[] = 'Oferta';
        }

        if ($this->inventory_mode === ProductInventoryMode::MadeToOrder) {
            $badges[] = 'Pedido especial';
        }

        return $badges;
    }
}
