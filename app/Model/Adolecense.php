<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Adolecense extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = "adolescents";
    protected $primaryKey = "id";
    public $timestamps = false;

}


