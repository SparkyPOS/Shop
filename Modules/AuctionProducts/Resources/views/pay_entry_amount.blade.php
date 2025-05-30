@extends('frontend.amazy.layouts.app')
@section('title')
    @if(@$product->product->meta_title != null)
        {{ @substr(@$product->product->meta_title,0, 60)}} | Pay Entry Amount
    @else
        {{ @substr(@$product->product_name,0, 60)}} | Pay Entry Amount
    @endif
@endsection
@section('share_meta')
    @if(@$product->product->meta_description != null)
        <meta property="og:description" content="{{@$product->product->meta_description}}" />
        <meta name="description" content="{{@$product->product->meta_description}}">
    @else
        <meta property="og:description" content="{{@$product->product->description}}" />
        <meta name="description" content="{{ @$product->product->description }}">
    @endif
    @if(@$product->product->meta_title != null)
        <meta name="title" content="{{ @substr(@$product->product->meta_title,0,60) }}"/>
        <meta property="og:title" content="{{substr(@$product->product->meta_title,0,60)}}" />
    @else
        <meta property="og:title" content="{{@substr(@$product->product_name,0,60)}}" />
        <meta name="title" content="{{ @substr(@$product->product_name,0,60) }}"/>
    @endif
    @if(@$product->product->meta_image != null && @getimagesize(showImage(@$product->product->meta_image))[0] > 200)
        <meta property="og:image" content="{{showImage($product->product->meta_image)}}" />
    @elseif(@$product->product->thumbnail_image_source != null && @getimagesize(showImage(@$product->product->thumbnail_image_source))[0] > 200)
        <meta property="og:image" content="{{showImage(@$product->product->thumbnail_image_source)}}" />
    @elseif(count(@$product->product->gallary_images) > 0 && @getimagesize(showImage(@$product->product->gallary_images[0]->images_source))[0] > 200)
        <meta property="og:image" content="{{showImage(@$product->product->gallary_images[0]->images_source)}}" />
    @endif
    <meta property="og:url" content="{{singleProductURL(@$product->seller->slug, $product->slug)}}" />
    <meta property="og:image:width" content="400" />
    <meta property="og:image:height" content="300" />
    <meta property="og:type" content="{{@$product->product->meta_description}}">
    @php
        $total_tag = count($product->product->tags);
        $meta_tags = '';
        foreach($product->product->tags as $key => $tag){
            if($key + 1 < $total_tag){
                $meta_tags .= $tag->name.', ';
            }else{
                $meta_tags .= $tag->name;
            }
        }
    @endphp
    <meta name ="keywords", content="{{$meta_tags}}">
@endsection
@push('styles')
    <link rel="stylesheet" href="{{asset(asset_path('frontend/amazy/css/page_css/product_details.css'))}}" />
    <link rel="stylesheet" href="{{asset(asset_path('frontend/default/css/lightbox.css'))}}" />
    @if(isRtl())
        <style>
            .zoomWindowContainer div {
                left: 0!important;
                right: 510px;
            }
            .product_details_part .cs_color_btn .radio input[type="radio"] + .radio-label:before {
                left: -1px !important;
            }
            @media (max-width: 970px) {
                .zoomWindowContainer div {
                    right: inherit!important;
                }
            }
        </style>
    @endif
