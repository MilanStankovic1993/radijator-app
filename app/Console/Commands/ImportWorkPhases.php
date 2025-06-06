<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\WorkPhase;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportWorkPhases extends Command
{
    protected $signature = 'import:workphases';

    protected $description = 'Import products and their work phases from Excel files';

    public function handle()
    {
        $path = storage_path('app/excels');
        $files = glob($path . '/*.xlsx');

        foreach ($files as $filePath) {
            $productName = pathinfo($filePath, PATHINFO_FILENAME);
            $this->info("Importing for product: {$productName}");

            // Kreiraj proizvod ako ne postoji
            $product = Product::firstOrCreate([
                'name' => $productName,
            ], [
                'code' => 'AUTO-' . strtoupper(Str::slug($productName)),
                'description' => 'Automatski dodat proizvod sa fajla: ' . $productName,
                'specifications' => 'Specifikacije nisu unete.',
                'price' => 0.00,
                'status' => 'active',
            ]);

            // Učitaj excel fajl
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            foreach ($rows as $row) {
                if (empty($row[0])) {
                    continue;
                }

                $phaseName = trim($row[0]);

                $workPhase = WorkPhase::firstOrCreate([
                    'name' => $phaseName,
                ]);

                // Poveži proizvod i fazu ako već nije povezano
                $exists = DB::table('product_work_phase')
                    ->where('product_id', $product->id)
                    ->where('work_phase_id', $workPhase->id)
                    ->exists();

                if (!$exists) {
                    $product->workPhases()->attach($workPhase->id);
                }
            }

            $this->info("Gotovo za {$productName}");
        }

        $this->info('Uvoz završen.');
    }
}
