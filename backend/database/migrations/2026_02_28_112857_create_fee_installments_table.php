<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_installments', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('student_fee_assignment_id');

            $table->unsignedInteger('installment_no');

            $table->decimal('amount', 10, 2);

            $table->enum('status', ['PENDING', 'PARTIAL', 'PAID'])
                  ->default('PENDING');

            $table->date('due_date')->nullable();

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            // Index
            $table->index('student_fee_assignment_id');

            // Optional but recommended foreign key
            $table->foreign('student_fee_assignment_id')
                  ->references('id')
                  ->on('student_fee_assignments')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_installments');
    }
};