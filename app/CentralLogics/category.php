<?php

namespace App\CentralLogics;

use App\Models\Category;
use App\Models\Food;
use App\Models\Restaurant;

class CategoryLogic
{
    public static function parents()
    {
        return Category::where('position', 0)->get();
    }

    public static function child($parent_id)
    {
        return Category::where(['parent_id' => $parent_id])->get();
    }

    // public static function products(array $additional_data)
    // {
    //     $paginator = Food::whereHas('restaurant', function($query)use($additional_data){
    //         return $query->whereIn('zone_id', $additional_data['zone_id']);
    //     })
    //     ->whereHas('category',function($q)use($additional_data){
    //         return $q->whereId($additional_data['category_id'])->orWhere('parent_id', $additional_data['category_id']);
    //     })

    //     ->when($additional_data['veg'] == 1 && $additional_data['non_veg'] == 0 , function($query) {
    //         $query->where('veg',1);
    //     })

    //     ->when($additional_data['non_veg'] == 1 && $additional_data['veg'] == 0  , function($query) {
    //         $query->where('veg',0);
    //     })
    //     ->when($additional_data['avg_rating'] > 0 , function($query) use($additional_data) {
    //         $query->where('avg_rating','>=' , $additional_data['avg_rating']);
    //     })

    //     ->when($additional_data['top_rated'] == 1 , function($query) {
    //         $query->where('avg_rating','>=' , 4);
    //     })

    //     ->when($additional_data['end_price'] > 0 , function($query)  use($additional_data){
    //         $query->whereBetween('price', [ $additional_data['start_price'] , $additional_data['end_price'] ]);
    //     })

    //     ->active()->type($additional_data['type'])->latest()->paginate($additional_data['limit'], ['*'], 'page', $additional_data['offset']);

    //     $maxPrice = Food::whereHas('category',function($q)use($additional_data){
    //         return $q->whereId($additional_data['category_id'])->orWhere('parent_id', $additional_data['category_id']);
    //     })->max('price');

    //     return [
    //         'total_size' => $paginator->total(),
    //         'limit' => $additional_data['limit'],
    //         'offset' => $additional_data['offset'],
    //         'products' => $paginator->items(),
    //         'max_price'=> (float) $maxPrice??0
    //     ];
    // }
//     public static function products(array $additional_data)
// {
//     $minRadiusMeters = $additional_data['min_radius_meters'] ?? null;
//     $longitude = $additional_data['longitude'] ?? null;
//     $latitude = $additional_data['latitude'] ?? null;

//     // Build base query with join
//     $query = Food::join('restaurants', 'foods.restaurant_id', '=', 'restaurants.id')
//         ->where('foods.status', 1) // Active foods
//         ->where('restaurants.status', 1) // Active restaurants
//         ->whereIn('restaurants.zone_id', $additional_data['zone_id'])
//         ->whereHas('category', function($q) use ($additional_data) {
//             return $q->whereId($additional_data['category_id'])->orWhere('parent_id', $additional_data['category_id']);
//         });

//     // Add radius condition if available
//     if ($minRadiusMeters && $longitude && $latitude) {
//         $query->selectRaw('foods.*, 
//             (6371 * acos(cos(radians(?)) * cos(radians(restaurants.latitude)) * 
//             cos(radians(restaurants.longitude) - radians(?)) + 
//             sin(radians(?)) * sin(radians(restaurants.latitude)))) * 1000 AS distance',
//             [$latitude, $longitude, $latitude])
//             ->havingRaw('distance <= ?', [$minRadiusMeters]);
//     } else {
//         $query->select('foods.*');
//     }

//     // Add other filters
//     $query->when($additional_data['veg'] == 1 && $additional_data['non_veg'] == 0, function($query) {
//         $query->where('foods.veg', 1);
//     })
//     ->when($additional_data['non_veg'] == 1 && $additional_data['veg'] == 0, function($query) {
//         $query->where('foods.veg', 0);
//     })
//     ->when($additional_data['avg_rating'] > 0, function($query) use ($additional_data) {
//         $query->where('foods.avg_rating', '>=', $additional_data['avg_rating']);
//     })
//     ->when($additional_data['top_rated'] == 1, function($query) {
//         $query->where('foods.avg_rating', '>=', 4);
//     })
//     ->when($additional_data['end_price'] > 0, function($query) use ($additional_data) {
//         $query->whereBetween('foods.price', [$additional_data['start_price'], $additional_data['end_price']]);
//     })
//     ->when($additional_data['type'] != 'all', function($query) use ($additional_data) {
//         $query->where('foods.type', $additional_data['type']);
//     })
//     ->orderBy('foods.created_at', 'desc');

//     $paginator = $query->paginate($additional_data['limit'], ['*'], 'page', $additional_data['offset']);

//     $maxPrice = Food::whereHas('category', function($q) use ($additional_data) {
//         return $q->whereId($additional_data['category_id'])->orWhere('parent_id', $additional_data['category_id']);
//     })->max('price');

//     return [
//         'total_size' => $paginator->total(),
//         'limit' => $additional_data['limit'],
//         'offset' => $additional_data['offset'],
//         'products' => $paginator->items(),
//         'max_price' => (float) ($maxPrice ?? 0)
//     ];
// }

