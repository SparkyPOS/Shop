<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Modules\MultiVendor\Entities\SellerAccount;
use Modules\RolePermission\Entities\Role;
use App\Services\SellerSidebarService;

class SyncVendorController extends Controller
{
    public function sync(Request $request)
    {
        try {
            // Mark inbound to prevent outbound loops
            app()->instance('sync::inbound', true);
            Log::debug('sync.vendor.shop.inbound: '.json_encode($request->all()));

            $root = $request->has('vendor') && is_array($request->input('vendor'))
                ? collect($request->input('vendor'))
                : collect($request->all());

            $data = validator($root->all(), [
                'vendor_pk' => 'nullable',
                'vendor_code' => 'nullable|string',
                'shop_name' => 'nullable|string',
                'email' => 'nullable|email',
                'phone' => 'nullable|string',
                'password' => 'nullable|string|min:6',
                'address' => 'array',
                'business' => 'array',
                'business.owner_name' => 'nullable|string',
                'business.address1' => 'nullable|string',
                'business.address2' => 'nullable|string',
                'business.country_id' => 'nullable|integer',
                'business.state_id' => 'nullable|integer',
                'business.city_id' => 'nullable|integer',
                'business.country' => 'nullable|string',
                'business.state' => 'nullable|string',
                'business.city' => 'nullable|string',
                'business.postal_code' => 'nullable|string',
                'business.person_incharge_name' => 'nullable|string',
                'business.registration_number' => 'nullable|string',
                'business.seller_tin' => 'nullable|string',
                'commission' => '',
                'commission.type' => 'nullable|string',
                'commission.rate' => 'nullable|numeric',
                'commission_rate' => 'nullable|numeric',
            ])->validate();

            $vendorCode = $data['vendor_code'] ?? null;
            $email = $data['email'] ?? null;
            $shopName = $data['shop_name'] ?? null;
            $phone = $data['phone'] ?? null;
            $password = $data['password'] ?? null;

            $sellerAccount = null;
            if ($vendorCode) {
                $sellerAccount = SellerAccount::where('vendor_id', $vendorCode)->first();
            }

            $user = null;
            if ($sellerAccount) {
                $user = $sellerAccount->user;
            }
            if (!$user && $email) {
                $user = User::where('email', $email)->first();
            }

            $role = Role::where('type', 'seller')->first();
            $isNew = false;
            if ($user) {
                $user->first_name = $shopName ?: ($user->first_name ?: 'Seller');
                if ($email) $user->email = $email;
                if ($phone) $user->username = $phone; // keep phone in username as system expects
                if ($password) $user->password = Hash::make($password);
                $user->role_id = $role?->id ?: $user->role_id;
                $user->is_active = 1;
                $user->save();
            } else {
                $user = User::create([
                    'first_name' => $shopName ?: 'Seller',
                    'email' => $email,
                    'email_verified_at' => now(),
                    'is_verified' => 1,
                    'is_active' => 1,
                    'role_id' => $role?->id,
                    'seller_status' => 'approve',
                    'username' => $phone ?: ($email ?: 'seller'.time()),
                    'verify_code' => sha1(time()),
                    'password' => Hash::make($password ?: str()->random(10)),
                    'currency_id' => app('general_setting')->currency,
                    'lang_code' => app('general_setting')->language_code,
                    'currency_code' => app('general_setting')->currency_code,
                ]);
                $isNew = true;
            }

            if ($sellerAccount) {
                $sellerAccount->seller_shop_display_name = $shopName ?: $sellerAccount->seller_shop_display_name;
                $sellerAccount->seller_phone = $phone ?: $sellerAccount->seller_phone;
                if ($vendorCode) $sellerAccount->vendor_id = $vendorCode;
                $sellerAccount->save();
            } else {
                SellerAccount::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'user_id' => $user->id,
                        'seller_id' => 'BDEXCJ' . rand(99999, 10000000),
                        'seller_shop_display_name' => $shopName ?: ($user->first_name ?: 'Seller'),
                        'seller_commission_id' => 1,
                        'commission_rate' => 0,
                        'subscription_type' => 'monthly',
                        'vendor_id' => $vendorCode,
                        'seller_phone' => $phone,
                    ]
                );
            }

            // Commission handling (flat by default)
            $commissionRate = null;
            if (!empty($data['commission_rate'])) {
                $commissionRate = (float) $data['commission_rate'];
            } elseif (!empty($data['commission']['rate'])) {
                $commissionRate = (float) $data['commission']['rate'];
            }
            if ($commissionRate !== null) {
                $acc = $sellerAccount ?: SellerAccount::where('user_id', $user->id)->first();
                if ($acc) {
                    $acc->seller_commission_id = 1; // flat
                    $acc->commission_rate = $commissionRate;
                    $acc->save();
                }
            }

            // Business Information handling
            $biz = $root->get('business', []);
            if (is_array($biz) && !empty($biz)) {
                $countryId = $biz['country_id'] ?? null;
                $stateId = $biz['state_id'] ?? null;
                $cityId = $biz['city_id'] ?? null;

                // Resolve by name if ids are not provided
                if (!$countryId && !empty($biz['country'])) {
                    $countryId = optional(\Modules\Setup\Entities\Country::where('name', $biz['country'])->first())->id;
                }
                if (!$stateId && !empty($biz['state']) && $countryId) {
                    $stateId = optional(\Modules\Setup\Entities\State::where('name', $biz['state'])->where('country_id', $countryId)->first())->id
                        ?? optional(\Modules\Setup\Entities\State::where('name', $biz['state'])->first())->id;
                }
                if (!$cityId && !empty($biz['city']) && $stateId) {
                    $cityId = optional(\Modules\Setup\Entities\City::where('name', $biz['city'])->where('state_id', $stateId)->first())->id
                        ?? optional(\Modules\Setup\Entities\City::where('name', $biz['city'])->first())->id;
                }

                \Modules\MultiVendor\Entities\SellerBusinessInformation::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'user_id' => $user->id,
                        'business_owner_name' => $biz['owner_name'] ?? null,
                        'business_address1' => $biz['address1'] ?? null,
                        'business_address2' => $biz['address2'] ?? null,
                        'business_country' => $countryId ?? app('general_setting')->default_country,
                        'business_state' => $stateId ?? app('general_setting')->default_state,
                        'business_city' => $cityId ?? null,
                        'business_postcode' => $biz['postal_code'] ?? null,
                        'business_person_in_charge_name' => $biz['person_incharge_name'] ?? null,
                        'business_registration_number' => $biz['registration_number'] ?? null,
                        'business_seller_tin' => $biz['seller_tin'] ?? null,
                    ]
                );
            }

            // Ensure seller navbar/menu entries are created for this user
            try { app(SellerSidebarService::class)->setupForSeller($user); } catch (\Throwable $e) { Log::warning('seller.sidebar.setup.failed', ['error'=>$e->getMessage()]); }

            Log::info('sync.vendor.shop.inbound.ok', [
                'action' => $isNew ? 'created' : 'updated',
                'user_id' => $user->id,
                'vendor_code' => $vendorCode,
                'email' => $user->email,
            ]);

            return ['success'=>true,'action'=>$isNew?'created':'updated','id'=>$user->id];
        } catch (\Throwable $e) {
            Log::warning('sync.vendor.shop.inbound.failed', ['error'=>$e->getMessage()]);
            return response()->json(['success'=>false,'message'=>$e->getMessage()], 500);
        }
    }
}
