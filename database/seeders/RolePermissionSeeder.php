<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Support\Permissions;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Permissions::ALL as $group => $perms) {
            foreach ($perms as $slug => $name) {
                Permission::query()->updateOrCreate(['slug' => $slug], ['name' => $name, 'group' => $group]);
            }
        }

        foreach (Permissions::ROLES as $slug => [$name, $description, $perms]) {
            $role = Role::query()->updateOrCreate(['slug' => $slug], ['name' => $name, 'description' => $description, 'is_system' => true]);
            // Only seed permissions on first creation so admin edits are never silently overwritten.
            if ($role->wasRecentlyCreated && $perms !== ['*']) {
                $role->permissions()->sync(Permission::query()->whereIn('slug', $perms)->pluck('id'));
            }
        }
    }
}
