<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'school_id',
        'academic_year_id',
        'class_id',
        'section_id',
        'student_id',
        'attendance_date',
        'status',
    ];

    /**
     * Relationships (future use, safe to add now)
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
