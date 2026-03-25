<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('fee_heads', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('school_id');

            $table->string('name');
            $table->string('code');

            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Indexes & constraints
            $table->unique(['school_id', 'code'], 'fee_heads_school_code_unique');
            $table->index('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_heads');
    }

};
