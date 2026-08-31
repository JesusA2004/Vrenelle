<?php

use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// CSRF verification is excluded for this prefix in bootstrap/app.php —
// these endpoints are called by the payment providers' servers, never by a
// browser holding a session/CSRF token.
Route::middleware(['throttle:60,1'])->prefix('webhooks')->group(function () {
    Route::post('/stripe', [WebhookController::class, 'stripe'])->name('webhooks.stripe');
    Route::post('/paypal', [WebhookController::class, 'paypal'])->name('webhooks.paypal');
    Route::post('/mercadopago', [WebhookController::class, 'mercadopago'])->name('webhooks.mercadopago');
});
