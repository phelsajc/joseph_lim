<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionDiagnosisTemplateItem extends Model
{
    protected $fillable = [
        'prescription_diagnosis_template_id',
        'brand_name',
        'generic_name',
        'medicine_id',
        'quantity',
        'frequency',
        'duration',
        'instructions',
        'sort_order',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(PrescriptionDiagnosisTemplate::class, 'prescription_diagnosis_template_id');
    }
}
