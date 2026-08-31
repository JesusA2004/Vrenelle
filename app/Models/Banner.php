<?php

namespace App\Models;

use App\Enums\BannerPosition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'position', 'title', 'subtitle', 'description', 'desktop_media', 'mobile_media',
        'video_desktop', 'video_mobile', 'cta_text', 'cta_url', 'content_position',
        'text_theme', 'overlay', 'sort_order', 'starts_at', 'ends_at', 'active',
    ];

    protected function casts(): array
    {
        return [
            'position' => BannerPosition::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'active' => 'boolean',
        ];
    }

    public function scopeVisible(Builder $query): Builder
    {
        $now = now();

        return $query->where('active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
            ->orderBy('sort_order');
    }

    public function scopePosition(Builder $query, BannerPosition|string $position): Builder
    {
        return $query->where('position', $position instanceof BannerPosition ? $position->value : $position);
    }
}
