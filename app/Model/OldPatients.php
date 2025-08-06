<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OldPatients extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $connection = 'old_emr';
    protected $table = "patients";
    protected $primaryKey = "PatientID";
    public $timestamps = false;

}


