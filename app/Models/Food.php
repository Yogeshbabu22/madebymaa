<?php

namespace App\Models;

use App\Scopes\ZoneScope;
use Illuminate\Support\Str;
use App\Scopes\RestaurantScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\ReportFilter;

class Food extends Model
{
    use HasFactory , ReportFilter;
    
       protected $table = 'food'; 

    protected $casts = [
        'tax' => 'float',
        'price' => 'float',
        'status' => 'integer',
        'discount' => 'float',
        'avg_rating' => 'float',
        'set_menu' => 'integer',
        'category_id' => 'integer',
        'restaurant_id' => 'integer',
        'reviews_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'veg' => 'integer',
        'min' => 'integer',
        'max' => 'integer',
        'maximum_cart_quantity' => 'integer',
        'recommended' => 'integer',
        'order_count'=>'integer',
        'rating_count'=>'integer',
        'is_halal'=>'integer',
        'new_available_times',
        'super_category_ids' => 'array'
    ];



//  public function restaurant()
//     {
//         return $this->belongsTo(Restaurant::class, 'restaurant_id');
//     }
    
    

    public function logs()
    {
        return $this->hasMany(Log::class,'model_id')->where('model','Food');
    }

    public function scopeRecommended($query)
    {
        return $query->where('recommended',1);
    }

    public function carts()
    {
        return $this->morphMany(Cart::class, 'item');
    }

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }


    public function scopeActive($query)
    {
        return $query->where('status', 1)->whereHas('restaurant', function ($query) {
            return $query->where('status', 1);
        });
    }

    public function scopeAvailable($query,$time)
    {
        $query->where(function($q)use($time){
            $q->where('available_time_starts','<=',$time)->where('available_time_ends','>=',$time);
        });
    }

    public function scopePopular($query)
    {
        return $query->orderBy('order_count', 'desc');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->latest();
    }

    public function rating()
    {
        return $this->hasMany(Review::class)
            ->select(DB::raw('avg(rating) average, count(food_id) rating_count, food_id'))
            ->groupBy('food_id');
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function orders()
    {
        return $this->hasMany(OrderDetail::class);
    }


    protected static function booted()
    {
        // dd( app()->getLocale());
        if (auth('vendor')->check() || auth('vendor_employee')->check()) {
            static::addGlobalScope(new RestaurantScope);
        }

        static::addGlobalScope(new ZoneScope);

        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
                return $query->where('locale', app()->getLocale());
            }]);
        });
    }


    // public function scopeType($query, $type)
    // {
    //     if ($type == 'veg') {
    //         return $query->where('veg', true);
    //     } else if ($type == 'non_veg') {
    //         return $query->where('veg', false);
    //     }

    //     return $query;
    // }

