<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BannerController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('banners.manage'), 403);

        return Inertia::render('admin/banners/Index', [
            'banners' => Banner::orderBy('position')->orderBy('sort_order')->get(),
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()->can('banners.manage'), 403);

        return Inertia::render('admin/banners/Form', []);
    }

    protected function rules(): array
    {
        return [
            'position' => ['required', Rule::in(['hero', 'story', 'special_collection', 'strip'])],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'desktop_media' => ['nullable', 'image', 'max:8192'],
            'mobile_media' => ['nullable', 'image', 'max:8192'],
            'cta_text' => ['nullable', 'string', 'max:100'],
            'cta_url' => ['nullable', 'string', 'max:255'],
            'content_position' => ['required', Rule::in(['left', 'center', 'right'])],
            'text_theme' => ['required', Rule::in(['light', 'dark'])],
            'overlay' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'active' => ['boolean'],
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('banners.manage'), 403);

        $data = $request->validate($this->rules());

        foreach (['desktop_media', 'mobile_media'] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('banners', 'public');
            }
        }

        Banner::create($data);

        return redirect()->route('admin.banners.index')->with('success', 'Banner creado correctamente.');
    }

    public function edit(Request $request, Banner $banner): Response
    {
        abort_unless($request->user()->can('banners.manage'), 403);

        return Inertia::render('admin/banners/Form', ['banner' => $banner]);
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        abort_unless($request->user()->can('banners.manage'), 403);

        $data = $request->validate($this->rules());

        foreach (['desktop_media', 'mobile_media'] as $field) {
            if ($request->hasFile($field)) {
                if ($banner->{$field}) {
                    Storage::disk('public')->delete($banner->{$field});
                }
                $data[$field] = $request->file($field)->store('banners', 'public');
            }
        }

        $banner->update($data);

        return redirect()->route('admin.banners.index')->with('success', 'Banner actualizado correctamente.');
    }

    public function destroy(Request $request, Banner $banner): RedirectResponse
    {
        abort_unless($request->user()->can('banners.manage'), 403);
        $banner->delete();

        return back()->with('success', 'Banner eliminado correctamente.');
    }
}
