<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Configuration Comparison:\n";
echo "========================\n\n";

// 1. Environment Comparison
echo "1. Environment Variables:\n";
echo "--------------------------\n";
echo "APP_ENV: " . env('APP_ENV') . "\n";
echo "APP_DEBUG: " . (env('APP_DEBUG') ? 'true' : 'false') . "\n";
echo "APP_MODE: " . env('APP_MODE') . "\n";
echo "APP_URL: " . env('APP_URL') . "\n";

echo "\n";

// 2. MSG91 Configuration
echo "2. MSG91 Configuration:\n";
echo "------------------------\n";

$msg91_config = DB::table('addon_settings')
    ->where('key_name', 'msg91')
    ->where('settings_type', 'sms_config')
    ->first();

if ($msg91_config) {
    echo "✅ MSG91 Config Found\n";
    echo "Status: " . ($msg91_config->is_active ? 'Active' : 'Inactive') . "\n";
    echo "Mode: " . $msg91_config->mode . "\n";
    
    $config_data = json_decode($msg91_config->live_values, true);
    if ($config_data) {
        echo "Gateway: " . ($config_data['gateway'] ?? 'N/A') . "\n";
        echo "Status: " . ($config_data['status'] ?? 'N/A') . "\n";
        echo "Template ID: " . ($config_data['template_id'] ?? 'N/A') . "\n";
        echo "Auth Key: " . (isset($config_data['auth_key']) ? substr($config_data['auth_key'], 0, 10) . '...' : 'N/A') . "\n";
        
        // Check if config is same as localhost
        $expected_template = "6745cf6dd6fc05732a359d93";
        $expected_auth_key_prefix = "428211AJLl";
        
        if ($config_data['template_id'] == $expected_template) {
            echo "✅ Template ID matches localhost\n";
        } else {
            echo "❌ Template ID differs from localhost\n";
            echo "Expected: " . $expected_template . "\n";
            echo "Found: " . $config_data['template_id'] . "\n";
        }
        
        if (strpos($config_data['auth_key'], $expected_auth_key_prefix) === 0) {
            echo "✅ Auth Key matches localhost\n";
        } else {
            echo "❌ Auth Key differs from localhost\n";
        }
    }
} else {
    echo "❌ MSG91 Config NOT Found!\n";
}

echo "\n";

// 3. All SMS Configurations
echo "3. All SMS Configurations:\n";
echo "--------------------------\n";

$all_configs = DB::table('addon_settings')
    ->where('settings_type', 'sms_config')
    ->get();

foreach ($all_configs as $config) {
    echo "Gateway: " . $config->key_name . " | Active: " . ($config->is_active ? 'Yes' : 'No') . "\n";
}

echo "\n";

// 4. Database Data Check
echo "4. Database Data Check:\n";
echo "------------------------\n";

// Check recent users
$recent_users = DB::table('users')->orderBy('id', 'desc')->limit(3)->get();
echo "Recent Users:\n";
foreach ($recent_users as $user) {
    echo "ID: " . $user->id . " | Phone: " . $user->phone . " | Verified: " . ($user->is_phone_verified ? 'Yes' : 'No') . "\n";
}

echo "\n";

// Check recent OTPs
$recent_otps = DB::table('phone_verifications')->orderBy('created_at', 'desc')->limit(3)->get();
echo "Recent OTPs:\n";
foreach ($recent_otps as $otp) {
    echo "Phone: " . $otp->phone . " | OTP: " . $otp->token . " | Created: " . $otp->created_at . "\n";
}

echo "\n";

// 5. Server Information
echo "5. Server Information:\n";
echo "-----------------------\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "Server Name: " . ($_SERVER['SERVER_NAME'] ?? 'Unknown') . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "\n";
echo "Current Time: " . date('Y-m-d H:i:s') . "\n";

echo "\n";

echo "Configuration Comparison Complete!\n";
echo "==================================\n";
echo "If MSG91 config differs from localhost, that's likely the issue!\n";
?>
