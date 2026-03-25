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
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();

            // Multi-school support
            $table->foreignId('school_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Academic year reference
            $table->foreignId('academic_year_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Class reference
            $table->foreignId('class_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Section (nullable if school has no sections)
            $table->foreignId('section_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            // Only one timetable active per class/year
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Prevent duplicate timetable for same class + section + year
            $table->unique([
                'academic_year_id',
                'class_id',
                'section_id'
            ], 'unique_class_timetable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetables');
    }
};