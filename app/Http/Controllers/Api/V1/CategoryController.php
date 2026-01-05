<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Food;
use App\Models\Category;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\CentralLogics\CategoryLogic;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\MainCategory; 
use App\Models\Zone;

class CategoryController extends Controller
{
    public function get_categories(Request $request)
    {
        try {
            $zone_id=  $request->header('zoneId') ? json_decode($request->header('zoneId'), true) : [];
            $name= $request->query('name');
            $categories = Category::withCount(['products','childes'])->with(['childes' => function($query)  {
                $query->withCount(['products','childes']);
            }])
            ->where(['position'=>0,'status'=>1])

            ->when($name, function($q)use($name){
                $key = explode(' ', $name);
                $q->where(function($q)use($key){
                    foreach ($key as $value){
                        $q->orWhere('name', 'like', '%'.$value.'%')->orWhere('slug', 'like', '%'.$value.'%');
                    }
                    return $q;
                });
            })
            ->orderBy('priority','desc')->get();



            if(count($zone_id) > 0){
                foreach ($categories as $category) {
                        $productCount = Food::active()
                        ->whereHas('restaurant', function ($query) use ($zone_id) {
                            $query->whereIn('zone_id', $zone_id);
                        })
                        ->whereHas('category',function($q)use($category){
                            return $q->whereId($category->id)->orWhere('parent_id', $category->id);
                        })
                        ->count();
                        $category['products_count'] = $productCount;
                    unset($category['childes']);
                }
                return response()->json($categories, 200);
            }

            return response()->json(Helpers::category_data_formatting($categories, true), 200);
        } catch (\Exception $e) {
            return response()->json([$e->getMessage()]);
        }
    }

    public function get_childes($id)
    {
        try {
            $categories = Category::when(is_numeric($id),function ($qurey) use($id){
                $qurey->where(['parent_id' => $id,'status'=>1]);
                })
                ->when(!is_numeric($id),function ($qurey) use($id){
                    $qurey->where(['slug' => $id,'status'=>1]);
                })
            ->orderBy('priority','desc')->get();
            return response()->json(Helpers::category_data_formatting($categories, true), 200);
        } catch (\Exception $e) {
            return response()->json([$e->getMessage()], 200);
        }
    }

   

    // public function get_products($id, Request $request)
    // {
    //     // Check if zoneId header is present
    //     if (!$request->hasHeader('zoneId')) {
    //         $errors = [];
    //         array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
    //         return response()->json(['errors' => $errors], 403);
    //     }
    
    //     // Validate request parameters
    //     $validator = Validator::make($request->all(), [
    //         'limit' => 'required',
    //         'offset' => 'required',
    //     ]);
    
    //     if ($validator->fails()) {
    //         return response()->json(['errors' => Helpers::error_processor($validator)], 403);
    //     }
    
    //     // Prepare additional data for product retrieval
    //     $additional_data = [
    //         'category_id' => $id,
    //         'zone_id' => json_decode($request->header('zoneId'), true),
    //         'limit' => $request['limit'] ?? 25,
    //         'offset' => $request['offset'] ?? 1,
    //         'type' => $request->query('type', 'all') ?? 'all',
    //         'veg' => $request->veg ?? 0,
    //         'non_veg' => $request->non_veg ?? 0,
    //         'new' => $request->new ?? 0,
    //         'avg_rating' => $request->avg_rating ?? 0,
    //         'top_rated' => $request->top_rated ?? 0,
    //         'start_price' => json_decode($request->price)[0] ?? 0,
    //         'end_price' => json_decode($request->price)[1] ?? 0,
    //     ];
    
    //     // Fetch products along with vendor information
    //     $data = CategoryLogic::products($additional_data);
        
    //     // Assuming the data contains products, you can modify this section to include vendor names
    //     $products = $data['products'];
        
    //     // Assuming the products are in a collection, we can map them to include owner_name
    //     foreach ($products as $product) {
    //         $product->owner_name = $this->getVendorName($product->restaurant_id);
    //     }
    
    //     // Format the product data
    //     $data['products'] = Helpers::product_data_formatting($products, true, false, app()->getLocale());
    
    //     // Log visitor activity if authenticated
    //     if (auth('api')->user() !== null) {
    //         $customer_id = auth('api')->user()->id;
    //         Helpers::visitor_log('category', $customer_id, $id, false);
    //     }
    
