<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Zone;
use App\Http\Controllers\Controller;
use App\CentralLogics\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use MatanYadaev\EloquentSpatial\Objects\Point;
use DB;

class ZoneController extends Controller
{
    // public function get_zones()
    // {
    //     $zones= Zone::where('status',1)->get();
    //     foreach($zones as $zone){
    //         $area = json_decode($zone->coordinates[0]->toJson(),true);
    //         $zone['formated_coordinates']=Helpers::format_coordiantes($area['coordinates']);
    //     }
    //     return response()->json($zones, 200);
    // }

public function get_zones()
    {
        // Retrieve all active zones
        $zones = Zone::where('status', 1)->get();
    
        // Loop through each zone to modify the data
        foreach ($zones as $zone) {
            // Decode the coordinates
            $area = json_decode($zone->coordinates[0]->toJson(), true);


             // Retrieve the add_radius value from the business_settings table
            // Assuming the key for add_radius in business_settings table is 'add_radius'
            $business_setting = DB::table('business_settings')
                ->where('key', 'add_radius')  // Fetching based on the key field
                ->value('value'); // Assuming the value is stored in the 'value' field
    
            // Add the add_radius value to the zone response
            $zone['add_radius'] = $business_setting ?? 0; // Default to 0 if not found
            
            // Format the coordinates using the helper function
            $zone['formatted_coordinates'] = Helpers::format_coordiantes($area['coordinates']);
            
           
        }
    
        // Return the modified zones data as JSON
        return response()->json($zones, 200);
    }
    

    public function zonesCheck(Request $request){
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'lng' => 'required',
            'zone_id' => 'required',
        ]);

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $zone = Zone::where('id',$request->zone_id)->whereContains('coordinates', new Point($request->lat, $request->lng, POINT_SRID))->exists();

        return response()->json($zone, 200);

    }

}
