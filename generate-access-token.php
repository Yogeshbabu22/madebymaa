<?php
require __DIR__ . '/vendor/autoload.php';

use Google\Auth\Credentials\ServiceAccountCredentials;

// Service account JSON का absolute path
$jsonPath = __DIR__ . '/storage/app/fcm-service-account.json';

if (!file_exists($jsonPath)) {
    die("Service account file not found at $jsonPath\n");
}

$scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
$credentials = new ServiceAccountCredentials($scopes, $jsonPath);

// Access token generate
$accessToken = $credentials->fetchAuthToken()['access_token'] ?? null;

if (!$accessToken) {
    die("Failed to generate access token\n");
}

// Save access token to file
file_put_contents(__DIR__ . '/storage/app/google-access-token.txt', $accessToken);
echo "Access token generated & saved successfully!\n";
