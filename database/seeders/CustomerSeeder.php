<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('customers')->insert([
            [
                'name' => 'Petar Petrović',
                'type' => 'individual',
                'jmbg' => '0101990123456',
                'phone' => '0612345678',
                'email' => 'petar@example.com',
                'address' => 'Nemanjina 1',
                'city' => 'Beograd',
                'date_of_birth' => '1990-01-01',
                'id_card_number' => '123456789',
                'note' => 'Preferira kontakt preko emaila.',
                'company_name' => null,
                'pib' => null,
                'contact_person' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Milan Marković',
                'type' => 'individual',
                'jmbg' => null,
                'phone' => null,
                'email' => null,
                'address' => null,
                'city' => null,
                'date_of_birth' => null,
                'id_card_number' => null,
                'note' => null,
                'company_name' => null,
                'pib' => null,
                'contact_person' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Firma ABC d.o.o.',
                'type' => 'company',
                'jmbg' => null,
                'phone' => null,
                'email' => null,
                'address' => 'Beogradska 10, Beograd',
                'city' => null,
                'date_of_birth' => null,
                'id_card_number' => null,
                'note' => null,
                'company_name' => 'Firma ABC d.o.o.',
                'pib' => '123456789',
                'contact_person' => 'Ivana Ilić',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'XYZ Solutions',
                'type' => 'company',
                'jmbg' => null,
                'phone' => null,
                'email' => null,
                'address' => 'Novi Sad 33, Novi Sad',
                'city' => null,
                'date_of_birth' => null,
                'id_card_number' => null,
                'note' => null,
                'company_name' => 'XYZ Solutions',
                'pib' => '987654321',
                'contact_person' => 'Marko Milenković',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
