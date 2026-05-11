<?php

use App\Http\Controllers\Api\V1\BookingApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/venue', [BookingApiController::class, 'venue']);
    Route::get('/services', [BookingApiController::class, 'services']);
    Route::get('/availability', [BookingApiController::class, 'availability']);
    Route::post('/bookings', [BookingApiController::class, 'store']);

    Route::prefix('{venue:slug}')->group(function () {
        Route::get('/venue', [BookingApiController::class, 'venue']);
        Route::get('/services', [BookingApiController::class, 'services']);
        Route::get('/availability', [BookingApiController::class, 'availability']);
        Route::post('/bookings', [BookingApiController::class, 'store']);
    });
});
