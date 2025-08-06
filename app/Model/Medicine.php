<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = "medicines";
    protected $primaryKey = "id";
    public $timestamps = false;

}


