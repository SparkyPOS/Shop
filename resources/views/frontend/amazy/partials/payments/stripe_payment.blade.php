<div class="col-lg-12 text-center mt_5 mb_25">
    <span></span>
</div>
<form action="{{route('frontend.order_payment')}}" method="post" id="stripe_form" class="stripe_form d-none">

    <input type="hidden" name="method" value="Stripe">
    <input type="hidden" name="amount" value="{{$total_amount - $coupon_am}}">
    <button type="submit" id="stribe_submit_btn" class="btn_1 order_submit_btn">{{ __('defaultTheme.process_to_payment') }}</button>
    @csrf
    @php
        if(app('general_setting')->seller_wise_payment && session()->has('seller_for_checkout')){
            $credential = getPaymentInfoViaSellerId(session()->get('seller_for_checkout'), 'stripe');
        }else{
            $credential = getPaymentInfoViaSellerId(1, 'stripe');
        }
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
    <script
        src="https://checkout.stripe.com/checkout.js"
        class="stripe-button"
        data-key="{{ @$credential->perameter_1 }}"
        data-name="Stripe Payment"
        data-image="{{showImage(app('general_setting')->favicon)}}"
        data-locale="auto"
        data-currency="{{$currency_code}}">
    </script>
</form>
