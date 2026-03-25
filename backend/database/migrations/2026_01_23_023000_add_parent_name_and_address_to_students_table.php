<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('students', function (Blueprint $table) {
        $table->string('parent_name')->nullable()->after('name');
        $table->text('address')->nullable()->after('parent_name');
    });
}

public function down()
{
    Schema::table('students', function (Blueprint $table) {
        $table->dropColumn(['parent_name', 'address']);
    });
}

 
};
