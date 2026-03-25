<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeInstallmentPlan extends Model
{
    use SoftDeletes;

    protected $table = 'fee_installment_plans';

    protected $fillable = [
        'student_fee_assignment_id',
        'school_id',
        'academic_year_id',
        'class_id',
        'name',
        'total_installments',
        'is_active',
    ];

    /* -----------------------------
       Relationships
    ----------------------------- */

    // Plan belongs to a fee assignment
    public function assignment()
    {
        return $this->belongsTo(
            StudentFeeAssignment::class,
            'student_fee_assignment_id'
        );
    }

    // Plan has many installments
    public function installments()
    {
        return $this->hasMany(
            FeeInstallment::class,
            'student_fee_assignment_id',
            'student_fee_assignment_id'
        );
    }
}
