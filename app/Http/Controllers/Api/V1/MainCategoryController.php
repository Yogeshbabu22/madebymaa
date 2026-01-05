<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MainCategory;
use App\Models\Food;
use App\Models\Category;
use App\CentralLogics\Helpers;

class MainCategoryController extends Controller
{
    public function getSuperCategory(Request $request)
    {
        $query = MainCategory::query();

        if ($request->filled('status')) {
            $query->where('status', (int) $request->get('status'));
        }

        // Filter by current time active if requested
        if ($request->boolean('active_now')) {
            $currentTime = now()->format('H:i:s');
            $query->where('status', 1)
                ->where(function($q) use ($currentTime) {
                    $q->whereNull('start_time')
                      ->orWhere('start_time', '<=', $currentTime);
                })
                ->where(function($q) use ($currentTime) {
                    $q->whereNull('end_time')
                      ->orWhere('end_time', '>=', $currentTime);
                });
        }

        $mainCategories = $query->orderBy('priority', 'desc')->orderBy('id', 'desc')->get([
            'id','name','image','slug','position','priority','status','start_time','end_time','created_at','updated_at'
        ]);

        $data = $mainCategories->map(function(MainCategory $mc){
            return [
                'id' => $mc->id,
                'name' => $mc->name,
                'image' => $mc->image,
                'slug' => $mc->slug,
                'position' => $mc->position,
                'priority' => $mc->priority,
                'status' => (int) $mc->status,
                'start_time' => $mc->start_time ? (string) $mc->start_time : null,
                'end_time' => $mc->end_time ? (string) $mc->end_time : null,
                'is_active_now' => method_exists($mc, 'isCurrentlyActive') ? $mc->isCurrentlyActive() : null,
                'created_at' => $mc->created_at,
                'updated_at' => $mc->updated_at,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Main categories fetched',
            'data' => $data
        ]);
    }

    public function getFoodBySuperCategory(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'restaurant_id' => 'required|integer',
            'super_category_id' => 'required|integer',
            'offset' => 'required|integer|min:1',
            'category_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => Helpers::error_processor($validator)
            ], 403);
        }

        $restaurantId = $request->restaurant_id;
        $superCategoryId = $request->super_category_id;
        $offset = (int) $request->offset;
        $categoryId = $request->category_id;
        $limit = $request->limit ?? 20; // Default limit if not provided

        // Check if super category exists
        $superCategory = MainCategory::find($superCategoryId);
        if (!$superCategory) {
            return response()->json([
                'errors' => [['code' => 'super_category_not_found', 'message' => 'Super category not found']]
            ], 404);
        }

        // Check if restaurant exists
        $restaurant = \App\Models\Restaurant::find($restaurantId);
        if (!$restaurant) {
            return response()->json([
                'errors' => [['code' => 'restaurant_not_found', 'message' => 'Restaurant not found']]
            ], 404);
        }

        $query = Food::active()
            ->where('restaurant_id', $restaurantId)
            ->where(function($q) use ($superCategoryId) {
                // Match IDs when stored as CSV or JSON-like string (e.g., "[6,5]", "6,5", with or without spaces/quotes)
                $q->whereRaw(
                    "FIND_IN_SET(?, REPLACE(REPLACE(REPLACE(REPLACE(super_category_ids, '[', ''), ']', ''), '\"', ''), ' ', ''))",
                    [(string)$superCategoryId]
                );
            })
            ->with(['restaurant', 'category', 'rating', 'reviews']);

        // If category_id is provided, filter by specific category
        // if ($categoryId) {
        //     $query->where('category_id', $categoryId);
        // } else {
        //     $categoryIds = Category::active()
        //         ->where('parent_id', $superCategoryId)
        //         ->pluck('id')
        //         ->toArray();

        //     if (!empty($categoryIds)) {
        //         $query->whereIn('category_id', $categoryIds);
        //     }
        // }
        if ($categoryId) {
    // Get all child categories of this category
    $categoryIds = Category::active()
        ->where('id', $categoryId)
        ->orWhere('parent_id', $categoryId)
        ->pluck('id')
        ->toArray();

    if (!empty($categoryIds)) {
        $query->whereIn('category_id', $categoryIds);
    }
} else {
    // Old logic
    $categoryIds = Category::active()
        ->where('parent_id', $superCategoryId)
        ->pluck('id')
        ->toArray();

    if (!empty($categoryIds)) {
        $query->whereIn('category_id', $categoryIds);
    }
}


        // Apply additional filters if needed
        $type = $request->query('type', 'all');
        if ($type !== 'all') {
            $query->type($type);
        }

        $totalSize = $query->count();

        $foods = $query->skip(($offset - 1) * $limit)
            ->take($limit)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $formattedFoods = Helpers::product_data_formatting(
            data: $foods,
            multi_data: true,
            trans: false,
            local: app()->getLocale()
        );

        // Enrich products with super category name mapping
        $parseSuperIds = function ($raw) {
            if ($raw === null) return [];
            if (is_array($raw)) return array_values(array_filter(array_map('intval', $raw)));
            $clean = str_replace(['[',']','"','"',' '], '', (string)$raw);
            if ($clean === '') return [];
            $parts = explode(',', $clean);
            $ids = [];
            foreach ($parts as $p) {
                $p = trim($p);
                if ($p === '') continue;
                if (ctype_digit($p)) $ids[] = (int)$p;
            }
            return $ids;
        };

        $allIds = [];
        foreach ($foods as $food) {
            $ids = $parseSuperIds($food->super_category_ids ?? null);
            foreach ($ids as $id) { $allIds[$id] = true; }
        }
        $idList = array_keys($allIds);
        $idToName = [];
        if (!empty($idList)) {
            $mcList = MainCategory::whereIn('id', $idList)->get(['id','name']);
            foreach ($mcList as $mc) { $idToName[$mc->id] = $mc->name; }
        }

        // Attach super_categories: [{id, name}]
        foreach ($formattedFoods as &$item) {
            $ids = $parseSuperIds($item['super_category_ids'] ?? null);
            $item['super_categories'] = array_map(function ($sid) use ($idToName) {
                return [ 'id' => $sid, 'name' => $idToName[$sid] ?? null ];
            }, $ids);
        }
        unset($item);

        $categories = Category::active()
            ->where('parent_id', $superCategoryId)
            ->with('translations')
            ->get([
                'id', 'name', 'image', 'parent_id', 'position', 'status', 'created_at', 'updated_at', 'priority', 'slug'
            ]);

        return response()->json([
            'status' => true,
            'data' => [
                'categories' => $categories,
                'items' => [
                    'total_size' => $totalSize,
                    'limit' => (string) $limit,
                    'offset' => (string) $offset,
                    'products' => $formattedFoods,
                ],
            ],
        ], 200);
    }
