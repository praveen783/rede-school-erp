<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();

            $table->string('organization');        // School / Institution name
            $table->string('designation');         // Role: e.g. Senior Teacher, HOD
            $table->string('department')->nullable(); // e.g. Science Dept
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();    // null = currently working
            $table->boolean('is_current')->default(false);
            $table->text('responsibilities')->nullable(); // Brief description
            $table->string('leaving_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_experiences');
    }
};
