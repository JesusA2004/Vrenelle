<?php

namespace App\Models;

use App\Enums\PromotionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $internal_name
 * @property string|null $description
 * @property PromotionType $type
 * @property float $value
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property int|null $minimum_purchase
 * @property int|null $maximum_discount
 * @property int|null $usage_limit
 * @property int|null $usage_per_user
 * @property int $used_count
 * @property bool $active
 * @property int $priority
 * @property bool $stackable
 */
class Promotion extends Model
{
    protected $fillable = [
        'name', 'internal_name', 'description', 'type', 'value',
        'starts_at', 'ends_at', 'minimum_purchase', 'maximum_discount',
        'usage_limit', 'usage_per_user', 'used_count', 'active', 'priority', 'stackable',
    ];

    protected function casts(): array
    {
        return [
            'type' => PromotionType::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'active' => 'boolean',
            'stackable' => 'boolean',
        ];
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'promotion_category');
    }

    /**
     * @return BelongsToMany<Collection, $this>
     */
    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'promotion_collection');
    }

    /**
     * @return BelongsToMany<Product, $this>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'promotion_product');
    }

    public function scopeActive(Builder $query): Builder
    {
        $now = now();

        return $query->where('active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now));
    }

    public function hasReachedUsageLimit(): bool
    {
        return $this->usage_limit !== null && $this->used_count >= $this->usage_limit;
    }
}
