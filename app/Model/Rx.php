<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Rx extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = "rx";
    protected $primaryKey = "rx_id";
    public $timestamps = false;

    public function prescriptionGroup()
    {
        return $this->belongsTo(PrescriptionGroup::class, 'prescription_group_id', 'id');
    }
}


