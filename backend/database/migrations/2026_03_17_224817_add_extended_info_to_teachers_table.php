<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('date_of_joining');
            $table->text('address')->nullable()->after('date_of_birth');
            $table->string('qualification')->nullable()->after('address');
            $table->unsignedSmallInteger('experience_years')->nullable()->after('qualification');
            $table->string('primary_subject')->nullable()->after('experience_years');
            $table->string('secondary_subject')->nullable()->after('primary_subject');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn([
                'date_of_birth',
                'address',
                'qualification',
                'experience_years',
                'primary_subject',
                'secondary_subject',
            ]);
        });
    }
};
