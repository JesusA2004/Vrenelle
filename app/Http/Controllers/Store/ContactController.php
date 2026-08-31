<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('store/Contact');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'reason' => ['required', 'in:producto,pedido,disponibilidad,garantia,otro'],
            'message' => ['required', 'string', 'max:2000'],
        ], [
            'name.required' => 'Tu nombre es obligatorio.',
            'email.required' => 'Tu correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'reason.required' => 'Selecciona un motivo.',
            'message.required' => 'Escribe tu mensaje.',
        ]);

        ContactMessage::create($data);

        return back()->with('success', 'Hemos recibido tu mensaje. Te contactaremos pronto.');
    }
}
