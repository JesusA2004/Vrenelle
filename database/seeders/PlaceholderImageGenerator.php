<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Storage;

/**
 * Generates simple, elegant dark gradient SVG placeholders for demo seed
 * data so the storefront never ships with broken image tags before real
 * product photography is uploaded through the admin panel.
 *
 * Deliberately dark (jewelry-on-dark-velvet tones) rather than pale: hero
 * and card overlays assume moody photography, and white/ivory text sits on
 * top of every one of these without needing a heavy tint to stay legible.
 */
class PlaceholderImageGenerator
{
    /**
     * @var array<int, array{0:string,1:string}>
     */
    protected static array $palettes = [
        ['#1a1714', '#3d3226'],
        ['#14120f', '#2e2419'],
        ['#1c1a22', '#332b3d'],
        ['#15140f', '#2a2a1f'],
        ['#171310', '#3a2a1c'],
        ['#15161a', '#262a30'],
    ];

    public static function product(string $slug, int $index, string $label): string
    {
        $path = "products/{$slug}-{$index}.svg";

        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, static::svg($label, $index));
        }

        return $path;
    }

    public static function collection(string $slug, string $label): string
    {
        $path = "collections/{$slug}.svg";

        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, static::svg($label, crc32($slug), 1200, 1500));
        }

        return $path;
    }

    public static function banner(string $slug, string $label): string
    {
        $path = "banners/{$slug}.svg";

        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, static::svg($label, crc32($slug), 1920, 1080));
        }

        return $path;
    }

    public static function category(string $slug, string $label): string
    {
        $path = "categories/{$slug}.svg";

        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, static::svg($label, crc32($slug), 800, 1000));
        }

        return $path;
    }

    protected static function svg(string $label, int $seed, int $width = 1000, int $height = 1250): string
    {
        [$from, $to] = static::$palettes[$seed % count(static::$palettes)];
        $gradientId = 'g'.abs($seed);
        $vignetteId = 'v'.abs($seed);

        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}">
            <defs>
                <linearGradient id="{$gradientId}" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="{$from}" />
                    <stop offset="100%" stop-color="{$to}" />
                </linearGradient>
                <radialGradient id="{$vignetteId}" cx="50%" cy="45%" r="75%">
                    <stop offset="55%" stop-color="#000000" stop-opacity="0" />
                    <stop offset="100%" stop-color="#000000" stop-opacity="0.35" />
                </radialGradient>
            </defs>
            <rect width="100%" height="100%" fill="url(#{$gradientId})" />
            <rect width="100%" height="100%" fill="url(#{$vignetteId})" />
        </svg>
        SVG;
    }
}
