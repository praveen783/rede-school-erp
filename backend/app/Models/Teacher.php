<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Subject;

class Teacher extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'user_id',
        'employee_code',
        'name',
        'email',
        'phone',
        'gender',
        'date_of_joining',
        'date_of_birth',
        'address',
        'qualification',
        'experience_years',
        'primary_subject',
        'secondary_subject',
        'is_active',
    ];

    public function subjects()
{
    return $this->belongsToMany(
        Subject::class,
        'teacher_subjects',
        'teacher_id',
        'subject_id'
    );
}
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function assignments()
    {
        return $this->hasMany(TeacherAssignment::class);
    }

    public function educations()
    {
        return $this->hasMany(TeacherEducation::class)->orderBy('passing_year', 'desc');
    }

    public function experiences()
    {
        return $this->hasMany(TeacherExperience::class)->orderBy('from_date', 'desc');
    }
}
