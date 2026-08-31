<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'store_name', 'value' => 'Vrenelle', 'type' => 'string', 'group' => 'general'],
            ['key' => 'store_tagline', 'value' => 'Joyería de autor, hecha para perdurar.', 'type' => 'string', 'group' => 'general'],
            ['key' => 'logo', 'value' => null, 'type' => 'image', 'group' => 'general'],
            ['key' => 'logo_dark', 'value' => null, 'type' => 'image', 'group' => 'general'],
            ['key' => 'favicon', 'value' => null, 'type' => 'image', 'group' => 'general'],
            ['key' => 'whatsapp_number', 'value' => '5215500000000', 'type' => 'string', 'group' => 'contact'],
            ['key' => 'contact_phone', 'value' => '+52 55 0000 0000', 'type' => 'string', 'group' => 'contact'],
            ['key' => 'contact_email', 'value' => 'contacto@vrenelle.mx', 'type' => 'string', 'group' => 'contact'],
            ['key' => 'contact_address', 'value' => 'Av. Presidente Masaryk 123, Polanco, Ciudad de México', 'type' => 'string', 'group' => 'contact'],
            ['key' => 'currency', 'value' => 'MXN', 'type' => 'string', 'group' => 'commerce'],
            ['key' => 'tax_rate', 'value' => '16', 'type' => 'integer', 'group' => 'commerce'],
            ['key' => 'prices_include_tax', 'value' => '1', 'type' => 'boolean', 'group' => 'commerce'],
            ['key' => 'instagram_url', 'value' => 'https://instagram.com', 'type' => 'string', 'group' => 'social'],
            ['key' => 'facebook_url', 'value' => 'https://facebook.com', 'type' => 'string', 'group' => 'social'],
            ['key' => 'tiktok_url', 'value' => 'https://tiktok.com', 'type' => 'string', 'group' => 'social'],
            ['key' => 'pinterest_url', 'value' => 'https://pinterest.com', 'type' => 'string', 'group' => 'social'],
            ['key' => 'meta_title', 'value' => 'Vrenelle — Joyería de autor', 'type' => 'string', 'group' => 'seo'],
            ['key' => 'meta_description', 'value' => 'Piezas de joyería fina diseñadas y elaboradas con materiales nobles. Colecciones exclusivas, hechas para perdurar.', 'type' => 'string', 'group' => 'seo'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
