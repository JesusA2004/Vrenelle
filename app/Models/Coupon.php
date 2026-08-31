<?php

namespace App\Models;

use App\Enums\CouponType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $code
 * @property string|null $description
 * @property CouponType $type
 * @property float $value
 * @property int|null $minimum_purchase
 * @property int|null $maximum_discount
 * @property Carbon|null $starts_at
 * @property Carbon|null $expires_at
 * @property int|null $usage_limit
 * @property int|null $usage_per_customer
 * @property int $used_count
 * @property bool $active
 * @property bool $first_purchase_only
 * @property bool $stackable
 */
class Coupon extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'description', 'type', 'value', 'minimum_purchase', 'maximum_discount',
        'starts_at', 'expires_at', 'usage_limit', 'usage_per_customer', 'used_count',
        'active', 'first_purchase_only', 'stackable',
    ];

    protected function casts(): array
    {
        return [
            'type' => CouponType::class,
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'active' => 'boolean',
            'first_purchase_only' => 'boolean',
            'stackable' => 'boolean',
        ];
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'coupon_category');
    }

    /**
     * @return BelongsToMany<Collection, $this>
     */
    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'coupon_collection');
    }

    /**
     * @return BelongsToMany<Product, $this>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'coupon_product');
    }

    /**
     * @return HasMany<CouponUsage, $this>
     */
    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        $now = now();

        return $query->where('active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now));
    }

    public function hasReachedUsageLimit(): bool
    {
        return $this->usage_limit !== null && $this->used_count >= $this->usage_limit;
    }
}
