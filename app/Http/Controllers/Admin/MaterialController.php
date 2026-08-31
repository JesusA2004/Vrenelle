<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class MaterialController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('materials.manage'), 403);

        return Inertia::render('admin/materials/Index', [
            'materials' => Material::withCount('products')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('materials.manage'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'hex_color' => ['nullable', 'string', 'max:7'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ], ['name.required' => 'El nombre es obligatorio.']);

        $data['slug'] = Str::slug($data['name']);
        Material::create($data);

        return back()->with('success', 'Material creado correctamente.');
    }

    public function update(Request $request, Material $material): RedirectResponse
    {
        abort_unless($request->user()->can('materials.manage'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'hex_color' => ['nullable', 'string', 'max:7'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ], ['name.required' => 'El nombre es obligatorio.']);

        $material->update($data);

        return back()->with('success', 'Material actualizado correctamente.');
    }

    public function destroy(Request $request, Material $material): RedirectResponse
    {
        abort_unless($request->user()->can('materials.manage'), 403);
        $material->delete();

        return back()->with('success', 'Material eliminado correctamente.');
    }
}
