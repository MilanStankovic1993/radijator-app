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
Route::get('/debug', function () {
    $user = \App\Models\User::first(); // koristi prvog korisnika, ili izmeni ID
    auth()->login($user);

    \Log::info('User: ' . $user->email);
    \Log::info('Roles: ' . json_encode($user->getRoleNames()));

    return auth()->check() ? 'OK' : 'NO';
});
Route::get('/session-check', function () {
    if (!auth()->check()) {
        return 'Not logged in';
    }

    $user = auth()->user();
    return [
        'email' => $user->email,
        'roles' => $user->getRoleNames(),
    ];
});