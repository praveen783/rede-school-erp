<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->unsignedBigInteger('marked_by')
                  ->nullable()
                  ->after('status');

            $table->enum('marked_role', ['teacher','admin'])
                  ->nullable()
                  ->after('marked_by');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['marked_by', 'marked_role']);
        });
    }
};