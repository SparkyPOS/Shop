@extends('frontend.default.layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{asset(asset_path('frontend/default/css/page_css/checkout.css'))}}" />
    <style>
        .cursor_pointer{
            cursor: pointer;
        }
        .form-control {
            border-radius: 0;
            height: 50px;
            margin-bottom: 17px;
            color: #8f8f8f;
            font-weight: 300;
        }
        .link_style{
            color: inherit!important;
        }
        .link_btn_design{
            font-size: 14px;
            color: #fd0027;
            text-transform: uppercase;
            font-weight: 600;
        }
        .link_btn_design:hover{
            font-size: 14px;
            color: #fd0027;
            text-transform: uppercase;
            font-weight: 600;
        }
        .modal_header_custom_design{
            border-bottom: none!important;
        }
        .cart_table_body{
            margin-top: 25px!important;
        }

        .tablesaw thead tr:first-child th {
             padding: 0 40px;
        }
        .custom_tr{
            padding-top: 10px!important;
        }
        .shipping_delivery_div {
            display: flex;
            grid-gap: 150px;
        }
        .ml-20{
            margin-left: 20px;
        }
        @media (max-width: 540px) {
            .shipping_delivery_div {
                display: block!important;
                margin-bottom: 20px;
            }
        }
    </style>
@endsection
@section('breadcrumb')
    {{ __('defaultTheme.customer_information') }}
@endsection
@section('title')
    {{ __('defaultTheme.checkout') }}
@endsection
@section('content')
    @php
        $postalCodeRequired = false;
        if(isModuleActive('ShipRocket')){
            $postalCodeRequired = true;
        }
    @endphp
    @include('frontend.default.partials._breadcrumb')
    <div id="mainDiv">
        @include('frontend.default.partials._checkout_details')
    </div>
@endsection

@push('scripts')
    <script>
        (function($) {
            "use strict";
            $(document).ready(function() {

                $(document).on('click', '.link_btn_design', function(event){
                    shippingAddressDiv();
                });

                function shippingAddressDiv(){
                    let shipping_address_div = $('.shipping_address_div');
                    let shipping_address_edit_div = $('.shipping_address_edit_div');
                    shipping_address_div.toggleClass('d-none');
                    shipping_address_edit_div.toggleClass('d-none');
                }
                function resetPickupLocationSelect(){
                    const $pickup = $('#pickup_location');
                    if (!$pickup.length) {
                        return;
                    }

                    const $niceWrapper = $pickup.next('.nice-select');
                    if ($niceWrapper.length) {
                        $niceWrapper.remove();
                    }

                    $pickup.css('display', 'block');
                }

                function refreshDeliveryType(deliveryType, pickupLocation = ''){
                    $('#pre-loader').show();
                    $.post("{{ route('frontend.checkout.delivery_type') }}", {
                        _token: "{{ csrf_token() }}",
                        delivery_type: deliveryType,
                        pickup_location: pickupLocation
                    }, function(response){
                        $('#mainDiv').html(response.MainCheckout);
                        $('select').niceSelect();
                        resetPickupLocationSelect();
                        $('#pre-loader').hide();
                    }).fail(function(){
                        $('#pre-loader').hide();
                    });
                }

                $(document).on('change', 'input[name=delivery_type]', function(){
                    const deliveryType = $(this).val();
                    const pickupLocation = $('#pickup_location').val() || '';
                    refreshDeliveryType(deliveryType, pickupLocation);
                });

                $(document).on('change', '#pickup_location', function(){
                    if ($('input[name=delivery_type]:checked').val() === 'pickup_location') {
                        refreshDeliveryType('pickup_location', $(this).val() || '');
                    }
                });

                resetPickupLocationSelect();

                $(document).on('click', '#shipping_methods', function(event){
                    let id = $(this).data('target');
                    $('#'+id).modal('show');
                });

                $(document).on('change', '.shipping_method_select', function(event){
                    $('#pre-loader').show();
                    let id = $(this).data('package');
                    let shipping_method = $(this).val();
                    let url = "{{route('frontend.change_shipping_method')}}";
                    let data = {
                        _token:"{{csrf_token()}}",
                        seller:id,
                        shipping_method:shipping_method,
                    }
                    $('#shipping_methods_'+id).modal('hide');
                    $.post(url,data, function(res){
                        $('#mainDiv').html(res);
                        $('select').niceSelect();
                        resetPickupLocationSelect();
                        $('#pre-loader').hide();
                    });
                });






                $(document).on('submit', '#mainOrderForm', function(event){

                    let is_submit = 0;
                    let postalCodeRequired = "{{$postalCodeRequired}}"
                    $('#error_term_check').text('');
                    $('#error_name').text('');
                    $('#error_address').text('');
                    $('#error_email').text('');
                    $('#error_phone').text('');
                    $('#error_country').text('');
                    $('#error_state').text('');
                    $('#error_city').text('');
                    $('#error_postal_code').text('');
                    $('#error_pickup_location').text('');
                    if(!$('#term_check').is(":checked")){
                        is_submit = 1;
                        $('#error_term_check').text('{{__("validation.please_agree_with_terms")}}');
                    }
                    if($('#name').val() == ''){
                        is_submit = 1;
                        $('#error_name').text('{{__("validation.this_field_is_required")}}');
                    }
                    if(postalCodeRequired == 1 && $('#postal_code').val() == ''){
                        is_submit = 1;
                        $('#error_postal_code').text('{{__("validation.this_field_is_required")}}');
                    }
                    if($('#address').val() == ''){
                        is_submit = 1;
                        $('#error_address').text('{{__("validation.this_field_is_required")}}');
                    }
                    if($('#email').val() == ''){
                        is_submit = 1;
                        $('#error_email').text('{{__("validation.this_field_is_required")}}');
                    }
                    if($('#phone').val() == ''){
                        is_submit = 1;
                        $('#error_phone').text('{{__("validation.this_field_is_required")}}');
                    }
                    if($('#country').val() == ''){
                        is_submit = 1;
                        $('#error_country').text('{{__("validation.this_field_is_required")}}');
                    }
                    if($('#state').val() == ''){
                        is_submit = 1;
                        $('#error_state').text('{{__("validation.this_field_is_required")}}');
                    }
                    if($('#city').val() == ''){
                        is_submit = 1;
                        $('#error_city').text('{{__("validation.this_field_is_required")}}');
                    }
                    if($('input[name=delivery_type]').length && $('input[name=delivery_type]:checked').val() == 'pickup_location' && $('#pickup_location').val() == ''){
                        is_submit = 1;
                        $('#error_pickup_location').text('{{__("validation.this_field_is_required")}}');
                    }
                    if(is_submit === 1){
                        event.preventDefault();
                    }else{

                    }
                });

                $(document).on('change', '#address_id', function(event) {
                    let data = {
                        _token:"{{csrf_token()}}",
                        id: $(this).val()
                    }
                    $('#pre-loader').show();
                    $.post("{{route('frontend.checkout.address.shipping')}}",data, function(res){
                        // $('#mainDiv').html(res.MainCheckout);
                        location.reload();
                        $('select').niceSelect();
                        // $('#pre-loader').hide();
                    });
                });


                $(document).on('change', '#country', function(event) {
                    let country = $('#country').val();
                    $('#pre-loader').show();
                    if (country) {
                        let base_url = $('#url').val();
                        let url = base_url + '/seller/profile/get-state?country_id=' + country;

                        $('#state').empty();

                        $('#state').append(
                            `<option value="">{{__("common.select_from_options")}}</option>`
                        );
                        $('#state').niceSelect('update');
                        $('#city').empty();
                        $('#city').append(
                            `<option value="">{{__("common.select_from_options")}}</option>`
                        );
                        $('#city').niceSelect('update');
                        $.get(url, function(data) {

                            $.each(data, function(index, stateObj) {
                                $('#state').append('<option value="' + stateObj
                                    .id + '">' + stateObj.name + '</option>');
                            });

                            $('#state').niceSelect('update');
                            $('#pre-loader').hide();
                        });
                    }
                });

                $(document).on('change', '#state', function(event){
                    let state = $('#state').val();
                    $('#pre-loader').show();
                    if(state){
                        let base_url = $('#url').val();
                        let url = base_url + '/seller/profile/get-city?state_id=' +state;


                        $('#city').empty();
                        $('#city').append(
                            `<option value="">{{__("common.select_from_options")}}</option>`
                        );
                        $.get(url, function(data){

                            $.each(data, function(index, cityObj) {
                                $('#city').append('<option value="'+ cityObj.id +'">'+ cityObj.name +'</option>');
                            });

                            $('#city').niceSelect('update');
                            $('#pre-loader').hide();
                        });
                    }
                });

            });
        })(jQuery);
    </script>
@endpush
