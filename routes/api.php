<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Ovde možeš dodavati API rute.
|
*/

Route::middleware('api')->get('/ping', function () {
    return response()->json(['message' => 'pong']);
});
