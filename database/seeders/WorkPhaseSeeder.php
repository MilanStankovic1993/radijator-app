<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\WorkPhase;

class WorkPhaseSeeder extends Seeder
{
    public function run(): void
    {
        $product1 = Product::where('code', 'MK-001')->first();
        $product2 = Product::where('code', 'DK-002')->first();

        if ($product1) {
            WorkPhase::create([
                'product_id' => $product1->id,
                'name' => 'Sečenje metala',
                'description' => 'Precizno sečenje metalnih komponenti.',
                'is_completed' => false,
            ]);

            WorkPhase::create([
                'product_id' => $product1->id,
                'name' => 'Zavarivanje',
                'description' => 'Spajanje delova u jednu celinu.',
                'is_completed' => false,
            ]);

            WorkPhase::create([
                'product_id' => $product1->id,
                'name' => 'Brušenje',
                'description' => 'Završna obrada ivica i površine.',
                'is_completed' => false,
            ]);
        }

        if ($product2) {
            WorkPhase::create([
                'product_id' => $product2->id,
                'name' => 'Sečenje drveta',
                'description' => 'Priprema drvenih panela na potrebne dimenzije.',
                'is_completed' => false,
            ]);

            WorkPhase::create([
                'product_id' => $product2->id,
                'name' => 'Sklapanje',
                'description' => 'Sastavljanje delova u gotov proizvod.',
                'is_completed' => false,
            ]);

            WorkPhase::create([
                'product_id' => $product2->id,
                'name' => 'Brušenje i lakiranje',
                'description' => 'Finalna obrada i zaštita površine.',
                'is_completed' => false,
            ]);
        }
    }
}
