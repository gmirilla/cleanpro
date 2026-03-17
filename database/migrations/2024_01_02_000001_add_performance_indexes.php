<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add indexes on all high-frequency query columns.
 * These fix the most common slow queries without changing any schema.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── bookings ──────────────────────────────────────────
        Schema::table('bookings', function (Blueprint $table) {
            $table->index('status');
            $table->index('customer_id');
            $table->index('assigned_staff_id');
            $table->index('service_date');
            $table->index('completed_at');
            $table->index(['status', 'service_date']);
            $table->index(['customer_id', 'status']);
            $table->index(['assigned_staff_id', 'status']);
        });

        // ── booking_items ─────────────────────────────────────
        Schema::table('booking_items', function (Blueprint $table) {
            $table->index('booking_id');
            $table->index('service_id');
        });

        // ── invoices ──────────────────────────────────────────
        Schema::table('invoices', function (Blueprint $table) {
            $table->index('booking_id');
            $table->index('status');
            $table->index(['status', 'due_date']);
        });

        // ── payments ──────────────────────────────────────────
        Schema::table('payments', function (Blueprint $table) {
            $table->index('booking_id');
            $table->index('payment_status');
            $table->index('paid_at');
            $table->index(['payment_status', 'paid_at']);
        });

        // ── staff ─────────────────────────────────────────────
        Schema::table('staff', function (Blueprint $table) {
            $table->index('availability_status');
            $table->index('user_id');
        });

        // ── customers ─────────────────────────────────────────
        Schema::table('customers', function (Blueprint $table) {
            $table->index('user_id');
        });

        // ── addresses ─────────────────────────────────────────
        Schema::table('addresses', function (Blueprint $table) {
            $table->index('customer_id');
            $table->index(['customer_id', 'is_default']);
        });

        // ── laundry_orders ────────────────────────────────────
        Schema::table('laundry_orders', function (Blueprint $table) {
            $table->index('booking_id');
        });

        // ── laundry_items ─────────────────────────────────────
        Schema::table('laundry_items', function (Blueprint $table) {
            $table->index('laundry_order_id');
            $table->index('status');
        });

        // ── users ─────────────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
            $table->index(['role', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['customer_id']);
            $table->dropIndex(['assigned_staff_id']);
            $table->dropIndex(['service_date']);
            $table->dropIndex(['completed_at']);
            $table->dropIndex(['status', 'service_date']);
            $table->dropIndex(['customer_id', 'status']);
            $table->dropIndex(['assigned_staff_id', 'status']);
        });

        Schema::table('booking_items', function (Blueprint $table) {
            $table->dropIndex(['booking_id']);
            $table->dropIndex(['service_id']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['booking_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['status', 'due_date']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['booking_id']);
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['paid_at']);
            $table->dropIndex(['payment_status', 'paid_at']);
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->dropIndex(['availability_status']);
            $table->dropIndex(['user_id']);
        });

        Schema::table('customers',  fn($t) => $t->dropIndex(['user_id']));
        Schema::table('addresses',  fn($t) => $t->dropIndex(['customer_id']));
        Schema::table('laundry_orders', fn($t) => $t->dropIndex(['booking_id']));
        Schema::table('laundry_items',  fn($t) => $t->dropIndex(['laundry_order_id']));
        Schema::table('users', fn($t) => $t->dropIndex(['role']));
    }
};
