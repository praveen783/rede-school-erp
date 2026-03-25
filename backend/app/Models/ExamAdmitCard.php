<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamAdmitCard extends Model
{
    use SoftDeletes;

    protected $table = 'exam_admit_cards';

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'exam_id',
        'student_id',
        'class_id',
        'section_id',
        'release_before_days',
        'release_date',
        'status',
        'created_by',
    ];

    /**
     * Subjects linked to this admit card
     */
    public function subjects()
    {
        return $this->hasMany(ExamAdmitCardSubject::class);
    }
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

}
