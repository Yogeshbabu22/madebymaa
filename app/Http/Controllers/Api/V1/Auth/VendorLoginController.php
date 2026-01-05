<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\Tag;
use App\Models\Zone;
use App\Models\Admin;
use App\Models\Vendor;
use App\Models\Restaurant;
use App\Models\Translation;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use App\Models\SubscriptionPackage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Models\SubscriptionTransaction;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use MatanYadaev\EloquentSpatial\Objects\Point;
use App\Library\Payer;
use App\Traits\Payment;
use App\Library\Receiver;
use App\Library\Payment as PaymentInfo;
use App\Models\RestaurantPendingList;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\CentralLogics\SMS_module;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class VendorLoginController extends Controller
{
    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            // 'email' => 'required',
            'phone' => 'required',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $static_password = "MadeByMaa@2024";
        $data = [
            'phone' => $request->phone,
            'password' => $static_password
        ];
 
        if (auth('vendor')->attempt($data)) {
            // $token = $this->genarate_token($request['email']);
            $vendor = Vendor::where(['phone' => $request['phone']])->first();
            $token = $this->genarate_token($vendor->email); //updated by Gowtham.S
            $restaurant=$vendor?->restaurants[0];

            if($restaurant?->restaurant_model == 'subscription' && $restaurant->restaurant_sub_trans && $restaurant->restaurant_sub_trans->transaction_status == 0){
                return response()->json([
                    'pending_payment' => [
                        'id' =>$restaurant->restaurant_sub_trans->id
                    ]
                ], 200);
            }



            if($vendor->restaurants[0]->status == 0 &&  $vendor->status == 0)
            {
                return response()->json([
                    'errors' => [
                        ['code' => 'auth-002', 'message' => translate('messages.inactive_vendor_warning')]
                    ]
                ], 403);
            }

            if( $restaurant?->restaurant_model == 'none')
            {
                return response()->json([
                    'subscribed' => [
                        'restaurant_id' => $vendor?->restaurants[0]?->id, 'type' => 'new_join'
                    ]
                ], 200);
            }

            if ( $restaurant?->restaurant_model == 'subscription' ) {
                $rest_sub = $restaurant?->restaurant_sub;
                if (isset($rest_sub)) {
                    if ($rest_sub?->mobile_app == 0 ) {
                        return response()->json([
                            'errors' => [
                                ['code'=>'no_mobile_app', 'message'=>translate('Your Subscription Plan is not Active for Mobile App')]
                            ]
                        ], 401);
                    }
                }
            }
            if( $restaurant?->restaurant_model == 'unsubscribed' && isset($restaurant?->restaurant_sub_update_application)){
                $vendor->auth_token = $token;
                $vendor?->save();
                        if($restaurant?->restaurant_sub_update_application?->max_product== 'unlimited' ){
                            $max_product_uploads= -1;
                        }
                        else{
                            $max_product_uploads= $restaurant?->restaurant_sub_update_application?->max_product - $restaurant?->foods()?->count();
                            if($max_product_uploads > 0){
                                $max_product_uploads ?? 0;
                            }elseif($max_product_uploads < 0) {
                                $max_product_uploads = 0;
                            }
                        }

                    $data['subscription_other_data'] =  [
                        'total_bill'=>  (float) SubscriptionTransaction::where('restaurant_id', $restaurant->id)->where('package_id', $restaurant?->restaurant_sub_update_application?->package?->id)->sum('paid_amount'),
                        'max_product_uploads' => (int) $max_product_uploads,
                        ];

                return response()->json(['token' => $token, 'zone_wise_topic'=> $vendor?->restaurants[0]?->zone?->restaurant_wise_topic,
                'subscription' => $restaurant?->restaurant_sub_update_application,
                'subscription_other_data' => $data['subscription_other_data'],
                'balance' =>(float)($vendor?->wallet?->balance ?? 0),
                'restaurant_id' =>(int) $restaurant?->id,
                'package' => $restaurant?->restaurant_sub_update_application?->package
                ], 205);
            }

            if($restaurant?->restaurant_model == 'unsubscribed' && !isset($restaurant?->restaurant_sub_update_application)){
                return response()->json([
                    'subscribed' => [
                        'restaurant_id' => $vendor?->restaurants[0]?->id, 'type' => 'new_join'
                    ]
                ], 200);
            }
            $vendor->auth_token = $token;
            $vendor?->save();
            return response()->json(['token' => $token, 'zone_wise_topic'=> $vendor?->restaurants[0]?->zone?->restaurant_wise_topic], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'errors' => $errors
            ], 401);
        }
    }
    // public function login(Request $request)
    // {

    //     $validator = Validator::make($request->all(), [
    //         'phone' => 'required',
    //         'password' => 'required|min:6'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => Helpers::error_processor($validator)], 403);
    //     }

    //     $data = [
    //         'phone' => $request->phone,
    //         'password' => $request->password
    //     ];

    //     if (auth('vendor')->attempt($data)) {
    //         $token = $this->genarate_token($request['phone']);
    //         $vendor = Vendor::where(['phone' => $request['phone']])->first();

    //         $restaurant=$vendor?->restaurants[0];

    //         if($restaurant?->restaurant_model == 'subscription' && $restaurant->restaurant_sub_trans && $restaurant->restaurant_sub_trans->transaction_status == 0){
    //             return response()->json([
    //                 'pending_payment' => [
    //                     'id' =>$restaurant->restaurant_sub_trans->id
    //                 ]
    //             ], 200);
    //         }



    //         if($vendor->restaurants[0]->status == 0 &&  $vendor->status == 0)
    //         {
    //             return response()->json([
    //                 'errors' => [
    //                     ['code' => 'auth-002', 'message' => translate('messages.inactive_vendor_warning')]
    //                 ]
    //             ], 403);
    //         }

    //         if( $restaurant?->restaurant_model == 'none')
    //         {
    //             return response()->json([
    //                 'subscribed' => [
    //                     'restaurant_id' => $vendor?->restaurants[0]?->id, 'type' => 'new_join'
    //                 ]
    //             ], 200);
    //         }

    //         if ( $restaurant?->restaurant_model == 'subscription' ) {
    //             $rest_sub = $restaurant?->restaurant_sub;
    //             if (isset($rest_sub)) {
    //                 if ($rest_sub?->mobile_app == 0 ) {
    //                     return response()->json([
    //                         'errors' => [
    //                             ['code'=>'no_mobile_app', 'message'=>translate('Your Subscription Plan is not Active for Mobile App')]
    //                         ]
    //                     ], 401);
    //                 }
    //             }
    //         }
    //         if( $restaurant?->restaurant_model == 'unsubscribed' && isset($restaurant?->restaurant_sub_update_application)){
    //             $vendor->auth_token = $token;
    //             $vendor?->save();
    //                     if($restaurant?->restaurant_sub_update_application?->max_product== 'unlimited' ){
    //                         $max_product_uploads= -1;
    //                     }
    //                     else{
    //                         $max_product_uploads= $restaurant?->restaurant_sub_update_application?->max_product - $restaurant?->foods()?->count();
    //                         if($max_product_uploads > 0){
    //                             $max_product_uploads ?? 0;
    //                         }elseif($max_product_uploads < 0) {
    //                             $max_product_uploads = 0;
    //                         }
    //                     }

    //                 $data['subscription_other_data'] =  [
    //                     'total_bill'=>  (float) SubscriptionTransaction::where('restaurant_id', $restaurant->id)->where('package_id', $restaurant?->restaurant_sub_update_application?->package?->id)->sum('paid_amount'),
    //                     'max_product_uploads' => (int) $max_product_uploads,
    //                     ];

    //             return response()->json(['token' => $token, 'zone_wise_topic'=> $vendor?->restaurants[0]?->zone?->restaurant_wise_topic,
    //             'subscription' => $restaurant?->restaurant_sub_update_application,
    //             'subscription_other_data' => $data['subscription_other_data'],
    //             'balance' =>(float)($vendor?->wallet?->balance ?? 0),
    //             'restaurant_id' =>(int) $restaurant?->id,
    //             'package' => $restaurant?->restaurant_sub_update_application?->package
    //             ], 426);
    //         }

    //         if($restaurant?->restaurant_model == 'unsubscribed' && !isset($restaurant?->restaurant_sub_update_application)){
    //             return response()->json([
    //                 'subscribed' => [
    //                     'restaurant_id' => $vendor?->restaurants[0]?->id, 'type' => 'new_join'
    //                 ]
    //             ], 200);
    //         }
    //         $vendor->auth_token = $token;
    //         $vendor?->save();
    //         return response()->json(['token' => $token, 'zone_wise_topic'=> $vendor?->restaurants[0]?->zone?->restaurant_wise_topic], 200);
    //     } else {
    //         $errors = [];
    //         array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
    //         return response()->json([
    //             'errors' => $errors
    //         ], 401);
    //     }
    // }
    

    private function genarate_token($email)
    {
        $token = Str::random(120);
        $is_available = Vendor::where('auth_token', $token)->where('email', '!=', $email)->count();
        if($is_available)
        {
            $this->genarate_token($email);
        }
        return $token;
    }


    public function register(Request $request)
    {
     
     
        $status = BusinessSetting::where('key', 'toggle_restaurant_registration')->first();
        if(!isset($status) || $status->value == '0')
        {
            return response()->json(['errors' => Helpers::error_formater('self-registration', translate('messages.restaurant_self_registration_disabled'))]);
        }

        $validator = Validator::make($request->all(), [
            'fName' => 'required',
            // 'restaurant_name' => 'required',
            // 'restaurant_address' => 'required',
            'lat' => 'required|numeric|min:-90|max:90',
            'lng' => 'required|numeric|min:-180|max:180',
            'email' => 'required|email|unique:vendors',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9|unique:vendors',
            'min_delivery_time' => 'required',
            'max_delivery_time' => 'required',
            // 'password' => ['required', Password::min(8)->mixedCase()->letters()->numbers()->symbols()->uncompromised()],
            'zone_id' => 'required',
            'logo' => 'required|max:2048',
            'cover_photo' => 'nullable|max:2048',
            'vat' => 'required',
            'delivery_time_type'=>'required',
            // 'additional_documents' => 'nullable|array|max:5',
            // 'additional_documents.*' => 'nullable|max:2048',

        ]);

        if($request->zone_id)
        {
            $zone = Zone::query()
            ->whereContains('coordinates', new Point($request->lat, $request->lng, POINT_SRID))
            ->where('id',$request->zone_id)
            ->first();
            if(!$zone){
                $validator->getMessageBag()->add('latitude', translate('messages.coordinates_out_of_zone'));
            }
        }

        $data = json_decode($request->translations, true);


        // info($data);
        if (count($data) < 1) {
            $validator->getMessageBag()->add('translations', translate('messages.Name and description in english is required'));
        }

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
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

        $static_password = "MadeByMaa@2024"; //updated by Gowtham.s
        $vendor = new Vendor();
        $vendor->f_name = $request->fName;
        $vendor->l_name = $request->lName;
        $vendor->email = $request->email;
        $vendor->phone = $request->phone;
        $vendor->password = bcrypt($static_password);
        $vendor->status = 1; //updated by Gowtham.s
        $vendor->save();

        $restaurant = new Restaurant;
        $restaurant->name = $data[0]['value'];
        $restaurant->phone = $request->phone;
        $restaurant->email = $request->email;
        $restaurant->logo = Helpers::upload( dir: 'restaurant/', format:'png', image:$request->file('logo'));
        $restaurant->cover_photo = Helpers::upload( dir: 'restaurant/cover/', format:'png',image: $request->file('cover_photo'));
        $restaurant->address = $data[1]['value'];

        $restaurant->latitude = $request->lat;
        $restaurant->longitude = $request->lng;
        $restaurant->vendor_id = $vendor->id;
        $restaurant->zone_id = $request->zone_id;
        $restaurant->tax = $request->vat;
        $restaurant->delivery_time =$request->min_delivery_time .'-'. $request->max_delivery_time.'-'.$request->delivery_time_type;
        $restaurant->status = 1; //updated by Gowtham.s
        $restaurant->restaurant_model = 'none';

        if(isset($request->additional_data)  && count(json_decode($request->additional_data,true)) > 0){
            $restaurant->additional_data = $request->additional_data ;
        }

        $additional_documents = [];
        if ($request->additional_documents) {
            foreach ($request->additional_documents as $key => $imagedata) {
                $additional = [];
                foreach($imagedata as $file){
                    if(is_file($file)){
                        $file_name = Helpers::upload('additional_documents/', $file->getClientOriginalExtension(), $file);
                        $additional[] = $file_name ;
                    }
                    $additional_documents[$key] = $additional;
                }
            }
            $restaurant->additional_documents = json_encode($additional_documents);
        }

        $restaurant->save();
        $restaurant->tags()->sync($tag_ids);

        foreach ($data as $key=>$i) {
            $data[$key]['translationable_type'] = 'App\Models\Restaurant';
            $data[$key]['translationable_id'] = $restaurant->id;
        }
        Translation::insert($data);



        $cuisine_ids = [];
        $cuisine_ids = json_decode($request->cuisine_ids, true);
        $restaurant?->cuisine()?->sync($cuisine_ids);
        try{
            $admin= Admin::where('role_id', 1)->first();
            if(config('mail.status') && Helpers::get_mail_status('registration_mail_status_restaurant') == '1'){
                Mail::to($request['email'])->send(new \App\Mail\VendorSelfRegistration('pending', $vendor->f_name.' '.$vendor->l_name));
            }
            if(config('mail.status') && Helpers::get_mail_status('restaurant_registration_mail_status_admin') == '1'){
                Mail::to($admin['email'])->send(new \App\Mail\RestaurantRegistration('pending', $vendor->f_name.' '.$vendor->l_name));
            }
        }catch(\Exception $ex){
            info($ex->getMessage());
        }
       if(!empty($request->phone)){
          RestaurantPendingList::where('mobile_no',$request->phone)->update(['register_status'=>1]); 
       }
        
        return response()->json([
            'restaurant_id'=> $restaurant->id,
            'message'=>translate('messages.application_placed_successfully')],200);
    }

    public function package_view(){
        $packages= SubscriptionPackage::where('status',1)->get();
        return response()->json(['packages'=> $packages], 200);
    }

    public function business_plan(Request $request){

        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required',
            'payment' => 'nullable',
            'business_plan' => 'required|in:subscription,commission',
            'package_id' => 'nullable|required_if:business_plan,subscription',

        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $restaurant=Restaurant::findOrFail($request->restaurant_id);

        if($request->business_plan == 'subscription' && $request->package_id != null ) {
            $restaurant_id=$restaurant->id;
            $package_id=$request->package_id;
            $payment_method=$request->payment_method ?? 'free_trial';
            $reference=$request->reference ?? null;
            $discount=$request->discount ?? 0;
            $restaurant=Restaurant::findOrFail($restaurant_id);
            $type=$request->type ?? 'new_join';

            if($request->payment == 'free_trial' ){
                $status=Helpers::subscription_plan_chosen(restaurant_id:$restaurant_id , package_id:$package_id,payment_method: $payment_method ,discount:$discount, reference:$reference ,type: $type);

                if($status === 'downgrade_error'){
                    return response()->json([
                        'errors' => ['message' => translate('messages.You_can_not_downgraded_to_this_package_please_choose_a_package_with_higher_upload_limits')]
                    ], 403);
                }
            }
            elseif($request->payment == 'paying_now'){
                $digital_payment = Helpers::get_business_settings('digital_payment');
                if( $digital_payment['status'] != 1){
                    return response()->json([
                        'errors' => ['message' => translate('messages.Digital_Payment_is_disable')]
                    ], 403);
                }

                $status= Helpers::subscription_plan_chosen(restaurant_id:$restaurant_id , package_id:$package_id,payment_method: 'pay_now' ,discount:$discount, reference:$reference ,type: $type);
                if($status === 'downgrade_error'){
                    return response()->json([
                        'errors' => ['message' => translate('messages.You_can_not_downgraded_to_this_package_please_choose_a_package_with_higher_upload_limits')]
                    ], 403);
                }
                return response()->json(['id'=>$status],200);
            }
            $data=[
            'restaurant_model' => 'subscription',
            'logo'=> $restaurant->logo,
            'message' => translate('messages.application_placed_successfully')
            ];
            return response()->json($data,200);
        }

        elseif($request->business_plan == 'commission' ){
            $restaurant->restaurant_model = 'commission';
            $restaurant->save();

        $data=['restaurant_model' => 'commission',
        'logo'=> $restaurant->logo,
        'message' => translate('messages.application_placed_successfully')
        ];
        return response()->json($data,200);
        }
    }


    public function subscription_payment_api(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'callback' => 'nullable',
            'payment_gateway' => 'required',

        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $subscription = SubscriptionTransaction::with('restaurant')->where('transaction_status',0)->findOrFail($request->id);
        $payer = new Payer(
            $subscription->restaurant->name ,
            $subscription->restaurant->email,
            $subscription->restaurant->phone,
            ''
        );
        $additional_data = [
            'business_name' => BusinessSetting::where(['key'=>'business_name'])->first()?->value,
            'business_logo' => dynamicStorage('storage/app/public/business') . '/' .BusinessSetting::where(['key' => 'logo'])->first()?->value
        ];
        $payment_info = new PaymentInfo(
            success_hook: 'sub_success',
            failure_hook: 'sub_fail',
            currency_code: Helpers::currency_code(),
            payment_method: $request->payment_gateway,
            payment_platform: 'web',
            payer_id: $subscription->restaurant_id,
            receiver_id: '100',
            additional_data:  $additional_data,
            payment_amount: $subscription->paid_amount ,
            external_redirect_link: $request->has('callback')?$request['callback']:session('callback'),
            attribute: 'restaurant_subscription_payments',
            attribute_id: $subscription->id,
        );

        $receiver_info = new Receiver('Admin','example.png');
        $redirect_link = Payment::generate_link($payer, $payment_info, $receiver_info);
        $data = [
            'redirect_link' => $redirect_link,
            // 'type'=> 'subscription'
        ];
        return response()->json($data, 200);
    }
    
    public function restaurant_pending_lists(Request $request){
        
    
            $validator = Validator::make($request->all(), [
                'f_name' => 'required',
                'l_name' => 'required',
                'email' => 'required|email|unique:restaurant_pending_lists',
                'mobile_no'=>'required|unique:restaurant_pending_lists',
                // 'password' => 'required|min:8',
                'address' => 'required',
                'latitude' => 'required',
                'longitude' => 'required',
            ],
            [
                'f_name.required'=>'The first name field is required.',
                'l_name.required'=>'The last name field is required.',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            try {

                    $validatedData = $validator->validated();
                    $restaurant = RestaurantPendingList::create([
                        'f_name' => $validatedData['f_name'],
                        'l_name' => $validatedData['l_name'],
                        'email' => $validatedData['email'],
                        'mobile_no' => $validatedData['mobile_no'],
                        // 'password' => Hash::make($validatedData['password']),
                        // 'encrypt_password' => Crypt::encrypt($validatedData['password']),
                        'address' => $validatedData['address'],
                        'latitude' => $validatedData['latitude'],
                        'longitude' => $validatedData['longitude'],
                    ]);
        
                    return response()->json(['status'=>true,'message' => 'Restaurant pending list created successfully.','status_code' => 200], 200);
                } catch (\Exception $e) {
                    return response()->json(['status'=>false,'message' => 'Restaurant pending list  creation failed.','status_code' => 200,'error' =>$e->getMessage()], 500);
                }
    }
    
     public function restaurant_confirm_check(Request $request){
         
      $validator = Validator::make($request->all(), [
            'mobile_no'=>'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $data_count =  RestaurantPendingList::where('mobile_no',$request->mobile_no)->get()->count(); 
        $approved_data =  RestaurantPendingList::where('mobile_no',$request->mobile_no)->first(); 
        
        if($data_count == 0){
            return response()->json(['status'=>false,'message' => 'Data Not Found.','status_code' => 400], 400);
        }
        $waiting_status = RestaurantPendingList::where('mobile_no',$request->mobile_no)->where('status','=','pending')->where('confirm_status',0)->get()->count();
        if($waiting_status > 0){
            return response()->json(['status'=>true,'message' => 'Waiting for feedback','status_code' => 200], 200);
        }
        
        $confirm_status = RestaurantPendingList::where('mobile_no',$request->mobile_no)->where('status','=','approved')->where('confirm_status',1)->get()->count();
        
        $resgiter_status = "";
        if($approved_data->register_status ==1 ){
            $resgiter_status = "Restaurant Registered Successfully";
        }else{
            $resgiter_status = "";
        }
        if($confirm_status > 0){
            return response()->json(['status'=>true,'message' => 'The Restaurant was approved by Admin.','register_status'=>$resgiter_status,
            'data'=>$approved_data,'status_code' => 200,], 200);
        }
        
         return response()->json(['status'=>true,'message' => 'The Restaurant was Rejected by Admin.','status_code' => 200], 200);
     }
     
     
     
     // Step 1: Send OTP to Any Phone Number (New or Existing Vendor)
public function sendVendorOtp(Request $request)
{
    $validator = Validator::make($request->all(), [
        'phone' => 'required',
    ], [
        'phone.required' => translate('The phone number field is required.'),
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => Helpers::error_processor($validator)], 403);
    }

    $phone = $request->phone;

    // Check if vendor exists (just for information, not blocking)
    $vendor_exists = DB::table('vendors')->where('phone', $phone)->exists();

    $vendor_verification = BusinessSetting::where('key', 'vendor_verification')->first()->value ?? true;

    if ($vendor_verification && env('APP_MODE') != 'demo') {
        $otp_interval_time = 60; // seconds
        $verification_data = DB::table('phone_verifications')->where('phone', $phone)->first();

        if (isset($verification_data) && Carbon::parse($verification_data->updated_at)->diffInSeconds() < $otp_interval_time) {
            $time = $otp_interval_time - Carbon::parse($verification_data->updated_at)->diffInSeconds();
            $errors = [];
            array_push($errors, ['code' => 'otp', 'message' => translate('messages.please_try_again_after_') . $time . ' ' . translate('messages.seconds')]);
            return response()->json(['errors' => $errors], 405);
        }

        // Generate OTP
        $otp = rand(100000, 999999);
        DB::table('phone_verifications')->updateOrInsert(['phone' => $phone], [
            'token' => $otp,
            'otp_hit_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Debug logs
        Log::info('vendor-otp:received', [
            'phone_input' => $phone,
            'vendor_exists' => $vendor_exists
        ]);

        // Normalize phone to international format for gateway
        $sendTo = preg_replace('/\D+/', '', $phone);
        if (preg_match('/^\d{10}$/', $sendTo)) {
            $sendTo = '91' . $sendTo;
        }

        // Send OTP using SMS module
        // $sms_response = SMS_module::send($sendTo, "Your Vendor Login OTP is: " . $otp);
        $sms_response = SMS_module::send($sendTo, $otp);
        Log::info('vendor-otp:sms_result', [
            'to' => $sendTo,
            'result' => $sms_response
        ]);

        // Return response based on vendor existence
        if ($vendor_exists) {
            return response()->json([
                'is_login' => true,
                'message' => translate('OTP sent successfully to registered vendor.'),
                'phone_verify_end_url' => "api/v1/auth/vendor/verify-otp",
                'otp' => $otp // Remove this in production for security reasons
            ], 200);
        } else {
            return response()->json([
                'is_login' => false,
                'message' => translate('OTP sent successfully. This number is not registered yet.'),
                'phone_verify_end_url' => "api/v1/auth/vendor/verify-otp",
                'otp' => $otp // Remove this in production for security reasons
            ], 200);
        }
    } else {
        return response()->json(['message' => translate('OTP verification is not enabled for vendors.')], 403);
    }
}

// Step 2: Verify OTP and Check Vendor Registration
public function verifyVendorOtp(Request $request)
{
    $validator = Validator::make($request->all(), [
        'phone' => 'required',
        'otp' => 'required|numeric',
    ], [
        'phone.required' => translate('The phone number field is required.'),
        'otp.required' => translate('The OTP field is required.'),
        'otp.numeric' => translate('The OTP must be a number.'),
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => false, 'errors' => Helpers::error_processor($validator)], 403);
    }

    $phone = $request->phone;
    $otp = $request->otp;
    $otp_data = DB::table('phone_verifications')->where('phone', $phone)->first();

    // Validate OTP
    if (!$otp_data || $otp_data->token != $otp) {
        return response()->json([
            'status' => false,
            'errors' => [['code' => 'otp', 'message' => translate('Invalid OTP. Please try again.')]]
        ], 403);
    }

    // Check OTP expiry (120 seconds for vendor)
    $otp_interval_time = 120; // seconds
    if (Carbon::parse($otp_data->updated_at)->diffInSeconds() > $otp_interval_time) {
        return response()->json([
            'status' => false,
            'errors' => [['code' => 'otp', 'message' => translate('OTP has expired. Please request a new OTP.')]]
        ], 403);
    }

    // Mark OTP as used
    DB::table('phone_verifications')->where('phone', $phone)->delete();

    // NOW CHECK: Is vendor registered?
    $vendor = Vendor::where('phone', $phone)->first();

    if (!$vendor) {
        // Vendor not registered - return is_login: false
        return response()->json([
            'status' => false,
            'is_login' => false,
            'message' => translate('Phone number is not registered as a vendor. Please register first.')
        ], 200);
    }

    // Vendor exists - Proceed with login
    // Generate token
    $token = $this->genarate_token($vendor->email);
    $restaurant = $vendor?->restaurants[0];

    // Check pending payment
    if($restaurant?->restaurant_model == 'subscription' && $restaurant->restaurant_sub_trans && $restaurant->restaurant_sub_trans->transaction_status == 0){
        return response()->json([
            'pending_payment' => [
                'id' => $restaurant->restaurant_sub_trans->id
            ]
        ], 200);
    }

    // Check vendor and restaurant status
    if($vendor->restaurants[0]->status == 0 && $vendor->status == 0) {
        return response()->json([
            'errors' => [
                ['code' => 'auth-002', 'message' => translate('messages.inactive_vendor_warning')]
            ]
        ], 403);
    }

    // Check restaurant model - none
    if($restaurant?->restaurant_model == 'none') {
        return response()->json([
            'subscribed' => [
                'restaurant_id' => $vendor?->restaurants[0]?->id,
                'type' => 'new_join'
            ]
        ], 200);
    }

    // Check subscription mobile app access
    if ($restaurant?->restaurant_model == 'subscription') {
        $rest_sub = $restaurant?->restaurant_sub;
        if (isset($rest_sub)) {
            if ($rest_sub?->mobile_app == 0) {
                return response()->json([
                    'errors' => [
                        ['code'=>'no_mobile_app', 'message'=>translate('Your Subscription Plan is not Active for Mobile App')]
                    ]
                ], 401);
            }
        }
    }

    // Handle unsubscribed with update application
    if($restaurant?->restaurant_model == 'unsubscribed' && isset($restaurant?->restaurant_sub_update_application)){
        $vendor->auth_token = $token;
        $vendor?->save();

        if($restaurant?->restaurant_sub_update_application?->max_product == 'unlimited'){
            $max_product_uploads = -1;
        } else {
            $max_product_uploads = $restaurant?->restaurant_sub_update_application?->max_product - $restaurant?->foods()?->count();
            if($max_product_uploads > 0){
                $max_product_uploads ?? 0;
            } elseif($max_product_uploads < 0) {
                $max_product_uploads = 0;
            }
        }

        $data['subscription_other_data'] = [
            'total_bill' => (float) SubscriptionTransaction::where('restaurant_id', $restaurant->id)
                ->where('package_id', $restaurant?->restaurant_sub_update_application?->package?->id)
                ->sum('paid_amount'),
            'max_product_uploads' => (int) $max_product_uploads,
        ];

        return response()->json([
            'status' => true,
            'is_login' => true,
            'token' => $token,
            'zone_wise_topic' => $vendor?->restaurants[0]?->zone?->restaurant_wise_topic,
            'subscription' => $restaurant?->restaurant_sub_update_application,
            'subscription_other_data' => $data['subscription_other_data'],
            'balance' => (float)($vendor?->wallet?->balance ?? 0),
            'restaurant_id' => (int) $restaurant?->id,
            'package' => $restaurant?->restaurant_sub_update_application?->package
        ], 205);
    }

    // Handle unsubscribed without update application
    if($restaurant?->restaurant_model == 'unsubscribed' && !isset($restaurant?->restaurant_sub_update_application)){
        return response()->json([
            'subscribed' => [
                'restaurant_id' => $vendor?->restaurants[0]?->id,
                'type' => 'new_join'
            ]
        ], 200);
    }

    // Success - save token and return
    $vendor->auth_token = $token;
    $vendor?->save();

    return response()->json([
        'status' => true,
        'is_login' => true,
        'token' => $token,
        'zone_wise_topic' => $vendor?->restaurants[0]?->zone?->restaurant_wise_topic,
        'message' => translate('Vendor login successful via OTP.')
    ], 200);
}
     
     
     
        
}
