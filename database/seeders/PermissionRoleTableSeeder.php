<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionRoleTableSeeder extends Seeder
{
    public function run(): void
    {
        $admin_permissions = Permission::all();
        $directorateUser_permissions = Permission::whereIn('id', [1, 2, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 28, 29, 30, 31, 32, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57])->get();
        $projectUser_permissions = Permission::whereIn('id', [1, 2, 12, 13, 14, 15, 16, 17, 28, 30, 31, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57])->get();

        Role::findOrFail(1)->permissions()->sync($admin_permissions->pluck('id'));
        Role::findOrFail(3)->permissions()->sync($directorateUser_permissions);
        Role::findOrFail(4)->permissions()->sync($projectUser_permissions);
    }
}
