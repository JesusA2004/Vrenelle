<?php

namespace App\Http\Controllers;

use App\Models\Collection as ProductCollection;
use App\Models\LegalPage;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = collect();

        $urls->push(['loc' => url('/'), 'changefreq' => 'daily', 'priority' => '1.0']);
        $urls->push(['loc' => url('/joyeria'), 'changefreq' => 'daily', 'priority' => '0.9']);
        $urls->push(['loc' => url('/colecciones'), 'changefreq' => 'weekly', 'priority' => '0.8']);
        $urls->push(['loc' => url('/contacto'), 'changefreq' => 'monthly', 'priority' => '0.3']);

        Product::active()->select('slug', 'updated_at')->each(function (Product $product) use ($urls) {
            $urls->push([
                'loc' => url("/producto/{$product->slug}"),
                'lastmod' => $product->updated_at->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ]);
        });

        ProductCollection::active()->select('slug', 'updated_at')->each(function (ProductCollection $collection) use ($urls) {
            $urls->push([
                'loc' => url("/colecciones/{$collection->slug}"),
                'lastmod' => $collection->updated_at->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ]);
        });

        LegalPage::select('slug', 'updated_at')->each(function (LegalPage $page) use ($urls) {
            $urls->push([
                'loc' => url("/legal/{$page->slug}"),
                'lastmod' => $page->updated_at->toAtomString(),
                'changefreq' => 'yearly',
                'priority' => '0.2',
            ]);
        });

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }
}
