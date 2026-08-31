<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ], [
            'email.required' => 'Ingresa un correo electrónico.',
            'email.email' => 'Ingresa un correo electrónico válido.',
        ]);

        NewsletterSubscriber::updateOrCreate(
            ['email' => $data['email']],
            ['status' => 'subscribed', 'subscribed_at' => now(), 'unsubscribed_at' => null],
        );

        return back()->with('success', 'Te has suscrito correctamente a nuestro boletín.');
    }
}
