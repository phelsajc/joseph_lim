<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Vaccinations extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = "vaccinations";
    protected $primaryKey = "id";
    public $timestamps = false;

}


