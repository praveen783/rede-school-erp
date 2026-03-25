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
        Schema::create('syllabus_resources', function (Blueprint $table) {

            $table->id();

            // Link to syllabus
            $table->unsignedBigInteger('syllabus_id');

            // Resource information
            $table->string('resource_type'); 
            // Example: pdf, link, video, document

            $table->string('resource_title');

            $table->string('resource_path');
            // For PDF: storage path
            // For link/video: URL

            $table->unsignedBigInteger('uploaded_by');

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Foreign Keys
            |--------------------------------------------------------------------------
            */

            $table->foreign('syllabus_id')
                  ->references('id')
                  ->on('syllabi')
                  ->onDelete('cascade');

            // Optional: If you have users table
            $table->foreign('uploaded_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('syllabus_resources');
    }
};