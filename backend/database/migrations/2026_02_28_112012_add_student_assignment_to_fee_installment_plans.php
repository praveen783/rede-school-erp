<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_installment_plans', function (Blueprint $table) {

            if (!Schema::hasColumn('fee_installment_plans', 'student_fee_assignment_id')) {
                $table->unsignedBigInteger('student_fee_assignment_id')
                      ->nullable()
                      ->after('id');

                $table->index('student_fee_assignment_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fee_installment_plans', function (Blueprint $table) {

            if (Schema::hasColumn('fee_installment_plans', 'student_fee_assignment_id')) {
                $table->dropColumn('student_fee_assignment_id');
            }
        });
    }
};