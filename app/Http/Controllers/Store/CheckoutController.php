<?php

namespace App\Http\Controllers\Store;

use App\Enums\PaymentProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\PlaceOrderRequest;
use App\Models\ShippingMethod;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\CouponException;
use App\Services\InsufficientStockException;
use App\Services\PaymentService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected CheckoutService $checkoutService,
        protected PaymentService $paymentService,
    ) {}

    public function show(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        $cart = $this->cartService->current($user);
        $summary = $this->cartService->summarize($cart);

        if (empty($summary['items'])) {
            return redirect('/joyeria')->with('error', 'Tu selección está vacía. Descubre nuestras colecciones para comenzar.');
        }

        $shippingMethods = ShippingMethod::active()->get();
        $defaultShippingMethod = $shippingMethods->first();

        $couponCode = $request->query('coupon') ?? $cart->coupon_code;
        $pricing = null;

        try {
            $pricing = $this->checkoutService->pricingSummary($cart, $couponCode, $defaultShippingMethod, $user);
        } catch (CouponException) {
            $pricing = $this->checkoutService->pricingSummary($cart, null, $defaultShippingMethod, $user);
            $couponCode = null;
        }

        $providers = collect(PaymentProvider::cases())->map(fn (PaymentProvider $p) => [
            'value' => $p->value,
            'label' => $p->label(),
            'available' => $this->paymentService->gateway($p)->isConfigured(),
            'installments' => $this->paymentService->availableInstallments($p, $pricing['total'])->map(fn ($rule) => [
                'months' => $rule->months,
                'feePercentage' => (float) $rule->customer_fee_percentage,
            ]),
        ]);

        return Inertia::render('store/Checkout', [
            'items' => collect($summary['items'])->map(fn ($line) => [
                'id' => $line['id'],
                'productName' => $line['product']->name,
                'variantName' => $line['variant']?->name,
                'image' => $line['product']->images->first() ? Storage::disk('public')->url($line['product']->images->first()->path) : null,
                'quantity' => $line['quantity'],
                'lineTotal' => Money::format($line['line_total']),
            ]),
            'pricing' => [
                'subtotal' => Money::format($pricing['subtotal']),
                'discountTotal' => Money::format($pricing['discount_total']),
                'shippingTotal' => Money::format($pricing['shipping_total']),
                'taxTotal' => Money::format($pricing['tax_total']),
                'total' => Money::format($pricing['total']),
                'totalCents' => $pricing['total'],
                'discountTotalCents' => $pricing['discount_total'],
                'taxTotalCents' => $pricing['tax_total'],
                'couponCode' => $couponCode,
                'freeShipping' => $pricing['free_shipping'],
            ],
            'shippingMethods' => $shippingMethods->map(fn (ShippingMethod $m) => [
                'id' => $m->id,
                'name' => $m->name,
                'description' => $m->description,
                'estimatedDays' => $m->estimated_days,
                'price' => Money::format($m->priceFor($pricing['subtotal'] - $pricing['discount_total'])),
            ]),
            'paymentProviders' => $providers,
            'addresses' => $user ? $user->addresses()->orderByDesc('is_default')->get() : [],
            'defaultAddress' => $user?->defaultAddress,
            'idempotencyKey' => (string) Str::uuid(),
        ]);
    }

    public function placeOrder(PlaceOrderRequest $request): RedirectResponse
    {
        $user = $request->user();
        $cart = $this->cartService->current($user);
        $data = $request->validated();

        $shippingMethod = ShippingMethod::findOrFail($data['shipping_method_id']);
        $billing = ($data['billing_same_as_shipping'] ?? true) ? $data['shipping'] : $data['billing'];

        try {
            $order = $this->checkoutService->placeOrder(
                cart: $cart,
                user: $user,
                customerName: $data['name'],
                email: $data['email'],
                phone: $data['phone'],
                shippingAddress: $data['shipping'],
                billingAddress: $billing,
                shippingMethod: $shippingMethod,
                couponCode: $data['coupon_code'] ?? $cart->coupon_code,
                paymentMethod: $data['payment_method'],
                installmentMonths: $data['installment_months'] ?? null,
                idempotencyKey: $request->header('X-Idempotency-Key') ?? $data['idempotency_key'] ?? null,
                notes: $data['notes'] ?? null,
            );
        } catch (InsufficientStockException $e) {
            return back()->with('cart_error', [
                'message' => $e->getMessage(),
                'productName' => $e->product->name,
                'whatsappUrl' => null,
            ]);
        } catch (CouponException $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($user && ($data['save_address'] ?? false)) {
            $user->addresses()->create([
                'label' => 'casa',
                ...$data['shipping'],
                'is_default' => $user->addresses()->count() === 0,
            ]);
        }

        // Grant this browser session access to the confirmation page for a
        // guest order, without making order numbers guessable/scrapeable.
        $request->session()->push('accessible_orders', $order->id);

        if ($data['payment_method'] === 'manual') {
            return redirect("/pedido/confirmado/{$order->order_number}");
        }

        $provider = PaymentProvider::from($data['payment_method']);

        try {
            $checkoutSession = $this->paymentService->startCheckout($order, $provider);
        } catch (\Throwable $e) {
            report($e);

            return redirect("/pedido/confirmado/{$order->order_number}")
                ->with('error', 'No fue posible iniciar el pago en línea. Nuestro equipo se pondrá en contacto contigo para completar tu pedido.');
        }

        return Inertia::location($checkoutSession->redirectUrl ?? "/pedido/confirmado/{$order->order_number}");
    }
}
