<?php

namespace App\Models;

use App\Enums\AddressLabel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'user_id', 'label', 'full_name', 'phone', 'street', 'ext_number', 'int_number',
        'neighborhood', 'city', 'state', 'postal_code', 'country', 'references', 'is_default',
    ];

    protected function casts(): array
    {
        return [
            'label' => AddressLabel::class,
            'is_default' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function toSnapshot(): array
    {
        return [
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'street' => $this->street,
            'ext_number' => $this->ext_number,
            'int_number' => $this->int_number,
            'neighborhood' => $this->neighborhood,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'references' => $this->references,
        ];
    }
}
