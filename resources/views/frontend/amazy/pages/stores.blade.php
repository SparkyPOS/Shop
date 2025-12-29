@extends('frontend.amazy.layouts.app')
@section('title')
   {{__('common.stores')}}
@endsection
@push('styles')
    <style>
        .vendor_widget .thumb img {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(1);
            width: 100% !important;
            height: 100% !important;
            min-width: 100% !important;
            min-height: 100% !important;
            max-width: 100% !important;
            -o-object-fit: cover;
            object-fit: cover;
            transition: all 0.3s ease-in-out;
        }
        .vendor_widget {
            border: 1px solid var(--border_color);
            background-color: #fff;
        }
        .vendor_widget .product_thumb_upper {
            position: relative;
            margin-bottom: 0;
        }
</style>
@endpush
@section('content')
    <!-- brand_banner::start  -->
    <div class="brand_banner d-flex align-items-center">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h3 class="branding_text">
                        {{__('common.stores')}}
                    </h3>
                </div>
            </div>
        </div>
    </div>
    <!-- brand_banner::end  -->
    <div class="prodcuts_area ">
        <div class="container">
            <div class="row">
                <div id="dataWithPaginate" class="col-lg-12 col-xl-12">
                    @include('frontend.amazy.partials.sellers_paginate_data', [
                        'listingLabel' => __('common.stores'),
                        'linkStoresToVendors' => true,
                    ])
                </div>
            </div>
        </div>
        <div class="add-product-to-cart-using-modal"></div>
        <input type="hidden" id="login_check" value="@if(auth()->check()) 1 @else 0 @endif">
        @if (app('request')->input('item') == "category" || (isset($item) && $item == "category"))
            <input type="hidden" id="item_request" name="item_request" value="{{ $category_id }}">
            <input type="hidden" id="item_request_type" name="item_request_type" value="category">
        @endif
    </div>
