<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('laundry_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->decimal('weight', 6, 2)->nullable();
            $table->unsignedInteger('garment_count')->nullable();
            $table->enum('detergent_type', ['standard','hypoallergenic','eco','customer_supplied'])->default('standard');
            $table->text('special_instructions')->nullable();
            $table->boolean('express_service')->default(false);
            $table->timestamps();
        });

        Schema::create('laundry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laundry_order_id')->constrained()->cascadeOnDelete();
            $table->enum('garment_type', ['shirt','trouser','dress','bedsheet','curtain','others']);
            $table->unsignedInteger('quantity')->default(1);
            $table->string('service_type')->nullable();
            $table->enum('status', ['received','washing','drying','ironing','ready','delivered'])->default('received');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laundry_items');
        Schema::dropIfExists('laundry_orders');
    }
};
