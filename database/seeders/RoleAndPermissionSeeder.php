<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create Granular Permissions
        $permissions = [
            'view_dashboard',
            'view_audit_logs',
            'manage_queues',
            'manage_users',
            'view_jobs',
            'create_jobs',
            'edit_jobs',
            'delete_jobs',
            'view_ai_content',
            'generate_ai_content',
            'edit_ai_content',
            'approve_ai_content',
            'reject_ai_content',
            'view_master_data',
            'manage_master_data',
            'manage_seo',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Define Roles and Assign Permissions
        
        // Super Admin
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->syncPermissions(Permission::all());

        // Admin
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->syncPermissions(Permission::all());

        // Editor
        $editor = Role::firstOrCreate(['name' => 'Editor']);
        $editor->syncPermissions([
            'view_dashboard',
            'view_jobs',
            'create_jobs',
            'edit_jobs',
            'view_ai_content',
            'generate_ai_content',
            'edit_ai_content',
            'approve_ai_content',
            'reject_ai_content',
            'view_master_data',
            'manage_master_data',
            'manage_seo',
        ]);

        // Reviewer
        $reviewer = Role::firstOrCreate(['name' => 'Reviewer']);
        $reviewer->syncPermissions([
            'view_dashboard',
            'view_jobs',
            'view_ai_content',
            'approve_ai_content',
            'reject_ai_content',
            'view_master_data',
        ]);

        // Moderator
        $moderator = Role::firstOrCreate(['name' => 'Moderator']);
        $moderator->syncPermissions([
            'view_dashboard',
            'view_jobs',
            'view_ai_content',
            'generate_ai_content',
            'view_master_data',
        ]);

        // Candidate (standard candidate gets no permissions)
        Role::firstOrCreate(['name' => 'Candidate']);

        // 3. Assign Default Super Admin to Portal Administrator
        $adminUser = User::where('email', 'admin@govjobs.com')->first();
        if ($adminUser) {
            $adminUser->syncRoles(['Super Admin']);
            $adminUser->update(['role' => 'super_admin']);
        }
    }
}
