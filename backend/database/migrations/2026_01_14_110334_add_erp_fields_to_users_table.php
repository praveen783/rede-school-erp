<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Role Based Access Control
            $table->string('role')->default('student')->after('password')->index();

            // Account lifecycle
            $table->boolean('is_active')->default(true)->after('role');

            // Audit & tracking
            $table->timestamp('last_login_at')->nullable()->after('is_active');

            // Soft delete for compliance
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'is_active',
                'last_login_at',
                'deleted_at',
            ]);
        });
    }
};
