<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\StaffController;
use Illuminate\Support\Facades\Route;

// ── Public API ──────────────────────────────────────────────────
Route::post('/auth/login',    [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

// ── Payment Webhooks (no auth, verified by signature) ───────────
Route::post('/payments/webhook/paystack', [PaymentController::class, 'paystackWebhook']);
Route::post('/payments/webhook/stripe',   [PaymentController::class, 'stripeWebhook']);

// ── Public Services ─────────────────────────────────────────────
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{service}', [ServiceController::class, 'show']);

// ── Authenticated API (Sanctum) ─────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // ── Bookings ──────────────────────────────────────────────
    Route::apiResource('bookings', BookingController::class);
    Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus']);
    Route::post('/bookings/{booking}/assign',  [BookingController::class, 'assignStaff']);
    Route::post('/bookings/{booking}/cancel',  [BookingController::class, 'cancel']);

    // ── Customer endpoints ────────────────────────────────────
    Route::get('/customer/orders',   [CustomerController::class, 'orders']);
    Route::get('/customer/invoices', [CustomerController::class, 'invoices']);
    Route::get('/customer/profile',  [CustomerController::class, 'profile']);
    Route::put('/customer/profile',  [CustomerController::class, 'updateProfile']);
    Route::apiResource('customers',  CustomerController::class)->except(['create', 'edit']);

    // ── Staff ─────────────────────────────────────────────────
    Route::apiResource('staff', StaffController::class)->except(['create', 'edit']);
    Route::get('/staff/{staff}/schedule', [StaffController::class, 'schedule']);

    // ── Invoices ──────────────────────────────────────────────
    Route::apiResource('invoices', InvoiceController::class)->only(['index', 'show']);
    Route::post('/invoices/{invoice}/pay',    [InvoiceController::class, 'initiatePayment']);

    // ── Payments ──────────────────────────────────────────────
    Route::get('/payments',             [PaymentController::class, 'index']);
    Route::get('/payments/{payment}',   [PaymentController::class, 'show']);
    Route::post('/payments/verify/{reference}', [PaymentController::class, 'verify']);

    // ── Services (admin) ─────────────────────────────────────
    Route::post('/services',            [ServiceController::class, 'store']);
    Route::put('/services/{service}',   [ServiceController::class, 'update']);
    Route::delete('/services/{service}',[ServiceController::class, 'destroy']);
});
