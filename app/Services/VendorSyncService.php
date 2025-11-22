<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\MultiVendor\Entities\SellerAccount;

class VendorSyncService
{
    public function syncVendorByUserId(int $userId): void
    {
        $baseUrl = rtrim(config('sync.base_url', ''), '/');
        if (!$baseUrl) return;

        $user = User::with('SellerAccount')->find($userId);
        if (!$user) return;

        $account = $user->SellerAccount;
        $payload = [
            'vendor_code' => $account?->vendor_id,
            'vendor_pk' => $account?->vendor_id ? null : null,
            'shop_name' => $account?->seller_shop_display_name ?? $user->first_name,
            'email' => $user->email,
            'phone' => $account?->seller_phone ?? $user->username,
            // Send only numeric commission value for POS to store as Online Commission
            'commission_rate' => $account?->commission_rate !== null ? (float) $account->commission_rate : null,
        ];

        try {
            $resp = Http::timeout(10)->acceptJson()->withHeaders([
                'X-Sync-Token'=> config('sync.token', env('SYNC_TOKEN','123456')),
            ])->post($baseUrl.'/api/sync/vendors', $payload)->throw();
            \Log::info('vendorsync.pos.response', [
                'status' => $resp->status(),
                'body' => $resp->body(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Vendor POS sync failed: '.$e->getMessage());
        }
    }
}
