<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->unique([
                'school_id',
                'academic_year_id',
                'class_id',
                'section_id',
                'student_id',
                'attendance_date'
            ], 'attendance_unique_record');
        });
    }

    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendance_unique_record');
        });
    }
};