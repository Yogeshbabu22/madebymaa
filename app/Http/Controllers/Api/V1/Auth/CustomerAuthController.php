<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\User;
use App\Models\Guest;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use Illuminate\Support\Carbon;
use App\Mail\EmailVerification;
use App\Mail\LoginVerification;
use App\Models\BusinessSetting;
use App\CentralLogics\SMS_module;
use App\Models\PhoneVerification;
use App\Models\WalletTransaction;
use App\Models\EmailVerifications;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Support\Facades\Mail;
use Modules\Gateways\Traits\SmsGateway;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage;

use App\Models\Booking;
use App\Models\Item;
use App\Http\Resources\BookingResource;

class CustomerAuthController extends Controller
{
    public function verify_phone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|min:9|max:14',
            'otp'=>'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $user = User::where('phone', $request->phone)->first();
        if($user)
        {
            if($user->is_phone_verified)
            {
                return response()->json([
                    'message' => translate('messages.phone_number_is_already_varified')
                ], 200);

            }

            if(env('APP_MODE')=='demo')
            {
                if($request['otp']=="1234")
                {
                    $user->is_phone_verified = 1;
                    $user->save();

                    return response()->json([
                        'message' => translate('messages.phone_number_varified_successfully'),
                        'otp' => 'inactive'
                    ], 200);
                }
                return response()->json([
                    'message' => translate('messages.phone_number_and_otp_not_matched')
                ], 404);
            }

            $data = DB::table('phone_verifications')->where([
                'phone' => $request['phone'],
                'token' => $request['otp'],
            ])->first();

            if($data)
            {
                DB::table('phone_verifications')->where([
                    'phone' => $request['phone'],
                    'token' => $request['otp'],
                ])->delete();

                $user->is_phone_verified = 1;
                $user->save();
                return response()->json([
                    'message' => translate('messages.phone_number_varified_successfully'),
                    // 'otp' => 'inactive'
                    'otp'=> 'Otp varified successfully'
                ], 200);
            }
            else{
                // $otp_hit = BusinessSetting::where('key', 'max_otp_hit')->first();
                // $max_otp_hit =isset($otp_hit) ? $otp_hit->value : 5 ;
                $max_otp_hit = 5;

                // $otp_hit_time = BusinessSetting::where('key', 'max_otp_hit_time')->first();
                // $max_otp_hit_time =isset($otp_hit_time) ? $otp_hit_time->value : 30 ;

                $max_otp_hit_time = 60; // seconds
                $temp_block_time = 600; // seconds

                $verification_data= DB::table('phone_verifications')->where('phone', $request['phone'])->first();

                if(isset($verification_data)){


                    // if($verification_data->is_blocked == 1){
                    //     $errors = [];
                    //     array_push($errors, ['code' => 'otp', 'message' => translate('messages.your_account_is_blocked')]);
                    //     return response()->json(['errors' => $errors ], 403);
                    // }



                    if(isset($verification_data->temp_block_time ) && Carbon::parse($verification_data->temp_block_time)->DiffInSeconds() <= $temp_block_time){
                        $time= $temp_block_time - Carbon::parse($verification_data->temp_block_time)->DiffInSeconds();

                        $errors = [];
                        array_push($errors, ['code' => 'otp_block_time',
                        'message' => translate('messages.please_try_again_after_').CarbonInterval::seconds($time)->cascade()->forHumans()
                         ]);
                        return response()->json([
                            'errors' => $errors
                        ], 405);
                    }

                    if($verification_data->is_temp_blocked == 1 && Carbon::parse($verification_data->updated_at)->DiffInSeconds() >= $max_otp_hit_time){
                        DB::table('phone_verifications')->updateOrInsert(['phone' => $request['phone']],
                            [
                                'otp_hit_count' => 0,
                                'is_temp_blocked' => 0,
                                'temp_block_time' => null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }

                    // if($verification_data->is_temp_blocked == 1 && Carbon::parse($verification_data->updated_at)->DiffInSeconds() < $max_otp_hit_time){
                    //         $errors = [];
                    //     array_push($errors, ['code' => 'otp', 'message' => translate('messages.please_try_again_after_').$time.' '.translate('messages.seconds') ]);
                    //     return response()->json([
                    //         'errors' => $errors
                    //     ], 405);
                    //     }

                    if($verification_data->otp_hit_count >= $max_otp_hit &&  Carbon::parse($verification_data->updated_at)->DiffInSeconds() < $max_otp_hit_time &&  $verification_data->is_temp_blocked == 0){

                        DB::table('phone_verifications')->updateOrInsert(['phone' => $request['phone']],
                            [
                            'is_temp_blocked' => 1,
                            'temp_block_time' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                            ]);
                        $errors = [];
                        array_push($errors, ['code' => 'otp_temp_blocked', 'message' => translate('messages.Too_many_attemps') ]);
                        return response()->json([
                            'errors' => $errors
                        ], 405);
                    }


                    // if($verification_data->otp_hit_count >= $max_otp_hit &&  Carbon::parse($verification_data->updated_at)->DiffInSeconds() < $max_otp_hit_time){

                    //     DB::table('phone_verifications')->updateOrInsert(['phone' => $request['phone']],
                    //         [
                    //         // 'is_temp_blocked' => 1,
                    //         'created_at' => now(),
                    //         'updated_at' => now(),
                    //         ]);
                    //         // $errors = [];
                    //         array_push($errors, ['code' => 'otp_warning', 'message' =>translate('messages.Too_many_attemps') ]);
                    //         return response()->json([
                    //             'errors' => $errors
                    //         ], 405);
                    // }
                }


                DB::table('phone_verifications')->updateOrInsert(['phone' => $request['phone']],
                [
                'otp_hit_count' => DB::raw('otp_hit_count + 1'),
                'updated_at' => now(),
                'temp_block_time' => null,
                ]);

                return response()->json([
                    'message' => translate('messages.phone_number_and_otp_not_matched')
                ], 404);
            }
        }
        return response()->json([
            'message' => translate('messages.not_found')
        ], 404);

    }

    public function check_email(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:users'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $email_verification= BusinessSetting::where(['key'=>'email_verification'])->first();

        if (isset($email_verification) && $email_verification->value){
            $token = rand(1000, 9999);
            dd($token);
            DB::table('email_verifications')->insert([
                'email' => $request['email'],
                'token' => $token,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
         
            try{
            if (config('mail.status') && Helpers::get_mail_status('registration_otp_mail_status_user') == '1') {
                $user = User::where('email', $request['email'])->first();
                Mail::to($request['email'])->send(new EmailVerification($token,$user->f_name));
            }
            }catch(\Exception $ex){
                info($ex->getMessage());
            }


            return response()->json([
                'message' => translate('Email is ready to register'),
                'token' => 'active'
            ], 200);
        }else{
            return response()->json([
                'message' => translate('Email is ready to register'),
                'token' => 'inactive'
            ], 200);
        }
    }

    public function verify_email(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $verify = EmailVerifications::where(['email' => $request['email'], 'token' => $request['token']])->first();

        if (isset($verify)) {
            $verify->delete();
            return response()->json([
                'message' => translate('messages.token_varified'),
            ], 200);
        }

        $errors = [];
        array_push($errors, ['code' => 'token', 'message' => translate('messages.token_not_found')]);
        return response()->json(['errors' => $errors ]
        , 404);
    }

    // public function register(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'f_name' => 'required',
    //         'l_name' => 'required',
    //         'email' => 'required|unique:users',
    //         'phone' => 'required|unique:users',
    //         'password' => ['required', Password::min(8)],

    //     ], [
    //         'f_name.required' => translate('The first name field is required.'),
    //         'l_name.required' => translate('The last name field is required.'),
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => Helpers::error_processor($validator)], 403);
    //     }
    //     $ref_by= null ;
    //     $customer_verification = BusinessSetting::where('key','customer_verification')->first()->value;
    //     //Save point to refeer
    //     if($request->ref_code) {
    //         $ref_status = BusinessSetting::where('key','ref_earning_status')->first()->value;
    //         if ($ref_status != '1') {
    //             return response()->json(['errors'=>Helpers::error_formater('ref_code', translate('messages.referer_disable'))], 403);
    //         }

    //         $referar_user = User::where('ref_code', '=', $request->ref_code)->first();
    //         if (!$referar_user || !$referar_user->status) {
    //             return response()->json(['errors'=>Helpers::error_formater('ref_code',translate('messages.referer_code_not_found'))], 405);
    //         }

    //         if(WalletTransaction::where('reference', $request->phone)->first()) {
    //             return response()->json(['errors'=>Helpers::error_formater('phone',translate('Referrer code already used'))], 203);
    //         }

    //         $notification_data = [
    //             'title' => translate('messages.Your_referral_code_is_used_by').' '.$request->f_name.' '.$request->l_name,
    //             'description' => translate('Be_prepare_to_receive_when_they_complete_there_first_purchase'),
    //             'order_id' => '',
    //             'image' => '',
    //             'type' => 'referral_code',
    //         ];

    //         if($referar_user?->cm_firebase_token){
    //             Helpers::send_push_notif_to_device($referar_user?->cm_firebase_token, $notification_data);
    //             DB::table('user_notifications')->insert([
    //                 'data' => json_encode($notification_data),
    //                 'user_id' => $referar_user?->id,
    //                 'created_at' => now(),
    //                 'updated_at' => now()
    //             ]);
    //         }


    //         $ref_by= $referar_user->id;
    //     }

    //     $user = User::create([
    //         'f_name' => $request->f_name,
    //         'l_name' => $request->l_name,
    //         'email' => $request->email,
    //         'phone' => $request->phone,
    //         'ref_by' =>   $ref_by,
    //         'password' => bcrypt($request->password),
    //     ]);
    //     $user->ref_code = Helpers::generate_referer_code($user);
    //     $user->save();

    //     $token = $user->createToken('RestaurantCustomerAuth')->accessToken;

    //     if($customer_verification && env('APP_MODE') !='demo')
    //     {

    //         // $interval_time = BusinessSetting::where('key', 'otp_interval_time')->first();
    //         // $otp_interval_time= isset($interval_time) ? $interval_time->value : 20;
    //         $otp_interval_time= 60; //seconds
    //         $verification_data= DB::table('phone_verifications')->where('phone', $request['phone'])->first();

    //         if(isset($verification_data) &&  Carbon::parse($verification_data->updated_at)->DiffInSeconds() < $otp_interval_time){
    //             $time= $otp_interval_time - Carbon::parse($verification_data->updated_at)->DiffInSeconds();
    //             $errors = [];
    //             array_push($errors, ['code' => 'otp', 'message' =>  translate('messages.please_try_again_after_').$time.' '.translate('messages.seconds')]);
    //             return response()->json([
    //                 'errors' => $errors
    //             ], 405);
    //         }

    //         $otp = rand(1000, 9999);
    //         DB::table('phone_verifications')->updateOrInsert(['phone' => $request['phone']],
    //             [
    //             'token' => $otp,
    //             'otp_hit_count' => 0,
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //             ]);

    //             $mail_status = Helpers::get_mail_status('registration_otp_mail_status_user');
    //             if (config('mail.status') && $mail_status == '1') {
    //                 Mail::to($request['email'])->send(new EmailVerification($otp,$request->f_name));
    //             }
    //             $published_status = 0;
    //             $payment_published_status = config('get_payment_publish_status');
    //             if (isset($payment_published_status[0]['is_published'])) {
    //                 $published_status = $payment_published_status[0]['is_published'];
    //             }

    //             if($published_status == 1){
    //                 $response = SmsGateway::send($request['phone'],$otp);
    //             }else{
    //                 $response = SMS_module::send($request['phone'],$otp);
    //             }

    //         if($response != 'success')
    //         {
    //             $errors = [];
    //             array_push($errors, ['code' => 'otp', 'message' => translate('messages.faield_to_send_sms')]);
    //             return response()->json([
    //                 'errors' => $errors
    //             ], 405);
    //         }
    //     }
    //     try
    //     {
    //         if (config('mail.status') && $request->email && Helpers::get_mail_status('registration_mail_status_user') == '1') {
    //             Mail::to($request->email)->send(new \App\Mail\CustomerRegistration($request->f_name . ' ' . $request->l_name));
    //         }
    //     }
    //     catch(\Exception $ex)
    //     {
    //         info($ex->getMessage());
    //     }

    //     if($request->guest_id  && isset($user->id)){

    //         $userStoreIds = Cart::where('user_id', $request->guest_id)
    //             ->join('food', 'carts.item_id', '=', 'food.id')
    //             ->pluck('food.restaurant_id')
    //             ->toArray();

    //         Cart::where('user_id', $user->id)
    //             ->whereHas('item', function ($query) use ($userStoreIds) {
    //                 $query->whereNotIn('restaurant_id', $userStoreIds);
    //             })
    //             ->delete();

    //         Cart::where('user_id', $request->guest_id)->update(['user_id' => $user->id,'is_guest' => 0]);
    //     }

    //     return response()->json(['token' => $token,'code'=>$otp,'is_phone_verified' => 0, 'phone_verify_end_url'=>"api/v1/auth/verify-phone" ], 200);
    // }
    
 public function register(Request $request)
{
    
   // dd('kjsdhjkh');
    // Validate input fields
    $validator = Validator::make($request->all(), [
        'f_name' => 'required',
        'l_name' => 'required',
        'email' => 'required|unique:users',
        'phone' => 'required|unique:users',
        // 'password' => ['required', Password::min(8)],
    ], [
        'f_name.required' => translate('The first name field is required.'),
        'l_name.required' => translate('The last name field is required.'),
    ]);

    // Return validation errors if any
    if ($validator->fails()) {
        return response()->json(['errors' => Helpers::error_processor($validator)], 403);
    }
 
    // Handle referral code if provided
    $ref_by = null;
    if ($request->ref_code) {
        $ref_status = BusinessSetting::where('key', 'ref_earning_status')->first()->value;
        if ($ref_status != '1') {
            return response()->json(['errors' => Helpers::error_formater('ref_code', translate('messages.referer_disable'))], 403);
        }

        $referar_user = User::where('ref_code', '=', $request->ref_code)->first();
        if (!$referar_user || !$referar_user->status) {
            return response()->json(['errors' => Helpers::error_formater('ref_code', translate('messages.referer_code_not_found'))], 405);
        }

        // Check if the referral code has already been used
        if (WalletTransaction::where('reference', $request->phone)->first()) {
            return response()->json(['errors' => Helpers::error_formater('phone', translate('Referrer code already used'))], 203);
        }

        // Send notification to the referrer
        $notification_data = [
            'title' => translate('messages.Your_referral_code_is_used_by') . ' ' . $request->f_name . ' ' . $request->l_name,
            'description' => translate('Be_prepare_to_receive_when_they_complete_there_first_purchase'),
            'order_id' => '',
            'image' => '',
            'type' => 'referral_code',
        ];

        if ($referar_user?->cm_firebase_token) {
            Helpers::send_push_notif_to_device($referar_user?->cm_firebase_token, $notification_data);
            DB::table('user_notifications')->insert([
                'data' => json_encode($notification_data),
                'user_id' => $referar_user?->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $ref_by = $referar_user->id;
    }

    // Create the user
    $user = User::create([
        'f_name' => $request->f_name,
        'l_name' => $request->l_name,
        'email' => $request->email,
        'phone' => $request->phone,
        'ref_by' => $ref_by,
        // 'password' => bcrypt($request->password),
    ]);

    // Generate and save referral code
    $user->ref_code = Helpers::generate_referer_code($user);
    $user->save();

    // Generate access token
    $token = $user->createToken('RestaurantCustomerAuth')->accessToken;

    // Optionally, send a welcome email to the user
    try {
        if (config('mail.status') && $request->email && Helpers::get_mail_status('registration_mail_status_user') == '1') {
            Mail::to($request->email)->send(new \App\Mail\CustomerRegistration($request->f_name . ' ' . $request->l_name));
        }
    } catch (\Exception $ex) {
        info($ex->getMessage());
    }

    // Handle guest cart conversion if applicable
    if ($request->guest_id && isset($user->id)) {
        $userStoreIds = Cart::where('user_id', $request->guest_id)
            ->join('food', 'carts.item_id', '=', 'food.id')
            ->pluck('food.restaurant_id')
            ->toArray();

        Cart::where('user_id', $user->id)
            ->whereHas('item', function ($query) use ($userStoreIds) {
                $query->whereNotIn('restaurant_id', $userStoreIds);
            })
            ->delete();

        Cart::where('user_id', $request->guest_id)->update(['user_id' => $user->id, 'is_guest' => 0]);
    }

    // Return success response with token and user data
    return response()->json([
        'token' => $token,
        'is_phone_verified' => 0,
         'user_id' => $user->id,
    ], 200);
}



//  public function requestOtp(Request $request)
//     {
//         $validator = Validator::make($request->all(), [
//             'phone' => 'required|exists:users,phone',
//         ], [
//             'phone.required' => translate('The phone number field is required.'),
//             'phone.exists' => translate('The phone number is not associated with any account.'),
//         ]);
    
//         if ($validator->fails()) {
//             return response()->json(['errors' => Helpers::error_processor($validator)], 403);
//         }
    
//         $phone = $request->phone;
//         $customer_verification = BusinessSetting::where('key', 'customer_verification')->first()->value;
    
//         if ($customer_verification && env('APP_MODE') != 'demo') {
//             $otp_interval_time = 60; // seconds
//             $verification_data = DB::table('phone_verifications')->where('phone', $phone)->first();
    
//             if (isset($verification_data) && Carbon::parse($verification_data->updated_at)->diffInSeconds() < $otp_interval_time) {
//                 $time = $otp_interval_time - Carbon::parse($verification_data->updated_at)->diffInSeconds();
//                 $errors = [];
//                 array_push($errors, ['code' => 'otp', 'message' => translate('messages.please_try_again_after_') . $time . ' ' . translate('messages.seconds')]);
//                 return response()->json(['errors' => $errors], 405);
//             }
    
//             // Generate OTP
//             $otp = rand(1000, 9999);
//             DB::table('phone_verifications')->updateOrInsert(['phone' => $phone], [
//                 'token' => $otp,
//                 'otp_hit_count' => 0,
//                 'created_at' => now(),
//                 'updated_at' => now(),
//             ]);
    
//             // Instead of sending an SMS, we return the OTP directly for this example
//             // In practice, you would use an actual SMS service to send the OTP
    
//             return response()->json(['message' => translate('OTP sent successfully. Please verify your OTP.'), 'phone_verify_end_url' => "api/v1/auth/verify-otp", 'otp' => $otp], 200);
//         } else {
//             return response()->json(['message' => translate('OTP verification is not enabled.')], 403);
//         }
//     }
    
//M  public function requestOtp(Request $request)
// {
//     $validator = Validator::make($request->all(), [
//         'phone' => 'required', // Removed 'exists' validation
//     ], [
//         'phone.required' => translate('The phone number field is required.'),
//     ]);

//     if ($validator->fails()) {
//         return response()->json(['errors' => Helpers::error_processor($validator)], 403);
//     }

//     $phone = $request->phone;
//     $customer_verification = BusinessSetting::where('key', 'customer_verification')->first()->value;

//     // Check if the phone number exists in the 'users' table
//     $user_exists = DB::table('users')->where('phone', $phone)->exists();

//     if ($customer_verification && env('APP_MODE') != 'demo') {
//         $otp_interval_time = 60; // seconds
//         $verification_data = DB::table('phone_verifications')->where('phone', $phone)->first();

//         if (isset($verification_data) && Carbon::parse($verification_data->updated_at)->diffInSeconds() < $otp_interval_time) {
//             $time = $otp_interval_time - Carbon::parse($verification_data->updated_at)->diffInSeconds();
//             $errors = [];
//             array_push($errors, ['code' => 'otp', 'message' => translate('messages.please_try_again_after_') . $time . ' ' . translate('messages.seconds')]);
//             return response()->json(['errors' => $errors], 405);
//         }

//         // Generate OTP
//         $otp = rand(100000, 999999);
//         DB::table('phone_verifications')->updateOrInsert(['phone' => $phone], [
//             'token' => $otp,
//             'otp_hit_count' => 0,
//             'created_at' => now(),
//             'updated_at' => now(),
//         ]);

//         // Follow a different flow if the user exists or not
//         if ($user_exists) {
//             // Existing user flow (e.g., additional handling for registered users)
//             return response()->json(['message' => translate('OTP sent successfully. Please verify your OTP for registered user.'), 'phone_verify_end_url' => "api/v1/auth/login-otp", 'otp' => $otp], 200);
//         } else {
//             // New number flow (e.g., OTP for non-registered users)
//             return response()->json(['message' => translate('OTP sent successfully. Please verify your OTP.'), 'phone_verify_end_url' => "api/v1/auth/register-otp", 'otp' => $otp], 200);
//         }
//     } else {
//         return response()->json(['message' => translate('OTP verification is not enabled.')], 403);
//     }
// }
    
    public function requestOtp(Request $request)
{
    $validator = Validator::make($request->all(), [
        'phone' => 'required', // Removed 'exists' validation
    ], [
        'phone.required' => translate('The phone number field is required.'),
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => Helpers::error_processor($validator)], 403);
    }

    $phone = $request->phone;
    $customer_verification = BusinessSetting::where('key', 'customer_verification')->first()->value;

    // Check if the phone number exists in the 'users' table
    $user_exists = DB::table('users')->where('phone', $phone)->exists();

    if ($customer_verification && env('APP_MODE') != 'demo') {
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
        \Log::info('request-otp:received', [
            'phone_input' => $phone,
            'user_exists' => $user_exists
        ]);

        // Normalize phone to international format for gateway
        $sendTo = preg_replace('/\D+/', '', $phone);
        if (preg_match('/^\d{10}$/', $sendTo)) {
            $sendTo = '91' . $sendTo;
        }
        // Send OTP using SMS module
        $sms_response = SMS_module::send($sendTo, "Your OTP is: " . $otp);
        \Log::info('request-otp:sms_result', [
            'to' => $sendTo,
            'result' => $sms_response
        ]);
   
        // Follow a different flow if the user exists or not
        if ($user_exists) {
            // Existing user flow (e.g., additional handling for registered users)
            return response()->json([
                'message' => translate('OTP sent successfully. Please verify your OTP for registered user.'),
                'phone_verify_end_url' => "api/v1/auth/login-otp",
                'otp' => $otp // Remove this in production for security reasons
            ], 200);
        } else {
            // New number flow (e.g., OTP for non-registered users)
            return response()->json([
                'message' => translate('OTP sent successfully. Please verify your OTP.'),
                'phone_verify_end_url' => "api/v1/auth/register-otp",
                'otp' => $otp // Remove this in production for security reasons
            ], 200);
        }
    } else {
        return response()->json(['message' => translate('OTP verification is not enabled.')], 403);
    }
}

// old manish+pratham    
    // public function verify_Login_Otp(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'phone' => 'required|exists:users,phone',
    //         'otp' => 'required|numeric',
    //     ], [
    //         'phone.required' => translate('The phone number field is required.'),
    //         'phone.exists' => translate('The phone number is not associated with any account.You have to register first.'),
    //         'otp.required' => translate('The OTP field is required.'),
    //         'otp.numeric' => translate('The OTP must be a number.'),
    //     ]);
    
    //     if ($validator->fails()) {
    //         return response()->json(['errors' => Helpers::error_processor($validator)], 403);
    //     }
    
    //     $phone = $request->phone;
    //     $otp = $request->otp;
    //     $otp_data = DB::table('phone_verifications')->where('phone', $phone)->first();
    
    //     if (!$otp_data || $otp_data->token != $otp) {
    //         return response()->json(['errors' => [['code' => 'otp', 'message' => translate('Invalid OTP. Please try again.')]]], 403);
    //     }
    
    //     // Check OTP expiry
    //     $otp_interval_time = 60; // seconds
    //     if (Carbon::parse($otp_data->updated_at)->diffInSeconds() > $otp_interval_time) {
    //         return response()->json(['errors' => [['code' => 'otp', 'message' => translate('OTP has expired. Please request a new OTP.')]]], 403);
    //     }
    
    //     // Mark OTP as used
    //     DB::table('phone_verifications')->where('phone', $phone)->delete();
    
    //     // Authenticate user
    //     $user = User::where('phone', $phone)->first();
    //     $token = $user->createToken('RestaurantCustomerAuth')->accessToken;
    
    //     return response()->json(['token' => $token, 'message' => translate('Login successful.')], 200);
    // }
    
    
    // wihout token
//     public function verify_Login_Otp(Request $request)
// {
//     $validator = Validator::make($request->all(), [
//         'phone' => 'required',
//         'otp'   => 'required|numeric',
//     ], [
//         'phone.required' => translate('The phone number field is required.'),
//         'otp.required' => translate('The OTP field is required.'),
//         'otp.numeric' => translate('The OTP must be a number.'),
//     ]);

//     if ($validator->fails()) {
//         return response()->json(['status' => false, 'errors' => Helpers::error_processor($validator)], 200);
//     }

//     $phone = $request->phone;
//     $otp = $request->otp;

//     // Get OTP details
//     $otp_data = DB::table('phone_verifications')->where('phone', $phone)->first();

//     if (!$otp_data || $otp_data->token != $otp) {
//         return response()->json(['status' => false, 'errors' => [['code' => 'otp', 'message' => translate('Invalid OTP. Please try again.')]]], 403);
//     }

//     // OTP Expiry check (60 sec)
//     $otp_interval_time = 60;
//     if (Carbon::parse($otp_data->updated_at)->diffInSeconds() > $otp_interval_time) {
//         return response()->json(['status' => false, 'errors' => [['code' => 'otp', 'message' => translate('OTP has expired. Please request a new OTP.')]]], 403);
//     }

//     // Delete OTP (use once)
//     DB::table('phone_verifications')->where('phone', $phone)->delete();


//     // Check if user exists
//     $user = User::where('phone', $phone)->first();

//     if ($user) {
//         // Already registered
//         $is_signup = 1;
//         $token = $user->createToken('AuthToken')->accessToken;
//     } else {
//         // Not registered
//         $is_signup = 0;
//         $token = null; // No token because no user
//     }

//     return response()->json([
//         'status' => true,
//         'signup' => $is_signup,     // 1 = registered, 0 = not registered
//         'token'  => $token,
//         'message' => translate('OTP verified successfully.'),
//     ], 200);
// }








// redum token

// public function verify_Login_Otp(Request $request)
// {
//     $validator = Validator::make($request->all(), [
//         'phone' => 'required',
//         'otp'   => 'required|numeric',
//     ]);

//     if ($validator->fails()) {
//         return response()->json(['status' => false, 'errors' => Helpers::error_processor($validator)], 200);
//     }

//     $phone = $request->phone;
//     $otp = $request->otp;

//     $otp_data = DB::table('phone_verifications')->where('phone', $phone)->first();

//     if (!$otp_data || $otp_data->token != $otp) {
//         return response()->json(['status' => false, 'errors' => [['code' => 'otp', 'message' => translate('Invalid OTP.')]]], 403);
//     }

//     // Expiry
//     if (Carbon::parse($otp_data->updated_at)->diffInSeconds() > 60) {
//         return response()->json(['status' => false, 'errors' => [['code' => 'otp', 'message' => translate('OTP expired.')]]], 403);
//     }

//     DB::table('phone_verifications')->where('phone', $phone)->delete();

//     // check existing user
//     $user = User::where('phone', $phone)->first();

//     // 1 = registered, 0 = not registered
//     $signup = $user ? 1 : 0;

//     // 👉 token generate without user
//     $custom_token = bin2hex(random_bytes(32)); // 64-character token

//     return response()->json([
//         'status'  => true,
//         'signup'  => $signup,
//         'token'   => $custom_token,
//         'message' => "OTP verified successfully."
//     ], 200);
// }



//real passport token
public function verify_Login_Otp(Request $request)
{
    $validator = Validator::make($request->all(), [
        'phone' => 'required',
        'otp'   => 'required|numeric',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => false, 'errors' => Helpers::error_processor($validator)], 200);
    }

    $phone = $request->phone;
    $otp = $request->otp;

    // Check OTP Table
    $otp_data = DB::table('phone_verifications')->where('phone', $phone)->first();

    if (!$otp_data || $otp_data->token != $otp) {
        return response()->json([
            'status' => false,
            'errors' => [['code' => 'otp', 'message' => "Invalid OTP"]]
        ], 403);
    }

    // Expiry Check
    if (Carbon::parse($otp_data->updated_at)->diffInSeconds() > 60) {
        return response()->json([
            'status' => false,
            'errors' => [['code' => 'otp', 'message' => "OTP has expired"]]
        ], 403);
    }

    // OTP used → delete
    DB::table('phone_verifications')->where('phone', $phone)->delete();

    // Check Registered User
    $user = User::where('phone', $phone)->first();

    if ($user) {
        // ✔ Registered user → passport token generate
        $token = $user->createToken('CustomerAuth')->accessToken;

        return response()->json([
            'status'  => true,
            'signup'  => 1,   // already registered
            'token'   => $token,   // valid passport token
            'message' => "Login successful."
        ], 200);

    } else {

        // ot registered → no token
        return response()->json([
            'status'  => true,
            'signup'  => 0,   // needs signup
            'token'   => null,
            'message' => "OTP verified. Please complete signup."
        ], 200);
    }
}




// public function verify_Login_Otp(Request $request)
// {
//     $phone = $request->phone;
//     $otp = $request->otp;

//     $otp_data = DB::table('phone_verifications')->where('phone', $phone)->first();

//     if (!$otp_data) {
//         return response()->json([
//             'debug' => 'No OTP row found for this phone',
//             'status' => false,
//             'errors' => [['code' => 'otp', 'message' => 'No OTP found']]
//         ]);
//     }

//     if ($otp_data->token != $otp) {
//         return response()->json([
//             'debug' => [
//                 'db_token' => $otp_data->token,
//                 'input_otp' => $otp
//             ],
//             'status' => false,
//             'errors' => [['code' => 'otp', 'message' => 'Invalid OTP']]
//         ]);
//     }

//     return response()->json([
//         'status' => true,
//         'debug' => 'OTP matched successfully',
//     ]);
// }



































    
//  public function verify_Login_Otp(Request $request)
//     {
//         $validator = Validator::make($request->all(), [
//             'phone' => 'required|exists:users,phone',
//             'otp' => 'required|numeric',
//         ], [
//             'phone.required' => translate('The phone number field is required.'),
//             'phone.exists' => translate('The phone number is not associated with any account. You have to register first.'),
//             'otp.required' => translate('The OTP field is required.'),
//             'otp.numeric' => translate('The OTP must be a number.'),
//         ]);
    
//         if ($validator->fails()) {
//             return response()->json(['status' => false, 'errors' => Helpers::error_processor($validator)], 200);
//         }
    
//         $phone = $request->phone;
//         $otp = $request->otp;
//         $otp_data = DB::table('phone_verifications')->where('phone', $phone)->first();
    
//         if (!$otp_data || $otp_data->token != $otp) {
//             return response()->json(['status' => false, 'errors' => [['code' => 'otp', 'message' => translate('Invalid OTP. Please try again.')]]], 403);
//         }
    
//         // Check OTP expiry
//         $otp_interval_time = 60; // seconds
//         if (Carbon::parse($otp_data->updated_at)->diffInSeconds() > $otp_interval_time) {
//             return response()->json(['status' => false, 'errors' => [['code' => 'otp', 'message' => translate('OTP has expired. Please request a new OTP.')]]], 403);
//         }
    
//         // Mark OTP as used
//         DB::table('phone_verifications')->where('phone', $phone)->delete();
    
//         // Authenticate user
//         $user = User::where('phone', $phone)->first();
//         $token = $user->createToken('RestaurantCustomerAuth')->accessToken;
    
//         return response()->json(['status' => true, 'token' => $token, 'message' => translate('Login successful.')], 200);
//     }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $data = [
            'phone' => $request->phone,
            'password' => $request->password
        ];

        $customer_verification = BusinessSetting::where('key','customer_verification')->first()->value;
        if (auth()->attempt($data)) {
            $token = auth()->user()->createToken('RestaurantCustomerAuth')->accessToken;
            if(!auth()->user()->status)
            {
                $errors = [];
                array_push($errors, ['code' => 'auth-003', 'message' => translate('messages.your_account_is_blocked')]);
                return response()->json([
                    'errors' => $errors
                ], 403);
            }
            $user = auth()->user();
            if($customer_verification && !auth()->user()->is_phone_verified && env('APP_MODE') != 'demo')
            {

                // $interval_time = BusinessSetting::where('key', 'otp_interval_time')->first();
                // $otp_interval_time= isset($interval_time) ? $interval_time->value : 60;
                $otp_interval_time= 60; //seconds

                $verification_data= DB::table('phone_verifications')->where('phone', $request['phone'])->first();

                if(isset($verification_data) &&  Carbon::parse($verification_data->updated_at)->DiffInSeconds() < $otp_interval_time){

                    $time= $otp_interval_time - Carbon::parse($verification_data->updated_at)->DiffInSeconds();
                    $errors = [];
                    array_push($errors, ['code' => 'otp', 'message' =>  translate('messages.please_try_again_after_').$time.' '.translate('messages.seconds')]);
                    return response()->json([
                        'errors' => $errors
                    ], 405);
                }

                $otp = rand(100000, 999999);
                DB::table('phone_verifications')->updateOrInsert(['phone' => $request['phone']],
                    [
                    'token' => $otp,
                    'otp_hit_count' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                    ]);

                if (config('mail.status') && Helpers::get_mail_status('login_otp_mail_status_user') == '1') {
                    Mail::to($user['email'])->send(new LoginVerification($otp,$user->f_name));
                }

                $published_status = 0;
                $payment_published_status = config('get_payment_publish_status');
                if (isset($payment_published_status[0]['is_published'])) {
                    $published_status = $payment_published_status[0]['is_published'];
                }

                if($published_status == 1){
                    $response = SmsGateway::send($request['phone'],$otp);
                }else{
                    $response = SMS_module::send($request['phone'],$otp);
                }
                // $response = 'qq';
                if($response != 'success')
                {
                    $errors = [];
                    array_push($errors, ['code' => 'otp', 'message' => translate('messages.faield_to_send_sms')]);
                    return response()->json([
                        'errors' => $errors
                    ], 405);
                }
            }

            if($user->ref_code == null && isset($user->id)){
                $ref_code = Helpers::generate_referer_code($user);
                DB::table('users')->where('phone', $user->phone)->update(['ref_code' => $ref_code]);
            }
            if($request->guest_id  && isset($user->id)){

                $userStoreIds = Cart::where('user_id', $request->guest_id)
                    ->join('food', 'carts.item_id', '=', 'food.id')
                    ->pluck('food.restaurant_id')
                    ->toArray();

                Cart::where('user_id', $user->id)
                    ->whereHas('item', function ($query) use ($userStoreIds) {
                        $query->whereNotIn('restaurant_id', $userStoreIds);
                    })
                    ->delete();

                Cart::where('user_id', $request->guest_id)->update(['user_id' => $user->id,'is_guest' => 0]);
            }
           
            return response()->json(['token' => $token,'message'=> "You have been login succesfully" ,'is_phone_verified'=>auth()->user()->is_phone_verified], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => translate('messages.Unauthorized')]);
            return response()->json([
                'errors' => $errors
            ], 401);
        }
    }

    public function guest_request(Request $request)
    {
        $guest = new Guest();
        $guest->ip_address = $request->ip();
        $guest->fcm_token = $request->fcm_token;

        if ($guest->save()) {
            return response()->json([
                'message' => translate('messages.guest_varified'),
                'guest_id' => $guest->id,
            ], 200);
        }

        return response()->json([
            'message' => translate('messages.failed')
        ], 404);
    }
    
    
