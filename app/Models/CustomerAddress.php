<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    protected $casts = [
        'user_id' => 'integer',
        'zone_id' => 'integer',
        'description' => 'string',
        'street_name' => 'string',
        'city_name' => 'string',
        'state_name' => 'string',
        'country_name' => 'string',
        'pincode' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
