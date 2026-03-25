<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('periods', function (Blueprint $table) {
            $table->id();

            // Multi-school support
            $table->foreignId('school_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Period details
            $table->string('name'); // Example: Period 1, Period 2, Lunch
            $table->time('start_time');
            $table->time('end_time');

            // Order of period in the day
            $table->unsignedInteger('order')->default(1);

            // Identify lunch/break
            $table->boolean('is_break')->default(false);

            // Active control
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Optional Indexes (for performance)
            $table->index(['school_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periods');
    }
};