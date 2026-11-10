<?php

use App\Models\Branch;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->seed(BranchSeeder::class);
    $this->seed(RoleAndPermissionSeeder::class);
});

test('admin can view user list with branch and roles', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get('/admin/users');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Users/Index')
        ->has('users.data')
        ->has('branches')
        ->has('roles')
    );
});

test('admin can create a new user with branch and role', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branch = Branch::where('code', 'BDG')->first();

    $response = $this->actingAs($admin)->post('/admin/users', [
        'name' => 'Budi PIC Bandung',
        'email' => 'budi.bandung@kencana.id',
        'branch_id' => $branch->id,
        'role' => 'pic',
        'password' => 'secret123',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('users', [
        'email' => 'budi.bandung@kencana.id',
        'branch_id' => $branch->id,
    ]);

    $newUser = User::where('email', 'budi.bandung@kencana.id')->first();
    expect($newUser->hasRole('pic'))->toBeTrue();

    // Verify activity log
    $activity = Activity::where('subject_type', User::class)
        ->where('subject_id', $newUser->id)
        ->first();

    expect($activity)->not->toBeNull();
    expect($activity->description)->toContain('Membuat pengguna baru');
});

test('creating user validates required fields and unique email', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $existing = User::factory()->create(['email' => 'existing@kencana.id']);

    $response = $this->actingAs($admin)->post('/admin/users', [
        'name' => '',
        'email' => 'existing@kencana.id',
        'role' => 'invalid-role',
        'password' => 'short',
    ]);

    $response->assertSessionHasErrors(['name', 'email', 'role', 'password']);
});

test('admin can update user and change branch and role with reason recorded in audit log', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $branchSby = Branch::where('code', 'SBY-HQ')->first();
    $branchJkt = Branch::where('code', 'JKT')->first();

    $targetUser = User::factory()->create([
        'name' => 'Target User',
        'email' => 'target@kencana.id',
        'branch_id' => $branchSby->id,
    ]);
    $targetUser->assignRole('pic');

    $response = $this->actingAs($admin)->put("/admin/users/{$targetUser->id}", [
        'name' => 'Target User Updated',
        'email' => 'target.updated@kencana.id',
        'branch_id' => $branchJkt->id,
        'role' => 'marketing',
        'reason' => 'Mutasi ke tim Marketing Jakarta',
    ]);

    $response->assertRedirect();

    $targetUser->refresh();
    expect($targetUser->name)->toBe('Target User Updated');
    expect($targetUser->email)->toBe('target.updated@kencana.id');
    expect($targetUser->branch_id)->toBe($branchJkt->id);
    expect($targetUser->hasRole('marketing'))->toBeTrue();

    // Verify audit log has reason
    $activity = Activity::where('subject_type', User::class)
        ->where('subject_id', $targetUser->id)
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull();
    expect($activity->description)->toContain('Memperbarui data pengguna');
    expect($activity->properties['reason'])->toBe('Mutasi ke tim Marketing Jakarta');
});

test('admin cannot delete own account', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->delete("/admin/users/{$admin->id}");

    $response->assertRedirect();
    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

test('admin can delete other user and it is recorded in audit log', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $otherUser = User::factory()->create(['email' => 'delete.me@kencana.id']);

    $response = $this->actingAs($admin)->delete("/admin/users/{$otherUser->id}");

    $response->assertRedirect();
    $this->assertDatabaseMissing('users', ['id' => $otherUser->id]);

    $activity = Activity::where('description', 'like', '%Menghapus pengguna: delete.me@kencana.id%')->first();
    expect($activity)->not->toBeNull();
});
