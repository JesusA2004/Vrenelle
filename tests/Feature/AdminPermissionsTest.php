<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('a user without permissions is forbidden from the admin panel', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin')->assertForbidden();
});

test('a user with an admin-facing permission can reach the dashboard', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::findOrCreate('products.view', 'web'));

    $this->actingAs($user)->get('/admin')->assertOk();
});

test('a user without products.view cannot list products', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::findOrCreate('orders.view', 'web'));

    $this->actingAs($user)->get('/admin/productos')->assertForbidden();
});

test('a non super-admin cannot grant the super_admin role to another user', function () {
    Role::findOrCreate('super_admin', 'web');
    $admin = User::factory()->create();
    $admin->givePermissionTo(Permission::findOrCreate('users.manage', 'web'));

    $target = User::factory()->create();

    $this->actingAs($admin)->put("/admin/usuarios/{$target->id}", [
        'name' => $target->name,
        'email' => $target->email,
        'roles' => ['super_admin'],
    ])->assertRedirect();

    expect($target->fresh()->hasRole('super_admin'))->toBeFalse();
});

test('a super-admin can grant the super_admin role', function () {
    $superAdminRole = Role::findOrCreate('super_admin', 'web');
    $admin = User::factory()->create();
    $admin->givePermissionTo(Permission::findOrCreate('users.manage', 'web'));
    $admin->assignRole($superAdminRole);

    $target = User::factory()->create();

    $this->actingAs($admin)->put("/admin/usuarios/{$target->id}", [
        'name' => $target->name,
        'email' => $target->email,
        'roles' => ['super_admin'],
    ])->assertRedirect();

    expect($target->fresh()->hasRole('super_admin'))->toBeTrue();
});
