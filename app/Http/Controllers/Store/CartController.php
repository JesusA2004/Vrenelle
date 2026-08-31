<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\CouponException;
use App\Services\DiscountService;
use App\Services\InsufficientStockException;
use App\Support\WhatsAppHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(protected CartService $cartService, protected DiscountService $discounts) {}

    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ], [
            'product_id.required' => 'El producto es obligatorio.',
            'product_id.exists' => 'El producto seleccionado no existe.',
            'quantity.required' => 'Indica una cantidad.',
            'quantity.min' => 'La cantidad mínima es 1.',
        ]);

        $product = Product::findOrFail($data['product_id']);
        $variant = isset($data['product_variant_id']) ? ProductVariant::find($data['product_variant_id']) : null;

        try {
            $cart = $this->cartService->current($request->user());
            $this->cartService->addItem($cart, $product, $variant, $data['quantity']);

            return back()->with('success', 'Pieza añadida a tu selección.');
        } catch (InsufficientStockException $e) {
            return $this->stockErrorRedirect($product, $variant, $data['quantity'], $e);
        }
    }

    public function updateQuantity(Request $request, CartItem $item): RedirectResponse
    {
        $this->authorizeCartItem($request, $item);

        $data = $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:20']]);

        try {
            $this->cartService->updateQuantity($item, $data['quantity']);

            return back();
        } catch (InsufficientStockException $e) {
            return $this->stockErrorRedirect($item->product, $item->variant, $data['quantity'], $e);
        }
    }

    public function remove(Request $request, CartItem $item): RedirectResponse
    {
        $this->authorizeCartItem($request, $item);
        $this->cartService->removeItem($item);

        return back()->with('success', 'Pieza eliminada de tu selección.');
    }

    public function applyCoupon(Request $request): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:50']]);

        $cart = $this->cartService->current($request->user());
        $summary = $this->cartService->summarize($cart);

        try {
            $coupon = $this->discounts->validateCoupon($data['code'], $request->user(), $summary['subtotal']);
            $cart->update(['coupon_code' => $coupon->code]);

            return back()->with('success', 'Cupón aplicado correctamente.');
        } catch (CouponException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function removeCoupon(Request $request): RedirectResponse
    {
        $cart = $this->cartService->current($request->user());
        $cart->update(['coupon_code' => null]);

        return back();
    }

    protected function authorizeCartItem(Request $request, CartItem $item): void
    {
        $cart = $this->cartService->current($request->user());
        abort_unless($item->cart_id === $cart->id, 403);
    }

    protected function stockErrorRedirect(Product $product, ?ProductVariant $variant, int $requested, InsufficientStockException $e): RedirectResponse
    {
        $url = url("/producto/{$product->slug}");

        return back()->with('cart_error', [
            'message' => $e->getMessage(),
            'productName' => $product->name,
            'whatsappUrl' => WhatsAppHelper::forExceededQuantity($product, $variant, $requested, $url),
        ]);
    }
}
