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
        Schema::table('students', function (Blueprint $table) {

            // Add category reference
            $table->foreignId('category_id')
                  ->nullable()
                  ->after('gender')
                  ->constrained('categories')
                  ->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {

            // Drop foreign key first
            $table->dropForeign(['category_id']);

            // Drop column
            $table->dropColumn('category_id');

        });
    }
};