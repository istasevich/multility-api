<?php

use app\Http\Controllers\Api\RateController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PingController;

Route::get('test', fn() => 'ok');

Route::get('/rates/convert', [RateController::class, 'convert']);


Route::middleware(['auth.apikey'])->group(function () {
    Route::get('/ping', PingController::class);
});
