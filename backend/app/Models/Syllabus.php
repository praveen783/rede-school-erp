<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Syllabus extends Model
{
    use HasFactory;

    /**
     * Table associated with the model.
     */
    protected $table = 'syllabi';

    /**
     * Mass assignable fields.
     */
    protected $fillable = [
        'school_id',
        'academic_year_id',
        'board_id',
        'class_id',
        'subject_id',
        'title',
        'description',
        'is_active',
        'created_by',
    ];

    /**
     * Cast attributes.
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // A syllabus belongs to a school
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    // A syllabus belongs to an academic year
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    // A syllabus belongs to a board (CBSE / State / ICSE)
    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    // A syllabus belongs to a class
    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    // A syllabus belongs to a subject
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    // A syllabus has many units
    public function units()
    {
        return $this->hasMany(SyllabusUnit::class);
    }

    // A syllabus has many resources
    public function resources()
    {
        return $this->hasMany(SyllabusResource::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes (Professional ERP Practice)
    |--------------------------------------------------------------------------
    */

    // Only active syllabi
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Filter by school
    public function scopeOfSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    // Filter by academic year
    public function scopeOfAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }
}