    public static function products(array $additional_data)
    {
        try {
            \Log::info('CategoryLogic::products called', [
                'category_id' => $additional_data['category_id'] ?? null,
                'zone_id' => $additional_data['zone_id'] ?? null,
                'min_radius_meters' => $additional_data['min_radius_meters'] ?? null,
                'longitude' => $additional_data['longitude'] ?? null,
                'latitude' => $additional_data['latitude'] ?? null
            ]);

            $minRadiusMeters = $additional_data['min_radius_meters'] ?? null;
            $longitude = $additional_data['longitude'] ?? null;
            $latitude = $additional_data['latitude'] ?? null;
            
            // Start with basic query
            $query = Food::query();
            
            // If radius and coordinates are available, use join approach
            if ($minRadiusMeters && $longitude && $latitude) {
                \Log::info('Using radius-based query');
                
                $query->join('restaurants', 'foods.restaurant_id', '=', 'restaurants.id')
                    ->where('foods.status', 1)
                    ->where('restaurants.status', 1)
                    ->whereIn('restaurants.zone_id', $additional_data['zone_id'])
                    ->selectRaw('foods.*, 
                        (6371 * acos(cos(radians(?)) * cos(radians(restaurants.latitude)) * 
                        cos(radians(restaurants.longitude) - radians(?)) + 
                        sin(radians(?)) * sin(radians(restaurants.latitude)))) * 1000 AS distance',
                        [$latitude, $longitude, $latitude])
                    ->havingRaw('distance <= ?', [$minRadiusMeters]);
                    
            } else {
                \Log::info('Using simple query (no radius)');
                
                $query->whereHas('restaurant', function($q) use ($additional_data) {
                    $q->whereIn('zone_id', $additional_data['zone_id'])
                      ->where('status', 1);
                })
                ->where('status', 1);
            }
            
            // Apply category filter
            $query->where(function($q) use ($additional_data) {
                $q->where('category_id', $additional_data['category_id'])
                  ->orWhereHas('category', function($q2) use ($additional_data) {
                      $q2->where('parent_id', $additional_data['category_id']);
                  });
            });
            
            // Apply other filters
            if (isset($additional_data['type']) && $additional_data['type'] != 'all') {
                $query->where('type', $additional_data['type']);
            }
            
            if ($additional_data['veg'] == 1 && $additional_data['non_veg'] == 0) {
                $query->where('veg', 1);
            }
            
            if ($additional_data['non_veg'] == 1 && $additional_data['veg'] == 0) {
                $query->where('veg', 0);
            }
            
            if (isset($additional_data['avg_rating']) && $additional_data['avg_rating'] > 0) {
                $query->where('avg_rating', '>=', $additional_data['avg_rating']);
            }
            
            if (isset($additional_data['top_rated']) && $additional_data['top_rated'] == 1) {
                $query->where('avg_rating', '>=', 4);
            }
            
            if (isset($additional_data['end_price']) && $additional_data['end_price'] > 0) {
                $query->whereBetween('price', [
                    $additional_data['start_price'] ?? 0, 
                    $additional_data['end_price']
                ]);
            }
            
            // Execute query
            $paginator = $query->orderBy('created_at', 'desc')
                              ->paginate(
                                  $additional_data['limit'] ?? 25, 
                                  ['*'], 
                                  'page', 
                                  $additional_data['offset'] ?? 1
                              );
            
            \Log::info('Query executed successfully', [
                'total' => $paginator->total(),
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage()
            ]);
            
            // Get max price
            $maxPrice = Food::where('status', 1)
                           ->where(function($q) use ($additional_data) {
                               $q->where('category_id', $additional_data['category_id'])
                                 ->orWhereHas('category', function($q2) use ($additional_data) {
                                     $q2->where('parent_id', $additional_data['category_id']);
                                 });
                           })
                           ->max('price');
            
            return [
                'total_size' => $paginator->total(),
                'limit' => $additional_data['limit'] ?? 25,
                'offset' => $additional_data['offset'] ?? 1,
                'products' => $paginator->items(),
                'max_price' => (float) ($maxPrice ?? 0)
            ];
            
        } catch (\Exception $e) {
            \Log::error('CategoryLogic::products Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $additional_data
            ]);
            
            // Return empty response on error
            return [
                'total_size' => 0,
                'limit' => $additional_data['limit'] ?? 25,
                'offset' => $additional_data['offset'] ?? 1,
                'products' => [],
                'max_price' => 0
            ];
        }
    }


    // public static function restaurants(array $additional_data)
    // {

    //     $paginator = Restaurant::withOpen($additional_data['longitude'] , $additional_data['latitude'] )->with(['discount'=>function($q){
    //         return $q->validate();
    //     }])->whereIn('zone_id', $additional_data['zone_id'] )
    //     ->whereHas('foods.category', function($query)use($additional_data){
    //         return $query->whereId( $additional_data['category_id'])->orWhere('parent_id', $additional_data['category_id']);
    //     })

    //     ->when($additional_data['veg'] == 1  , function($query) {
    //         $query->where('veg',1);
    //     })
    //     ->when($additional_data['non_veg'] == 1  , function($query) {
    //         $query->where('non_veg',1);
    //     })

    //     ->when($additional_data['avg_rating'] > 0 , function($query) use($additional_data) {
    //         $query->selectSub(function ($query) use ($additional_data){
    //             $query->selectRaw('AVG(reviews.rating)')
    //                 ->from('reviews')
    //                 ->join('food', 'food.id', '=', 'reviews.food_id')
    //                 ->whereColumn('food.restaurant_id', 'restaurants.id')
    //                 ->groupBy('food.restaurant_id')
    //                 ->havingRaw('AVG(reviews.rating) >= ?', [$additional_data['avg_rating']]);
    //         }, 'avg_r')->having('avg_r', '>=', $additional_data['avg_rating']);
    //     })

    //     ->when($additional_data['top_rated'] == 1 , function($query){
    //                 $query->selectSub(function ($query) {
    //                     $query->selectRaw('AVG(reviews.rating)')
    //                         ->from('reviews')
    //                         ->join('food', 'food.id', '=', 'reviews.food_id')
    //                         ->whereColumn('food.restaurant_id', 'restaurants.id')
    //                         ->groupBy('food.restaurant_id')
    //                         ->havingRaw('AVG(reviews.rating) > ?', [4]);
    //                 }, 'avg_r')->having('avg_r', '>=', 4);
    //             })

    //     ->active()->withcount('foods')->type($additional_data['type'])->latest()->paginate($additional_data['limit'], ['*'], 'page', $additional_data['offset']);

    //     return [
    //         'total_size' => $paginator->total(),
    //         'limit' => $additional_data['limit'],
    //         'offset' => $additional_data['offset'],
    //         'restaurants' => $paginator->items()
    //     ];
    // }

