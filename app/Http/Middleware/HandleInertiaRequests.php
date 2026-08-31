<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\Collection as ProductCollection;
use App\Services\CartService;
use App\Support\Money;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $cartService = app(CartService::class);
        $cart = $cartService->peek($user);
        $cartSummary = $cart ? $cartService->summarize($cart) : ['items' => [], 'subtotal' => 0];

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
                'roles' => $user?->getRoleNames() ?? [],
                'permissions' => $user?->getAllPermissions()->pluck('name') ?? [],
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'cart' => [
                'count' => collect($cartSummary['items'])->sum('quantity'),
                'subtotal' => Money::format($cartSummary['subtotal']),
                'items' => collect($cartSummary['items'])->map(fn ($line) => [
                    'id' => $line['id'],
                    'product' => [
                        'name' => $line['product']->name,
                        'slug' => $line['product']->slug,
                        'image' => $line['product']->images->first() ? Storage::disk('public')->url($line['product']->images->first()->path) : null,
                    ],
                    'variant' => $line['variant'] ? ['id' => $line['variant']->id, 'name' => $line['variant']->name] : null,
                    'quantity' => $line['quantity'],
                    'unitPrice' => Money::format($line['unit_price']),
                    'lineTotal' => Money::format($line['line_total']),
                ])->values(),
            ],
            'wishlist' => [
                'productIds' => $user ? $user->wishlist?->items()->pluck('product_id') ?? [] : [],
            ],
            'store' => [
                'name' => Settings::storeName(),
                'tagline' => Settings::get('store_tagline'),
                'logo' => Settings::get('logo') ? Storage::disk('public')->url(Settings::get('logo')) : null,
                'logoDark' => Settings::get('logo_dark') ? Storage::disk('public')->url(Settings::get('logo_dark')) : null,
                'whatsapp' => Settings::whatsappNumber(),
                'phone' => Settings::get('contact_phone'),
                'email' => Settings::get('contact_email'),
                'address' => Settings::get('contact_address'),
                'currency' => Settings::currency(),
                'instagram' => Settings::get('instagram_url'),
                'facebook' => Settings::get('facebook_url'),
                'tiktok' => Settings::get('tiktok_url'),
                'pinterest' => Settings::get('pinterest_url'),
            ],
            'nav' => Cache::remember('nav.menu', now()->addMinutes(10), function () {
                return [
                    'categories' => Category::active()->root()->orderBy('sort_order')->get()
                        ->map(fn (Category $c) => [
                            'name' => $c->name,
                            'slug' => $c->slug,
                            'image' => $c->image ? Storage::disk('public')->url($c->image) : null,
                        ]),
                    'collections' => ProductCollection::active()->featured()->orderBy('sort_order')->take(4)->get()
                        ->map(fn (ProductCollection $c) => [
                            'name' => $c->name,
                            'slug' => $c->slug,
                            'image' => $c->image ? Storage::disk('public')->url($c->image) : null,
                        ]),
                ];
            }),
            'flash' => [
                'toast' => fn () => $request->session()->get('toast')
                    ?? ($request->session()->get('success') ? ['type' => 'success', 'message' => $request->session()->get('success')] : null)
                    ?? ($request->session()->get('error') ? ['type' => 'error', 'message' => $request->session()->get('error')] : null),
                'cartError' => fn () => $request->session()->get('cart_error'),
            ],
        ];
    }
}
