<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->boolean('is_active')->default(0)->change();
        });
    }

    public function down()
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->boolean('is_active')->default(1)->change();
        });
    }

};
