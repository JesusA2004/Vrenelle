<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Events\OrderCreated;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function __construct(
        protected CartService $cartService,
        protected DiscountService $discounts,
        protected InventoryService $inventory,
    ) {}

    /**
     * Recalculates the full price breakdown for a cart, entirely from
     * server-trusted data. Used both to render the checkout summary and
     * as the authoritative source when the order is actually placed.
     *
     * @return array{subtotal:int, promotion_discount:int, coupon_discount:int, discount_total:int, shipping_total:int, tax_total:int, total:int, coupon:?Coupon, free_shipping:bool}
     */
    public function pricingSummary(Cart $cart, ?string $couponCode, ?ShippingMethod $shippingMethod, ?User $user): array
    {
        $summary = $this->cartService->summarize($cart);
        $lines = collect($summary['items'])->map(fn ($i) => [
            'product' => $i['product'],
            'quantity' => $i['quantity'],
            'line_total' => $i['line_total'],
        ]);

        $subtotal = $summary['subtotal'];

        $promoResult = $this->discounts->applyPromotions($lines);
        $promotionDiscount = $promoResult['discount'];
        $freeShippingFromPromotion = $this->discounts->promotionsIncludeFreeShipping($promoResult['promotions']);

        $coupon = null;
        $couponDiscount = 0;

        if ($couponCode) {
            $coupon = $this->discounts->validateCoupon($couponCode, $user, $subtotal - $promotionDiscount);
            $couponDiscount = $this->discounts->calculateCouponDiscount($coupon, $subtotal - $promotionDiscount);
        }

        $discountTotal = $promotionDiscount + $couponDiscount;
        $taxableBase = max(0, $subtotal - $discountTotal);

        $freeShipping = $freeShippingFromPromotion || ($coupon?->type?->value === 'free_shipping');
        $shippingTotal = $freeShipping ? 0 : ($shippingMethod?->priceFor($taxableBase) ?? 0);

        $pricesIncludeTax = Settings::pricesIncludeTax();
        $taxRate = Settings::taxRate();
        $taxTotal = $pricesIncludeTax ? 0 : (int) round($taxableBase * ($taxRate / 100));

        $total = $taxableBase + $shippingTotal + $taxTotal;

        return [
            'subtotal' => $subtotal,
            'promotion_discount' => $promotionDiscount,
            'coupon_discount' => $couponDiscount,
            'discount_total' => $discountTotal,
            'shipping_total' => $shippingTotal,
            'tax_total' => $taxTotal,
            'total' => $total,
            'coupon' => $coupon,
            'free_shipping' => $freeShipping,
        ];
    }

    /**
     * Creates the order and its snapshot line items, decrements inventory
     * atomically, and clears the cart — all inside a single transaction.
     * Supports idempotency via a client-supplied key so a double submit or
     * page refresh never creates two orders.
     *
     * @param  array{full_name?:string, phone?:string, street?:string, ext_number?:string, int_number?:string, neighborhood?:string, city?:string, state?:string, postal_code?:string, country?:string, references?:string}  $shippingAddress
     *
     * @throws InsufficientStockException
     */
    public function placeOrder(
        Cart $cart,
        ?User $user,
        string $customerName,
        string $email,
        string $phone,
        array $shippingAddress,
        array $billingAddress,
        ?ShippingMethod $shippingMethod,
        ?string $couponCode,
        string $paymentMethod,
        ?int $installmentMonths,
        ?string $idempotencyKey,
        ?string $notes = null,
    ): Order {
        if ($idempotencyKey) {
            $existing = Order::query()->where('idempotency_key', $idempotencyKey)->first();

            if ($existing) {
                return $existing;
            }
        }

        return DB::transaction(function () use (
            $cart, $user, $customerName, $email, $phone, $shippingAddress, $billingAddress,
            $shippingMethod, $couponCode, $paymentMethod, $installmentMonths, $idempotencyKey, $notes,
        ) {
            $pricing = $this->pricingSummary($cart, $couponCode, $shippingMethod, $user);
            $summary = $this->cartService->summarize($cart);

            if (empty($summary['items'])) {
                throw new \RuntimeException('El carrito está vacío.');
            }

            $order = Order::create([
                'user_id' => $user?->id,
                'shipping_method_id' => $shippingMethod?->id,
                'coupon_id' => $pricing['coupon']?->id,
                'status' => OrderStatus::Pending,
                'payment_status' => 'pending',
                'fulfillment_status' => 'unfulfilled',
                'subtotal' => $pricing['subtotal'],
                'discount_total' => $pricing['discount_total'],
                'shipping_total' => $pricing['shipping_total'],
                'tax_total' => $pricing['tax_total'],
                'total' => $pricing['total'],
                'currency' => $cart->currency,
                'customer_name' => $customerName,
                'email' => $email,
                'phone' => $phone,
                'billing_address' => $billingAddress,
                'shipping_address' => $shippingAddress,
                'payment_method' => $paymentMethod,
                'installment_months' => $installmentMonths,
                'notes' => $notes,
                'idempotency_key' => $idempotencyKey,
            ]);

            foreach ($summary['items'] as $line) {
                $product = $line['product'];
                $variant = $line['variant'];

                // Authoritative stock check + decrement, locked per row.
                $this->inventory->decrementForSale($product, $variant, $line['quantity'], $order);

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'product_name' => $product->name,
                    'sku' => $variant?->sku ?? $product->sku,
                    'variant_name' => $variant?->name,
                    'image_path' => $product->primaryImage()?->path,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'discount' => 0,
                    'total' => $line['line_total'],
                ]);
            }

            $order->statusHistory()->create([
                'user_id' => $user?->id,
                'from_status' => null,
                'to_status' => OrderStatus::Pending->value,
                'comment' => 'Pedido creado.',
                'created_at' => now(),
            ]);

            if ($pricing['coupon']) {
                $this->discounts->recordCouponUsage($pricing['coupon'], $order, $user, $pricing['coupon_discount']);
            }

            $this->cartService->clear($cart);

            OrderCreated::dispatch($order);

            return $order;
        });
    }
}
