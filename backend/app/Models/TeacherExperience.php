<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherExperience extends Model
{
    protected $table = 'teacher_experiences';

    protected $fillable = [
        'teacher_id',
        'school_id',
        'organization',
        'designation',
        'department',
        'from_date',
        'to_date',
        'is_current',
        'responsibilities',
        'leaving_reason',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'from_date'  => 'date',
        'to_date'    => 'date',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
