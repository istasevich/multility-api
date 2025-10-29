<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PingController;

Route::get('test', fn() => 'ok');

Route::get('/pi', function () {
    return response()->json(['status' => 'ok', 'app' => 'Multility Core API']);
});

Route::get('/ping', PingController::class);
