<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Rx_service extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = "servicesrendered";
    protected $primaryKey = "rendered_id";
    public $timestamps = false;

}


