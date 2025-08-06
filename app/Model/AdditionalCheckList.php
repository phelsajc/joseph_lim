<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AdditionalCheckList extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = "additional_checklist";
    protected $primaryKey = "id";
    public $timestamps = false;

}


