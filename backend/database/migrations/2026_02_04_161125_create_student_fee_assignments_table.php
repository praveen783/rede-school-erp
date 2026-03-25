<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('student_fee_assignments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();

            $table->unsignedBigInteger('fee_structure_id')->nullable();
            $table->unsignedBigInteger('installment_plan_id')->nullable();

            $table->enum('assignment_type', ['ACADEMIC', 'ADHOC']);

            $table->decimal('base_amount', 10, 2)->default(0);
            $table->decimal('override_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);

            $table->enum('status', ['UNPAID', 'PARTIAL', 'PAID'])->default('UNPAID');

            $table->boolean('is_active')->default(true);
            $table->timestamp('assigned_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('school_id');
            $table->index('student_id');
            $table->index('assignment_type');
            $table->index('status');
            $table->index('is_active');

            // Foreign keys
            $table->foreign('student_id')
                ->references('id')
                ->on('students')
                ->onDelete('cascade');

            $table->foreign('fee_structure_id')
                ->references('id')
                ->on('fee_structures')
                ->onDelete('set null');

            $table->foreign('installment_plan_id')
                ->references('id')
                ->on('fee_installment_plans')
                ->onDelete('set null');
        });
    }


    
    public function down(): void
    {
        Schema::dropIfExists('student_fee_assignments');
    }

};