public function uploadPhoto(Request $request)
{
    // Validate the request to ensure an image is provided
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB file size
    ], [
        'user_id.required' => translate('User ID is required.'),
        'user_id.exists' => translate('User does not exist.'),
        'photo.required' => translate('A photo is required.'),
        'photo.image' => translate('The file must be an image.'),
        'photo.mimes' => translate('Only jpeg, png, jpg, and gif formats are allowed.'),
        'photo.max' => translate('Image size must not exceed 2MB.'),
    ]);

    // Return validation errors if any
    if ($validator->fails()) {
        return response()->json(['errors' => Helpers::error_processor($validator)], 403);
    }

    // Retrieve the user
    $user = User::find($request->user_id);

    // Store the photo
    if ($request->hasFile('photo')) {
        // Delete the old photo if it exists
        if ($user->image) {
            Storage::disk('public')->delete($user->image);
        }

        // Store the new photo in the 'uploads/users' directory and get the path
        $photoPath = $request->file('photo')->store('uploads/users', 'public');

        // Update the user's image field with the new path
        $user->image = $photoPath;
        $user->save();
    }

    // Return success response with the new photo URL
    return response()->json([
        'message' => translate('Photo uploaded successfully.'),
        // 'photo_url' => Storage::disk('public')->url($user->image),
    ], 200);
}

