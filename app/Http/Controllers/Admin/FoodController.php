<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Tag;
use App\Models\Food;
use App\Models\Review;
use App\Models\Category;
use App\Models\MainCategory;
use App\Models\Restaurant;
use App\Models\Translation;
use App\Models\FoodSchedule;
use Illuminate\Support\Str;
use App\Models\ItemCampaign;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Exports\FoodListExport;
use App\Scopes\RestaurantScope;
use App\Exports\FoodReviewExport;
use Illuminate\Support\Facades\DB;
use App\CentralLogics\ProductLogic;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Maatwebsite\Excel\Facades\Excel;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Exports\RestaurantFoodExport;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class FoodController extends Controller
{
    public function index()
    {
        $categories = Category::where(['position' => 0])->get();
        $mainCategories = MainCategory::active()->get();
        return view('admin-views.product.index', compact('categories', 'mainCategories'));
    }

    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'name.0' => 'required',
    //         'name.*' => 'max:191',
    //         'category_id' => 'required',
    //         'image' => 'required|max:2048',
    //         'price' => 'required|numeric|between:.01,999999999999.99',
    //         'discount' => 'required|numeric|min:0',
    //         'restaurant_id' => 'required',
    //         'description.*' => 'max:1000',
    //         'veg'=>'required'
    //     ], [
    //         'description.*.max' => translate('messages.description_length_warning'),
    //         'name.0.required' => translate('messages.item_name_required'),
    //         'category_id.required' => translate('messages.category_required'),
    //         'veg.required'=>translate('messages.item_type_is_required')
    //     ]);


    //     if ($request['discount_type'] == 'percent') {
    //         $dis = ($request['price'] / 100) * $request['discount'];
    //     } else {
    //         $dis = $request['discount'];
    //     }

    //     if ($request['price'] <= $dis) {
    //         $validator->getMessageBag()->add('unit_price', translate('messages.discount_can_not_be_more_than_or_equal'));
    //     }

    //     if ($request['price'] <= $dis || $validator->fails()) {
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
    //     $food->name = $request->name[array_search('default', $request->lang)];

    //     $category = [];
    //     if ($request->category_id != null) {
    //         $category[] = [
    //             'id' => $request->category_id,
    //             'position' => 1,
    //         ];
    //     }
    //     if ($request->sub_category_id != null) {
    //         $category[] = [
    //             'id' => $request->sub_category_id,
    //             'position' => 2,
    //         ];
    //     }
    //     if ($request->sub_sub_category_id != null) {
    //         $category[] = [
    //             'id' => $request->sub_sub_category_id,
    //             'position' => 3,
    //         ];
    //     }

    //     $food->category_ids = json_encode($category);
    //     $food->category_id = $request?->sub_category_id ?? $request?->category_id;
    //     $food->description =  $request->description[array_search('default', $request->lang)];
    //     $food->choice_options = json_encode([]);

    //     $variations = [];
    //     if(isset($request->options))
    //     {
    //         foreach(array_values($request->options) as $key=>$option)
    //         {

    //             $temp_variation['name']= $option['name'];
    //             $temp_variation['type']= $option['type'];
    //             $temp_variation['min']= $option['min'] ?? 0;
    //             $temp_variation['max']= $option['max'] ?? 0;
    //             $temp_variation['required']= $option['required']??'off';
    //             if($option['min'] > 0 &&  $option['min'] > $option['max']  ){
    //                 $validator->getMessageBag()->add('name', translate('messages.minimum_value_can_not_be_greater_then_maximum_value'));
    //                 return response()->json(['errors' => Helpers::error_processor($validator)]);
    //             }
    //             if(!isset($option['values'])){
    //                 $validator->getMessageBag()->add('name', translate('messages.please_add_options_for').$option['name']);
    //                 return response()->json(['errors' => Helpers::error_processor($validator)]);
    //             }
    //             if($option['max'] > count($option['values'])  ){
    //                 $validator->getMessageBag()->add('name', translate('messages.please_add_more_options_or_change_the_max_value_for').$option['name']);
    //                 return response()->json(['errors' => Helpers::error_processor($validator)]);
    //             }
    //             $temp_value = [];

    //             foreach(array_values($option['values']) as $value)
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
    //     $food->image = Helpers::upload(dir: 'product/', format:'png', image:$request->file('image'));
    //     $food->available_time_starts = $request->available_time_starts;
    //     $food->available_time_ends = $request->available_time_ends;
    //     $food->discount =  $request->discount ?? 0;
    //     $food->discount_type = $request->discount_type;

    //     $food->attributes = $request->has('attribute_id') ? json_encode($request->attribute_id) : json_encode([]);
    //     $food->add_ons = $request->has('addon_ids') ? json_encode($request->addon_ids) : json_encode([]);
    //     $food->restaurant_id = $request->restaurant_id;
    //     $food->veg = $request->veg;
    //     $food->maximum_cart_quantity = $request->maximum_cart_quantity;
    //     $food->is_halal =  $request->is_halal ?? 0;
    //     $food->save();
    //     $food->tags()->sync($tag_ids);

    //     $data = [];
    //     $default_lang = str_replace('_', '-', app()->getLocale());
    //     foreach ($request->lang as $index => $key) {
    //         if($default_lang == $key && !($request->name[$index])){
    //             if ($key != 'default') {
    //                 array_push($data, array(
    //                     'translationable_type' => 'App\Models\Food',
    //                     'translationable_id' => $food->id,
    //                     'locale' => $key,
    //                     'key' => 'name',
    //                     'value' => $food->name,
    //                 ));
    //             }
    //         }else{
    //             if ($request->name[$index] && $key != 'default') {
    //                 array_push($data, array(
    //                     'translationable_type' => 'App\Models\Food',
    //                     'translationable_id' => $food->id,
    //                     'locale' => $key,
    //                     'key' => 'name',
    //                     'value' => $request->name[$index],
    //                 ));
    //             }
    //         }
    //         if($default_lang == $key && !($request->description[$index])){
    //             if ($key != 'default') {
    //                 array_push($data, array(
    //                     'translationable_type' => 'App\Models\Food',
    //                     'translationable_id' => $food->id,
    //                     'locale' => $key,
    //                     'key' => 'description',
    //                     'value' => $food->description,
    //                 ));
    //             }
    //         }else{
    //             if ($request->description[$index] && $key != 'default') {
    //                 array_push($data, array(
    //                     'translationable_type' => 'App\Models\Food',
    //                     'translationable_id' => $food->id,
    //                     'locale' => $key,
    //                     'key' => 'description',
    //                     'value' => $request->description[$index],
    //                 ));
    //             }
    //         }

    //     }
    //     Translation::insert($data);

    //     return response()->json([], 200);
    // }

//         public function store(Request $request)
// {
//     $validator = Validator::make($request->all(), [
//         'name.0' => 'required',
//         'name.*' => 'max:191',
//         'category_id' => 'required',
//         'image' => 'required|max:2048',
//         'price' => 'required|numeric|between:.01,999999999999.99',
//         'discount' => 'required|numeric|min:0',
//         'restaurant_id' => 'required',
//         'description.*' => 'max:1000',
//         'ingredients' => 'required',
//         'veg' => 'required',
//     ], [
//         'description.*.max' => translate('messages.description_length_warning'),
//         'name.0.required' => translate('messages.item_name_required'),
//         'category_id.required' => translate('messages.category_required'),
//         'ingredients.required' => translate('messages.ingredients_required'),
//         'veg.required' => translate('messages.item_type_is_required'),
//     ]);

//     // Discount calculation
//     if ($request['discount_type'] == 'percent') {
//         $dis = ($request['price'] / 100) * $request['discount'];
//     } else {
//         $dis = $request['discount'];
//     }

//     // Validate discount against price
//     if ($request['price'] <= $dis) {
//         $validator->getMessageBag()->add('unit_price', translate('messages.discount_can_not_be_more_than_or_equal'));
//     }

//     // Return validation errors if any
//     if ($request['price'] <= $dis || $validator->fails()) {
//         return response()->json(['errors' => Helpers::error_processor($validator)]);
//     }

//     // Handle tags
//     $tag_ids = [];
//     if ($request->tags != null) {
//         $tags = explode(",", $request->tags);
//         foreach ($tags as $value) {
//             $tag = Tag::firstOrNew(['tag' => $value]);
//             $tag->save();
//             array_push($tag_ids, $tag->id);
//         }
//     }

//     // Create new food item
//     $food = new Food;
//     $food->name = $request->name[array_search('default', $request->lang)];

//     // Handle categories
//     $category = [];
//     if ($request->category_id != null) {
//         $category[] = ['id' => $request->category_id, 'position' => 1];
//     }
//     if ($request->sub_category_id != null) {
//         $category[] = ['id' => $request->sub_category_id, 'position' => 2];
//     }
//     if ($request->sub_sub_category_id != null) {
//         $category[] = ['id' => $request->sub_sub_category_id, 'position' => 3];
//     }

//     // Assign food properties
//     $food->category_ids = json_encode($category);
//     $food->category_id = $request->sub_category_id ?? $request->category_id;
//     $food->description = $request->description[array_search('default', $request->lang)];
//     $food->ingredients = $request->ingredients[array_search('default', $request->lang)];
//     $food->choice_options = json_encode([]);

//     // Handle variations
//     $variations = [];
//     if (isset($request->options)) {
//         foreach (array_values($request->options) as $option) {
//             // Handle variation data
//             $temp_variation = [
//                 'name' => $option['name'],
//                 'type' => $option['type'],
//                 'min' => $option['min'] ?? 0,
//                 'max' => $option['max'] ?? 0,
//                 'required' => $option['required'] ?? 'off',
//             ];

//             // Validate options
//             if ($option['min'] > 0 && $option['min'] > $option['max']) {
//                 $validator->getMessageBag()->add('name', translate('messages.minimum_value_can_not_be_greater_then_maximum_value'));
//                 return response()->json(['errors' => Helpers::error_processor($validator)]);
//             }
//             if (!isset($option['values'])) {
//                 $validator->getMessageBag()->add('name', translate('messages.please_add_options_for') . $option['name']);
//                 return response()->json(['errors' => Helpers::error_processor($validator)]);
//             }
//             if ($option['max'] > count($option['values'])) {
//                 $validator->getMessageBag()->add('name', translate('messages.please_add_more_options_or_change_the_max_value_for') . $option['name']);
//                 return response()->json(['errors' => Helpers::error_processor($validator)]);
//             }

//             // Prepare option values
//             $temp_value = [];
//             foreach (array_values($option['values']) as $value) {
//                 $temp_option['label'] = $value['label'] ?? '';
//                 $temp_option['optionPrice'] = $value['optionPrice'];
//                 array_push($temp_value, $temp_option);
//             }
//             $temp_variation['values'] = $temp_value;
//             array_push($variations, $temp_variation);
//         }
//     }

//     // Assign variations to food
//     $food->variations = json_encode($variations);
//     $food->price = $request->price;
//     $food->image = Helpers::upload(dir: 'product/', format: 'png', image: $request->file('image'));
//     $food->available_time_starts = $request->available_time_starts;
//     $food->available_time_ends = $request->available_time_ends;
//     $food->discount = $request->discount ?? 0;
//     $food->discount_type = $request->discount_type;

//     // Assign attributes and add-ons
//     $food->attributes = $request->has('attribute_id') ? json_encode($request->attribute_id) : json_encode([]);
//     $food->add_ons = $request->has('addon_ids') ? json_encode($request->addon_ids) : json_encode([]);
//     $food->restaurant_id = $request->restaurant_id;
//     $food->veg = $request->veg;
//     $food->maximum_cart_quantity = $request->maximum_cart_quantity;
//     $food->is_halal = $request->is_halal ?? 0;

//     // Save the food item
//     $food->save();
//     $food->tags()->sync($tag_ids);

//     // Prepare translation data
//     $data = [];
//     $default_lang = str_replace('_', '-', app()->getLocale());
//     foreach ($request->lang as $index => $key) {
//         // Handle name translation
//         if ($default_lang == $key && !$request->name[$index]) {
//             if ($key != 'default') {
//                 array_push($data, [
//                     'translationable_type' => 'App\Models\Food',
//                     'translationable_id' => $food->id,
//                     'locale' => $key,
//                     'key' => 'name',
//                     'value' => $food->name,
//                 ]);
//             }
//         } elseif ($request->name[$index] && $key != 'default') {
//             array_push($data, [
//                 'translationable_type' => 'App\Models\Food',
//                 'translationable_id' => $food->id,
//                 'locale' => $key,
//                 'key' => 'name',
//                 'value' => $request->name[$index],
//             ]);
//         }

//         // Handle description translation
//         if ($default_lang == $key && !$request->description[$index]) {
//             if ($key != 'default') {
//                 array_push($data, [
//                     'translationable_type' => 'App\Models\Food',
//                     'translationable_id' => $food->id,
//                     'locale' => $key,
//                     'key' => 'description',
//                     'value' => $food->description,
//                 ]);
//             }
//         } elseif ($request->description[$index] && $key != 'default') {
//             array_push($data, [
//                 'translationable_type' => 'App\Models\Food',
//                 'translationable_id' => $food->id,
//                 'locale' => $key,
//                 'key' => 'description',
//                 'value' => $request->description[$index],
//             ]);
//         }

//         // Handle ingredients translation
//         if ($default_lang == $key && !$request->ingredients[$index]) {
//             if ($key != 'default') {
//                 array_push($data, [
//                     'translationable_type' => 'App\Models\Food',
//                     'translationable_id' => $food->id,
//                     'locale' => $key,
//                     'key' => 'ingredients',
//                     'value' => $food->ingredients,
//                 ]);
//             }
//         } elseif ($request->ingredients[$index] && $key != 'default') {
//             array_push($data, [
//                 'translationable_type' => 'App\Models\Food',
//                 'translationable_id' => $food->id,
//                 'locale' => $key,
//                 'key' => 'ingredients',
//                 'value' => $request->ingredients[$index],
//             ]);
//         }
//     }

//     // Insert translations into the database
//     Translation::insert($data);

//     return response()->json([], 200);
// }

public function store(Request $request)
{
    // dd($request->all());
    try {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name.0' => 'required',
            'name.*' => 'max:191',
            'category_id' => 'required',
            'image' => 'required|max:2048',
            'price' => 'required|numeric|between:.01,999999999999.99',
            'discount' => 'required|numeric|min:0',
            'restaurant_id' => 'required',
            'description.*' => 'max:1000',
            'ingredients' => 'required',
            'veg' => 'required',
            'monday_start.*' => 'nullable|date_format:H:i',
            'monday_end.*' => 'nullable|date_format:H:i',
            'tuesday_start.*' => 'nullable|date_format:H:i',
            'tuesday_end.*' => 'nullable|date_format:H:i',
            'wednesday_start.*' => 'nullable|date_format:H:i',
            'wednesday_end.*' => 'nullable|date_format:H:i',
            'thursday_start.*' => 'nullable|date_format:H:i',
            'thursday_end.*' => 'nullable|date_format:H:i',
            'friday_start.*' => 'nullable|date_format:H:i',
            'friday_end.*' => 'nullable|date_format:H:i',
            'saturday_start.*' => 'nullable|date_format:H:i',
            'saturday_end.*' => 'nullable|date_format:H:i',
            'sunday_start.*' => 'nullable|date_format:H:i',
            'sunday_end.*' => 'nullable|date_format:H:i',
        ], [
            'description.*.max' => translate('messages.description_length_warning'),
            'name.0.required' => translate('messages.item_name_required'),
            'category_id.required' => translate('messages.category_required'),
            'ingredients.required' => translate('messages.ingredients_required'),
            'veg.required' => translate('messages.item_type_is_required'),
        ]);

        // Discount calculation
        if ($request['discount_type'] == 'percent') {
            $dis = ($request['price'] / 100) * $request['discount'];
        } else {
            $dis = $request['discount'];
        }

        // Validate discount against price
        if ($request['price'] <= $dis) {
            $validator->getMessageBag()->add('unit_price', translate('messages.discount_can_not_be_more_than_or_equal'));
        }

        // Return validation errors if any
        if ($request['price'] <= $dis || $validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        // Handle tags
        $tag_ids = [];
        if ($request->tags != null) {
            $tags = explode(",", $request->tags);
            foreach ($tags as $value) {
                $tag = Tag::firstOrNew(['tag' => $value]);
                $tag->save();
                array_push($tag_ids, $tag->id);
            }
        }

        // Collect selected main category IDs from availability_data (for super_category_ids)
        $selectedMainCategoryIds = [];
        if ($request->has('availability_data') && !empty($request->availability_data)) {
            $tempAvailabilityData = json_decode($request->availability_data, true);
            if (is_array($tempAvailabilityData)) {
                foreach ($tempAvailabilityData as $day => $categoriesData) {
                    if (is_array($categoriesData)) {
                        foreach ($categoriesData as $categoryName => $categoryInfo) {
                            if (isset($categoryInfo['available']) && $categoryInfo['available']) {
                                $mainCategoryIdInt = isset($categoryInfo['main_category_id']) ? (int) $categoryInfo['main_category_id'] : null;
                                if ($mainCategoryIdInt && !in_array($mainCategoryIdInt, $selectedMainCategoryIds)) {
                                    $selectedMainCategoryIds[] = $mainCategoryIdInt;
                                }
                            }
                        }
                    }
                }
            }
        }

        // Create new food item
        $food = new Food;
        $defaultIndex = array_search('default', $request->lang);
        if ($defaultIndex === false) {
            // If default not found, use first name value
            $defaultIndex = 0;
        }
        $foodName = $request->name[$defaultIndex] ?? $request->name[0] ?? '';
        $food->name = $foodName;

        // Handle categories
        $category = [];
        if ($request->category_id != null) {
            $category[] = ['id' => $request->category_id, 'position' => 1];
        }
        if ($request->sub_category_id != null) {
            $category[] = ['id' => $request->sub_category_id, 'position' => 2];
        }
        if ($request->sub_sub_category_id != null) {
            $category[] = ['id' => $request->sub_sub_category_id, 'position' => 3];
        }

        $food->category_ids = json_encode($category);
        $food->category_id = $request->sub_category_id ?? $request->category_id;

        // Handle super categories from availability selection
        if (!empty($selectedMainCategoryIds)) {
            $food->super_category_ids = $selectedMainCategoryIds;
        } elseif ($request->has('super_category_ids')) {
            // Fallback to comma-separated string if provided
            $superCatIds = is_array($request->super_category_ids)
                ? $request->super_category_ids
                : explode(',', $request->super_category_ids);
            $food->super_category_ids = array_filter($superCatIds);
        } else {
            $food->super_category_ids = [];
        }

        // Use same defaultIndex for description and ingredients
        $food->description = $request->description[$defaultIndex] ?? $request->description[0] ?? '';
        $food->ingredients = $request->ingredients[$defaultIndex] ?? $request->ingredients[0] ?? '';
        $food->choice_options = json_encode([]);
        $food->price = $request->price;
        $food->image = Helpers::upload(dir: 'product/', format: 'png', image: $request->file('image'));
        $food->available_time_starts = $request->available_time_starts;
        $food->available_time_ends = $request->available_time_ends;
        $food->discount = $request->discount ?? 0;
        $food->discount_type = $request->discount_type;
        $food->attributes = $request->has('attribute_id') ? json_encode($request->attribute_id) : json_encode([]);
        $food->add_ons = $request->has('addon_ids') ? json_encode($request->addon_ids) : json_encode([]);
        $food->restaurant_id = $request->restaurant_id;
        $food->veg = $request->veg;
        $food->maximum_cart_quantity = $request->maximum_cart_quantity;
        $food->is_halal = $request->is_halal ?? 0;

        // Save the new availability times (will be updated after FoodSchedule creation)
        $food->new_available_times = $request->availability_data ?? null;

        // Save the food item first to get ID
        $food->save();

        // Generate and save slug based on original name (before translations)
        if (empty($food->slug) && !empty($foodName)) {
            $food->slug = $food->generateSlug($foodName);
            $food->save();
        }
        $food->tags()->sync($tag_ids);

        if ($request->has('availability_data') || $request->input('availability_data') !== null) {
            try {
                $availableTimesJson = $request->input('availability_data') ?? $request->availability_data ?? null;


                // Handle null or empty string case
                if (!$availableTimesJson || empty(trim($availableTimesJson)) || $availableTimesJson === '{}' || $availableTimesJson === '[]') {
                    Log::info('Food Store - Empty availability_data, skipping FoodSchedule creation', [
                        'food_id' => $food->id
                    ]);
                } else {
                    $parsed = json_decode($availableTimesJson, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::error('Food Store - Invalid JSON format', [
                            'food_id' => $food->id,
                            'json_error' => json_last_error_msg(),
                            'json_error_code' => json_last_error(),
                            'data_preview' => substr($availableTimesJson, 0, 200)
                        ]);
                    } elseif (is_array($parsed) && !empty($parsed)) {
                        Log::info('Food Store - Starting FoodSchedule processing', [
                            'food_id' => $food->id,
                            'parsed_days_count' => count($parsed),
                            'parsed_structure' => array_keys($parsed)
                        ]);

                        // Remove previous schedules for this food (if any)
                        $deletedCount = FoodSchedule::where('food_id', $food->id)->delete();


                        // Load all main categories once
                        $allMainCategories = MainCategory::all();


                        // Create lookup maps
                        $mainCategoriesByName = [];
                        $mainCategoriesBySlug = [];
                        foreach ($allMainCategories as $mcat) {
                            $mainCategoriesByName[strtolower($mcat->name)] = $mcat;
                            if (!empty($mcat->slug)) {
                                $mainCategoriesBySlug[strtolower($mcat->slug)] = $mcat;
                            }
                        }


                        $createdCount = 0;
                        $skippedCount = 0;
                        $errorCount = 0;

                        // Format: {"monday": {"breakfast": {"available": true, "main_category_id": 4}, ...}, ...}
                        foreach ($parsed as $day => $categoriesData) {
                            Log::info('Food Store - Processing day', [
                                'food_id' => $food->id,
                                'day' => $day,
                                'categories_data_type' => gettype($categoriesData),
                                'is_array' => is_array($categoriesData)
                            ]);

                            if (!is_array($categoriesData)) {
                                Log::warning('Food Store - categoriesData is not array, skipping', [
                                    'food_id' => $food->id,
                                    'day' => $day,
                                    'type' => gettype($categoriesData)
                                ]);
                                continue;
                            }

                            foreach ($categoriesData as $categoryName => $categoryInfo) {
                                Log::info('Food Store - Processing category', [
                                    'food_id' => $food->id,
                                    'day' => $day,
                                    'category_name' => $categoryName,
                                    'category_info' => $categoryInfo
                                ]);

                                // Check if available is true
                                if (!isset($categoryInfo['available']) || !$categoryInfo['available']) {
                                    Log::info('Food Store - Category not available, skipping', [
                                        'food_id' => $food->id,
                                        'day' => $day,
                                        'category_name' => $categoryName,
                                        'available' => $categoryInfo['available'] ?? 'not set'
                                    ]);
                                    $skippedCount++;
                                    continue;
                                }

                                // Find main category - try multiple methods
                                $mainCat = null;
                                $categoryKey = strtolower($categoryName);

                                // Method 1: Try by name
                                if (isset($mainCategoriesByName[$categoryKey])) {
                                    $mainCat = $mainCategoriesByName[$categoryKey];
                                }

                                // Method 2: Try by slug
                                if (!$mainCat && isset($mainCategoriesBySlug[$categoryKey])) {
                                    $mainCat = $mainCategoriesBySlug[$categoryKey];
                                }

                                // Method 3: Try by main_category_id from data
                                if (!$mainCat && isset($categoryInfo['main_category_id'])) {
                                    $mainCat = MainCategory::find((int) $categoryInfo['main_category_id']);
                                }

                                // Method 4: Try direct search by name (case insensitive)
                                if (!$mainCat) {
                                    $mainCat = MainCategory::whereRaw('LOWER(name) = ?', [$categoryKey])->first();
                                }

                                if (!$mainCat) {
                                    Log::warning('Food Store - MainCategory not found, skipping', [
                                        'food_id' => $food->id,
                                        'day' => $day,
                                        'category_name' => $categoryName,
                                        'category_key' => $categoryKey,
                                        'main_category_id' => $categoryInfo['main_category_id'] ?? null,
                                        'available_categories' => array_keys($mainCategoriesByName)
                                    ]);
                                    $skippedCount++;
                                    continue;
                                }

                                Log::info('Food Store - MainCategory found', [
                                    'food_id' => $food->id,
                                    'day' => $day,
                                    'category_name' => $categoryName,
                                    'main_category_id' => $mainCat->id,
                                    'main_category_name' => $mainCat->name
                                ]);

                                // Get time slots from main category or use default
                                $startTime = '00:00';
                                $endTime = '23:59';

                                if ($mainCat->start_time) {
                                    try {
                                        $startTime = is_string($mainCat->start_time)
                                            ? $mainCat->start_time
                                            : $mainCat->start_time->format('H:i');
                                    } catch (\Exception $e) {
                                        $startTime = '00:00';
                                    }
                                }

                                if ($mainCat->end_time) {
                                    try {
                                        $endTime = is_string($mainCat->end_time)
                                            ? $mainCat->end_time
                                            : $mainCat->end_time->format('H:i');
                                    } catch (\Exception $e) {
                                        $endTime = '23:59';
                                    }
                                }

                                try {
                                    $scheduleData = [
                                        'food_id' => $food->id,
                                        'vendor_id' => $food->restaurant_id ?? null,
                                        'day' => ucfirst(strtolower($day)),
                                        'main_category_id' => $mainCat->id,
                                        'available_time_start' => $startTime,
                                        'available_time_end' => $endTime,
                                    ];

                                    Log::info('Food Store - Attempting to create FoodSchedule', [
                                        'food_id' => $food->id,
                                        'schedule_data' => $scheduleData
                                    ]);

                                    // Use direct DB insert to ensure data is saved
                                    try {
                                        // Direct DB insert - check if vendor_id column exists
                                        $insertData = [
                                            'food_id' => $scheduleData['food_id'],
                                            'day' => $scheduleData['day'],
                                            'main_category_id' => $scheduleData['main_category_id'],
                                            'available_time_start' => $scheduleData['available_time_start'],
                                            'available_time_end' => $scheduleData['available_time_end'],
                                            'created_at' => now(),
                                            'updated_at' => now(),
                                        ];

                                        // Only add vendor_id if column exists in table
                                        if (DB::getSchemaBuilder()->hasColumn('food_schedules', 'vendor_id')) {
                                            $insertData['vendor_id'] = $scheduleData['vendor_id'];
                                        }

                                        $scheduleId = DB::table('food_schedules')->insertGetId($insertData);

                                        Log::info('Food Store - FoodSchedule inserted via DB', [
                                            'food_id' => $food->id,
                                            'schedule_id' => $scheduleId,
                                            'day' => $day,
                                            'main_category_id' => $mainCat->id
                                        ]);

                                        // Verify it was actually saved
                                        $verifySchedule = DB::table('food_schedules')->where('id', $scheduleId)->first();
                                        if ($verifySchedule) {
                                            $createdCount++;
                                            Log::info('Food Store - FoodSchedule created and verified successfully', [
                                                'food_id' => $food->id,
                                                'schedule_id' => $scheduleId,
                                                'day' => $day,
                                                'main_category_id' => $mainCat->id,
                                                'verified' => true
                                            ]);
                                        } else {
                                            Log::error('Food Store - FoodSchedule inserted but not found in database!', [
                                                'food_id' => $food->id,
                                                'schedule_id' => $scheduleId
                                            ]);
                                        }
                                    } catch (\Exception $e) {
                                        $errorCount++;
                                        Log::error('Food Store - DB insert failed', [
                                            'food_id' => $food->id,
                                            'day' => $day,
                                            'category_name' => $categoryName,
                                            'main_category_id' => $mainCat->id ?? null,
                                            'error' => $e->getMessage(),
                                            'error_code' => $e->getCode(),
                                            'file' => $e->getFile(),
                                            'line' => $e->getLine(),
                                            'trace' => $e->getTraceAsString()
                                        ]);

                                        // Fallback to Eloquent if DB insert fails
                                        try {
                                            $schedule = FoodSchedule::create($scheduleData);
                                            if ($schedule && $schedule->id) {
                                                $createdCount++;
                                                Log::info('Food Store - FoodSchedule created via Eloquent fallback', [
                                                    'food_id' => $food->id,
                                                    'schedule_id' => $schedule->id
                                                ]);
                                            }
                                        } catch (\Exception $e2) {
                                            Log::error('Food Store - Both DB insert and Eloquent create failed', [
                                                'food_id' => $food->id,
                                                'db_error' => $e->getMessage(),
                                                'eloquent_error' => $e2->getMessage()
                                            ]);
                                        }
                                    }
                                } catch (\Exception $e) {
                                    Log::error('Food Store - FoodSchedule creation error', [
                                        'food_id' => $food->id,
                                        'day' => $day,
                                        'category_name' => $categoryName,
                                        'main_category_id' => $mainCat->id,
                                        'error' => $e->getMessage(),
                                        'trace' => $e->getTraceAsString()
                                    ]);
                                }
                            }
                        }

                        // Update new_available_times with the actual data used
                        $food->new_available_times = $availableTimesJson;
                        $food->save();

                        // Verify records were actually created
                        $actualCount = FoodSchedule::where('food_id', $food->id)->count();

                        Log::info('Food Store - FoodSchedule creation completed - SUMMARY', [
                            'food_id' => $food->id,
                            'created_count' => $createdCount,
                            'skipped_count' => $skippedCount,
                            'error_count' => $errorCount,
                            'actual_db_count' => $actualCount,
                            'parsed_days' => count($parsed),
                            'status' => ($createdCount > 0 && $actualCount > 0) ? 'SUCCESS' : 'FAILED'
                        ]);

                        if ($createdCount > 0 && $actualCount === 0) {
                            Log::error('Food Store - CRITICAL: FoodSchedule records not saved to database!', [
                                'food_id' => $food->id,
                                'expected_count' => $createdCount,
                                'actual_count' => $actualCount
                            ]);
                        } elseif ($createdCount === 0) {
                            Log::warning('Food Store - No FoodSchedule records were created', [
                                'food_id' => $food->id,
                                'skipped_count' => $skippedCount,
                                'error_count' => $errorCount,
                                'parsed_days' => count($parsed)
                            ]);
                        }
                    } else {
                        Log::warning('Food Store - Empty or invalid parsed data', [
                            'food_id' => $food->id,
                            'parsed_is_array' => is_array($parsed),
                            'parsed_empty' => empty($parsed)
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Food Store - FoodSchedule creation exception', [
                    'food_id' => $food->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Don't fail the entire request if schedule creation fails
            }
        } else {
            Log::info('Food Store - No availability_data in request', [
                'food_id' => $food->id
            ]);
        }

        // Prepare translation data (unchanged)
        $data = [];
        $default_lang = str_replace('_', '-', app()->getLocale());
        foreach ($request->lang as $index => $key) {
            if ($request->name[$index] && $key != 'default') {
                array_push($data, [
                    'translationable_type' => 'App\Models\Food',
                    'translationable_id' => $food->id,
                    'locale' => $key,
                    'key' => 'name',
                    'value' => $request->name[$index],
                ]);
            }
        }
        Translation::insert($data);

        return response()->json([
            'success' => true,
            'message' => 'Food item created successfully!',
            'data' => $food,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}

    public function view($id)
    {
        $product = Food::withoutGlobalScope(RestaurantScope::class)->findOrFail($id);
        $reviews=Review::where(['food_id'=>$id])->with('customer')->latest()->paginate(config('default_pagination'));
        return view('admin-views.product.view', compact('product','reviews'));
    }

    public function edit($id)
    {
        $product = Food::withoutGlobalScope(RestaurantScope::class)->withoutGlobalScope('translate')->with('translations')->findOrFail($id);

        if(!$product)
        {
            Toastr::error(translate('messages.food_not_found'));
            return back();
        }
        $product_category = json_decode($product->category_ids);
        $categories = Category::where(['parent_id' => 0])->get();
        $mainCategories = MainCategory::active()->get();

        // Load existing availability data from new_available_times and FoodSchedule
        $existingAvailability = [];
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        // First, try to load from new_available_times JSON column
        if (!empty($product->new_available_times)) {
            $storedAvailability = json_decode($product->new_available_times, true);
            if (is_array($storedAvailability)) {
                $existingAvailability = $storedAvailability;
            }
        }

        // Initialize structure for all days and categories
        foreach ($days as $day) {
            if (!isset($existingAvailability[$day])) {
                $existingAvailability[$day] = [];
            }
            foreach ($mainCategories as $category) {
                $categoryName = strtolower($category->name);
                if (!isset($existingAvailability[$day][$categoryName])) {
                    $existingAvailability[$day][$categoryName] = [
                        'available' => false,
                        'main_category_id' => $category->id
                    ];
                }
            }
        }

        // Also load from FoodSchedule for backward compatibility
        $schedules = FoodSchedule::where('food_id', $product->id)->get();
        foreach ($schedules as $schedule) {
            $day = strtolower($schedule->day);
            if ($schedule->main_category_id) {
                $mainCategory = MainCategory::find($schedule->main_category_id);
                if ($mainCategory) {
                    $categoryName = strtolower($mainCategory->name);
                    if (isset($existingAvailability[$day][$categoryName])) {
                        $existingAvailability[$day][$categoryName]['available'] = true;
                        $existingAvailability[$day][$categoryName]['main_category_id'] = $schedule->main_category_id;
                    }
                }
            }
        }

        return view('admin-views.product.edit', compact('product', 'product_category', 'categories', 'mainCategories', 'existingAvailability'));
    }

    public function status(Request $request)
    {
        $product = Food::withoutGlobalScope(RestaurantScope::class)->findOrFail($request->id);
        $product->status = $request->status;
        $product->save();

        if($request->status != 1){
            $product?->carts()?->delete();
        }
        Toastr::success(translate('messages.food_status_updated'));
        return back();
    }

    // public function update(Request $request, $id)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'array',
    //         'name.0' => 'required',
    //         'name.*' => 'max:191',
    //         'category_id' => 'required',
    //         'price' => 'required|numeric|between:.01,999999999999.99',
    //         'restaurant_id' => 'required',
    //         'veg' => 'required',
    //         'description' => 'array',
    //         'description.*' => 'max:1000',
    //         'discount' => 'required|numeric|min:0',
    //         'image' => 'nullable|max:2048',
    //     ], [
    //         'description.*.max' => translate('messages.description_length_warning'),
    //         'name.0.required' => translate('messages.item_name_required'),
    //         'category_id.required' => translate('messages.category_required'),
    //         'veg.required'=>translate('messages.item_type_is_required'),
    //     ]);

    //     if ($request['discount_type'] == 'percent') {
    //         $dis = ($request['price'] / 100) * $request['discount'];
    //     } else {
    //         $dis = $request['discount'];
    //     }

    //     if ($request['price'] <= $dis) {
    //         $validator->getMessageBag()->add('unit_price', translate('messages.discount_can_not_be_more_than_or_equal'));
    //     }

    //     if ($request['price'] <= $dis || $validator->fails()) {
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

    //     $p = Food::withoutGlobalScope(RestaurantScope::class)->find($id);

    //     $p->name = $request->name[array_search('default', $request->lang)];
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

    //     $p->category_id = $request->sub_category_id ?? $request->category_id;
    //     $p->category_ids = json_encode($category);
    //     $p->description = $request->description[array_search('default', $request->lang)];

    //     $p->choice_options = json_encode([]);



    //     $variations = [];
    //     if(isset($request->options))
    //     {
    //         foreach(array_values($request->options) as $key=>$option)
    //         {
    //             $temp_variation['name']= $option['name'];
    //             $temp_variation['type']= $option['type'];
    //             $temp_variation['min']= $option['min'] ?? 0;
    //             $temp_variation['max']= $option['max'] ?? 0;
    //             if($option['min'] > 0 &&  $option['min'] > $option['max']  ){
    //                 $validator->getMessageBag()->add('name', translate('messages.minimum_value_can_not_be_greater_then_maximum_value'));
    //                 return response()->json(['errors' => Helpers::error_processor($validator)]);
    //             }
    //             if(!isset($option['values'])){
    //                 $validator->getMessageBag()->add('name', translate('messages.please_add_options_for').$option['name']);
    //                 return response()->json(['errors' => Helpers::error_processor($validator)]);
    //             }
    //             if($option['max'] > count($option['values'])  ){
    //                 $validator->getMessageBag()->add('name', translate('messages.please_add_more_options_or_change_the_max_value_for').$option['name']);
    //                 return response()->json(['errors' => Helpers::error_processor($validator)]);
    //             }
    //             $temp_variation['required']= $option['required']??'off';
    //             $temp_value = [];
    //             foreach(array_values($option['values']) as $value)
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

    //     $slug = Str::slug($request->name[array_search('default', $request->lang)]);
    //     $p->slug = $p->slug? $p->slug :"{$slug}{$p->id}";
    //     //combinations end
    //     $p->variations = json_encode($variations);
    //     $p->price = $request->price;
    //     $p->image = $request->has('image') ? Helpers::update(dir:'product/', old_image: $p->image, format:'png', image: $request->file('image')) : $p->image;
    //     $p->available_time_starts = $request->available_time_starts;
    //     $p->available_time_ends = $request->available_time_ends;

    //     $p->discount = $request->discount ?? 0;
    //     $p->discount_type = $request->discount_type;

    //     $p->attributes = $request->has('attribute_id') ? json_encode($request->attribute_id) : json_encode([]);
    //     $p->add_ons = $request->has('addon_ids') ? json_encode($request->addon_ids) : json_encode([]);
    //     $p->restaurant_id = $request->restaurant_id;
    //     $p->veg = $request->veg;
    //     $p->maximum_cart_quantity = $request->maximum_cart_quantity;
    //     $p->is_halal =  $request->is_halal ?? 0;

    //     $p->save();
    //     $p->tags()->sync($tag_ids);
    //     $default_lang = str_replace('_', '-', app()->getLocale());

    //     foreach ($request->lang as $index => $key) {
    //         if($default_lang == $key && !($request->name[$index])){
    //             if ($key != 'default') {
    //                 Translation::updateOrInsert(
    //                     [
    //                         'translationable_type' => 'App\Models\Food',
    //                         'translationable_id' => $p->id,
    //                         'locale' => $key,
    //                         'key' => 'name'
    //                     ],
    //                     ['value' => $p->name]
    //                 );
    //             }
    //         }else{

    //             if ($request->name[$index] && $key != 'default') {
    //                 Translation::updateOrInsert(
    //                     [
    //                         'translationable_type' => 'App\Models\Food',
    //                         'translationable_id' => $p->id,
    //                         'locale' => $key,
    //                         'key' => 'name'
    //                     ],
    //                     ['value' => $request->name[$index]]
    //                 );
    //             }
    //         }

    //         if($default_lang == $key && !($request->description[$index])){
    //             if (isset($p->description) && $key != 'default') {
    //                 Translation::updateOrInsert(
    //                     [
    //                         'translationable_type' => 'App\Models\Food',
    //                         'translationable_id' => $p->id,
    //                         'locale' => $key,
    //                         'key' => 'description'
    //                     ],
    //                     ['value' => $p->description]
    //                 );
    //             }

    //         }else{
    //             if ($request->description[$index] && $key != 'default') {
    //                 Translation::updateOrInsert(
    //                     [
    //                         'translationable_type' => 'App\Models\Food',
    //                         'translationable_id' => $p->id,
    //                         'locale' => $key,
    //                         'key' => 'description'
    //                     ],
    //                     ['value' => $request->description[$index]]
    //                 );
    //             }

    //         }
    //     }

    //     return response()->json([], 200);
    // }

    public function update(Request $request, $id)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'name' => 'array',
            'name.0' => 'required',
            'name.*' => 'max:191',
            'category_id' => 'required',
            'price' => 'required|numeric|between:.01,999999999999.99',
            'restaurant_id' => 'required',
            'veg' => 'required',
            'description' => 'array',
            'description.*' => 'max:1000',
            'discount' => 'required|numeric|min:0',
            'image' => 'nullable|max:2048',
            'ingredients' => 'nullable|max:191', // Single string for ingredients
        ], [
            'description.*.max' => translate('messages.description_length_warning'),
            'name.0.required' => translate('messages.item_name_required'),
            'category_id.required' => translate('messages.category_required'),
            'veg.required'=>translate('messages.item_type_is_required'),
            'ingredients' => translate('messages.ingredient_name_required'),
        ]);

        // Calculate discount based on type
        if ($request['discount_type'] == 'percent') {
            $dis = ($request['price'] / 100) * $request['discount'];
        } else {
            $dis = $request['discount'];
        }

        // Discount validation
        if ($request['price'] <= $dis) {
            $validator->getMessageBag()->add('unit_price', translate('messages.discount_can_not_be_more_than_or_equal'));
        }

        if ($request['price'] <= $dis || $validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        // Handle tags
        $tag_ids = [];
        if ($request->tags != null) {
            $tags = explode(",", $request->tags);
        }
        if (isset($tags)) {
            foreach ($tags as $key => $value) {
                $tag = Tag::firstOrNew(['tag' => $value]);
                $tag->save();
                array_push($tag_ids, $tag->id);
            }
        }

        // Find the food item to update
        $p = Food::withoutGlobalScope(RestaurantScope::class)->find($id);
        if (!$p) {
            return response()->json(['message' => 'Food item not found.'], 404);
        }

        // Set basic fields
        $defaultIndex = array_search('default', $request->lang);
        if ($defaultIndex === false) {
            $defaultIndex = 0;
        }
        $p->name = $request->name[$defaultIndex] ?? $request->name[0] ?? '';
        $p->category_id = $request->sub_category_id ?? $request->category_id;
        $p->description = $request->description[$defaultIndex] ?? $request->description[0] ?? '';

        // Handle variations
        $variations = [];
        if (isset($request->options)) {
            foreach (array_values($request->options) as $key => $option) {
                $temp_variation['name'] = $option['name'];
                $temp_variation['type'] = $option['type'];
                $temp_variation['min'] = $option['min'] ?? 0;
                $temp_variation['max'] = $option['max'] ?? 0;

                // Validate min and max values
                if ($option['min'] > 0 && $option['min'] > $option['max']) {
                    $validator->getMessageBag()->add('name', translate('messages.minimum_value_can_not_be_greater_then_maximum_value'));
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                if (!isset($option['values'])) {
                    $validator->getMessageBag()->add('name', translate('messages.please_add_options_for').$option['name']);
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                if ($option['max'] > count($option['values'])) {
                    $validator->getMessageBag()->add('name', translate('messages.please_add_more_options_or_change_the_max_value_for').$option['name']);
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }

                $temp_variation['required'] = $option['required'] ?? 'off';
                $temp_value = [];
                foreach (array_values($option['values']) as $value) {
                    if (isset($value['label'])) {
                        $temp_option['label'] = $value['label'];
                    }
                    $temp_option['optionPrice'] = $value['optionPrice'];
                    array_push($temp_value, $temp_option);
                }
                $temp_variation['values'] = $temp_value;
                array_push($variations, $temp_variation);
            }
        }

        // Handle slug - use same defaultIndex
        $foodName = $request->name[$defaultIndex] ?? $request->name[0] ?? '';
        if (!empty($foodName) && empty($p->slug)) {
            $slug = Str::slug($foodName);
            $p->slug = $p->generateSlug($foodName);
        }

        // Set other properties
        $p->variations = json_encode($variations);
        $p->price = $request->price;
        $p->image = $request->has('image') ? Helpers::update(dir: 'product/', old_image: $p->image, format: 'png', image: $request->file('image')) : $p->image;
        $p->discount = $request->discount ?? 0;
        $p->discount_type = $request->discount_type;
        $p->restaurant_id = $request->restaurant_id;
        $p->veg = $request->veg;
        $p->maximum_cart_quantity = $request->maximum_cart_quantity;
        $p->is_halal = $request->is_halal ?? 0;

        // Update ingredients to the new value (removes previous ingredients)
        $p->ingredients = $request->ingredients; // Completely replace previous ingredients

        // Handle availability data with main categories and days
        $availableTimes = [];
        $selectedMainCategoryIds = [];

        if ($request->has('availability_data') && !empty($request->availability_data)) {
            $availabilityData = json_decode($request->availability_data, true);
            if (is_array($availabilityData)) {
                foreach ($availabilityData as $day => $categoriesData) {
                    if (!is_array($categoriesData)) {
                        continue;
                    }

                    // Each day has category name as keys (not ID)
                    foreach ($categoriesData as $categoryName => $categoryInfo) {
                        $mainCategoryIdInt = isset($categoryInfo['main_category_id']) ? (int) $categoryInfo['main_category_id'] : null;

                        if (!$mainCategoryIdInt) {
                            // If main_category_id not in data, try to find by category name
                            $mainCategory = MainCategory::whereRaw('LOWER(name) = ?', [strtolower($categoryName)])->first();
                            if ($mainCategory) {
                                $mainCategoryIdInt = $mainCategory->id;
                            } else {
                                continue;
                            }
                        }

                        // Track selected main category if available is true
                        if (isset($categoryInfo['available']) && $categoryInfo['available']) {
                            if (!in_array($mainCategoryIdInt, $selectedMainCategoryIds)) {
                                $selectedMainCategoryIds[] = $mainCategoryIdInt;
                            }
                        }

                        // Store availability for this day - maintain same structure as input
                        // Store all categories (both available true and false) to match JavaScript format
                        if (!isset($availableTimes[strtolower($day)])) {
                            $availableTimes[strtolower($day)] = [];
                        }

                        // Store with category name as key (not ID)
                        $availableTimes[strtolower($day)][strtolower($categoryName)] = [
                            'available' => isset($categoryInfo['available']) ? (bool) $categoryInfo['available'] : false,
                            'main_category_id' => $mainCategoryIdInt,
                        ];
                    }
                }
            }
        }

        // Encode availability times as JSON - use JSON_FORCE_OBJECT to ensure object format
        $encodedAvailableTimes = json_encode($availableTimes, JSON_FORCE_OBJECT);
        $p->new_available_times = $encodedAvailableTimes;

        // Handle super categories from availability selection
        if (!empty($selectedMainCategoryIds)) {
            $p->super_category_ids = $selectedMainCategoryIds;
        } elseif ($request->has('super_category_ids')) {
            // Fallback to comma-separated string if provided
            $superCatIds = is_array($request->super_category_ids)
                ? $request->super_category_ids
                : explode(',', $request->super_category_ids);
            $p->super_category_ids = array_filter($superCatIds);
        } else {
            $p->super_category_ids = [];
        }

        // Save the food item
        $p->save();
        $p->tags()->sync($tag_ids);

        // Save availability to FoodSchedule table
        // Always process availability_data if it exists in request
        // Debug: Log all request data
        Log::info('Food Update - Request data check', [
            'food_id' => $p->id,
            'has_availability_data' => $request->has('availability_data'),
            'all_request_keys' => array_keys($request->all()),
            'availability_data_present' => $request->input('availability_data') !== null,
            'availability_data_value' => $request->input('availability_data') ? substr($request->input('availability_data'), 0, 200) : 'null',
        ]);

        $availabilityDataToProcess = null;

        if ($request->has('availability_data') || $request->input('availability_data') !== null) {
            // Use data from request (even if empty string, we need to handle it)
            $availabilityDataToProcess = $request->input('availability_data') ?? $request->availability_data ?? null;
        }

        // Log for debugging
        Log::info('Food Update - availability_data check', [
            'food_id' => $p->id,
            'has_availability_data' => $request->has('availability_data'),
            'availability_data_to_process' => $availabilityDataToProcess ? substr($availabilityDataToProcess, 0, 200) : 'null',
            'availability_data_length' => $availabilityDataToProcess ? strlen($availabilityDataToProcess) : 0,
        ]);

        if ($availabilityDataToProcess !== null) {
            try {
                // Handle empty string case - delete all schedules
                if (empty(trim($availabilityDataToProcess)) || $availabilityDataToProcess === '{}' || $availabilityDataToProcess === '[]') {
                    FoodSchedule::where('food_id', $p->id)->delete();
                    Log::info('Food Update - Cleared all FoodSchedule entries for food_id: ' . $p->id);
                } else {
                    // Handle both JSON string and array
                    if (is_string($availabilityDataToProcess)) {
                        $availabilityData = json_decode($availabilityDataToProcess, true);
                    } else {
                        $availabilityData = $availabilityDataToProcess;
                    }

                    if (json_last_error() === JSON_ERROR_NONE && is_array($availabilityData) && !empty($availabilityData)) {
                        Log::info('Food Update - Starting FoodSchedule processing', [
                            'food_id' => $p->id,
                            'parsed_days_count' => count($availabilityData),
                            'parsed_structure' => array_keys($availabilityData)
                        ]);

                        // Delete existing schedules for this food
                        $deletedCount = FoodSchedule::where('food_id', $p->id)->delete();
                        Log::info('Food Update - Deleted existing FoodSchedule entries', [
                            'food_id' => $p->id,
                            'deleted_count' => $deletedCount
                        ]);

                        // Load all main categories once
                        $allMainCategories = MainCategory::all();
                        Log::info('Food Update - Loaded MainCategories', [
                            'food_id' => $p->id,
                            'main_categories_count' => $allMainCategories->count()
                        ]);

                        // Create lookup maps
                        $mainCategoriesByName = [];
                        $mainCategoriesBySlug = [];
                        foreach ($allMainCategories as $mcat) {
                            $mainCategoriesByName[strtolower($mcat->name)] = $mcat;
                            if (!empty($mcat->slug)) {
                                $mainCategoriesBySlug[strtolower($mcat->slug)] = $mcat;
                            }
                        }

                        Log::info('Food Update - Created lookup maps', [
                            'food_id' => $p->id,
                            'by_name_count' => count($mainCategoriesByName),
                            'by_slug_count' => count($mainCategoriesBySlug),
                            'category_names' => array_keys($mainCategoriesByName)
                        ]);

                        $createdCount = 0;
                        $skippedCount = 0;
                        $errorCount = 0;

                        // New format: {"monday": {"breakfast": {"available": true, "main_category_id": 4}, ...}, ...}
                        foreach ($availabilityData as $day => $categoriesData) {
                            Log::info('Food Update - Processing day', [
                                'food_id' => $p->id,
                                'day' => $day,
                                'categories_data_type' => gettype($categoriesData),
                                'is_array' => is_array($categoriesData)
                            ]);

                            if (!is_array($categoriesData)) {
                                Log::warning('Food Update - categoriesData is not array, skipping', [
                                    'food_id' => $p->id,
                                    'day' => $day,
                                    'type' => gettype($categoriesData)
                                ]);
                                continue;
                            }

                            foreach ($categoriesData as $categoryName => $categoryInfo) {
                                Log::info('Food Update - Processing category', [
                                    'food_id' => $p->id,
                                    'day' => $day,
                                    'category_name' => $categoryName,
                                    'category_info' => $categoryInfo
                                ]);

                                // Check if available is true
                                if (!isset($categoryInfo['available']) || !$categoryInfo['available']) {
                                    Log::info('Food Update - Category not available, skipping', [
                                        'food_id' => $p->id,
                                        'day' => $day,
                                        'category_name' => $categoryName,
                                        'available' => $categoryInfo['available'] ?? 'not set'
                                    ]);
                                    $skippedCount++;
                                    continue;
                                }

                                // Find main category - try multiple methods
                                $mainCategory = null;
                                $categoryKey = strtolower($categoryName);

                                // Method 1: Try by name
                                if (isset($mainCategoriesByName[$categoryKey])) {
                                    $mainCategory = $mainCategoriesByName[$categoryKey];
                                }

                                // Method 2: Try by slug
                                if (!$mainCategory && isset($mainCategoriesBySlug[$categoryKey])) {
                                    $mainCategory = $mainCategoriesBySlug[$categoryKey];
                                }

                                // Method 3: Try by main_category_id from data
                                if (!$mainCategory && isset($categoryInfo['main_category_id'])) {
                                    $mainCategory = MainCategory::find((int) $categoryInfo['main_category_id']);
                                }

                                // Method 4: Try direct search by name (case insensitive)
                                if (!$mainCategory) {
                                    $mainCategory = MainCategory::whereRaw('LOWER(name) = ?', [$categoryKey])->first();
                                }

                                if (!$mainCategory) {
                                    Log::warning('Food Update - MainCategory not found, skipping', [
                                        'food_id' => $p->id,
                                        'day' => $day,
                                        'category_name' => $categoryName,
                                        'category_key' => $categoryKey,
                                        'main_category_id' => $categoryInfo['main_category_id'] ?? null,
                                        'available_categories' => array_keys($mainCategoriesByName)
                                    ]);
                                    $skippedCount++;
                                    continue;
                                }

                                Log::info('Food Update - MainCategory found', [
                                    'food_id' => $p->id,
                                    'day' => $day,
                                    'category_name' => $categoryName,
                                    'main_category_id' => $mainCategory->id,
                                    'main_category_name' => $mainCategory->name
                                ]);

                                // Get time slots from main category or use default
                                $startTime = '00:00';
                                $endTime = '23:59';

                                if ($mainCategory->start_time) {
                                    try {
                                        $startTime = is_string($mainCategory->start_time)
                                            ? $mainCategory->start_time
                                            : $mainCategory->start_time->format('H:i');
                                    } catch (\Exception $e) {
                                        $startTime = '00:00';
                                    }
                                }

                                if ($mainCategory->end_time) {
                                    try {
                                        $endTime = is_string($mainCategory->end_time)
                                            ? $mainCategory->end_time
                                            : $mainCategory->end_time->format('H:i');
                                    } catch (\Exception $e) {
                                        $endTime = '23:59';
                                    }
                                }

                                try {
                                    $scheduleData = [
                                        'food_id' => $p->id,
                                        'vendor_id' => $p->restaurant_id ?? null,
                                        'day' => ucfirst(strtolower($day)),
                                        'main_category_id' => $mainCategory->id,
                                        'available_time_start' => $startTime,
                                        'available_time_end' => $endTime,
                                    ];

                                    Log::info('Food Update - Attempting to create FoodSchedule', [
                                        'food_id' => $p->id,
                                        'schedule_data' => $scheduleData
                                    ]);

                                    // Use direct DB insert to ensure data is saved
                                    try {
                                        // Direct DB insert - check if vendor_id column exists
                                        $insertData = [
                                            'food_id' => $scheduleData['food_id'],
                                            'day' => $scheduleData['day'],
                                            'main_category_id' => $scheduleData['main_category_id'],
                                            'available_time_start' => $scheduleData['available_time_start'],
                                            'available_time_end' => $scheduleData['available_time_end'],
                                            'created_at' => now(),
                                            'updated_at' => now(),
                                        ];

                                        // Only add vendor_id if column exists in table
                                        if (DB::getSchemaBuilder()->hasColumn('food_schedules', 'vendor_id')) {
                                            $insertData['vendor_id'] = $scheduleData['vendor_id'];
                                        }

                                        $scheduleId = DB::table('food_schedules')->insertGetId($insertData);

                                        Log::info('Food Update - FoodSchedule inserted via DB', [
                                            'food_id' => $p->id,
                                            'schedule_id' => $scheduleId,
                                            'day' => $day,
                                            'main_category_id' => $mainCategory->id
                                        ]);

                                        // Verify it was actually saved
                                        $verifySchedule = DB::table('food_schedules')->where('id', $scheduleId)->first();
                                        if ($verifySchedule) {
                                            $createdCount++;
                                            Log::info('Food Update - FoodSchedule created and verified successfully', [
                                                'food_id' => $p->id,
                                                'schedule_id' => $scheduleId,
                                                'day' => $day,
                                                'main_category_id' => $mainCategory->id,
                                                'verified' => true
                                            ]);
                                        } else {
                                            Log::error('Food Update - FoodSchedule inserted but not found in database!', [
                                                'food_id' => $p->id,
                                                'schedule_id' => $scheduleId
                                            ]);
                                        }
                                    } catch (\Exception $e) {
                                        $errorCount++;
                                        Log::error('Food Update - DB insert failed', [
                                            'food_id' => $p->id,
                                            'day' => $day,
                                            'category_name' => $categoryName,
                                            'main_category_id' => $mainCategory->id ?? null,
                                            'error' => $e->getMessage(),
                                            'error_code' => $e->getCode(),
                                            'file' => $e->getFile(),
                                            'line' => $e->getLine(),
                                            'trace' => $e->getTraceAsString()
                                        ]);

                                        // Fallback to Eloquent if DB insert fails
                                        try {
                                            $schedule = FoodSchedule::create($scheduleData);
                                            if ($schedule && $schedule->id) {
                                                $createdCount++;
                                                Log::info('Food Update - FoodSchedule created via Eloquent fallback', [
                                                    'food_id' => $p->id,
                                                    'schedule_id' => $schedule->id
                                                ]);
                                            }
                                        } catch (\Exception $e2) {
                                            Log::error('Food Update - Both DB insert and Eloquent create failed', [
                                                'food_id' => $p->id,
                                                'db_error' => $e->getMessage(),
                                                'eloquent_error' => $e2->getMessage()
                                            ]);
                                        }
                                    }
                                } catch (\Exception $e) {
                                    // Log error but continue processing other days
                                    Log::error('Food Update - FoodSchedule creation error', [
                                        'food_id' => $p->id,
                                        'day' => $day,
                                        'category_name' => $categoryName,
                                        'main_category_id' => $mainCategory->id,
                                        'error' => $e->getMessage(),
                                        'trace' => $e->getTraceAsString()
                                    ]);
                                }
                            }
                        }

                        // Verify records were actually created
                        $actualCount = FoodSchedule::where('food_id', $p->id)->count();

                        Log::info('Food Update - FoodSchedule update completed - SUMMARY', [
                            'food_id' => $p->id,
                            'created_count' => $createdCount,
                            'skipped_count' => $skippedCount,
                            'error_count' => $errorCount,
                            'actual_db_count' => $actualCount,
                            'parsed_days' => count($availabilityData),
                            'status' => ($createdCount > 0 && $actualCount > 0) ? 'SUCCESS' : 'FAILED'
                        ]);

                        if ($createdCount > 0 && $actualCount === 0) {
                            Log::error('Food Update - CRITICAL: FoodSchedule records not saved to database!', [
                                'food_id' => $p->id,
                                'expected_count' => $createdCount,
                                'actual_count' => $actualCount
                            ]);
                        } elseif ($createdCount === 0) {
                            Log::warning('Food Update - No FoodSchedule records were created', [
                                'food_id' => $p->id,
                                'skipped_count' => $skippedCount,
                                'error_count' => $errorCount,
                                'parsed_days' => count($availabilityData)
                            ]);
                        }
                    } else {
                        Log::error('FoodSchedule update - Invalid JSON format', [
                            'food_id' => $p->id,
                            'json_error' => json_last_error_msg(),
                            'json_error_code' => json_last_error(),
                            'data_preview' => substr($availabilityDataToProcess, 0, 200)
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Availability data processing error', [
                    'food_id' => $p->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        } else {
            Log::info('Food Update - No availability_data in request, skipping FoodSchedule update', [
                'food_id' => $p->id
            ]);
        }

        // Handle translations
        $default_lang = str_replace('_', '-', app()->getLocale());

        if ($request->has('lang') && is_array($request->lang)) {
            foreach ($request->lang as $index => $key) {
            // Update name translations
            if ($default_lang == $key && !($request->name[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\Food',
                            'translationable_id' => $p->id,
                            'locale' => $key,
                            'key' => 'name'
                        ],
                        ['value' => $p->name]
                    );
                }
            } else {
                if ($request->name[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\Food',
                            'translationable_id' => $p->id,
                            'locale' => $key,
                            'key' => 'name'
                        ],
                        ['value' => $request->name[$index]]
                    );
                }
            }

            // Update description translations
            if ($default_lang == $key && !($request->description[$index])) {
                if (isset($p->description) && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\Food',
                            'translationable_id' => $p->id,
                            'locale' => $key,
                            'key' => 'description'
                        ],
                        ['value' => $p->description]
                    );
                }
            } else {
                if ($request->description[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\Food',
                            'translationable_id' => $p->id,
                            'locale' => $key,
                            'key' => 'description'
                        ],
                        ['value' => $request->description[$index]]
                    );
                }
            }
        }
        }

        return response()->json([], 200);
    }

    public function delete(Request $request)
    {
        $product = Food::withoutGlobalScope(RestaurantScope::class)->withoutGlobalScope('translate')->find($request->id);

        if($product->image)
        {
            if (Storage::disk('public')->exists('product/' . $product['image'])) {
                Storage::disk('public')->delete('product/' . $product['image']);
            }
        }
        $product?->translations()?->delete();
        $product?->carts()?->delete();
        $product->delete();
        Toastr::success(translate('messages.product_deleted_successfully'));
        return back();
    }



    public function variant_price(Request $request)
    {
        if($request->item_type=='food')
        {
            $product = Food::withoutGlobalScope(RestaurantScope::class)->find($request->id);
        }
        else
        {
            $product = ItemCampaign::find($request->id);
        }
        // $product = Food::withoutGlobalScope(RestaurantScope::class)->find($request->id);
        $str = '';
        $quantity = 0;
        $price = 0;
        $addon_price = 0;

        foreach (json_decode($product->choice_options) as $key => $choice) {
            if ($str != null) {
                $str .= '-' . str_replace(' ', '', $request[$choice->name]);
            } else {
                $str .= str_replace(' ', '', $request[$choice->name]);
            }
        }

        if($request['addon_id'])
        {
            foreach($request['addon_id'] as $id)
            {
                $addon_price+= $request['addon-price'.$id]*$request['addon-quantity'.$id];
            }
        }

        if ($str != null) {
            $count = count(json_decode($product->variations));
            for ($i = 0; $i < $count; $i++) {
                if (json_decode($product->variations)[$i]->type == $str) {
                    $price = json_decode($product->variations)[$i]->price - Helpers::product_discount_calculate($product, json_decode($product->variations)[$i]->price,$product->restaurant);
                }
            }
        } else {
            $price = $product->price - Helpers::product_discount_calculate($product, $product->price,$product->restaurant);
        }

        return array('price' => Helpers::format_currency(($price * $request->quantity)+$addon_price));
    }
    public function get_categories(Request $request)
    {
        $cat = Category::where(['parent_id' => $request->parent_id])->get();
        $res = '<option value="' . 0 . '" disabled selected>---'.translate('messages.Select').'---</option>';
        foreach ($cat as $row) {
            if ($row->id == $request->sub_category) {
                $res .= '<option value="' . $row->id . '" selected >' . $row->name . '</option>';
            } else {
                $res .= '<option value="' . $row->id . '">' . $row->name . '</option>';
            }
        }
        return response()->json([
            'options' => $res,
        ]);
    }

    public function get_foods(Request $request)
    {
        $foods = Food::withoutGlobalScope(RestaurantScope::class)->with('restaurant')->whereHas('restaurant', function($query)use($request){
            $query->where('zone_id', $request->zone_id);
        })->get();
        $res = '';
        if(count($foods)>0 && !$request->data)
        {
            $res = '<option value="' . 0 . '" disabled selected>---'.translate('messages.Select').'---</option>';
        }

        foreach ($foods as $row) {
            $res .= '<option value="'.$row->id.'" ';
            if($request->data)
            {
                $res .= in_array($row->id, $request->data)?'selected ':'';
            }
            $res .= '>'.$row->name.' ('.$row->restaurant->name.')'. '</option>';
        }
        return response()->json([
            'options' => $res,
        ]);
    }

    public function list(Request $request)
    {
        $key = explode(' ', $request['search']);
        $restaurant_id = $request->query('restaurant_id', 'all');
        $category_id = $request->query('category_id', 'all');
        $type = $request->query('type', 'all');
        $foods = Food::withoutGlobalScope(RestaurantScope::class)
        ->withoutGlobalScope('translate') // Disable translation scope to get original names
        ->with(['restaurant','category.parent'])
        ->when(is_numeric($restaurant_id), function($query)use($restaurant_id){
            return $query->where('restaurant_id', $restaurant_id);
        })
        ->when(is_numeric($category_id), function($query)use($category_id){
            return $query->whereHas('category',function($q)use($category_id){
                return $q->whereId($category_id)->orWhere('parent_id', $category_id);
            });
        })
        ->when(isset($key) , function($q) use($key) {
            $q->where(function($q) use($key){
                foreach ($key as $value) {
                    $q->where('name', 'like', "%{$value}%");
                }
            });
        })
        ->type($type)
        ->latest()
        ->paginate(config('default_pagination'));
        $restaurant =$restaurant_id !='all'? Restaurant::findOrFail($restaurant_id):null;
        $category =$category_id !='all'? Category::with('translations')->findOrFail($category_id):null;
        return view('admin-views.product.list', compact('foods','restaurant','category', 'type'));
    }

    public function search(Request $request){
        $key = explode(' ', $request['search']);
        $restaurant_id=$request->restaurant_id ?? null;

            $foods = Food::withoutGlobalScope(RestaurantScope::class)
                            ->when(is_numeric($restaurant_id), function($query)use($restaurant_id){
                                return $query->where('restaurant_id', $restaurant_id);
                            })
                            ->where(function($q) use($key){
                                foreach ($key as $value) {
                                    $q->where('name', 'like', "%{$value}%");
                                }
                            })

                            ->limit(50)->get();
            return response()->json(['count'=>count($foods),
                'view'=>view('admin-views.product.partials._table',compact('foods'))->render()
            ]);
    }

    public function search_vendor(Request $request){
        $key = explode(' ', $request['search']);
        $restaurant_id=$request->restaurant_id ?? null;
            $foods = Food::withoutGlobalScope(RestaurantScope::class)
                            ->when(is_numeric($restaurant_id), function($query)use($restaurant_id){
                                return $query->where('restaurant_id', $restaurant_id);
                            })
                            ->where(function($q) use($key){
                                foreach ($key as $value) {
                                    $q->where('name', 'like', "%{$value}%");
                                }
                            })->limit(50)->get();
            return response()->json(['count'=>count($foods),
                'view'=>view('admin-views.vendor.view.partials._product',compact('foods'))->render()
            ]);
    }

    public function review_list(Request $request)
    {
        $key = explode(' ', $request['search']);
        $reviews = Review::with(['customer','food'=> function ($q) {
            $q->withoutGlobalScope(RestaurantScope::class);
        }])
        ->when(isset($key), function($query) use($key){
            $query->whereHas('food', function ($query) use ($key) {
                foreach ($key as $value) {
                    $query->where('name', 'like', "%{$value}%");
                }
            });
        })
        ->latest()->paginate(config('default_pagination'));
        return view('admin-views.product.reviews-list', compact('reviews'));
    }

    public function reviews_status(Request $request)
    {
        $review = Review::find($request->id);
        $review->status = $request->status;
        $review->save();
        Toastr::success(translate('messages.review_visibility_updated'));
        return back();
    }

    public function bulk_import_index()
    {
        return view('admin-views.product.bulk-import');
    }

    public function bulk_import_data(Request $request)
    {

        $request->validate([
            'upload_excel'=>'required|max:2048'
        ],[
            'upload_excel.required' => translate('messages.File_is_required!'),
            'upload_excel.max' => translate('messages.Max_file_size_is_2mb'),
        ]);
        try {
            $collections = (new FastExcel)->import($request->file('upload_excel'));
        } catch (\Exception $exception) {
            info(["line___{$exception->getLine()}",$exception->getMessage()]);
            Toastr::error(translate('messages.you_have_uploaded_a_wrong_format_file'));
            return back();
        }

        $data = [];
        if($request->button == 'import'){
            try {
                foreach ($collections as $collection) {
                    if ($collection['Id'] === "" || $collection['Name'] === "" || $collection['CategoryId'] === "" || $collection['SubCategoryId'] === "" || $collection['Price'] === "" || empty($collection['AvailableTimeStarts'])  || empty($collection['AvailableTimeEnds']) || $collection['RestaurantId'] === "" || $collection['Discount'] === "") {
                        Toastr::error(translate('messages.please_fill_all_required_fields'));
                        return back();
                    }
                    if(isset($collection['Price']) && ($collection['Price'] < 0  )  ) {
                        Toastr::error(translate('messages.Price_must_be_greater_then_0_on_id').' '.$collection['Id']);
                        return back();
                    }
                    if(isset($collection['Discount']) && ($collection['Discount'] < 0  )  ) {
                        Toastr::error(translate('messages.Discount_must_be_greater_then_0_on_id').' '.$collection['Id']);
                        return back();
                    }

                    try{
                            $t1= Carbon::parse($collection['AvailableTimeStarts']);
                            $t2= Carbon::parse($collection['AvailableTimeEnds']) ;
                            if($t1->gt($t2)   ) {
                                Toastr::error(translate('messages.AvailableTimeEnds_must_be_greater_then_AvailableTimeStarts_on_id').' '.$collection['Id']);
                                return back();
                            }
                        }catch(\Exception $e){
                            info(["line___{$e->getLine()}",$e->getMessage()]);
                            Toastr::error(translate('messages.Invalid_AvailableTimeEnds_or_AvailableTimeStarts_on_id').' '.$collection['Id']);
                            return back();
                        }


                    array_push($data, [
                        'name' => $collection['Name'],
                        'description' => $collection['Description'],
                        'image' => $collection['Image'],
                        'category_id' => $collection['SubCategoryId']?$collection['SubCategoryId']:$collection['CategoryId'],
                        'category_ids' => json_encode([['id' => $collection['CategoryId'], 'position' => 1], ['id' => $collection['SubCategoryId'], 'position' => 2]]),
                        'restaurant_id' => $collection['RestaurantId'],
                        'price' => $collection['Price'],
                        'discount' => $collection['Discount'] ?? 0,
                        'discount_type' => $collection['DiscountType'] ?? 'percent',
                        'available_time_starts' => $collection['AvailableTimeStarts'],
                        'available_time_ends' => $collection['AvailableTimeEnds'],
                        'variations' => $collection['Variations'] ?? json_encode([]),
                        'add_ons' => $collection['Addons'] ?($collection['Addons']==""?json_encode([]):$collection['Addons']): json_encode([]),
                        'veg' => $collection['Veg'] == 'yes' ? 1 : 0,
                        'recommended' => $collection['Recommended'] == 'yes' ? 1 : 0,
                        'status' => $collection['Status'] == 'active' ? 1 : 0,
                        'created_at'=>now(),
                        'updated_at'=>now()
                    ]);
                }
            }catch(\Exception $e){
                info(["line___{$e->getLine()}",$e->getMessage()]);
                Toastr::error(translate('messages.failed_to_import_data'));
                return back();
            }
            try{
                DB::beginTransaction();
                $chunkSize = 100;
                $chunk_items= array_chunk($data,$chunkSize);
                foreach($chunk_items as $key=> $chunk_item){
                    DB::table('food')->insert($chunk_item);
                }
                DB::commit();
            }catch(\Exception $e){
                DB::rollBack();
                info(["line___{$e->getLine()}",$e->getMessage()]);
                Toastr::error(translate('messages.failed_to_import_data'));
                return back();
            }
            Toastr::success(translate('messages.product_imported_successfully', ['count'=>count($data)]));
            return back();
        }

        try{
            foreach ($collections as $collection) {
                if ($collection['Id'] === "" || $collection['Name'] === "" || $collection['CategoryId'] === "" || $collection['SubCategoryId'] === "" || $collection['Price'] === "" || empty($collection['AvailableTimeStarts'])  || empty($collection['AvailableTimeEnds']) || $collection['RestaurantId'] === "" || $collection['Discount'] === "") {
                    Toastr::error(translate('messages.please_fill_all_required_fields'));
                    return back();
                }
                if(isset($collection['Price']) && ($collection['Price'] < 0  )  ) {
                    Toastr::error(translate('messages.Price_must_be_greater_then_0_on_id').' '.$collection['Id']);
                    return back();
                }
                if(isset($collection['Discount']) && ($collection['Discount'] < 0  )  ) {
                    Toastr::error(translate('messages.Discount_must_be_greater_then_0_on_id').' '.$collection['Id']);
                    return back();
                }

                try{
                        $t1= Carbon::parse($collection['AvailableTimeStarts']);
                        $t2= Carbon::parse($collection['AvailableTimeEnds']) ;
                        if($t1->gt($t2)   ) {
                            Toastr::error(translate('messages.AvailableTimeEnds_must_be_greater_then_AvailableTimeStarts_on_id').' '.$collection['Id']);
                            return back();
                        }
                    }catch(\Exception $e){
                        info(["line___{$e->getLine()}",$e->getMessage()]);
                        Toastr::error(translate('messages.Invalid_AvailableTimeEnds_or_AvailableTimeStarts_on_id').' '.$collection['Id']);
                        return back();
                    }
                array_push($data, [
                    'id' => $collection['Id'],
                    'name' => $collection['Name'],
                    'description' => $collection['Description'],
                    'image' => $collection['Image'],
                    'category_id' => $collection['SubCategoryId']?$collection['SubCategoryId']:$collection['CategoryId'],
                    'category_ids' => json_encode([['id' => $collection['CategoryId'], 'position' => 1], ['id' => $collection['SubCategoryId'], 'position' => 2]]),
                    'restaurant_id' => $collection['RestaurantId'],
                    'price' => $collection['Price'],
                    'discount' => $collection['Discount'] ?? 0,
                    'discount_type' => $collection['DiscountType'],
                    'available_time_starts' => $collection['AvailableTimeStarts'],
                    'available_time_ends' => $collection['AvailableTimeEnds'],
                    'variations' => $collection['Variations'] ?? json_encode([]),
                    'add_ons' => $collection['Addons'] ?($collection['Addons']==""?json_encode([]):$collection['Addons']): json_encode([]),
                    'veg' => $collection['Veg'] == 'yes' ? 1 : 0,
                    'recommended' => $collection['Recommended'] == 'yes' ? 1 : 0,
                    'status' => $collection['Status'] == 'active' ? 1 : 0,
                    'updated_at'=>now()
                ]);
            }
        }catch(\Exception $e)
        {
            info(["line___{$e->getLine()}",$e->getMessage()]);
            Toastr::error(translate('messages.failed_to_import_data'));
            return back();
        }
        try{
            DB::beginTransaction();
            $chunkSize = 100;
            $chunk_items= array_chunk($data,$chunkSize);
            foreach($chunk_items as $key=> $chunk_item){
                DB::table('food')->upsert($chunk_item,['id'],['name','description','image','category_id','category_ids','price','discount','discount_type','available_time_starts','available_time_ends','variations','add_ons','restaurant_id','status','veg','recommended']);
            }
            DB::commit();
        }catch(\Exception $e)
        {
            DB::rollBack();
            info(["line___{$e->getLine()}",$e->getMessage()]);
            Toastr::error(translate('messages.failed_to_import_data'));
            return back();
        }

        Toastr::success(translate('messages.Food_imported_successfully', ['count' => count($data)]));
        return back();


    }

    public function bulk_export_index()
    {
        return view('admin-views.product.bulk-export');
    }

    public function bulk_export_data(Request $request)
    {
        $request->validate([
            'type'=>'required',
            'start_id'=>'required_if:type,id_wise',
            'end_id'=>'required_if:type,id_wise',
            'from_date'=>'required_if:type,date_wise',
            'to_date'=>'required_if:type,date_wise'
        ]);
        $products = Food::when($request['type']=='date_wise', function($query)use($request){
            $query->whereBetween('created_at', [$request['from_date'].' 00:00:00', $request['to_date'].' 23:59:59']);
        })
        ->when($request['type']=='id_wise', function($query)use($request){
            $query->whereBetween('id', [$request['start_id'], $request['end_id']]);
        })
        ->withoutGlobalScope(RestaurantScope::class)->get();
        return (new FastExcel(ProductLogic::format_export_foods(Helpers::Export_generator($products))))->download('Foods.xlsx');
    }



    public function food_variation_generator(Request $request){
        $validator = Validator::make($request->all(), [
            'options' => 'required',
        ]);

        $food_variations = [];
        if (isset($request->options)) {
            foreach (array_values($request->options) as $key => $option) {

                $temp_variation['name'] = $option['name'];
                $temp_variation['type'] = $option['type'];
                $temp_variation['min'] = $option['min'] ?? 0;
                $temp_variation['max'] = $option['max'] ?? 0;
                $temp_variation['required'] = $option['required'] ?? 'off';
                if ($option['min'] > 0 &&  $option['min'] > $option['max']) {
                    $validator->getMessageBag()->add('name', translate('messages.minimum_value_can_not_be_greater_then_maximum_value'));
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                if (!isset($option['values'])) {
                    $validator->getMessageBag()->add('name', translate('messages.please_add_options_for') . $option['name']);
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                if ($option['max'] > count($option['values'])) {
                    $validator->getMessageBag()->add('name', translate('messages.please_add_more_options_or_change_the_max_value_for') . $option['name']);
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                $temp_value = [];

                foreach (array_values($option['values']) as $value) {
                    if (isset($value['label'])) {
                        $temp_option['label'] = $value['label'];
                    }
                    $temp_option['optionPrice'] = $value['optionPrice'];
                    array_push($temp_value, $temp_option);
                }
                $temp_variation['values'] = $temp_value;
                array_push($food_variations, $temp_variation);
            }
        }

        return response()->json([
            'variation' => json_encode($food_variations)
        ]);
    }


    public function export(Request $request){
        try{
            $key = explode(' ', $request['search']);
            $restaurant_id = $request->query('restaurant_id', 'all');
            $category_id = $request->query('category_id', 'all');
            $type = $request->query('type', 'all');
            $foods = Food::withoutGlobalScope(RestaurantScope::class)
            ->with(['tags','restaurant','category.parent'])
            ->when(is_numeric($restaurant_id), function($query)use($restaurant_id){
                return $query->where('restaurant_id', $restaurant_id);
            })
            ->when(is_numeric($category_id), function($query)use($category_id){
                return $query->whereHas('category',function($q)use($category_id){
                    return $q->whereId($category_id)->orWhere('parent_id', $category_id);
                });
            })
            ->when(isset($key) , function($q) use($key) {
                $q->where(function($q) use($key){
                    foreach ($key as $value) {
                        $q->where('name', 'like', "%{$value}%");
                    }
                });
            })
            ->type($type)
            ->latest()
            ->get();

            $data=[
                'data' =>$foods,
                'search' =>$request['search'] ?? null,
                'restaurant' => $restaurant_id !='all'? Restaurant::findOrFail($restaurant_id)?->name:null,
                'category' => $category_id !='all'? Category::findOrFail($category_id)?->name:null,
            ];

            if($request->type == 'csv'){
                return Excel::download(new FoodListExport($data), 'FoodList.csv');
            }
            return Excel::download(new FoodListExport($data), 'FoodList.xlsx');
        }  catch(\Exception $e)
            {
                Toastr::error("line___{$e->getLine()}",$e->getMessage());
                info(["line___{$e->getLine()}",$e->getMessage()]);
                return back();
            }
    }


    public function reviews_export(Request $request){
        try{
                $key = explode(' ', $request['search']);
                $reviews = Review::with(['customer','food'=> function ($q) {
                    $q->withoutGlobalScope(RestaurantScope::class);
                }])
                ->when(isset($key), function($query) use($key){
                    $query->whereHas('food', function ($query) use ($key) {
                        foreach ($key as $value) {
                            $query->where('name', 'like', "%{$value}%");
                        }
                    });
                })
                ->latest()->get();

                $data=[
                    'data' =>$reviews,
                    'search' =>$request['search'] ?? null,
                ];

                if($request->type == 'csv'){
                    return Excel::download(new FoodReviewExport($data), 'FoodReview.csv');
                }
                return Excel::download(new FoodReviewExport($data), 'FoodReview.xlsx');
            }  catch(\Exception $e){
                Toastr::error("line___{$e->getLine()}",$e->getMessage());
                info(["line___{$e->getLine()}",$e->getMessage()]);
                return back();
            }
    }




    public function food_wise_reviews_export(Request $request){
        try{
                $product = Food::withoutGlobalScope(RestaurantScope::class)->findOrFail($request->id)?->category_ids;
                $reviews=Review::where(['food_id'=> $request->id])->with('customer')->latest()->get();

                $data=[
                    'type' =>'single',
                    'category' =>\App\CentralLogics\Helpers::get_category_name($product),
                    'data' =>$reviews,
                    'search' =>$request['search'] ?? null,
                    'restaurant' =>$request['restaurant'] ?? null,
                ];

                if($request->type == 'csv'){
                    return Excel::download(new FoodReviewExport($data), 'FoodWiseReview.csv');
                }
                return Excel::download(new FoodReviewExport($data), 'FoodWiseReview.xlsx');
            }  catch(\Exception $e){
                Toastr::error("line___{$e->getLine()}",$e->getMessage());
                info(["line___{$e->getLine()}",$e->getMessage()]);
                return back();
            }
    }


    public function restaurant_food_export($type, $restaurant_id){
        try{
            $key = explode(' ', request()?->search);
            $foods =Food::withoutGlobalScope(\App\Scopes\RestaurantScope::class)
            ->with('category.parent')
            ->where('restaurant_id', $restaurant_id)
            ->when(isset($key) , function($q) use($key) {
                $q->where(function($q) use($key){
                    foreach ($key as $value) {
                        $q->where('name', 'like', "%{$value}%");
                    }
                });
            })
            ->latest()->get();

            $restaurant= Restaurant::where('id',$restaurant_id)->select(['name','zone_id'])->first();
            $data=[
                'data'=> $foods,
                'search'=> request()?->search ?? null,
                'zone'=>Helpers::get_zones_name($restaurant->zone_id),
                'restaurant_name'=> $restaurant->name,
            ];
            if($type == 'csv'){
                return Excel::download(new RestaurantFoodExport($data), 'FoodList.csv');
            }
            return Excel::download(new RestaurantFoodExport($data), 'FoodList.xlsx');
        }  catch(\Exception $e){
            Toastr::error("line___{$e->getLine()}",$e->getMessage());
            info(["line___{$e->getLine()}",$e->getMessage()]);
            return back();
        }
    }

}
