<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\AuditService;
use App\Support\SlugGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('categories.manage'), 403);

        $categories = Category::withCount('products')
            ->when($request->filled('buscar'), fn ($q) => $q->where('name', 'like', '%'.$request->string('buscar').'%'))
            ->orderBy('sort_order')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/categories/Index', [
            'categories' => $categories,
            'filters' => $request->only('buscar'),
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()->can('categories.manage'), 403);

        return Inertia::render('admin/categories/Form', [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    protected function rules(?int $ignoreId = null): array
    {
        return [
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($ignoreId)],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:4096'],
            'icon' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('categories.manage'), 403);

        $data = $request->validate($this->rules(), $this->messages());
        $data['slug'] = $data['slug'] ?? SlugGenerator::unique(Category::class, $data['name']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create($data);
        AuditService::log('created', $category, [], $category->toArray());

        return redirect()->route('admin.categories.index')->with('success', 'Categoría creada correctamente.');
    }

    public function edit(Request $request, Category $category): Response
    {
        abort_unless($request->user()->can('categories.manage'), 403);

        return Inertia::render('admin/categories/Form', [
            'category' => $category,
            'categories' => Category::where('id', '!=', $category->id)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        abort_unless($request->user()->can('categories.manage'), 403);

        $data = $request->validate($this->rules($category->id), $this->messages());
        $data['slug'] = $data['slug'] ?? SlugGenerator::unique(Category::class, $data['name'], ignoreId: $category->id);

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $old = $category->toArray();
        $category->update($data);
        AuditService::log('updated', $category, $old, $category->toArray());

        return redirect()->route('admin.categories.index')->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        abort_unless($request->user()->can('categories.manage'), 403);

        AuditService::log('deleted', $category, $category->toArray(), []);
        $category->delete();

        return back()->with('success', 'Categoría eliminada correctamente.');
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'slug.unique' => 'Ya existe una categoría con este slug.',
            'image.image' => 'El archivo debe ser una imagen.',
            'image.max' => 'La imagen no debe superar 4 MB.',
        ];
    }
}
