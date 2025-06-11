<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class WorkOrderItemsExport implements FromView
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
            'items' => $this->workOrder->items, // relacija sa WorkOrderItem modelima
        ]);
    }
}
