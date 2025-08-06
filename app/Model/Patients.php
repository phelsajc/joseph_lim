<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Patients extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = "patients";
    protected $primaryKey = "id";
    public $timestamps = false;

}


