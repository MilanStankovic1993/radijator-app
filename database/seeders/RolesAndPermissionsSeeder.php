<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 🔄 Očisti keširane dozvole
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

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

        // 🛡️ Kreiraj CRUD dozvole za svaki resource
        foreach ($resources as $resource) {
            foreach (['view', 'create', 'edit', 'delete'] as $action) {
                Permission::firstOrCreate(['name' => "$action $resource"]);
            }
        }

        // 🛠️ Specifične permisije
        Permission::firstOrCreate(['name' => 'manage users']);
        Permission::firstOrCreate(['name' => 'manage roles']);

        // 🎩 Admin rola
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        // 📊 Manager rola
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions([
            'view users', 'edit users',
            'view products', 'create products', 'edit products',
            'view workorders', 'create workorders', 'edit workorders',
            'view reports',
            'manage users',
            'manage roles',
        ]);

        // 👁️ Viewer rola
        $viewer = Role::firstOrCreate(['name' => 'viewer']);
        $viewer->syncPermissions([
            'view users',
            'view reports',
        ]);

        $this->command->info('✅ Roles and permissions seeded.');
    }
}
