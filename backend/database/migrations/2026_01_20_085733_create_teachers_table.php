<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('school_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('employee_code');
            $table->string('name');

            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();

            $table->date('date_of_joining')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Unique per school
            $table->unique(['school_id', 'employee_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
