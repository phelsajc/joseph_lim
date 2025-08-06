<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Attachments extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = "patients_attachments";
    protected $primaryKey = "AttachmentID";
    public $timestamps = false;

}


