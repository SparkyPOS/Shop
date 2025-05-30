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

        <div class="row">
            <div class="col-lg-8">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="main-title">
                            <h3 class="mb-30">{{__('auctionproduct.Auction Configuration')}}</h3>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="white_box_50px box_shadow_white mb-20">
                            <form action="{{ route('auctionproducts.configuration.update') }}" method="post">
                                @csrf
                                <div class="row">

                                    <div class="col-lg-12">
                                        <div class="primary_input mb-15">
                                            <label class="primary_input_label" for=""> {{__("auctionproduct.auction_end_check")}} {{__("auctionproduct.cronjob_url")}}</label>
                                            <input class="primary_input_field" placeholder='{{__("auctionproduct.cronjob_url")}}' type="text" value="{{route('auctionproducts.auction-end.cronjob')}}" readonly>
                                        </div>
                                    </div>


                                </div>

                                <div class="row">

                                    <div class="col-lg-6">
                                        <div class="primary_input mb-15">
                                            <label class="primary_input_label" for=""> {{__("auctionproduct.pusher_app_id")}}</label>
                                            <input class="primary_input_field" placeholder='{{__("auctionproduct.pusher_app_id")}}' type="text" name="pusher_app_id" value="{{ !empty($setting->pusher_app_id) ? $setting->pusher_app_id:'' }}" >
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="primary_input mb-15">
                                            <label class="primary_input_label" for=""> {{__("auctionproduct.pusher_key")}}</label>
                                            <input class="primary_input_field" placeholder='{{__("auctionproduct.pusher_key")}}' type="text" name="pusher_key" value="{{ !empty($setting->pusher_key) ? $setting->pusher_key:'' }}" >
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="primary_input mb-15">
                                            <label class="primary_input_label" for=""> {{__("auctionproduct.pusher_secret")}} </label>
                                            <input class="primary_input_field" placeholder='{{__("auctionproduct.pusher_secret")}}' type="text" name="pusher_secret" value="{{ !empty($setting->pusher_secret) ? $setting->pusher_secret:'' }}" >
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="primary_input mb-15">
                                            <label class="primary_input_label" for=""> {{__("auctionproduct.pusher_cluster")}}</label>
                                            <input class="primary_input_field" placeholder='{{__("auctionproduct.pusher_cluster")}}' name="pusher_cluster" type="text" value="{{ !empty($setting->pusher_cluster) ? $setting->pusher_cluster:'' }}" >
                                        </div>
                                    </div>


                                </div>

                                <div class="row">
                                    <div class="col-lg-12 d-flex justify-content-center">
                                            <button class="primary_btn_large">{{ __('common.save') }}</button>
                                    </div>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
@endsection
@push('scripts')
    <script>

    </script>
@endpush
