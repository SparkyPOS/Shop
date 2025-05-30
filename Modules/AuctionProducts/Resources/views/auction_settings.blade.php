@extends('backEnd.master')
@section('styles')
    <link rel="stylesheet" href="{{asset(asset_path('modules/marketing/css/flash_deal_create.css'))}}" />
@endsection
@section('mainContent')
@if(isModuleActive('FrontendMultiLang'))
@php
$LanguageList = getLanguageList();
@endphp
@endif
<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid p-0">
        <form action="{{route('auctionproducts.update.auction.settings')}}" enctype="multipart/form-data" method="POST">
            @csrf
            <input type="hidden" name="id" value="{{$auction->id}}">
        <div class="row">
            <div class="col-lg-8">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="main-title">
                            <h3 class="mb-30"> {{__('auctionproduct.settings')}} </h3>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div id="formHtml" class="col-lg-12">
                        <div class="white-box">
                                <div class="add-visitor">
                                    <div class="row">

                                        <div class="col-lg-12">
                                            <div class="primary_input">
                                                <label class="primary_input_label" for="">{{ __('auctionproduct.Award Auction Bidder') }}</label>
                                                <ul id="theme_nav" class="permission_list sms_list ">
                                                    <li>
                                                        <label data-id="bg_option" class="primary_checkbox d-flex mr-12 extra_width">
                                                            <input name="bidder_award_system" id="bidder_award_system_off" value="0" class="active" type="radio" {{$auction->bidder_award_system==0 ? 'checked' : ''}}>
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <p>{{ __('auctionproduct.automatically') }}</p>
                                                    </li>
                                                    <li>
                                                        <label data-id="color_option" class="primary_checkbox d-flex mr-12 extra_width">
                                                            <input name="bidder_award_system" id="bidder_award_system_on" value="1" class="de_active" type="radio"  {{$auction->bidder_award_system==1 ? 'checked' : ''}}>
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <p>{{ __('auctionproduct.manually') }}</p>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="col-lg-12">
                                            <div class="primary_input mb-15">
                                                <label class="primary_input_label" for="auction_end_date">{{ __('auctionproduct.auction_end_date') }} <span class="text-danger">*</span></label>
                                                <div class="primary_datepicker_input">
                                                    <div class="no-gutters input-right-icon">
                                                        <div class="col">
                                                            <div class="">
                                                                <input placeholder="{{__('auctionproduct.auction_end_date')}}" class="primary_input_field primary-input date form-control" id="auction_end_date" type="text" name="auction_end_date" value="{{date('m/d/Y',strtotime($auction->auction_end_date))}}" autocomplete="off" required>
                                                            </div>
                                                        </div>
                                                        <button class="" type="button"> <i class="ti-calendar" id="start-date-icon"></i></button>
                                                    </div>
                                                </div>
                                                <span class="text-danger" id="error_auction_end_date"></span>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row mt-40">
                                        <div class="col-lg-12 text-center">
                                        <button id="submit_btn" type="submit" class="primary-btn fix-gr-bg" data-toggle="tooltip" title="" data-original-title=""> <span class="ti-check"></span> {{__('common.save')}} </button>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
    </div>
</section>
@endsection

