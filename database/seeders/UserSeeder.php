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

        // 1. Admin korisnik (default)
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password123'),
            ]
        );
        $adminUser->assignRole($adminRole);

        // 2. Manager
        $managerUser = User::updateOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Manager User',
                'password' => bcrypt('password123'),
            ]
        );
        $managerUser->assignRole($managerRole);

        // 3. Viewer
        $viewerUser = User::updateOrCreate(
            ['email' => 'viewer@example.com'],
            [
                'name' => 'Viewer User',
                'password' => bcrypt('password123'),
            ]
        );
        $viewerUser->assignRole($viewerRole);

        // 4. Milan (admin)
        $milan = User::updateOrCreate(
            ['email' => 'milan.stankovic@radijator.rs'],
            [
                'name' => 'Milan',
                'password' => bcrypt('28januar'),
            ]
        );
        $milan->assignRole($adminRole);

        // 5. Mihajlo (admin)
        $mihajlo = User::updateOrCreate(
            ['email' => 'mihajlo.ilic@radijator.rs'],
            [
                'name' => 'Mihajlo',
                'password' => bcrypt('mihajlo123'),
            ]
        );
        $mihajlo->assignRole($adminRole);
    }
}
