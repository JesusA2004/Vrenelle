<?php

namespace App\Services;

use App\Enums\CouponType;
use App\Enums\PromotionType;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Server-side discount engine. Every validation here is authoritative —
 * the frontend only ever displays what this service decides.
 */
class DiscountService
{
    /**
     * Automatically-applied promotions (no code needed) matching the given
     * cart line items. Returns, per product id, the single best applicable
     * discount amount in cents for that line.
     *
     * @param  Collection<int, array{product: Product, quantity: int, line_total: int}>  $lines
     * @return array{promotions: Collection<int, Promotion>, discount: int, per_product: array<int, int>}
     */
    public function applyPromotions(Collection $lines): array
    {
        $activePromotions = Promotion::active()
            ->with(['categories', 'collections', 'products'])
            ->orderByDesc('priority')
            ->get();

        $perProductDiscount = [];
        $appliedPromotions = collect();
        $productAlreadyDiscounted = [];

        foreach ($activePromotions as $promotion) {
            if ($promotion->hasReachedUsageLimit()) {
                continue;
            }

            foreach ($lines as $line) {
                /** @var Product $product */
                $product = $line['product'];

                if (! $this->promotionMatchesProduct($promotion, $product)) {
                    continue;
                }

                // Non-stackable promotions skip products a previous (higher priority) promotion already discounted.
                if (! $promotion->stackable && isset($productAlreadyDiscounted[$product->id])) {
                    continue;
                }

                $lineDiscount = $this->calculateLineDiscount($promotion, $product, $line['line_total']);

                if ($lineDiscount <= 0) {
                    continue;
                }

                $perProductDiscount[$product->id] = ($perProductDiscount[$product->id] ?? 0) + $lineDiscount;
                $productAlreadyDiscounted[$product->id] = true;
                $appliedPromotions->push($promotion);
            }
        }

        return [
            'promotions' => $appliedPromotions->unique('id')->values(),
            'discount' => array_sum($perProductDiscount),
            'per_product' => $perProductDiscount,
        ];
    }

    protected function promotionMatchesProduct(Promotion $promotion, Product $product): bool
    {
        $hasScope = $promotion->categories->isNotEmpty() || $promotion->collections->isNotEmpty() || $promotion->products->isNotEmpty();

        if (! $hasScope) {
            return true; // store-wide promotion
        }

        if ($promotion->products->contains('id', $product->id)) {
            return true;
        }

        if ($product->category_id && $promotion->categories->contains('id', $product->category_id)) {
            return true;
        }

        if ($product->collection_id && $promotion->collections->contains('id', $product->collection_id)) {
            return true;
        }

        return false;
    }

    protected function calculateLineDiscount(Promotion $promotion, Product $product, int $lineTotal): int
    {
        $discount = match ($promotion->type) {
            PromotionType::Percentage, PromotionType::CategoryDiscount, PromotionType::CollectionDiscount => (int) round($lineTotal * ((float) $promotion->value / 100)),
            PromotionType::Fixed => (int) round($promotion->value * 100),
            PromotionType::SpecialPrice => max(0, $lineTotal - (int) round($promotion->value * 100)),
            PromotionType::FreeShipping => 0, // handled separately as shipping waiver
        };

        if ($promotion->maximum_discount !== null) {
            $discount = min($discount, $promotion->maximum_discount);
        }

        return min($discount, $lineTotal);
    }

    public function promotionsIncludeFreeShipping(Collection $promotions): bool
    {
        return $promotions->contains(fn (Promotion $p) => $p->type === PromotionType::FreeShipping);
    }

    /**
     * @throws CouponException
     */
    public function validateCoupon(string $code, ?User $user, int $subtotalCents): Coupon
    {
        $coupon = Coupon::query()->where('code', $code)->first();

        if (! $coupon) {
            throw new CouponException('Este código no es válido.');
        }

        if (! $coupon->active || ($coupon->starts_at && $coupon->starts_at->isFuture()) || ($coupon->expires_at && $coupon->expires_at->isPast())) {
            throw new CouponException('Este cupón ha expirado o aún no está disponible.');
        }

        if ($coupon->hasReachedUsageLimit()) {
            throw new CouponException('Este cupón alcanzó su límite de usos.');
        }

        if ($coupon->minimum_purchase !== null && $subtotalCents < $coupon->minimum_purchase) {
            throw new CouponException('Tu compra no alcanza el mínimo requerido para este cupón.');
        }

        if ($user) {
            if ($coupon->usage_per_customer !== null) {
                $used = CouponUsage::query()->where('coupon_id', $coupon->id)->where('user_id', $user->id)->count();

                if ($used >= $coupon->usage_per_customer) {
                    throw new CouponException('Ya has utilizado este cupón el máximo de veces permitido.');
                }
            }

            if ($coupon->first_purchase_only && Order::query()->where('user_id', $user->id)->whereIn('payment_status', ['paid'])->exists()) {
                throw new CouponException('Este cupón es exclusivo para tu primera compra.');
            }
        }

        return $coupon;
    }

    public function calculateCouponDiscount(Coupon $coupon, int $subtotalAfterPromotions): int
    {
        $discount = match ($coupon->type) {
            CouponType::Percentage => (int) round($subtotalAfterPromotions * ((float) $coupon->value / 100)),
            CouponType::Fixed => (int) round($coupon->value * 100),
            CouponType::FreeShipping => 0,
        };

        if ($coupon->maximum_discount !== null) {
            $discount = min($discount, $coupon->maximum_discount);
        }

        return min($discount, $subtotalAfterPromotions);
    }

    public function recordCouponUsage(Coupon $coupon, ?Order $order, ?User $user, int $discountAmount): void
    {
        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'order_id' => $order?->id,
            'user_id' => $user?->id,
            'discount_amount' => $discountAmount,
            'created_at' => now(),
        ]);

        $coupon->increment('used_count');
    }
}
