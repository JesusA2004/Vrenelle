<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AddressController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('account/Addresses', [
            'addresses' => $request->user()->addresses()->orderByDesc('is_default')->get(),
        ]);
    }

    protected function rules(): array
    {
        return [
            'label' => ['required', 'in:casa,oficina,otra'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'street' => ['required', 'string', 'max:255'],
            'ext_number' => ['required', 'string', 'max:20'],
            'int_number' => ['nullable', 'string', 'max:20'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:10'],
            'references' => ['nullable', 'string', 'max:500'],
            'is_default' => ['boolean'],
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules(), $this->messages());
        $user = $request->user();

        if ($data['is_default'] ?? false) {
            $user->addresses()->update(['is_default' => false]);
        }

        $user->addresses()->create([...$data, 'country' => 'MX']);

        return back()->with('success', 'Dirección guardada correctamente.');
    }

    public function update(Request $request, Address $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $data = $request->validate($this->rules(), $this->messages());

        if ($data['is_default'] ?? false) {
            $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($data);

        return back()->with('success', 'Dirección actualizada correctamente.');
    }

    public function destroy(Request $request, Address $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->id, 403);
        $address->delete();

        return back()->with('success', 'Dirección eliminada.');
    }

    protected function messages(): array
    {
        return [
            'full_name.required' => 'El nombre completo es obligatorio.',
            'phone.required' => 'El teléfono es obligatorio.',
            'street.required' => 'La calle es obligatoria.',
            'ext_number.required' => 'El número exterior es obligatorio.',
            'neighborhood.required' => 'La colonia es obligatoria.',
            'city.required' => 'El municipio o alcaldía es obligatorio.',
            'state.required' => 'El estado es obligatorio.',
            'postal_code.required' => 'El código postal es obligatorio.',
        ];
    }
}
