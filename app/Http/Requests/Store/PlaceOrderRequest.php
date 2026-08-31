<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],

            'shipping.full_name' => ['required', 'string', 'max:255'],
            'shipping.phone' => ['required', 'string', 'max:30'],
            'shipping.street' => ['required', 'string', 'max:255'],
            'shipping.ext_number' => ['required', 'string', 'max:20'],
            'shipping.int_number' => ['nullable', 'string', 'max:20'],
            'shipping.neighborhood' => ['required', 'string', 'max:255'],
            'shipping.city' => ['required', 'string', 'max:255'],
            'shipping.state' => ['required', 'string', 'max:255'],
            'shipping.postal_code' => ['required', 'string', 'max:10'],
            'shipping.references' => ['nullable', 'string', 'max:500'],
            'shipping.country' => ['nullable', 'string', 'size:2'],

            'billing_same_as_shipping' => ['boolean'],
            'billing.full_name' => ['required_if:billing_same_as_shipping,false', 'nullable', 'string', 'max:255'],
            'billing.phone' => ['required_if:billing_same_as_shipping,false', 'nullable', 'string', 'max:30'],
            'billing.street' => ['required_if:billing_same_as_shipping,false', 'nullable', 'string', 'max:255'],
            'billing.ext_number' => ['required_if:billing_same_as_shipping,false', 'nullable', 'string', 'max:20'],
            'billing.int_number' => ['nullable', 'string', 'max:20'],
            'billing.neighborhood' => ['required_if:billing_same_as_shipping,false', 'nullable', 'string', 'max:255'],
            'billing.city' => ['required_if:billing_same_as_shipping,false', 'nullable', 'string', 'max:255'],
            'billing.state' => ['required_if:billing_same_as_shipping,false', 'nullable', 'string', 'max:255'],
            'billing.postal_code' => ['required_if:billing_same_as_shipping,false', 'nullable', 'string', 'max:10'],

            'shipping_method_id' => ['required', 'integer', 'exists:shipping_methods,id'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'payment_method' => ['required', 'in:stripe,paypal,mercadopago,manual'],
            'installment_months' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'save_address' => ['boolean'],
            'idempotency_key' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tu nombre es obligatorio.',
            'email.required' => 'Tu correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'phone.required' => 'Un teléfono de contacto es obligatorio.',
            'shipping.full_name.required' => 'El nombre del destinatario es obligatorio.',
            'shipping.street.required' => 'La calle es obligatoria.',
            'shipping.ext_number.required' => 'El número exterior es obligatorio.',
            'shipping.neighborhood.required' => 'La colonia es obligatoria.',
            'shipping.city.required' => 'El municipio o alcaldía es obligatorio.',
            'shipping.state.required' => 'El estado es obligatorio.',
            'shipping.postal_code.required' => 'El código postal es obligatorio.',
            'shipping_method_id.required' => 'Selecciona un método de envío.',
            'payment_method.required' => 'Selecciona un método de pago.',
            'payment_method.in' => 'El método de pago seleccionado no es válido.',
        ];
    }
}
