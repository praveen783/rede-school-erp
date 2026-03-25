<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\TeacherAssignment;

class Section extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'class_id',
        'name',
    ];
    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class);
    }
}
