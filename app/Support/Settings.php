<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Centralized, cached access to the `settings` table. Never hardcode store
 * configuration (WhatsApp number, currency, social links, etc.) in
 * controllers or components — always read it through this class.
 */
class Settings
{
    /**
     * @return array<string, mixed>
     */
    protected static function all(): array
    {
        return Cache::rememberForever('settings.all', function () {
            return Setting::all()->mapWithKeys(fn (Setting $setting) => [
                $setting->key => $setting->castValue(),
            ])->toArray();
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::all()[$key] ?? $default;
    }

    public static function set(string $key, mixed $value, string $type = 'string', string $group = 'general'): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => is_array($value) ? json_encode($value) : $value, 'type' => $type, 'group' => $group]
        );
    }

    public static function storeName(): string
    {
        return static::get('store_name', config('app.name'));
    }

    public static function whatsappNumber(): ?string
    {
        return static::get('whatsapp_number');
    }

    public static function currency(): string
    {
        return static::get('currency', 'MXN');
    }

    public static function taxRate(): float
    {
        return (float) static::get('tax_rate', 0);
    }

    public static function pricesIncludeTax(): bool
    {
        return (bool) static::get('prices_include_tax', true);
    }
}
