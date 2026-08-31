<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CollectionController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\LegalPageController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentInstallmentRuleController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ShippingMethodController;
use App\Http\Controllers\Admin\StoneController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Middleware\EnsureCanAccessAdmin;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', EnsureCanAccessAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::resource('productos', ProductController::class)
        ->parameters(['productos' => 'product'])
        ->names('products')
        ->except(['show']);

    Route::post('productos/{product}/imagenes/{image}/principal', [ProductImageController::class, 'setPrimary'])->name('products.images.primary');
    Route::post('productos/{product}/imagenes/orden', [ProductImageController::class, 'reorder'])->name('products.images.reorder');
    Route::delete('productos/{product}/imagenes/{image}', [ProductImageController::class, 'destroy'])->name('products.images.destroy');

    Route::post('productos/{product}/variantes', [ProductVariantController::class, 'store'])->name('products.variants.store');
    Route::patch('productos/{product}/variantes/{variant}', [ProductVariantController::class, 'update'])->name('products.variants.update');
    Route::delete('productos/{product}/variantes/{variant}', [ProductVariantController::class, 'destroy'])->name('products.variants.destroy');

    Route::resource('categorias', CategoryController::class)->parameters(['categorias' => 'category'])->names('categories')->except(['show']);
    Route::resource('colecciones', CollectionController::class)->parameters(['colecciones' => 'collection'])->names('collections')->except(['show']);

    Route::get('materiales', [MaterialController::class, 'index'])->name('materials.index');
    Route::post('materiales', [MaterialController::class, 'store'])->name('materials.store');
    Route::patch('materiales/{material}', [MaterialController::class, 'update'])->name('materials.update');
    Route::delete('materiales/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');

    Route::get('piedras', [StoneController::class, 'index'])->name('stones.index');
    Route::post('piedras', [StoneController::class, 'store'])->name('stones.store');
    Route::patch('piedras/{stone}', [StoneController::class, 'update'])->name('stones.update');
    Route::delete('piedras/{stone}', [StoneController::class, 'destroy'])->name('stones.destroy');

    Route::get('inventario', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('inventario/ajustar', [InventoryController::class, 'adjust'])->name('inventory.adjust');

    Route::get('pedidos', [OrderController::class, 'index'])->name('orders.index');
    Route::get('pedidos/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('pedidos/{order}/estado', [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::patch('pedidos/{order}/envio', [OrderController::class, 'updateTracking'])->name('orders.tracking');
    Route::post('pedidos/{order}/cancelar', [OrderController::class, 'cancel'])->name('orders.cancel');

    Route::get('clientes', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('clientes/{customer}', [CustomerController::class, 'show'])->name('customers.show');

    Route::resource('promociones', PromotionController::class)->parameters(['promociones' => 'promotion'])->names('promotions')->except(['show']);
    Route::resource('cupones', CouponController::class)->parameters(['cupones' => 'coupon'])->names('coupons')->except(['show']);
    Route::resource('banners', BannerController::class)->names('banners')->except(['show']);

    Route::get('envios', [ShippingMethodController::class, 'index'])->name('shipping.index');
    Route::post('envios', [ShippingMethodController::class, 'store'])->name('shipping.store');
    Route::patch('envios/{shippingMethod}', [ShippingMethodController::class, 'update'])->name('shipping.update');
    Route::delete('envios/{shippingMethod}', [ShippingMethodController::class, 'destroy'])->name('shipping.destroy');

    Route::get('reportes', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reportes/exportar', [ReportController::class, 'exportCsv'])->name('reports.export');

    Route::resource('usuarios', UserController::class)->parameters(['usuarios' => 'user'])->names('users')->except(['show']);

    Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
    Route::patch('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    Route::get('configuracion', [SettingController::class, 'edit'])->name('settings.edit');
    Route::post('configuracion', [SettingController::class, 'update'])->name('settings.update');

    Route::get('paginas-legales', [LegalPageController::class, 'index'])->name('legal.index');
    Route::get('paginas-legales/{legalPage}/editar', [LegalPageController::class, 'edit'])->name('legal.edit');
    Route::patch('paginas-legales/{legalPage}', [LegalPageController::class, 'update'])->name('legal.update');

    Route::get('msi', [PaymentInstallmentRuleController::class, 'index'])->name('installments.index');
    Route::post('msi', [PaymentInstallmentRuleController::class, 'store'])->name('installments.store');
    Route::patch('msi/{paymentInstallmentRule}', [PaymentInstallmentRuleController::class, 'update'])->name('installments.update');
    Route::delete('msi/{paymentInstallmentRule}', [PaymentInstallmentRuleController::class, 'destroy'])->name('installments.destroy');

    Route::get('auditoria', [AuditLogController::class, 'index'])->name('audit.index');
});
