<?php

namespace App\Models;

use App\Enums\ProductInventoryMode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $category_id
 * @property int|null $collection_id
 * @property int|null $material_id
 * @property int|null $stone_id
 * @property string $name
 * @property string $slug
 * @property string $sku
 * @property string|null $short_description
 * @property string|null $description
 * @property int $price
 * @property int|null $compare_at_price
 * @property int|null $cost
 * @property ProductInventoryMode $inventory_mode
 * @property int $stock
 * @property int $low_stock_threshold
 * @property bool $show_price
 * @property bool $is_active
 * @property bool $is_featured
 * @property bool $is_new
 * @property bool $is_exclusive
 * @property bool $is_customizable
 * @property string|null $carats
 * @property string|null $purity
 * @property string|null $weight
 * @property string|null $color
 * @property string|null $cut
 * @property string|null $size
 * @property string|null $length
 * @property string|null $width
 * @property string|null $height
 * @property string|null $finish
 * @property string|null $certification
 * @property string|null $country_of_origin
 * @property string|null $warranty
 * @property string|null $care_instructions
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property Carbon|null $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'collection_id', 'material_id', 'stone_id',
        'name', 'slug', 'sku', 'short_description', 'description',
        'price', 'compare_at_price', 'cost',
        'inventory_mode', 'stock', 'low_stock_threshold', 'show_price',
        'is_active', 'is_featured', 'is_new', 'is_exclusive', 'is_customizable',
        'carats', 'purity', 'weight', 'color', 'cut', 'size', 'length', 'width', 'height',
        'finish', 'certification', 'country_of_origin', 'warranty', 'care_instructions',
        'meta_title', 'meta_description', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'inventory_mode' => ProductInventoryMode::class,
            'show_price' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_new' => 'boolean',
            'is_exclusive' => 'boolean',
            'is_customizable' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<Collection, $this>
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * @return BelongsTo<Material, $this>
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * @return BelongsTo<Stone, $this>
     */
    public function stone(): BelongsTo
    {
        return $this->belongsTo(Stone::class);
    }

    /**
     * @return HasMany<ProductVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * @return HasMany<ProductImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<InventoryMovement, $this>
     */
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /**
     * @return HasMany<ProductInquiry, $this>
     */
    public function inquiries(): HasMany
    {
        return $this->hasMany(ProductInquiry::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return BelongsToMany<Promotion, $this>
     */
    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class, 'promotion_product');
    }

    /**
     * @return BelongsToMany<Coupon, $this>
     */
    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class, 'coupon_product');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function (Builder $q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeNewArrivals(Builder $query): Builder
    {
        return $query->where('is_new', true);
    }

    /**
     * Whether the product can currently be purchased for immediate delivery
     * (i.e. it has real stock at the product level). Does not consider
     * per-variant stock — use ProductVariant::isAvailable() for that.
     */
    public function hasImmediateStock(): bool
    {
        return $this->inventory_mode === ProductInventoryMode::Stock && $this->stock > 0;
    }

    public function isLowStock(): bool
    {
        return $this->inventory_mode === ProductInventoryMode::Stock
            && $this->stock > 0
            && $this->stock <= $this->low_stock_threshold;
    }

    public function isOnSale(): bool
    {
        return $this->compare_at_price !== null && $this->compare_at_price > $this->price;
    }

    public function discountPercentage(): ?int
    {
        if (! $this->isOnSale()) {
            return null;
        }

        return (int) round((($this->compare_at_price - $this->price) / $this->compare_at_price) * 100);
    }

    public function primaryImage(): ?ProductImage
    {
        return $this->images->firstWhere('is_primary', true) ?? $this->images->first();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
