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
        Schema::create('timetable_entries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('timetable_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->enum('day_of_week', [
                'Monday','Tuesday','Wednesday',
                'Thursday','Friday','Saturday'
            ]);

            $table->foreignId('period_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('subject_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('teacher_id')
                  ->constrained('teachers')
                  ->cascadeOnDelete();

            $table->timestamps();

            // Prevent duplicate period per timetable
            $table->unique([
                'timetable_id',
                'day_of_week',
                'period_id'
            ], 'unique_period_per_day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable_entries');
    }
};