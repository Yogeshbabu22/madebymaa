<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Models\Tag;
use App\Models\Food;
use App\Models\FoodSchedule;
use App\Models\Vendor;
use App\Models\Review;
use App\Models\Translation;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\VendorFoodOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class FoodController extends Controller
{

    // public function store(Request $request)
    // {
    //     if(!$request?->vendor?->restaurants[0]?->food_section)
    //     {
    //         return response()->json([
    //             'errors'=>[
    //                 ['code'=>'unauthorized', 'message'=>translate('messages.permission_denied')]
    //             ]
    //         ],403);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'category_id' => 'required',
    //         'price' => 'required|numeric|min:0.01',
    //         'discount' => 'required|numeric|min:0',
    //         'veg' => 'required|boolean',
    //         'translations'=>'required',
    //         'image' => 'nullable|max:2048',

    //     ], [
    //         'category_id.required' => translate('messages.category_required'),
    //     ]);

    //     if ($request['discount_type'] == 'percent') {
    //         $dis = ($request['price'] / 100) * $request['discount'];
    //     } else {
    //         $dis = $request['discount'];
    //     }

    //     if ($request['price'] <= $dis) {
    //         $validator->getMessageBag()->add('unit_price', translate('messages.discount_can_not_be_more_than_or_equal'));
    //     }

    //     $data = json_decode($request?->translations, true);

    //     if (count($data) < 1) {
    //         $validator->getMessageBag()->add('translations', translate('messages.Name and description in english is required'));
    //     }

    //     if ($request['price'] <= $dis || count($data) < 1 || $validator->fails()) {
    //         return response()->json(['errors' => Helpers::error_processor($validator)]);
    //     }


    //     $tag_ids = [];
    //     if ($request->tags != null) {
    //         $tags = explode(",", $request->tags);
    //     }
    //     if(isset($tags)){
    //         foreach ($tags as $key => $value) {
    //             $tag = Tag::firstOrNew(
    //                 ['tag' => $value]
    //             );
    //             $tag->save();
    //             array_push($tag_ids,$tag->id);
    //         }
    //     }


    //     $food = new Food;
    //     $food->name = $data[0]['value'];

    //     $category = [];
    //     if ($request->category_id != null) {
    //         array_push($category, [
    //             'id' => $request->category_id,
    //             'position' => 1,
    //         ]);
    //     }
    //     if ($request->sub_category_id != null) {
    //         array_push($category, [
    //             'id' => $request->sub_category_id,
    //             'position' => 2,
    //         ]);
    //     }
    //     if ($request->sub_sub_category_id != null) {
    //         array_push($category, [
    //             'id' => $request->sub_sub_category_id,
    //             'position' => 3,
    //         ]);
    //     }
    //     $food->category_id = $request?->sub_category_id ?? $request?->category_id;
    //     $food->category_ids = json_encode($category);
    //     $food->description = $data[1]['value'];

    //     // $choice_options = [];
    //     // if ($request->has('choice')) {
    //     //     foreach (json_decode($request->choice_no) as $key => $no) {
    //     //         $str = 'choice_options_' . $no;
    //     //         if ($request[$str][0] == null) {
    //     //             $validator->getMessageBag()->add('name', translate('messages.attribute_choice_option_value_can_not_be_null'));
    //     //             return response()->json(['errors' => Helpers::error_processor($validator)]);
    //     //         }
    //     //         $item['name'] = 'choice_' . $no;
    //     //         $item['title'] = json_decode($request->choice)[$key];
    //     //         $item['options'] = explode(',', implode('|', preg_replace('/\s+/', ' ', json_decode($request[$str]))));
    //     //         array_push($choice_options, $item);
    //     //     }
    //     // }
    //     $food->choice_options = json_encode([]);
    //     $variations = [];
    //     if(isset($request->options))
    //     {
    //         foreach(json_decode($request->options, true) as $option)
    //         {
    //             $temp_variation['name']= $option['name'];
    //             $temp_variation['type']= $option['type'];
    //             $temp_variation['min']= $option['min'] ?? 0;
    //             $temp_variation['max']= $option['max'] ?? 0;
    //             $temp_variation['required']= $option['required']??'off';
    //             $temp_value = [];
    //             foreach($option['values'] as $value)
    //             {
    //                 if(isset($value['label'])){
    //                     $temp_option['label'] = $value['label'];
    //                 }
    //                 $temp_option['optionPrice'] = $value['optionPrice'];
    //                 array_push($temp_value,$temp_option);
    //             }
    //             $temp_variation['values']= $temp_value;
    //             array_push($variations,$temp_variation);
    //         }
    //     }
    //     //combinations end
    //     $food->variations = json_encode($variations);
    //     $food->price = $request->price;
    //     $food->image = Helpers::upload(dir:'product/', format:'png', image: $request->file('image'));
    //     $food->available_time_starts = $request->available_time_starts;
    //     $food->available_time_ends = $request->available_time_ends;
    //     $food->discount = $request->discount ?? 0;
    //     $food->discount_type = $request->discount_type;
    //     $food->attributes = $request->has('attribute_id') ? $request->attribute_id : json_encode([]);
    //     $food->add_ons = $request->has('addon_ids') ? json_encode(explode(',',$request->addon_ids)) : json_encode([]);
    //     $food->restaurant_id = $request['vendor']->restaurants[0]->id;
    //     $food->veg = $request->veg;
    //     $food->maximum_cart_quantity = $request->maximum_cart_quantity;
    //     $food->is_halal =  $request->is_halal ?? 0;

    //     $restaurant=$request['vendor']->restaurants[0];
    //     if (  $restaurant->restaurant_model == 'subscription' ) {

    //         $rest_sub = $restaurant?->restaurant_sub;
    //         if (isset($rest_sub)) {
    //             if ($rest_sub?->max_product != "unlimited" && $rest_sub?->max_product > 0 ) {
    //                 $total_food= Food::where('restaurant_id', $restaurant->id)->count()+1;
    //                 if ( $total_food >= $rest_sub->max_product  ){
    //                     $restaurant->update(['food_section' => 0]);
    //                 }
    //             }
    //         } else{
    //             return response()->json([
    //                 'unsubscribed'=>[
    //                     ['code'=>'unsubscribed', 'message'=>translate('messages.you_are_not_subscribed_to_any_package')]
    //                 ]
    //             ]);
    //         }
    //     } elseif($restaurant->restaurant_model == 'unsubscribed'){
    //         return response()->json([
    //             'unsubscribed'=>[
    //                 ['code'=>'unsubscribed', 'message'=>translate('messages.you_are_not_subscribed_to_any_package')]
    //             ]
    //         ]);
    //     }

    //     $food->save();
    //     $food?->tags()?->sync($tag_ids);

    //     foreach ($data as $key=>$item) {
    //         $data[$key]['translationable_type'] = 'App\Models\Food';
    //         $data[$key]['translationable_id'] = $food->id;
    //     }
    //     Translation::insert($data);

    //     return response()->json(['message'=>translate('messages.product_added_successfully')], 200);
    // }




//   public function store(Request $request)
// {
//     // Validation rules for the fields
//     $validator = Validator::make($request->all(), [
//         'name' => 'required|string|max:191',
//         'category_id' => 'required',
//         'image' => 'required|max:2048',
//         'price' => 'required|numeric|between:.01,999999999999.99',
//         'discount' => 'required|numeric|min:0',
//         'restaurant_id' => 'required',
//         'description' => 'nullable|string|max:1000',
//         'veg' => 'required',
//         'ingredients' => 'nullable|string|max:191',
//         'available_times' => 'required|json'
//     ], [
//         'description.max' => translate('messages.description_length_warning'),
//         'name.required' => translate('messages.item_name_required'),
//         'category_id.required' => translate('messages.category_required'),
//         'veg.required' => translate('messages.item_type_is_required'),
//         'ingredients' => translate('messages.ingredient_name_required'),
//     ]);

//     if ($validator->fails()) {
//         return response()->json(['errors' => Helpers::error_processor($validator)]);
//     }

//     // Calculate discount amount
//     $dis = $request->discount_type == 'percent' ? ($request->price / 100) * $request->discount : $request->discount;
//     if ($request->price <= $dis) {
//         $validator->getMessageBag()->add('unit_price', translate('messages.discount_can_not_be_more_than_or_equal'));
//         return response()->json(['errors' => Helpers::error_processor($validator)]);
//     }

//     // Process tags
//     $tag_ids = [];
//     if (!empty($request->tags)) {
//         $tags = explode(",", $request->tags);
//         foreach ($tags as $value) {
//             $tag = Tag::firstOrNew(['tag' => $value]);
//             $tag->save();
//             $tag_ids[] = $tag->id;
//         }
//     }

//     // Create Food instance
//     $food = new Food;
//     $food->name = $request->name;

//     // Set category IDs
//     $category = [];
//     if ($request->category_id) {
//         $category[] = ['id' => $request->category_id, 'position' => 1];
//     }
//     if ($request->sub_category_id) {
//         $category[] = ['id' => $request->sub_category_id, 'position' => 2];
//     }
//     if ($request->sub_sub_category_id) {
//         $category[] = ['id' => $request->sub_sub_category_id, 'position' => 3];
//     }
//     $food->category_ids = json_encode($category);
//     $food->category_id = $request->sub_category_id ?? $request->category_id;

//     // Description field
//     $food->description = $request->description ?? '';

//     // Process variations
//     $variations = [];
//     if ($request->has('options') && is_string($request->options)) {
//         $options = json_decode($request->options, true); // Decode as array
//         if (is_array($options)) {
//             foreach ($options as $option) {
//                 $temp_variation = [
//                     'name' => $option['name'] ?? '',
//                     'type' => $option['type'] ?? '',
//                     'min' => $option['min'] ?? 0,
//                     'max' => $option['max'] ?? 0,
//                     'required' => $option['required'] ?? 'off',
//                     'values' => []
//                 ];
//                 if (!empty($option['values'])) {
//                     foreach ($option['values'] as $value) {
//                         $temp_variation['values'][] = [
//                             'label' => $value['label'] ?? '',
//                             'optionPrice' => $value['optionPrice'] ?? 0
//                         ];
//                     }
//                 }
//                 $variations[] = $temp_variation;
//             }
//         }
//     }
//     $food->variations = json_encode($variations);

//     // Remaining fields
//     $food->price = $request->price;
//     $food->image = Helpers::upload(dir: 'product/', format: 'png', image: $request->file('image'));
//     $food->discount = $request->discount;
//     $food->discount_type = $request->discount_type;
//     $food->attributes = json_encode($request->attribute_id ?? []);
//     // $food->add_ons = json_encode($request->addon_ids ?? []);
//     $food->add_ons = $request->addon_ids ?? [];
//     $food->restaurant_id = $request->restaurant_id;
//     $food->veg = $request->veg;
//     $food->maximum_cart_quantity = $request->maximum_cart_quantity;
//     $food->is_halal = $request->is_halal ?? 0;
//     $food->ingredients = $request->ingredients;
//     // Persist optional super category mapping (comma separated ids expected from client)
//     if ($request->has('super_category_ids')) {
//         $food->super_category_ids = (string) $request->super_category_ids;
//     }

//     $food->save();
//     $food->tags()->sync($tag_ids);

//     // Decode and validate the available times JSON
//     $availableTimesJson = $request->available_times;
//     $jsonedOb = json_decode($availableTimesJson, false);

//     if (json_last_error() !== JSON_ERROR_NONE) {
//         return response()->json(['error' => 'Invalid JSON format for available times']);
//     }

//     // Days of the week array
//     $daysOfWeek = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

//     // Loop through each day
//     foreach ($daysOfWeek as $day) {
//         // Check if day exists in the decoded JSON object and has slots
//         if (isset($jsonedOb->$day) && !empty($jsonedOb->$day->slots)) {
//             // Loop through each slot for the day
//             foreach ($jsonedOb->$day->slots as $slot) {
//                 // Validate start and end times
//                 if (!empty($slot->start) && !empty($slot->end)) {
//                     // Save each slot to the FoodSchedule model
//                     FoodSchedule::create([
//                         'food_id' => $food->id,
//                         'day' => ucfirst($day), // Capitalize the day if needed
//                         'available_time_start' => $slot->start,
//                         'available_time_end' => $slot->end,
//                     ]);
//                 }
//             }
//         }
//     }

//     // Persist the raw JSON of available times as well (for reference/editing)
//     $food->new_available_times = $availableTimesJson;
//     $food->save();

//     // Prepare full response data
//     $foodData = $food->toArray();
//     $foodData['tags'] = $tag_ids;
//     $foodData['available_times'] = $availableTimesJson;

//     return response()->json(['success' => translate('messages.food_added_successfully'), 'data' => $foodData]);
// }
public function store(Request $request)
{
    // Validation rules for the fields
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:191',
        'category_id' => 'required',
        'image' => 'required|max:2048',
        'price' => 'required|numeric|between:.01,999999999999.99',
        'discount' => 'required|numeric|min:0',
        'restaurant_id' => 'required',
        'description' => 'nullable|string|max:1000',
        'veg' => 'required',
        'ingredients' => 'nullable|string|max:191',
        'available_times' => 'required|json'
    ], [
        'description.max' => translate('messages.description_length_warning'),
        'name.required' => translate('messages.item_name_required'),
        'category_id.required' => translate('messages.category_required'),
        'veg.required' => translate('messages.item_type_is_required'),
        'ingredients' => translate('messages.ingredient_name_required'),
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => Helpers::error_processor($validator)]);
    }

    // Calculate discount amount
    $dis = $request->discount_type == 'percent' ? ($request->price / 100) * $request->discount : $request->discount;
    if ($request->price <= $dis) {
        $validator->getMessageBag()->add('unit_price', translate('messages.discount_can_not_be_more_than_or_equal'));
        return response()->json(['errors' => Helpers::error_processor($validator)]);
    }

    // Process tags
    $tag_ids = [];
    if (!empty($request->tags)) {
        $tags = explode(",", $request->tags);
        foreach ($tags as $value) {
            $tag = Tag::firstOrNew(['tag' => $value]);
            $tag->save();
            $tag_ids[] = $tag->id;
        }
    }

    // Create Food instance
    $food = new Food;
    $food->name = $request->name;

    // Set category IDs
    $category = [];
    if ($request->category_id) {
        $category[] = ['id' => $request->category_id, 'position' => 1];
    }
    if ($request->sub_category_id) {
        $category[] = ['id' => $request->sub_category_id, 'position' => 2];
    }
    if ($request->sub_sub_category_id) {
        $category[] = ['id' => $request->sub_sub_category_id, 'position' => 3];
    }
    $food->category_ids = json_encode($category);
    $food->category_id = $request->sub_category_id ?? $request->category_id;

    // Description field
    $food->description = $request->description ?? '';

    // Process variations
    $variations = [];
    if ($request->has('options') && is_string($request->options)) {
        $options = json_decode($request->options, true); // Decode as array
        if (is_array($options)) {
            foreach ($options as $option) {
                $temp_variation = [
                    'name' => $option['name'] ?? '',
                    'type' => $option['type'] ?? '',
                    'min' => $option['min'] ?? 0,
                    'max' => $option['max'] ?? 0,
                    'required' => $option['required'] ?? 'off',
                    'values' => []
                ];
                if (!empty($option['values'])) {
                    foreach ($option['values'] as $value) {
                        $temp_variation['values'][] = [
                            'label' => $value['label'] ?? '',
                            'optionPrice' => $value['optionPrice'] ?? 0
                        ];
                    }
                }
                $variations[] = $temp_variation;
            }
        }
    }
    $food->variations = json_encode($variations);

    // Remaining fields
    $food->price = $request->price;
    $food->image = Helpers::upload(dir: 'product/', format: 'png', image: $request->file('image'));
    $food->discount = $request->discount;
    $food->discount_type = $request->discount_type;
    $food->attributes = json_encode($request->attribute_id ?? []);
    // $food->add_ons = json_encode($request->addon_ids ?? []);
    $food->add_ons = $request->addon_ids ?? [];
    $food->restaurant_id = $request->restaurant_id;
    $food->veg = $request->veg;
    $food->maximum_cart_quantity = $request->maximum_cart_quantity;
    $food->is_halal = $request->is_halal ?? 0;
    $food->ingredients = $request->ingredients;
    // Persist optional super category mapping (comma separated ids expected from client)
    if ($request->has('super_category_ids')) {
        $food->super_category_ids = (string) $request->super_category_ids;
    }

    $food->save();
    $food->tags()->sync($tag_ids);

    // After $food->save(), process available_times
    $availableTimesJson = $request->available_times;
    $parsed = json_decode($availableTimesJson, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return response()->json(['error' => 'Invalid JSON format for available times']);
    }
    // Remove previous schedules for this food
    \App\Models\FoodSchedule::where('food_id', $food->id)->delete();

    // Load all main categories for name/slug lookup
    $mainCategories = \App\Models\MainCategory::all();
    $mealsMap = [];
    foreach ($mainCategories as $mcat) {
        $mealsMap[strtolower($mcat->name)] = $mcat;
        if (!empty($mcat->slug)) {
            $mealsMap[strtolower($mcat->slug)] = $mcat;
        }
    }
    // For each day & meal availability
    foreach ($parsed as $day => $meals) {
        foreach ($meals as $mealName => $mealInfo) {
            if (isset($mealInfo['available']) && $mealInfo['available']) {
                // Find the MainCategory record (by slug or name, case-insensitive)
                $mealKey = strtolower($mealName);
                $mainCat = $mealsMap[$mealKey] ?? null;
                if ($mainCat) {
                    $start = $mainCat->start_time ? $mainCat->start_time->format('H:i') : '00:00';
                    $end = $mainCat->end_time ? $mainCat->end_time->format('H:i') : '23:59';
                    \App\Models\FoodSchedule::create([
                        'food_id' => $food->id,
                        'day' => ucfirst($day),
                        'main_category_id' => $mainCat->id,
                        'available_time_start' => $start,
                        'available_time_end' => $end,
                    ]);
                }
            }
        }
    }
    // Store reference JSON
    $food->new_available_times = $availableTimesJson;
    $food->save();

    // Prepare full response data
    $foodData = $food->toArray();
    $foodData['tags'] = $tag_ids;
    $foodData['available_times'] = $availableTimesJson;

    return response()->json(['success' => translate('messages.food_added_successfully'), 'data' => $foodData]);
}

