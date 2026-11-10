<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'manage-users',
            'record-director-decision',
            'verify-event',
            'view-respondents',
            'create-event',
            'view-branch-events',
            'view-all-events',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles and assign permissions
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions([
            'manage-users',
            'record-director-decision',
            'verify-event',
            'view-respondents',
            'create-event',
            'view-all-events',
        ]);

        $direksi = Role::firstOrCreate(['name' => 'direksi', 'guard_name' => 'web']);
        $direksi->syncPermissions([
            'record-director-decision',
            'view-all-events',
        ]);

        $marketing = Role::firstOrCreate(['name' => 'marketing', 'guard_name' => 'web']);
        $marketing->syncPermissions([
            'verify-event',
            'view-respondents',
            'view-all-events',
        ]);

        $pic = Role::firstOrCreate(['name' => 'pic', 'guard_name' => 'web']);
        $pic->syncPermissions([
            'create-event',
            'view-branch-events',
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
