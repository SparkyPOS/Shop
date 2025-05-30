@extends('frontend.amazy.layouts.app')
@section('title')
    @if(@$product->product->meta_title != null)
        {{ @substr(@$product->product->meta_title,0, 60)}}
    @else
        {{ @substr(@$product->product_name,0, 60)}}
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
    @include('auctionproducts::components.place_bid_modal',compact('auction','max_bid'))

    @php
        $start_date = date('Y/m/d',strtotime($auction->auction_start_date));
        $end_date = date('Y/m/d',strtotime($auction->auction_end_date));
        $current_date = date('Y/m/d');
        $auction_date = '1990/01/01';
        $start = 1;
        if($start_date<= $current_date && $end_date >= $current_date){
            $auction_date = $end_date;
            $start = 1;
        }
        elseif ($start_date >= $current_date && $end_date >= $current_date) {
            $auction_date = $start_date;
            $start = 0;
        }
    @endphp

    <!-- product_details_wrapper::start  -->
    <div class="product_details_wrapper">
        <div class="container">
            <div class="row">
                <div class="col-xl-9">
                    <div class="row">
                        <div class="col-lg-6 col-xl-6">
                            <div class="slider-container slick_custom_container mb_30" id="myTabContent">
                                <div class="slider-for gallery_large">
                                    @if(count($product->product->gallary_images) > 0)
                                        @foreach($product->product->gallary_images as $image)
                                            <div class="item-slick {{$product->product->gallary_images->first()->id == $image->id?'slick-current slick-active':''}}" id="thumb_{{$image->id}}">
                                                <img class="varintImg zoom_01" src="{{showImage($image->images_source)}}" data-zoom-image="{{showImage($image->images_source)}}" alt="{{$product->product_name}}" title="{{$product->product_name}}">
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="item-slick slick-current slick-active" id="thumb_{{$product->id}}">
                                            <img class="varintImg zoom_01" @if ($product->thum_img != null) data-zoom-image="{{showImage($product->thum_img)}}" @else data-zoom-image="{{showImage($product->product->thumbnail_image_source)}}" @endif @if ($product->thum_img != null) src="{{showImage($product->thum_img)}}" @else src="{{showImage($product->product->thumbnail_image_source)}}" @endif alt="{{$product->product_name}}" title="{{$product->product_name}}">
                                        </div>
                                    @endif
                                </div>
                                <div class="slider-nav">
                                    @if(count($product->product->gallary_images) > 0)
                                        @foreach($product->product->gallary_images as $i => $image)
                                            <div class="item-slick {{$i == 0?'slick-active slick-current':''}}">
                                                <img src="{{showImage($image->images_source)}}" alt="{{$product->product_name}}" title="{{$product->product_name}}">
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="item-slick slick-active slick-current">
                                            <img @if ($product->thum_img != null) src="{{showImage($product->thum_img)}}" @else src="{{showImage($product->product->thumbnail_image_source)}}" @endif alt="{{$product->product_name}}" title="{{$product->product_name}}">
                                        </div>
                                    @endif
                                </div>
                                <input type="hidden" id="product_id" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" id="maximum_order_qty" value="{{@$product->product->max_order_qty}}">
                                <input type="hidden" id="minimum_order_qty" value="{{@$product->product->minimum_order_qty}}">
                                <input type="hidden" name="thumb_image" id="thumb_image" value="{{showImage($product->thum_img ? $product->thum_img : $product->product->thumbnail_image_source)}}">
                            </div>
                        </div>
                        <div class="col-lg-6 col-xl-6">
                            <div class="product_content_details mb_20">
                                <h3>{{$product->product_name}}</h3>
                                @if(app('general_setting')->product_subtitle_show)
                                    @if($product->subtitle_1)
                                        <h5>{{$product->subtitle_1}}</h5>
                                    @endif
                                    @if($product->subtitle_2)
                                        <h5>{{$product->subtitle_2}}</h5>
                                    @endif
                                @endif
                                        <p class="stock_text"> <span class="text-uppercase">{{ __('common.listed_date') }}: {{dateConvert(@$product->product->created_at)}}</span>
                                <div class="viendor_text d-flex align-items-center">
                                    <p class="stock_text"> <span class="text-uppercase">{{__('defaultTheme.sku')}}:</span> <span class="stock_value" id="sku_id_li"> {{@$product->skus->where('status',1)->first()->sku->sku??'-'}}</span></p>
                                    <p class="stock_text"> <span class="text-uppercase">{{__('common.category')}}:</span>
                                        @php
                                            $cates = count($product->product->categories);
                                        @endphp
                                        @foreach($product->product->categories as $key => $category)
                                            <span>{{$category->name}}</span>
                                            @if($key + 1 < $cates), @endif
                                        @endforeach
                                    </p>
                                </div>

                                <input type="hidden" name="product_type" class="product_type" value="{{ $product->product->product_type }}">

                                @if($product->product->product_type == 2 && session()->get('item_details') != '')
                                    @foreach (session()->get('item_details') as $key => $item)
                                        @if ($item['attr_id'] === 1)
                                            <div class="product_color_varient mb_20">
                                                <h5 class="font_14 f_w_500 theme_text3  text-capitalize d-block mb_10" id="color_name">{{ $item['name'] }}: {{$item['value'][0]}} </h5>
                                                <div class="color_List d-flex gap_5 flex-wrap">
                                                    <input type="hidden" class="attr_value_name" name="attr_val_name[]" value="{{$item['value'][0]}}">
                                                    <input type="hidden" class="attr_value_id" name="attr_val_id[]" value="{{$item['id'][0]}}-{{$item['attr_id']}}">
                                                    @foreach ($item['value'] as $k => $value_name)
                                                        <label class="round_checkbox d-flex">
                                                            <input id="radio-{{$k}}" name="color_filt" class="attr_val_name  radio_{{ $item['id'][$k] }}" type="radio" color="color" @if ($k === 0) checked @endif data-value="{{ $item['id'][$k] }}" data-name="{{ $item['name'] }}" data-value-key="{{$item['attr_id']}}" value="{{ $value_name }}"/>
                                                            <span class="checkmark colors_{{$k}} class_color_{{ $item['code'][$k] }}">
                                                                <div class="check_bg_color"></div>
                                                            </span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                        @if ($item['attr_id'] != 1)
                                            <div class="product_color_varient mb_20">
                                                <h5 class="font_14 f_w_500 theme_text3  text-capitalize d-block mb_10" id="size_name{{$key}}">{{$item['name']}}: {{$item['value'][0]}}</h5>
                                                <div class="color_List d-flex gap_5 flex-wrap">
                                                    <input type="hidden" class="attr_value_name" data-name="{{ $item['name'] }}" name="attr_val_name[]" value="{{$item['value'][0]}}">
                                                    <input type="hidden" class="attr_value_id" name="attr_val_id[]" value="{{$item['id'][0]}}-{{$item['attr_id']}}">
                                                    @foreach ($item['value'] as $m => $value_name)
                                                        <a class="attr_val_name size_btn not_111 @if ($m === 0) selected_btn @endif" color="not" id="attr_val_variant_id_{{ $item['id'][$m] }}" data-name="{{ $item['name'] }}" data-value-key="{{$item['attr_id']}}" data-value="{{ $item['id'][$m] }}">{{ $value_name }}</a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                    @php
                                        $variant_images = [];
                                        $variant_skus = [];
                                        foreach($product->skus->where('status',1) as $sku){
                                            if(@$sku->sku->variant_image){
                                                $variant_images[] = $sku->sku->variant_image;
                                                $variant_skus[] = $sku->sku->sku;
                                                $variant_product_sku_ids[] = $sku->product_sku_id;
                                            }
                                        }
                                    @endphp
                                    @if(count($variant_images) > 0)
                                    <div class="single_details_content variant_image d-flex flex-wrap align-items-center mb-2 mb-md-3">
                                        <h5>{{__('amazy.Variant images')}}:</h5>
                                        @if(count($variant_images) > 5)
                                            <div class="variant-slider owl-carousel">
                                                @foreach($variant_images as $variant_key => $variant_image)
                                                    <div class="sku_img_div @if($loop->first) active @endif " id="{{$variant_skus[$variant_key]}}" data-id="{{$variant_product_sku_ids[$variant_key]}}" onclick="changeProdDetailsByVariantImg(this)">
                                                        <img src="{{showImage($variant_image)}}" title="{{ $variant_skus[$variant_key] }}" class="img-fluid p-1 var_img_sources " alt="{{ $variant_skus[$variant_key] }}" data-id="{{$variant_skus[$variant_key]}}"/>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                        <div class="img_div_width d-flex">
                                            @foreach($variant_images as $variant_key => $variant_image)
                                             <div class="sku_img_div @if($loop->first) active @endif " id="{{$variant_skus[$variant_key]}}"  data-id="{{$variant_product_sku_ids[$variant_key]}}">
                                                <img src="{{showImage($variant_image)}}" title="{{ $variant_skus[$variant_key] }}" class="img-fluid p-1 var_img_sources " alt="{{ $variant_skus[$variant_key] }}" data-id="{{$variant_skus[$variant_key]}}"/>
                                            </div>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                @endif
                                @endif
                                <!--show wholesale price -->
                                @if(isModuleActive('WholeSale'))
                                    <div class="{{ @$product->skus->where('status',1)->first()->wholeSalePrices->count() ? 'd-flex':'d-none'}}">
                                        <table class="table-sm append_w_s_p_tbl mb-3" width="100%">
                                            <thead>
                                            <tr class="border-bottom ">
                                                <td  class="text-left">
                                                    <label for="" class="control-label">{{__('common.Min QTY')}}</label>
                                                </td>
                                                <td class="text-left">
                                                    <label for="" class="control-label">{{__('common.Max QTY')}}</label>
                                                </td>
                                                <td class="text-left">
                                                    <label for="" class="control-label">{{__('common.unit_price')}}</label>
                                                </td>
                                            </tr>
                                            </thead>
                                            <tbody id="append_w_s_p_all">
                                            </tbody>
                                        </table>
                                    </div>
                                @endif



                                <div class="single_pro_varient">
                                    <h5 class="font_16 f_w_500 theme_text3 pt-2 text-6870" > @if($start == 0) {{__('auctionproduct.auction_starts_in')}}: @else {{__('auctionproduct.auction_ends_in')}}: @endif </h5>
                                    <div class="product_number_count mr_5">
                                        <div id="count_down" class="deals_end_count amazy_date_counter"></div>
                                    </div>
                                </div>

                                <div class="product_info">
                                    <div class="single_pro_varient">
                                        <h5 class="font_16 f_w_500 theme_text3 pt-2 text-6870" >{{__('auctionproduct.starting_bid')}}:</h5>
                                        <div class="product_number_count mr_5">
                                            <span class="font_17">{{ getNumberTranslate(single_price($auction->starting_bidding_price)) }}</span>
                                        </div>
                                    </div>

                                    @php
                                        $highestBid = 0;
                                        $counter=0;
                                        if(!empty($auction->auction_bid) && $auction->auction_bid->count() > 0){
                                            foreach ($auction->auction_bid as  $bid) {
                                                $counter++;
                                            }
                                            $highestBid = $auction->auction_bid[$counter-1]->bid_amount;
                                        }
                                    @endphp

                                    <div class="single_pro_varient m-n-30" >
                                        <h5 class="font_16 f_w_500 theme_text3 pt-2 text-6870" >{{__('auctionproduct.highest_bid')}}:</h5>
                                        <div class="product_number_count mr_5">
                                            <span class="font_17">{{ getNumberTranslate(single_price($highestBid)) }}</span>
                                        </div>
                                    </div>

                                    <div class="row mt_30 " id="add_to_cart_div">
                                        @if($start == 1)
                                        @if($is_entry_amount_paid == 0)
                                           <div class="col-lg-12 mb-3">
                                                <p>{{__('auctionproduct.to_start_bidding_on_this_auction_you_need_to_pay_entry_amount')}} {{ single_price($auction->entry_amount) }}</p>
                                           </div>
                                        @endif
                                            @if($auction->status == 1 && $auction->auction_end_date > date('Y-m-d'))
                                                @if($is_entry_amount_paid == 1)

                                                    <div class="col-md-6">
                                                        <button type="button" id="placeBid" class="amaz_primary_btn3 mb_20  w-100 text-center justify-content-center text-uppercase buy_now_btn" data-id="{{$auction->id}}" data-type="product">{{__('auctionproduct.place_bid')}}</button>
                                                    </div>
                                                @elseif($is_entry_amount_paid == 2)
                                                     @auth
                                                         <div class="col-md-6">
                                                             <a href="javascript:void(0)"  class="amaz_primary_btn3 mb_20  w-100 text-center justify-content-center text-uppercase buy_now_btn">{{__('auctionproduct.panding_entry_amount')}}</a>
                                                         </div>
                                                    @endauth
                                                    @guest
                                                        <div class="col-md-6">
                                                            <a href="{{url('/login')}}"  class="amaz_primary_btn3 mb_20  w-100 text-center justify-content-center text-uppercase buy_now_btn">{{__('auctionproduct.pay_entry_amount')}}</a>
                                                        </div>
                                                    @endguest
                                                @else
                                                    @auth
                                                     <div class="col-md-6">
                                                         <a href="{{ route('auction.payentryAmount',$auction->id) }}"  class="amaz_primary_btn3 mb_20  w-100 text-center justify-content-center text-uppercase buy_now_btn">{{__('auctionproduct.pay_entry_amount')}}</a>
                                                     </div>
                                                    @endauth

                                                    @guest
                                                        <div class="col-md-6">
                                                            <a href="{{url('/login')}}"  class="amaz_primary_btn3 mb_20  w-100 text-center justify-content-center text-uppercase buy_now_btn">{{__('auctionproduct.pay_entry_amount')}}</a>
                                                        </div>
                                                    @endguest

                                                @endif
                                            @else
                                                <div class="col-md-6">
                                                    <button type="button" disabled class="amaz_primary_btn style2 mb_20  add_to_cart text-uppercase flex-fill text-center w-100">{{__('auctionproduct.place_bid')}}</button>
                                                </div>
                                            @endif
                                            <div class="col-md-6">
                                                <button class="amaz_primary_btn style2 mb_20  bid_histoy text-uppercase flex-fill text-center w-100" data-url="{{ route('auction.auctionHistory',$auction->id) }}">
                                                    {{ __('Bid History') }}
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @php
                                $both_buy_product = null;
                                if(@$product->product->display_in_details == 1){
                                    if($product->up_sales->count()){
                                        $both_buy_product = @$product->up_sales[0]->up_seller_products[0];
                                    }
                                }else{
                                    if($product->cross_sales->count()){
                                        $both_buy_product = @$product->cross_sales[0]->cross_seller_products[0];
                                    }
                                }
                            @endphp

                            @php
                                $sharelinks = Share::currentPage()->facebook()->twitter()->linkedin()->whatsapp()->telegram()->reddit()->getRawLinks();
                            @endphp
                            <div class="contact_wiz_box mt_20">
                                <span class="contact_box_title font_16 f_w_500 d-block lh-1 ">{{__('defaultTheme.share_on')}}:</span>
                                <div class="contact_link">
                                   <a target="_blank" href="{{ $sharelinks['facebook'] }}">
                                       <i class="fab fa-facebook"></i>
                                   </a>
                                   <a target="_blank" href="{{ $sharelinks['twitter'] }}">
                                       <i class="fab fa-twitter"></i>
                                   </a>
                                   <a target="_blank" href="{{ $sharelinks['linkedin'] }}">
                                       <i class="fab fa-linkedin-in"></i>
                                   </a>
                                   <a target="_blank" href="{{ $sharelinks['whatsapp'] }}">
                                       <i class="fab fa-whatsapp"></i>
                                   </a>
                                   <a target="_blank" href="{{ $sharelinks['telegram'] }}">
                                       <i class="fab fa-telegram-plane"></i>
                                   </a>
                                    <a target="_blank" href="{{ $sharelinks['reddit'] }}">
                                        <i class="ti-reddit"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="product_details_dec mb_76">
                                <div class="product_details_dec_header">
                                    <h4 class="font_20 f_w_400 m-0 ">{{__('common.description')}}</h4>
                                </div>
                                <div class="product_details_dec_body">
                                     {!! $auction->auction_description !!}
                                    <hr>
                                    @php
                                        echo $product->product->description;
                                    @endphp
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- product_details_wrapper::end  -->
    @if(@$product->hasDeal)
        <input type="hidden" id="discount_type" value="{{@$product->hasDeal->discount_type}}">
        <input type="hidden" id="discount" value="{{@$product->hasDeal->discount}}">
    @else
        @if(@$product->hasDiscount == 'yes')
        <input type="hidden" id="discount_type" value="{{$product->discount_type}}">
        <input type="hidden" id="discount" value="{{$product->discount}}">
        @else
        <input type="hidden" id="discount_type" value="{{$product->discount_type}}">
        <input type="hidden" id="discount" value="0">
        @endif
    @endif
    <!--for whole sale price -->
    @if(isModuleActive('WholeSale'))
        <input type="hidden" id="getWholesalePrice" value="@if(@$product->skus->where('status',1)->first()->wholeSalePrices->count()){{ json_encode(@$product->skus->where('status',1)->first()->wholeSalePrices) }} @else 0 @endif">
    @endif
    <input type="hidden" id="isWholeSaleActive" value="{{isModuleActive('WholeSale')}}">
    <input type="hidden" id="isMultiVendorActive" value="{{isModuleActive('MultiVendor')}}">

    <div id="showHistor"></div>
