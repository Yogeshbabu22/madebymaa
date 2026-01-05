<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Cuisine;
use App\Models\Food; // Add this line
use Illuminate\Support\Facades\DB;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Zone; 

class CuisineController extends Controller
{
    public function get_all_cuisines()
    {
        $Cuisines = Cuisine::where('status',1)->get();
        return response()->json( ['Cuisines' => $Cuisines], 200);
    }
    // public function get_restaurants(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'cuisine_id' => 'required',
    //         'limit' => 'required',
    //         'offset' => 'required',
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json(['errors' => Helpers::error_processor($validator)], 203);
    //     }
    //     $longitude= $request->header('longitude');
    //     $latitude= $request->header('latitude');

    //     if (!$request->hasHeader('zoneId')) {
    //         $errors = [];
    //         array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
    //         return response()->json([
    //             'errors' => $errors
    //         ], 403);
    //     }
    //     $zone_id= json_decode($request->header('zoneId'), true);
    //     $limit = $request->query('limit', 1);
    //     $offset = $request->query('offset', 1);

    //     $restaurants=Restaurant::whereIn('zone_id',$zone_id)
    //     ->with(['discount'=>function($q){
    //         return $q->validate();
    //     }])
    //     ->cuisine($request->cuisine_id)->active()->WithOpen($longitude,$latitude)->withCount('foods')
    //     ->paginate($limit, ['*'], 'page', $offset);

    //     $restaurants_data = Helpers::restaurant_data_formatting($restaurants->items(), true);

    //     $data = [
    //         'total_size' => $restaurants->total(),
    //         'limit' => $limit,
    //         'offset' => $offset,
    //         'restaurants' => $restaurants_data,
    //     ];
    //     return response()->json($data, 200);