// Create a new booking
// public function store(Request $request)
// {
//     // dd($request);
//     $validated = $request->validate([
//         'item_id' => 'required|exists:items,id',
//         'customer_name' => 'required|string|max:255',
//         'customer_email' => 'required|email',
//         'booking_date' => 'required|date',
//     ]);

//     $item = Item::find($validated['item_id']);

//     if ($item->delivery_type == 'advance') {
//         $preparationTime = $item->preparation_time_hours;
//         $earliestDeliveryTime = Carbon::parse($validated['booking_date'])->addHours($preparationTime);
//         $validated['booking_date'] = $earliestDeliveryTime;
//     }

//     $booking = Booking::create($validated);

//     return response()->json([
//         'message' => 'Booking created successfully!',
//         'data' => $booking
//     ], 201);
// }

public function store(Request $request)
{
    // First, validate the item_id and other required fields
    $validated = $request->validate([
        'item_id' => 'required|exists:items,id', // Validate that item_id exists in the items table
        'customer_name' => 'required|string|max:255',
        'customer_email' => 'required|email',
        'booking_date' => 'required|date',
        'delivery_type' => [
            'required',
            function ($attribute, $value, $fail) use ($request) {
                // Check if the item_id exists first
                $item = Item::find($request->item_id);
                if (!$item) {
                    $fail('The selected item does not exist.');
                } elseif ($item->delivery_type !== $value) {
                    // Check if the delivery_type matches the one in the Item table
                    $fail('The selected delivery type is invalid for the item.');
                }
            },
        ],
    ]);

    // Retrieve the Item to access delivery_type and preparation time
    $item = Item::find($validated['item_id']);

    // If delivery_type is 'advance', adjust the booking date based on preparation time
    $preparationTime = null;
    if ($item->delivery_type === 'advance') {
        $preparationTime = $item->preparation_time_hours;
        $earliestDeliveryTime = Carbon::parse($validated['booking_date'])->addHours($preparationTime);
        $validated['booking_date'] = $earliestDeliveryTime;
    }

    // Create the booking with validated data
    $booking = Booking::create($validated);

    // Prepare a response, including preparation time message
    $response = [
        'item_id' => $booking->item_id,
        'item_name' => $item->name,  // Include item name from Item table
        'customer_name' => $booking->customer_name,
        'customer_email' => $booking->customer_email,
        'booking_date' => $booking->booking_date,
        'delivery_type' => $validated['delivery_type'],
        'preparation_time_message' => $preparationTime ? "Preparation time is {$preparationTime} hours." : "No preparation time required.",
    ];

    return response()->json([
        'message' => 'Booking created successfully!',
        'data' => $response
    ], 201);
}




