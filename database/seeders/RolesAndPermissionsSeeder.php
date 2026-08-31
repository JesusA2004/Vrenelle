<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * @var array<int, string>
     */
    protected array $permissions = [
        'products.view', 'products.create', 'products.edit', 'products.delete',
        'categories.manage', 'collections.manage', 'materials.manage',
        'inventory.view', 'inventory.adjust',
        'orders.view', 'orders.edit', 'orders.cancel',
        'customers.view',
        'promotions.view', 'promotions.manage',
        'coupons.view', 'coupons.manage',
        'banners.manage', 'newsletter.manage',
        'reports.view',
        'users.manage', 'roles.manage', 'settings.manage',
        'audit.view',
    ];

    public function run(): void
    {
        Cache::forget('spatie.permission.cache');

        foreach ($this->permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $superAdmin = Role::findOrCreate('super_admin', 'web');
        $superAdmin->syncPermissions($this->permissions);

        $admin = Role::findOrCreate('administrador', 'web');
        $admin->syncPermissions(array_values(array_diff($this->permissions, ['settings.manage', 'roles.manage'])));

        $sales = Role::findOrCreate('ventas', 'web');
        $sales->syncPermissions([
            'orders.view', 'orders.edit', 'orders.cancel',
            'customers.view', 'reports.view', 'products.view',
        ]);

        $inventory = Role::findOrCreate('inventario', 'web');
        $inventory->syncPermissions([
            'products.view', 'products.create', 'products.edit',
            'inventory.view', 'inventory.adjust', 'materials.manage',
        ]);

        $marketing = Role::findOrCreate('marketing', 'web');
        $marketing->syncPermissions([
            'collections.manage', 'categories.manage', 'banners.manage',
            'promotions.view', 'promotions.manage', 'coupons.view', 'coupons.manage',
            'newsletter.manage', 'products.view',
        ]);

        $support = Role::findOrCreate('soporte', 'web');
        $support->syncPermissions([
            'customers.view', 'orders.view', 'products.view',
        ]);
    }
}
