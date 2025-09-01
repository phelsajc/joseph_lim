<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PeTemplate extends Model
{
    use SoftDeletes;

    protected $table = 'pe_templates';

    protected $fillable = [
        'name',
        'content',
        'type',
        'created_by',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    public $timestamps = false;

    /**
     * Get the user who created this template
     */
    public function creator()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    /**
     * Scope to get only active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get templates by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get custom templates created by a specific user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('created_by', $userId);
    }
}

