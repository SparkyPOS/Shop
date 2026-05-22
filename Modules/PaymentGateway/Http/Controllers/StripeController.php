<?php

namespace Modules\PaymentGateway\Http\Controllers;


use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Brian2694\Toastr\Facades\Toastr;
use App\Repositories\OrderRepository;
use \Modules\Wallet\Repositories\WalletRepository;
use Modules\Account\Repositories\TransactionRepository;
use Modules\Account\Entities\Transaction;
use Modules\FrontendCMS\Entities\SubsciptionPaymentInfo;
use App\Traits\Accounts;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Validator;
use Modules\UserActivityLog\Traits\LogActivity;
use Stripe;
use Stripe\Transfer;

class StripeController extends Controller
{
    use Accounts;

    public function __construct()
    {
        $this->middleware('maintenance_mode');
    }

    public function payment_page(Request $request)
    {

         return view('paymentgateway::stripe_payment.create');
    }

    public function createPaymentIntent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $amount = (float) $request->input('amount');
        $credential = $this->getCredential();

        if (empty($credential?->perameter_1) || empty($credential?->perameter_3)) {
            return response()->json([
                'message' => __('payment_gatways.stripe_configuration'),
            ], 422);
        }

        Stripe\Stripe::setApiKey($credential->perameter_3);

