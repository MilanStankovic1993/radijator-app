<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::create([
            'name' => 'Metalna konstrukcija',
            'code' => 'MK-001',
            'description' => 'Osnovna metalna konstrukcija za proizvod.',
            'specifications' => 'Dimenzije: 200x150x50mm; Težina: 1.5kg',
            'price' => 1250.50,
            'status' => 'active',
        ]);

        Product::create([
            'name' => 'Drvena kutija',
            'code' => 'DK-002',
            'description' => 'Kutija za pakovanje proizvoda.',
            'specifications' => 'Materijal: bukva; Dimenzije: 300x200x100mm',
            'price' => 620.00,
            'status' => 'inactive',
        ]);
    }
}