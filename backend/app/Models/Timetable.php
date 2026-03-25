<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Timetable extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Table Name
    |--------------------------------------------------------------------------
    */
    protected $table = 'timetables';

    /*
    |--------------------------------------------------------------------------
    | Mass Assignable Fields
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'school_id',
        'academic_year_id',
        'class_id',
        'section_id',
        'is_active',
    ];

    /*
    |--------------------------------------------------------------------------
    | Attribute Casting
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'school_id'        => 'integer',
        'academic_year_id' => 'integer',
        'class_id'         => 'integer',
        'section_id'       => 'integer',
        'is_active'        => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Timetable belongs to a School
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Timetable belongs to an Academic Year
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Timetable belongs to a Class
     */
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Timetable belongs to a Section (nullable)
     */
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Timetable has many entries (period-wise schedule)
     */
    public function entries()
    {
        return $this->hasMany(TimetableEntry::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: Only active timetable
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by school
     */
    public function scopeOfSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope: Filter by academic year
     */
    public function scopeOfAcademicYear($query, $yearId)
    {
        return $query->where('academic_year_id', $yearId);
    }

}