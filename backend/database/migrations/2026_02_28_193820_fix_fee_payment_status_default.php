<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    public function up(): void
    {
        DB::statement("
            ALTER TABLE fee_payments
            MODIFY status ENUM('CREATED','SUCCESS','FAILED')
            NOT NULL DEFAULT 'CREATED'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE fee_payments
            MODIFY status ENUM('CREATED','SUCCESS','FAILED')
            NOT NULL DEFAULT 'SUCCESS'
        ");
    }
};