@endsection
@include(theme('partials.add_to_cart_script'))
@include(theme('partials.add_to_compare_script'))
@push('scripts')
<script type="text/javascript">
(function($){
    "use strict";
    $(document).ready(function(){
        function reloadSellerList(){
            const paginate = $('#paginate_by').val();
            const sort_by = $('#product_short_list').val();
            const url = new URL(window.location.href);
            if (paginate) url.searchParams.set('paginate', paginate);
            if (sort_by) url.searchParams.set('sort_by', sort_by);
            $('#pre-loader').removeClass('d-none');
            $.get(url.toString(), function(data){
                const html = $(data).find('#dataWithPaginate').html();
                $('#dataWithPaginate').html(html);
                $('#pre-loader').addClass('d-none');
                if ($.fn.niceSelect) { $('select').niceSelect(); }
            });
        }
        $(document).on('change', '.sellers_filter', reloadSellerList);
    });
})(jQuery);
</script>
@endpush
@push('scripts')
    <script type="text/javascript">
        (function($){
            "use strict";
            var filterType = [];
            $(document).ready(function(){
                '@if(isset($color) && $color->id == 1)'+
                '@foreach ($color->values as $ki => $item)'+
                $("span.colors_{{ $ki }}").css("background", "{{ $item->value }}");
                '@endforeach'+
                '@endif'
                $(document).on('click', '#refresh_btn', function(event){
                    event.preventDefault();
                    filterType = [];
                    fetch_data(1);
                    $('.attr_checkbox').prop('checked', false);
                    $('.color_checkbox').removeClass('selected_btn');
                    $('.category_checkbox').prop('checked', false);
                    $('#price_range_div').html(
                        `<div class="wrapper">
                    <div class="range-slider">
                        <input type="text" class="js-range-slider-0" value=""/>
                    </div>
                    <div class="extra-controls form-inline">
                        <div class="form-group">
                            <div class="price_rangs">
                                <input type="text" class="js-input-from form-control" id="min_price" value="{{ 0 }}" readonly/>
                                <p>Min</p>
                            </div>
                            <div class="price_rangs">
                                <input type="text" class="js-input-to form-control" id="max_price" value="{{0 }}" readonly/>
                                <p>Max</p>
                            </div>
                        </div>
                    </div>
                </div>`
                    );

                });
                $(document).on('click', '.getProductByChoice', function(event){
                 let type = $(this).data('type');
                 let el = $(this).data('value');
                    getProductByChoice(type, el);
                });
                $(document).on('change', '.getFilterUpdateByIndex', function(event){
                    var paginate = $('#paginate_by').val();
                    var prev_stat = $('.filterCatCol').val();
                    var sort_by = $('#product_short_list').val();
                    var requestItem = $('#item_request').val();
                    var requestItemType = $('#item_request_type').val();
                    $('#pre-loader').show();
                    $.get("{{ route('frontend.sort_product_filter_by_type') }}", {paginate:paginate, sort_by:sort_by, requestItem:requestItem, requestItemType:requestItemType}, function(data){
                        $('#dataWithPaginate').html(data);
                        $('#product_short_list').niceSelect();
                        $('#paginate_by').niceSelect();
                        $('#pre-loader').hide();
                        $('.filterCatCol').val(prev_stat);
                        activeTab();
                        initLazyload();
                    });
                });


                function fetch_data(page){
                    $('#pre-loader').show();
                    if(page != 'undefined'){
                        var paginate = $('#paginate_by').val();
                        var sort_by = $('#product_short_list').val();
                        if (sort_by != null && paginate != null) {
                            var url = window.location.href+'&sort_by='+sort_by+'&paginate='+paginate+'&page='+page;
                        }else if (sort_by == null && paginate != null) {
                            var url = window.location.href+'&paginate='+paginate+'&page='+page;
                        }else {
                            var url = window.location.href+'&page='+page;
                        }
                        $.ajax({
                            url: url,
                            success:function(data)
                            {
                                $('#dataWithPaginate').html(data);
                                $('#product_short_list').niceSelect();
                                $('#paginate_by').niceSelect();
                                $('#pre-loader').hide();
                                activeTab();
                                initLazyload();
                            }
                        });
                    }else{
                        toastr.warning("{{__('defaultTheme.this_is_undefined')}}","{{__('common.warning')}}");
                    }
                }
                function fetch_filter_data(page){
                    $('#pre-loader').show();
                    var paginate = $('#paginate_by').val();
                    var sort_by = $('#product_short_list').val();
                    var requestItem = $('#item_request').val();
                    var requestItemType = $('#item_request_type').val();
                    if (sort_by != null && paginate != null) {
                        var url = "{{route('frontend.product_filter_page_by_type')}}"+'?requestItem='+requestItem+'&requestItemType='+requestItemType+'&sort_by='+sort_by+'&paginate='+paginate+'&page='+page;
                    }else if (sort_by == null && paginate != null) {
                        var url = "{{route('frontend.product_filter_page_by_type')}}"+'?requestItem='+requestItem+'&requestItemType='+requestItemType+'&paginate='+paginate+'&page='+page;
                    }else {
                        var url = "{{route('frontend.product_filter_page_by_type')}}"+'?requestItem='+requestItem+'&requestItemType='+requestItemType+'&page='+page;
                    }
                    if(page != 'undefined'){
                        $.ajax({
                            url:url,
                            success:function(data)
                            {
                                $('#dataWithPaginate').html(data);
                                $('#product_short_list').niceSelect();
                                $('#paginate_by').niceSelect();
                                $('.filterCatCol').val(1);
                                $('#pre-loader').hide();
                                activeTab();
                                initLazyload();
                            }
                        });
                    }else{
                        toastr.warning("{{__('defaultTheme.this_is_undefined')}}","{{__('common.warning')}}");
                    }
                }
                let minimum_price = 0;
                let maximum_price = 0;
                let price_range_gloval = 0;
                $(document).on('click', '.js-range-slider-0', function(event){
                    var price_range = $("#amount").data('value').split('-');
                    minimum_price = price_range[0];
                    maximum_price = price_range[1];
                    price_range_gloval = price_range;
                    myEfficientFn();
                });
            });
        })(jQuery);
    </script>
@endpush
