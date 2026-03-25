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
        Schema::create('categories', function (Blueprint $table) {

            $table->id();

            // Multi-school isolation
            $table->foreignId('school_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Category Name (SC, ST, BC, OBC, General)
            $table->string('name');

            // Optional description
            $table->string('description')->nullable();

            // Status (Active / Inactive)
            $table->boolean('is_active')->default(true);

            // Timestamps
            $table->timestamps();

            // Prevent duplicate category per school
            $table->unique(['school_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};