<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('garment_prices', function (Blueprint $table) {
            $table->id();
            $table->string('garment_type')->unique();   // e.g. shirt, trouser, dress
            $table->string('label');                    // Display label e.g. "Shirt"
            $table->decimal('price', 10, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('garment_prices');
    }
};
