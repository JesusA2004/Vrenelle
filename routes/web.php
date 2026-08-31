<?php

use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $user = request()->user();

        return $user->can('orders.view') || $user->can('products.view')
            ? redirect()->route('admin.dashboard')
            : redirect()->route('account.dashboard');
    })->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/store.php';
require __DIR__.'/account.php';
require __DIR__.'/admin.php';
require __DIR__.'/webhooks.php';
