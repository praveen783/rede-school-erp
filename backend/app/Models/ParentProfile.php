<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\School;
use App\Models\Student;

class ParentProfile extends Model
{
    use SoftDeletes;

    protected $table = 'parents';

    protected $fillable = [
        'user_id',
        'school_id',
        'name',
        'phone',
        'email',
        'occupation',
        'is_active',
    ];

    /**
     * Parent belongs to a school
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Parent → Students (ONE parent, MANY students)
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'parent_id');
    }
}