@endsection
@push('scripts')
<script src="{{ asset(asset_path('frontend/default/js/zoom.js')) }}"></script>
<script src="{{ asset(asset_path('frontend/default/js/lightbox.js')) }}"></script>
<script>
    (function($){
        "use strict";
        $(document).ready(function(){
            if (window.matchMedia('(min-width: 500px)').matches && $(".zoom_01").length > 0) {
                zoom_enable();
            }else{
                $('.varintImg').removeClass('zoom_01');
            }
            function zoom_enable(){
                $(".zoom_01").elevateZoom({
                    zoomEnabled: true,
                    zoomWindowHeight:120,
                    zoomWindowWidth:120,
                    zoomLevel:.9
                });
            }
            let getWholesalePrice = '';
            if($('#isWholeSaleActive').val() == 1 && $('#getWholesalePrice').val() != 0){
                 getWholesalePrice = JSON.parse($('#getWholesalePrice').val());
                if(getWholesalePrice){
                    appendWholeSaleP();
                    $('.append_w_s_p_tbl').removeClass('d-none');
                }else {
                    $('.append_w_s_p_tbl').addClass('d-none');
                }
            }else{
                 getWholesalePrice = null;
            }
            // both_buy_price($('#base_sku_price').val().trim());
            function both_buy_price(product_price){
                let both_buy_price = $('#both_buy_price').val();
                let qty = $('.qty').data('value');
                let total_product_price = parseFloat(product_price) * parseInt(qty);
                let total = currency_format(total_product_price + parseFloat(both_buy_price));
                $('#both_buy_price_show').text(total);
            }
            $(document).on('click', '.page_link', function(event){
                event.preventDefault();
                let page = $(this).attr('href').split('page=')[1];
                fetch_data(page);
            });
            function fetch_data(page){
                $('#pre-loader').show();
                let url = "{{route('frontend.product.reviews.get-data')}}" + '?product_id='+ "{{$product->id}}" +'&page=' + page;
                if(page != 'undefined'){
                    $.ajax({
                        url: url,
                        success:function(data)
                        {
                            $('#Reviews').html(data);
                            $('#pre-loader').hide();
                        }
                    });
                }else{
                    toastr.warning('this is undefined');
                }
            }
            let productType = $('.product_type').val();
            if (productType == 2) {
                '@if (session()->has('item_details') != '')'+
                    '@foreach (session()->get('item_details') as $key => $item)'+
                        '@if ($item['attr_id'] === 1)'+
                            '@foreach ($item['value'] as $k => $value_name)'+
                                $(".colors_{{$k}}").css("background", "{{ $item['code'][$k]}}");
                            '@endforeach'+
                        '@endif'+
                    '@endforeach'+
                '@endif'
            }
            $(document).on('click', '.attr_val_name', function(){
                $(this).parent().parent().find('.attr_value_name').val($(this).attr('data-value')+'-'+$(this).attr('data-value-key'));
                $(this).parent().parent().find('.attr_value_id').val($(this).attr('data-value')+'-'+$(this).attr('data-value-key'));
                if ($(this).attr('color') == "color") {
                    $(this).closest('.color_List').find('.attr_clr').removeClass('selected_btn');
                }
                if ($(this).attr('color') == "not") {
                    $(this).closest('.color_List').find('.not_111').removeClass('selected_btn');
                }
                $(this).addClass('selected_btn');
                get_price_accordint_to_sku();
            });



            $(document).on('click', '.item-slick', function(event){
                let logo = $(this).children().attr("src");
                $('.varintImg').attr("src", logo);
                $('.varintImg').data("zoom-image", logo);
                $('.varintImg').addClass('zoom_01');
                zoom_enable();
            });
            $(document).on('click', '.add_to_cart_btn', function(event){
                event.preventDefault();
                let showData = {
                    'name' : "{{ @$product->product_name }}",
                    'url' : "{{singleProductURL(@$product->seller->slug, @$product->slug)}}",
                    'price' : currency_format($('#final_price').val()),
                    'thumbnail' : $('#thumb_image').val()
                };
                addToCart($('#product_sku_id').val(),$('#seller_id').val(),$('#qty').data('value'),$('#base_sku_price').val().trim(),$('#shipping_type').val(),'product',showData);
            });
            $(document).on('click', '#both_buy_btn', function (event){
                event.preventDefault();
                let product_sku_id = $(this).data('sku_id');
                let product_id = $(this).data('product_id');
                let qty = $(this).data('qty');
                let seller_id = $(this).data('seller_id');
                addToCart(product_sku_id, seller_id, qty, $('#both_buy_price').val(), '0', 'product');
                addToCart($('#product_sku_id').val(),$('#seller_id').val(),$('#qty').data('value'),$('#base_sku_price').val().trim(),$('#shipping_type').val(),'product');
            });
            $(document).on('click', '#wishlist_btn', function(event){
                event.preventDefault();
                let product_id = $(this).data('product_id');
                let seller_id = $(this).data('seller_id');
                let type = "product";
                let is_login = $('#login_check').val();
                if(is_login == 1){
                    addToWishlist(product_id, seller_id, type);
                }else{
                    toastr.warning("{{__('defaultTheme.please_login_first')}}","{{__('common.warning')}}");
                }
            });
            $(document).on('click', '#add_to_compare_btn_modify', function(event){
                event.preventDefault();
                let product_sku_class = $(this).data('product_sku_id');
                let product_sku_id = $(product_sku_class).val();
                let product_type = $(this).data('product_type');
                addToCompare(product_sku_id, product_type, 'product');
            });
            $(document).on('click', '.qtyChange', function(event){
                event.preventDefault();
                let value = $(this).data('value');
                qtyChange(value);
            });
            function qtyChange(val){
                $('.cart-qty-minus').prop('disabled',false);
                let available_stock = $('#availability').html();
                let stock_manage_status = $('#stock_manage_status').val();
                let maximum_order_qty = $('#maximum_order_qty').val();
                let minimum_order_qty = $('#minimum_order_qty').val();
                let qty = $('#qty').data('value');
                if (stock_manage_status != 0) {
                    if(val == '+'){
                        if (parseInt(qty) < parseInt(available_stock)) {
                            if(maximum_order_qty != ''){
                                if(parseInt(qty) < parseInt(maximum_order_qty)){
                                let qty1 = parseInt(++qty);
                                $('#qty').val(numbertrans(qty1));
                                $('#qty').data('value',qty1);
                                totalValue(qty1, '#base_price','#total_price', getWholesalePrice);
                                }else{
                                    toastr.warning('{{__("defaultTheme.maximum_quantity_limit_is")}}'+maximum_order_qty+'.', '{{__("common.warning")}}');
                                }
                            }else{
                                let qty1 = parseInt(++qty);
                                $('#qty').val(numbertrans(qty1));
                                $('#qty').data('value',qty1);
                                totalValue(qty1, '#base_price','#total_price', getWholesalePrice);
                            }
                        }else{
                            toastr.error("{{__('defaultTheme.no_more_stock')}}", "{{__('common.error')}}");
                        }
                    }
                    if(val == '-'){
                        if (parseInt(qty) <= parseInt(available_stock)) {
                            if(minimum_order_qty != ''){
                                if(parseInt(qty) > parseInt(minimum_order_qty)){
                                    if(qty>1){
                                        let qty1 = parseInt(--qty)
                                        $('#qty').val(numbertrans(qty1));
                                        $('#qty').data('value',qty1);
                                        totalValue(qty1, '#base_price','#total_price', getWholesalePrice)
                                        $('.cart-qty-minus').prop('disabled',false);
                                    }else{
                                        $('.cart-qty-minus').prop('disabled',true);
                                    }
                                }else{
                                    toastr.warning('{{__("defaultTheme.minimum_quantity_Limit_is")}}'+minimum_order_qty+'.', '{{__("common.warning")}}')
                                }
                            }else{
                                if(parseInt(qty)>1){
                                    let qty1 = parseInt(--qty)
                                    $('#qty').val(numbertrans(qty1));
                                    $('#qty').data('value',qty1);
                                    totalValue(qty1, '#base_price','#total_price', getWholesalePrice)
                                    $('.cart-qty-minus').prop('disabled',false);
                                }else{
                                    $('.cart-qty-minus').prop('disabled',true);
                                }
                            }
                        }else{
                            toastr.error("{{__('defaultTheme.no_more_stock')}}", "{{__('common.error')}}");
                        }
                    }
                }
                else {
                    if(val == '+'){
                        if(maximum_order_qty != ''){
                            if(parseInt(qty) < parseInt(maximum_order_qty)){
                            let qty1 = parseInt(++qty);
                            $('#qty').val(numbertrans(qty1));
                            $('#qty').data('value',qty1);
                            totalValue(qty1, '#base_price','#total_price', getWholesalePrice);
                            }else{
                                toastr.warning('{{__("defaultTheme.maximum_quantity_limit_is")}}'+maximum_order_qty+'.', '{{__("common.warning")}}')
                            }
                        }else{
                            let qty1 = parseInt(++qty);
                            $('#qty').val(numbertrans(qty1));
                            $('#qty').data('value',qty1);
                            totalValue(qty1, '#base_price','#total_price', getWholesalePrice);
                        }
                    }
                    if(val == '-'){
                        if(minimum_order_qty != ''){
                            if(parseInt(qty) > parseInt(minimum_order_qty)){
                                if(qty>1){
                                    let qty1 = parseInt(--qty)
                                    $('#qty').val(numbertrans(qty1));
                                    $('#qty').data('value',qty1);
                                    totalValue(qty1, '#base_price','#total_price', getWholesalePrice)
                                    $('.cart-qty-minus').prop('disabled',false);
                                }else{
                                    $('.cart-qty-minus').prop('disabled',true);
                                }
                            }else{
                                toastr.warning('{{__("defaultTheme.minimum_quantity_Limit_is")}}'+minimum_order_qty+'.', '{{__("common.warning")}}')
                            }
                        }else{
                            if(parseInt(qty)>1){
                                let qty1 = parseInt(--qty)
                                $('#qty').val(numbertrans(qty1));
                                $('#qty').data('value',qty1);
                                totalValue(qty1, '#base_price','#total_price', getWholesalePrice)
                                $('.cart-qty-minus').prop('disabled',false);
                            }else{
                                $('.cart-qty-minus').prop('disabled',true);
                            }
                        }
                    }
                }
            }
            function totalValue(qty, main_price, total_price, getWholesalePrice){
                if($('#isWholeSaleActive').val() == 1 && getWholesalePrice != null){
                    let max_qty='',min_qty='',selling_price='';
                    for (let i = 0; i < getWholesalePrice.length; ++i) {
                        max_qty = getWholesalePrice[i].max_qty;
                        min_qty = getWholesalePrice[i].min_qty;
                        selling_price = getWholesalePrice[i].selling_price;
                        let main_price = 0;
                        if ( (min_qty<=qty) && (max_qty>=qty) ){
                            main_price = selling_price;
                        }
                        else if(max_qty < qty){
                            main_price = selling_price;
                        }
                        else if(main_price=='#base_price'){
                             main_price = $('#base_sku_price').val().trim();
                        }
                    }
                    let discount = $('#discount').val();
                    let discount_type = $('#discount_type').val();
                    if (discount_type == 0) {
                        discount = (main_price * discount) / 100;
                    }
                    let base_sku_price = (main_price - discount);
                }else {
                    let base_sku_price = $('#base_sku_price').val().trim();
                }
                let value = parseInt(qty) * parseFloat(base_sku_price);
                $(total_price).html(currency_format(value));
                both_buy_price(base_sku_price);
                $('#final_price').val(value);
            }
            function get_price_accordint_to_sku(){
                let value = $("input[name='attr_val_name[]']").map(function(){return $(this).val();}).get();
                let id = $("input[name='attr_val_id[]']").map(function(){return $(this).val();}).get();
                let product_id = $("#product_id").val();
                let user_id = $('#seller_id').val();
                $('#pre-loader').show();
                $.post("{{ route('seller.get_seller_product_sku_wise_price') }}", {_token:'{{ csrf_token() }}', id:id, product_id:product_id, user_id:user_id}, function(response){
                    if (response != 0) {
                        let discount_type = $('#discount_type').val();
                        let discount = $('#discount').val();
                        let qty = $('.qty').data('value');
                        if(typeof response.data.whole_sale_prices != 'undefined'){
                            if(response.data.whole_sale_prices.length > 0){
                                getWholesalePrice = response.data.whole_sale_prices;
                                if(getWholesalePrice){
                                    appendWholeSaleP();
                                    $('.append_w_s_p_tbl').removeClass('d-none');
                                }else {
                                    $('.append_w_s_p_tbl').addClass('d-none');
                                }
                            }
                        }
                        calculatePrice(response.data.selling_price, discount, discount_type, qty);
                        $('#sku_id_li').text(response.data.sku.sku);
                        let color = response.data.sku.sku.split('-');
                        $(".sku_img_div").removeClass('active');
                        $("#"+response.data.sku.sku).addClass('active');
                        let variant_img = response.data.sku.variant_image;
                        if(variant_img){
                        if(variant_img.includes('amazonaws.com')){
                            let image_path = variant_img;
                        }else if(variant_img.includes('digitaloceanspaces.com')){
                            let image_path = variant_img;
                        }else if(variant_img.includes('drive.google.com')){
                            let image_path = variant_img;
                        }else if(variant_img.includes('wasabisys.com')){
                            let image_path = variant_img;
                        }else if(variant_img.includes('backblazeb2.com')){
                            let image_path = variant_img;
                        }else if(variant_img.includes('dropboxusercontent.com')){
                            let image_path = variant_img;
                        }else if(variant_img.includes('storage.googleapis.com')){
                            let image_path = variant_img;
                        }else if(variant_img.includes('contabostorage.com')){
                            let image_path = variant_img;
                        }else if(variant_img.includes('b-cdn.net')){
                            let image_path = variant_img;
                        }else{
                            let image_path="{{asset(asset_path(''))}}/" + variant_img;
                        }
                        $('.varintImg').attr("src", image_path);
                        $('.varintImg').data("zoom-image", image_path);
                        $('.varintImg').addClass('zoom_01');
                        zoom_enable();
                    }
                    $(response.data.product.variantDetails).each(function( key,index ) {
                        if(response.data.product.variantDetails.length > 1){
                            $.each(color, function(i, v) {
                                let isLastElement = i == color.length -1;
                                if (isLastElement) {
                                    $('#color_name').text(index.name +': ' + v);
                                }else{
                                    $('#size_name'+key).text(index.name +': ' + color[key+1]);
                                }
                            });
                        }else{
                            if (index.attr_id == 1) {
                                $('#color_name').text(index.name +': ' + color[1]);
                            }else if (index.attr_id == 2) {
                                $('#size_name').text(index.name +': ' + color[1] + '-'+ color[2]);
                            }else{
                                $('#size_name').text(index.name +': ' + color[1]);
                            }
                        }
                    });
                        $('#product_sku_id').val(response.data.id);
                        if (response.data.product_stock == 0) {
                            $('#availability').html("{{__('defaultTheme.unlimited')}}");
                        }else{
                            $('#availability').html(response.data.product_stock);
                        }
                        if(response.data.product.stock_manage == 1 && parseInt(response.data.product_stock) >= parseInt(response.data.product.product.minimum_order_qty) || response.data.product.stock_manage == 0){
                            $('#add_to_cart_div').html(`
                                <div class="col-md-6">
                                    <button type="button" id="add_to_cart_btn" class="amaz_primary_btn style2 mb_20  add_to_cart text-uppercase add_to_cart_btn flex-fill text-center w-100">{{__('defaultTheme.add_to_cart')}}</button>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" id="butItNow" class="amaz_primary_btn3 mb_20  w-100 text-center justify-content-center text-uppercase buy_now_btn" data-id="{{$product->id}}" data-type="product">{{__('common.buy_now')}}</button>
                                </div>
                            `);
                            $('#stock_div').html(`<span class="stoke_badge">{{__('common.in_stock')}}</span>`);
                            if($('#isMultiVendorActive').val() == 1){
                                $('#cart_footer_mobile').html(`
                                    <a href="
                                        @if ($product->seller->slug)
                                            {{route('frontend.seller',$product->seller->slug)}}
                                        @else
                                            {{route('frontend.seller',base64_encode($product->seller->id))}}
                                        @endif
                                    " class="d-flex flex-column justify-content-center product_details_icon">
                                        <i class="ti-save"></i>
                                        <span>{{__('common.store')}}</span>
                                    </a>
                                    <button type="button" class="product_details_button style1 buy_now_btn" data-id="{{$product->id}}" data-type="product">
                                        <span>{{__('common.buy_now')}}</span>
                                    </button>
                                    <button class="product_details_button add_to_cart_btn" type="button">{{__('common.add_to_cart')}}</button>
                                `);
                            }else{
                                if($('#isMultiVendorActive').val() == 1){
                                    $('#cart_footer_mobile').html(`
                                        <a href="
                                            @if ($product->seller->slug)
                                                {{route('frontend.seller',$product->seller->slug)}}
                                            @else
                                                {{route('frontend.seller',base64_encode($product->seller->id))}}
                                            @endif
                                        " class="d-flex flex-column justify-content-center product_details_icon">
                                            <i class="ti-save"></i>
                                            <span>{{__('common.store')}}</span>
                                        </a>
                                        <button type="button" class="product_details_button style1 buy_now_btn" data-id="{{$product->id}}" data-type="product">
                                            <span>{{__('common.buy_now')}}</span>
                                        </button>
                                        <button class="product_details_button add_to_cart_btn" type="button">{{__('common.add_to_cart')}}</button>
                                    `);
                                }else{
                                    $('#cart_footer_mobile').html(`
                                        <button type="button" class="product_details_button style1 buy_now_btn" data-id="{{$product->id}}" data-type="product">
                                            <span>{{__('common.buy_now')}}</span>
                                        </button>
                                        <button class="product_details_button add_to_cart_btn" type="button">{{__('common.add_to_cart')}}</button>
                                    `);
                                }
                            }
                        }
                        else{
                            $('#add_to_cart_div').html(`
                                <div class="col-md-6">
                                    <button type="button" disabled class="amaz_primary_btn style2 mb_20 add_to_cart text-uppercase flex-fill text-center w-100">{{__('defaultTheme.out_of_stock')}}</button>
                                </div>
                            `);
                            $('#stock_div').html(`<span class="stokeout_badge">{{__('defaultTheme.out_of_stock')}}</span>`);
                            toastr.warning("{{__('defaultTheme.out_of_stock')}}");
                            $('#cart_footer_mobile').html(`
                                <button type="button" class="product_details_button style1" disabled>
                                    <span>{{__('defaultTheme.out_of_stock')}}</span>
                                </button>
                                <button type="button" class="product_details_button" disabled>{{__('defaultTheme.out_of_stock')}}</button>
                            `);
                        }
                    }else {
                        toastr.error("{{__('defaultTheme.no_stock_found_for_this_seller')}}", "{{__('common.error')}}");
                    }
                    $('#pre-loader').hide();
                });
            }

            function calculatePrice(main_price, discount, discount_type, qty){


                let total_price = 0;
                if($('#isWholeSaleActive').val() == 1 && getWholesalePrice != null){
                    let max_qty='',min_qty='',selling_price='';
                    for (let i = 0; i < getWholesalePrice.length; ++i) {
                        max_qty = getWholesalePrice[i].max_qty;
                        min_qty = getWholesalePrice[i].min_qty;
                        selling_price = getWholesalePrice[i].selling_price;

                        if ( (min_qty<=qty) && (max_qty>=qty) ){
                            main_price = selling_price;
                        }
                        else if(max_qty < qty){
                            main_price = selling_price;
                        }
                    }
                }
                if (discount_type == 0) {
                    discount = (main_price * discount) / 100;
                }
                total_price = (main_price - discount);
                $('#total_price').text(currency_format((total_price * qty)));
                both_buy_price((total_price));
                $('#base_sku_price').val(total_price);
                $('#final_price').val(total_price);
            }
            function appendWholeSaleP(){
                $('#append_w_s_p_all').empty();
                $.each(getWholesalePrice, function(index, value) {
                    $('#append_w_s_p_all').append(`
                    <tr class="border-bottom">
                        <td class="text-left">
                            <span>${numbertrans(value.min_qty)}</span>
                        </td>
                        <td class="text-left">
                            <span>${numbertrans(value.max_qty)}</span>
                        </td>
                        <td class="text-left">
                            <span>${currency_format(value.selling_price)}</span>
                        </td>
                    </tr>
                `);
                });
            }

            $(document).on("click","#follow_seller_btn" ,function(event){
                event.preventDefault();
                let id = $('#seller_id').val();
                let data = {
                    seller_id: id,
                    _token : "{{csrf_token()}}"
                }
                $('#pre-loader').show();
                $(this).prop("disabled",true);
                $.post("{{route('frontend.follow_seller')}}",data,function(response){
                    if(response.message == 'success'){
                        toastr.success("{{__('amazy.Followed Successfully')}}","{{__('common.success')}}");
                        $('#follow_seller_btn').text("{{__('amazy.Followed')}}");
                        $('#follow_seller_count').text(numbertrans(response.result));
                    }
                    else{
                        $(this).prop("disabled",false);
                        toastr.error("{{__('amazy.Not Followed')}}","{{__('common.error')}}");
                    }
                    $('#pre-loader').hide();
                });
            });

            // Showing Review Image Bigger
            $(document).on('click', '.lightboxed', function (event) {
                let selector = $('iframe');
                $.each(selector, function () {
                    let src = $(this).attr('src');
                    let selector = $(this).closest('.lightboxed--frame');
                    let caption = selector.find('.lightboxed--caption').text();
                    $(this).remove();
                    selector.append("<img src='" + src + "' data-caption='" + caption + "'>");
                });
            });

        });
    })(jQuery);
