<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $resources = [
            'users',
            'products',
            'workphases',
            'workorders',
            'phasetrackings',
            'warehouses',
            'orderrequests',
            'services',
            'reports',
        ];

        foreach ($resources as $resource) {
            Permission::firstOrCreate(['name' => 'view ' . $resource]);
            Permission::firstOrCreate(['name' => 'create ' . $resource]);
            Permission::firstOrCreate(['name' => 'edit ' . $resource]);
            Permission::firstOrCreate(['name' => 'delete ' . $resource]);
        }

        Permission::firstOrCreate(['name' => 'manage users']);
        Permission::firstOrCreate(['name' => 'manage roles']);

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->givePermissionTo([
            'view users', 'edit users',
            'view products', 'create products', 'edit products',
            'view workorders', 'create workorders', 'edit workorders',
            'view reports',
            'manage users',
            'manage roles',
        ]);

        $viewer = Role::firstOrCreate(['name' => 'viewer']);
        $viewer->givePermissionTo([
            'view users',
            'view reports',
        ]);
    }
}
