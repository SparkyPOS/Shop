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
        <form action="{{route('auctionproducts.store')}}" enctype="multipart/form-data" method="POST">
        <div class="row">
                @csrf
            <div class="col-lg-8">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="main-title">
                            <h3 class="mb-30"> {{__('auctionproduct.create_new_auction')}} </h3>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div id="formHtml" class="col-lg-12">
                        <div class="white-box">
                                <div class="add-visitor">
                                    <div class="row">

                                        <div class="col-lg-12">
                                            <div class="primary_input mb-25">
                                                <label class="primary_input_label" for="auction_title">{{__('common.title')}} <span class="text-danger">*</span></label>
                                                <input class="primary_input_field" type="text" id="auction_title" name="auction_title" autocomplete="off" value="{{ old('auction_title') }}" placeholder="{{__('common.title')}}">
                                                @error('auction_title')
                                                    <span class="text-danger" id="error_auction_title">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-12">
                                            <div class="primary_input mb-15">
                                                <label class="primary_input_label" for="products">{{ __('common.products') }} <span class="text-danger">*</span></label>
                                                <select id="auctionproducts" name="seller_product_id" class="mb-15">
                                                    <option disabled selected value="">{{ __('marketing.select_products') }}</option>
                                                </select>
                                                @error('seller_product_id')
                                                    <span class="text-danger">{{$message}}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-12">
                                            <div class="primary_input mb-25">
                                                <label class="primary_input_label" for="quantity">{{__('auctionproduct.quantity')}} <span class="text-danger">*</span></label>
                                                <input class="primary_input_field" type="text" id="quantity" name="quantity" min='0' pattern="[0-9]*" autocomplete="off" value="{{ old('quantity') }}" placeholder="{{__('auctionproduct.quantity')}}">
                                                @error('quantity')
                                                    <span class="text-danger" id="error_quantity">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-12">
                                            <div class="primary_input mb-15">
                                                <label class="primary_input_label" for="date">{{__('auctionproduct.date_range')}} <span class="text-danger">*</span></label>
                                                <div class="primary_datepicker_input">
                                                    <div class="no-gutters input-right-icon">
                                                        <div class="col">
                                                            <div class="">
                                                                <input placeholder="{{__('auctionproduct.date_range')}}" class="primary_input_field primary-input form-control" id="date" type="text" name="date" autocomplete="off" readonly required>
                                                            </div>
                                                            <input type="hidden" name="start_date" id="start_date" value="">
                                                            <input type="hidden" name="end_date" id="end_date" value="">
                                                        </div>
                                                        <button class="btn-date" data-id="#date" type="button"> <i class="ti-calendar" id="start-date-icon"></i> </button>
                                                    </div>
                                                </div>
                                                @error('date')
                                                    <span class="text-danger" id="error_date">{{$message}}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-12">
                                            <div class="primary_input mb-25">
                                                <label class="primary_input_label" for="starting_bidding_price">{{__('auctionproduct.starting_bidding_price')}} <span class="text-danger">*</span></label>
                                                <input class="primary_input_field" type="text" id="starting_bidding_price" min='0' pattern="[0-9]*" name="starting_bidding_price" autocomplete="off" value="{{ old('starting_bidding_price') }}" placeholder="{{__('auctionproduct.starting_bidding_price')}}">
                                                @error('starting_bidding_price')
                                                    <span class="text-danger" id="error_starting_bidding_price">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-12">
                                            <div class="primary_input mb-25">
                                                <label class="primary_input_label" for="increment_price">{{__('auctionproduct.increment_price')}} </label>
                                                <input class="primary_input_field" type="text" id="increment_price" min='0' pattern="[0-9]*" value="0"  name="increment_price" autocomplete="off" value="{{ old('increment_price') }}" placeholder="{{__('auctionproduct.increment_price')}}">
                                                @error('increment_price')
                                                    <span class="text-danger" id="error_increment_price">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-12">
                                            <div class="primary_input mb-25">
                                                <label class="primary_input_label" for="reserve_price">{{__('auctionproduct.reserve_price')}} <span class="text-danger">*</span></label>
                                                <input class="primary_input_field" type="text" id="reserve_price" min='0' pattern="[0-9]*" name="reserve_price" autocomplete="off" value="{{ old('reserve_price') }}" placeholder="{{__('auctionproduct.reserve_price')}}">
                                                @error('reserve_price')
                                                    <span class="text-danger" id="error_reserve_price">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-12">
                                            <div class="primary_input mb-25">
                                                <label class="primary_input_label" for="entry_amount">{{__('auctionproduct.entry_amount')}} <span class="text-danger">*</span></label>
                                                <input class="primary_input_field" type="text" id="entry_amount" name="entry_amount" min='0' pattern="[0-9]*" autocomplete="off" value="{{ old('entry_amount') }}" placeholder="{{__('auctionproduct.entry_amount')}}">
                                                @error('entry_amount')
                                                    <span class="text-danger" id="error_entry_amount">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-12">
                                            <div class="primary_input mb-25">
                                                <label class="primary_input_label" for="auction_description">{{__('auctionproduct.auction_description')}} <span class="text-danger">*</span></label>
                                                <div class="primary_input mb-15">
                                                    <textarea class="summernote" name="auction_description"> {{old('auction_description')}}</textarea>
                                                </div>
                                                @error('auction_description')
                                                    <span class="text-danger" id="error_auction_description">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-12">

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
            <div class="col-lg-4">

                <div class="row ">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-4 no-gutters">
                                <div class="main-title">
                                    <h3 class="mb-30">{{__('auctionproduct.auction_status')}}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="white-box">
                        <div class="primary_input mb-15">
                            <label class="primary_input_label" for="date">{{__('common.status')}} <span class="text-danger">*</span></label>
                            <label class="switch_toggle" for="checkbox_status">
                                <input type="checkbox" name="status" id="checkbox_status" value="1" class="status_change_checkbox" checked>
                                <div class="slider round"></div>
                            </label>

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
@push('scripts')
    <script>
        (function($){
            "use strict";
            $(document).ready(function(){
                let today = new Date();
                let dd = String(today.getDate()).padStart(2, '0');
                let mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
                let yyyy = today.getFullYear();
                today = mm + '/' + dd + '/' + yyyy;
                dynamicSelect2WithAjax("#auctionproducts", "{{url('/auctionproducts/seller-products/get-by-ajax')}}", "GET");
                $('#date').daterangepicker({
                    "timePicker": false,
                    "linkedCalendars": false,
                    "autoUpdateInput": false,
                    "showCustomRangeLabel": false,
                    "startDate": today,
                    "endDate": today,
                    "buttonClasses": "primary-btn fix-gr-bg",
                    "applyButtonClasses": "primary-btn fix-gr-bg",
                    "cancelClass": "primary-btn fix-gr-bg",
                }, function(start, end, label) {
                    $('#date').val(start.format('DD-MM-YYYY')+' to ' + end.format('DD-MM-YYYY'));
                    $('#start_date').val(start.format('DD-MM-YYYY'));
                    $('#end_date').val(end.format('DD-MM-YYYY'));
                });
                $(document).on('click', '.product_delete_btn', function(event){
                    event.preventDefault();
                    let this_data = $(this)[0];
                    delete_product_row(this_data);
                });
                function delete_product_row(this_data){
                    let row = this_data.parentNode.parentNode;
                    row.parentNode.removeChild(row);
                }
                $('.summernote').summernote({
                    height: 200,
                    codeviewFilter: true,
                    codeviewIframeFilter: true,
                    disableDragAndDrop:true,
                    callbacks: {
                        onImageUpload: function (files) {
                            sendFile(files, '.summernote')
                        }
                    }
                });
            });
        })(jQuery);
    </script>
@endpush
