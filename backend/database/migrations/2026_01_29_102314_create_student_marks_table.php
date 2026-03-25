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
        Schema::create('student_marks', function (Blueprint $table) {
            $table->id();

            // Context columns
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('academic_year_id');

            // Exam linkage
            $table->unsignedBigInteger('exam_id');
            $table->unsignedBigInteger('exam_subject_id');
            $table->unsignedBigInteger('student_id');

            // Marks
            $table->integer('internal_marks')->nullable();
            $table->integer('external_marks')->nullable();
            $table->integer('total_marks');

            // Result details
            $table->boolean('is_pass')->default(false);
            $table->string('grade', 5)->nullable();
            $table->decimal('grade_point', 4, 2)->nullable();

            $table->timestamps();

            // Indexes (important for performance)
            $table->index(['exam_id', 'student_id']);
            $table->index(['student_id', 'academic_year_id']);
            $table->index(['exam_subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_marks');
    }
};
