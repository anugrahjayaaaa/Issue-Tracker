<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // ponytail: flat permission list; expand per-module as features land
    private const PERMISSIONS = [
        'user.view', 'user.create', 'user.edit', 'user.delete', 'user.restore', 'user.force-delete', 'user.lock',
        'role.view', 'role.create', 'role.edit', 'role.delete', 'role.restore', 'role.force-delete',
        'permission.view', 'permission.create', 'permission.edit', 'permission.delete', 'permission.restore', 'permission.force-delete',
        'audit.view',
        'session.view', 'session.revoke',
        'api-token.view', 'api-token.create', 'api-token.delete',
        'feature.manage',
        'logs.view',
        'billing.view', 'billing.cancel',
        'translation.view', 'translation.edit',
        'telescope.view',
        'periscope.view',
        // Issue Tracker
        'project.manage',
        'issue.view', 'issue.create', 'issue.edit', 'issue.delete',
        'comment.create', 'comment.edit', 'comment.delete',
    ];

    public function run(): void
    {
        // Permissions
        foreach (self::PERMISSIONS as $perm) {
            Permission::findOrCreate($perm, 'web');
        }

        // Feature flags are declared in AppServiceProvider + config/pennant.php
        // (Laravel Pennant, DB store). They default ON; no seeding needed.

        // Default plans: 'free' is always present (price 0, no PG needed).
        // slug/price/limits/features are fully custom via CRUD (doc §9b).
        Plan::updateOrCreate(
            ['slug' => 'free'],
            [
                'name' => 'Free',
                'price_monthly' => 0,
                'is_active' => true,
                'limits' => ['max_members' => 2, 'max_projects' => 1, 'max_storage_mb' => 500],
                'features' => ['audit', 'telescope'],
            ]
        );

        // Default active plan = free (Model 1 instance setting).
        Setting::updateOrCreate(['key' => 'active_plan'], ['value' => 'free']);

        // Roles
        $superAdmin = Role::findOrCreate('super-admin', 'web');
        $admin = Role::findOrCreate('admin', 'web');
        $staff = Role::findOrCreate('staff', 'web');

        $admin->syncPermissions(self::PERMISSIONS);
        $staff->syncPermissions(['user.view', 'audit.view', 'project.manage', 'issue.view', 'comment.create']);
        $superAdmin->syncPermissions(self::PERMISSIONS);

        // Super-admin user (first-run)
        $user = User::updateOrCreate(
            ['email' => 'admin@laravel-base.local'],
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'phone' => '+6281200000001',
                'password' => bcrypt('Admin@base12345'),
                'email_verified_at' => now(),
            ]
        );
        $user->assignRole('super-admin');

        $this->call(LanguageLineSeeder::class);
    }
}
