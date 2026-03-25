<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mark extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'exam_id',
        'class_id',
        'section_id',
        'subject_id',
        'student_id',
        'marks_obtained',
        'is_absent',
    ];

    protected $casts = [
        'is_absent' => 'boolean',
        'marks_obtained' => 'decimal:2',
    ];
}
