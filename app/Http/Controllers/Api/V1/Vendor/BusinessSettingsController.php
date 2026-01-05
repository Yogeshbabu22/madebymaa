<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use App\Models\RestaurantSchedule;
use App\Models\RestaurantConfig;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class BusinessSettingsController extends Controller
{

    public function update_restaurant_setup(Request $request)
    {
        logger('request', $request->all());
        $validator = Validator::make($request->all(), [
            // 'name' => 'required',
            // 'address' => 'required',
            'contact_number' => 'required',
            'delivery' => 'required|boolean',
            'take_away' => 'required|boolean',
            'schedule_order' => 'required|boolean',
            'veg' => 'required|boolean',
            'non_veg' => 'required|boolean',
            'order_subscription_active' => 'required|boolean',
            'minimum_order' => 'required|numeric',
            'gst' => 'required_if:gst_status,1',
            'free_delivery_distance' => 'required_if:free_delivery_distance_status,1',
            'customer_order_date' => 'required_if:customer_order_date_status,1',
            'logo' => 'nullable|max:2048',
            'cover_photo' => 'nullable|max:2048',
            'meta_title' => 'max:100',
            'city_name'=>'required',
            'state_name'=>'required',
            'country_name'=>'required',
            'street_name'=>'required',
            'pincode'=>'required',
            'latitude'=>'required',
            'longitude' => 'required',

            // 'minimum_delivery_time' => 'required|numeric',
            // 'maximum_delivery_time' => 'required|numeric',
            // 'delivery_time_type'=>'required|in:min,hours,days'

            // 'cuisine_ids' => 'required',
        ],[
            'gst.required_if' => translate('messages.gst_can_not_be_empty'),
            'free_delivery_distance.required_if' => translate('messages.free_delivery_distance_can_not_be_empty'),
            'meta_title.max'=>translate('Title_must_be_within_100_character'),

        ]);
        $restaurant = $request['vendor']->restaurants[0];
        $data =0;
        if(($restaurant->restaurant_model == 'subscription'  && $restaurant?->restaurant_sub?->self_delivery == 1)  || ($restaurant->restaurant_model == 'commission' &&  $restaurant->self_delivery_system == 1) ){
        $data =1;
        }

        $validator->sometimes('per_km_delivery_charge', 'required_with:minimum_delivery_charge', function ($request) use($data) {
            return ($data);
        });
        $validator->sometimes('minimum_delivery_charge', 'required_with:per_km_delivery_charge', function ($request) use($data) {
            return ($data);
        });


        $data_trans = json_decode($request->translations, true);

        if (empty($data_trans) || !is_array($data_trans) || count($data_trans) < 1) {
            $validator->getMessageBag()->add('translations', translate('messages.Name and address in english is required'));
        }


        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $home_delivery = BusinessSetting::where('key', 'home_delivery')->first()?->value ?? null;
        if ($request->delivery   && !$home_delivery) {
            return response()->json([
                'error'=>[
                    ['code'=>'delivery_or_take_way', 'message'=>translate('messages.Home_delivery_is_disabled_by_admin')]
                ]
            ],403);
        }
        $take_away = BusinessSetting::where('key', 'take_away')->first()?->value ?? null;
        if ($request->take_away && !$take_away) {
            return response()->json([
                'error'=>[
                    ['code'=>'delivery_or_take_way', 'message'=>translate('messages.Take_away_is_disabled_by_admin')]
                ]
            ],403);
        }

        $instant_order = BusinessSetting::where('key', 'instant_order')->first()?->value ?? null;

        if ($request->instant_order && !$instant_order) {
            return response()->json([
                'error'=>[
                    ['code'=>'instant_order', 'message'=>translate('messages.instant_order_is_disabled_by_admin')]
                ]
            ],403);
        }


        if(!$request->take_away && !$request->delivery)
        {
            return response()->json([
                'error'=>[
                    ['code'=>'delivery_or_take_way', 'message'=>translate('messages.can_not_disable_both_take_away_and_delivery')]
                ]
            ],403);
        }

        if(!$request->veg && !$request->non_veg)
        {
            return response()->json([
                'error'=>[
                    ['code'=>'veg_non_veg', 'message'=>translate('messages.veg_non_veg_disable_warning')]
                ]
            ],403);
        }
        if(!$request->instant_order && $instant_order && !$request->schedule_order)
        {
            return response()->json([
                'error'=>[
                    ['code'=>'order', 'message'=>translate('messages.can_not_disable_both_instant_order_and_schedule_order')]
                ]
            ],403);
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

        $restaurant->order_subscription_active = $request->order_subscription_active;
        $restaurant->delivery = $request->delivery;
        $restaurant->take_away = $request->take_away;
        $restaurant->schedule_order = $request->schedule_order;
        $restaurant->veg = $request->veg;
        $restaurant->non_veg = $request->non_veg;
        $restaurant->minimum_order = $request->minimum_order;
        $restaurant->opening_time = $request->opening_time;
        $restaurant->closeing_time = $request->closeing_time;
        $restaurant->off_day = $request->off_day??'';
        $restaurant->gst = json_encode(['status'=>$request->gst_status, 'code'=>$request->gst]);
        $restaurant->free_delivery_distance = json_encode(['status'=>$request->free_delivery_distance_status, 'value'=>$request->free_delivery_distance]);
        $restaurant->street_name  = $request->street_name;
        $restaurant->city_name  = $request->city_name;
        $restaurant->state_name  = $request->state_name;
        $restaurant->country_name  = $request->country_name;
        $restaurant->pincode  = $request->pincode;
        $restaurant->latitude = $request->latitude;
        $restaurant->longitude = $request->longitude;

        // $restaurant->name = $request->name;
        // $restaurant->address = $request->address;

        // Get name and address from translations array safely
        $restaurantName = $restaurant->name; // Default to existing name
        $restaurantAddress = $restaurant->address; // Default to existing address

        foreach ($data_trans as $trans) {
            if (isset($trans['key']) && isset($trans['value'])) {
                if ($trans['key'] == 'name') {
                    $restaurantName = $trans['value'];
                } elseif ($trans['key'] == 'address') {
                    $restaurantAddress = $trans['value'];
                }
            }
        }

        $restaurant->name = $restaurantName;
        $restaurant->address = $restaurantAddress;

        $restaurant->phone = $request->contact_number;
        $restaurant->minimum_shipping_charge = $data?$request->minimum_delivery_charge??0: $restaurant->minimum_shipping_charge;
        $restaurant->per_km_shipping_charge = $data?$request->per_km_delivery_charge??0: $restaurant->per_km_shipping_charge;

        $restaurant->maximum_shipping_charge = $data?$request->maximum_delivery_charge??0: $restaurant->maximum_delivery_charge;
        $restaurant->logo = $request->has('logo') ? Helpers::update(dir:'restaurant/', old_image:$restaurant->logo, format:'png', image:$request->file('logo')) : $restaurant->logo;
        $restaurant->cover_photo = $request->has('cover_photo') ? Helpers::update(dir:'restaurant/cover/', old_image:$restaurant->cover_photo,format: 'png', image:$request->file('cover_photo')) : $restaurant->cover_photo;
        // $restaurant->delivery_time =$request->minimum_delivery_time .'-'. $request->maximum_delivery_time.'-'.$request->delivery_time_type;

        // Get meta_title and meta_description from translations array safely
        $metaTitle = $restaurant->meta_title ?? null;
        $metaDescription = $restaurant->meta_description ?? null;

        foreach ($data_trans as $trans) {
            if (isset($trans['key']) && isset($trans['value'])) {
                if ($trans['key'] == 'meta_title') {
                    $metaTitle = $trans['value'];
                } elseif ($trans['key'] == 'meta_description') {
                    $metaDescription = $trans['value'];
                }
            }
        }

        $restaurant->meta_title = $metaTitle;
        $restaurant->meta_description = $metaDescription;
        $restaurant->meta_image = $request->has('meta_image') ? Helpers::update(dir:'restaurant/',old_image: $restaurant->meta_image, format:'png',image: $request->file('meta_image')) : $restaurant->meta_image;


        $restaurant->cutlery = $request->cutlery ?? 0;

        // Third Party Delivery Partner
        if ($request->has('delivery_partner')) {
            $restaurant->delivery_partner = $request->delivery_partner;
        }

        $restaurant->save();

        $restaurant->tags()->sync($tag_ids);


        $conf = RestaurantConfig::firstOrNew(
            ['restaurant_id' =>  $restaurant->id]
        );
        $conf->instant_order = $request->instant_order ?? 0;
        $conf->customer_order_date = $request->customer_order_date ?? 0;
        $conf->customer_date_order_sratus = $request->customer_date_order_sratus ?? 0;
        $conf->halal_tag_status = $request->halal_tag_status ?? 0;
        $conf->save();



        // Save translations safely
        if (!empty($data_trans) && is_array($data_trans)) {
            foreach ($data_trans as $key=>$i) {
                if (isset($i['locale']) && isset($i['key']) && isset($i['value'])) {
                    Translation::updateOrInsert(
                        ['translationable_type'  => 'App\Models\Restaurant',
                            'translationable_id'    => $restaurant->id,
                            'locale'                => $i['locale'],
                            'key'                   => $i['key']],
                        ['value'                 => $i['value']]
                    );
                }
            }
        }

        $cuisine_ids = [];
        $cuisine_ids = json_decode($request->cuisine_ids, true);
        $restaurant->cuisine()->sync($cuisine_ids);

        // Save availability schedule with main categories (only in update)
        if ($request->has('availability_data') && !empty($request->availability_data)) {
            try {
                $availabilityData = json_decode($request->availability_data, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($availabilityData)) {
                    // Delete existing schedules for this restaurant
                    \App\Models\RestaurantSchedule::where('restaurant_id', $restaurant->id)->delete();

                    // Get all main categories for time slot lookup
                    $mainCategories = \App\Models\MainCategory::all()->keyBy(function($mc) {
                        return strtolower($mc->name);
                    });

                    // Day mapping: Monday -> 1, Tuesday -> 2, etc.
                    $dayMap = [
                        'monday' => 1,
                        'tuesday' => 2,
                        'wednesday' => 3,
                        'thursday' => 4,
                        'friday' => 5,
                        'saturday' => 6,
                        'sunday' => 7,
                    ];

                    // Save to restaurant_schedule table
                    foreach ($availabilityData as $day => $categoriesData) {
                        if (!is_array($categoriesData)) {
                            continue;
                        }

                        foreach ($categoriesData as $categoryName => $categoryInfo) {
                            // Check if available is true
                            if (!isset($categoryInfo['available']) || !$categoryInfo['available']) {
                                continue;
                            }

                            // Find main category by name (key is category name now, not ID)
                            $mainCategory = $mainCategories->get(strtolower($categoryName));
                            if (!$mainCategory) {
                                // Fallback: try to get from main_category_id in data
                                if (isset($categoryInfo['main_category_id'])) {
                                    $mainCategory = \App\Models\MainCategory::find((int) $categoryInfo['main_category_id']);
                                }
                                if (!$mainCategory) {
                                    continue;
                                }
                            }

                            // Get time from main category or use default
                            $startTime = '00:00:00';
                            $endTime = '23:59:59';

                            if ($mainCategory->start_time) {
                                try {
                                    $startTime = is_string($mainCategory->start_time)
                                        ? $mainCategory->start_time
                                        : $mainCategory->start_time->format('H:i:s');
                                } catch (\Exception $e) {
                                    $startTime = '00:00:00';
                                }
                            }

                            if ($mainCategory->end_time) {
                                try {
                                    $endTime = is_string($mainCategory->end_time)
                                        ? $mainCategory->end_time
                                        : $mainCategory->end_time->format('H:i:s');
                                } catch (\Exception $e) {
                                    $endTime = '23:59:59';
                                }
                            }

                            // Get day number from day name
                            $dayNumber = $dayMap[strtolower($day)] ?? null;
                            if (!$dayNumber) {
                                continue;
                            }

                            try {
                                \App\Models\RestaurantSchedule::create([
                                    'restaurant_id' => $restaurant->id,
                                    'day' => $dayNumber,
                                    'opening_time' => $startTime,
                                    'closing_time' => $endTime,
                                    'main_category_id' => $mainCategory->id,
                                ]);
                            } catch (\Exception $e) {
                                // Log error but continue processing other days
                                info('RestaurantSchedule creation error: ' . $e->getMessage());
                            }
                        }
                    }

                    // Save availability data to restaurants table new_available_times column
                    $restaurant->new_available_times = json_encode($availabilityData);
                    $restaurant->save();
                }
            } catch (\Exception $e) {
                info('Availability data processing error: ' . $e->getMessage());
            }
        }

        if($restaurant?->vendor?->userinfo) {
            $userinfo = $restaurant->vendor->userinfo;
            $userinfo->f_name = $restaurant->name;
            $userinfo->image = $restaurant->logo;
            $userinfo->save();
        }

        return response()->json(['message'=>translate('messages.restaurant_settings_updated')], 200);
    }

    public function add_schedule(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'opening_time'=>'required|date_format:H:i:s',
            'closing_time'=>'required|date_format:H:i:s|after:opening_time',
            'day' => 'required',
            'main_category_id' => 'nullable|exists:main_categories,id',
        ],[
            'closing_time.after'=>translate('messages.End time must be after the start time')
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)],400);
        }
        $restaurant = $request['vendor']->restaurants[0];

        // Check for overlapping schedule (same day, same main_category_id if provided)
        $temp = RestaurantSchedule::where('day', $request->day)
            ->where('restaurant_id', $restaurant->id)
            ->where(function($q) use ($request) {
                if ($request->has('main_category_id') && $request->main_category_id) {
                    $q->where('main_category_id', $request->main_category_id);
                } else {
                    $q->whereNull('main_category_id');
                }
            })
            ->where(function($q) use ($request) {
                return $q->where(function($query) use ($request) {
                    return $query->where('opening_time', '<=', $request->opening_time)
                        ->where('closing_time', '>=', $request->opening_time);
                })->orWhere(function($query) use ($request) {
                    return $query->where('opening_time', '<=', $request->closing_time)
                        ->where('closing_time', '>=', $request->closing_time);
                });
            })
            ->first();

        if(isset($temp))
        {
            return response()->json(['errors' => [
                ['code'=>'time', 'message'=>translate('messages.schedule_overlapping_warning')]
            ]], 400);
        }

        $scheduleData = [
            'restaurant_id' => $restaurant->id,
            'day' => $request->day,
            'opening_time' => $request->opening_time,
            'closing_time' => $request->closing_time,
        ];

        if ($request->has('main_category_id') && $request->main_category_id) {
            $scheduleData['main_category_id'] = $request->main_category_id;
        }

        $restaurant_schedule = RestaurantSchedule::insertGetId($scheduleData);

        $existingAvailability = [];
        if ($restaurant->new_available_times) {
            $existingAvailability = json_decode($restaurant->new_available_times, true);
            if (!is_array($existingAvailability)) {
                $existingAvailability = [];
            }
        }

        // Day name mapping
        $dayNames = [1 => 'monday', 2 => 'tuesday', 3 => 'wednesday', 4 => 'thursday', 5 => 'friday', 6 => 'saturday', 7 => 'sunday'];
        $dayName = $dayNames[$request->day] ?? null;

        if ($dayName) {
            // Initialize day if not exists
            if (!isset($existingAvailability[$dayName])) {
                $existingAvailability[$dayName] = [];
            }

            $categoryName = null;
            if ($request->has('main_category_id') && $request->main_category_id) {
                $mainCategory = \App\Models\MainCategory::find($request->main_category_id);
                if ($mainCategory) {
                    $categoryName = strtolower($mainCategory->name);
                }
            }

            if ($categoryName) {
                $existingAvailability[$dayName][$categoryName] = [
                    'available' => true,
                    'main_category_id' => $request->main_category_id
                ];
            }

            // Save updated availability data
            $restaurant->new_available_times = json_encode($existingAvailability);
            $restaurant->save();
        }

        return response()->json(['message'=>translate('messages.Schedule added successfully'), 'id'=>$restaurant_schedule], 200);
    }

    public function remove_schedule(Request $request, $restaurant_schedule)
    {
        $restaurant = $request['vendor']->restaurants[0];
        $schedule = RestaurantSchedule::where('restaurant_id', $restaurant->id)->find($restaurant_schedule);
        if(!$schedule)
        {
            return response()->json([
                'error'=>[
                    ['code'=>'not-fond', 'message'=>translate('messages.Schedule not found')]
                ]
            ],404);
        }
        $schedule->delete();
        return response()->json(['message'=>translate('messages.Schedule removed successfully')], 200);
    }

    public function get_availability(Request $request)
    {
        $restaurant = $request['vendor']->restaurants[0];

        // Get existing availability data from restaurants table
        $availabilityData = [];
        if ($restaurant->new_available_times) {
            $decoded = json_decode($restaurant->new_available_times, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $availabilityData = $decoded;
            }
        }

        // If specific day requested via query parameter
        $requestedDay = $request->query('day');
        if ($requestedDay) {
            $requestedDay = strtolower($requestedDay);
            $dayData = $availabilityData[$requestedDay] ?? [];
            return response()->json([
                'status' => true,
                'message' => translate('messages.Data retrieved successfully'),
                'data' => [
                    'day' => $requestedDay,
                    'availability' => $dayData
                ]
            ], 200);
        }

        // Get current day name
        $currentDay = strtolower(now()->format('l')); // Monday, Tuesday, etc.

        // Return current day's availability
        $currentDayData = $availabilityData[$currentDay] ?? [];

        return response()->json([
            'status' => true,
            'message' => translate('messages.Data retrieved successfully'),
            'data' => [
                'current_day' => $currentDay,
                'availability' => $currentDayData,
                'all_days' => $availabilityData // Also return all days data
            ]
        ], 200);
    }

    public function update_availability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'day' => 'required|string',
            'breakfast' => 'nullable|array',
            'breakfast.available' => 'nullable|boolean',
            'breakfast.main_category_id' => 'nullable|exists:main_categories,id',
            'lunch' => 'nullable|array',
            'lunch.available' => 'nullable|boolean',
            'lunch.main_category_id' => 'nullable|exists:main_categories,id',
            'dinner' => 'nullable|array',
            'dinner.available' => 'nullable|boolean',
            'dinner.main_category_id' => 'nullable|exists:main_categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 400);
        }

        $restaurant = $request['vendor']->restaurants[0];
        $day = strtolower($request->input('day'));

        // Validate day name
        $validDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        if (!in_array($day, $validDays)) {
            return response()->json([
                'errors' => [
                    ['code' => 'invalid_day', 'message' => 'Invalid day name. Must be one of: ' . implode(', ', $validDays)]
                ]
            ], 400);
        }

        // Get existing availability data
        $availabilityData = [];
        if ($restaurant->new_available_times) {
            $decoded = json_decode($restaurant->new_available_times, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $availabilityData = $decoded;
            }
        }

        // Get all main categories for time slot lookup
        $mainCategories = \App\Models\MainCategory::all()->keyBy(function($mc) {
            return strtolower($mc->name);
        });

        // Day mapping: Monday -> 1, Tuesday -> 2, etc.
        $dayMap = [
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            'sunday' => 7,
        ];
        $dayNumber = $dayMap[$day];

        // Delete existing schedules for this day and restaurant
        \App\Models\RestaurantSchedule::where('restaurant_id', $restaurant->id)
            ->where('day', $dayNumber)
            ->delete();

        // Initialize day data if not exists
        if (!isset($availabilityData[$day])) {
            $availabilityData[$day] = [];
        }

        // Process each meal category (breakfast, lunch, dinner)
        $categories = ['breakfast', 'lunch', 'dinner'];
        foreach ($categories as $categoryName) {
            if ($request->has($categoryName)) {
                $categoryInfo = $request->input($categoryName);

                // Update availability data
                $availabilityData[$day][$categoryName] = [
                    'available' => isset($categoryInfo['available']) ? (bool)$categoryInfo['available'] : false,
                ];

                if (isset($categoryInfo['main_category_id'])) {
                    $availabilityData[$day][$categoryName]['main_category_id'] = (int)$categoryInfo['main_category_id'];
                }

                // If available and main_category_id provided, create schedule entry
                if (isset($categoryInfo['available']) && $categoryInfo['available'] && isset($categoryInfo['main_category_id'])) {
                    $mainCategory = \App\Models\MainCategory::find((int)$categoryInfo['main_category_id']);

                    if ($mainCategory) {
                        // Get time from main category or use default
                        $startTime = '00:00:00';
                        $endTime = '23:59:59';

                        if ($mainCategory->start_time) {
                            try {
                                $startTime = is_string($mainCategory->start_time)
                                    ? $mainCategory->start_time
                                    : $mainCategory->start_time->format('H:i:s');
                            } catch (\Exception $e) {
                                $startTime = '00:00:00';
                            }
                        }

                        if ($mainCategory->end_time) {
                            try {
                                $endTime = is_string($mainCategory->end_time)
                                    ? $mainCategory->end_time
                                    : $mainCategory->end_time->format('H:i:s');
                            } catch (\Exception $e) {
                                $endTime = '23:59:59';
                            }
                        }

                        try {
                            \App\Models\RestaurantSchedule::create([
                                'restaurant_id' => $restaurant->id,
                                'day' => $dayNumber,
                                'opening_time' => $startTime,
                                'closing_time' => $endTime,
                                'main_category_id' => $mainCategory->id,
                            ]);
                        } catch (\Exception $e) {
                            info('RestaurantSchedule creation error for ' . $day . ' ' . $categoryName . ': ' . $e->getMessage());
                        }
                    }
                }
            }
        }

        // Save updated availability data to restaurants table
        $restaurant->new_available_times = json_encode($availabilityData);
        $restaurant->save();

        return response()->json([
            'status' => true,
            'message' => translate('messages.Availability updated successfully'),
            'data' => [
                'day' => $day,
                'availability' => $availabilityData[$day]
            ]
        ], 200);
    }
}
