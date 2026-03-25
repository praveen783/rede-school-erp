<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {

            $table->unsignedBigInteger('class_id')->after('academic_year_id');
            $table->unsignedBigInteger('section_id')->after('class_id');

            $table->foreign('class_id')
                ->references('id')
                ->on('classes')
                ->onDelete('cascade');

            $table->foreign('section_id')
                ->references('id')
                ->on('sections')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {

            $table->dropForeign(['class_id']);
            $table->dropForeign(['section_id']);

            $table->dropColumn(['class_id', 'section_id']);
        });
    }
};