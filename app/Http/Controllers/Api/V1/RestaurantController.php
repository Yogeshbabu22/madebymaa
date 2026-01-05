<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Coupon;
use App\Models\Review;
use App\Models\Restaurant;
use App\Models\Vendor;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\CentralLogics\RestaurantLogic;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\BusinessSetting;
use App\Models\Zone;

class RestaurantController extends Controller
{
    public function get_restaurants(Request $request, $filter_data="all")
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }

        $additional_data=[
            'zone_id'=> json_decode($request->header('zoneId'), true),
            'filter'=> $request->query('filter_data') ?? $filter_data,
            'limit' =>$request['limit'] ?? 25,
            'offset' =>$request['offset'] ?? 1,
            'type' =>$request->query('type', 'all') ?? 'all',
            'name' =>$request->query('name') ?? null,
            'longitude' =>$request->header('longitude') ?? 0,
            'latitude' => $request->header('latitude') ?? 0,
            'cuisine' => $request->query('cuisine', 'all') ?? 'all',
            'veg' =>$request->veg ?? null,
            'non_veg' =>$request->non_veg ?? null,
            'discount' =>$request->discount ?? null,
            'top_rated' =>$request->top_rated  ?? null,
            'delivery' =>$request->delivery ?? null,
            'takeaway' =>$request->takeaway ?? null,
            'avg_rating' =>$request->avg_rating ?? null,
        ];


        $restaurants = RestaurantLogic::get_restaurants(additional_data: $additional_data );



        $restaurants['restaurants'] = Helpers::restaurant_data_formatting(data:$restaurants['restaurants'],multi_data: true);
        
        
        foreach ($restaurants['restaurants'] as &$restaurant) {
        $ownerName = trim(($restaurant['vendor']['f_name'] ?? '') . ' ' . ($restaurant['vendor']['l_name'] ?? ''));
        $restaurant['owner_name'] = $ownerName; 
    }

        return response()->json($restaurants, 200);
    }
    
    
    
    
    
//  public function get_restaurants(Request $request, $filter_data = "all")
// {
//     if (!$request->hasHeader('zoneId')) {
//         $errors = [];
//         array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
//         return response()->json([
//             'errors' => $errors
//         ], 403);
//     }

//     $additional_data = [
//         'zone_id' => json_decode($request->header('zoneId'), true),
//         'filter' => $request->query('filter_data') ?? $filter_data,
//         'limit' => $request['limit'] ?? 25,
//         'offset' => $request['offset'] ?? 1,
//         'type' => $request->query('type', 'all') ?? 'all',
//         'name' => $request->query('name') ?? null,
//         'longitude' => $request->header('longitude') ?? 0,
//         'latitude' => $request->header('latitude') ?? 0,
//         'cuisine' => $request->query('cuisine', 'all') ?? 'all',
//         'veg' => $request->veg ?? null,
//         'non_veg' => $request->non_veg ?? null,
//         'discount' => $request->discount ?? null,
//         'top_rated' => $request->top_rated ?? null,
//         'delivery' => $request->delivery ?? null,
//         'takeaway' => $request->takeaway ?? null,
//         'avg_rating' => $request->avg_rating ?? null,
//     ];

//     // Start the query builder for restaurants
//     $restaurantsQuery = Restaurant::query();

//     // If cuisine filter is applied
//     if ($additional_data['cuisine'] !== 'all') {
//         $restaurantsQuery->whereHas('cuisines', function ($query) use ($additional_data) {
//             $query->where('name', 'like', '%' . $additional_data['cuisine'] . '%');
//         });
//     }

//     // Add other filters based on additional data if needed (e.g., name, veg, etc.)
//     if ($additional_data['name']) {
//         $restaurantsQuery->where('name', 'like', '%' . $additional_data['name'] . '%');
//     }

//     // Apply veg filter if provided
//     if ($additional_data['veg'] !== null) {
//         $restaurantsQuery->where('veg', $additional_data['veg']);
//     }

//     // Apply non_veg filter if provided
//     if ($additional_data['non_veg'] !== null) {
//         $restaurantsQuery->where('non_veg', $additional_data['non_veg']);
//     }

//     // Apply pagination
//     $restaurantsQuery->limit($additional_data['limit'])->offset(($additional_data['offset'] - 1) * $additional_data['limit']);