        try {
            $amountInCents = (int) round($amount * 100);
            $payload = [
                'amount' => (int) round($amount * 100),
                'currency' => strtolower(getCurrencyCode()),
                'payment_method_types' => ['card'],
                'description' => 'Order payment from ' . url('/'),
                'metadata' => [
                    'purpose' => 'order_payment',
                ],
            ];

            // For seller-wise checkout, create a Stripe Connect destination charge so
            // the checkout amount lands on the seller's connected account and the
            // platform keeps only the configured commission percentage.
            if (isModuleActive('MultiVendor') && app('general_setting')->seller_wise_payment && session()->has('seller_for_checkout')) {
                $seller = \App\Models\User::with('SellerAccount')->find(session()->get('seller_for_checkout'));

                if ($seller && !empty($seller->stripe_account_id)) {
                    $commissionRate = (float) optional($seller->SellerAccount)->commission_rate;
                    $applicationFeeAmount = (int) round(($amountInCents * max($commissionRate, 0)) / 100);
                    $applicationFeeAmount = min($applicationFeeAmount, $amountInCents);

                    $payload['transfer_data'] = [
                        'destination' => $seller->stripe_account_id,
                    ];
                    $payload['application_fee_amount'] = $applicationFeeAmount;
                    $payload['metadata']['seller_id'] = (string) $seller->id;
                    $payload['metadata']['commission_rate'] = (string) $commissionRate;
                }
            }

            $paymentIntent = Stripe\PaymentIntent::create($payload);

            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'publishable_key' => $credential->perameter_1,
            ]);
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());

            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function stripePost($data)
    {
        // Stripe expects lowercase ISO currency codes
        $currency_code = strtolower(getCurrencyCode());
        $credential = $this->getCredential();
        Stripe\Stripe::setApiKey(@$credential->perameter_3);
        try{
            $stripe = null;
            $chargeId = null;
            $isDestinationCharge = false;

            if (!empty($data['stripe_payment_intent_id'])) {
                $stripe = Stripe\PaymentIntent::retrieve($data['stripe_payment_intent_id']);
                if (!$stripe || $stripe->status !== 'succeeded') {
                    throw new Exception('Stripe payment is not completed.');
                }
                $chargeId = is_string($stripe->latest_charge)
                    ? $stripe->latest_charge
                    : ($stripe->latest_charge->id ?? null);
                $isDestinationCharge = !empty($stripe->transfer_data->destination ?? null);
            } else {
                $stripe = Stripe\Charge::create ([
                    "amount" => (int) round($data['amount'] * 100),
                    "currency" => $currency_code,
                    "source" => $data['stripeToken'],
                    "description" => "Payment from ". url('/')
                ]);
                $chargeId = $stripe->id ?? null;
            }

            // Only attempt seller payouts when enabled and data provided
            $sellers = $data['seller'] ?? [];
            if (!$isDestinationCharge && isModuleActive('MultiVendor') && app('general_setting')->seller_wise_payment && is_array($sellers) && count($sellers) > 0) {
                foreach ($sellers as $seller) {
                    $total_amount = isset($seller['price']) ? (float) $seller['price'] : 0.0;
                    $user = isset($seller['seller_id']) ? \App\Models\User::find($seller['seller_id']) : null;
                    if (!$user) {
                        continue; // invalid seller entry
                    }
                    $sellerAccount = $user->SellerAccount ?? null;
                    $commission_rate = $sellerAccount && isset($sellerAccount->commission_rate) ? (float) $sellerAccount->commission_rate : 0.0;
                    $comission = ($total_amount * $commission_rate) / 100.0;
                    $seller_amount = max($total_amount - $comission, 0);

                    // Require a connected account id to transfer. Skip if not present.
                    if (!empty($user->stripe_account_id) && $seller_amount > 0) {
                        Transfer::create([
                            'amount' => (int) round($seller_amount * 100),
                            'currency' => $currency_code,
                            'destination' => $user->stripe_account_id,
                            'source_transaction' => $chargeId,
                        ]);
                    }
                }
            }


        }catch(Exception $e){
            Toastr::error($e->getMessage(), __('common.error'));
            return redirect()->back();
        }
        if (($stripe['status'] ?? null) == "succeeded") {
            $return_data = $stripe['id'];
            if (session()->has('wallet_recharge')) {
                $walletService = new WalletRepository;
                return $walletService->walletRecharge($data['amount'], $credential->method->id, $return_data);
            }
            if (session()->has('order_payment')) {
                $orderPaymentService = new OrderRepository;
                $order_payment = $orderPaymentService->orderPaymentDone($data['amount'], $credential->method->id, $return_data, (auth()->check())?auth()->user():null);
                if($order_payment == 'failed'){
                    Toastr::error('Invalid Payment');
                    return redirect(url('/checkout'));
                }
                $payment_id = $order_payment->id;
                Session()->forget('order_payment');
                LogActivity::successLog('Order payment successful.');
                return $payment_id;
            }
            if (session()->has('subscription_payment')) {
                $defaultIncomeAccount = $this->defaultIncomeAccount();
                $seller_subscription = getParentSeller()->SellerSubscriptions;
                $transactionRepo = new TransactionRepository(new Transaction);
                $transaction = $transactionRepo->makeTransaction(getParentSeller()->first_name." - Subsriction Payment", "in", "Stripe", "subscription_payment", $defaultIncomeAccount, "Subscription Payment", $seller_subscription, $data['amount'], Carbon::now()->format('Y-m-d'), getParentSellerId(), null, null);
                $seller_subscription->update(['last_payment_date' => Carbon::now()->format('Y-m-d')]);
                SubsciptionPaymentInfo::create([
                    'transaction_id' => $transaction->id,
                    'txn_id' => $return_data,
                    'seller_id' => getParentSellerId(),
                    'subscription_type' => getParentSeller()->sellerAccount->subscription_type,
                    'commission_type' => @$seller_subscription->pricing->name
                ]);
                LogActivity::successLog('Subscription payment successful.');
                return true;
            }
        }else {
            return redirect()->route('frontend.welcome');
        }
    }

    private function getCredential(){
        // Always charge on the platform account for Stripe Connect.
        // Funds will be transferred to connected accounts after a successful charge
        // when a seller has a valid stripe_account_id. If a seller is not connected,
        // funds remain in the platform account by design.
        return getPaymentInfoViaSellerId(1, 'stripe');
    }

}
