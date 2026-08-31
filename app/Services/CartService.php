<?php

namespace App\Services;

use App\Enums\ProductInventoryMode;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartService
{
    protected const SESSION_KEY = 'cart_token';

    public function __construct(protected InventoryService $inventory) {}

    /**
     * Resolve (and create if needed) the cart for the current visitor:
     * the authenticated user's cart, or a guest cart tracked by a token
     * stored in their session.
     */
    public function current(?User $user): Cart
    {
        if ($user) {
            return Cart::query()->firstOrCreate(['user_id' => $user->id], ['currency' => 'MXN']);
        }

        $token = session(self::SESSION_KEY);

        if ($token) {
            $cart = Cart::query()->where('session_token', $token)->whereNull('user_id')->first();

            if ($cart) {
                return $cart;
            }
        }

        $token = (string) Str::uuid();
        session([self::SESSION_KEY => $token]);

        return Cart::create(['session_token' => $token, 'currency' => 'MXN']);
    }

    /**
     * Looks up the current visitor's cart without creating one — safe to
     * call on every request (e.g. to render the header's cart badge)
     * without littering the carts table with a row per anonymous visitor.
     */
    public function peek(?User $user): ?Cart
    {
        if ($user) {
            return Cart::query()->where('user_id', $user->id)->first();
        }

        $token = session(self::SESSION_KEY);

        return $token ? Cart::query()->where('session_token', $token)->whereNull('user_id')->first() : null;
    }

    /**
     * Called right after login: fold the guest cart's items into the
     * user's own cart, then discard the guest cart.
     */
    public function mergeGuestCartIntoUser(User $user): void
    {
        $token = session(self::SESSION_KEY);

        if (! $token) {
            return;
        }

        $guestCart = Cart::query()->where('session_token', $token)->whereNull('user_id')->first();

        if (! $guestCart) {
            return;
        }

        $userCart = Cart::query()->firstOrCreate(['user_id' => $user->id], ['currency' => 'MXN']);

        foreach ($guestCart->items as $item) {
            $existing = $userCart->items()
                ->where('product_id', $item->product_id)
                ->where('product_variant_id', $item->product_variant_id)
                ->first();

            if ($existing) {
                $max = $this->inventory->availableQuantity($item->product, $item->variant);
                $existing->update(['quantity' => min($existing->quantity + $item->quantity, max(1, $max))]);
            } else {
                $item->update(['cart_id' => $userCart->id]);
            }
        }

        $guestCart->delete();
        session()->forget(self::SESSION_KEY);
    }

    /**
     * @throws InsufficientStockException
     */
    public function addItem(Cart $cart, Product $product, ?ProductVariant $variant, int $quantity): CartItem
    {
        $this->assertQuantityAvailable($product, $variant, $quantity);

        return DB::transaction(function () use ($cart, $product, $variant, $quantity) {
            $item = $cart->items()
                ->where('product_id', $product->id)
                ->where('product_variant_id', $variant?->id)
                ->first();

            $newQuantity = ($item?->quantity ?? 0) + $quantity;

            $this->assertQuantityAvailable($product, $variant, $newQuantity);

            if ($item) {
                $item->update(['quantity' => $newQuantity]);

                return $item->fresh();
            }

            return $cart->items()->create([
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'quantity' => $quantity,
            ]);
        });
    }

    /**
     * @throws InsufficientStockException
     */
    public function updateQuantity(CartItem $item, int $quantity): CartItem
    {
        if ($quantity < 1) {
            $item->delete();

            return $item;
        }

        $this->assertQuantityAvailable($item->product, $item->variant, $quantity);
        $item->update(['quantity' => $quantity]);

        return $item->fresh();
    }

    public function removeItem(CartItem $item): void
    {
        $item->delete();
    }

    public function clear(Cart $cart): void
    {
        $cart->items()->delete();
        $cart->update(['coupon_code' => null]);
    }

    /**
     * Recalculates line totals purely from server-trusted data (current
     * product/variant prices) — never from anything the client sent.
     *
     * @return array{items: array<int, array<string, mixed>>, subtotal: int}
     */
    public function summarize(Cart $cart): array
    {
        $cart->load(['items.product.images', 'items.variant']);

        $items = $cart->items->map(function (CartItem $item) {
            $unitPrice = $item->unitPrice();

            return [
                'id' => $item->id,
                'product' => $item->product,
                'variant' => $item->variant,
                'quantity' => $item->quantity,
                'unit_price' => $unitPrice,
                'line_total' => $unitPrice * $item->quantity,
                'available' => $this->inventory->availableQuantity($item->product, $item->variant),
            ];
        });

        return [
            'items' => $items->all(),
            'subtotal' => (int) $items->sum('line_total'),
        ];
    }

    /**
     * @throws InsufficientStockException
     */
    protected function assertQuantityAvailable(Product $product, ?ProductVariant $variant, int $quantity): void
    {
        if ($product->inventory_mode !== ProductInventoryMode::Stock) {
            return; // made_to_order / inquiry_only are not stock-limited in the cart
        }

        $available = $this->inventory->availableQuantity($product, $variant);

        if ($quantity > $available) {
            throw new InsufficientStockException($product, $variant, $quantity, $available);
        }
    }
}
