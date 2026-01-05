<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\CentralLogics\Helpers;

class AdloggsService
{
    protected $apiKey;
    protected $baseUrl;
    protected $partnerId;

    public function __construct()
    {
        // Get configuration from Business Settings
        $adloggsConfig = Helpers::get_business_settings('adloggs_config') ?? [];

        $this->apiKey = $adloggsConfig['api_key'] ?? env('ADLOGGS_API_KEY');
        $this->baseUrl = $adloggsConfig['base_url'] ?? env('ADLOGGS_BASE_URL', 'https://dev.adloggs.com');
        $this->partnerId = $adloggsConfig['partner_merchant_id'] ?? env('ADLOGGS_PARTNER_ID', 5421);
    }

    protected function headers()
    {
        return [
            'x-api-key' => $this->apiKey,
            'Accept' => 'application/json'
        ];
    }

    public function checkAvailability(array $pickup, array $delivery, string $paymentType = 'Online')
    {
        try {
            $payload = [
                'partner_merchant_id' => $this->partnerId,
                'partner_order_id' => 'ORD' . time() . rand(100,999),
                'pickup_lat' => (float) $pickup['lat'],
                'pickup_long' => (float) $pickup['lng'],
                'pickup_pincode' => $pickup['pincode'] ?? '',
                'delivery_lat' => (float) $delivery['lat'],
                'delivery_long' => (float) $delivery['lng'],
                'delivery_pincode' => $delivery['pincode'] ?? '',
                'utc_offset' => 330,
                'payment_type' => $paymentType
            ];

            $resp = Http::withHeaders($this->headers())->post("{$this->baseUrl}/aa/oporder/v1.2/service/availability", $payload);
            return $resp->json();
        } catch (\Exception $e) {
            Log::error('Adloggs availability error', ['error' => $e->getMessage()]);
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function createOrder(array $payload)
    {
        try {
            $resp = Http::withHeaders($this->headers())->post("{$this->baseUrl}/aa/oporder/v2/create", $payload);
            return $resp->json();
        } catch (\Exception $e) {
            Log::error('Adloggs createOrder error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getOrderStatus(string $orderUuid)
    {
        try {
            $resp = Http::withHeaders($this->headers())->post("{$this->baseUrl}/aa/oporder/getcurrentstatus", ['order_uuid' => $orderUuid]);
            return $resp->json();
        } catch (\Exception $e) {
            Log::error('Adloggs getOrderStatus error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function cancelOrder(string $orderUuid, string $reason = 'Cancelled by merchant')
    {
        try {
            $resp = Http::withHeaders($this->headers())->post("{$this->baseUrl}/aa/oporder/v1.2/cancel", [
                'order_uuid' => $orderUuid,
                'order_cancel_description' => $reason
            ]);
            return $resp->json();
        } catch (\Exception $e) {
            Log::error('Adloggs cancelOrder error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function walletBalance()
    {
        try {
            $resp = Http::withHeaders($this->headers())->post("{$this->baseUrl}/aa/oporder/wallet/balance", [
                'partner_merchant_id' => $this->partnerId
            ]);
            return $resp->json();
        } catch (\Exception $e) {
            Log::error('Adloggs walletBalance error', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