//     // Fetch the filtered restaurants
//     $restaurants = $restaurantsQuery->get();

//     // Get the total number of matching restaurants for pagination
//     $totalSize = $restaurantsQuery->count();

//     // Add vendor_name to each restaurant's data by querying directly from the DB
//     foreach ($restaurants as &$restaurant) {
//         $vendor = DB::table('vendors')
//             ->select('f_name', 'l_name') // Fetch only first name and last name
//             ->where('id', $restaurant->vendor_id)
//             ->first();

//         $restaurant->owner_name = $vendor ? $vendor->f_name . ' ' . $vendor->l_name : null; // Combine f_name and l_name
//     }

//     // Format restaurant data before sending response
//     $formattedRestaurants = Helpers::restaurant_data_formatting(data: $restaurants, multi_data: true);

//     // Prepare the final response in the required structure
//     $response = [
//         'total_size' => $totalSize,
//         'limit' => $additional_data['limit'],
//         'offset' => $additional_data['offset'],
//         'restaurants' => $formattedRestaurants,
//     ];

//     return response()->json($response, 200);
// }










    // public function get_latest_restaurants(Request $request, $filter_data="all")
    // {
    //     if (!$request->hasHeader('zoneId')) {
    //         $errors = [];
    //         array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
    //         return response()->json([
    //             'errors' => $errors
    //         ], 403);
    //     }

    //     $type = $request->query('type', 'all');
    //     $longitude= $request->header('longitude');
    //     $latitude= $request->header('latitude');
    //     $zone_id= json_decode($request->header('zoneId'), true);
    //     $restaurants = RestaurantLogic::get_latest_restaurants(zone_id:$zone_id, limit:$request['limit'], offset:$request['offset'], type:$type ,longitude:$longitude,latitude:$latitude,veg:$request->veg ,non_veg:$request->non_veg, discount:$request->discount,top_rated: $request->top_rated);
    //     $restaurants['restaurants'] = Helpers::restaurant_data_formatting(data:$restaurants['restaurants'],multi_data: true );

    //     return response()->json($restaurants['restaurants'], 200);
    // }
    
    public function get_latest_restaurants(Request $request, $filter_data="all")
{
    if (!$request->hasHeader('zoneId')) {
        $errors = [];
        array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
        return response()->json([
            'errors' => $errors
        ], 403);
    }

    $type = $request->query('type', 'all');
    $longitude = $request->header('longitude');
    $latitude = $request->header('latitude');
    $zone_id = json_decode($request->header('zoneId'), true);

    // Fetch the latest restaurants with related vendor data
    $restaurants = RestaurantLogic::get_latest_restaurants(
        zone_id: $zone_id, 
        limit: $request['limit'], 
        offset: $request['offset'], 
        type: $type, 
        longitude: $longitude,
        latitude: $latitude,
        veg: $request->veg, 
        non_veg: $request->non_veg, 
        discount: $request->discount, 
        top_rated: $request->top_rated
    );

    // Format restaurant data
    $restaurants['restaurants'] = Helpers::restaurant_data_formatting(data: $restaurants['restaurants'], multi_data: true);

    // Fetch and append owner_name (f_name + l_name from vendors) for each restaurant
    foreach ($restaurants['restaurants'] as &$restaurant) {
        $vendor = $restaurant->vendor; // Assuming the relationship 'vendor' exists in the Restaurant model

        if ($vendor) {
            $restaurant['owner_name'] = $vendor->f_name . ' ' . $vendor->l_name;
        } else {
            $restaurant['owner_name'] = null; // If no vendor is found
        }
    }

    return response()->json($restaurants['restaurants'], 200);
}


    // public function get_popular_restaurants(Request $request)
    // {
    //     if (!$request->hasHeader('zoneId')) {
    //         $errors = [];
    //         array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
    //         return response()->json([
    //             'errors' => $errors
    //         ], 403);
    //     }
    //     $longitude= $request->header('longitude');
    //     $latitude= $request->header('latitude');
    //     $type = $request->query('type', 'all');
    //     $zone_id= json_decode($request->header('zoneId'), true);
    //     $restaurants = RestaurantLogic::get_popular_restaurants(zone_id:$zone_id,limit: $request['limit'], offset:$request['offset'],type: $type,longitude:$longitude,latitude:$latitude,veg:$request->veg ,non_veg:$request->non_veg, discount:$request->discount,top_rated: $request->top_rated);
    //     $restaurants['restaurants'] = Helpers::restaurant_data_formatting(data:$restaurants['restaurants'], multi_data:true);
    //     return response()->json($restaurants['restaurants'], 200);
    // }



    public function get_popular_restaurants(Request $request)
{
    if (!$request->hasHeader('zoneId')) {
        $errors = [];
        array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
        return response()->json([
            'errors' => $errors
        ], 403);
    }
    
    $longitude = $request->header('longitude');
    $latitude = $request->header('latitude');
    $type = $request->query('type', 'all');
    $zone_id = json_decode($request->header('zoneId'), true);
    
    $restaurants = RestaurantLogic::get_popular_restaurants(
        zone_id: $zone_id,
        limit: $request['limit'],
        offset: $request['offset'],
        type: $type,
        longitude: $longitude,
        latitude: $latitude,
        veg: $request->veg,
        non_veg: $request->non_veg,
        discount: $request->discount,
        top_rated: $request->top_rated
    );

    // Format restaurant data
    $restaurants['restaurants'] = Helpers::restaurant_data_formatting(data: $restaurants['restaurants'], multi_data: true);

    // Add vendor_name to each restaurant
    foreach ($restaurants['restaurants'] as &$restaurant) {
        $ownerName = trim(($restaurant['vendor']['f_name'] ?? '') . ' ' . ($restaurant['vendor']['l_name'] ?? ''));
        $restaurant['owner_name'] = $ownerName; // Add the concatenated vendor name
    }

    return response()->json($restaurants['restaurants'], 200);
}





    // public function recently_viewed_restaurants(Request $request)
    // {
    //     if (!$request->hasHeader('zoneId')) {
    //         $errors = [];
    //         array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
    //         return response()->json([
    //             'errors' => $errors
    //         ], 403);
    //     }
    //     $longitude= $request->header('longitude');
    //     $latitude= $request->header('latitude');
       
    //     $type = $request->query('type', 'all');
         
    //     $zone_id= json_decode($request->header('zoneId'), true);
       
    //     $restaurants = RestaurantLogic::recently_viewed_restaurants_data(zone_id:$zone_id, limit:$request['limit'], offset:$request['offset'],type: $type,longitude:$longitude,latitude:$latitude);
       
    //     $restaurants['restaurants'] = Helpers::restaurant_data_formatting(data:$restaurants['restaurants'], multi_data:true);

    //     return response()->json($restaurants['restaurants'], 200);
    // }
    
