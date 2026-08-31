<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    protected $fillable = ['name', 'description', 'price', 'free_from', 'estimated_days', 'active', 'sort_order'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true)->orderBy('sort_order');
    }

    public function priceFor(int $subtotalCents): int
    {
        if ($this->free_from !== null && $subtotalCents >= $this->free_from) {
            return 0;
        }

        return $this->price;
    }
}
