<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorFoodOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'food_id',
        'pre_order',
        'instant_order',
    ];

    protected $casts = [
        'pre_order' => 'boolean',
        'instant_order' => 'boolean',
    ];
    // In App\Models\VendorFoodOrder.php

public function food()
{
    return $this->belongsTo(Food::class, 'food_id');
}

}
