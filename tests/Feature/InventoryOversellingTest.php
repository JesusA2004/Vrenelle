<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Services\InsufficientStockException;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;

function makeProduct(array $overrides = []): Product
{
    $category = Category::create(['name' => 'Anillos', 'slug' => 'anillos-'.uniqid(), 'is_active' => true]);

    return Product::create(array_merge([
        'category_id' => $category->id,
        'name' => 'Anillo de prueba',
        'slug' => 'anillo-prueba-'.uniqid(),
        'sku' => 'SKU-'.uniqid(),
        'price' => 100000,
        'inventory_mode' => 'stock',
        'stock' => 3,
        'low_stock_threshold' => 2,
        'is_active' => true,
    ], $overrides));
}

test('inventory service decrements stock on sale', function () {
    $product = makeProduct(['stock' => 5]);
    $order = Order::create([
        'status' => 'pending', 'payment_status' => 'pending', 'fulfillment_status' => 'unfulfilled',
        'subtotal' => 0, 'total' => 0, 'customer_name' => 'Test', 'email' => 't@t.com', 'phone' => '123',
    ]);

    DB::transaction(function () use ($product, $order) {
        app(InventoryService::class)->decrementForSale($product, null, 2, $order);
    });

    expect($product->fresh()->stock)->toBe(3);
    expect($product->inventoryMovements()->count())->toBe(1);
});

test('inventory service refuses to oversell', function () {
    $product = makeProduct(['stock' => 1]);
    $order = Order::create([
        'status' => 'pending', 'payment_status' => 'pending', 'fulfillment_status' => 'unfulfilled',
        'subtotal' => 0, 'total' => 0, 'customer_name' => 'Test', 'email' => 't@t.com', 'phone' => '123',
    ]);

    expect(fn () => DB::transaction(function () use ($product, $order) {
        app(InventoryService::class)->decrementForSale($product, null, 2, $order);
    }))->toThrow(InsufficientStockException::class);

    // Stock must remain untouched — the whole transaction rolled back.
    expect($product->fresh()->stock)->toBe(1);
});

test('restocking returns inventory and logs the movement', function () {
    $product = makeProduct(['stock' => 0]);
    $order = Order::create([
        'status' => 'cancelled', 'payment_status' => 'pending', 'fulfillment_status' => 'unfulfilled',
        'subtotal' => 0, 'total' => 0, 'customer_name' => 'Test', 'email' => 't@t.com', 'phone' => '123',
    ]);

    DB::transaction(function () use ($product, $order) {
        app(InventoryService::class)->restock($product, null, 2, reference: $order);
    });

    expect($product->fresh()->stock)->toBe(2);
});

test('manual adjustment logs previous and new stock', function () {
    $product = makeProduct(['stock' => 10]);

    app(InventoryService::class)->adjust($product, null, 4, 'Conteo físico');

    expect($product->fresh()->stock)->toBe(4);
    $movement = $product->inventoryMovements()->latest('id')->first();
    expect($movement->previous_stock)->toBe(10)
        ->and($movement->new_stock)->toBe(4)
        ->and($movement->reason)->toBe('Conteo físico');
});
