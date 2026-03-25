<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'name',
        'description',
        'is_active'
    ];

    /**
     * Category belongs to a school
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Category has many students
     */
    public function students()
    {
        return $this->hasMany(Student::class);
    }
}