<?php

use App\Models\Branch;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BranchSeeder::class);
    $this->seed(RoleAndPermissionSeeder::class);
});

test('admin can view branches listing', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get('/admin/branches');
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Branches/Index')
        ->has('branches.data')
    );
});

test('non-admin cannot access branch management', function () {
    $pic = User::factory()->create();
    $pic->assignRole('pic');

    $response = $this->actingAs($pic)->get('/admin/branches');
    $response->assertForbidden();
});

test('admin can create a new branch', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/admin/branches', [
        'name' => 'Kantor Cabang Malang',
        'code' => ' mlg ',
        'is_headquarters' => false,
    ]);

    $response->assertRedirect('/admin/branches');

    $this->assertDatabaseHas('branches', [
        'name' => 'Kantor Cabang Malang',
        'code' => 'MLG',
        'is_headquarters' => false,
    ]);
});

test('admin cannot create duplicate branch code', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/admin/branches', [
        'name' => 'Duplikat Surabaya',
        'code' => 'SBY-HQ',
        'is_headquarters' => false,
    ]);

    $response->assertSessionHasErrors('code');
});

test('admin can update a branch', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::where('code', 'JKT')->first();

    $response = $this->actingAs($admin)->put("/admin/branches/{$branch->id}", [
        'name' => 'Jakarta Raya & Sekitarnya',
        'code' => 'JKT',
        'is_headquarters' => false,
    ]);

    $response->assertRedirect('/admin/branches');

    $this->assertDatabaseHas('branches', [
        'id' => $branch->id,
        'name' => 'Jakarta Raya & Sekitarnya',
        'code' => 'JKT',
    ]);
});

test('admin cannot delete branch that has associated users', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::where('code', 'SBY-HQ')->first();
    User::factory()->create(['branch_id' => $branch->id]);

    $response = $this->actingAs($admin)->delete("/admin/branches/{$branch->id}");
    $response->assertSessionHasErrors('error');
    $this->assertDatabaseHas('branches', ['id' => $branch->id]);
});

test('admin can delete branch without users or events', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $emptyBranch = Branch::create([
        'name' => 'Cabang Baru Tanpa Data',
        'code' => 'EMPTY-01',
        'is_headquarters' => false,
    ]);

    $response = $this->actingAs($admin)->delete("/admin/branches/{$emptyBranch->id}");
    $response->assertRedirect('/admin/branches');
    $this->assertDatabaseMissing('branches', ['id' => $emptyBranch->id]);
});
