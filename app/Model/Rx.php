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
    protected $primaryKey = "lab_test_id";
    public $timestamps = false;

}


