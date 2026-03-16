<?php

use App\Http\Controllers\Auth\PaystackController;
use App\Http\Controllers\Customer\BookingController as CustomerBookingController;
use App\Http\Controllers\Customer\InvoiceController as CustomerInvoiceController;
use App\Http\Controllers\WebhookController;
use App\Livewire\Admin\BookingCalendar;
use App\Livewire\Admin\BookingManager;
use App\Livewire\Admin\CompanyDashboard;
use App\Livewire\Admin\CustomerManager;
use App\Livewire\Admin\InvoiceViewer;
use App\Livewire\Admin\LaundryOrderManager;
use App\Livewire\Admin\GarmentPriceManager;
use App\Livewire\Admin\ReportsDashboard;
use App\Livewire\Admin\ServiceManager;
use App\Livewire\Admin\StaffManager;
use App\Livewire\Customer\BookingForm;
use App\Livewire\Customer\CustomerDashboard;
use App\Livewire\Customer\PaymentCheckout;
use App\Livewire\Staff\StaffTaskBoard;
use Illuminate\Support\Facades\Route;

// ── Public ─────────────────────────────────────────────────────
Route::get('/', fn () => redirect()->route('login'));

// ── Auth (Breeze/Fortify) ───────────────────────────────────────
require __DIR__ . '/auth.php';

// ── Webhooks (excluded from CSRF in bootstrap/app.php) ─────────
Route::prefix('webhooks')->name('webhooks.')->group(function () {
    Route::post('/paystack', [WebhookController::class, 'paystack'])->name('paystack');
    Route::post('/stripe',   [WebhookController::class, 'stripe'])->name('stripe');
});

// ── Paystack payment callback ───────────────────────────────────
Route::get('/payments/paystack/callback', [PaystackController::class, 'callback'])
    ->middleware('auth')
    ->name('payments.paystack.callback');

// ── Admin & Super Admin ─────────────────────────────────────────
Route::middleware(['auth', 'role:super_admin,admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard',      CompanyDashboard::class)->name('dashboard');
        Route::get('/bookings',       BookingManager::class)->name('bookings');
        Route::get('/calendar',       BookingCalendar::class)->name('calendar');
        Route::get('/customers',      CustomerManager::class)->name('customers');
        Route::get('/staff',          StaffManager::class)->name('staff');
        Route::get('/services',       ServiceManager::class)->name('services');
        Route::get('/laundry-orders', LaundryOrderManager::class)->name('laundry-orders');
        Route::get('/invoices',       InvoiceViewer::class)->name('invoices');
        Route::get('/reports',        ReportsDashboard::class)->name('reports');
        Route::get('/garment-prices', GarmentPriceManager::class)->name('garment-prices');
    });



// ── Staff ───────────────────────────────────────────────────────
Route::middleware(['auth', 'role:staff'])
    ->prefix('staff')
    ->name('staff.')
    ->group(function () {
        Route::get('/dashboard', StaffTaskBoard::class)->name('dashboard');
        Route::get('/tasks',     StaffTaskBoard::class)->name('tasks');
    });

// ── Customer ────────────────────────────────────────────────────
Route::middleware(['auth', 'role:customer'])
    ->prefix('customer')
    ->name('customer.')
    ->group(function () {
        Route::get('/dashboard',  CustomerDashboard::class)->name('dashboard');
        Route::get('/book',       BookingForm::class)->name('book');

        // Standard controller routes (non-Livewire pages)
        Route::get('/bookings',           [CustomerBookingController::class, 'index'])->name('bookings');
        Route::get('/bookings/{id}',      [CustomerBookingController::class, 'show'])->name('bookings.show');
        Route::get('/invoices',           [CustomerInvoiceController::class, 'index'])->name('invoices');
        Route::get('/invoices/{invoice}', [CustomerInvoiceController::class, 'show'])->name('invoices.show');

        // Livewire checkout — uses {invoice} model binding matched by mount(Invoice $invoice)
        Route::get('/checkout/{invoice}', PaymentCheckout::class)->name('checkout');
    });

// ── Post-login fallback redirect ────────────────────────────────
Route::middleware('auth')->get('/home', function () {
    return redirect(auth()->user()->getDashboardRoute());
})->name('home');
