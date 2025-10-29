<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\PingController;

Route::get('/', function () {
    return view('welcome');
});




Route::get('/health', HealthController::class);
