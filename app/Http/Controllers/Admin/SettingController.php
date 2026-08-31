<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function edit(Request $request): Response
    {
        abort_unless($request->user()->can('settings.manage'), 403);

        $settings = Setting::all()->mapWithKeys(fn (Setting $s) => [$s->key => $s->castValue()]);

        return Inertia::render('admin/settings/Edit', ['settings' => $settings]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('settings.manage'), 403);

        $data = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'store_tagline' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'logo_dark' => ['nullable', 'image', 'max:2048'],
            'favicon' => ['nullable', 'image', 'max:512'],
            'whatsapp_number' => ['nullable', 'string', 'max:20'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_address' => ['nullable', 'string', 'max:500'],
            'currency' => ['required', 'string', 'max:3'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'prices_include_tax' => ['boolean'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'tiktok_url' => ['nullable', 'url', 'max:255'],
            'pinterest_url' => ['nullable', 'url', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ], ['store_name.required' => 'El nombre de la tienda es obligatorio.']);

        $before = Setting::all()->mapWithKeys(fn ($s) => [$s->key => $s->value])->toArray();

        foreach (['logo', 'logo_dark', 'favicon'] as $imageField) {
            if ($request->hasFile($imageField)) {
                $data[$imageField] = $request->file($imageField)->store('settings', 'public');
            } else {
                unset($data[$imageField]);
            }
        }

        foreach ($data as $key => $value) {
            $type = in_array($key, ['prices_include_tax'], true) ? 'boolean' : (in_array($key, ['tax_rate'], true) ? 'integer' : 'string');
            Setting::updateOrCreate(['key' => $key], ['value' => $value, 'type' => $type, 'group' => 'general']);
        }

        AuditService::log('updated', new Setting(['key' => 'settings']), $before, $data);

        return back()->with('success', 'Configuración actualizada correctamente.');
    }
}
