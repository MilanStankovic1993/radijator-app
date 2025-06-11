<?php

use Illuminate\Support\Facades\Route;
use App\Exports\WorkOrderItemsExport;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/export/work-order/{id}', function ($id) {
    $workOrder = \App\Models\WorkOrder::with('items')->findOrFail($id);
    return Excel::download(new WorkOrderItemsExport($workOrder), 'work_order_items.xlsx');
})->name('export.work-order');

Route::get('/', function () {
    return view('welcome');
});
