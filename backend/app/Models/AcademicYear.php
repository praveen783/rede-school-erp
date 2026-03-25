<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicYear extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'start_date',
        'end_date',
        'is_active',
        'status'
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function isActive(): bool
    {
        return (int) $this->is_active === 1;
    }

    // CLOSED = is_active = 0
    public function isClosed(): bool
    {
        return (int) $this->is_active === 0;
    }

    public function isReadOnly(): bool
    {
        return $this->status === 'closed' || $this->status === 'archived';
    }

    
}
