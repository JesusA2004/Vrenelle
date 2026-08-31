<?php

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Database\QueryException;

function statusTestOrder(Product $product, int $quantity = 1): Order
{
    $order = Order::create([
        'status' => 'pending', 'payment_status' => 'pending', 'fulfillment_status' => 'unfulfilled',
        'subtotal' => $product->price * $quantity, 'total' => $product->price * $quantity,
        'customer_name' => 'Cliente', 'email' => 'c@example.com', 'phone' => '555',
    ]);

    $order->items()->create([
        'product_id' => $product->id, 'product_name' => $product->name, 'sku' => $product->sku,
        'quantity' => $quantity, 'unit_price' => $product->price, 'total' => $product->price * $quantity,
    ]);

    return $order;
}

test('marking an order as paid updates status and payment_status once', function () {
    $category = Category::create(['name' => 'Anillos', 'slug' => 'anillos-'.uniqid(), 'is_active' => true]);
    $product = Product::create([
        'category_id' => $category->id, 'name' => 'Anillo', 'slug' => 'anillo-'.uniqid(), 'sku' => 'SKU-'.uniqid(),
        'price' => 100000, 'inventory_mode' => 'stock', 'stock' => 5, 'is_active' => true,
    ]);
    $order = statusTestOrder($product);

    app(OrderService::class)->markPaid($order);

    expect($order->fresh()->payment_status->value)->toBe('paid')
        ->and($order->fresh()->status)->toBe(OrderStatus::Paid);
});

test('cancelling an order restocks its items exactly once', function () {
    $category = Category::create(['name' => 'Collares', 'slug' => 'collares-'.uniqid(), 'is_active' => true]);
    $product = Product::create([
        'category_id' => $category->id, 'name' => 'Collar', 'slug' => 'collar-'.uniqid(), 'sku' => 'SKU-'.uniqid(),
        'price' => 100000, 'inventory_mode' => 'stock', 'stock' => 3, 'is_active' => true,
    ]);
    $order = statusTestOrder($product, quantity: 2);

    $service = app(OrderService::class);
    $service->changeStatus($order, OrderStatus::Cancelled);
    $service->changeStatus($order->fresh(), OrderStatus::Cancelled); // idempotent no-op, same status

    expect($product->fresh()->stock)->toBe(5); // 3 + 2, restocked only once
});

test('payment transactions are unique per provider and provider transaction id', function () {
    $category = Category::create(['name' => 'Pulseras', 'slug' => 'pulseras-'.uniqid(), 'is_active' => true]);
    $product = Product::create([
        'category_id' => $category->id, 'name' => 'Pulsera', 'slug' => 'pulsera-'.uniqid(), 'sku' => 'SKU-'.uniqid(),
        'price' => 100000, 'inventory_mode' => 'stock', 'stock' => 5, 'is_active' => true,
    ]);
    $order = statusTestOrder($product);

    PaymentTransaction::create([
        'order_id' => $order->id, 'provider' => 'stripe', 'provider_transaction_id' => 'evt_123',
        'status' => 'succeeded', 'amount' => 100000, 'currency' => 'MXN',
    ]);

    expect(fn () => PaymentTransaction::create([
        'order_id' => $order->id, 'provider' => 'stripe', 'provider_transaction_id' => 'evt_123',
        'status' => 'succeeded', 'amount' => 100000, 'currency' => 'MXN',
    ]))->toThrow(QueryException::class);
});