public static function restaurants(array $additional_data)
{
    try {
        $minRadiusMeters = $additional_data['min_radius_meters'] ?? null;
        $longitude = $additional_data['longitude'] ?? 0;
        $latitude = $additional_data['latitude'] ?? 0;

        // Start with base query
        $query = Restaurant::whereIn('zone_id', (array)$additional_data['zone_id'])
                          ->active();
        
        // Add distance calculation if coordinates exist
        if ($longitude && $latitude) {
            $query->selectRaw('restaurants.*, 
                (6371 * acos(cos(radians(?)) * cos(radians(restaurants.latitude)) * 
                cos(radians(restaurants.longitude) - radians(?)) + 
                sin(radians(?)) * sin(radians(restaurants.latitude)))) * 1000 AS distance',
                [$latitude, $longitude, $latitude]);
        }
        
        // Apply radius condition
        if ($minRadiusMeters && $longitude && $latitude) {
            $query->havingRaw('distance <= ?', [$minRadiusMeters]);
        }
        
        // Category filter
        $query->whereHas('foods.category', function($query) use ($additional_data) {
            return $query->whereId($additional_data['category_id'])
                        ->orWhere('parent_id', $additional_data['category_id']);
        });
        
        // Apply other filters
        if (isset($additional_data['veg']) && $additional_data['veg'] == 1) {
            $query->where('veg', 1);
        }
        
        if (isset($additional_data['non_veg']) && $additional_data['non_veg'] == 1) {
            $query->where('non_veg', 1);
        }
        
        // Type filter
        if (isset($additional_data['type']) && $additional_data['type'] != 'all') {
            $query->where('type', $additional_data['type']);
        }
        
        // Get results
        $paginator = $query->with(['discount' => function($q) {
                                return $q->validate();
                            }])
                          ->withCount('foods')
                          ->orderBy('created_at', 'desc')
                          ->paginate($additional_data['limit'], ['*'], 'page', $additional_data['offset']);

        return [
            'total_size' => $paginator->total(),
            'limit' => $additional_data['limit'],
            'offset' => $additional_data['offset'],
            'restaurants' => $paginator->items()
        ];
        
    } catch (\Exception $e) {
        \Log::error('CategoryLogic::restaurants Simple Error: ' . $e->getMessage(), [
            'data' => $additional_data
        ]);
        
        return [
            'total_size' => 0,
            'limit' => $additional_data['limit'] ?? 25,
            'offset' => $additional_data['offset'] ?? 1,
            'restaurants' => []
        ];
    }
}



    public static function all_products($id)
    {
        $cate_ids=[];
        array_push($cate_ids,(int)$id);
        foreach (CategoryLogic::child($id) as $ch1){
            array_push($cate_ids,$ch1['id']);
            foreach (CategoryLogic::child($ch1['id']) as $ch2){
                array_push($cate_ids,$ch2['id']);
            }
        }
        return Food::whereIn('category_id', $cate_ids)->get();
    }


    public static function export_categories($collection){
        $data = [];
        foreach($collection as $key=>$item){
            $data[] = [
                'Id'=>$item->id,
                'Name'=>$item->name,
                'Image'=>$item->image,
                'ParentId'=>$item->parent_id,
                'Position'=>$item->position,
                'Priority'=>$item->priority,
                'Status'=>$item->status == 1 ? 'active' : 'inactive',
            ];
        }
        return $data;
    }
}
