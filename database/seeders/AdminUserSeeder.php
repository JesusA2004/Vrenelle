<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(['email' => 'admin@vrenelle.mx'], [
            'name' => 'Administrador Vrenelle',
            'password' => Hash::make('Vrenelle#2026'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $admin->syncRoles(['super_admin']);

        $sales = User::updateOrCreate(['email' => 'ventas@vrenelle.mx'], [
            'name' => 'Equipo de Ventas',
            'password' => Hash::make('Vrenelle#2026'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $sales->syncRoles(['ventas']);

        $customer = User::updateOrCreate(['email' => 'cliente@vrenelle.mx'], [
            'name' => 'Cliente Demo',
            'password' => Hash::make('Vrenelle#2026'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $customer->wishlist()->firstOrCreate([]);
    }
}