public function recently_viewed_restaurants(Request $request)
{
    if (!$request->hasHeader('zoneId')) {
        $errors = [];
        array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
        return response()->json([
            'errors' => $errors
        ], 403);
    }
    $longitude = $request->header('longitude');
    $latitude = $request->header('latitude');
    $type = $request->query('type', 'all');
    $zone_id = json_decode($request->header('zoneId'), true);

    // Fetch recently viewed restaurants data
    $restaurants = RestaurantLogic::recently_viewed_restaurants_data(
        zone_id: $zone_id, 
        limit: $request['limit'], 
        offset: $request['offset'], 
        type: $type, 
        longitude: $longitude, 
        latitude: $latitude
    );

    // Format the restaurant data
    $restaurants['restaurants'] = Helpers::restaurant_data_formatting(data: $restaurants['restaurants'], multi_data: true);

    // Add owner_name to each restaurant in the response
    foreach ($restaurants['restaurants'] as &$restaurant) {
        if (isset($restaurant['vendor'])) {
            // Assuming the vendor relationship exists and contains f_name and l_name
            $restaurant['owner_name'] = $restaurant['vendor']['f_name'] . ' ' . $restaurant['vendor']['l_name'];
        } else {
            $restaurant['owner_name'] = null; // Default if no vendor information is available
        }
    }

    return response()->json($restaurants['restaurants'], 200);
}


    // public function get_details($id)
    // {
    //     $restaurant = RestaurantLogic::get_restaurant_details($id);
    //     if($restaurant)
    //     {
    //         $category_ids = DB::table('food')
    //         ->join('categories', 'food.category_id', '=', 'categories.id')
    //         ->selectRaw('IF((categories.position = "0"), categories.id, categories.parent_id) as categories')
    //         ->where('food.restaurant_id', $restaurant->id)
    //         ->where('categories.status',1)
    //         ->groupBy('categories')
    //         ->get();
    //         $restaurant = Helpers::restaurant_data_formatting(data: $restaurant);
    //         $restaurant['category_ids'] = array_map('intval', $category_ids->pluck('categories')->toArray());

    //         if(auth('api')->user() !== null){
    //             $customer_id =auth('api')->user()->id;
    //             Helpers::visitor_log(model:'restaurant',user_id:$customer_id,visitor_log_id:$restaurant->id,order_count:false);
    //         }
    //     }

    //     return response()->json($restaurant, 200);
    // }
