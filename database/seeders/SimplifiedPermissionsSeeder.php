<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SimplifiedPermissionsSeeder extends Seeder
{
    /**
     * Seed simplified permissions for the system
     * Only 4 modules: Files, Users, Profile, File-statuses
     */
    public function run()
    {
        $permissions = [
            // Files Module
            ['name' => 'create_files', 'description' => 'Create Files', 'module' => 'Files'],
            ['name' => 'view_files', 'description' => 'View Files', 'module' => 'Files'],
            ['name' => 'edit_files', 'description' => 'Edit Files', 'module' => 'Files'],
            ['name' => 'delete_files', 'description' => 'Delete Files', 'module' => 'Files'],

            // Users Module
            ['name' => 'create_users', 'description' => 'Create Users', 'module' => 'Users'],
            ['name' => 'view_users', 'description' => 'View Users', 'module' => 'Users'],
            ['name' => 'edit_users', 'description' => 'Edit Users', 'module' => 'Users'],
            ['name' => 'delete_users', 'description' => 'Delete Users', 'module' => 'Users'],

            // Profile Module
            ['name' => 'view_profile', 'description' => 'View Profile', 'module' => 'Profile'],
            ['name' => 'edit_profile', 'description' => 'Edit Profile', 'module' => 'Profile'],

            // File Statuses Module
            ['name' => 'view_file_statuses', 'description' => 'View File Status', 'module' => 'File Status'],
            ['name' => 'edit_file_statuses', 'description' => 'Change File Status', 'module' => 'File Status'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission['name']],
                [
                    'description' => $permission['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('Seeded ' . count($permissions) . ' simplified permissions.');

        // Optionally, remove old permissions that are not in this list
        $permissionNames = array_column($permissions, 'name');
        $deleted = DB::table('permissions')->whereNotIn('name', $permissionNames)->delete();

        if ($deleted > 0) {
            $this->command->info("Removed {$deleted} old permissions.");
        }

        // Automatically assign all permissions to Admin role
        $adminRole = DB::table('roles')->where('name', 'Admin')->orWhere('name', 'admin')->first();

        if ($adminRole) {
            // Get all permission IDs
            $allPermissionIds = DB::table('permissions')->whereIn('name', $permissionNames)->pluck('id');

            // Remove existing permissions for admin (to avoid duplicates)
            DB::table('role_permission')->where('role_id', $adminRole->id)->delete();

            // Assign all permissions to admin
            foreach ($allPermissionIds as $permissionId) {
                DB::table('role_permission')->insert([
                    'role_id' => $adminRole->id,
                    'permission_id' => $permissionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->command->info("Assigned all {$allPermissionIds->count()} permissions to Admin role.");
        } else {
            $this->command->warn("Admin role not found. Please assign permissions manually.");
        }

        // Assign view_files and view_profile to ALL roles by default
        // NOTE: edit_file_statuses is NOT given to all roles - only high-level roles
        $defaultPermissions = ['view_profile', 'edit_profile', 'view_files'];
        $defaultPermissionIds = DB::table('permissions')
            ->whereIn('name', $defaultPermissions)
            ->pluck('id');

        // Additional permission for Junior Clerk and Assistant Registrar
        $createFilesPermissionId = DB::table('permissions')
            ->where('name', 'create_files')
            ->value('id');

        // edit_file_statuses permission - only for high-level roles
        $editFileStatusesPermissionId = DB::table('permissions')
            ->where('name', 'edit_file_statuses')
            ->value('id');

        // Roles that can change file status (close/reopen)
        $rolesWithStatusPermission = ['dg', 'registrar', 'hcj', 'additional registrar'];

        $allRoles = DB::table('roles')->get();

        foreach ($allRoles as $role) {
            // Skip Admin (already has all permissions)
            if (strtolower($role->name) === 'admin') {
                continue;
            }

            // Remove existing default permissions for this role (to avoid duplicates)
            DB::table('role_permission')
                ->where('role_id', $role->id)
                ->whereIn('permission_id', $defaultPermissionIds)
                ->delete();

            // Assign default permissions
            foreach ($defaultPermissionIds as $permissionId) {
                DB::table('role_permission')->insert([
                    'role_id' => $role->id,
                    'permission_id' => $permissionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Give Junior Clerk and Assistant Registrar create_files permission
            if (in_array(strtolower($role->name), ['junior clerk', 'assistant registrar'])) {
                // Check if already assigned to avoid duplicate
                $exists = DB::table('role_permission')
                    ->where('role_id', $role->id)
                    ->where('permission_id', $createFilesPermissionId)
                    ->exists();

                if (!$exists) {
                    DB::table('role_permission')->insert([
                        'role_id' => $role->id,
                        'permission_id' => $createFilesPermissionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Give high-level roles edit_file_statuses permission
            if (in_array(strtolower($role->name), $rolesWithStatusPermission)) {
                // Check if already assigned to avoid duplicate
                $exists = DB::table('role_permission')
                    ->where('role_id', $role->id)
                    ->where('permission_id', $editFileStatusesPermissionId)
                    ->exists();

                if (!$exists) {
                    DB::table('role_permission')->insert([
                        'role_id' => $role->id,
                        'permission_id' => $editFileStatusesPermissionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info("Assigned default permissions (view_profile, edit_profile, view_files) to all " . ($allRoles->count() - 1) . " non-admin roles.");
        $this->command->info("Assigned create_files permission to Junior Clerk and Assistant Registrar roles.");
        $this->command->info("Assigned edit_file_statuses permission to DG, Registrar, HCJ, and Additional Registrar roles.");
    }
}
