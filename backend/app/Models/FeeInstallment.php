<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeInstallment extends Model
{
    protected $table = 'fee_installments';

    protected $fillable = [
        'student_fee_assignment_id',
        'installment_no',
        'amount',
        'status',
        'due_date',
        'paid_at',
    ];

    /* -----------------------------
       Relationships
    ----------------------------- */

    // Installment belongs to assignment
    public function assignment()
    {
        return $this->belongsTo(
            StudentFeeAssignment::class,
            'student_fee_assignment_id'
        );
    }
}