//     public function get_details(Request $request, $id)
// {
//     $main_category_id = $request->main_category_id; 

//     $restaurant = RestaurantLogic::get_restaurant_details($id);

//     if ($restaurant)
//     {
       
//         $category_ids = DB::table('food')
//             ->join('categories', 'food.category_id', '=', 'categories.id')
//             ->selectRaw('IF((categories.position = "0"), categories.id, categories.parent_id) as categories')
//             ->where('food.restaurant_id', $restaurant->id)
//             ->where('categories.status', 1)
//             ->groupBy('categories')
//             ->get();

//         $restaurant = Helpers::restaurant_data_formatting(data: $restaurant);
//         $restaurant['category_ids'] = array_map('intval', $category_ids->pluck('categories')->toArray());

       
//         $selectedSchedule = collect($restaurant['schedules'] ?? [])
//             ->firstWhere('main_category_id', $main_category_id);

//         // $restaurant['selected_category_schedule'] = [
//         //     'main_category_id' => $main_category_id,
//         //     'opening_time' => $selectedSchedule['opening_time'] ?? null,
//         //     'closing_time' => $selectedSchedule['closing_time'] ?? null,
//         // ];
// $restaurant['selected_category_schedule'] = [
//     [
//         "id"=> 725,
//         "restaurant_id"=> 50,
//         "day"              => 4,
//         "main_category_id" =>  $main_category_id, // force integer
//         "opening_time"     => $selectedSchedule['opening_time'] ?? null,
//         "closing_time"     => $selectedSchedule['closing_time'] ?? null,
//     ]
// ];



//         if(auth('api')->user() !== null){
//             $customer_id = auth('api')->user()->id;
//             Helpers::visitor_log(
//                 model: 'restaurant',
//                 user_id: $customer_id,
//                 visitor_log_id: $restaurant->id,
//                 order_count: false
//             );
//         }
//     }

