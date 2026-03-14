<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('phone')->nullable();
            $table->string('position')->default('Cleaner');
            $table->enum('availability_status', ['available', 'busy', 'off_duty'])->default('available');
            $table->json('working_days')->nullable();
            $table->time('shift_start')->nullable();
            $table->time('shift_end')->nullable();
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->unsignedInteger('completed_jobs')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void { Schema::dropIfExists('staff'); }
};
