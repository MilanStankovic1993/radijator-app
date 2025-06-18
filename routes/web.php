<?php

use Illuminate\Support\Facades\Route;
use App\Exports\WorkOrderItemsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\User;

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
Route::get('/check-access', function () {
    $user = auth()->user();

    if (!$user) {
        return 'Not authenticated';
    }

    $result = $user->canAccessPanel(filament()->getCurrentPanel());
    return [
        'email' => $user->email,
        'roles' => $user->getRoleNames(),
        'can_access_panel' => $result,
    ];
});
Route::get('/test-user', function () {
    return [
        'auth_check' => auth()->check(),
        'user' => optional(auth()->user())->email,
        'roles' => auth()->check() ? auth()->user()->getRoleNames() : [],
    ];
});
Route::get('/logs', function () {
    return response()->file(storage_path('logs/laravel.log'));
});
Route::get('/test-panel-access', function () {
    $user = auth()->user();
    $panel = filament()->getCurrentPanel();

    return [
        'email' => optional($user)->email,
        'check' => optional($user)?->canAccessPanel($panel),
        'session' => auth()->check(),
        'cookie' => request()->cookie(config('session.cookie')),
        'domain' => config('session.domain'),
        'driver' => config('session.driver'),
        'same_site' => config('session.same_site'),
    ];
});
Route::get('/ajax-check', function () {
    return response()->json([
        'auth' => auth()->check(),
        'user' => auth()->user()?->email,
        'roles' => auth()->user()?->getRoleNames(),
    ]);
});