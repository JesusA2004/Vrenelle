<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class StoneController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('materials.manage'), 403);

        return Inertia::render('admin/stones/Index', [
            'stones' => Stone::withCount('products')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('materials.manage'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ], ['name.required' => 'El nombre es obligatorio.']);

        $data['slug'] = Str::slug($data['name']);
        Stone::create($data);

        return back()->with('success', 'Piedra creada correctamente.');
    }

    public function update(Request $request, Stone $stone): RedirectResponse
    {
        abort_unless($request->user()->can('materials.manage'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ], ['name.required' => 'El nombre es obligatorio.']);

        $stone->update($data);

        return back()->with('success', 'Piedra actualizada correctamente.');
    }

    public function destroy(Request $request, Stone $stone): RedirectResponse
    {
        abort_unless($request->user()->can('materials.manage'), 403);
        $stone->delete();

        return back()->with('success', 'Piedra eliminada correctamente.');
    }
}
