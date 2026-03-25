<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exam_admit_cards', function (Blueprint $table) {
            $table->id();

            // Scope & ownership
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('exam_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id');

            // Release control
            $table->unsignedInteger('release_before_days')->default(10);
            $table->date('release_date')->nullable(); // manual override

            // Exam session & timing
            $table->enum('exam_session', ['FN', 'AN'])->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('duration_minutes');

            // Status
            $table->enum('status', ['Draft', 'Published'])->default('Draft');

            // Audit
            $table->unsignedBigInteger('created_by');

            $table->timestamps();
            $table->softDeletes();

            // 🔐 Uniqueness: one admit card per exam + class + section
            $table->unique(
                ['exam_id', 'class_id', 'section_id', 'academic_year_id'],
                'unique_exam_class_section_admit'
            );

            // Indexes for performance
            $table->index(['school_id', 'academic_year_id']);
            $table->index(['exam_id', 'class_id', 'section_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_admit_cards');
    }
};
