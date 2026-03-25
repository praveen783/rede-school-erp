<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('class_id');

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('school_id');
            $table->index('academic_year_id');
            $table->index('class_id');

            // Uniqueness rule (VERY IMPORTANT)
            $table->unique(
                ['school_id', 'academic_year_id', 'class_id'],
                'fee_structures_unique_per_class_year'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
    }

};