@endpush
@section('content')
<div class="product_details_wrapper">
    <div class="container">
        <div class="row mb-5">
            <div class="col-xl-6">
                <form action="{{ route('auction.auctionEntryAmountPay',$auction->id) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-12 mb_10">
                            <h3 class="check_v3_title2">{{__('common.payment')}}</h3>
                            <h6 class="shekout_subTitle_text">{{__('defaultTheme.all_transactions_are_secure_and_encrypted')}}.</h6>
                        </div>
                        <div class="col-12">
                            <div class="accordion checkout_acc_style mb_30" id="accordionExample">

                                @foreach($gateway_activations as $key => $payment)
                                    <div class="accordion-item">
                                        <div class="accordion-header" id="headingOne">
                                            <span class="accordion-button shadow-none" data-bs-toggle="collapse" data-bs-target="#collapse{{$key}}"  aria-controls="collapse{{$key}}">
                                                <span class="w-100">
                                                    <label class="primary_checkbox d-inline-flex style4 gap_10 w-100" >
                                                        <input type="radio" name="payment_method" class="payment_method" data-name="{{$payment->method}}" value="{{$payment->id}}" {{$key == 0?'checked':''}}>
                                                        <span class="checkmark mr_10"></span>
                                                        <span class="label_name f_w_500 ">
                                                            @php
                                                                switch ($payment->method) {
                                                                    case 'Cash On Delivery':
                                                                    echo __("payment_gatways.cash_on_delivery");
                                                                    break;
                                                                    case 'Wallet':
                                                                    echo __("payment_gatways.wallet");
                                                                    break;
                                                                    case 'PayPal':
                                                                    echo __("payment_gatways.paypal");
                                                                    break;
                                                                    case 'Stripe':
                                                                    echo __("payment_gatways.stripe");
                                                                    break;
                                                                    case 'PayStack':
                                                                    echo __("payment_gatways.paystack");
                                                                    break;
                                                                    case 'RazorPay':
                                                                    echo __("payment_gatways.razorpay");
                                                                    break;
                                                                    case 'PayTM':
                                                                    echo __("payment_gatways.paytm");
                                                                    break;
                                                                    case 'Instamojo':
                                                                    echo __("payment_gatways.instamojo");
                                                                    break;
                                                                    case 'Midtrans':
                                                                    echo __("payment_gatways.midtrans");
                                                                    break;
                                                                    case 'PayUMoney':
                                                                    echo __("payment_gatways.payumoney");
                                                                    break;
                                                                    case 'JazzCash':
                                                                    echo __("payment_gatways.jazzcash");
                                                                    break;
                                                                    case 'Google Pay':
                                                                    echo __("payment_gatways.google_pay");
                                                                    break;
                                                                    case 'FlutterWave':
                                                                    echo __("payment_gatways.flutter_wave_payment");
                                                                    break;
                                                                    case 'Bank Payment':
                                                                    echo __("payment_gatways.bank_payment");
                                                                    break;
                                                                    case 'Bkash':
                                                                    echo __("payment_gatways.bkash");
                                                                    break;
                                                                    case 'SslCommerz':
                                                                    echo __("payment_gatways.ssl_commerz");
                                                                    break;
                                                                    case 'Mercado Pago':
                                                                    echo __("payment_gatways.mercado_pago");
                                                                    break;
                                                                    case 'Tabby':

                                                                    echo trans('payment_gatways.4 intereset-free Payments');
                                                                    echo '<span style="position: absolute; right:0"><img height="20" src="'.asset('public/'.$payment->logo).'"></span>';

                                                                    break;
                                                                    case 'CCAvenue':
                                                                    echo __("payment_gatways.ccavenue");
                                                                    break;

                                                                    case 'Clickpay':
                                                                    echo __("payment_gatways.Clickpay");
                                                                    break;
                                                                }
                                                                @endphp
                                                        </span>
                                                    </label>
                                                </span>
                                            </span>
                                        </div>
                                        <div id="collapse{{$key}}" class="accordion-collapse collapse {{$key == 0?'show':''}}" aria-labelledby="heading{{$key}}" data-bs-parent="#accordionExample">
                                            <div class="accordion-body" id="acc_{{$payment->id}}">
                                                <!-- content ::start  -->
                                                <div class="row">

                                                    @if($payment->method == 'Cash On Delivery')

                                                    @elseif($payment->method == 'Wallet')
                                                        <div class="col-lg-12 text-center mb_20">
                                                            <strong>{{__('common.balance')}}: {{single_price(auth()->user()->CustomerCurrentWalletAmounts)}}</strong>
                                                        </div>
                                                    @elseif($payment->method == 'Stripe')

                                                    @elseif($payment->method == 'PayPal')

                                                    @elseif($payment->method == 'PayStack')

                                                    @elseif($payment->method == 'RazorPay')

                                                    @elseif($payment->method == 'Instamojo')

                                                    @elseif($payment->method == 'PayTM')

                                                    @elseif($payment->method == 'Midtrans')

                                                    @elseif($payment->method == 'PayUMoney')

                                                    @elseif($payment->method == 'JazzCash')

                                                    @elseif($payment->method == 'Google Pay')

                                                    @elseif($payment->method == 'FlutterWave')

                                                    @elseif($payment->method == 'Bank Payment')
                                                     @include('auctionproducts::payment_gateways.bank_payment')
                                                    @elseif(isModuleActive('Bkash') && $payment->method=="Bkash")

                                                    @elseif(isModuleActive('MercadoPago') && $payment->method=="Mercado Pago")

                                                    @elseif(isModuleActive('Tabby') && $payment->method=="Tabby")
                                                        @php
                                                            $tabby_gateway = getPaymentGatewayInfo($payment->id);
                                                            if($tabby_gateway){
                                                                $tabby_fee = $tabby_gateway->perameter_3;
                                                                $place_holder =$tabby_gateway->perameter_4;
                                                            }
                                                        @endphp

                                                    @elseif(isModuleActive('CCAvenue') && $payment->method=="CCAvenue")

                                                    @elseif(isModuleActive('SslCommerz') && $payment->method=="SslCommerz")

                                                    @elseif(isModuleActive('Clickpay') && $payment->method=="Clickpay")
                                                        @includeIf('clickpay::auction_entry_amount_payment',compact('auction'))
                                                    @endif

                                                </div>
                                                <!-- content ::end  -->
                                            </div>
                                        </div>
                                    </div>

                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button class="amaz_primary_btn style2  min_200 text-center text-uppercase">
                                Pay now
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-lg-6">
                    <div class="order_sumery_box flex-fill">
                        <h3 class="check_v3_title mb_25">Auction Details</h3>
                        <div class="subtotal_lists">
                                <div class="single_total_list d-flex align-items-center">
                                    <div class="single_total_left">
                                        @php
                                            if (@$product->thum_img != null) {
                                                $thumbnail = showImage(@$product->thum_img);
                                            } else {
                                                $thumbnail = showImage(@$product->product->thumbnail_image_source);
                                            }
                                        @endphp
                                            <img src="{{ $thumbnail }}" class="img-fluid" alt="{{ $product->product->product_name }}">
                                            <p>{{ $auction->auction_title }}</p>
                                            <p>Starting Bid: {{ single_price($auction->starting_bidding_price) }}</p>
                                    </div>

                                </div>

                        </div>
                    </div>
                    <div class="order_sumery_box flex-fill">
                        <h3 class="check_v3_title mb_25">Payment Summery</h3>
                        <div class="subtotal_lists">
                                <div class="single_total_list d-flex align-items-center">
                                    <div class="single_total_left flex-fill">
                                        <h4>{{ __("auctionproducts::auctionproduct.Auction Entry Amount") }}</h4>
                                    </div>
                                    <div class="single_total_right">
                                        <span>{{ single_price($auction->entry_amount) }}</span>
                                    </div>
                                </div>
                                <div class="total_amount d-flex align-items-center flex-wrap">
                                    <div class="single_total_left flex-fill">
                                        <span class="total_text">{{ __("common.total") }}</span>
                                    </div>
                                    <div class="single_total_right">
                                        <span class="total_text" id="total_amount" data-amount="{{  $auction->entry_amount }}">{{ single_price( $auction->entry_amount) }}</span>
                                    </div>
                                </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</div>

@endsection
