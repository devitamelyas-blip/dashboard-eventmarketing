<?php

use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BranchSeeder::class);
    $this->seed(RoleAndPermissionSeeder::class);
});

test('admin can view activity logs list', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    activity()->causedBy($admin)->log('Pengujian aksi oleh admin');

    $response = $this->actingAs($admin)->get('/admin/activity-logs');
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/ActivityLogs/Index')
        ->has('activities.data')
    );
});

test('non-admin cannot view activity logs', function () {
    $pic = User::factory()->create();
    $pic->assignRole('pic');

    $response = $this->actingAs($pic)->get('/admin/activity-logs');
    $response->assertForbidden();
});
