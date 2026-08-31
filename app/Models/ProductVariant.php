<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'sku', 'name', 'price', 'stock', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return HasMany<VariantOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(VariantOption::class);
    }

    /**
     * @return HasMany<ProductImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function isAvailable(): bool
    {
        return $this->is_active && $this->stock > 0;
    }

    /**
     * Effective price in cents: the variant override, or the parent product's price.
     */
    public function effectivePrice(): int
    {
        return $this->price ?? $this->product->price;
    }
}
