<?php

use Illuminate\Support\Facades\Route;
use App\Exports\WorkOrderItemsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Log;

Route::get('/', function () {
    return view('welcome');
});

// Export radnog naloga
Route::get('/export/work-order/{id}', function ($id) {
    $workOrder = WorkOrder::with('items')->findOrFail($id);
    return Excel::download(new WorkOrderItemsExport($workOrder), 'work_order_items.xlsx');
})->middleware('auth')->name('export.work-order');

// Prikaz laravel log fajla (opcionalno, ograniči pristup)
Route::get('/logs', function () {
    abort_unless(auth()->check() && auth()->user()->hasRole('admin'), 403);
    return response()->file(storage_path('logs/laravel.log'));
});
Route::get('/test-error', function () {
    Log::critical('Testna kritična greška sa staging okruženja!');
    return 'Kritična greška testirana.';
});
