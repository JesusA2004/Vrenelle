<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CouponController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('coupons.view'), 403);

        return Inertia::render('admin/coupons/Index', [
            'coupons' => Coupon::latest()->paginate(15),
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()->can('coupons.manage'), 403);

        return Inertia::render('admin/coupons/Form', $this->formProps());
    }

    protected function rules(?int $ignoreId = null): array
    {
        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('coupons', 'code')->ignore($ignoreId)],
            'description' => ['nullable', 'string', 'max:500'],
            'type' => ['required', Rule::in(['percentage', 'fixed', 'free_shipping'])],
            'value' => ['required', 'numeric', 'min:0'],
            'minimum_purchase' => ['nullable', 'numeric', 'min:0'],
            'maximum_discount' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_per_customer' => ['nullable', 'integer', 'min:1'],
            'active' => ['boolean'],
            'first_purchase_only' => ['boolean'],
            'stackable' => ['boolean'],
            'category_ids' => ['nullable', 'array'],
            'collection_ids' => ['nullable', 'array'],
            'product_ids' => ['nullable', 'array'],
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('coupons.manage'), 403);

        $data = $request->validate($this->rules(), $this->messages());
        $data['code'] = strtoupper($data['code']);
        $data = $this->normalizeMoney($data);

        $coupon = Coupon::create($data);
        $this->syncRelations($coupon, $data);

        return redirect()->route('admin.coupons.index')->with('success', 'Cupón creado correctamente.');
    }

    public function edit(Request $request, Coupon $coupon): Response
    {
        abort_unless($request->user()->can('coupons.manage'), 403);

        $coupon->load('categories:id', 'collections:id', 'products:id');

        return Inertia::render('admin/coupons/Form', [
            ...$this->formProps(),
            'coupon' => [
                ...$coupon->toArray(),
                'minimum_purchase' => $coupon->minimum_purchase ? $coupon->minimum_purchase / 100 : null,
                'maximum_discount' => $coupon->maximum_discount ? $coupon->maximum_discount / 100 : null,
                'category_ids' => $coupon->categories->pluck('id'),
                'collection_ids' => $coupon->collections->pluck('id'),
                'product_ids' => $coupon->products->pluck('id'),
            ],
        ]);
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        abort_unless($request->user()->can('coupons.manage'), 403);

        $data = $request->validate($this->rules($coupon->id), $this->messages());
        $data['code'] = strtoupper($data['code']);
        $data = $this->normalizeMoney($data);

        $coupon->update($data);
        $this->syncRelations($coupon, $data);

        return redirect()->route('admin.coupons.index')->with('success', 'Cupón actualizado correctamente.');
    }

    public function destroy(Request $request, Coupon $coupon): RedirectResponse
    {
        abort_unless($request->user()->can('coupons.manage'), 403);
        $coupon->delete();

        return back()->with('success', 'Cupón eliminado correctamente.');
    }

    protected function normalizeMoney(array $data): array
    {
        $data['minimum_purchase'] = isset($data['minimum_purchase']) ? (int) round($data['minimum_purchase'] * 100) : null;
        $data['maximum_discount'] = isset($data['maximum_discount']) ? (int) round($data['maximum_discount'] * 100) : null;

        return $data;
    }

    protected function syncRelations(Coupon $coupon, array $data): void
    {
        $coupon->categories()->sync($data['category_ids'] ?? []);
        $coupon->collections()->sync($data['collection_ids'] ?? []);
        $coupon->products()->sync($data['product_ids'] ?? []);
    }

    protected function formProps(): array
    {
        return [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'collections' => Collection::orderBy('name')->get(['id', 'name']),
            'products' => Product::orderBy('name')->get(['id', 'name']),
        ];
    }

    protected function messages(): array
    {
        return [
            'code.required' => 'El código es obligatorio.',
            'code.unique' => 'Ya existe un cupón con este código.',
            'value.required' => 'El valor del descuento es obligatorio.',
        ];
    }
}
