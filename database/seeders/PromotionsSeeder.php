<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\PaymentInstallmentRule;
use App\Models\Promotion;
use Illuminate\Database\Seeder;

class PromotionsSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::updateOrCreate(['code' => 'BIENVENIDA10'], [
            'description' => '10% de descuento en tu primera compra.',
            'type' => 'percentage',
            'value' => 10,
            'minimum_purchase' => 500000,
            'maximum_discount' => 200000,
            'usage_limit' => null,
            'usage_per_customer' => 1,
            'active' => true,
            'first_purchase_only' => true,
            'stackable' => false,
        ]);

        Promotion::updateOrCreate(['name' => 'Envío gratis en pedidos mayores a $8,000'], [
            'internal_name' => 'free-shipping-8000',
            'description' => 'Envío gratuito automático al superar el monto mínimo.',
            'type' => 'free_shipping',
            'value' => 0,
            'minimum_purchase' => 800000,
            'active' => true,
            'priority' => 1,
            'stackable' => true,
        ]);

        // Installment rules are seeded inactive by default — activate them from
        // the admin panel only once the corresponding gateway credentials are
        // configured and the merchant account genuinely supports the plan.
        $rules = [
            ['provider' => 'mercadopago', 'months' => 3, 'fee' => 0, 'customer_fee' => 0, 'merchant_fee' => 0],
            ['provider' => 'mercadopago', 'months' => 6, 'fee' => 8.5, 'customer_fee' => 0, 'merchant_fee' => 8.5],
            ['provider' => 'mercadopago', 'months' => 12, 'fee' => 15, 'customer_fee' => 5, 'merchant_fee' => 10],
            ['provider' => 'stripe', 'months' => 3, 'fee' => 0, 'customer_fee' => 0, 'merchant_fee' => 0],
        ];

        foreach ($rules as $rule) {
            PaymentInstallmentRule::updateOrCreate(
                ['provider' => $rule['provider'], 'months' => $rule['months']],
                [
                    'minimum_amount' => 300000,
                    'maximum_amount' => null,
                    'fee_percentage' => $rule['fee'],
                    'customer_fee_percentage' => $rule['customer_fee'],
                    'merchant_fee_percentage' => $rule['merchant_fee'],
                    'active' => false,
                ]
            );
        }
    }
}
