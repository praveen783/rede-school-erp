<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('fee_structure_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('fee_structure_id');
            $table->unsignedBigInteger('fee_head_id');

            $table->decimal('amount', 10, 2);

            $table->timestamps();

            // Indexes
            $table->index('fee_structure_id');
            $table->index('fee_head_id');

            // Prevent duplicate fee heads in same structure
            $table->unique(
                ['fee_structure_id', 'fee_head_id'],
                'fee_structure_items_unique'
            );

            // Foreign keys
            $table->foreign('fee_structure_id')
                ->references('id')
                ->on('fee_structures')
                ->onDelete('cascade');

            $table->foreign('fee_head_id')
                ->references('id')
                ->on('fee_heads')
                ->onDelete('restrict');
        });
    }


    
    public function down(): void
    {
        Schema::dropIfExists('fee_structure_items');
    }

};
