<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Services\AuditService;
use App\Support\SlugGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CollectionController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('collections.manage'), 403);

        $collections = Collection::withCount('products')
            ->when($request->filled('buscar'), fn ($q) => $q->where('name', 'like', '%'.$request->string('buscar').'%'))
            ->orderBy('sort_order')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/collections/Index', [
            'collections' => $collections,
            'filters' => $request->only('buscar'),
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()->can('collections.manage'), 403);

        return Inertia::render('admin/collections/Form', []);
    }

    protected function rules(?int $ignoreId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('collections', 'slug')->ignore($ignoreId)],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:5000'],
            'story' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'max:6144'],
            'mobile_image' => ['nullable', 'image', 'max:6144'],
            'banner' => ['nullable', 'image', 'max:6144'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'published_at' => ['nullable', 'date'],
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('collections.manage'), 403);

        $data = $request->validate($this->rules(), $this->messages());
        $data['slug'] = $data['slug'] ?? SlugGenerator::unique(Collection::class, $data['name']);

        foreach (['image', 'mobile_image', 'banner'] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('collections', 'public');
            }
        }

        $collection = Collection::create($data);
        AuditService::log('created', $collection, [], $collection->toArray());

        return redirect()->route('admin.collections.index')->with('success', 'Colección creada correctamente.');
    }

    public function edit(Request $request, Collection $collection): Response
    {
        abort_unless($request->user()->can('collections.manage'), 403);

        return Inertia::render('admin/collections/Form', ['collection' => $collection]);
    }

    public function update(Request $request, Collection $collection): RedirectResponse
    {
        abort_unless($request->user()->can('collections.manage'), 403);

        $data = $request->validate($this->rules($collection->id), $this->messages());
        $data['slug'] = $data['slug'] ?? SlugGenerator::unique(Collection::class, $data['name'], ignoreId: $collection->id);

        foreach (['image', 'mobile_image', 'banner'] as $field) {
            if ($request->hasFile($field)) {
                if ($collection->{$field}) {
                    Storage::disk('public')->delete($collection->{$field});
                }
                $data[$field] = $request->file($field)->store('collections', 'public');
            }
        }

        $old = $collection->toArray();
        $collection->update($data);
        AuditService::log('updated', $collection, $old, $collection->toArray());

        return redirect()->route('admin.collections.index')->with('success', 'Colección actualizada correctamente.');
    }

    public function destroy(Request $request, Collection $collection): RedirectResponse
    {
        abort_unless($request->user()->can('collections.manage'), 403);

        AuditService::log('deleted', $collection, $collection->toArray(), []);
        $collection->delete();

        return back()->with('success', 'Colección eliminada correctamente.');
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'slug.unique' => 'Ya existe una colección con este slug.',
        ];
    }
}
