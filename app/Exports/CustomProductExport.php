<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\Product;

class CustomProductExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Product::all()->map(function ($product) {
            return [
                $product->id,
                $product->code,
                $product->name,
                $product->description,
                $product->price,
                $product->status,
            ];
        });
    }

    public function headings(): array
    {
        return ['ID', 'Šifra', 'Naziv', 'Opis', 'Cena', 'Status'];
    }
}