    //     return response()->json($data, 200);
    // }
    
    
//     public function get_products($id, Request $request)
// {
    
//     if (!$request->hasHeader('zoneId')) {
//         $errors = [];
//         array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
//         return response()->json(['errors' => $errors], 403);
//     }

//     $validator = Validator::make($request->all(), [
//         'limit' => 'required',
//         'offset' => 'required',
//     ]);

//     if ($validator->fails()) {
//         return response()->json(['errors' => Helpers::error_processor($validator)], 403);
//     }

//     $additional_data = [
//         'category_id' => $id,
//         'zone_id' => json_decode($request->header('zoneId'), true),
//         'limit' => $request['limit'] ?? 25,
//         'offset' => $request['offset'] ?? 1,
//         'type' => $request->query('type', 'all') ?? 'all',
//         'veg' => $request->veg ?? 0,
//         'non_veg' => $request->non_veg ?? 0,
//         'new' => $request->new ?? 0,
//         'avg_rating' => $request->avg_rating ?? 0,
//         'top_rated' => $request->top_rated ?? 0,
//         'start_price' => json_decode($request->price)[0] ?? 0,
//         'end_price' => json_decode($request->price)[1] ?? 0,
//     ];

//     $data = CategoryLogic::products($additional_data);
//     $products = $data['products'];

//     foreach ($products as $product) {
//         // vendor name
//         $product->owner_name = $this->getVendorName($product->restaurant_id);

//         // super_category mapping
//         $superCategoryList = [];
//         if (!empty($product->super_category_ids)) {
//             $superCategoryIds = $product->super_category_ids;

//             $superCategories = MainCategory::whereIn('id', $superCategoryIds)
//                 ->select('id', 'name')
//                 ->get();

//             $superCategoryList = $superCategories->map(function ($cat) {
//                 return [
//                     'id' => $cat->id,
//                     'name' => $cat->name
//                 ];
//             });
//         }

//         $product->super_categories = $superCategoryList;
//     }

//     $data['products'] = Helpers::product_data_formatting($products, true, false, app()->getLocale());

//     if (auth('api')->user() !== null) {
//         $customer_id = auth('api')->user()->id;
//         Helpers::visitor_log('category', $customer_id, $id, false);
//     }

//     return response()->json($data, 200);
// }
    
//     // Method to fetch vendor name based on restaurant ID
//     private function getVendorName($restaurantId)
//     {
//         // Fetch the vendor's full name based on restaurant_id
//         return DB::table('vendors')
//             ->join('restaurants', 'vendors.id', '=', 'restaurants.vendor_id')
//             ->where('restaurants.id', $restaurantId)
//             ->select(DB::raw("CONCAT(vendors.f_name, ' ', vendors.l_name) as owner_name"))
//             ->value('owner_name');
//     }

//   public function get_products($id, Request $request)
// {
    
//     // ✅ 1. Zone ID check
//     if (!$request->hasHeader('zoneId')) {
//         $errors = [];
//         array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
//         return response()->json(['errors' => $errors], 403);
//     }

//     // ✅ 2. Validate limit & offset
//     $validator = Validator::make($request->all(), [
//         'limit' => 'required',
//         'offset' => 'required',
//     ]);

//     if ($validator->fails()) {
//         return response()->json(['errors' => Helpers::error_processor($validator)], 403);
//     }

//     $zone_id = json_decode($request->header('zoneId'), true);
//     $longitude = $request->header('longitude');
//     $latitude = $request->header('latitude');
    
//     // ✅ 3. Calculate minimum radius for zones
//     $minRadiusMeters = null;
//     if (!empty($zone_id)) {
//         $minRadiusKm = Zone::whereIn('id', (array) $zone_id)->min('radius');
//         if (!empty($minRadiusKm) && $minRadiusKm > 0) {
//             $minRadiusMeters = $minRadiusKm * 1000;
//         }
//     }

//     // ✅ 4. Additional data for product fetching
//     $additional_data = [
//         'category_id' => $id,
//         'zone_id' => $zone_id,
//         'limit' => $request['limit'] ?? 25,
//         'offset' => $request['offset'] ?? 1,
//         'type' => $request->query('type', 'all') ?? 'all',
//         'veg' => $request->veg ?? 0,
//         'non_veg' => $request->non_veg ?? 0,
//         'new' => $request->new ?? 0,
//         'avg_rating' => $request->avg_rating ?? 0,
//         'top_rated' => $request->top_rated ?? 0,
//         'start_price' => json_decode($request->price)[0] ?? 0,
//         'end_price' => json_decode($request->price)[1] ?? 0,
//         'min_radius_meters' => $minRadiusMeters,
//         'longitude' => $longitude,
//         'latitude' => $latitude,
//     ];

//     // ✅ 5. Fetch products
//     $data = CategoryLogic::products($additional_data);
//     $products = $data['products'];

//     // ✅ 6. Add vendor name to each product
//     foreach ($products as $product) {
//         $product->owner_name = $this->getVendorName($product->restaurant_id);
//     }

//     // ✅ 7. Format product data
//     $data['products'] = Helpers::product_data_formatting($products, true, false, app()->getLocale());

//     // ✅ 8. Fetch super category list (id and name only)
//     $superCategories = MainCategory::select('id', 'name')->get();
//     $data['super_category'] = $superCategories;

//     if (auth('api')->user() !== null) {
//         $customer_id = auth('api')->user()->id;
//         Helpers::visitor_log('category', $customer_id, $id, false);
//     }

//     // ✅ 9. Final response
//     return response()->json($data, 200);
// }


public function get_products($id, Request $request)
{
    try {
        // ✅ 1. Zone ID check
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json(['errors' => $errors], 403);
        }

        // ✅ 2. Validate limit & offset
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $zone_id = json_decode($request->header('zoneId'), true);
        $longitude = $request->header('longitude');
        $latitude = $request->header('latitude');
        
        // ✅ 3. Calculate minimum radius for zones
        $minRadiusMeters = null;
        if (!empty($zone_id)) {
            $minRadiusKm = Zone::whereIn('id', (array) $zone_id)->min('radius');
            if (!empty($minRadiusKm) && $minRadiusKm > 0) {
                $minRadiusMeters = $minRadiusKm * 1000;
            }
        }

        // ✅ 4. Additional data for product fetching
        $additional_data = [
            'category_id' => $id,
            'zone_id' => $zone_id,
            'limit' => $request['limit'] ?? 25,
            'offset' => $request['offset'] ?? 1,
            'type' => $request->query('type', 'all') ?? 'all',
            'veg' => $request->veg ?? 0,
            'non_veg' => $request->non_veg ?? 0,
            'new' => $request->new ?? 0,
            'avg_rating' => $request->avg_rating ?? 0,
            'top_rated' => $request->top_rated ?? 0,
            'start_price' => json_decode($request->price)[0] ?? 0,
            'end_price' => json_decode($request->price)[1] ?? 0,
            'min_radius_meters' => $minRadiusMeters,
            'longitude' => $longitude,
            'latitude' => $latitude,
        ];

        // Log the request
        \Log::info('get_products called', [
            'category_id' => $id,
            'zone_id' => $zone_id,
            'longitude' => $longitude,
            'latitude' => $latitude,
            'minRadiusMeters' => $minRadiusMeters
        ]);

        // ✅ 5. Fetch products
        $data = CategoryLogic::products($additional_data);
        $products = $data['products'];

        // ✅ 6. Add vendor name to each product
        foreach ($products as $product) {
            $product->owner_name = $this->getVendorName($product->restaurant_id);
        }

        // ✅ 7. Format product data
        $data['products'] = Helpers::product_data_formatting($products, true, false, app()->getLocale());

        // ✅ 8. Fetch super category list (id and name only)
        $superCategories = MainCategory::select('id', 'name')->get();
        $data['super_category'] = $superCategories;

        if (auth('api')->user() !== null) {
            $customer_id = auth('api')->user()->id;
            Helpers::visitor_log('category', $customer_id, $id, false);
        }

        // ✅ 9. Final response
        return response()->json($data, 200);

    } catch (\Exception $e) {
        \Log::error('get_products Error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'category_id' => $id,
            'request' => $request->all(),
            'headers' => [
                'zoneId' => $request->header('zoneId'),
                'longitude' => $request->header('longitude'),
                'latitude' => $request->header('latitude')
            ]
        ]);
        
