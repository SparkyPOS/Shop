<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Customer\Entities\CustomerAddress;

class CustomerSyncService
{
    public function syncCustomerById(int $userId): void
    {
        $baseUrl = rtrim(config('sync.base_url', ''), '/');
        if (!$baseUrl) return;

        $user = User::with(['customerBillingAddress','customerShippingAddress'])->find($userId);
        if (!$user) return;

        $payload = [
            'external_customer_id' => $userId,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'name' => trim($user->first_name.' '.$user->last_name),
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => [
                'billing' => $this->mapAddress($user->customerBillingAddress),
                'shipping' => $this->mapAddress($user->customerShippingAddress),
            ],
        ];

        try {
            $resp = Http::timeout(10)->acceptJson()->withHeaders([
                'X-Sync-Token'=> config('sync.token', env('SYNC_TOKEN','123456')),
            ])->post($baseUrl.'/api/sync/customers', $payload)->throw();
            \Log::info('customersync.pos.response', [
                'status' => $resp->status(),
                'body' => $resp->body(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Customer POS sync failed: '.$e->getMessage());
        }
    }

    private function mapAddress(?CustomerAddress $addr): array
    {
        if (!$addr) return [];
        return [
            'address_1' => $addr->address,
            'postal_code' => $addr->postal_code,
            'phone' => $addr->phone,
            'city' => $addr->city,
            'state' => $addr->state,
            'country' => $addr->country,
        ];
    }
}
