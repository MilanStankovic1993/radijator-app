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

        if (!File::exists($uploadsPath)) {
            $this->command->warn("⚠️ Folder ne postoji: $uploadsPath");
            return;
        }

        $files = File::files($uploadsPath);

        foreach ($files as $file) {
            $filename = $file->getFilename(); // npr. S0008257 - Biolux 14.xlsx
            $code = substr($filename, 0, 8);
            $nameGuess = trim(substr(pathinfo($filename, PATHINFO_FILENAME), 9)); // "Biolux 14"

            // Traži proizvod po imenu
            $product = Product::where('name', 'like', "%$nameGuess%")->first();

            if ($product) {
                // Ažuriraj samo ako se razlikuje
                if (
                    $product->code !== $code ||
                    $product->import_file !== 'uploads/' . $filename
                ) {
                    $product->update([
                        'code' => $code,
                        'import_file' => 'uploads/' . $filename,
                    ]);
                    $this->command->info("✅ Ažuriran: {$product->name} | $code");
                }
            } else {
                $this->command->warn("⚠️ Nije pronađen za fajl: $filename");
            }
        }
    }
}
