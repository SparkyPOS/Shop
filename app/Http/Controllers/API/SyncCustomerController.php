<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Customer\Entities\CustomerAddress;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Traits\ImageStore;
use Illuminate\Support\Str;

class SyncCustomerController extends Controller
{
    use ImageStore;
    public function show(Request $request, int $id)
    {
        // simple token guard
        $token = $request->header('X-Sync-Token');
        abort_unless($token && hash_equals($token, config('sync.token', env('SYNC_TOKEN',''))), 403);

        $user = User::with(['customerBillingAddress','customerShippingAddress'])->findOrFail($id);
        $avatar = $user->avatar;
        if ($avatar && !Str::startsWith($avatar, ['http://','https://'])) {
            $avatar = url('/'.ltrim($avatar, '/'));
        }
        $fullName = trim(($user->first_name.' '.$user->last_name) ?: ($user->first_name ?? ''));
        return [
            'external_customer_id' => (string) $user->id,
            'first_name' => (string) ($user->first_name ?? ''),
            'last_name' => (string) ($user->last_name ?? ''),
            'name' => (string) ($user->username ?? $user->email ?? $user->phone ?? ''), // POS username
            'full_name' => $fullName,
            'email' => (string) ($user->email ?? ''),
            'phone' => (string) ($user->phone ?? ''),
            'avatar_url' => $avatar,
            'address' => [
                'billing' => [
                    'address_1' => $user->customerBillingAddress->address ?? null,
                    'postal_code' => $user->customerBillingAddress->postal_code ?? null,
                    'phone' => $user->customerBillingAddress->phone ?? null,
                    'city' => $user->customerBillingAddress->city ?? null,
                    'state' => $user->customerBillingAddress->state ?? null,
                    'country' => $user->customerBillingAddress->country ?? null,
                ],
                'shipping' => [
                    'address_1' => $user->customerShippingAddress->address ?? null,
                    'postal_code' => $user->customerShippingAddress->postal_code ?? null,
                    'phone' => $user->customerShippingAddress->phone ?? null,
                    'city' => $user->customerShippingAddress->city ?? null,
                    'state' => $user->customerShippingAddress->state ?? null,
                    'country' => $user->customerShippingAddress->country ?? null,
                ],
            ],
        ];
    }
    public function sync(Request $request)
    {
        try {
            app()->instance('sync::inbound', true);
            Log::debug(json_encode($request->all()));

            $externalId = $request->post('external_customer_id');
            $email = $request->post('email');
            $phone = $request->post('phone');
            // On POS side, `name` is considered the username; `full_name` holds First Last
            $name = trim((string) $request->post('name',''));
            $fullName = trim((string) $request->post('full_name',''));
            $first = $request->post('first_name');
            $last = $request->post('last_name');

            // Prefer explicit full_name for splitting into first/last when first/last missing
            $nameForSplit = $fullName ?: $name;
            if (!$first && !$last && $nameForSplit) {
                $parts = preg_split('/\s+/', $nameForSplit);
                $last = array_pop($parts);
                $first = trim(implode(' ', $parts)) ?: $last;
            }

            $user = null;
            if ($externalId) {
                $user = User::where('external_customer_id', $externalId)->first();
            }
            if (!$user && $email) {
                $user = User::where('email', $email)->first();
            }

            $payload = [
                'first_name' => $first ?: 'Customer',
                'last_name' => $last ?: '',
                'email' => $email,
                'phone' => $phone,
                'is_active' => 1,
            ];
            if ($name) { // map POS `name` to our `username`
                $payload['username'] = $name;
            }

            if ($user) {
                $user->fill(array_filter($payload, fn($v)=>!is_null($v)));
                $user->save();
            } else {
                // Set a default password and role later if needed
                $payload['password'] = bcrypt(str()->random(12));
                $payload['role_id'] = $payload['role_id'] ?? 4; // assume 4 = customer
                $user = User::create($payload);
            }

            if ($externalId) {
                $user->external_customer_id = $externalId;
                $user->save();
            }

            // Handle avatar sync (multipart avatar file preferred)
            try {
                $hasUploadedFile = $request->hasFile('avatar') || $request->hasFile('photo');
                $newAvatarPath = null;

                if ($hasUploadedFile) {
                    $file = $request->file('avatar') ?: $request->file('photo');
                    $this->deleteImage($user->avatar);
                    $newAvatarPath = $this->saveImage($file, 150, 150);
                }

                // Fallback: accept avatar_url when file upload isn't provided
                if (!$hasUploadedFile) {
                    $url = (string) ($request->input('avatar_url') ?? $request->input('photo_url') ?? $request->input('image_url') ?? '');
                    if ($url !== '') {
                        $this->deleteImage($user->avatar);
                        $newAvatarPath = $url;
                    }
                }

                if ($newAvatarPath) {
                    $user->avatar = $newAvatarPath;
                    $user->save();
                }
            } catch (\Throwable $e) {
                Log::warning('sync.customer.avatar_failed', ['error'=>$e->getMessage()]);
            }

            // Addresses
            $addr = (array) $request->post('address', []);
            foreach (['billing','shipping'] as $type) {
                if (!empty($addr[$type]) && is_array($addr[$type])) {
                    $src = $addr[$type];
                    $address = CustomerAddress::firstOrNew([
                        'customer_id' => $user->id,
                        'is_'.$type.'_default' => 1,
                    ]);
                    $address->customer_id = $user->id;
                    $address->address = $src['address_1'] ?? ($src['address'] ?? '');
                    $address->postal_code = $src['postal_code'] ?? ($src['pobox'] ?? null);
                    $address->phone = $src['phone'] ?? ($phone ?? null);
                    $address->city = $src['city'] ?? null;
                    $address->state = $src['state'] ?? null;
                    $address->country = $src['country'] ?? null;
                    $address->is_shipping_default = $type === 'shipping' ? 1 : ($address->is_shipping_default ?? 0);
                    $address->is_billing_default = $type === 'billing' ? 1 : ($address->is_billing_default ?? 0);
                    $address->save();
                }
            }

            Log::info('sync.customer.shop.inbound', [
                'action' => ($user?->wasRecentlyCreated ?? false) ? 'created' : 'updated',
                'id' => $user->id ?? null,
                'external_customer_id' => $externalId,
                'email' => $email,
            ]);
        } catch (\Throwable $th) {
            return ['success'=>false,'message'=>$th->getMessage()];
        }

        return ['success'=>true,'action'=>(($user?->wasRecentlyCreated ?? false) ? 'created' : 'updated'), 'id'=>$user->id ?? null];
    }
}
