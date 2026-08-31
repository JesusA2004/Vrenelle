<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated customers are redirected to their account dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('account.dashboard'));
});

test('authenticated staff are redirected to the admin dashboard', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::findOrCreate('orders.view', 'web'));
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('admin.dashboard'));
});
