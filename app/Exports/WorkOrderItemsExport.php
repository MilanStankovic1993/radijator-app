<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class WorkOrderItemsExport implements FromView, WithDrawings
{
    protected $workOrder;

    public function __construct($workOrder)
    {
        $this->workOrder = $workOrder;
    }

    public function view(): View
    {
        return view('exports.work-order-items', [
            'workOrder' => $this->workOrder,
            'items' => $this->workOrder->items()->with(['workPhase', 'product'])->get(),
        ]);
    }
    public function drawings()
    {
        $drawings = [];

        foreach ($this->workOrder->items as $index => $item) {
            $drawing = new Drawing();
            $drawing->setName('Logo ' . ($index + 1));
            $drawing->setDescription('Radijator Inženjering - ' . ($index + 1));
            $drawing->setPath(public_path('logo.png'));
            $drawing->setHeight(90);

            // Primer: stavljaš u kolonu F i redove 1, 30, 60, itd.
            $row = 2 + ($index * 22); // Pretpostavka: svaki zapis zauzima oko 30 redova
            $drawing->setCoordinates('E' . $row);
            $drawing->setOffsetX(10);
            $drawing->setOffsetY(5);

            $drawings[] = $drawing;
        }

        return $drawings;
    }
}
