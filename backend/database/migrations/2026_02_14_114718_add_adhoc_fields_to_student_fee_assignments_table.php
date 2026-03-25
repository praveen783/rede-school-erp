<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_fee_assignments', function (Blueprint $table) {

            $table->string('title')->nullable()->after('assignment_type');
            $table->date('due_date')->nullable()->after('title');
            $table->text('remarks')->nullable()->after('due_date');

        });
    }

    public function down(): void
    {
        Schema::table('student_fee_assignments', function (Blueprint $table) {

            $table->dropColumn(['title', 'due_date', 'remarks']);

        });
    }
};