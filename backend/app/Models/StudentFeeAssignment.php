<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentFeeAssignment extends Model
{
    protected $table = 'student_fee_assignments';

    protected $fillable = [
        'school_id',
        'student_id',
        'academic_year_id',
        'class_id',
        'fee_structure_id',
        'assignment_type',
        'title',        
        'due_date',     
        'remarks',      
        'base_amount',
        'override_amount',
        'total_amount',
        'paid_amount',
        'due_amount',
        'status',
        'is_active',
        'assigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    public function class()
    {
        return $this->belongsTo(\App\Models\SchoolClass::class, 'class_id');
    }
    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
    public function installmentPlan()
    {
        return $this->belongsTo(
            \App\Models\FeeInstallmentPlan::class,
            'installment_plan_id'
        );
    }
    public function installments()
    {
        return $this->hasMany(
            \App\Models\FeeInstallment::class,
            'student_fee_assignment_id'
        );
    }
    public function payments()
    {
        return $this->hasMany(\App\Models\FeePayment::class);
    }
}