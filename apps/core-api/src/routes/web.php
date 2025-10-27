<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/ping', function () {
    return response()->json(['status' => 'ok', 'app' => 'Multility Core API']);
});



Route::get('/health', HealthController::class);
