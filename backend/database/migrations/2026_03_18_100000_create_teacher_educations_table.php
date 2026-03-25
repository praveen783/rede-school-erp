<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();

            $table->string('degree');              // e.g. B.Ed, M.Sc, B.Tech
            $table->string('field_of_study')->nullable();  // e.g. Mathematics, Computer Science
            $table->string('institution');         // College/University name
            $table->string('board_or_university')->nullable(); // Affiliating university
            $table->year('passing_year')->nullable();
            $table->string('result')->nullable();  // e.g. "First Class", "Distinction"
            $table->decimal('percentage', 5, 2)->nullable(); // e.g. 78.50
            $table->string('grade')->nullable();   // e.g. A+, O
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_educations');
    }
};
