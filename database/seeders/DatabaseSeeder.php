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

        if ($driver === 'mysql') {
            // MySQL: disable foreign key checks
            Schema::disableForeignKeyConstraints();
        } elseif ($driver === 'pgsql') {
            // PostgreSQL: disable constraint checks
            DB::statement('SET session_replication_role = replica;');
        }

        // Truncate tabele
        \App\Models\WorkOrderItem::truncate();
        \App\Models\WorkOrder::truncate();
        \App\Models\Product::truncate();
        \App\Models\WorkPhase::truncate();
        \App\Models\Customer::truncate();
        \App\Models\User::truncate();
        \Spatie\Permission\Models\Role::truncate();
        \Spatie\Permission\Models\Permission::truncate();

        if ($driver === 'mysql') {
            Schema::enableForeignKeyConstraints();
        } elseif ($driver === 'pgsql') {
            DB::statement('SET session_replication_role = DEFAULT;');
        }

        // Seeduj
        $this->call([
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            ProductWorkPhaseSeeder::class,
            ProductFileSeeder::class,
            CustomerSeeder::class,
        ]);
    }
}
