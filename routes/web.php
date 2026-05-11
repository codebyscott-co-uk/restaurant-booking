<?php

use App\Http\Controllers\AdminDiaryController;
use App\Http\Controllers\AdminDiningAreaController;
use App\Http\Controllers\AdminRestaurantTableController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\AdminServiceController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BookingController::class, 'create'])->name('bookings.create');
Route::post('/book', [BookingController::class, 'store'])->name('bookings.store');
Route::get('/booking/{booking}', [BookingController::class, 'show'])->name('bookings.show');

Route::middleware('guest')->group(function () {
    Route::get('/staff/login', [AuthController::class, 'create'])->name('login');
    Route::post('/staff/login', [AuthController::class, 'store'])->name('login.store');
});

Route::post('/staff/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/diary', AdminDiaryController::class)->name('diary');
    Route::resource('services', AdminServiceController::class)->except(['show']);
    Route::resource('areas', AdminDiningAreaController::class)->except(['show']);
    Route::resource('tables', AdminRestaurantTableController::class)->except(['index', 'show']);
    Route::get('/settings', [AdminSettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');
    Route::resource('staff', AdminStaffController::class)
        ->parameters(['staff' => 'user'])
        ->except(['show']);
});
