<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SlugGenerator
{
    /**
     * Generates a unique slug for the given model class/column, appending
     * -2, -3, ... on collision. Pass $ignoreId when updating an existing row.
     */
    public static function unique(string $modelClass, string $source, string $column = 'slug', ?int $ignoreId = null): string
    {
        $base = Str::slug($source);
        $slug = $base;
        $i = 2;

        /** @var Model $modelClass */
        while ($modelClass::query()
            ->where($column, $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
