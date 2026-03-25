<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeHead extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'fee_heads';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'school_id',
        'name',
        'code',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * ============================
     * Relationships
     * ============================
     */

    /**
     * Fee head belongs to a school.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Fee head can be used in many fee structure items.
     */
    public function feeStructureItems()
    {
        return $this->hasMany(FeeStructureItem::class);
    }

    /**
     * Fee head can be used in many student fee items.
     */
    public function studentFeeItems()
    {
        return $this->hasMany(StudentFeeItem::class);
    }

    /**
     * ============================
     * Query Scopes
     * ============================
     */

    /**
     * Scope only active fee heads.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope fee heads by school.
     */
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }
}
