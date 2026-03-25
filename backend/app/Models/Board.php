<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Board extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'boards';

    /**
     * Mass assignable fields.
     */
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    /**
     * Cast attributes.
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relationship: A board has many syllabi
     */
    public function syllabi()
    {
        return $this->hasMany(Syllabus::class);
    }

    /**
     * Scope: Only active boards
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}