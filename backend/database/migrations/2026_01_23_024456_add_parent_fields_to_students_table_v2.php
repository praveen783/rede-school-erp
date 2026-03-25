<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'parent_name')) {
                $table->string('parent_name')->nullable()->after('name');
            }

            if (!Schema::hasColumn('students', 'address')) {
                $table->text('address')->nullable()->after('parent_name');
            }
        });
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'parent_name')) {
                $table->dropColumn('parent_name');
            }

            if (Schema::hasColumn('students', 'address')) {
                $table->dropColumn('address');
            }
        });
    }

};
