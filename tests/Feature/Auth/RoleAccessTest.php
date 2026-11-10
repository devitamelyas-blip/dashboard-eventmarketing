<?php

use App\Models\Branch;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleAndPermissionSeeder;

beforeEach(function () {
    $this->seed(BranchSeeder::class);
    $this->seed(RoleAndPermissionSeeder::class);
});

test('unauthenticated users are redirected to login from protected routes', function () {
    $this->get('/dashboard')->assertRedirect('/login');
    $this->get('/admin/users')->assertRedirect('/login');
});

test('admin can access admin user management and dashboard', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertOk();

    $this->actingAs($admin)
        ->get('/admin/users')
        ->assertOk();
});

test('direksi cannot access admin user management', function () {
    $direksi = User::factory()->create();
    $direksi->assignRole('direksi');

    $this->actingAs($direksi)
        ->get('/dashboard')
        ->assertOk();

    $this->actingAs($direksi)
        ->get('/admin/users')
        ->assertForbidden();
});

test('marketing cannot access admin user management', function () {
    $marketing = User::factory()->create();
    $marketing->assignRole('marketing');

    $this->actingAs($marketing)
        ->get('/dashboard')
        ->assertOk();

    $this->actingAs($marketing)
        ->get('/admin/users')
        ->assertForbidden();
});

test('pic cannot access admin user management', function () {
    $pic = User::factory()->create();
    $pic->assignRole('pic');

    $this->actingAs($pic)
        ->get('/dashboard')
        ->assertOk();

    $this->actingAs($pic)
        ->get('/admin/users')
        ->assertForbidden();
});
