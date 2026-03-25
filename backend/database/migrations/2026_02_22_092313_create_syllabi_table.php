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
        Schema::create('syllabi', function (Blueprint $table) {

            $table->id();

            // Multi-school isolation
            $table->unsignedBigInteger('school_id');

            // Academic year mapping
            $table->unsignedBigInteger('academic_year_id');

            // Board (CBSE / State / ICSE)
            $table->unsignedBigInteger('board_id');

            // Class & Subject mapping
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('subject_id');

            // Syllabus Info
            $table->string('title');
            $table->text('description')->nullable();

            // Status control
            $table->boolean('is_active')->default(true);

            // Who created it
            $table->unsignedBigInteger('created_by');

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Foreign Keys
            |--------------------------------------------------------------------------
            */

            $table->foreign('school_id')
                  ->references('id')
                  ->on('schools')
                  ->onDelete('cascade');

            $table->foreign('academic_year_id')
                  ->references('id')
                  ->on('academic_years')
                  ->onDelete('cascade');

            $table->foreign('board_id')
                  ->references('id')
                  ->on('boards')
                  ->onDelete('restrict');

            $table->foreign('class_id')
                  ->references('id')
                  ->on('classes')
                  ->onDelete('cascade');

            $table->foreign('subject_id')
                  ->references('id')
                  ->on('subjects')
                  ->onDelete('cascade');

            /*
            |--------------------------------------------------------------------------
            | UNIQUE CONSTRAINT (VERY IMPORTANT)
            |--------------------------------------------------------------------------
            | Prevent duplicate syllabus per:
            | school + academic year + class + subject
            */

            $table->unique(
                ['school_id', 'academic_year_id', 'class_id', 'subject_id'],
                'unique_syllabus_per_class_subject_year'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('syllabi');
    }
};