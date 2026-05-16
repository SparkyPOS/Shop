<div class="col-lg-12 text-center mt_5 mb_25">
    <span></span>
</div>
<form action="{{route('frontend.order_payment')}}" method="post" id="stripe_form" class="stripe_form d-none">
    <input type="hidden" name="method" value="Stripe">
    <input type="hidden" name="amount" value="{{$total_amount - $coupon_am}}">
    <input type="hidden" name="stripe_payment_intent_id" id="stripe_payment_intent_id">
    <button type="submit" id="stribe_submit_btn" class="btn_1 order_submit_btn">{{ __('defaultTheme.process_to_payment') }}</button>
    @csrf
    @php
        // Use platform publishable key for Stripe Connect (charge on platform)
        $credential = getPaymentInfoViaSellerId(1, 'stripe');
    @endphp
    @php
    $i=0;
        $total = 0;
        $tax = 0;
        $subtotal = 0;
        $actual_price = 0;
    @endphp
    @foreach($cartData as $seller_id => $packages)
        @php
            $seller = App\Models\User::where('id',$seller_id)->first();

            $seller_actual_price = 0;
//            $current_pkg ++;
//            $total_shipping_charge += $package_wise_shipping[$seller_id]['shipping_cost'];
        @endphp
        @foreach($packages as $key => $item)
            @php
                $actual_price = $item->total_price;
                $seller_actual_price += $item->total_price;
//                $subtotal += $item->giftCard->sell_price * $item->qty;
            @endphp
        @endforeach
        <input type="hidden" name="seller[{{$i}}][seller_id]" value="{{$seller_id}}">
        <input type="hidden" name="seller[{{$i}}][price]" value="{{$seller_actual_price}}">
        <input type="hidden" name="seller[{{$i}}][shipping]" value="0">

        @php
        $i++;
        @endphp
    @endforeach
</form>
<div class="stripe_payment_element_wrapper">
    <div id="stripe-payment-element" class="stripe_payment_element"></div>
    <div id="stripe-payment-errors" class="text-danger mt_10"></div>
    <input type="hidden" id="stripe_publishable_key" value="{{ @$credential->perameter_1 }}">
    <input type="hidden" id="stripe_intent_route" value="{{ route('stripe.payment_intent') }}">
</div>