</script>
<script>
    function zoom_enable_for_variant(){
                $(".zoom_01").elevateZoom({
                    zoomEnabled: true,
                    zoomWindowHeight:120,
                    zoomWindowWidth:120,
                    zoomLevel:.9
                });
    }
    function both_variant_buy_price(product_price){
                let both_buy_price = $('#both_buy_price').val();
                let qty = $('.qty').data('value');
                let total_product_price = parseFloat(product_price) * parseInt(qty);
                let total = currency_format(total_product_price + parseFloat(both_buy_price));
                $('#both_buy_price_show').text(total);
    }
    function calculateVariantProductPrice(main_price, discount, discount_type, qty){

            let total_price = 0;
            if($('#isWholeSaleActive').val() == 1 && getWholesalePrice != null){
                let max_qty='',min_qty='',selling_price='';
                for (let i = 0; i < getWholesalePrice.length; ++i) {
                    max_qty = getWholesalePrice[i].max_qty;
                    min_qty = getWholesalePrice[i].min_qty;
                    selling_price = getWholesalePrice[i].selling_price;

                    if ( (min_qty<=qty) && (max_qty>=qty) ){
                        main_price = selling_price;
                    }
                    else if(max_qty < qty){
                        main_price = selling_price;
                    }
                }
            }
            if (discount_type == 0) {
                discount = (main_price * discount) / 100;
            }
            total_price = (main_price - discount);
            $('#total_price').text(currency_format((total_price * qty)));
            both_variant_buy_price((total_price));
            $('#base_sku_price').val(total_price);
            $('#final_price').val(total_price);
    }

    function changeProdDetailsByVariantImg(element){
            let color_id = $(element).children("img").data("id");
            let attr_id = $( '.attr_val_name' ).data("value-key");
            $(".sku_img_div").removeClass('active');
            $("#"+color_id).addClass('active');
            let value = $("input[name='attr_val_name[]']").map(function(){return $(this).val();}).get();
            let id = $("input[name='attr_val_id[]']").map(function(){return $(this).val();}).get();

            let product_id = $(element).data("id");
            let user_id = $('#seller_id').val();
            $('#pre-loader').show();

            $.post("{{ route('seller.get_seller_product_variant_wise_price') }}", {_token:'{{ csrf_token() }}', id:id, product_id:product_id, user_id:user_id}, function(response){
                if (response != 0) {
                    let discount_type = $('#discount_type').val();
                    let discount = $('#discount').val();
                    let qty = $('.qty').data('value');
                    if(typeof response.data.whole_sale_prices != 'undefined'){
                        if(response.data.whole_sale_prices.length > 0){
                            getWholesalePrice = response.data.whole_sale_prices;
                            if(getWholesalePrice){
                                appendWholeSaleP();
                                $('.append_w_s_p_tbl').removeClass('d-none');
                            }else {
                                $('.append_w_s_p_tbl').addClass('d-none');
                            }
                        }
                    }
                    calculateVariantProductPrice(response.data.selling_price, discount, discount_type, qty);
                    $('#sku_id_li').text(response.data.sku.sku);
                    let color = response.data.sku.sku.split('-');
                    $("#"+response.data.sku.sku).addClass('active');
                    let variant_img = response.data.sku.variant_image;

                    if(variant_img){
                    if(variant_img.includes('amazonaws.com')){
                        let image_path = variant_img;
                    }else if(variant_img.includes('digitaloceanspaces.com')){
                        let image_path = variant_img;
                    }else if(variant_img.includes('drive.google.com')){
                        let image_path = variant_img;
                    }else if(variant_img.includes('wasabisys.com')){
                        let image_path = variant_img;
                    }else if(variant_img.includes('backblazeb2.com')){
                        let image_path = variant_img;
                    }else if(variant_img.includes('dropboxusercontent.com')){
                        let image_path = variant_img;
                    }else if(variant_img.includes('storage.googleapis.com')){
                        let image_path = variant_img;
                    }else if(variant_img.includes('contabostorage.com')){
                        let image_path = variant_img;
                    }else if(variant_img.includes('b-cdn.net')){
                        let image_path = variant_img;
                    }else{
                        let image_path;
                        if(window.location.origin.includes('localhost')){
                            let strurl = $(location).attr("pathname").split('/');
                            image_path = window.location.origin+'/'+strurl[1]+'/public/' + variant_img;
                        }else{
                            image_path = window.location.origin+'/public/' + variant_img;
                        }
                    }
                    $('.varintImg').attr("src", image_path);
                    $('.varintImg').data("zoom-image", image_path);
                    $('.varintImg').addClass('zoom_01');
                    zoom_enable_for_variant();
                }

                let globalSelector=0;
                let globalColorSelector=0;
                $(response.data.product.skus).each(function(key,index){
                    if(response.data.product_sku_id==index.product_sku_id){
                        $(index.product_variations).each(function(key2, index2){
                            if(index2.attribute.name=='Shoe Size'){
                                globalSelector=1;
                                $('.attr_val_name').removeClass('selected_btn');
                                $('#attr_val_variant_id_'+index2.attribute_value.id).addClass('selected_btn');
                            }
                            if(index2.attribute.name=='Color'){
                                if(globalColorSelector==0){
                                    $('.attr_val_name').removeAttr("checked");
                                    globalColorSelector=1;
                                }
                                $('.radio_'+index2.attribute_value.id).attr('checked','checked');
                            }
                            if(index2.attribute.name=='Size'){
                                if(globalSelector==0){
                                    $('.attr_val_name').removeClass('selected_btn');
                                    globalSelector=1;
                                }
                                $('#attr_val_variant_id_'+index2.attribute_value.id).addClass('selected_btn');
                            }
                        })
                    }
                });

                $(response.data.product.variantDetails).each(function( key,index ) {
                    if(response.data.product.variantDetails.length == 1){
                        $.each(color, function(i, v) {
                            let isLastElement = i == color.length -1;
                            if (isLastElement) {
                                $('#color_name').text(index.name +': ' + v);
                            }else{
                                $('#size_name'+key).text(index.name +': ' + color[key+1]);
                            }
                        });
                    }else{
                        if (index.attr_id == 1) {
                            $('#color_name').text(index.name +': ' + color[2]);
                        }else if (index.attr_id == 2) {
                            $('#size_name').text(index.name +': ' + color[1] + '-'+ color[2]);
                        }else{
                            $('#size_name').text(index.name +': ' + color[1]);
                        }
                    }
                });
                    $('#product_sku_id').val(response.data.id);
                    if (response.data.product_stock == 0) {
                        $('#availability').html("{{__('defaultTheme.unlimited')}}");
                    }else{
                        $('#availability').html(response.data.product_stock);
                    }
                    if(response.data.product.stock_manage == 1 && parseInt(response.data.product_stock) >= parseInt(response.data.product.product.minimum_order_qty) || response.data.product.stock_manage == 0){
                        $('#add_to_cart_div').html(`
                            <div class="col-md-6">
                                <button type="button" id="add_to_cart_btn" class="amaz_primary_btn style2 mb_20  add_to_cart text-uppercase add_to_cart_btn flex-fill text-center w-100">{{__('defaultTheme.add_to_cart')}}</button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" id="butItNow" class="amaz_primary_btn3 mb_20  w-100 text-center justify-content-center text-uppercase buy_now_btn" data-id="{{$product->id}}" data-type="product">{{__('common.buy_now')}}</button>
                            </div>
                        `);
                        $('#stock_div').html(`<span class="stoke_badge">{{__('common.in_stock')}}</span>`);
                        if($('#isMultiVendorActive').val() == 1){
                            $('#cart_footer_mobile').html(`
                                <a href="
                                    @if ($product->seller->slug)
                                        {{route('frontend.seller',$product->seller->slug)}}
                                    @else
                                        {{route('frontend.seller',base64_encode($product->seller->id))}}
                                    @endif
                                " class="d-flex flex-column justify-content-center product_details_icon">
                                    <i class="ti-save"></i>
                                    <span>{{__('common.store')}}</span>
                                </a>
                                <button type="button" class="product_details_button style1 buy_now_btn" data-id="{{$product->id}}" data-type="product">
                                    <span>{{__('common.buy_now')}}</span>
                                </button>
                                <button class="product_details_button add_to_cart_btn" type="button">{{__('common.add_to_cart')}}</button>
                            `);
                        }else{
                            if($('#isMultiVendorActive').val() == 1){
                                $('#cart_footer_mobile').html(`
                                    <a href="
                                        @if ($product->seller->slug)
                                            {{route('frontend.seller',$product->seller->slug)}}
                                        @else
                                            {{route('frontend.seller',base64_encode($product->seller->id))}}
                                        @endif
                                    " class="d-flex flex-column justify-content-center product_details_icon">
                                        <i class="ti-save"></i>
                                        <span>{{__('common.store')}}</span>
                                    </a>
                                    <button type="button" class="product_details_button style1 buy_now_btn" data-id="{{$product->id}}" data-type="product">
                                        <span>{{__('common.buy_now')}}</span>
                                    </button>
                                    <button class="product_details_button add_to_cart_btn" type="button">{{__('common.add_to_cart')}}</button>
                                `);
                            }else{
                                $('#cart_footer_mobile').html(`
                                    <button type="button" class="product_details_button style1 buy_now_btn" data-id="{{$product->id}}" data-type="product">
                                        <span>{{__('common.buy_now')}}</span>
                                    </button>
                                    <button class="product_details_button add_to_cart_btn" type="button">{{__('common.add_to_cart')}}</button>
                                `);
                            }
                        }
                    }
                    else{
                        $('#add_to_cart_div').html(`
                            <div class="col-md-6">
                                <button type="button" disabled class="amaz_primary_btn style2 mb_20 add_to_cart text-uppercase flex-fill text-center w-100">{{__('defaultTheme.out_of_stock')}}</button>
                            </div>
                        `);
                        $('#stock_div').html(`<span class="stokeout_badge">{{__('defaultTheme.out_of_stock')}}</span>`);
                        toastr.warning("{{__('defaultTheme.out_of_stock')}}");
                        $('#cart_footer_mobile').html(`
                            <button type="button" class="product_details_button style1" disabled>
                                <span>{{__('defaultTheme.out_of_stock')}}</span>
                            </button>
                            <button type="button" class="product_details_button" disabled>{{__('defaultTheme.out_of_stock')}}</button>
                        `);
                    }
                }else {
                    toastr.error("{{__('defaultTheme.no_stock_found_for_this_seller')}}", "{{__('common.error')}}");
                }
                $('#pre-loader').hide();
            });
    }
