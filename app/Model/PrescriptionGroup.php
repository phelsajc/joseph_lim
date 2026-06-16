<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PrescriptionGroup extends Model
{
    protected $table = 'prescription_groups';

    public $timestamps = false;

    protected $fillable = [
        'appointment_id',
        'title',
        'sort_order',
        'created_dt',
    ];

    public function medicines()
    {
        return $this->hasMany(Rx::class, 'prescription_group_id', 'id')
            ->orderBy('sort_order')
            ->orderBy('rx_id');
    }
}