//     return response()->json($restaurant, 200);
// }
public function get_details(Request $request, $id)
{
    $main_category_id = (int) $request->main_category_id;

    $restaurant = RestaurantLogic::get_restaurant_details($id);

    if ($restaurant) {

        // Fetch category IDs
        $category_ids = DB::table('food')
            ->join('categories', 'food.category_id', '=', 'categories.id')
            ->selectRaw('IF(categories.position = "0", categories.id, categories.parent_id) as categories')
            ->where('food.restaurant_id', $restaurant->id)
            ->where('categories.status', 1)
            ->groupBy('categories')
            ->pluck('categories')
            ->map(fn($id) => (int)$id)
            ->toArray();

        $restaurant = Helpers::restaurant_data_formatting(data: $restaurant);
        $restaurant['category_ids'] = $category_ids;

        // ===============================
        //   FIND SELECTED MAIN CATEGORY SCHEDULE
        // ===============================

        $selectedSchedule = collect($restaurant['schedules'] ?? [])
            ->firstWhere('main_category_id', $main_category_id);
// dd($selectedSchedule);
        if ($selectedSchedule) {
            // Build response array
            $restaurant['selected_category_schedule'] = [
                [
                    "id"              => (int) $selectedSchedule['id'],
                    "restaurant_id"   => (int) $selectedSchedule['restaurant_id'],
                    "day"             => (int) $selectedSchedule['day'],
                    "main_category_id"=> (int) $selectedSchedule['main_category_id'],
                    "opening_time"    => $selectedSchedule['opening_time'],
                    "closing_time"    => $selectedSchedule['closing_time'],
                ]
            ];
        } else {
            // No schedule found → return empty array
            $restaurant['selected_category_schedule'] = [];
        }

        // Visitor Log
        if (auth('api')->user() !== null) {
            $customer_id = auth('api')->user()->id;
            Helpers::visitor_log(
                model: 'restaurant',
                user_id: $customer_id,
                visitor_log_id: $restaurant->id,
                order_count: false
            );
        }
    }

    return response()->json($restaurant, 200);
}


    public function get_searched_restaurants(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $type = $request->query('type', 'all');
        $longitude= $request->header('longitude');
        $latitude= $request->header('latitude');
        $zone_id= json_decode($request->header('zoneId'), true);
        $restaurants = RestaurantLogic::search_restaurants(name:$request['name'], zone_id:$zone_id, category_id:$request->category_id,limit:$request['limit'], offset:$request['offset'],type: $type,longitude:$longitude,latitude:$latitude ,popular: $request->popular ,new: $request->new ,rating: $request->rating,
        rating_3_plus:$request->rating_3_plus,rating_4_plus:$request->rating_4_plus ,rating_5:$request->rating_5 ,
        discounted: $request->discounted ,sort_by: $request->sort_by );
        $restaurants['restaurants'] = Helpers::restaurant_data_formatting( data: $restaurants['restaurants'],multi_data: true);
        return response()->json($restaurants, 200);
    }

    // public function reviews(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'restaurant_id' => 'required',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => Helpers::error_processor($validator)], 403);
    //     }
    //     $id = $request['restaurant_id'];


    //     $reviews = Review::with(['customer', 'food'])
    //     ->whereHas('food', function($query)use($id){
    //         return $query->where('restaurant_id', $id);
    //     })
    //     ->active()->latest()->get();

    //     $storage = [];
    //     foreach ($reviews as $item) {
    //         $item['attachment'] = json_decode($item['attachment']);
    //         $item['food_name'] = null;
    //         $item['food_image'] = null;
    //         $item['customer_name'] = null;
    //         if($item->food)
    //         {
    //             $item['food_name'] = $item?->food?->name;
    //             $item['food_image'] = $item?->food?->image;
    //             if(count($item?->food?->translations)>0)
    //             {
    //                 $translate = array_column($item->food->translations->toArray(), 'value', 'key');
    //                 $item['food_name'] = $translate['name'];
    //             }
    //         }
    //         if($item?->customer)
    //         {
    //             $item['customer_name'] = $item?->customer?->f_name.' '.$item?->customer?->l_name;
    //         }

    //         unset($item['food']);
    //         unset($item['customer']);
    //         array_push($storage, $item);
    //     }

    //     return response()->json($storage, 200);
    // }
    
    public function reviews(Request $request)
{
    $validator = Validator::make($request->all(), [
        'restaurant_id' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => Helpers::error_processor($validator)], 403);
    }
    $id = $request['restaurant_id'];

    // Fetch reviews along with related customer and food data
    $reviews = Review::with(['customer', 'food'])
        ->whereHas('food', function($query) use($id) {
            return $query->where('restaurant_id', $id);
        })
        ->active()->latest()->get();

    $storage = [];
    foreach ($reviews as $item) {
        $item['attachment'] = json_decode($item['attachment']);
        $item['food_name'] = null;
        $item['food_image'] = null;
        $item['customer_name'] = null;
        $item['owner_name'] = null; 
        
        // Set food details
        if ($item->food) {
            $item['food_name'] = $item?->food?->name;
            $item['food_image'] = $item?->food?->image;

           
            if (count($item?->food?->translations) > 0) {
                $translate = array_column($item->food->translations->toArray(), 'value', 'key');
                $item['food_name'] = $translate['name'];
            }

            
            if ($item->food->restaurant && $item->food->restaurant->vendor) {
                $vendor = $item->food->restaurant->vendor;
                $item['owner_name'] = $vendor->f_name . ' ' . $vendor->l_name;
            }
        }

        // Set customer details
        if ($item?->customer) {
            $item['customer_name'] = $item?->customer?->f_name . ' ' . $item?->customer?->l_name;
        }

        // Unset unnecessary fields
        unset($item['food']);
        unset($item['customer']);
        array_push($storage, $item);
    }

    return response()->json($storage, 200);
}


    public function get_coupons(Request $request){

        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $restaurant_id=$request->restaurant_id;
        $customer_id=$request->customer_id ?? null;

        $coupons = Coupon::Where(function ($q) use ($restaurant_id,$customer_id) {
            $q->Where('coupon_type', 'restaurant_wise')->whereJsonContains('data', [$restaurant_id])
                ->where(function ($q1) use ($customer_id) {
                    $q1->whereJsonContains('customer_id', [$customer_id])->orWhereJsonContains('customer_id', ['all']);
                });
        })->orWhereHas('restaurant',function($q) use ($restaurant_id){
            $q->where('id',$restaurant_id);
        })
        ->active()->whereDate('expire_date', '>=', date('Y-m-d'))->whereDate('start_date', '<=', date('Y-m-d'))
        ->get();
        return response()->json($coupons, 200);
    }



    
    
 public function get_recommended_restaurants(Request $request)
 {
 // Check if zoneId header is present
 if (!$request->hasHeader('zoneId')) {
 $errors = [];
 array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
 return response()->json([
 'errors' => $errors
 ], 403);
 }
 
 // Get latitude and longitude from request headers, defaulting to 0
 $longitude = $request->header('longitude') ?? 0;
 $latitude = $request->header('latitude') ?? 0;
 $zone_ids = json_decode($request->header('zoneId'), true);
 
 // Fetch radius from the zones table based on the provided zoneId
 $zones = Zone::whereIn('id', $zone_ids)->get(['id', 'radius']);
 
 if ($zones->isEmpty()) {
 return response()->json([
 'errors' => [['code' => 'zoneId', 'message' => translate('messages.invalid_zone_id')]]
 ], 404); // 404 Not Found status code
 }
 
 // Optionally handle cases with multiple zone radii
 // Example: Use the minimum radius from the provided zones
 $zone_radius = $zones->min('radius');

 // Fetch all restaurants within the zones
 $restaurants = Restaurant::withOpen($longitude, $latitude)
 ->withCount('foods')
 ->with(['foods_for_reorder'])
 ->active()
 ->whereIn('zone_id', $zone_ids)
 ->inRandomOrder()
 ->limit(100) // Get a reasonable number of restaurants first
 ->get();
 
 // Filter restaurants based on the radius
 $filtered_restaurants = $restaurants->filter(function($restaurant) use ($latitude, $longitude, $zone_radius) {
 $distance = $this->calculateDistance($latitude, $longitude, $restaurant->latitude, $restaurant->longitude);
 // Only keep restaurants within the radius

 return $distance <= $zone_radius;
 
 });
 
 // Check if there are any restaurants found
 if ($filtered_restaurants->isEmpty()) {
 // Return a 'no restaurants found' message
 return response()->json([
 'message' => translate('messages.no_restaurant_found'),
 'errors' => [
 ['code' => 'no_restaurant', 'message' => translate('messages.no_restaurant_in_radius')]
 ]
 ], 404); // 404 Not Found status code
 }
 
 // Map the filtered restaurants to format the data as needed
 $data = $filtered_restaurants->map(function ($restaurant) use ($latitude, $longitude) {
 $restaurant->foods = $restaurant->foods_for_reorder->take(5);
 unset($restaurant->foods_for_reorder);
 
 // Calculate the distance between the user and the restaurant, round it, and add " km"
 $distance = $this->calculateDistance($latitude, $longitude, $restaurant->latitude, $restaurant->longitude);
 $restaurant->distance = round($distance, 2) . ' km'; // Rounded to 1 decimal place and " km" appended
 
 return $restaurant;
 });
 
 // Format and return the response
 return response()->json(Helpers::restaurant_data_formatting($data, true), 200);
 }
 


