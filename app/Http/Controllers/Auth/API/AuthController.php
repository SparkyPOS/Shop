<?php

namespace App\Http\Controllers\Auth\API;

use App\Models\User;
use App\Models\Order;
use App\Traits\Notification;
use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Models\SocialProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Modules\Wallet\Entities\WalletBalance;
use Modules\Customer\Entities\CustomerAddress;
use Modules\GeneralSetting\Entities\EmailTemplateType;
use Modules\OrderManage\Entities\CustomerNotification;
use Modules\Affiliate\Repositories\AffiliateRepository;
use Modules\GeneralSetting\Entities\NotificationSetting;
use Modules\GeneralSetting\Entities\UserNotificationSetting;
use Modules\GeneralSetting\Services\NotificationSettingService;
use App\Services\CustomerSyncService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\RolePermission\Entities\Role;

/**
 * @group User Management
 *
 * APIs for User Management
 */

class AuthController extends Controller
{
    use Notification;
    protected $authService, $notificationSettingService;
    public function __construct(NotificationSettingService $notificationSettingService, AuthService $authService)
    {
        $this->notificationSettingService = $notificationSettingService;
        $this->authService = $authService;
    }

    /**
     * Login
     * @bodyParam email string required email or phone from user
     * @bodyParam password string required password
     *
     * @response{
     * "user": {
     *       "id": 8,
     *       "first_name": "Hafijur SPN",
     *       "last_name": "21",
     *       "username": null,
     *       "photo": null,
     *       "role_id": 4,
     *       "mobile_verified_at": null,
     *       "email": "spn21@spondonit.com",
     *       "is_verified": 0,
     *       "verify_code": null,
     *       "email_verified_at": null,
     *       "notification_preference": "mail",
     *       "is_active": 1,
     *       "avatar": null,
     *       "phone": null,
     *       "date_of_birth": null,
     *       "description": null,
     *       "secret_login": 0,
     *       "secret_logged_in_by_user": null,
     *       "created_at": "2021-06-09T11:56:56.000000Z",
     *       "updated_at": "2021-06-09T12:29:05.000000Z",
     *       "role": {
     *           "id": 4,
     *           "name": "Customer",
     *           "type": "customer",
     *           "details": null,
     *           "created_at": "2021-05-29T05:26:46.000000Z",
     *           "updated_at": null
     *       }
     *   },
     *   "token": "5|Y6PwOvfBo0W4k04SWlZV8naNLNdmXVOUnRQt4KZg",
     *   "message" : "Successfully logged In"
     * }
     */
    public function ssoLoginView(Request $request) {
        $token = $request->get('t', null);
        return view('auth.sso')->with(array('token'=> $token, 'redirectTo'=>null));
    }

