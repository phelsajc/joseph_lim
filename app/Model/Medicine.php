<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    protected $table = "medicines";
    protected $primaryKey = "id";
    public $timestamps = false;

    protected $fillable = [
        'medicine_name',
        'generic_name',
        'generic_id',
        'unit',
        'isincluded',
        'default_qty',
        'default_bf_b',
        'default_bf_a',
        'default_l_b',
        'default_l_a',
        'default_s_b',
        'default_s_a',
        'default_bt',
        'default_remarks',
        'created_at',
    ];
}


