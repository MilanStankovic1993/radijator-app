<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstWhere('name', 'admin');
        $managerRole = Role::firstWhere('name', 'manager');
        $viewerRole = Role::firstWhere('name', 'viewer');

        // 🔐 Default password
        $defaultPassword = bcrypt('password123');

        // 👤 Admin korisnik
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin User', 'password' => $defaultPassword]
        );
        $adminUser->syncRoles([$adminRole]);

        // 👤 Manager korisnik
        $managerUser = User::updateOrCreate(
            ['email' => 'manager@example.com'],
            ['name' => 'Manager User', 'password' => $defaultPassword]
        );
        $managerUser->syncRoles([$managerRole]);

        // 👤 Viewer korisnik
        $viewerUser = User::updateOrCreate(
            ['email' => 'viewer@example.com'],
            ['name' => 'Viewer User', 'password' => $defaultPassword]
        );
        $viewerUser->syncRoles([$viewerRole]);

        // 👤 Milan (admin)
        $milan = User::updateOrCreate(
            ['email' => 'milan.stankovic@radijator.rs'],
            ['name' => 'Milan', 'password' => bcrypt('28januar')]
        );
        $milan->syncRoles([$adminRole]);

        // 👤 Mihajlo (admin)
        $mihajlo = User::updateOrCreate(
            ['email' => 'mihajlo.ilic@radijator.rs'],
            ['name' => 'Mihajlo', 'password' => bcrypt('mihajlo123')]
        );
        $mihajlo->syncRoles([$adminRole]);

        $this->command->info('✅ Users seeded successfully.');
    }
}
