<?php

use App\Http\Controllers\Api\DocumentController;
use app\Http\Controllers\Api\RateController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PingController;

Route::get('test', fn() => 'ok');

Route::get('/rates/convert', [RateController::class, 'convert']);
Route::post('/pdf/generate', [DocumentController::class, 'generate']);
Route::get('/pdf/stream/{path}', [DocumentController::class, 'stream'])
    ->where('path', '.*');


Route::middleware(['auth.apikey'])->group(function () {
    Route::get('/ping', PingController::class);
});
