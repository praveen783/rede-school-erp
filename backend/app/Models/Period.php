<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'name',
        'start_time',
        'end_time',
        'order',
        'is_break',
        'is_active',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function timetableEntries()
    {
        return $this->hasMany(TimetableEntry::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    // Optional: formatted time range accessor
    public function getTimeRangeAttribute()
    {
        return date('h:i A', strtotime($this->start_time)) . 
               ' - ' . 
               date('h:i A', strtotime($this->end_time));
    }
}