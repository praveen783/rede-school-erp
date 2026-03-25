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
        Schema::create('fee_installment_plans', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('class_id');

            $table->string('name'); // e.g. Full Payment, Term-wise
            $table->unsignedTinyInteger('total_installments');

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('school_id');
            $table->index('academic_year_id');
            $table->index('class_id');

            // Uniqueness rule
            $table->unique(
                ['school_id', 'academic_year_id', 'class_id', 'name'],
                'fee_installment_plans_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_installment_plans');
    }


    
    
};
