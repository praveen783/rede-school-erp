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
        Schema::table('student_fee_assignments', function (Blueprint $table) {

            // Add paid_amount if not exists
            if (!Schema::hasColumn('student_fee_assignments', 'paid_amount')) {
                $table->decimal('paid_amount', 10, 2)
                      ->default(0)
                      ->after('total_amount');
            }

            // Add due_amount if not exists
            if (!Schema::hasColumn('student_fee_assignments', 'due_amount')) {
                $table->decimal('due_amount', 10, 2)
                      ->default(0)
                      ->after('paid_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_fee_assignments', function (Blueprint $table) {

            if (Schema::hasColumn('student_fee_assignments', 'paid_amount')) {
                $table->dropColumn('paid_amount');
            }

            if (Schema::hasColumn('student_fee_assignments', 'due_amount')) {
                $table->dropColumn('due_amount');
            }
        });
    }
};