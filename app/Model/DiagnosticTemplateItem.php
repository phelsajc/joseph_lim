<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiagnosticTemplateItem extends Model
{
    protected $fillable = [
        'diagnostic_template_id',
        'diagnostic_name',
        'category',
        'notes',
        'priority',
        'active',
        'sort_order',
    ];

    protected $casts = [
        'active' => 'boolean',
        'priority' => 'integer',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(DiagnosticTemplate::class, 'diagnostic_template_id');
    }
}

