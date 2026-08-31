<?php

namespace Database\Seeders;

use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class ShippingMethodsSeeder extends Seeder
{
    public function run(): void
    {
        ShippingMethod::updateOrCreate(['name' => 'Envío estándar'], [
            'description' => 'Entrega asegurada en la puerta de tu domicilio.',
            'price' => 25000,
            'free_from' => 500000,
            'estimated_days' => '3-5 días hábiles',
            'active' => true,
            'sort_order' => 1,
        ]);

        ShippingMethod::updateOrCreate(['name' => 'Envío express'], [
            'description' => 'Para cuando el momento no puede esperar.',
            'price' => 45000,
            'free_from' => null,
            'estimated_days' => '1-2 días hábiles',
            'active' => true,
            'sort_order' => 2,
        ]);

        ShippingMethod::updateOrCreate(['name' => 'Recoger en boutique'], [
            'description' => 'Recibe tu pieza personalmente en nuestra boutique de Polanco.',
            'price' => 0,
            'free_from' => null,
            'estimated_days' => 'Disponible en 24 horas',
            'active' => true,
            'sort_order' => 3,
        ]);
    }
}
