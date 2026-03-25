<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'class_id',
        'section_id',
        'name',
        'start_date',
        'end_date',
        'status',
        'is_result_published',
        'is_active',
    ];

    /* ===========================
       RELATIONSHIPS (REQUIRED)
       =========================== */

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function class()
    {
        // IMPORTANT: model name is Classes, not Class
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function subjects()
    {
        return $this->hasMany(ExamSubject::class);
    }
}