     public function ssoLogin(Request $request) {
        $token = $request->get('token', null);

        $mainapp = env('MAIN_APP_URL', 'https://app.sparkypos.com');
        if (empty($token)) {
            return redirect($mainapp.'/sign-in');
        }
        
        try {
            $encryptedToken = base64_decode($token, true);

            if ($encryptedToken === false) {
                return redirect($mainapp.'/sign-in');
            }

            $parsedToken = Crypt::decryptString($encryptedToken);
            [$userId, $date, $redirectTo, $ssoMeta] = $this->extractSsoPayload($parsedToken);
            $issuedAt = strtotime((string) ($date ?? ''));
            $timeDifference = $issuedAt ? time() - $issuedAt : null;

            if ($timeDifference !== null && $timeDifference >= 0 && $timeDifference < 600) {
                $user = null;
                if (Schema::hasColumn('users', 'app_user_id')) {
                    $user = User::where('app_user_id', $userId)->first();
                }
                if (!$user && Schema::hasColumn('users', 'pos_user_id')) {
                    $user = User::where('pos_user_id', $userId)->first();
                }
                if (!$user && Schema::hasColumn('users', 'external_customer_id')) {
                    $user = User::where('external_customer_id', (string) $userId)->first();
                }
                if (!$user && !empty($ssoMeta['email'])) {
                    $user = User::where('email', (string) $ssoMeta['email'])->first();
                }
                if (!$user) {
                    $user = $this->provisionShopUserFromSso($userId, $ssoMeta);
                } else {
                    $this->hydrateShopUserSsoIdentifiers($user, $userId);
                }
                if ($user instanceof User) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    Auth::loginUsingId($user->id);
                    $request->session()->regenerate();

                    $redirectTarget = $this->appendQueryParam($redirectTo ?: url('/'), 'success', 'true');

                    return view('auth.sso')->with([
                        'redirectTo' => $redirectTarget,
                        'token' => null,
                    ]);
                }
            }
            
            return redirect($mainapp.'/sign-in');

        } catch (\Throwable $e) {
            return redirect($mainapp.'/sign-in');
        }
    }

    private function extractSsoPayload(string $decryptedToken): array
    {
        $payload = json_decode($decryptedToken, true);
        if (is_array($payload)) {
            $userId = (string) ($payload['appUserId'] ?? $payload['userId'] ?? '');
            $issuedAt = (string) ($payload['issuedAt'] ?? '');
            $redirectTo = $payload['redirectTo'] ?? null;

            return [$userId, $issuedAt, $redirectTo, $payload];
        }

        [$userId, $date, $redirectTo] = array_pad(explode('|', $decryptedToken, 3), 3, null);

        return [$userId, $date, $redirectTo, []];
    }

    private function hydrateShopUserSsoIdentifiers(User $user, string $sourceUserId): void
    {
        $shouldSave = false;

        if (Schema::hasColumn('users', 'app_user_id') && (empty($user->app_user_id) || (string) $user->app_user_id !== (string) $sourceUserId)) {
            $user->app_user_id = (int) $sourceUserId;
            $shouldSave = true;
        }
        if (Schema::hasColumn('users', 'pos_user_id') && (empty($user->pos_user_id) || (string) $user->pos_user_id !== (string) $sourceUserId)) {
            $user->pos_user_id = (int) $sourceUserId;
            $shouldSave = true;
        }
        if (Schema::hasColumn('users', 'external_customer_id') && (empty($user->external_customer_id) || (string) $user->external_customer_id !== (string) $sourceUserId)) {
            $user->external_customer_id = (int) $sourceUserId;
            $shouldSave = true;
        }

        if ($shouldSave) {
            $user->save();
        }
    }

    private function provisionShopUserFromSso(string $sourceUserId, array $ssoMeta): ?User
    {
        if (trim($sourceUserId) === '') {
            return null;
        }

        $email = isset($ssoMeta['email']) ? trim((string) $ssoMeta['email']) : null;
        if ($email === '') {
            $email = null;
        }
        if ($email && User::where('email', $email)->exists()) {
            $email = null;
        }

        $firstName = trim((string) ($ssoMeta['firstName'] ?? $ssoMeta['first_name'] ?? ''));
        $lastName = trim((string) ($ssoMeta['lastName'] ?? $ssoMeta['last_name'] ?? ''));
        if ($firstName === '' && !empty($ssoMeta['fullName'])) {
            $parts = preg_split('/\s+/', trim((string) $ssoMeta['fullName']));
            $lastName = trim((string) array_pop($parts));
            $firstName = trim(implode(' ', $parts));
        }
        if ($firstName === '') {
            $firstName = 'SSO';
        }
        if ($lastName === '') {
            $lastName = 'User';
        }

        $baseUsername = trim((string) ($ssoMeta['username'] ?? $ssoMeta['name'] ?? ($email ? explode('@', $email)[0] : 'user_'.$sourceUserId)));
        if ($baseUsername === '') {
            $baseUsername = 'user_'.$sourceUserId;
        }
        $username = $baseUsername;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername.'_'.$counter++;
        }

        $shopRoleType = $this->mapSparkyRoleToShopType(
            isset($ssoMeta['roleNamespace']) ? (string) $ssoMeta['roleNamespace'] : null,
            isset($ssoMeta['roleType']) ? (string) $ssoMeta['roleType'] : null
        );
        $role = Role::where('type', $shopRoleType)->orderBy('id')->first()
            ?: Role::where('type', 'customer')->orderBy('id')->first();
        if (!$role) {
            return null;
        }

        $user = new User();
        $user->first_name = $firstName;
        $user->last_name = $lastName;
        $user->username = $username;
        $user->email = $email;
        $user->phone = !empty($ssoMeta['phone']) ? (string) $ssoMeta['phone'] : null;
        $user->role_id = $role->id;
        $user->is_active = 1;
        $user->password = Hash::make(Str::random(32));
        if (Schema::hasColumn('users', 'app_user_id')) {
            $user->app_user_id = (int) $sourceUserId;
        }
        if (Schema::hasColumn('users', 'pos_user_id')) {
            $user->pos_user_id = (int) $sourceUserId;
        }
        if (Schema::hasColumn('users', 'external_customer_id')) {
            $user->external_customer_id = (int) $sourceUserId;
        }
        $user->save();

        return $user;
    }

    private function mapSparkyRoleToShopType(?string $roleNamespace, ?string $roleType): string
    {
        $source = strtolower(trim((string) ($roleNamespace ?: $roleType ?: '')));

        if (in_array($source, ['admin', 'superadmin', 'nexopos.store.administrator'], true)) {
            return 'admin';
        }
        if (in_array($source, ['staff', 'nexopos.store.cashier'], true)) {
            return 'staff';
        }
        if (in_array($source, ['vendor', 'seller'], true)) {
            return 'seller';
        }

        return 'customer';
    }

    private function appendQueryParam(string $url, string $key, string $value): string
    {
        $separator = str_contains($url, '?') ? '&' : '?';

        return $url.$separator.urlencode($key).'='.urlencode($value);
    }

    public function ssoRedirectToPos(Request $request)
    {
        $mainapp = rtrim(env('MAIN_APP_URL', 'https://app.sparkypos.com'), '/');
        $shopBase = rtrim(url('/'), '/');
        $target = $request->query('redirect_to');
        if ($target && !preg_match('/^https?:/i', $target)) {
            $target = $mainapp.'/'.ltrim($target, '/');
        }
        $target = $target ?: $mainapp;

        $requestedReturnTarget = $request->query('redirect_to');
        $shopReturnTarget = is_string($requestedReturnTarget) && trim($requestedReturnTarget) !== ''
            ? trim($requestedReturnTarget)
            : $request->headers->get('referer');

        if (is_string($shopReturnTarget) && !preg_match('/^https?:\/\//i', $shopReturnTarget)) {
            $shopReturnTarget = $shopBase.'/'.ltrim($shopReturnTarget, '/');
        }

        if (!is_string($shopReturnTarget) || trim($shopReturnTarget) === '') {
            $shopReturnTarget = $shopBase;
        }
        $shopReturnTarget = trim($shopReturnTarget);
        if (preg_match('/^https?:\/\//i', $shopReturnTarget)) {
            $returnParts = parse_url($shopReturnTarget);
            $shopParts = parse_url($shopBase);
            $sameHost = ($returnParts['host'] ?? null) && ($shopParts['host'] ?? null)
                && strtolower($returnParts['host']) === strtolower($shopParts['host']);
            if (!$sameHost) {
                $shopReturnTarget = $shopBase;
            }
        } else {
            $shopReturnTarget = $shopBase;
        }

        if (!Auth::check()) {
            return redirect()->away(
                $mainapp.'/sign-in?origin=shop&redirect_to='.urlencode($shopReturnTarget)
            );
        }

        $user = Auth::user();

        $appUserId = null;
        if (Schema::hasColumn('users', 'app_user_id') && !empty($user->app_user_id)) {
            $appUserId = (string) $user->app_user_id;
        } elseif (Schema::hasColumn('users', 'pos_user_id') && !empty($user->pos_user_id)) {
            $appUserId = (string) $user->pos_user_id;
        } elseif (Schema::hasColumn('users', 'external_customer_id') && !empty($user->external_customer_id)) {
            $appUserId = (string) $user->external_customer_id;
        } elseif (!empty($user->id)) {
            $appUserId = (string) $user->id;
        }

        if (!$appUserId) {
            return redirect()->away(
                $mainapp.'/sign-in?origin=shop&redirect_to='.urlencode($shopReturnTarget)
            );
        }

        try {
            $payload = [
                'appUserId' => (string) $appUserId,
                'issuedAt' => now()->toIso8601String(),
                'redirectTo' => $target,
                'email' => (string) ($user->email ?? ''),
                'username' => (string) ($user->username ?? ''),
                'firstName' => (string) ($user->first_name ?? ''),
                'lastName' => (string) ($user->last_name ?? ''),
                'phone' => (string) ($user->phone ?? ''),
                'roleType' => optional($user->role)->type,
                'roleNamespace' => $this->mapShopRoleTypeToSparkyNamespace(optional($user->role)->type),
            ];
            $token = base64_encode(Crypt::encryptString(json_encode($payload, JSON_UNESCAPED_SLASHES)));
            return redirect()->away($mainapp.'/sso?t='.urlencode($token));
        } catch (\Throwable $e) {
            return redirect()->away($mainapp.'/sign-in');
        }
    }

    private function mapShopRoleTypeToSparkyNamespace(?string $shopRoleType): string
    {
        $roleType = strtolower(trim((string) $shopRoleType));

        if (in_array($roleType, ['superadmin', 'admin'], true)) {
            return 'admin';
        }
        if ($roleType === 'staff') {
            return 'nexopos.store.cashier';
        }
        if ($roleType === 'seller') {
            return 'Vendor';
        }

        return 'Customer';
    }

    public function ssoLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $mainapp = rtrim(env('MAIN_APP_URL', 'https://app.sparkypos.com'), '/');
        $target = $request->query('redirect_to');
        if ($target && !preg_match('/^https?:/i', $target)) {
            $target = $mainapp.'/'.ltrim($target, '/');
        }
        return redirect()->away($target ?: $mainapp.'/sign-in');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
        $user = User::where('email', $request->email)->where('is_active', 1)->whereHas('role', function ($q) {
            return $q->where('type', 'customer');
        })->first();
        if (!$user) {
            $user = User::where('username', $request->email)->where('is_active', 1)->whereHas('role', function ($q) {
                return $q->where('type', 'customer');
            })->first();
        }
        if ($user && Hash::check($request->password, $user->password) && $user->role->type == 'customer') {
            $token = $user->createToken('my_token')->plainTextToken;
            try { app(CustomerSyncService::class)->syncCustomerById($user->id); } catch (\Throwable $e) { \Log::warning('customersync.pos.sync_after_login_failed', ['error'=>$e->getMessage()]); }
            $response = [
                'user' => $user,
                'token' => $token,
                'message' => 'Successfully logged In'
            ];
            return response($response, 200);
        } else {
            return response(['message' => 'Invalid Credintials'], 401);
        }
    }

    public function customerLogin(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
        $user = User::where('is_active', 1)->whereHas('role', function ($role) {
            return $role->where('type', 'customer');
        })
            ->where('email', $request->email)
            ->orWhere('username', $request->email)
            ->first();
        if ($user && password_verify($request->password, $user->password) && $user->role->type == 'customer') {
            $token = $user->createToken('my_token')->plainTextToken;
            try { app(CustomerSyncService::class)->syncCustomerById($user->id); } catch (\Throwable $e) { \Log::warning('customersync.pos.sync_after_customer_login_failed', ['error'=>$e->getMessage()]); }
            $response = [
                'user' => $user,
                'token' => $token,
                'message' => 'Successfully logged In'
            ];
            return response($response, 200);
        } else {
            return response(['message' => 'Invalid Credintials'], 401);
        }
    }

    public function socialLogin(Request $request)
    {
        $request->validate([
            'provider_id' => ['required'],
            'provider_name' => ['required'],
            'name' => ['nullable'],
            'email' => ['nullable'],
            'token' => 'required'
        ]);
        if ($request->provider_name == 'google') {
            $res = \Illuminate\Support\Facades\Http::get('https://oauth2.googleapis.com/tokeninfo?id_token=' . $request->token);
            if ($res->successful()) {
                return $this->getTokenBySocial($request);
            } else {
                return response()->json(['message' => 'Invalid token.'], 422);
            }
        } elseif ($request->provider_name == 'facebook') {
            $res = \Illuminate\Support\Facades\Http::get('https://graph.facebook.com/me?access_token=' . $request->token);
            if ($res->successful()) {
                return $this->getTokenBySocial($request);
            } else {
                return response()->json(['message' => 'Invalid token.'], 422);
            }
        } else {
            return response()->json(['message' => 'Invalid provider name.'], 422);
        }
    }

    /**
     * Logout user
     * @response{
     * "message": "Logged out successfully"
     * }
     */

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
        return response(['message' => 'Logged out successfully'], 200);
    }

    /**
     * Register Customer
     * @bodyParam first_name string required customer first name
     * @bodyParam last_name string required customer last name
     * @bodyParam email string required email or phone from user
     * @bodyParam referral_code string nullable referral code from another user
     * @bodyParam password string required password
     * @bodyParam password_confirmation string required same as password
     * @bodyParam user_type string required customer
     *
     * @response 201{
     *
     * "user": {
     *       "first_name": "customer 5",
     *       "last_name": null,
     *       "username": null,
     *       "email": "customer5@gmail.com",
     *       "role_id": 4,
     *       "phone": null,
     *       "updated_at": "2021-06-10T11:41:35.000000Z",
     *       "created_at": "2021-06-10T11:41:35.000000Z",
     *       "id": 9
     *   },
     *   "token": "6|PV66uUWWSNzekyWW05XqItqI9ernvqAqEkxbYGh0",
     *   "message" : "Successfully registered"
     *
     * }
     */

    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'email' => ['required', 'string', 'max:255'],
            'password' => 'required|min:8|confirmed',
            'user_type' => 'required'
        ], [
            'password.min' => 'The password field minimum 8 character.'
        ]);
        $user_exist = $this->authService->getRegister($request->all());
        if ($user_exist) {
            return $this->registerCustomerResponse($user_exist);
        }

        $request->validate([
            'first_name' => 'required',
            'email' => ['required', 'string', 'max:255', 'unique:users,email', 'check_unique_phone'],
            'password' => 'required|min:8|confirmed',
            'user_type' => 'required'
        ], [
            'password.min' => 'The password field minimum 8 character.'
        ]);
        if ($request->user_type == 'customer') {
            $user = $this->authService->register($request->all());
            return $this->registerCustomerResponse($user);
        } else {
            $response = ['message' => 'invalid Credintials'];
            return response()->json($response, 409);
        }
    }

    /**
     * Change Password
     * @bodyParam old_password string required old password
     * @bodyParam password string required new password
     * @bodyParam password_confirmation string required same as new password
     *
     * @response{
     *     'message' => 'password change successfully'
     * }
     */

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);
        $user = $request->user();
        if ($user) {
            $response = $this->authService->changePassword($user, $request->only('old_password', 'password'));
            if ($response == 1) {
                return response()->json(['message' => 'password change successfully'], 200);
            } else {
                return response()->json(['message' => 'Invalid Credintials.'], 409);
            }
        } else {
            return response()->json(['message' => 'user not found'], 404);
        }
    }

    /**
     * Get user
     * @response{
     * "user": {
     *       "id": 9,
     *       "first_name": "customer 5",
     *       "last_name": null,
     *       "username": null,
     *       "photo": null,
     *       "role_id": 4,
     *       "mobile_verified_at": null,
     *       "email": "customer5@gmail.com",
     *       "is_verified": 0,
     *       "verify_code": null,
     *       "email_verified_at": null,
     *       "notification_preference": "mail",
     *       "is_active": 1,
     *       "avatar": null,
     *       "phone": null,
     *       "date_of_birth": null,
     *       "description": null,
     *       "secret_login": 0,
     *       "secret_logged_in_by_user": null,
     *       "created_at": "2021-06-10T11:41:35.000000Z",
     *       "updated_at": "2021-06-10T11:41:35.000000Z",
     *       "customer_addresses": []
     *   },
     *   "message": "success"
     * }
     */

    public function getUser(Request $request)
    {
        $user = User::with('customerAddresses', 'currency', 'language')
            ->where('id', $request->user()->id)
            ->where('is_active', 1)
            ->first();
        if ($user) {
            return response()->json(['user' => $user, 'message' => 'success'], 200);
        } else {
            return response()->json(['message' => 'user not found'], 404);
        }
    }

    /**
     * Forgot Password
     * @bodyParam email string required customers email
     * @response{
     *      "message": "Reset password link sent on your email id."
     * }
     */

    public function forgotPasswordAPI(Request $request)
    {
        $request->validate(['email' => 'required|email',]);
        $user = User::where('email', $request->email)->where('role_id', 4)->first();
        if ($user) {
            return $this->forgot($request->all());
        } else {
            return response()->json(['message' => 'Customer not found.'], 404);
        }
    }

    /**
     * Delete Account
     * @response{
     *      "message": "success"
     * }
     */
    public function customerDelete(Request $request)
    {
        $user = User::find($request->user()->id);
        if ($user) {
            DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->delete();
            $customer_orders  = Order::where('customer_id', $user->id)->pluck('id');
            $wallet = WalletBalance::where('user_id', $user->id)->pluck('id');
            if ($customer_orders->count() || $wallet->count()) {
                $user->update(['is_active' => 2]);
            } else {
                $addresses = $user->customerAddresses->pluck('id');
                CustomerAddress::destroy($addresses);
                $notifications = CustomerNotification::where('customer_id', $user->id)->pluck('id');
                CustomerNotification::destroy($notifications);
                $notification_settings = UserNotificationSetting::where('user_id', $user->id)->pluck('id');
                UserNotificationSetting::destroy($notification_settings);
                $user->delete();
            }
            return response()->json(['message' => 'success'], 200);
        } else {
            return response()->json(['message' => 'invalid'], 404);
        }
    }

    public function userNotifications(Request $request)
    {
        $user_id = $request->user()->id;
        $notifications = $this->notificationSettingService->userNotifications($user_id);

        if ($notifications) {
            return response()->json([
                'notifications' => $notifications
            ], 200);
        } else {
            return response()->json([
                'message' => 'not found'
            ], 404);
        }
    }

    private function registerCustomerResponse($user)
    {
        $token = $user->createToken('my_token')->plainTextToken;
        $response = [
            'user' => $user,
            'token' => $token,
            'message' => 'Successfully registered'
        ];
        return response()->json($response, 201);
    }

    private function getTokenBySocial($request)
    {
        $provider = SocialProvider::where('provider_id', $request->provider_id)->where('provider_name', $request->provider_name)->first();
        if ($provider) {
            $user = User::where('id', $provider->user_id)->where('is_active', 1)->first();
            if ($user) {
                $token = $user->createToken('my_token')->plainTextToken;
                $response = [
                    'user' => $user,
                    'token' => $token,
                    'message' => 'Successfully logged In'
                ];
                return response($response, 200);
            } else {
                return response()->json(['message' => 'Your Account is Disabled.'], 422);
            }
        } else {
            $exsist = User::where('email', $request->email)->first();
            if (!$exsist) {
                $newUser = User::create([
                    'first_name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make("verystrongpass1234"),
                    'role_id' => 4,
                    'is_verified' => 1,
                    'currency_id' => app('general_setting')->currency,
                    'lang_code' => app('general_setting')->language_code,
                    'currency_code' => app('general_setting')->currency_code,
                ]);

                SocialProvider::create([
                    'user_id' => $newUser->id,
                    'provider_id' => $request->provider_id,
                    'provider_name' => $request->provider_name,
                ]);

                //affiliate user
                if (isModuleActive('Affiliate')) {
                    $affiliateRepo = new AffiliateRepository();
                    $affiliateRepo->affiliateUser($newUser->id);
                }

                // User Notification Setting Create
                (new UserNotificationSetting)->createForRegisterUser($newUser->id);
                $this->typeId = EmailTemplateType::where('type', 'register_email_template')->first()->id; //register email templete typeid
                $this->adminNotificationUrl = '/customer/active-customer-list';
                $this->routeCheck = 'cusotmer.list.get-data';
                $notification = NotificationSetting::where('slug', 'register')->first();
                if ($notification) {
                    $this->notificationSend($notification->id, $newUser->id);
                }
                $token = $newUser->createToken('my_token')->plainTextToken;
                $response = [
                    'user' => $newUser,
                    'token' => $token,
                    'message' => 'Successfully logged In'
                ];
                return response($response, 200);
            } else {
                return response()->json(['message' => 'Email Already Taken By Normal Registration.'], 422);
            }
        }
    }

    private function forgot($user)
    {
        Password::sendResetLink($user);
        return response()->json(["message" => 'Reset password link sent on your email id.']);
    }
}
