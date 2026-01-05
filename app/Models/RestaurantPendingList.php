<?php

namespace App\Models;

use App\Models\Vendor;
use App\Scopes\ZoneScope;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\ReportFilter;

class RestaurantPendingList extends Model
{
   use HasFactory;

    protected $table = 'restaurant_pending_lists';

    protected $fillable = [
        'f_name',
        'l_name',
        'email',
        'mobile_no',
        'password',
        'address',
        'latitude',
        'longitude',
        'encrypt_password',
        'confirm_status',
    ];

    protected $casts = [
        'f_name' => 'string',
        'l_name' => 'string',
        'email' => 'string',
        'mobile_no'=>'string',
        'password' => 'string',
        'address' => 'string',
        'latitude' => 'string',
        'longitude' => 'string',
        'confirm_status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

}
