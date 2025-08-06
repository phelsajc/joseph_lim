<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Appointments extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = "appointments";
    protected $primaryKey = "id";
    public $timestamps = false;

}


