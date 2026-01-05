<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'food_id',
        // 'vendor_id',
        'day',
        'main_category_id',
        'available_time_start',
        'available_time_end',
    ];

    public function food()
    {
        return $this->belongsTo(Food::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

}
