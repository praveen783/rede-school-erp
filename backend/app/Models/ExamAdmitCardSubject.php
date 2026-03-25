<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAdmitCardSubject extends Model
{
    protected $table = 'exam_admit_card_subjects';

    protected $fillable = [
        'exam_admit_card_id',
        'subject_id',
        'exam_date',
    ];

    /**
     * Parent admit card
     */
    public function admitCard()
    {
        return $this->belongsTo(ExamAdmitCard::class);
    }
}
