<?php

namespace App\CentralLogics;

use App\Models\BusinessSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Nexmo\Laravel\Facade\Nexmo;
use Twilio\Rest\Client;

class SMS_module
{
    public static function send($receiver, $otp)
    {
        // Debug: Check all SMS configurations
        // $twilio_config = self::get_settings('twilio');
        // $nexmo_config = self::get_settings('nexmo');
        // $twofactor_config = self::get_settings('2factor');
        // $msg91_config = self::get_settings('msg91');
        // $alphanet_config = self::get_settings('alphanet_sms');
        
        // dd([
        //     'receiver' => $receiver,
        //     'otp' => $otp,
        //     'twilio_config' => $twilio_config,
        //     'nexmo_config' => $nexmo_config,
        //     'twofactor_config' => $twofactor_config,
        //     'msg91_config' => $msg91_config,
        //     'alphanet_config' => $alphanet_config,
        //     'all_configs_status' => [
        //         'twilio_active' => isset($twilio_config) && $twilio_config['status'] == 1,
        //         'nexmo_active' => isset($nexmo_config) && $nexmo_config['status'] == 1,
        //         'twofactor_active' => isset($twofactor_config) && $twofactor_config['status'] == 1,
        //         'msg91_active' => isset($msg91_config) && $msg91_config['status'] == 1,
        //         'alphanet_active' => isset($alphanet_config) && $alphanet_config['status'] == 1,
        //     ]
        // ]);

        // Visibility: log which gateways are configured on this env
        $twilioPeek = self::get_settings('twilio');
        $nexmoPeek = self::get_settings('nexmo');
        $twoFactorPeek = self::get_settings('2factor');
        $msg91Peek = self::get_settings('msg91');
        \Log::info('sms_config_status', [
            'twilio' => (bool) ($twilioPeek['status'] ?? 0),
            'nexmo' => (bool) ($nexmoPeek['status'] ?? 0),
            'twofactor' => (bool) ($twoFactorPeek['status'] ?? 0),
            'msg91' => (bool) ($msg91Peek['status'] ?? 0),
        ]);

        $config = $twilioPeek;
        if (isset($config) && ($config['status'] == 1 || $config['status'] == '1')) {
            return self::twilio($receiver, $otp);
        }

        $config = $nexmoPeek;
        if (isset($config) && ($config['status'] == 1 || $config['status'] == '1')) {
            return self::nexmo($receiver, $otp);
        }

        $config = $twoFactorPeek;
        if (isset($config) && ($config['status'] == 1 || $config['status'] == '1')) {
            return self::two_factor($receiver, $otp);
        }

        $config = $msg91Peek;
        if (isset($config) && ($config['status'] == 1 || $config['status'] == '1')) {
            return self::msg_91($receiver, $otp);
        }
        $config = self::get_settings('alphanet_sms');
        if (isset($config) && ($config['status'] == 1 || $config['status'] == '1')) {
            return self::alphanet_sms($receiver, $otp);
        }

        return 'not_found';
    }

    public static function twilio($receiver, $otp): string
    {
        $config = self::get_settings('twilio');
        $response = 'error';
        if (isset($config) && $config['status'] == 1) {
            $message = str_replace("#OTP#", $otp, $config['otp_template']);
            $sid = $config['sid'];
            $token = $config['token'];
            try {
                $twilio = new Client($sid, $token);
                $twilio->messages
                    ->create($receiver, // to
                        array(
                            "messagingServiceSid" => $config['messaging_service_sid'],
                            "body" => $message
                        )
                    );
                $response = 'success';
            } catch (\Exception $exception) {
                $response = 'error';
            }
        }
        return $response;
    }