//   public function get_recommended_restaurants(Request $request)
//     {
//         // Check if zoneId header is present
//         if (!$request->hasHeader('zoneId')) {
//             $errors = [];
//             array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
//             return response()->json([
//                 'errors' => $errors
//             ], 403);
//         }
    
//         // Get latitude and longitude from request headers, defaulting to 0
//         $longitude = $request->header('longitude') ?? 0;
//         $latitude = $request->header('latitude') ?? 0;
//         $zone_ids = json_decode($request->header('zoneId'), true);
    
//         // Fetch radius from the zones table based on the provided zoneId
//         $zones = Zone::whereIn('id', $zone_ids)->get(['id', 'radius']);
        
//         if ($zones->isEmpty()) {
//             return response()->json([
//                 'errors' => [['code' => 'zoneId', 'message' => translate('messages.invalid_zone_id')]]
//             ], 404); // 404 Not Found status code
//         }
    
//         // Optionally handle cases with multiple zone radii
//         // Example: Use the minimum radius from the provided zones
//         $zone_radius = $zones->min('radius');

//         // Fetch all restaurants within the zones
//         $restaurants = Restaurant::withOpen($longitude, $latitude)
//             ->withCount('foods')
//             ->with(['foods_for_reorder'])
//             ->active()
//             ->whereIn('zone_id', $zone_ids)
//             ->inRandomOrder()
//             ->limit(100) // Get a reasonable number of restaurants first
//             ->get();
    
