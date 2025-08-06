<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Generics extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = "generics";
    protected $primaryKey = "id";
    public $timestamps = false;

}


