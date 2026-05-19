<?php
namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class OrderSyncService
{
    public function syncOrderById($orderId)
    {
        $baseUrl = rtrim(config('sync.base_url', ''), '/');
        
        if(!$baseUrl) {
            Log::debug('Order sync skipped: sync.base_url not configured', ['order_id' => $orderId]);
            return;
        }
    }
}