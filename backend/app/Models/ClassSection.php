<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassSection extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'class_sections';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'school_id',
        'academic_year_id',
        'class_id',
        'section_id',
        'status',
    ];

    /**
     * ============================
     * Relationships
     * ============================
     */

    /**
     * ClassSection belongs to a School
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * ClassSection belongs to an Academic Year
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * ClassSection belongs to a Class
     */
    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
        // NOTE:
        // If your model name is `ClassModel` or `Classes`,
        // change SchoolClass::class accordingly
    }

    /**
     * ClassSection belongs to a Section
     */
    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
