<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $driver = DB::getDriverName();

        // ✅ Bez pokušaja SET session_replication_role — nije dozvoljen na Render PGSQL
        if ($driver === 'mysql') {
            Schema::disableForeignKeyConstraints();
        }

        // 🚫 Ne koristi truncate ako može da izazove FK constraint greške — koristi delete()
        DB::table('work_order_items')->delete();
        DB::table('work_orders')->delete();
        DB::table('products')->delete();
        DB::table('work_phases')->delete();
        DB::table('customers')->delete();
        DB::table('users')->delete();
        DB::table('roles')->delete();
        DB::table('permissions')->delete();
        DB::table('model_has_roles')->delete();
        DB::table('role_has_permissions')->delete();

        if ($driver === 'mysql') {
            Schema::enableForeignKeyConstraints();
        }

        // ✅ Seeduj
        $this->call([
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            ProductWorkPhaseSeeder::class,
            ProductFileSeeder::class,
            CustomerSeeder::class,
        ]);
    }
}
