<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AppointmentVitals extends Model
{
    protected $table = 'appointment_vitals';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'appointment_id',
        'patientid',
        'recorded_at',
        'recorded_by',
        'vit_sys',
        'vit_dia',
        'weight',
        'height',
        'bmi',
        'vit_temp',
        'vit_cr',
        'vit_rr',
        'o2_stat',
    ];
}
