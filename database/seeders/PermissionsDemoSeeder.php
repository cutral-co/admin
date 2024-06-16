<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsDemoSeeder extends Seeder
{
    /**
     * Create the initial roles and permissions.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'permission.view', 'description' => 'Puede ver permisos']);
        Permission::create(['name' => 'permission.asign', 'description' => 'Puede otorgar o quitar permisos']);
        Permission::create(['name' => 'role.view', 'description' => 'Puede ver permisos']);
        Permission::create(['name' => 'role.asign', 'description' => 'Puede otorgar o quitar permisos']);

        // create roles and assign existing permissions
        $role1 = Role::create(['name' => 'sudo', 'description' => 'SUDO']);
        $role1->givePermissionTo('permission.view');
        $role1->givePermissionTo('permission.asign');
        $role1->givePermissionTo('role.view');
        $role1->givePermissionTo('role.asign');
    }
}
