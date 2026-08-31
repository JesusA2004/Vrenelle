<?php

use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Services\CheckoutService;
use App\Services\InsufficientStockException;

function checkoutProduct(int $stock = 5, int $priceCents = 100000): Product
{
    $category = Category::create(['name' => 'Aretes', 'slug' => 'aretes-'.uniqid(), 'is_active' => true]);

    return Product::create([
        'category_id' => $category->id,
        'name' => 'Aretes de prueba',
        'slug' => 'aretes-prueba-'.uniqid(),
        'sku' => 'SKU-'.uniqid(),
        'price' => $priceCents,
        'inventory_mode' => 'stock',
        'stock' => $stock,
        'low_stock_threshold' => 2,
        'is_active' => true,
    ]);
}

function shippingAddress(): array
{
    return [
        'full_name' => 'Cliente de Prueba', 'phone' => '5555555555', 'street' => 'Calle Falsa',
        'ext_number' => '123', 'int_number' => null, 'neighborhood' => 'Centro', 'city' => 'CDMX',
        'state' => 'CDMX', 'postal_code' => '01000', 'references' => null, 'country' => 'MX',
    ];
}

test('placing an order decrements stock and clears the cart', function () {
    $product = checkoutProduct(stock: 5);
    $cart = Cart::create(['session_token' => 'tok-'.uniqid(), 'currency' => 'MXN']);
    $cart->items()->create(['product_id' => $product->id, 'quantity' => 2]);
    $shippingMethod = ShippingMethod::create(['name' => 'Estándar', 'price' => 5000, 'active' => true]);

    $order = app(CheckoutService::class)->placeOrder(
        cart: $cart,
        user: null,
        customerName: 'Cliente de Prueba',
        email: 'cliente@example.com',
        phone: '5555555555',
        shippingAddress: shippingAddress(),
        billingAddress: shippingAddress(),
        shippingMethod: $shippingMethod,
        couponCode: null,
        paymentMethod: 'manual',
        installmentMonths: null,
        idempotencyKey: 'idem-'.uniqid(),
    );

    expect($order->exists)->toBeTrue()
        ->and($order->items()->count())->toBe(1)
        ->and($order->total)->toBe(200000 + 5000);

    expect($product->fresh()->stock)->toBe(3);
    expect($cart->fresh()->items()->count())->toBe(0);
});

test('placing an order twice with the same idempotency key returns the same order', function () {
    $product = checkoutProduct(stock: 5);
    $cart = Cart::create(['session_token' => 'tok-'.uniqid(), 'currency' => 'MXN']);
    $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);
    $key = 'idem-'.uniqid();

    $service = app(CheckoutService::class);

    $first = $service->placeOrder(
        cart: $cart, user: null, customerName: 'Cliente', email: 'c@example.com', phone: '555',
        shippingAddress: shippingAddress(), billingAddress: shippingAddress(),
        shippingMethod: null, couponCode: null, paymentMethod: 'manual',
        installmentMonths: null, idempotencyKey: $key,
    );

    $second = $service->placeOrder(
        cart: $cart, user: null, customerName: 'Cliente', email: 'c@example.com', phone: '555',
        shippingAddress: shippingAddress(), billingAddress: shippingAddress(),
        shippingMethod: null, couponCode: null, paymentMethod: 'manual',
        installmentMonths: null, idempotencyKey: $key,
    );

    expect($second->id)->toBe($first->id);
    // Stock should only have been decremented once.
    expect($product->fresh()->stock)->toBe(4);
});

test('checkout refuses to oversell when cart quantity exceeds stock', function () {
    $product = checkoutProduct(stock: 1);
    $cart = Cart::create(['session_token' => 'tok-'.uniqid(), 'currency' => 'MXN']);
    $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);

    // Someone else buys the last piece first.
    $product->decrement('stock', 1);

    expect(fn () => app(CheckoutService::class)->placeOrder(
        cart: $cart, user: null, customerName: 'Cliente', email: 'c@example.com', phone: '555',
        shippingAddress: shippingAddress(), billingAddress: shippingAddress(),
        shippingMethod: null, couponCode: null, paymentMethod: 'manual',
        installmentMonths: null, idempotencyKey: 'idem-'.uniqid(),
    ))->toThrow(InsufficientStockException::class);

    // No order should have been created.
    expect(Order::count())->toBe(0);
});
