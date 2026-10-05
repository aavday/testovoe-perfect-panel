<?php

use App\Http\Controllers\RatesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum', 'ability:use-api'])->group(function () {
    Route::get('v1/rates', [RatesController::class, 'index']);
    Route::post('v1/convert', [RatesController::class, 'convert']);
});
