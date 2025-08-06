<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Ancillary extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = "ancillary";
    protected $primaryKey = "id";
    public $timestamps = false;

}


