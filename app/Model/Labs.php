<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Labs extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = "lab_tests";
    protected $primaryKey = "lab_test_id";
    public $timestamps = false;

}