        return response()->json([
            'error' => 'Internal Server Error',
            'message' => config('app.debug') ? $e->getMessage() : 'Something went wrong'
        ], 500);
    }
}


// Method to fetch vendor name based on restaurant ID
private function getVendorName($restaurantId)
{
    // Fetch the vendor's full name based on restaurant_id
    return DB::table('vendors')
        ->join('restaurants', 'vendors.id', '=', 'restaurants.vendor_id')
        ->where('restaurants.id', $restaurantId)
        ->select(DB::raw("CONCAT(vendors.f_name, ' ', vendors.l_name) as owner_name"))
        ->value('owner_name');
}

//   public function get_restaurants($id, Request $request)
//     {
//         // Check if zoneId header is present
//         if (!$request->hasHeader('zoneId')) {
//             $errors = [];
//             array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
//             return response()->json(['errors' => $errors], 403);
//         }
    
//         // Validate request parameters
//         $validator = Validator::make($request->all(), [
//             'limit' => 'required',
//             'offset' => 'required',
//         ]);
    
//         if ($validator->fails()) {
//             return response()->json(['errors' => Helpers::error_processor($validator)], 403);
//         }
    
//         // Prepare additional data for restaurant retrieval
//         $additional_data = [
//             'category_id' => $id,
//             'zone_id' => json_decode($request->header('zoneId'), true),
//             'limit' => $request['limit'] ?? 25,
//             'offset' => $request['offset'] ?? 1,
//             'type' => $request->query('type', 'all') ?? 'all',
//             'longitude' => $request->header('longitude') ?? 0,
//             'latitude' => $request->header('latitude') ?? 0,
//             'veg' => $request->veg ?? 0,
//             'non_veg' => $request->non_veg ?? 0,
//             'new' => $request->new ?? 0,
//             'avg_rating' => $request->avg_rating ?? 0,
//             'top_rated' => $request->top_rated ?? 0,
//         ];
    
