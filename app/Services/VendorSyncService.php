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
        $biz = optional(\Modules\MultiVendor\Entities\SellerBusinessInformation::where('user_id', $user->id)->first());
        $payload = [
            'vendor_code' => $account?->vendor_id,
            'vendor_pk' => $account?->vendor_id ? null : null,
            'shop_name' => $account?->seller_shop_display_name ?? $user->first_name,
            'email' => $user->email,
            'phone' => $account?->seller_phone ?? $user->username,
            // Send only numeric commission value for POS to store as Online Commission
            'commission_rate' => $account?->commission_rate !== null ? (float) $account->commission_rate : null,
        ];

        // Include address and business blocks to align with POS schema
        $payload['address'] = [
            'address' => $biz?->business_address1,
            'address_1' => $biz?->business_address1,
            'address2' => $biz?->business_address2,
            'city' => $biz?->business_city,
            'state' => $biz?->business_state,
            'country' => $biz?->business_country,
            'postal_code' => $biz?->business_postcode,
        ];
        $payload['business'] = [
            'owner_name' => $biz?->business_owner_name,
            'address1' => $biz?->business_address1,
            'address2' => $biz?->business_address2,
            'city' => $biz?->business_city,
            'state' => $biz?->business_state,
            'country' => $biz?->business_country,
            'postal_code' => $biz?->business_postcode,
            'person_incharge_name' => $biz?->business_person_in_charge_name,
            'registration_number' => $biz?->business_registration_number,
            'seller_tin' => $biz?->seller_tin,
        ];

        // Include vendor avatar/photo as absolute URL if available
        try {
            $avatar = $user->avatar ?? null;
            if ($avatar) {
                if (!\Illuminate\Support\Str::startsWith($avatar, ['http://', 'https://'])) {
                    // try convert to absolute URL
                    $avatar = url('/' . ltrim(function_exists('asset_path') ? asset_path($avatar) : $avatar, '/'));
                }
                $payload['avatar_url'] = $avatar;
                $payload['photo_url'] = $avatar;
            }
        } catch (\Throwable $e) { /* ignore avatar issues */ }

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
