<?php

namespace App\Http\Controllers\Store;

use App\Enums\BannerPosition;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCardResource;
use App\Models\Banner;
use App\Models\Collection as ProductCollection;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $hero = Banner::visible()->position(BannerPosition::Hero)->first();
        $story = Banner::visible()->position(BannerPosition::Story)->first();
        $special = Banner::visible()->position(BannerPosition::SpecialCollection)->first();

        $collections = ProductCollection::active()->featured()->orderBy('sort_order')->take(3)->get();

        $featured = Product::active()->featured()->with(['images', 'collection'])->latest('published_at')->take(8)->get();
        $newArrivals = Product::active()->newArrivals()->with(['images', 'collection'])->latest('published_at')->take(8)->get();
        $bestSellers = Product::active()
            ->withSum(['orderItems as sold_quantity' => fn ($q) => $q->whereHas('order', fn ($o) => $o->where('payment_status', 'paid'))], 'quantity')
            ->with(['images', 'collection'])
            ->orderByDesc('sold_quantity')
            ->take(8)
            ->get();

        return Inertia::render('store/Home', [
            'hero' => $hero ? $this->bannerToArray($hero) : null,
            'story' => $story ? $this->bannerToArray($story) : null,
            'special' => $special ? $this->bannerToArray($special) : null,
            'collections' => $collections->map(fn (ProductCollection $c) => [
                'name' => $c->name,
                'slug' => $c->slug,
                'shortDescription' => $c->short_description,
                'image' => $c->image ? Storage::disk('public')->url($c->image) : null,
            ]),
            'featured' => ProductCardResource::collection($featured),
            'newArrivals' => ProductCardResource::collection($newArrivals),
            'bestSellers' => ProductCardResource::collection($bestSellers),
        ]);
    }

    protected function bannerToArray(Banner $banner): array
    {
        return [
            'title' => $banner->title,
            'subtitle' => $banner->subtitle,
            'description' => $banner->description,
            'desktopMedia' => $banner->desktop_media ? Storage::disk('public')->url($banner->desktop_media) : null,
            'mobileMedia' => $banner->mobile_media ? Storage::disk('public')->url($banner->mobile_media) : null,
            'videoDesktop' => $banner->video_desktop ? Storage::disk('public')->url($banner->video_desktop) : null,
            'videoMobile' => $banner->video_mobile ? Storage::disk('public')->url($banner->video_mobile) : null,
            'ctaText' => $banner->cta_text,
            'ctaUrl' => $banner->cta_url,
            'contentPosition' => $banner->content_position,
            'textTheme' => $banner->text_theme,
            'overlay' => $banner->overlay,
        ];
    }
}
