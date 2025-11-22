<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Customer\Entities\CustomerAddress;
use Illuminate\Support\Facades\Log;

class SyncCustomerController extends Controller
{
    public function sync(Request $request)
    {
        try {
            app()->instance('sync::inbound', true);
            Log::debug(json_encode($request->all()));

            $externalId = $request->post('external_customer_id');
            $email = $request->post('email');
            $phone = $request->post('phone');
            $name = trim((string) $request->post('name',''));
            $first = $request->post('first_name');
            $last = $request->post('last_name');

            if (!$first && !$last && $name) {
                $parts = preg_split('/\s+/', $name);
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
