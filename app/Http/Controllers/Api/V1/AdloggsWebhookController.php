<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\CentralLogics\Helpers;
use App\CentralLogics\OrderLogic;

class AdloggsWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Adloggs webhook received', $request->all());

        $payload = $request->all();
        $order = Order::where('adloggs_order_uuid', $payload['order_uuid'] ?? null)
            ->orWhere('id', $payload['partner_order_id'] ?? null)
            ->first();

        if (!$order) {
            Log::warning('Adloggs webhook: order not found', $payload);
            return response()->json(['status' => false, 'message' => 'order not found'], 404);
        }

        $statusMap = [
            2 => 'pending',
            3 => 'assigned',
            11 => 'arrived_at_pickup',
            4 => 'picked_up',
            9 => 'out_for_delivery',
            8 => 'arrived',
            5 => 'delivered',
            6 => 'cancelled',
            13 => 'return_initiated',
            14 => 'rto_delivered'
        ];

        $order_status_id = $payload['order_status_id'] ?? null;
        $newDeliveryStatus = $statusMap[$order_status_id] ?? $order->delivery_status;

        // Update delivery status and agent details
        $order->delivery_status = $newDeliveryStatus;
        $order->delivery_agent_name = data_get($payload, 'deliveryStaffDetails.name');
        $order->delivery_agent_phone = data_get($payload, 'deliveryStaffDetails.phone');

        // Update order status based on delivery status
        if ($newDeliveryStatus == 'picked_up' && $order->order_status == 'confirmed') {
            $order->order_status = 'handover';
            $order->handover = now();
        } elseif ($newDeliveryStatus == 'out_for_delivery' && in_array($order->order_status, ['confirmed', 'handover'])) {
            $order->order_status = 'handover';
            if (!$order->handover) {
                $order->handover = now();
            }
        } elseif ($newDeliveryStatus == 'delivered' && $order->order_status != 'delivered') {
            $order->order_status = 'delivered';
            $order->delivered = now();

            // Update order payment if COD
            if ($order->payment_method == 'cash_on_delivery') {
                OrderLogic::update_unpaid_order_payment(order_id: $order->id, payment_method: $order->payment_method);
            }

            // Increment order counts
            if ($order->restaurant) {
                $order->restaurant->increment('order_count');
            }
        } elseif ($newDeliveryStatus == 'cancelled' && $order->order_status != 'canceled') {
            $order->order_status = 'canceled';
            $order->canceled = now();
            $order->canceled_by = 'third_party_delivery';
            $order->cancellation_reason = data_get($payload, 'order_cancel_description', 'Cancelled by third party delivery service');
        }

        $order->save();

        // Send notification to customer
        try {
            Helpers::send_order_notification($order);
        } catch (\Exception $e) {
            Log::error('Failed to send order notification', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }

        return response()->json(['status' => true, 'message' => 'Webhook processed successfully']);
    }
}
