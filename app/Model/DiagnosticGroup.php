<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DiagnosticGroup extends Model
{
    protected $table = 'diagnostic_groups';

    public $timestamps = false;

    protected $fillable = [
        'appointment_id',
        'title',
        'lab_remarks',
        'request_date',
        'findings',
        'notes',
        'recommendations',
        'sort_order',
        'created_dt',
    ];

    public function diagnostics()
    {
        return $this->hasMany(Ancillary::class, 'diagnostic_group_id', 'id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
