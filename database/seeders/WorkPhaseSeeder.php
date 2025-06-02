<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\WorkPhase;

class WorkPhaseSeeder extends Seeder
{
    public function run(): void
    {
        $phases = [
            // Grdica
            ['name' => 'Sečenje cevi', 'location' => 'Grdica', 'description' => 'Priprema i sečenje cevi na potrebne dimenzije.'],
            ['name' => 'Zavarivanje spojeva', 'location' => 'Grdica', 'description' => 'Spajanje delova zavarivanjem.'],
            ['name' => 'Brušenje', 'location' => 'Grdica', 'description' => 'Brušenje i priprema površina.'],

            // Seovac
            ['name' => 'Farbanje', 'location' => 'Seovac', 'description' => 'Nanošenje zaštitnog sloja boje.'],
            ['name' => 'Pakovanje', 'location' => 'Seovac', 'description' => 'Pakovanje gotovih proizvoda u ambalažu.'],
            ['name' => 'Kontrola kvaliteta', 'location' => 'Seovac', 'description' => 'Provera dimenzija i završenosti.'],
        ];

        foreach ($phases as $phase) {
            WorkPhase::create($phase);
        }
    }
}
