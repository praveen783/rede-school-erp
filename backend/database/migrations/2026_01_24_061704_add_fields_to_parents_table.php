<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('parents', function (Blueprint $table) {

            if (!Schema::hasColumn('parents', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete()
                    ->after('id');
            }

            if (!Schema::hasColumn('parents', 'school_id')) {
                $table->foreignId('school_id')
                    ->nullable()
                    ->constrained('schools')
                    ->nullOnDelete()
                    ->after('user_id');
            }

            if (!Schema::hasColumn('parents', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('email');
            }

            if (!Schema::hasColumn('parents', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }


    public function down(): void
    {
        Schema::table('parents', function (Blueprint $table) {

            $table->dropForeign(['user_id']);
            $table->dropForeign(['school_id']);

            $table->dropColumn([
                'user_id',
                'school_id',
                'is_active'
            ]);

            $table->dropSoftDeletes();
        });
    }


};
