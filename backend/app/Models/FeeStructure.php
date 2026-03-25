<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeStructure extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'class_id',
        'name',
        'is_active',
        
    ];

    protected $casts = [
        'school_id'        => 'integer',
        'academic_year_id' => 'integer',
        'class_id'         => 'integer',
    ];

    public function items()
    {
        return $this->hasMany(FeeStructureItem::class);
    }
    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

}
