<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SyllabusUnit extends Model
{
    use HasFactory;

    /**
     * Table name (optional but explicit is professional)
     */
    protected $table = 'syllabus_units';

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'syllabus_id',
        'unit_title',
        'unit_order',
        'learning_outcomes',
        'estimated_hours',
        'is_completed',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'is_completed' => 'boolean',
        'estimated_hours' => 'integer',
        'unit_order' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * A unit belongs to a syllabus
     */
    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes (ERP-Level Clean Practice)
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: Order units properly
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('unit_order', 'asc');
    }

    /**
     * Scope: Completed units
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * Scope: Pending units
     */
    public function scopePending($query)
    {
        return $query->where('is_completed', false);
    }
}