    // }
    



// public function get_restaurants_by_filter_cuisine(Request $request)
// {
//     $validator = Validator::make($request->all(), [
//         'cuisine_id' => 'required|array', // Change to array
//         'cuisine_id.*' => 'exists:cuisines,id', // Validate each cuisine_id exists
//         'limit' => 'required|integer|min:1',
//         'offset' => 'required|integer|min:0',
//     ]);

//     if ($validator->fails()) {
//         return response()->json(['errors' => Helpers::error_processor($validator)], 203);
//     }

//     $longitude = $request->header('longitude');
//     $latitude = $request->header('latitude');

//     if (!$request->hasHeader('zoneId')) {
//         $errors = [];
//         array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
//         return response()->json(['errors' => $errors], 403);
//     }

//     $zone_id = json_decode($request->header('zoneId'), true);
//     $limit = $request->query('limit', 1);
//     $offset = $request->query('offset', 1);

//     // Update the query to handle multiple cuisine_ids
//     $restaurants = Restaurant::whereIn('zone_id', $zone_id)
//         ->with(['discount' => function ($q) {
//             return $q->validate();
//         }])
//         ->whereHas('cuisines', function ($query) use ($request) {
//             $query->whereIn('cuisines.id', $request->cuisine_id); // Filter by multiple cuisine_ids and specify table name
//         })
//         ->active()
//         ->WithOpen($longitude, $latitude)
//         ->withCount('foods')
//         ->paginate($limit, ['*'], 'page', $offset);

//     $restaurants_data = Helpers::restaurant_data_formatting($restaurants->items(), true);

//     $data = [
//         'total_size' => $restaurants->total(),
//         'limit' => $limit,
//         'offset' => $offset,
//         'restaurants' => $restaurants_data,
//     ];
    
//     return response()->json($data, 200);
// }


// public function get_restaurants(Request $request)
// {
//     $validator = Validator::make($request->all(), [
//         'cuisine_id' => 'required',
//         'limit' => 'required',
//         'offset' => 'required',
//     ]);
    
//     if ($validator->fails()) {
//         return response()->json(['errors' => Helpers::error_processor($validator)], 203);
//     }
    
//     $longitude = $request->header('longitude');
//     $latitude = $request->header('latitude');

//     if (!$request->hasHeader('zoneId')) {
//         $errors = [];
//         array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
//         return response()->json([
//             'errors' => $errors
//         ], 403);
//     }
    
//     $zone_id = json_decode($request->header('zoneId'), true);
//     $limit = $request->query('limit', 1);
//     $offset = $request->query('offset', 1);

//     // Fetch restaurants with vendors
//     $restaurants = Restaurant::whereIn('zone_id', $zone_id)
//         ->with(['discount' => function ($q) {
//             return $q->validate();
//         }, 'vendor']) // Eager load the vendor relationship
//         ->cuisine($request->cuisine_id)->active()->WithOpen($longitude, $latitude)->withCount('foods')
//         ->paginate($limit, ['*'], 'page', $offset);

//     // Format restaurant data and include owner_name
//     $restaurants_data = $restaurants->getCollection()->map(function ($restaurant) {
//         $owner_name = $restaurant->vendor ? $restaurant->vendor->f_name . ' ' . $restaurant->vendor->l_name : null;

//         // Return all attributes of the restaurant along with owner_name
//         return array_merge($restaurant->toArray(), [
//             'owner_name' => $owner_name, // Add the owner_name to the response
//         ]);
//     });

//     $data = [
//         'total_size' => $restaurants->total(),
//         'limit' => $limit,
//         'offset' => $offset,
//         'restaurants' => $restaurants_data,
//     ];
    
//     return response()->json($data, 200);
// }

public function get_restaurants(Request $request)
{
    $validator = Validator::make($request->all(), [
        'cuisine_id' => 'required',
        'limit' => 'required',
        'offset' => 'required',
    ]);
    
    if ($validator->fails()) {
        return response()->json(['errors' => Helpers::error_processor($validator)], 203);
    }
    
    $longitude = $request->header('longitude');
    $latitude = $request->header('latitude');

    if (!$request->hasHeader('zoneId')) {
        $errors = [];
        array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
        return response()->json([
            'errors' => $errors
        ], 403);
    }
    
    $zone_id = json_decode($request->header('zoneId'), true);
    $limit = $request->query('limit', 1);
    $offset = $request->query('offset', 1);

    // **Calculate minimum radius for zones**
    $minRadiusMeters = null;
    if (!empty($zone_id)) {
        $minRadiusKm = Zone::whereIn('id', (array) $zone_id)->min('radius');
        if (!empty($minRadiusKm) && $minRadiusKm > 0) {
            $minRadiusMeters = $minRadiusKm * 1000;
        }
    }

    // Fetch restaurants with vendors
    $restaurants = Restaurant::whereIn('zone_id', $zone_id)
        ->with(['discount' => function ($q) {
            return $q->validate();
        }, 'vendor']) // Eager load the vendor relationship
        ->cuisine($request->cuisine_id)
        ->active()
        ->WithOpen($longitude, $latitude)
        ->withCount('foods')
        
        // **Apply radius condition if coordinates and radius exist**
        ->when($minRadiusMeters && isset($longitude) && isset($latitude), function($query) use ($minRadiusMeters) {
            $query->havingRaw('distance <= ?', [$minRadiusMeters]);
        })
        
        ->paginate($limit, ['*'], 'page', $offset);

    // Format restaurant data and include owner_name
    $restaurants_data = $restaurants->getCollection()->map(function ($restaurant) {
        $owner_name = $restaurant->vendor ? $restaurant->vendor->f_name . ' ' . $restaurant->vendor->l_name : null;

        // Return all attributes of the restaurant along with owner_name
        return array_merge($restaurant->toArray(), [
            'owner_name' => $owner_name, // Add the owner_name to the response
        ]);
    });

    $data = [
        'total_size' => $restaurants->total(),
        'limit' => $limit,
        'offset' => $offset,
        'restaurants' => $restaurants_data,
    ];
    
    return response()->json($data, 200);
}



  
}
