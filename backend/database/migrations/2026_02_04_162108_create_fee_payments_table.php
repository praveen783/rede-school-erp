<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('student_fee_assignment_id');

            // Optional installment reference (for reporting)
            $table->unsignedTinyInteger('installment_no')->nullable();

            $table->decimal('amount', 10, 2);

            $table->enum('payment_mode', [
                'CASH',
                'UPI',
                'CARD',
                'BANK_TRANSFER',
                'CHEQUE'
            ]);

            $table->string('reference_no')->nullable(); // txn id / cheque no
            $table->date('paid_on');

            $table->unsignedBigInteger('collected_by'); // admin user id
            $table->text('remarks')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('school_id');
            $table->index('student_fee_assignment_id');
            $table->index('payment_mode');
            $table->index('paid_on');

            // Foreign keys
            $table->foreign('student_fee_assignment_id')
                ->references('id')
                ->on('student_fee_assignments')
                ->onDelete('cascade');
        });
    }


    
    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
    }

};
