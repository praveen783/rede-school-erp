<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Teacher;

class Subject extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'is_active',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function classes()
    {
        return $this->belongsToMany(
            SchoolClass::class,
            'class_subjects',
            'subject_id',
            'class_id'
        );
    }
    public function teachers()
    {
        return $this->belongsToMany(
            Teacher::class,
            'teacher_subjects',
            'subject_id',
            'teacher_id'
        );
    }
    
    public function exams()
    {
        return $this->belongsToMany(
            Exam::class,
            'exam_subjects'
        )->withPivot(['max_marks', 'pass_marks']);
    }
    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class);
    }

}
