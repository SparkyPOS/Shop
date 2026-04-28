
@extends('frontend.amazy.layouts.app')
@php
    $pageTitle = optional($data)->mainTitle ?: 'Return & Exchange Policy';
    $returnTitle = optional($data)->returnTitle ?: 'Returns, Exchanges, and Refunds';
    $returnDescription = optional($data)->returnDescription;
@endphp

@section('title')
    {{ $pageTitle }}
@endsection

@section('content')
<section class="return_part padding_top bg-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="single_return_part">
                    <h5 class="font_18 f_w_700">{{ $returnTitle }}</h5>
                    @if(!empty($returnDescription))
                        {!! $returnDescription !!}
                    @else
                        <p>
                            We want you to be satisfied with every order. If an item does not work out,
                            you can request a return or exchange based on the policy below.
                        </p>
                    @endif
                    <div class="mt-4">
                        <h6 class="font_16 f_w_700">USA Return & Exchange Policy</h6>
                        <ul class="mb-4">
                            <li>Standard return window: 30 days from delivery date for eligible items.</li>
                            <li>Item condition: must be unused, unwashed, and in original packaging with tags.</li>
                            <li>Non-returnable items: final sale, personalized goods, gift cards, and hygiene-sensitive items.</li>
                            <li>Damaged or wrong item: report within 72 hours of delivery with photos for quick resolution.</li>
                            <li>Refund method: approved refunds are issued to the original payment method within 5-10 business days.</li>
                            <li>Exchange option: size/color exchanges are subject to stock availability at request time.</li>
                            <li>Shipping cost: original shipping is non-refundable unless the return is due to our error.</li>
                        </ul>
                    </div>
                    <div class="mt-5 w-100">
                        <a href="{{url('/contact-us')}}" class="amaz_primary_btn style2 mb_20  add_to_cart flex-fill text-center">{{ __('common.contact_us') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
