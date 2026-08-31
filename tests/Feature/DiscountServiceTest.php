<?php

use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use App\Services\CouponException;
use App\Services\DiscountService;

test('a valid coupon within its rules passes validation', function () {
    $coupon = Coupon::create([
        'code' => 'VALIDO10', 'type' => 'percentage', 'value' => 10,
        'minimum_purchase' => 50000, 'active' => true,
    ]);

    $validated = app(DiscountService::class)->validateCoupon('VALIDO10', null, 100000);

    expect($validated->id)->toBe($coupon->id);
});

test('an expired coupon is rejected', function () {
    Coupon::create([
        'code' => 'VENCIDO', 'type' => 'percentage', 'value' => 10,
        'active' => true, 'expires_at' => now()->subDay(),
    ]);

    expect(fn () => app(DiscountService::class)->validateCoupon('VENCIDO', null, 100000))
        ->toThrow(CouponException::class);
});

test('a coupon below the minimum purchase is rejected', function () {
    Coupon::create([
        'code' => 'MIN500', 'type' => 'fixed', 'value' => 50,
        'minimum_purchase' => 500000, 'active' => true,
    ]);

    expect(fn () => app(DiscountService::class)->validateCoupon('MIN500', null, 100000))
        ->toThrow(CouponException::class);
});

test('a coupon that reached its usage limit is rejected', function () {
    Coupon::create([
        'code' => 'LIMITADO', 'type' => 'fixed', 'value' => 50,
        'active' => true, 'usage_limit' => 1, 'used_count' => 1,
    ]);

    expect(fn () => app(DiscountService::class)->validateCoupon('LIMITADO', null, 100000))
        ->toThrow(CouponException::class);
});

test('a first-purchase-only coupon is rejected for a customer with a paid order', function () {
    $user = User::factory()->create();
    $coupon = Coupon::create([
        'code' => 'BIENVENIDA', 'type' => 'percentage', 'value' => 10,
        'active' => true, 'first_purchase_only' => true,
    ]);

    Order::create([
        'user_id' => $user->id, 'status' => 'paid', 'payment_status' => 'paid', 'fulfillment_status' => 'unfulfilled',
        'subtotal' => 100000, 'total' => 100000, 'customer_name' => $user->name, 'email' => $user->email, 'phone' => '555',
    ]);

    expect(fn () => app(DiscountService::class)->validateCoupon($coupon->code, $user, 100000))
        ->toThrow(CouponException::class);
});

test('percentage coupon discount is capped by maximum_discount', function () {
    $coupon = Coupon::create([
        'code' => 'TOPADO', 'type' => 'percentage', 'value' => 50,
        'active' => true, 'maximum_discount' => 20000,
    ]);

    $discount = app(DiscountService::class)->calculateCouponDiscount($coupon, 100000);

    expect($discount)->toBe(20000);
});

test('a nonexistent coupon code is rejected', function () {
    expect(fn () => app(DiscountService::class)->validateCoupon('NOEXISTE', null, 100000))
        ->toThrow(CouponException::class);
});
