<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimetableEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'timetable_id',
        'day_of_week',
        'period_id',
        'subject_id',
        'teacher_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function timetable()
    {
        return $this->belongsTo(Timetable::class);
    }

    public function period()
    {
        return $this->belongsTo(Period::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        // If teacher table is separate model
        return $this->belongsTo(Teacher::class);
        
        // OR if teacher is inside users table:
        // return $this->belongsTo(User::class, 'teacher_id');
    }
}