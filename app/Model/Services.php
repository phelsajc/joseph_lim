<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = "services";
    protected $primaryKey = "services_id";
    public $timestamps = false;

}


