<?php

use App\Http\Controllers\Account\AddressController;
use App\Http\Controllers\Account\DashboardController;
use App\Http\Controllers\Account\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('mi-cuenta')->name('account.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('/pedidos', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/pedidos/{orderNumber}', [OrderController::class, 'show'])->name('orders.show');

    Route::get('/direcciones', [AddressController::class, 'index'])->name('addresses.index');
    Route::post('/direcciones', [AddressController::class, 'store'])->name('addresses.store');
    Route::patch('/direcciones/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/direcciones/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
});
