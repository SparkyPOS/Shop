<?php

namespace Modules\PaymentGateway\Http\Controllers;


use Illuminate\Contracts\Support\Renderable;
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

    public function stripePost($data)
    {
        // Stripe expects lowercase ISO currency codes
        $currency_code = strtolower(getCurrencyCode());
        $credential = $this->getCredential();
        Stripe\Stripe::setApiKey(@$credential->perameter_3);
        try{
            $stripe = Stripe\Charge::create ([
                "amount" => (int) round($data['amount'] * 100),
                "currency" => $currency_code,
                "source" => $data['stripeToken'],
                "description" => "Payment from ". url('/')
            ]);

            // Only attempt seller payouts when enabled and data provided
            $sellers = $data['seller'] ?? [];
            if (isModuleActive('MultiVendor') && app('general_setting')->seller_wise_payment && is_array($sellers) && count($sellers) > 0) {
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
                            'source_transaction' => $stripe->id, // Charge ID from customer payment
                        ]);
                    }
                }
            }


        }catch(Exception $e){
            Toastr::error($e->getMessage(), __('common.error'));
            return redirect()->back();
        }
        if ($stripe['status'] == "succeeded") {
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
