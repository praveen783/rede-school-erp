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
        Schema::create('syllabus_units', function (Blueprint $table) {

            $table->id();

            // Link to main syllabus
            $table->unsignedBigInteger('syllabus_id');

            // Unit Details
            $table->string('unit_title');
            $table->integer('unit_order')->default(1);

            // Academic Structure
            $table->text('learning_outcomes')->nullable();
            $table->integer('estimated_hours')->nullable();

            // Future progress tracking
            $table->boolean('is_completed')->default(false);

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Foreign Key
            |--------------------------------------------------------------------------
            */

            $table->foreign('syllabus_id')
                  ->references('id')
                  ->on('syllabi')
                  ->onDelete('cascade');

            /*
            |--------------------------------------------------------------------------
            | Optional: Prevent duplicate unit order per syllabus
            |--------------------------------------------------------------------------
            */

            $table->unique(
                ['syllabus_id', 'unit_order'],
                'unique_unit_order_per_syllabus'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('syllabus_units');
    }
};