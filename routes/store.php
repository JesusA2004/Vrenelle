<?php

use App\Http\Controllers\Store\CartController;
use App\Http\Controllers\Store\CatalogController;
use App\Http\Controllers\Store\CheckoutController;
use App\Http\Controllers\Store\CollectionController;
use App\Http\Controllers\Store\ContactController;
use App\Http\Controllers\Store\HomeController;
use App\Http\Controllers\Store\LegalPageController;
use App\Http\Controllers\Store\NewsletterController;
use App\Http\Controllers\Store\OrderConfirmationController;
use App\Http\Controllers\Store\ProductController;
use App\Http\Controllers\Store\ProductInquiryController;
use App\Http\Controllers\Store\SearchController;
use App\Http\Controllers\Store\WhatsAppLinkController;
use App\Http\Controllers\Store\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/joyeria', [CatalogController::class, 'index'])->name('catalog');
Route::get('/producto/{product:slug}', [ProductController::class, 'show'])->name('product.show');

Route::get('/colecciones', [CollectionController::class, 'index'])->name('collections');
Route::get('/colecciones/{collection:slug}', [CollectionController::class, 'show'])->name('collections.show');

Route::get('/buscar', [SearchController::class, 'search'])->name('search');

Route::post('/carrito/agregar', [CartController::class, 'add'])->name('cart.add');
Route::patch('/carrito/item/{item}', [CartController::class, 'updateQuantity'])->name('cart.update');
Route::delete('/carrito/item/{item}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/carrito/cupon', [CartController::class, 'applyCoupon'])->name('cart.coupon.apply');
Route::delete('/carrito/cupon', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');

Route::post('/whatsapp/enlace', [WhatsAppLinkController::class, 'generate'])->name('whatsapp.link');
Route::post('/solicitudes-producto', [ProductInquiryController::class, 'store'])->name('product-inquiries.store');

Route::middleware('auth')->group(function () {
    Route::get('/favoritos', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/favoritos/alternar', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
});

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'placeOrder'])->name('checkout.place');
Route::get('/pedido/confirmado/{orderNumber}', [OrderConfirmationController::class, 'show'])->name('order.confirmation');

Route::post('/newsletter', [NewsletterController::class, 'store'])->name('newsletter.store');

Route::get('/contacto', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contacto', [ContactController::class, 'store'])->name('contact.store');

Route::get('/legal/{legalPage:slug}', [LegalPageController::class, 'show'])->name('legal.show');