public function index()
{
    // Fetch all bookings with related items
    $bookings = Booking::all()->map(function ($booking) {
        $item = Item::find($booking->item_id); // Fetch the item based on item_id

        return [
            // 'id' => $booking->id,
            'item_id' => $booking->item_id,
            'item_name' => $item ? $item->name : null,
            'customer_name' => $booking->customer_name,
            'customer_email' => $booking->customer_email,
            'booking_date' => $booking->booking_date,
            'delivery_type' => $item ? $item->delivery_type : null,
            'preparation_time_hours' => $item ? $item->preparation_time_hours : null,
        ];
    });

    return response()->json($bookings);
}

// Get details of a specific booking
public function show($id)
{
    $booking = Booking::findOrFail($id);
    return response()->json($booking);
}

// Update a booking
public function update(Request $request, $id)
{
    $validated = $request->validate([
        'item_id' => 'sometimes|required|exists:items,id',
        'customer_name' => 'sometimes|required|string|max:255',
        'customer_email' => 'sometimes|required|email',
        'booking_date' => 'sometimes|required|date',
    ]);

    $booking = Booking::findOrFail($id);
    $booking->update($validated);

    return response()->json([
        'message' => 'Booking updated successfully!',
        'data' => $booking
    ]);
}

// Delete a booking
public function destroy($id)
{
    $booking = Booking::findOrFail($id);
    $booking->delete();

    return response()->json([
        'message' => 'Booking deleted successfully!'
    ]);
}


