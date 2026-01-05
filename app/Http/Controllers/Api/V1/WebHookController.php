<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;
class WebHookController extends Controller
{
    
    public function handleWebHook(Request $request){
      try{
   
    $validated = Validator::make($request->all(), [
    'order_status_id' => 'required|integer',  // This should remain required
    'order_uuid' => 'required|string',         // This should remain required
    'partner_order_id' => 'required|string',   // This should remain required
    'eta' => 'nullable',                       // eta is nullable
    'deliveryStaffDetails.name' => 'nullable|string',  // Nullable
    'deliveryStaffDetails.phone' => 'nullable|string', // Nullable
    'deliveryStaffDetails.currentLocation.lat' => 'nullable', // Nullable
    'deliveryStaffDetails.currentLocation.long' => 'nullable', // Nullable
]);
        if($validated->fails()){
            return response()->json([
                'status'=>false,
                'message'=>$validated->errors()->first()
            ]);
        }
        $validate = $validated->validated();
        $orderStatusId = $validate['order_status_id'];
        $orderUuid = $validate['order_uuid'];
        $partnerOrderId = $validate['partner_order_id'];
        $deliveryStaffName = $validate['deliveryStaffDetails']['name'];
        $deliveryStaffPhone = $validate['deliveryStaffDetails']['phone'];
        $currentLat = $validate['deliveryStaffDetails']['currentLocation']['lat'];
        $currentLong = $validate['deliveryStaffDetails']['currentLocation']['long'];

        $order = Order::where('id', $partnerOrderId)->first();

        if (!$order) {
            return response()->json(['status'=>false,'message' => 'Order not found'], 404);
        }
        $statusMapping = [
            3 => 'assigned', //Assigned
            11 => 'arrived_at_pickup', // Arrived at Pickup
            4 => "picked_up",         // Picked Up
            5 => "delivered",        // Delivered
            9 => "out_for_delivery", // Out for Delivery
            8 => "arrived_at_delivery",          // Arrived
            13 => "return",          // Return 
            14 => "rto_delivered",          // RTO Delivered
        ];
        $newStatus = $statusMapping[$orderStatusId] ?? null;
        if (!$newStatus) {
            return response()->json(['status'=>false,'message' => 'Invalid status ID'], 400);
        }

        $order->order_status = $newStatus;
        $order->delivery_men_name =  $deliveryStaffName;
        $order->delivery_men_phone = $deliveryStaffPhone;
        $order->delivery_men_current_lat =  $currentLat;
        $order->delivery_men_current_long =  $currentLong;
        $order->save();

        return response()->json(['status'=>true,'message' => 'Order status updated successfully'], 200);
 }catch(\Exception $e){
        return response()->json([
           'status'=>false,
           'message'=>$e->getMessage()
        ]);
    }
    }
}
