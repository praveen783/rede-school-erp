<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeePayment extends Model
{
    protected $fillable = [
        'school_id',
        'student_fee_assignment_id',
        'installment_no',
        'amount',

        // Razorpay fields
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',

        'payment_mode',
        'gateway',
        'status',
        'reference_no',
        'paid_on',
        'collected_by',
        'remarks',
        'receipt_path',
    ];

    protected $casts = [
        'paid_on' => 'date',
    ];

    public function assignment()
    {
        return $this->belongsTo(
            \App\Models\StudentFeeAssignment::class,
            'student_fee_assignment_id'
        );
    }
}