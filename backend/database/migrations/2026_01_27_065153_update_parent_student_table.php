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
        Schema::table('parent_student', function (Blueprint $table) {

            // Add school isolation
            $table->foreignId('school_id')
                ->after('student_id')
                ->constrained()
                ->cascadeOnDelete();

            // Add timestamps
            $table->timestamps();

            
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parent_student', function (Blueprint $table) {
            
            // Drop foreign key first
            $table->dropForeign(['school_id']);

            // Drop columns
            $table->dropColumn(['school_id', 'created_at', 'updated_at']);
        });
    }

};
