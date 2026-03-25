<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Accountant extends Model
{
    use SoftDeletes;

    protected $table = 'accountants';

    protected $fillable = [
        'user_id',
        'school_id',
        'name',
        'email',
        'phone',
        'is_active',
    ];

    /* =====================
       Relationships
    ===================== */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
