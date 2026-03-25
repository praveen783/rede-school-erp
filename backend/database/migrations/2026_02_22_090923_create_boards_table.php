<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('boards', function (Blueprint $table) {

            $table->id();

            // Board name (CBSE, State Board, ICSE)
            $table->string('name')->unique();

            // Optional detailed description
            $table->text('description')->nullable();

            // Useful for enabling/disabling boards later
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('boards');
    }
};
