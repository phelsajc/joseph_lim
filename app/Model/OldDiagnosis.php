<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OldDiagnosis extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $connection = 'old_emr';
    protected $table = "patients_visits_and_tests";
    protected $primaryKey = "visit_id";
    public $timestamps = false;
}


