<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SchoolClass;

class TeacherAssignment extends Model
{
    protected $fillable = [
        'school_id',
        'academic_year_id',
        'teacher_id',
        'class_id',
        'section_id',
        'subject_id',
        'is_class_teacher',
        'is_active',
        
    ];

    /* ========================
     | Relationships
     |========================
     */

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    // IMPORTANT: adjust model name if yours is different
    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
