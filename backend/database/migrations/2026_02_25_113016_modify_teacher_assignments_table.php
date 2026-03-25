<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('teacher_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('subject_id')->nullable()->change();
            $table->boolean('is_class_teacher')->default(0)->after('subject_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('teacher_assignments', function (Blueprint $table) {
            $table->dropColumn('is_class_teacher');
            $table->unsignedBigInteger('subject_id')->nullable(false)->change();
        });
    }
};