//         // Fetch restaurants
//         $data = CategoryLogic::restaurants($additional_data);
        
//         // Assuming the data contains restaurants, we can modify this section to include owner names
//         $restaurants = $data['restaurants'];
        
//         // Assuming the restaurants are in a collection, we can map them to include owner_name
//         foreach ($restaurants as $restaurant) {
//             $restaurant->owner_name = $this->getVendorName($restaurant->id); // Use restaurant ID to fetch vendor name
//         }
    
//         // Format the restaurant data
//         $data['restaurants'] = Helpers::restaurant_data_formatting($restaurants, true);
    
//         return response()->json($data, 200);
//     }


public function get_restaurants($id, Request $request)
{
    try {
        // Check if zoneId header is present
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json(['errors' => $errors], 403);
        }
    
        // Validate request parameters
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $zone_id = json_decode($request->header('zoneId'), true);
        $longitude = $request->header('longitude') ?? 0;
        $latitude = $request->header('latitude') ?? 0;
        
        // Calculate minimum radius for zones
        $minRadiusMeters = null;
        if (!empty($zone_id)) {
            $minRadiusKm = Zone::whereIn('id', (array) $zone_id)->min('radius');
            if (!empty($minRadiusKm) && $minRadiusKm > 0) {
                $minRadiusMeters = $minRadiusKm * 1000;
            }
        }

        // Prepare additional data for restaurant retrieval
        $additional_data = [
            'category_id' => $id,
            'zone_id' => $zone_id,
            'limit' => $request['limit'] ?? 25,
            'offset' => $request['offset'] ?? 1,
            'type' => $request->query('type', 'all') ?? 'all',
            'longitude' => $longitude,
            'latitude' => $latitude,
            'veg' => $request->veg ?? 0,
            'non_veg' => $request->non_veg ?? 0,
            'new' => $request->new ?? 0,
            'avg_rating' => $request->avg_rating ?? 0,
            'top_rated' => $request->top_rated ?? 0,
            'min_radius_meters' => $minRadiusMeters, // Add radius to data
        ];
    
        // Fetch restaurants
        $data = CategoryLogic::restaurants($additional_data);
        
        // Assuming the data contains restaurants, we can modify this section to include owner names
        $restaurants = $data['restaurants'];
        
        // Assuming the restaurants are in a collection, we can map them to include owner_name
        foreach ($restaurants as $restaurant) {
            $restaurant->owner_name = $this->getVendorName($restaurant->id); // Use restaurant ID to fetch vendor name
        }
    
        // Format the restaurant data
        $data['restaurants'] = Helpers::restaurant_data_formatting($restaurants, true);
    
        return response()->json($data, 200);
        
    } catch (\Exception $e) {
        \Log::error('get_restaurants Error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'category_id' => $id,
            'request' => $request->all()
        ]);
        
        return response()->json([
            'error' => 'Internal Server Error',
            'message' => config('app.debug') ? $e->getMessage() : 'Something went wrong'
        ], 500);
    }
}


    public function get_all_products($id,Request $request)
    {
        try {
            return response()->json(Helpers::product_data_formatting(CategoryLogic::all_products($id), true, false, app()->getLocale()), 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }
}
