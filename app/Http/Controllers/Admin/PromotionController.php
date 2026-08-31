<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PromotionController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('promotions.view'), 403);

        return Inertia::render('admin/promotions/Index', [
            'promotions' => Promotion::latest()->paginate(15),
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()->can('promotions.manage'), 403);

        return Inertia::render('admin/promotions/Form', $this->formProps());
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'internal_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', Rule::in(['percentage', 'fixed', 'special_price', 'free_shipping', 'collection_discount', 'category_discount'])],
            'value' => ['required', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'minimum_purchase' => ['nullable', 'numeric', 'min:0'],
            'maximum_discount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_per_user' => ['nullable', 'integer', 'min:1'],
            'active' => ['boolean'],
            'priority' => ['nullable', 'integer'],
            'stackable' => ['boolean'],
            'category_ids' => ['nullable', 'array'],
            'collection_ids' => ['nullable', 'array'],
            'product_ids' => ['nullable', 'array'],
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('promotions.manage'), 403);

        $data = $request->validate($this->rules(), ['name.required' => 'El nombre es obligatorio.']);
        $data = $this->normalizeMoney($data);

        $promotion = Promotion::create($data);
        $this->syncRelations($promotion, $data);

        return redirect()->route('admin.promotions.index')->with('success', 'Promoción creada correctamente.');
    }

    public function edit(Request $request, Promotion $promotion): Response
    {
        abort_unless($request->user()->can('promotions.manage'), 403);

        $promotion->load('categories:id', 'collections:id', 'products:id');

        return Inertia::render('admin/promotions/Form', [
            ...$this->formProps(),
            'promotion' => [
                ...$promotion->toArray(),
                'minimum_purchase' => $promotion->minimum_purchase ? $promotion->minimum_purchase / 100 : null,
                'maximum_discount' => $promotion->maximum_discount ? $promotion->maximum_discount / 100 : null,
                'category_ids' => $promotion->categories->pluck('id'),
                'collection_ids' => $promotion->collections->pluck('id'),
                'product_ids' => $promotion->products->pluck('id'),
            ],
        ]);
    }

    public function update(Request $request, Promotion $promotion): RedirectResponse
    {
        abort_unless($request->user()->can('promotions.manage'), 403);

        $data = $request->validate($this->rules(), ['name.required' => 'El nombre es obligatorio.']);
        $data = $this->normalizeMoney($data);

        $promotion->update($data);
        $this->syncRelations($promotion, $data);

        return redirect()->route('admin.promotions.index')->with('success', 'Promoción actualizada correctamente.');
    }

    public function destroy(Request $request, Promotion $promotion): RedirectResponse
    {
        abort_unless($request->user()->can('promotions.manage'), 403);
        $promotion->delete();

        return back()->with('success', 'Promoción eliminada correctamente.');
    }

    protected function normalizeMoney(array $data): array
    {
        $data['minimum_purchase'] = isset($data['minimum_purchase']) ? (int) round($data['minimum_purchase'] * 100) : null;
        $data['maximum_discount'] = isset($data['maximum_discount']) ? (int) round($data['maximum_discount'] * 100) : null;

        return $data;
    }

    protected function syncRelations(Promotion $promotion, array $data): void
    {
        $promotion->categories()->sync($data['category_ids'] ?? []);
        $promotion->collections()->sync($data['collection_ids'] ?? []);
        $promotion->products()->sync($data['product_ids'] ?? []);
    }

    protected function formProps(): array
    {
        return [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'collections' => Collection::orderBy('name')->get(['id', 'name']),
            'products' => Product::orderBy('name')->get(['id', 'name']),
        ];
    }
}
