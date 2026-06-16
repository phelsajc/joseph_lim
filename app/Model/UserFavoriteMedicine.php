<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserFavoriteMedicine extends Model
{
    protected $table = 'user_favorite_medicines';

    protected $fillable = [
        'user_id',
        'medicine_id',
        'drug_name',
        'custom_generic_name',
        'default_qty',
        'default_bf_b',
        'default_bf_a',
        'default_l_b',
        'default_l_a',
        'default_s_b',
        'default_s_a',
        'default_bt',
        'default_dosage',
        'default_frequency',
        'default_duration',
        'default_remarks',
    ];

    protected $casts = [
        'medicine_id' => 'integer',
    ];
}
