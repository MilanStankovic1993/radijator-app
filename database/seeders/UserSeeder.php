<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $viewerRole = Role::where('name', 'viewer')->first();

        // Kreiranje i/ili ažuriranje admin korisnika i dodela role
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password123'),
            ]
        );
        $adminUser->assignRole($adminRole);

        // Kreiranje i/ili ažuriranje manager korisnika i dodela role
        $managerUser = User::updateOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Manager User',
                'password' => bcrypt('password123'),
            ]
        );
        $managerUser->assignRole($managerRole);

        // Kreiranje i/ili ažuriranje viewer korisnika i dodela role
        $viewerUser = User::updateOrCreate(
            ['email' => 'viewer@example.com'],
            [
                'name' => 'Viewer User',
                'password' => bcrypt('password123'),
            ]
        );
        $viewerUser->assignRole($viewerRole);
    }
}