//     public function getFoodBySuperCategory(Request $request)
// {
//     $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
//         'restaurant_id' => 'required|integer',
//         'super_category_id' => 'required|integer',
//         'offset' => 'required|integer|min:1',
//         'category_id' => 'nullable|integer',
//         'limit' => 'nullable|integer|min:1'
//     ]);

//     if ($validator->fails()) {
//         return response()->json([
//             'errors' => Helpers::error_processor($validator)
//         ], 403);
//     }

//     $restaurantId = $request->restaurant_id;
//     $superCategoryId = $request->super_category_id;
//     $offset = (int) $request->offset;
//     $limit = (int) ($request->limit ?? 20);
//     $categoryId = $request->category_id;

//     // Check if super category exists
//     $superCategory = MainCategory::find($superCategoryId);
//     if (!$superCategory) {
//         return response()->json([
//             'errors' => [['code' => 'super_category_not_found', 'message' => 'Super category not found']]
//         ], 404);
//     }

//     // Check if restaurant exists
//     $restaurant = \App\Models\Restaurant::find($restaurantId);
//     if (!$restaurant) {
//         return response()->json([
//             'errors' => [['code' => 'restaurant_not_found', 'message' => 'Restaurant not found']]
//         ], 404);
//     }

//     // Base query
//     $query = Food::active()
//         ->where('restaurant_id', $restaurantId)
//         ->with(['restaurant', 'category', 'rating', 'reviews']);

//     // Handle super_category_ids (CSV or JSON)
//     $query->where(function ($q) use ($superCategoryId) {
//         $q->whereRaw(
//             "FIND_IN_SET(?, REPLACE(REPLACE(REPLACE(REPLACE(super_category_ids, '[', ''), ']', ''), '\"', ''), ' ', ''))",
//             [(string) $superCategoryId]
//         );
//     });

//     // Category filter
//     if ($categoryId) {
//         $query->where('category_id', $categoryId);
//     } else {
//         $categoryIds = Category::active()
//             ->where('parent_id', $superCategoryId)
//             ->pluck('id')
//             ->toArray();

//         if (!empty($categoryIds)) {
//             $query->whereIn('category_id', $categoryIds);
//         }
//     }

//     // Optional type filter
//     $type = $request->query('type', 'all');
//     if ($type !== 'all' && method_exists(Food::class, 'scopeType')) {
//         $query->type($type);
//     }

//     $totalSize = $query->count();

//     // Pagination
//     $foods = $query->skip(($offset - 1) * $limit)
//         ->take($limit)
//         ->orderBy('created_at', 'desc')
//         ->orderBy('id', 'desc')
//         ->get();

//     // Format products
//     $formattedFoods = Helpers::product_data_formatting(
//         data: $foods,
//         multi_data: true,
//         trans: false,
//         local: app()->getLocale()
//     );

//     // Parse super_category_ids and attach names
//     $parseSuperIds = function ($raw) {
//         if ($raw === null) return [];
//         if (is_array($raw)) return array_map('intval', $raw);
//         $clean = str_replace(['[', ']', '"', ' '], '', (string)$raw);
//         if ($clean === '') return [];
//         return array_map('intval', explode(',', $clean));
//     };

//     // Build super_category id => name mapping
//     $allIds = [];
//     foreach ($foods as $food) {
//         foreach ($parseSuperIds($food->super_category_ids) as $id) {
//             $allIds[$id] = true;
//         }
//     }

//     $idToName = [];
//     if (!empty($allIds)) {
//         $mcList = MainCategory::whereIn('id', array_keys($allIds))->get(['id', 'name']);
//         foreach ($mcList as $mc) {
//             $idToName[$mc->id] = $mc->name;
//         }
//     }

//     // Attach super_categories to each product
//     foreach ($formattedFoods as &$item) {
//         $ids = $parseSuperIds($item['super_category_ids'] ?? null);
//         $item['super_categories'] = array_map(fn($sid) => [
//             'id' => $sid,
//             'name' => $idToName[$sid] ?? null
//         ], $ids);
//     }
//     unset($item);

//     // Get child categories
//     $categories = Category::active()
//         ->where('parent_id', $superCategoryId)
//         ->with('translations')
//         ->get([
//             'id', 'name', 'image', 'parent_id', 'position', 'status', 'created_at', 'updated_at', 'priority', 'slug'
//         ]);

//     return response()->json([
//         'status' => true,
//         'data' => [
//             'categories' => $categories,
//             'items' => [
//                 'total_size' => $totalSize,
//                 'limit' => (string) $limit,
//                 'offset' => (string) $offset,
//                 'products' => $formattedFoods,
//             ],
//         ],
//     ], 200);
// }


}