    public static function nexmo($receiver, $otp): string
    {
        $config = self::get_settings('nexmo');
        $response = 'error';
        if (isset($config) && $config['status'] == 1) {
            $message = str_replace("#OTP#", $otp, $config['otp_template']);
            try {
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, 'https://rest.nexmo.com/sms/json');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, "from=".$config['from']."&text=".$message."&to=".$receiver."&api_key=".$config['api_key']."&api_secret=".$config['api_secret']);

                $headers = array();
                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                $result = curl_exec($ch);
                if (curl_errno($ch)) {
                    echo 'Error:' . curl_error($ch);
                }
                curl_close($ch);
                $response = 'success';
            } catch (\Exception $exception) {
                $response = 'error';
            }
        }
        return $response;
    }

    public static function two_factor($receiver, $otp): string
    {
        $config = self::get_settings('2factor');
        $response = 'error';
        if (isset($config) && $config['status'] == 1) {
            $api_key = $config['api_key'];
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://2factor.in/API/V1/" . $api_key . "/SMS/" . $receiver . "/" . $otp . "",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if (!$err) {
                $response = 'success';
            } else {
                $response = 'error';
            }
        }
        return $response;
    }

    public static function msg_91($receiver, $otp): string
    {
        $config = self::get_settings('msg91');
        // allow both authkey and auth_key
        if (isset($config) && isset($config['authkey']) && !isset($config['auth_key'])) {
            $config['auth_key'] = $config['authkey'];
        }
        
        // Extract OTP number from message
        $otp_number = $otp;
        if (strpos($otp, 'Your OTP is: ') !== false) {
            $otp_number = str_replace('Your OTP is: ', '', $otp);
        }
        
        // Debug: Check MSG91 configuration
        // dd([
        //     'msg91_config' => $config,
        //     'receiver' => $receiver,
        //     'original_otp' => $otp,
        //     'extracted_otp' => $otp_number,
        //     'config_status' => isset($config) ? $config['status'] : 'Config not found',
        //     'template_id' => isset($config) ? $config['template_id'] : 'Not set',
        //     'auth_key' => isset($config) ? substr($config['auth_key'], 0, 10) . '...' : 'Not set'
        // ]);
        
        $response = 'error';
        if (isset($config) && ($config['status'] == 1 || $config['status'] == '1')) {
            $receiver = str_replace("+", "", $receiver);
            $payload = [
                'template_id' => $config['template_id'] ?? '',
                'mobile' => $receiver,
                'otp' => (string) $otp_number,
            ];
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.msg91.com/api/v5/otp',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => array(
                    'content-type: application/json',
                    'authkey: ' . ($config['auth_key'] ?? ''),
                ),
            ));
            $raw = curl_exec($curl);
            $err = curl_error($curl);
            $http = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            if ($err) { \Log::info('msg91_error', ['err'=>$err,'http'=>$http]); return 'error'; }
            $json = json_decode($raw, true);
            if ($http>=200 && $http<300 && is_array($json) && strtolower($json['type'] ?? '')==='success') { return 'success'; }
            \Log::info('msg91_response', ['http'=>$http,'raw'=>$raw,'payload'=>$payload]);
            return 'error';
        }
        return $response;
    }


    public static function alphanet_sms($receiver, $otp): string
    {
        $config = self::get_settings('alphanet_sms');
        $response = 'error';
        if (isset($config) && $config['status'] == 1) {
            $receiver = str_replace("+", "", $receiver);
            $message = str_replace("#OTP#", $otp, $config['otp_template']);
            $api_key = $config['api_key'];
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.sms.net.bd/sendsms',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array('api_key' => $api_key, 'msg' => $message, 'to' => $receiver),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ((int) data_get(json_decode($response,true),'error') === 0) {
                $response = 'success';
            } else {
                $response = 'error';
            }
        }
        return $response;
    }

    
    public static function get_settings($name)
    {
        // Primary: addon_settings
        $config = DB::table('addon_settings')
            ->where('key_name', $name)
            ->where('settings_type', 'sms_config')
            ->first();
        if (isset($config) && !is_null($config->live_values)) {
            $val = json_decode($config->live_values, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $val;
            }
        }
        // Fallback: business_settings
        $bs = BusinessSetting::where('key', $name)->first();
        if ($bs && !empty($bs->value)) {
            $val = json_decode($bs->value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $val;
            }
        }
        return null;
    }
}
