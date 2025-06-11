<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\Product;

class ProductFileSeeder extends Seeder
{
    public function run(): void
    {
        $uploadsPath = public_path('storage/uploads');
        $files = File::files($uploadsPath);

        foreach ($files as $file) {
            $filename = $file->getFilename(); // npr. S0008257 - Biolux 14.xlsx
            $code = substr($filename, 0, 8);
            $nameGuess = trim(substr(pathinfo($filename, PATHINFO_FILENAME), 9)); // "Biolux 14"

            $product = Product::where('name', 'like', "%$nameGuess%")->first();

            if ($product) {
                $product->update([
                    'code' => $code,
                    'import_file' => 'uploads/' . $filename,
                ]);

                echo "✅ Ažuriran: {$product->name} | $code\n";
            } else {
                echo "⚠️ Nije pronađen za fajl: $filename\n";
            }
        }
    }
}
