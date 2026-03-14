<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Auth\PaystackController;
use App\Http\Controllers\WebhookController;
use App\Livewire\Admin\BookingCalendar;
use App\Livewire\Admin\BookingManager;
use App\Livewire\Admin\CompanyDashboard;
use App\Livewire\Admin\CustomerManager;
use App\Livewire\Admin\InvoiceViewer;
use App\Livewire\Admin\LaundryOrderManager;
use App\Livewire\Admin\ReportsDashboard;
use App\Livewire\Admin\ServiceManager;
use App\Livewire\Admin\StaffManager;
use App\Livewire\Customer\BookingForm;
use App\Livewire\Customer\CustomerDashboard;
use App\Livewire\Customer\PaymentCheckout;
use App\Livewire\Staff\StaffTaskBoard;
use Illuminate\Support\Facades\Route;

// ── Public ─────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));

// ── Auth (Breeze) ───────────────────────────────────────────────
require __DIR__ . '/auth.php';

// ── Webhooks (no CSRF, signed) ──────────────────────────────────
Route::prefix('webhooks')->name('webhooks.')->group(function () {
    Route::post('/paystack', [WebhookController::class, 'paystack'])->name('paystack');
    Route::post('/stripe',   [WebhookController::class, 'stripe'])->name('stripe');
});

// ── Paystack Callback ───────────────────────────────────────────
Route::get('/payments/paystack/callback', [PaystackController::class, 'callback'])
    ->middleware('auth')
    ->name('payments.paystack.callback');

// ── Admin / Super Admin ─────────────────────────────────────────
Route::middleware(['auth', 'role:super_admin,admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard',     CompanyDashboard::class)->name('dashboard');
    Route::get('/bookings',      BookingManager::class)->name('bookings');
    Route::get('/calendar',      BookingCalendar::class)->name('calendar');
    Route::get('/customers',     CustomerManager::class)->name('customers');
    Route::get('/staff',         StaffManager::class)->name('staff');
    Route::get('/services',      ServiceManager::class)->name('services');
    Route::get('/laundry-orders',LaundryOrderManager::class)->name('laundry-orders');
    Route::get('/invoices',      InvoiceViewer::class)->name('invoices');
    Route::get('/reports',       ReportsDashboard::class)->name('reports');
});

// ── Staff ───────────────────────────────────────────────────────
Route::middleware(['auth', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', StaffTaskBoard::class)->name('dashboard');
    Route::get('/tasks',     StaffTaskBoard::class)->name('tasks');
    Route::get('/schedule',  StaffTaskBoard::class)->name('schedule');
});

// ── Customer ────────────────────────────────────────────────────
Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard',           CustomerDashboard::class)->name('dashboard');
    Route::get('/book',                BookingForm::class)->name('book');
    Route::get('/bookings',            \App\Http\Controllers\Customer\BookingController::class . '@index')->name('bookings');
    Route::get('/bookings/{id}',       \App\Http\Controllers\Customer\BookingController::class . '@show')->name('bookings.show');
    Route::get('/invoices',            \App\Http\Controllers\Customer\InvoiceController::class . '@index')->name('invoices');
    Route::get('/invoices/{invoice}',  \App\Http\Controllers\Customer\InvoiceController::class . '@show')->name('invoices.show');
    Route::get('/checkout/{invoice}',  PaymentCheckout::class)->name('checkout');
});

// ── Redirect after login ────────────────────────────────────────
Route::middleware('auth')->get('/home', function () {
    return redirect(auth()->user()->getDashboardRoute());
})->name('home');
