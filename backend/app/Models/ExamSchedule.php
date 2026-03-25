<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSchedule extends Model
{
    protected $fillable = [
        'school_id',
        'academic_year_id',
        'exam_id',
        'class_id',     // ✅ REQUIRED
        'section_id',   // ✅ REQUIRED
        'subject_id',
        'exam_date',
        'start_time',
        'end_time',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}