//         // Filter restaurants based on the radius
//         $filtered_restaurants = $restaurants->filter(function($restaurant) use ($latitude, $longitude, $zone_radius) {
//             $distance = $this->calculateDistance($latitude, $longitude, $restaurant->latitude, $restaurant->longitude);
//             // Only keep restaurants within the radius

//             return $distance <= $zone_radius;
          
//         });
    
//         // Check if there are any restaurants found
//         if ($filtered_restaurants->isEmpty()) {
//             // Return a 'no restaurants found' message
//             return response()->json([
//                 'message' => translate('messages.no_restaurant_found'),
//                 'errors' => [
//                     ['code' => 'no_restaurant', 'message' => translate('messages.no_restaurant_in_radius')]
//                 ]
//             ], 404); // 404 Not Found status code
//         }
    
//         // Map the filtered restaurants to format the data as needed
//         $data = $filtered_restaurants->map(function ($restaurant) use ($latitude, $longitude) {
//             $restaurant->foods = $restaurant->foods_for_reorder->take(5);
//             unset($restaurant->foods_for_reorder);
    
//             // Calculate the distance between the user and the restaurant, round it, and add " km"
//             $distance = $this->calculateDistance($latitude, $longitude, $restaurant->latitude, $restaurant->longitude);
//             $restaurant->distance = round($distance, 2) . ' km'; // Rounded to 1 decimal place and " km" appended
    
//             return $restaurant;
//         });
    
//         // Format and return the response
//         return response()->json(Helpers::restaurant_data_formatting($data, true), 200);
//     }
    
  public function calculateDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo) {
        $earthRadiusKm = 6371; // Earth's radius in kilometers
    
        // Convert latitude and longitude from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);
    
        // Calculate the difference between the latitudes and longitudes
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
    
        // Haversine formula
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                    cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    
        // Distance in kilometers
        $distanceKm = $earthRadiusKm * $angle;
    
        // Convert kilometers to meters (SI unit)
        $distanceMeters = $distanceKm * 1000;
    
        return $distanceKm; // Distance in meters (SI unit)
    }

    public function get_visited_restaurants(Request $request){
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }

        $longitude= $request->header('longitude') ?? 0;
        $latitude= $request->header('latitude') ?? 0;
        $user_id = $request->user ? $request->user->id : $request['guest_id'];
        // dd($user_id);
        $zone_id= json_decode($request->header('zoneId'), true);
        $data = Restaurant::withOpen($longitude,$latitude)
        ->wherehas('users', function($q) use($user_id) {
            $q->where('user_id',$user_id);
        })
        ->with('users')
        ->withcount('foods')
        ->with(['foods_for_reorder'])
        ->Active()
        ->whereIn('zone_id', $zone_id)

        ->selectRaw('(SELECT `visit_count` FROM `visitor_logs` WHERE `restaurants`.`id` = `visitor_logs`.`visitor_log_id` AND `user_id` = ? ORDER BY `visit_count` DESC LIMIT 1) as visit_count', [$user_id])

        ->orderBy('visit_count', 'desc')

        ->limit(20)
        ->get()
		->map(function ($data) {
			$data->foods = $data->foods_for_reorder->take(5);
            unset($data->foods_for_reorder);
			return $data;
		});

        return response()->json(Helpers::restaurant_data_formatting($data, true), 200);
    }






}
