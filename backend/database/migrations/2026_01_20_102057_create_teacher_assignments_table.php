<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teacher_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('school_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('academic_year_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('teacher_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('class_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('section_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('subject_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique([
                'academic_year_id',
                'class_id',
                'section_id',
                'subject_id'
            ], 'unique_subject_assignment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_assignments');
    }
};
