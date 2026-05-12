<?php

use App\Http\Controllers\AdminAvailabilityController;
use App\Http\Controllers\AdminBookingController;
use App\Http\Controllers\AdminBillingController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminDiaryController;
use App\Http\Controllers\AdminDiningAreaController;
use App\Http\Controllers\AdminFeatureController;
use App\Http\Controllers\AdminProfileController;
use App\Http\Controllers\AdminReportsController;
use App\Http\Controllers\AdminRestaurantTableController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\AdminServiceController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CustomerBookingController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\StripeWebhookController;
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

Route::prefix('r/{venue:slug}')->name('tenant.')->group(function () {
    Route::get('/', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/book', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/booking/{booking}', [BookingController::class, 'tenantShow'])->name('bookings.show');
    Route::get('/manage-booking', [CustomerBookingController::class, 'lookup'])->name('bookings.lookup');
    Route::post('/manage-booking', [CustomerBookingController::class, 'find'])->name('bookings.lookup.find');
    Route::get('/manage-booking/{booking}/{token}', [CustomerBookingController::class, 'tenantShow'])->name('bookings.manage.show');
    Route::get('/manage-booking/{booking}/{token}/edit', [CustomerBookingController::class, 'tenantEdit'])->name('bookings.manage.edit');
    Route::put('/manage-booking/{booking}/{token}', [CustomerBookingController::class, 'tenantUpdate'])->name('bookings.manage.update');
    Route::patch('/manage-booking/{booking}/{token}/cancel', [CustomerBookingController::class, 'tenantCancel'])->name('bookings.manage.cancel');
    Route::get('/widget/bookings', [WidgetController::class, 'show'])->name('widget.bookings');
    Route::get('/widget/embed.js', [WidgetController::class, 'script'])->name('widget.script');
});

Route::middleware('guest')->group(function () {
    Route::get('/staff/login', [AuthController::class, 'create'])->name('login');
    Route::post('/staff/login', [AuthController::class, 'store'])->name('login.store');
    Route::get('/signup', [OnboardingController::class, 'create'])->name('signup');
    Route::post('/signup', [OnboardingController::class, 'store'])->name('signup.store');
});

Route::post('/staff/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/stripe/payment/{id}', [\Laravel\Cashier\Http\Controllers\PaymentController::class, 'show'])->name('cashier.payment');
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])->name('cashier.webhook');

Route::middleware(['auth', 'tenant.staff'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::get('/billing', [AdminBillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/checkout/{plan}', [AdminBillingController::class, 'checkout'])->name('billing.checkout');
    Route::post('/billing/swap/{plan}', [AdminBillingController::class, 'swap'])->name('billing.swap');
    Route::post('/billing/resume', [AdminBillingController::class, 'resume'])->name('billing.resume');
    Route::match(['get', 'post'], '/billing/portal', [AdminBillingController::class, 'portal'])->name('billing.portal');
    Route::get('/upgrade/{feature}', [AdminFeatureController::class, 'locked'])->name('features.locked');
    Route::get('/customers', [AdminFeatureController::class, 'customers'])->middleware('feature:customer_crm')->name('customers.index');
    Route::get('/reports', [AdminReportsController::class, 'index'])->middleware('feature:analytics')->name('reports.index');
    Route::get('/reports/export/{report}', [AdminReportsController::class, 'export'])->middleware('feature:advanced_reporting')->name('reports.export');
    Route::get('/waitlist', [AdminFeatureController::class, 'waitlist'])->middleware('feature:waitlist')->name('waitlist.index');
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