private function getVariations(Request $request)
{
    $variations = [];

    // Ensure 'options' is present and a valid JSON string
    if ($request->has('options') && !empty($request->options)) {
        $options = json_decode($request->options, true); // Decode JSON to array

        // Check if decoding was successful
        if (json_last_error() === JSON_ERROR_NONE) {
            foreach ($options as $option) {
                $temp_variation = [
                    'name' => $option['name'],
                    'type' => $option['type'],
                    'min' => $option['min'] ?? 0,
                    'max' => $option['max'] ?? 0,
                    'required' => $option['required'] ?? 'off',
                    'values' => [],
                ];

                // Ensure the 'values' field is an array before looping
                if (isset($option['values']) && is_array($option['values'])) {
                    foreach ($option['values'] as $value) {
                        $temp_option = [
                            'label' => $value['label'] ?? null,
                            'optionPrice' => $value['optionPrice'] ?? 0, // Handle missing 'optionPrice'
                        ];
                        $temp_variation['values'][] = $temp_option;
                    }
                }

                $variations[] = $temp_variation;
            }
        } else {
            // Handle invalid JSON format error
            return response()->json(['error' => 'Invalid options format.'], 400);
        }
    }

    return $variations;
}


private function checkSubscription(Request $request, Food $food)
{
    $restaurant = $request['vendor']->restaurants[0];

    if ($restaurant->restaurant_model == 'subscription') {
        $rest_sub = $restaurant->restaurant_sub;
        if (isset($rest_sub)) {
            if ($rest_sub->max_product != "unlimited" && $rest_sub->max_product > 0) {
                $total_food = Food::where('restaurant_id', $restaurant->id)->count() + 1;
                if ($total_food >= $rest_sub->max_product) {
                    $restaurant->update(['food_section' => 0]);
                }
            }
        } else {
            return response()->json([
                'unsubscribed' => [
                    ['code' => 'unsubscribed', 'message' => translate('messages.you_are_not_subscribed_to_any_package')]
                ]
            ]);
        }
    } elseif ($restaurant->restaurant_model == 'unsubscribed') {
        return response()->json([
            'unsubscribed' => [
                ['code' => 'unsubscribed', 'message' => translate('messages.you_are_not_subscribed_to_any_package')]
            ]
        ]);
    }
}

    public function status(Request $request)
    {
        if(!$request?->vendor?->restaurants[0]?->food_section)
        {
            return response()->json([
                'errors'=>[
                    ['code'=>'unauthorized', 'message'=>translate('messages.permission_denied')]
                ]
            ],403);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $product = Food::find($request->id);
        $product->status = $request->status;
        $product?->save();

        if($request->status != 1){
            $product?->carts()?->delete();
        }
        return response()->json(['message' => translate('messages.product_status_updated')], 200);
    }

    public function get_product($id)
    {

        // try {
            $item = Food::withoutGlobalScope('translate')->with('tags')->where('id',$id)
            ->first();
            $item = Helpers::product_data_formatting_translate($item, false, false, app()->getLocale());
            return response()->json($item, 200);
        // } catch (\Exception $e) {
        //     return response()->json([
        //         'errors' => ['code' => 'product-001', 'message' => translate('messages.not_found')]
        //     ], 404);
        // }
    }

    public function recommended(Request $request)
    {
        if(!$request?->vendor?->restaurants[0]?->food_section)
        {
            return response()->json([
                'errors'=>[
                    ['code'=>'unauthorized', 'message'=>translate('messages.permission_denied')]
                ]
            ],403);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $product = Food::find($request->id);
        $product->recommended = $request->status;
        $product?->save();

        return response()->json(['message' => translate('messages.product_recommended_status_updated')], 200);

    }




    public function update(Request $request)
    {
        if(!$request?->vendor?->restaurants[0]?->food_section)
        {
            return response()->json([
                'errors'=>[
                    ['code'=>'unauthorized', 'message'=>translate('messages.permission_denied')]
                ]
            ],403);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'category_id' => 'required',
            'price' => 'required|numeric|min:0.01',
            'discount' => 'required|numeric|min:0',
            'veg' => 'required|boolean',
            'image' => 'nullable|max:2048',

        ], [
            'category_id.required' => translate('messages.category_required'),
        ]);

        if ($request['discount_type'] == 'percent') {
            $dis = ($request['price'] / 100) * $request['discount'];
        } else {
            $dis = $request['discount'];
        }

        if ($request['price'] <= $dis) {
            $validator->getMessageBag()->add('unit_price', translate('messages.discount_can_not_be_more_than_or_equal'));
        }
        $data = json_decode($request->translations, true);

        if (count($data) < 1) {
            $validator->getMessageBag()->add('translations', translate('messages.Name and description in english is required'));
        }

        if ($request['price'] <= $dis || count($data) < 1 || $validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }


        $tag_ids = [];
        if ($request->tags != null) {
            $tags = explode(",", $request->tags);
        }
        if(isset($tags)){
            foreach ($tags as $key => $value) {
                $tag = Tag::firstOrNew(
                    ['tag' => $value]
                );
                $tag->save();
                array_push($tag_ids,$tag->id);
            }
        }

        $p = Food::findOrFail($request->id);

        $p->name = $data[0]['value'];

        $category = [];
        if ($request->category_id != null) {
            array_push($category, [
                'id' => $request->category_id,
                'position' => 1,
            ]);
        }
        if ($request->sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_category_id,
                'position' => 2,
            ]);
        }
        if ($request->sub_sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_sub_category_id,
                'position' => 3,
            ]);
        }

        $p->category_id = $request?->sub_category_id ?? $request->category_id;
        $p->category_ids = json_encode($category);
        $p->description = $data[1]['value'];

        // $choice_options = [];
        // if ($request->has('choice')) {
        //     foreach (json_decode($request->choice_no) as $key => $no) {
        //         $str = 'choice_options_' . $no;
        //         if (json_decode($request[$str])[0] == null) {
        //             $validator->getMessageBag()->add('name', translate('messages.attribute_choice_option_value_can_not_be_null'));
        //             return response()->json(['errors' => Helpers::error_processor($validator)]);
        //         }
        //         $item['name'] = 'choice_' . $no;
        //         $item['title'] = json_decode($request->choice)[$key];
        //         $item['options'] = explode(',', implode('|', preg_replace('/\s+/', ' ', json_decode($request[$str]))));
        //         array_push($choice_options, $item);
        //     }
        // }
        $p->choice_options = json_encode([]);
        $variations = [];
        if(isset($request->options))
        {
            foreach(json_decode($request->options,true) as $key=>$option)
            {
                $temp_variation['name']= $option['name'];
                $temp_variation['type']= $option['type'];
                $temp_variation['min']= $option['min'] ?? 0;
                $temp_variation['max']= $option['max'] ?? 0;
                $temp_variation['required']= $option['required']??'off';
                $temp_value = [];
                foreach($option['values'] as $value)
                {
                    if(isset($value['label'])){
                        $temp_option['label'] = $value['label'];
                    }
                    $temp_option['optionPrice'] = $value['optionPrice'];
                    array_push($temp_value,$temp_option);
                }
                $temp_variation['values']= $temp_value;
                array_push($variations,$temp_variation);
            }
        }

        $slug = Str::slug($p->name);
        $p->slug = $p->slug? $p->slug :"{$slug}-{$p->id}";

        $p->variations = json_encode($variations);
        $p->price = $request->price;
        $p->image = $request->has('image') ? Helpers::update(dir:'product/', old_image:$p->image,  format:'png', image: $request->file('image')) : $p->image;
        $p->available_time_starts = $request->available_time_starts;
        $p->available_time_ends = $request->available_time_ends;
        $p->discount = $request->discount ?? 0;
        $p->discount_type = $request->discount_type;
        $p->attributes = $request->has('attribute_id') ? $request->attribute_id : json_encode([]);
        $p->add_ons = $request->has('addon_ids') ? json_encode(explode(',',$request->addon_ids)) : json_encode([]);
        $p->veg = $request->veg;
        $p->ingredients = $request->ingredients;
        if ($request->has('super_category_ids')) {
            $p->super_category_ids = (string) $request->super_category_ids;
        }
        $p->maximum_cart_quantity = $request->maximum_cart_quantity;
        $p->is_halal =  $request->is_halal ?? 0;
        if ($request->filled('available_times')) {
            $p->new_available_times = $request->available_times;
        }

        $p?->save();
        $p?->tags()?->sync($tag_ids);

        foreach ($data as $key=>$item) {
            Translation::updateOrInsert(
                ['translationable_type' => 'App\Models\Food',
                    'translationable_id' => $p->id,
                    'locale' => $item['locale'],
                    'key' => $item['key']],
                ['value' => $item['value']]
            );
        }

        return response()->json(['message'=>translate('messages.product_updated_successfully')], 200);
    }




    public function updates(Request $request, $id)
{
    // Validation rules for the fields
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:191',
        'category_id' => 'required',
        'image' => 'nullable|image|max:2048', // Allow image to be nullable
        'price' => 'required|numeric|between:.01,999999999999.99',
        'discount' => 'required|numeric|min:0',
        'restaurant_id' => 'required',
        'description' => 'nullable|string|max:1000',
        'veg' => 'required', // Ensure veg is required, but we will map it properly below
        'ingredients' => 'nullable|string|max:191',
        'available_times' => 'required|json'
    ], [
        'description.max' => translate('messages.description_length_warning'),
        'name.required' => translate('messages.item_name_required'),
        'category_id.required' => translate('messages.category_required'),
        'veg.required' => translate('messages.item_type_is_required'),
        'ingredients' => translate('messages.ingredient_name_required'),
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => Helpers::error_processor($validator)]);
    }

    // Calculate discount amount
    $dis = $request->discount_type == 'percent' ? ($request->price / 100) * $request->discount : $request->discount;
    if ($request->price <= $dis) {
        $validator->getMessageBag()->add('unit_price', translate('messages.discount_can_not_be_more_than_or_equal'));
        return response()->json(['errors' => Helpers::error_processor($validator)]);
    }

    // Find the food item to update
    $food = Food::find($id);
    if (!$food) {
        return response()->json(['error' => 'Food item not found'], 404);
    }

    // Update fields
    $food->name = $request->name;

    // Set category IDs same as store method
    $category = [];
    if ($request->category_id) {
        $category[] = ['id' => $request->category_id, 'position' => 1];
    }
    if ($request->sub_category_id) {
        $category[] = ['id' => $request->sub_category_id, 'position' => 2];
    }
    if ($request->sub_sub_category_id) {
        $category[] = ['id' => $request->sub_sub_category_id, 'position' => 3];
    }
    $food->category_ids = json_encode($category);
    $food->category_id = $request->sub_category_id ?? $request->category_id;

    $food->price = $request->price;
    $food->discount = $request->discount;
    $food->discount_type = $request->discount_type;
    $food->restaurant_id = $request->restaurant_id;
    $food->description = $request->description ?? '';
    $food->veg = $request->veg; // Same as store method
    $food->ingredients = $request->ingredients;
    $food->add_ons = $request->has('addon_ids') ? $request->addon_ids : [];

    // Persist optional super category mapping (comma separated ids expected from client)
    if ($request->has('super_category_ids')) {
        $food->super_category_ids = (string) $request->super_category_ids;
    }

    $food->maximum_cart_quantity = $request->maximum_cart_quantity;
    $food->is_halal = $request->is_halal ?? 0;
    $food->attributes = json_encode($request->attribute_id ?? []);

    // Update image only if provided
    if ($request->hasFile('image')) {
        $food->image = Helpers::upload(dir: 'product/', format: 'png', image: $request->file('image'));
    }

    // Update tags
    $tag_ids = [];
    if (!empty($request->tags)) {
        $tags = explode(",", $request->tags);
        foreach ($tags as $value) {
            $tag = Tag::firstOrNew(['tag' => $value]);
            $tag->save();
            $tag_ids[] = $tag->id;
        }
    }
    $food->tags()->sync($tag_ids);

    // Update variations
    $variations = [];
    if ($request->has('options') && is_string($request->options)) {
        $options = json_decode($request->options, true);
        if (is_array($options)) {
            foreach ($options as $option) {
                $temp_variation = [
                    'name' => $option['name'] ?? '',
                    'type' => $option['type'] ?? '',
                    'min' => $option['min'] ?? 0,
                    'max' => $option['max'] ?? 0,
                    'required' => $option['required'] ?? 'off',
                    'values' => []
                ];
                if (!empty($option['values'])) {
                    foreach ($option['values'] as $value) {
                        $temp_variation['values'][] = [
                            'label' => $value['label'] ?? '',
                            'optionPrice' => $value['optionPrice'] ?? 0
                        ];
                    }
                }
                $variations[] = $temp_variation;
            }
        }
    }
    $food->variations = json_encode($variations);

    // Process available_times same as store method
    $availableTimesJson = $request->available_times;
    $parsed = json_decode($availableTimesJson, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return response()->json(['error' => 'Invalid JSON format for available times']);
    }
    // Remove previous schedules for this food
    \App\Models\FoodSchedule::where('food_id', $food->id)->delete();

    // Load all main categories for name/slug lookup
    $mainCategories = \App\Models\MainCategory::all();
    $mealsMap = [];
    foreach ($mainCategories as $mcat) {
        $mealsMap[strtolower($mcat->name)] = $mcat;
        if (!empty($mcat->slug)) {
            $mealsMap[strtolower($mcat->slug)] = $mcat;
        }
    }
    // For each day & meal availability
    foreach ($parsed as $day => $meals) {
        foreach ($meals as $mealName => $mealInfo) {
            if (isset($mealInfo['available']) && $mealInfo['available']) {
                // Find the MainCategory record (by slug or name, case-insensitive)
                $mealKey = strtolower($mealName);
                $mainCat = $mealsMap[$mealKey] ?? null;
                if ($mainCat) {
                    $start = $mainCat->start_time ? $mainCat->start_time->format('H:i') : '00:00';
                    $end = $mainCat->end_time ? $mainCat->end_time->format('H:i') : '23:59';
                    \App\Models\FoodSchedule::create([
                        'food_id' => $food->id,
                        'day' => ucfirst($day),
                        'main_category_id' => $mainCat->id,
                        'available_time_start' => $start,
                        'available_time_end' => $end,
                    ]);
                }
            }
        }
    }
    // Store reference JSON
    $food->new_available_times = $availableTimesJson;

    // Save the updated food item
    $food->save();

    // Prepare full response data
    $foodData = $food->toArray();
    $foodData['tags'] = $tag_ids;
    $foodData['available_times'] = $availableTimesJson;

    return response()->json(['success' => translate('messages.food_updated_successfully'), 'data' => $foodData]);
}













    public function delete(Request $request)
    {
        if(!$request?->vendor?->restaurants[0]?->food_section)
        {
            return response()->json([
                'errors'=>[
                    ['code'=>'unauthorized', 'message'=>translate('messages.permission_denied')]
                ]
            ],403);
        }
        $product = Food::findOrFail($request->id);

        if($product?->image)
        {
            if (Storage::disk('public')->exists('product/' . $product['image'])) {
                Storage::disk('public')->delete('product/' . $product['image']);
            }
        }
        $product?->carts()?->delete();
        $product?->translations()?->delete();
        $product?->delete();

        return response()->json(['message'=>translate('messages.product_deleted_successfully')], 200);
    }

    public function search(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $key = explode(' ', $request['name']);

        $products = Food::active()
        ->with(['rating'])
        ->where('restaurant_id', $request['vendor']?->restaurants[0]?->id)
        ->when($request->category_id, function($query)use($request){
            $query->whereHas('category',function($q)use($request){
                return $q->whereId($request->category_id)->orWhere('parent_id', $request->category_id);
            });
        })
        ->when($request->restaurant_id, function($query) use($request){
            return $query->where('restaurant_id', $request->restaurant_id);
        })
        ->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('name', 'like', "%{$value}%");
            }
            $q->orWhereHas('tags',function($query)use($key){
                $query->where(function($q)use($key){
                    foreach ($key as $value) {
                        $q->where('tag', 'like', "%{$value}%");
                    };
                });
            });
        })
        ->limit(50)
        ->get();

        $data = Helpers::product_data_formatting(data:$products,multi_data: true,trans: false, local:app()->getLocale());
        return response()->json($data, 200);
    }

    public function reviews(Request $request)
    {
        $id = $request['vendor']?->restaurants[0]?->id;;

        $reviews = Review::with(['customer', 'food'])
        ->whereHas('food', function($query)use($id){
            return $query->where('restaurant_id', $id);
        })
        ->latest()->get();

        $storage = [];
        foreach ($reviews as $item) {
            $item['attachment'] = json_decode($item['attachment']);
            $item['food_name'] = null;
            $item['food_image'] = null;
            $item['customer_name'] = null;
            if($item->food)
            {
                $item['food_name'] = $item?->food?->name;
                $item['food_image'] = $item?->food?->image;
                if(count($item?->food?->translations)>0)
                {
                    $translate = array_column($item?->food?->translations?->toArray(), 'value', 'key');
                    $item['food_name'] = $translate['name'];
                }
            }

            if($item->customer)
            {
                $item['customer_name'] = $item?->customer?->f_name.' '.$item?->customer?->l_name;
            }

            unset($item['food']);
            unset($item['customer']);
            array_push($storage, $item);
        }

        return response()->json($storage, 200);
    }


     public function addSchedule(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            // 'vendor_id' => 'required|exists:vendors,id', // Ensure vendor_id exists in vendors table
            'food_id' => 'required|exists:food,id',      // Ensure food_id exists in food table
            'schedules' => 'required|array',
            'schedules.*.day' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'schedules.*.available_time_start' => 'required|date_format:H:i',
            'schedules.*.available_time_end' => 'required|date_format:H:i|after:schedules.*.available_time_start',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Fetch the food item by ID from the request
        $food = Food::findOrFail($request->food_id);

        // Clear previous schedules and add new ones
        $food->schedules()->delete();
        foreach ($request->schedules as $schedule) {
            FoodSchedule::create([
                'food_id' => $food->id,
                // 'vendor_id' => $request->vendor_id, // Add vendor_id from the request
                'day' => $schedule['day'],
                'available_time_start' => $schedule['available_time_start'],
                'available_time_end' => $schedule['available_time_end'],
            ]);
        }

        // Return a response with food_id included
        return response()->json([
            'message' => 'Schedule added successfully',
            'food_id' => $food->id
        ], 200);
    }


    public function updateSchedule(Request $request)
{
    // Validate incoming request
    $validator = Validator::make($request->all(), [
        'food_id' => 'required|exists:food,id',  // Ensure food_id exists in food table
        'schedules' => 'required|array',
        'schedules.*.day' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        'schedules.*.available_time_start' => 'required|date_format:H:i',
        'schedules.*.available_time_end' => 'required|date_format:H:i|after:schedules.*.available_time_start',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Fetch the food item by ID from the request and verify vendor ownership
    $food = Food::where('id', $request->food_id)->firstOrFail();

    // Clear previous schedules and update with new ones
    $food->schedules()->delete();
    foreach ($request->schedules as $schedule) {
        FoodSchedule::create([
            'food_id' => $food->id,
            'day' => $schedule['day'],
            'available_time_start' => $schedule['available_time_start'],
            'available_time_end' => $schedule['available_time_end'],
        ]);
    }

    return response()->json([
        'message' => 'Schedule updated successfully',
        'food_id' => $food->id
    ], 200);
}

public function listSchedule($food_id)
{
    // Validate that the food item exists
    $food = Food::find($food_id);
    if (!$food) {
        return response()->json([
            'errors' => [
                ['code' => 'food-001', 'message' => 'Food item not found.']
            ]
        ], 404);
    }

    // Retrieve schedules for the specified food item
    $schedules = $food->schedules()->get(['day', 'available_time_start', 'available_time_end']);

    return response()->json([
        'food_id' => $food_id,
        'schedules' => $schedules
    ], 200);
}

public function createOrder(Request $request): JsonResponse
{
    // Validate the incoming request data
    $validator = Validator::make($request->all(), [
        'food_id' => 'required|integer|exists:food,id',
        'pre_order' => 'boolean',
        'instant_order' => 'boolean',
    ]);

    // Check if the vendor is authenticated
    $vendor = $request['vendor']; // Use 'api' guard to get the authenticated user

    if (!$vendor) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $vendor_id = $vendor->id; // Get the authenticated vendor's ID

    // Check if the food_id belongs to the vendor's restaurant using a direct query
    $foodExistsForVendor = DB::table('food')
        ->join('restaurants', 'food.restaurant_id', '=', 'restaurants.id')
        ->where('food.id', $request->food_id)
        ->where('restaurants.vendor_id', $vendor_id) // Assuming there's a vendor_id in restaurants table
        ->exists();

    if (!$foodExistsForVendor) {
        return response()->json(['message' => 'Food item does not belong to the specified vendor'], 404);
    }

    // Check if an order already exists for the vendor and food
    $existingOrder = VendorFoodOrder::where('vendor_id', $vendor_id)
        ->where('food_id', $request->food_id)
        ->first();

    if ($existingOrder) {
        return response()->json(['message' => 'Order already exists for this vendor and food item'], 409);
    }

    // Return validation errors if any
    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    // Validate and create the order with defaults for optional fields
    $validated = $validator->validated();

    $order = VendorFoodOrder::create([
        'vendor_id' => $vendor_id, // Use the vendor_id from the authenticated user
        'food_id' => $validated['food_id'],
        'pre_order' => $validated['pre_order'] ?? false, // Default to false if not provided
        'instant_order' => $validated['instant_order'] ?? false, // Default to false if not provided
    ]);

    return response()->json(['message' => 'Order data stored successfully', 'data' => $order], 201);
}




public function showOrder($vendor_id, $food_id): JsonResponse
{
    $order = VendorFoodOrder::where('vendor_id', $vendor_id)->where('food_id', $food_id)->first();

    if (!$order) {
        return response()->json(['message' => 'Order data not found'], 404);
    }

    return response()->json(['data' => $order], 200);
}







    public function updateOrder(Request $request, $food_id): JsonResponse
{
    // Get the authenticated vendor from the token
    $vendor = $request['vendor']; // Assuming vendor is authenticated via token and 'api' guard is used

    if (!$vendor) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }


    // Validate only if provided; fields are optional
    $request->validate([
        'pre_order' => 'nullable|boolean',
        'instant_order' => 'nullable|boolean',
    ]);

    // Find the order based on the vendor's ID and the provided food ID
    $order = VendorFoodOrder::where('vendor_id', $vendor->id)->where('food_id', $food_id)->first();


    if (!$order) {

        $newOrder =  new VendorFoodOrder();
        $newOrder->pre_order =  $request->pre_order;
        $newOrder->instant_order =  $request->instant_order;
        $newOrder->vendor_id =  $vendor->id;
        $newOrder->food_id = $food_id;
        $newOrder->save();

        return response()->json(['message'=>'Data saved', 'data' => $newOrder],200);


    }
    // Update the order with the provided values or retain existing values if not provided
    $order->update([
        'pre_order' => $request->has('pre_order') ? $request->pre_order : $order->pre_order,
        'instant_order' => $request->has('instant_order') ? $request->instant_order : $order->instant_order,
    ]);

    return response()->json(['message' => 'Order data updated successfully', 'data' => $order], 200);
}







public function destroyOrder($vendor_id, $food_id): JsonResponse
{
    $order = VendorFoodOrder::where('vendor_id', $vendor_id)->where('food_id', $food_id)->first();

    if (!$order) {
        return response()->json(['message' => 'Order data not found'], 404);
    }

    $order->delete();

    return response()->json(['message' => 'Order data deleted successfully'], 200);
}

}