</script>
<script>
    // auction counter
         if ($("#count_down").length > 0) {
                    let auctionDate = "{{$auction_date}}";
                    $("#count_down").countdown(auctionDate, function (event) {
                        $(this).html(
                            event.strftime(
                            '<div class="single_count"><span>%D</span><p>Days</p></div><div class="single_count"><span>%H</span><p>Hours</p></div><div class="single_count"><span>%M</span><p>Minutes</p></div><div class="single_count"><span>%S</span><p>Seconds</p></div>'
                            )
                        );
            });
          }

        $(document).on('click','#placeBid', function(event){
                event.preventDefault();
                @if($max_bid > 0)
                    let bid_amount = "{{ $max_bid + $auction->increment_price }}";
                        bid_amount = parseFloat(bid_amount);
                @else
                    let bid_amount = "{{ $auction->starting_bidding_price }}";
                        bid_amount = parseFloat(bid_amount);
                @endif

                $('#bid_amount').val(bid_amount);
                @if(Illuminate\Support\Facades\Auth::check())
                    $('#placebid_modal').modal('show');
                @else
                    window.location.href = '{{url("/login")}}';
                @endif
            });


            $("#place_bid_form").submit(function (event) {
                $('#pre-loader').show();
                let formData = {

                    bid_amount : $('#bid_amount').val(),
                    auction_id : $('#auction_id').val(),
                    _token: "{{ csrf_token() }}"
                };

                $.ajax({
                type: "POST",
                url: "{{ route('auctionproducts.place.bid') }}",
                data: formData,
                dataType: "json",
                encode: true,
                }).done(function (data) {
                    $('#pre-loader').hide();
                    $("#bid_amount-group").removeClass("has-error");
                    $('.help-block').remove();

                    if(data.success===0){
                        if (data.errors.bid_amount) {
                        $("#bid_amount-group").addClass("has-error");
                        $("#bid_amount-group").append(
                            '<div class="help-block text-danger">' + data.errors.bid_amount[0] + "</div>"
                        );
                        }
                    }
                        if(data.success==2 && data.data=='first_low_bid'){
                            toastr.error("Bid amount should be greater than Starting Bid!", "{{__('common.error')}}");
                        }
                        if(data.success==3 && data.data=='low_bid_amount'){
                            toastr.error("Bid amount should be greater than Highest Bid!", "{{__('common.error')}}");
                        }
                        if(data.success===1 && data.data==true){
                            $('#placebid_modal').modal('hide');
                            toastr.success("Bid placed successfully", "{{__('common.success')}}");
                            location.reload();
                        }else{
                            toastr.error("Can't place Bid!", "{{__('common.error')}}");
                        }
                });

                event.preventDefault();
            });

            function resetForm(form){
                $(form +' #name_error').text('');
                $(form +' #percentage_error').text('');
            }


            $(document).on('click','.bid_histoy',function(){
                let url = $(this).attr('data-url');
                $.ajax({
                    url:url,
                    method:"get",

                }).done(function(response){
                    if(response.status == 1){
                        $("#showHistor").html(response.html);
                        $("#bidHistory").modal("show");
                    }else{
                        toastr.error('Something want wrong !','Error');
                    }

                });
            });
</script>
@php
    $setting = DB::table('general_settings')->where('id','1')->first();
@endphp
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
  <script>
    Pusher.logToConsole = true;
    var pusher = new Pusher("{{env('PUSHER_APP_KEY')}}", {
      cluster: "{{env('PUSHER_APP_CLUSTER')}}"
    });
    var channel = pusher.subscribe('auction_event_{{ $auction->id }}');
       channel.bind('auction_bid_{{ $auction->id }}', function(data) {
        console.log(data);
       toastr.success(data.message,'success');
    });
  </script>

@endpush

@include(theme('partials.add_to_cart_script'))
@include(theme('partials.add_to_compare_script'))
