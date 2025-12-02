<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
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

        // Prepare form fields (multipart)
        $fullName = trim(($user->first_name.' '.$user->last_name) ?: ($user->first_name ?? ''));
        $usernameLike = (string) ($user->username ?? $user->email ?? $user->phone ?? '');
        $fields = [
            'external_customer_id' => (string) $userId,
            'first_name' => (string) ($user->first_name ?? ''),
            'last_name' => (string) ($user->last_name ?? ''),
            // POS expects `name` to be username
            'name' => $usernameLike,
            // Provide explicit full name as well
            'full_name' => $fullName,
            'email' => (string) ($user->email ?? ''),
            'phone' => (string) ($user->phone ?? ''),
        ];
        $billing = $this->mapAddress($user->customerBillingAddress);
        $shipping = $this->mapAddress($user->customerShippingAddress);
        foreach ($billing as $k=>$v) { $fields["address[billing][$k]"] = (string) ($v ?? ''); }
        foreach ($shipping as $k=>$v) { $fields["address[shipping][$k]"] = (string) ($v ?? ''); }

        try {
            $req = Http::timeout(15)
                ->asMultipart()
                ->withHeaders(['X-Sync-Token'=> config('sync.token', env('SYNC_TOKEN','123456'))]);

            // Attach avatar as file if available (support both avatar and photo fields)
            $avatarPath = $user->avatar;
            $attached = false;
            if ($avatarPath) {
                $publicUrl = $avatarPath;
                if (!Str::startsWith($publicUrl, ['http://','https://'])) {
                    $publicUrl = url('/'.ltrim($publicUrl, '/'));
                }
                // Always include URL hints for POS
                $fields['avatar_url'] = $publicUrl;
                $fields['photo_url'] = $publicUrl;
            }
            if ($avatarPath) {
                try {
                    $filename = basename(parse_url($avatarPath, PHP_URL_PATH) ?: 'avatar.jpg');
                    if (Str::startsWith($avatarPath, ['http://','https://'])) {
                        $bin = @file_get_contents($avatarPath);
                        if ($bin !== false) {
                            $req = $req->attach('avatar', $bin, $filename)
                                       ->attach('photo', $bin, $filename);
                            $attached = true;
                        }
                    } else {
                        $full = public_path(trim($avatarPath,'/'));
                        if (!is_file($full)) {
                            // try asset_path helper if available
                            if (function_exists('asset_path')) { $full = asset_path($avatarPath); }
                        }
                        if (is_file($full)) {
                            $bin = file_get_contents($full);
                            $req = $req->attach('avatar', $bin, $filename)
                                       ->attach('photo', $bin, $filename);
                            $attached = true;
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('Customer POS sync avatar attach failed: '.$e->getMessage());
                }
            }
            // (URL is already added above)

            $resp = $req->post($baseUrl.'/api/sync/customers', $fields)->throw();
            \Log::info('customersync.pos.response', [ 'status' => $resp->status() ]);
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
