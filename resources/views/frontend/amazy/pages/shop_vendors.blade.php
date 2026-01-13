@extends('frontend.amazy.layouts.app')
@section('title')
    Vendors under {{ $shop->SellerAccount->seller_shop_display_name ?? ($shop->first_name.' '.$shop->last_name) }}
@endsection
@section('content')
    <div class="brand_banner d-flex align-items-center">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h3 class="branding_text">
                        Vendors of
                        {{ $shop->SellerAccount->seller_shop_display_name ?? ($shop->first_name.' '.$shop->last_name) }}
                    </h3>
                </div>
            </div>
        </div>
    </div>
    <div class="prodcuts_area ">
        <div class="container">
            <div class="row">
                <div class="col-12 mb-3">
                    <a class="menu_btn_1 text-nowrap" href="{{ route('frontend.shops') }}">← Back to Stores</a>
                </div>
                <div id="dataWithPaginate" class="col-lg-12 col-xl-12">
                    @include('frontend.amazy.partials.vendors_paginate_data')
                </div>
            </div>
        </div>
        <div class="add-product-to-cart-using-modal"></div>
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
