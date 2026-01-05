<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    use HasFactory;
    
    public function getDataAttribute($value)
    {
        return json_decode($value, true);
    }

    public function getCreatedAtAttribute($value)
    {
        return date('Y-m-d H:i:s',strtotime($value));
    }
    public function getUpdatedAtAttribute($value)
    {
        return date('Y-m-d H:i:s', strtotime($value));  
    }
}


// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

// class UserNotification extends Model
// {
//     use HasFactory;

//     // Ensure both created_at and updated_at fields are handled consistently
//     protected $casts = [
//         'created_at' => 'datetime:Y-m-d H:i:s', // Ensures created_at is formatted as 'Y-m-d H:i:s'
//         'updated_at' => 'datetime:Y-m-d H:i:s', // Ensures updated_at is formatted the same way
//     ];
 
//     public function getDataAttribute($value)
//     {
//         return json_decode($value, true);
//     }
 
//     public function getCreatedAtAttribute($value)
//     {
//         return date('Y-m-d H:i:s', strtotime($value));  
//     }

//     // Custom accessor for 'updated_at' field, ensuring it matches 'created_at' format
//     public function getUpdatedAtAttribute($value)
//     {
//         return date('Y-m-d H:i:s', strtotime($value)); // Customize the format for updated_at
//     }
// }

