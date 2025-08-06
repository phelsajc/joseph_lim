<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Rxb extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = "rx_blank";
    protected $primaryKey = "lab_test_id";
    public $timestamps = false;

}


