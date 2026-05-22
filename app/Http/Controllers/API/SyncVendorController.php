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
use App\Traits\ImageStore;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class SyncVendorController extends Controller
{
    use ImageStore;
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
                'stripe_account_id' => 'nullable|string',
                'stripeAccountId' => 'nullable|string',
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
            $stripeAccountId = trim((string) ($data['stripe_account_id'] ?? $data['stripeAccountId'] ?? $root->get('stripe_account_id') ?? $root->get('stripeAccountId') ?? ''));
            if ($stripeAccountId === '') {
                $stripeAccountId = null;
            }

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
                if ($stripeAccountId !== null) $user->stripe_account_id = $stripeAccountId;
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
                    'stripe_account_id' => $stripeAccountId,
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
            if (isset($data['commission_rate']) && is_numeric($data['commission_rate'])) {
                $commissionRate = (float) $data['commission_rate'];
            } elseif (isset($data['commission']['rate']) && is_numeric($data['commission']['rate'])) {
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

            // Business Information handling (prefer 'business' block)
            $biz = $root->get('business', []);
            if (is_array($biz)) {
                $hasAnyBiz = collect($biz)->filter(function($v){ return !is_null($v) && $v !== ''; })->isNotEmpty();
                if ($hasAnyBiz) {
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
            }

            // Address block handling (alternative to 'business')
            $addr = $root->get('address', []);
            if (is_array($addr)) {
                $hasAnyAddr = collect($addr)->filter(function($v){ return !is_null($v) && $v !== ''; })->isNotEmpty();
                if ($hasAnyAddr) {
                $countryId = $addr['country_id'] ?? null;
                $stateId = $addr['state_id'] ?? null;
                $cityId = $addr['city_id'] ?? null;

                if (!$countryId && !empty($addr['country'])) {
                    $countryId = optional(\Modules\Setup\Entities\Country::where('name', $addr['country'])->first())->id;
                }
                if (!$stateId && !empty($addr['state']) && $countryId) {
                    $stateId = optional(\Modules\Setup\Entities\State::where('name', $addr['state'])->where('country_id', $countryId)->first())->id
                        ?? optional(\Modules\Setup\Entities\State::where('name', $addr['state'])->first())->id;
                }
                if (!$cityId && !empty($addr['city']) && $stateId) {
                    $cityId = optional(\Modules\Setup\Entities\City::where('name', $addr['city'])->where('state_id', $stateId)->first())->id
                        ?? optional(\Modules\Setup\Entities\City::where('name', $addr['city'])->first())->id;
                }

                \Modules\MultiVendor\Entities\SellerBusinessInformation::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'user_id' => $user->id,
                        'business_address1' => $addr['address'] ?? ($addr['address_1'] ?? null),
                        'business_address2' => $addr['address2'] ?? null,
                        'business_country' => $countryId ?? app('general_setting')->default_country,
                        'business_state' => $stateId ?? app('general_setting')->default_state,
                        'business_city' => $cityId ?? null,
                        'business_postcode' => $addr['postal_code'] ?? ($addr['zip'] ?? null),
                    ]
                );
                }
            }


            // Handle vendor avatar/photo
            try {
                $hasFile = $request->hasFile('avatar') || $request->hasFile('photo');
                $newAvatarPath = null;
                if ($hasFile) {
                    $file = $request->file('avatar') ?: $request->file('photo');
                    // Save avatar using ImageStore helper (resizes and stores properly)
                    $newAvatarPath = $this->saveAvatar($file, 150, 150);
                } else {
                    // Fallback to URL fields
                    $url = (string) ($request->vendor['avatar_url'] ?? $request->input('photo_url') ?? '');
                    Log::debug('avatar_url', ['url'=>$url]);
                    if ($url) {
                        // Prefer downloading and storing locally so Shop serves the image reliably
                        $host = parse_url($url, PHP_URL_HOST) ?: '';
                        $isLocalHost = in_array($host, ['localhost','127.0.0.1']);
                        try {
                            $resp = Http::timeout(10)->get($url);
                            if ($resp->successful()) {
                                $filename = basename(parse_url($url, PHP_URL_PATH) ?: ('avatar_' . uniqid() . '.jpg'));
                                $tempPath = 'tmp-sync/' . uniqid('av_') . '_' . $filename;
                                Storage::disk('public')->put($tempPath, $resp->body());
                                $abs = storage_path('app/public/' . $tempPath);
                                $uploaded = new UploadedFile($abs, $filename, @mime_content_type($abs) ?: 'image/jpeg', null, true);
                                $newAvatarPath = $this->saveAvatar($uploaded, 150, 150);
                                Storage::disk('public')->delete($tempPath);
                            } else if (!$isLocalHost) {
                                // fallback to keeping remote URL only if not localhost
                                $newAvatarPath = $url;
                            }
                        } catch (\Throwable $e) {
                            if (!$isLocalHost) {
                                $newAvatarPath = $url; // fallback to remote only if not localhost
                            }
                        }
                    }
                }
                if ($newAvatarPath) {
                    if ($user->avatar) { $this->deleteImage($user->avatar); }
                    $user->avatar = $newAvatarPath;
                    $user->save();
                }
            } catch (\Throwable $e) {
                Log::warning('sync.vendor.avatar_failed', ['error'=>$e->getMessage()]);
            }

            // Ensure seller navbar/menu entries are created for this user
            try { app(SellerSidebarService::class)->setupForSeller($user); } catch (\Throwable $e) { Log::warning('seller.sidebar.setup.failed', ['error'=>$e->getMessage()]); }

            Log::info('sync.vendor.shop.inbound.ok', [
                'action' => $isNew ? 'created' : 'updated',
                'user_id' => $user->id,
                'vendor_code' => $vendorCode,
                'email' => $user->email,
                'stripe_account_id' => $user->stripe_account_id,
            ]);

            return ['success'=>true,'action'=>$isNew?'created':'updated','id'=>$user->id];
        } catch (\Throwable $e) {
            Log::warning('sync.vendor.shop.inbound.failed', ['error'=>$e->getMessage()]);
            return response()->json(['success'=>false,'message'=>$e->getMessage()], 500);
        }
    }
}
