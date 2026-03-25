<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherEducation extends Model
{
    protected $table = 'teacher_educations';

    protected $fillable = [
        'teacher_id',
        'school_id',
        'degree',
        'field_of_study',
        'institution',
        'board_or_university',
        'passing_year',
        'result',
        'percentage',
        'grade',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
