<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShippingMethodController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('settings.manage') || $request->user()->can('promotions.manage'), 403);

        return Inertia::render('admin/shipping/Index', [
            'methods' => ShippingMethod::orderBy('sort_order')->get(),
        ]);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'numeric', 'min:0'],
            'free_from' => ['nullable', 'numeric', 'min:0'],
            'estimated_days' => ['nullable', 'string', 'max:100'],
            'active' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('settings.manage') || $request->user()->can('promotions.manage'), 403);

        $data = $request->validate($this->rules(), ['name.required' => 'El nombre es obligatorio.']);
        $data['price'] = (int) round($data['price'] * 100);
        $data['free_from'] = isset($data['free_from']) ? (int) round($data['free_from'] * 100) : null;

        ShippingMethod::create($data);

        return back()->with('success', 'Método de envío creado correctamente.');
    }

    public function update(Request $request, ShippingMethod $shippingMethod): RedirectResponse
    {
        abort_unless($request->user()->can('settings.manage') || $request->user()->can('promotions.manage'), 403);

        $data = $request->validate($this->rules(), ['name.required' => 'El nombre es obligatorio.']);
        $data['price'] = (int) round($data['price'] * 100);
        $data['free_from'] = isset($data['free_from']) ? (int) round($data['free_from'] * 100) : null;

        $shippingMethod->update($data);

        return back()->with('success', 'Método de envío actualizado correctamente.');
    }

    public function destroy(Request $request, ShippingMethod $shippingMethod): RedirectResponse
    {
        abort_unless($request->user()->can('settings.manage'), 403);
        $shippingMethod->delete();

        return back()->with('success', 'Método de envío eliminado correctamente.');
    }
}
