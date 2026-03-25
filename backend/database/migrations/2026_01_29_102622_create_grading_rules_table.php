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
        Schema::create('grading_rules', function (Blueprint $table) {
            $table->id();

            // Marks range
            $table->integer('min_marks');
            $table->integer('max_marks');

            // Grade details
            $table->string('grade', 5);
            $table->decimal('grade_point', 4, 2);

            $table->timestamps();

            // Prevent overlapping rules (logical index)
            $table->index(['min_marks', 'max_marks']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grading_rules');
    }
};
