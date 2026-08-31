<?php

namespace App\Models;

use App\Enums\PaymentProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PaymentInstallmentRule extends Model
{
    protected $fillable = [
        'provider', 'months', 'minimum_amount', 'maximum_amount',
        'fee_percentage', 'customer_fee_percentage', 'merchant_fee_percentage', 'active',
    ];

    protected function casts(): array
    {
        return [
            'provider' => PaymentProvider::class,
            'active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function appliesTo(int $amountCents): bool
    {
        if ($amountCents < $this->minimum_amount) {
            return false;
        }

        if ($this->maximum_amount !== null && $amountCents > $this->maximum_amount) {
            return false;
        }

        return true;
    }
}
