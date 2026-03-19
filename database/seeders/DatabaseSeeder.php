<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // ODRADJENO NA PRODUKCIJI
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            ProductWorkPhaseSeeder::class,
            ProductFileSeeder::class,
            CustomerSeeder::class,
            //////////////////////////////////////
            
        ]);
    }
}
