<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentInstallmentRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PaymentInstallmentRuleController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('settings.manage'), 403);

        return Inertia::render('admin/installments/Index', [
            'rules' => PaymentInstallmentRule::orderBy('provider')->orderBy('months')->get(),
        ]);
    }

    protected function rules(): array
    {
        return [
            'provider' => ['required', Rule::in(['stripe', 'paypal', 'mercadopago'])],
            'months' => ['required', 'integer', 'min:2', 'max:24'],
            'minimum_amount' => ['required', 'numeric', 'min:0'],
            'maximum_amount' => ['nullable', 'numeric', 'min:0'],
            'fee_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'customer_fee_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'merchant_fee_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'active' => ['boolean'],
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('settings.manage'), 403);

        $data = $request->validate($this->rules());
        $data['minimum_amount'] = (int) round($data['minimum_amount'] * 100);
        $data['maximum_amount'] = isset($data['maximum_amount']) ? (int) round($data['maximum_amount'] * 100) : null;

        PaymentInstallmentRule::updateOrCreate(
            ['provider' => $data['provider'], 'months' => $data['months']],
            $data
        );

        return back()->with('success', 'Plan de meses sin intereses guardado correctamente.');
    }

    public function update(Request $request, PaymentInstallmentRule $paymentInstallmentRule): RedirectResponse
    {
        abort_unless($request->user()->can('settings.manage'), 403);

        $data = $request->validate($this->rules());
        $data['minimum_amount'] = (int) round($data['minimum_amount'] * 100);
        $data['maximum_amount'] = isset($data['maximum_amount']) ? (int) round($data['maximum_amount'] * 100) : null;

        $paymentInstallmentRule->update($data);

        return back()->with('success', 'Plan actualizado correctamente.');
    }

    public function destroy(Request $request, PaymentInstallmentRule $paymentInstallmentRule): RedirectResponse
    {
        abort_unless($request->user()->can('settings.manage'), 403);
        $paymentInstallmentRule->delete();

        return back()->with('success', 'Plan eliminado correctamente.');
    }
}