public function store_item(Request $request)
{
    // Validate the request data
    $validator = Validator::make($request->all(),[
        'name' => 'required|string|max:255',
        'delivery_type' => 'required|string|in:advance,instant', // assuming these are the only valid types
        'preparation_time_hours' => 'required|numeric|min:0',
    ]);
    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    $validated = $validator->validated();
    // Create a new item
    $item = Item::create($validated);

    return response()->json([
        'message' => 'Item created successfully!',
        'data' => $item
    ], 201);
}


public function update_item(Request $request, $id)
{
    // Find the item by ID
    $item = Item::findOrFail($id);

    // Validate the request data
    $validator = Validator::make($request->all(),[
        'name' => 'sometimes|required|string|max:255',
        'delivery_type' => 'sometimes|required|string|in:advance,instant',
        'preparation_time_hours' => 'sometimes|required|numeric|min:0',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    $validated = $validator->validated();
    // Update the item
    $item->update($validated);

    return response()->json([
        'message' => 'Item updated successfully!',
        'data' => $item
    ], 200);
}


public function list_item()
{
    // Fetch all items
    $items = Item::all();

    return response()->json($items);
}

public function show_item($id)
{
    // Find the item by ID
    $item = Item::find($id);

    if (!$item) {
        // Return a custom error response if the booking is not found
        return response()->json([
            'status' => false,
            'message' => 'Item not found.'
        ], 404);
    }

    return response()->json($item);
}

public function destroy_item($id)
{
 
    // Find the item by ID
    $item = Item::findOrFail($id);

    // Delete the item
    $item->delete();

    return response()->json([
        'message' => 'Item deleted successfully!',
    ], 200);
}
    
}
