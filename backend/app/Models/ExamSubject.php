<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSubject extends Model
{
    protected $table = 'exam_subjects';

    protected $fillable = [
        'exam_id',
        'subject_id',
        'max_marks',
        'pass_marks',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
