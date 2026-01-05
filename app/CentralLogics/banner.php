<?php

namespace App\CentralLogics;

use App\Models\Banner;
use App\Models\Food;
use App\Models\Restaurant;
use App\CentralLogics\Helpers;

class BannerLogic
{
    // public static function get_banners($zone_id)
    // {
    //     $banners = Banner::active()->whereIn('zone_id', $zone_id)->get();
    //     $data = [];
    //     foreach($banners as $banner)
    //     {
    //         if($banner->type=='restaurant_wise')
    //         {
    //             $restaurant = Restaurant::where(['id'=> $banner->data,'status' => 1])->first();
    //             if($restaurant){
    //                 $data[]=[
    //                     'id'=>$banner->id,
    //                     'title'=>$banner->title,
    //                     'type'=>$banner->type,
    //                     'image'=>$banner->image,
    //                     'restaurant'=> $restaurant?Helpers::restaurant_data_formatting(data:$restaurant, multi_data:false):null,
    //                     'food'=>null
    //                 ];
    //             }
    //         }
    //         if($banner->type=='item_wise')
    //         {
    //             $food = Food::wherehas('restaurant', function($query){
    //                 $query->where('status',1);
    //             })->where(['id'=> $banner->data,'status' => 1])->first();
    //             if($food){
    //                 $data[]=[
    //                     'id'=>$banner->id,
    //                     'title'=>$banner->title,
    //                     'type'=>$banner->type,
    //                     'image'=>$banner->image,
    //                     'restaurant'=> null,
    //                     'food'=> $food?Helpers::product_data_formatting(data:$food, multi_data:false, trans:false, local:app()->getLocale()):null,
    //                 ];
    //             }
    //         }
    //     }
    //     return $data;
    // }
     public static function get_banners($zone_id)
    {
        $banners = Banner::active()->whereIn('zone_id', $zone_id)->get();
        $data = [];
        
        foreach($banners as $banner) {
            if($banner->type == 'restaurant_wise') {
                $restaurant = Restaurant::where(['id' => $banner->data, 'status' => 1])->first();
                if ($restaurant) {
                    // Eager load the vendor relationship
                    $restaurant->load('vendor');
                    
                    // Add owner_name to the restaurant data
                    $ownerName = null;
                    if ($restaurant->vendor) {
                        $ownerName = trim(($restaurant->vendor->f_name ?? '') . ' ' . ($restaurant->vendor->l_name ?? ''));
                    }

                    // Include owner_name inside the restaurant array
                    $data[] = [
                        'id' => $banner->id,
                        'title' => $banner->title,
                        'type' => $banner->type,
                        'image' => $banner->image,
                        'restaurant' => $restaurant ? Helpers::restaurant_data_formatting(data: $restaurant, multi_data: false) : null,
                    ];

                    // Now adding the owner_name inside the restaurant data
                    if ($restaurant) {
                        $data[count($data) - 1]['restaurant']['owner_name'] = $ownerName;
                    }

                    // Adding food as null (if needed for consistency)
                    $data[count($data) - 1]['food'] = null;
                }
            }
            
            if ($banner->type == 'item_wise') {
                $food = Food::whereHas('restaurant', function($query) {
                    $query->where('status', 1);
                })->where(['id' => $banner->data, 'status' => 1])->first();

                if ($food) {
                    $data[] = [
                        'id' => $banner->id,
                        'title' => $banner->title,
                        'type' => $banner->type,
                        'image' => $banner->image,
                        'restaurant' => null,
                        'food' => $food ? Helpers::product_data_formatting(data: $food, multi_data: false, trans: false, local: app()->getLocale()) : null,
                    ];
                }
            }
        }

        return $data;
    }
}
