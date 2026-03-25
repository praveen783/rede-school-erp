<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SyllabusResource extends Model
{
    use HasFactory;

    /**
     * Table name (explicit for clarity)
     */
    protected $table = 'syllabus_resources';

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'syllabus_id',
        'resource_type',
        'resource_title',
        'resource_path',
        'uploaded_by',
    ];
    protected $appends = ['resource_url'];
    
    public function getResourceUrlAttribute()
    {
        if (!$this->resource_path) {
            return null;
        }

        if (in_array($this->resource_type, ['pdf', 'document'])) {
            return asset('storage/' . $this->resource_path);
        }

        return $this->resource_path;
    }
    
    /**
     * Cast attributes
     */
    protected $casts = [
        'syllabus_id' => 'integer',
        'uploaded_by' => 'integer',
    ];

    

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * A resource belongs to a syllabus
     */
    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }

    /**
     * A resource belongs to a user (uploader)
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes (Professional ERP Practice)
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: Only PDF resources
     */
    public function scopePdf($query)
    {
        return $query->where('resource_type', 'pdf');
    }

    /**
     * Scope: Only link resources
     */
    public function scopeLink($query)
    {
        return $query->where('resource_type', 'link');
    }

    /**
     * Scope: Only video resources
     */
    public function scopeVideo($query)
    {
        return $query->where('resource_type', 'video');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if resource is file-based
     */
    public function isFile()
    {
        return in_array($this->resource_type, ['pdf', 'document']);
    }

    /**
     * Check if resource is external link
     */
    public function isExternal()
    {
        return in_array($this->resource_type, ['link', 'video']);
    }
}