public function scopeType($query, $type)
{
    // Handle case for 'veg', 'non_veg', and 'all'
    if ($type == 'veg') {
        return $query->where('veg', true);
    } elseif ($type == 'non_veg') {
        return $query->where('veg', false);
    } elseif ($type == 'all') {
        // No filtering for 'all'
        return $query;
    }

    // Default query if invalid type is passed
    return $query;
}


    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::created(function ($food) {
            // Use original name attribute to avoid translation issues
            $name = $food->getOriginal('name') ?? $food->getAttributes()['name'] ?? $food->name;
            $food->slug = $food->generateSlug($name);
            $food->save();
        });
    }
    public function generateSlug($name)
    {
        if (empty($name)) {
            $name = 'food-' . $this->id;
        }
        $slug = Str::slug($name);
        if ($max_slug = static::where('slug', 'like',"{$slug}%")->latest('id')->value('slug')) {

            if($max_slug == $slug) return "{$slug}-2";

            $max_slug = explode('-',$max_slug);
            $count = array_pop($max_slug);
            if (isset($count) && is_numeric($count)) {
                $max_slug[]= ++$count;
                return implode('-', $max_slug);
            }
        }
        return $slug;
    }


    public function getNameAttribute($value){
        if (count($this->translations) > 0) {
            // info(count($this->translations));
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'name') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    public function getDescriptionAttribute($value){
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'description') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

 public function schedules()
    {
        return $this->hasMany(FoodSchedule::class);
    }
    // In App\Models\Food.php

public function vendorFoodOrders()
{
    return $this->hasMany(VendorFoodOrder::class, 'food_id');
}

// public function scopeCurrentlyAvailable($query, $mealSlug = null) {
//     $now = now();
//     $currentDay = ucfirst(strtolower($now->format('l')));
//     $currentTime = $now->format('H:i');
//     $mainCategory = null;
//     if ($mealSlug) {
//         $mainCategory = \App\Models\MainCategory::where('slug', $mealSlug)
//             ->orWhereRaw('LOWER(name) = ?', [strtolower($mealSlug)])
//             ->first();
//     } else {
//         // If no mealSlug, auto-detect from MainCategory time slots
//         $possible = \App\Models\MainCategory::all();
//         foreach ($possible as $mcat) {
//             if ($mcat->start_time && $mcat->end_time && $currentTime >= $mcat->start_time->format('H:i') && $currentTime <= $mcat->end_time->format('H:i')) {
//                 $mainCategory = $mcat;
//                 break;
//             }
//         }
//     }
//     if (!$mainCategory) return $query->whereRaw('0=1');
//     $mealCategoryId = $mainCategory->id;
//     return $query->whereHas('schedules', function ($q) use ($currentDay, $currentTime, $mealCategoryId) {
//         $q->where('day', $currentDay)
//           ->where('main_category_id', $mealCategoryId)
//           ->where('available_time_start', '<=', $currentTime)
//           ->where('available_time_end', '>=', $currentTime);
//     });
// }

public function scopeCurrentlyAvailable($query, $mealSlug = null) {
    // dd("bhdfnhd");
    $now = now();
    $currentDay = strtolower($now->format('l')); // monday, tuesday, etc.
    $currentTime = $now->format('H:i');
    $mainCategory = null;

    // Step 1: Get MainCategory either from mealSlug or auto-detect from current time
    if ($mealSlug) {
        // If mealSlug is provided (e.g., API request with meal=breakfast)
        $mainCategory = \App\Models\MainCategory::where('slug', $mealSlug)
            ->orWhereRaw('LOWER(name) = ?', [strtolower($mealSlug)])
            ->first();
    } else {
        // Auto-detect MainCategory based on current time slot
        // e.g., if current time is 8:00 and Breakfast time is 7-11, it will detect Breakfast
        $possible = \App\Models\MainCategory::all();
        foreach ($possible as $mcat) {
            if ($mcat->start_time && $mcat->end_time && $currentTime >= $mcat->start_time->format('H:i') && $currentTime <= $mcat->end_time->format('H:i')) {
                $mainCategory = $mcat;
                break;
            }
        }
    }

    // If no MainCategory found, return empty results
    if (!$mainCategory) return $query->whereRaw('0=1');

    // Step 2: Verify current time is within MainCategory's time slot
    // This ensures products don't show outside their MainCategory time window
    // Example: If Breakfast time is 7-11 and current time is 12:00, don't show Breakfast products
    if ($mainCategory->start_time && $mainCategory->end_time) {
        $categoryStartTime = $mainCategory->start_time->format('H:i');
        $categoryEndTime = $mainCategory->end_time->format('H:i');

        // If current time is NOT within MainCategory time slot, return empty results
        if ($currentTime < $categoryStartTime || $currentTime > $categoryEndTime) {
            return $query->whereRaw('0=1');
        }
    }

    $mealCategoryId = $mainCategory->id;
    $mealCategoryName = strtolower($mainCategory->name);
    $currentDayCapitalized = ucfirst($currentDay); // Monday, Tuesday, etc.

    // Step 3: Check availability for the specific day and MainCategory
    // This checks if food is available=true for the current day and MainCategory
    // Check both new_available_times JSON column AND FoodSchedule table (backward compatibility)
    return $query->where(function($q) use ($currentDay, $currentDayCapitalized, $mealCategoryName, $mealCategoryId, $currentTime) {
        // Option 1: Check new_available_times JSON column (new format)
        // Format: {"monday": {"breakfast": {"available": true, "main_category_id": 4}, ...}}
        $q->where(function($jsonQuery) use ($currentDay, $mealCategoryName) {
            $jsonQuery->whereNotNull('new_available_times')
                ->whereRaw('JSON_EXTRACT(new_available_times, ?) IS NOT NULL', ["$.{$currentDay}.{$mealCategoryName}"])
                ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(new_available_times, ?)) = ?', ["$.{$currentDay}.{$mealCategoryName}.available", 'true']);
        })
        // Option 2: Check FoodSchedule table (old format, backward compatibility)
        ->orWhereHas('schedules', function ($scheduleQuery) use ($currentDayCapitalized, $mealCategoryId, $currentTime) {
            $scheduleQuery->where('day', $currentDayCapitalized)
                ->where('main_category_id', $mealCategoryId)
                ->where('available_time_start', '<=', $currentTime)
                ->where('available_time_end', '>=', $currentTime);
        });
    });
}


}
