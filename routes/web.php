<?php

use App\Http\Controllers\AdminAvailabilityController;
use App\Http\Controllers\AdminBookingController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminDiaryController;
use App\Http\Controllers\AdminDiningAreaController;
use App\Http\Controllers\AdminRestaurantTableController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\AdminServiceController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CustomerBookingController;
use App\Http\Controllers\WidgetController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BookingController::class, 'create'])->name('bookings.create');
Route::post('/book', [BookingController::class, 'store'])->name('bookings.store');
Route::get('/booking/{booking}', [BookingController::class, 'show'])->name('bookings.show');
Route::get('/manage-booking', [CustomerBookingController::class, 'lookup'])->name('bookings.lookup');
Route::post('/manage-booking', [CustomerBookingController::class, 'find'])->name('bookings.lookup.find');
Route::get('/manage-booking/{booking}/{token}', [CustomerBookingController::class, 'show'])->name('bookings.manage.show');
Route::get('/manage-booking/{booking}/{token}/edit', [CustomerBookingController::class, 'edit'])->name('bookings.manage.edit');
Route::put('/manage-booking/{booking}/{token}', [CustomerBookingController::class, 'update'])->name('bookings.manage.update');
Route::patch('/manage-booking/{booking}/{token}/cancel', [CustomerBookingController::class, 'cancel'])->name('bookings.manage.cancel');
Route::get('/widget/bookings', [WidgetController::class, 'show'])->name('widget.bookings');
Route::get('/widget/embed.js', [WidgetController::class, 'script'])->name('widget.script');

Route::middleware('guest')->group(function () {
    Route::get('/staff/login', [AuthController::class, 'create'])->name('login');
    Route::post('/staff/login', [AuthController::class, 'store'])->name('login.store');
});

Route::post('/staff/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::get('/diary', AdminDiaryController::class)->name('diary');
    Route::get('/bookings/create', [AdminBookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [AdminBookingController::class, 'store'])->name('bookings.store');
    Route::patch('/bookings/{booking}/status', [AdminBookingController::class, 'updateStatus'])->name('bookings.status.update');
    Route::resource('services', AdminServiceController::class)->except(['show']);
    Route::resource('areas', AdminDiningAreaController::class)->except(['show']);
    Route::resource('tables', AdminRestaurantTableController::class)->except(['index', 'show']);
    Route::get('/availability', [AdminAvailabilityController::class, 'index'])->name('availability.index');
    Route::put('/availability/hours', [AdminAvailabilityController::class, 'updateHours'])->name('availability.hours.update');
    Route::post('/availability/closures', [AdminAvailabilityController::class, 'storeClosure'])->name('availability.closures.store');
    Route::delete('/availability/closures/{closure}', [AdminAvailabilityController::class, 'destroyClosure'])->name('availability.closures.destroy');
    Route::get('/settings', [AdminSettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');
    Route::resource('staff', AdminStaffController::class)
        ->parameters(['staff' => 'user'])
        ->except(['show']);
});
