<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\CartService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;

class MergeGuestCartOnLogin
{
    public function __construct(protected CartService $cartService) {}

    public function handleLogin(Login $event): void
    {
        if ($event->user instanceof User) {
            $this->cartService->mergeGuestCartIntoUser($event->user);
            $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
        }
    }

    public function handleRegistered(Registered $event): void
    {
        if ($event->user instanceof User) {
            $this->cartService->mergeGuestCartIntoUser($event->user);
        }
    }
}
