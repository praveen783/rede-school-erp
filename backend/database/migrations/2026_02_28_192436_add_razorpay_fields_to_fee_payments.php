<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {

    public function up(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {

            $table->string('razorpay_order_id')->nullable()->after('amount');
            $table->string('razorpay_payment_id')->nullable()->after('razorpay_order_id');
            $table->string('razorpay_signature')->nullable()->after('razorpay_payment_id');

            $table->enum('gateway', ['OFFLINE','RAZORPAY'])
                  ->default('OFFLINE')
                  ->after('payment_mode');

            $table->enum('status', ['CREATED','SUCCESS','FAILED'])
                  ->default('SUCCESS')
                  ->after('gateway');
        });
    }

    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropColumn([
                'razorpay_order_id',
                'razorpay_payment_id',
                'razorpay_signature',
                'gateway',
                'status'
            ]);
        });
    }
};