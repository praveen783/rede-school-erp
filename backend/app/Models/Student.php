<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\ParentProfile;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'school_id',
        'academic_year_id',
        'class_id',
        'section_id',
        'admission_no',
        'name',
        'photo_path',
        'parent_name',
        'address',
        'gender',
        'category_id',
        'dob',
        'date_of_joining',
        'is_active',
    ];

    //  Class (SchoolClass)
    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    //  Section
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }
    public function school()
    {
        return $this->belongsTo(\App\Models\School::class);
    }
    //  Academic Year
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
    public function parent()
    {
        return $this->belongsTo(ParentProfile::class, 'parent_id');
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
