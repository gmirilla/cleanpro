<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * SQLite does not support ALTER COLUMN, so we:
     *  1. Create a temporary replacement table with garment_type as string
     *  2. Copy all existing data across
     *  3. Drop the old table
     *  4. Rename the temp table
     */
    public function up(): void
    {
        // For MySQL / PostgreSQL, a simple change() is enough
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('laundry_items', function (Blueprint $table) {
                $table->string('garment_type')->change();
            });
            return;
        }

        // ── SQLite path ──────────────────────────────────────────────
        DB::statement('PRAGMA foreign_keys = OFF');

        DB::statement('
            CREATE TABLE laundry_items_new (
                id                INTEGER PRIMARY KEY AUTOINCREMENT,
                laundry_order_id  INTEGER NOT NULL,
                garment_type      VARCHAR(255) NOT NULL,
                quantity          INTEGER UNSIGNED NOT NULL DEFAULT 1,
                unit_price        NUMERIC(10,2) NOT NULL DEFAULT 0,
                subtotal          NUMERIC(10,2) NOT NULL DEFAULT 0,
                service_type      VARCHAR(255) NULL,
                status            VARCHAR(255) NOT NULL DEFAULT \'received\',
                created_at        DATETIME NULL,
                updated_at        DATETIME NULL,
                FOREIGN KEY (laundry_order_id) REFERENCES laundry_orders(id) ON DELETE CASCADE
            )
        ');

        // Copy all existing rows — fill new columns with 0 defaults if they don't exist yet
        $columns = Schema::getColumnListing('laundry_items');
        $hasUnitPrice = in_array('unit_price', $columns);
        $hasSubtotal  = in_array('subtotal', $columns);

        DB::statement('
            INSERT INTO laundry_items_new
                (id, laundry_order_id, garment_type, quantity, unit_price, subtotal, service_type, status, created_at, updated_at)
            SELECT
                id,
                laundry_order_id,
                garment_type,
                quantity,
                ' . ($hasUnitPrice ? 'unit_price' : '0') . ',
                ' . ($hasSubtotal  ? 'subtotal'   : '0') . ',
                service_type,
                status,
                created_at,
                updated_at
            FROM laundry_items
        ');

        DB::statement('DROP TABLE laundry_items');
        DB::statement('ALTER TABLE laundry_items_new RENAME TO laundry_items');

        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        // Reversing back to enum is intentionally not supported —
        // existing data may contain custom garment types that would violate the constraint.
    }
};
