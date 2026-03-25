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
        Schema::create('student_fee_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('student_fee_assignment_id');
            $table->unsignedBigInteger('fee_head_id');

            $table->decimal('amount', 10, 2);

            $table->enum('source', ['BASE', 'OVERRIDE']);

            $table->timestamps();

            // Indexes
            $table->index('student_fee_assignment_id');
            $table->index('fee_head_id');
            $table->index('source');

            // Prevent duplicate fee heads per assignment
            $table->unique(
                ['student_fee_assignment_id', 'fee_head_id'],
                'student_fee_items_unique'
            );

            // Foreign keys
            $table->foreign('student_fee_assignment_id')
                ->references('id')
                ->on('student_fee_assignments')
                ->onDelete('cascade');

            $table->foreign('fee_head_id')
                ->references('id')
                ->on('fee_heads')
                ->onDelete('restrict');
        });
    }



    public function down(): void
    {
        Schema::dropIfExists('student_fee_items');
    }

};
