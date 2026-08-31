<?php

use App\Models\Category;
use App\Models\Product;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Regression coverage for a real bug: wrapping a single JsonResource or a
 * paginated ResourceCollection directly as an Inertia prop silently adds a
 * "data"/"meta" envelope (from Laravel's resource response pipeline) that
 * the Vue pages don't expect, breaking pagination counts and crashing the
 * product page ("variants is not iterable"). These assert the flat shape
 * the frontend actually relies on.
 */
function shapeTestProduct(array $overrides = []): Product
{
    $category = Category::create(['name' => 'Anillos', 'slug' => 'anillos-'.uniqid(), 'is_active' => true]);

    $product = Product::create(array_merge([
        'category_id' => $category->id,
        'name' => 'Anillo de prueba',
        'slug' => 'anillo-prueba-'.uniqid(),
        'sku' => 'SKU-'.uniqid(),
        'price' => 100000,
        'inventory_mode' => 'stock',
        'stock' => 5,
        'is_active' => true,
    ], $overrides));

    $product->variants()->create([
        'sku' => $product->sku.'-T7',
        'name' => 'Talla 7',
        'stock' => 3,
        'is_active' => true,
    ]);

    return $product;
}

test('the catalog page exposes a flat paginator, not a nested resource envelope', function () {
    shapeTestProduct();
    shapeTestProduct();

    $this->get('/joyeria')
        ->assertInertia(fn (Assert $page) => $page
            ->component('store/Catalog')
            ->has('products.data', 2)
            ->where('products.total', 2)
            ->has('products.links')
        );
});

test('the product page exposes flat product fields, including an iterable variants array', function () {
    $product = shapeTestProduct();

    $this->get("/producto/{$product->slug}")
        ->assertInertia(fn (Assert $page) => $page
            ->component('store/ProductShow')
            ->where('product.slug', $product->slug)
            ->has('product.variants', 1)
            ->has('product.images')
        );
});
