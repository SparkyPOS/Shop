@php
    $total_number_of_item_per_page = $products->perPage();
    $total_number_of_items = $products->total() > 0 ? $products->total() : 0;
    $total_number_of_pages = $total_number_of_items / $total_number_of_item_per_page;
    $reminder = $total_number_of_items % $total_number_of_item_per_page;
    if ($reminder > 0) {
        $total_number_of_pages += 1;
    }
    $current_page = $products->currentPage();
    $previous_page = $products->currentPage() - 1;
    if ($current_page == $products->lastPage()) {
        $show_end = $total_number_of_items;
    } else {
        $show_end = $total_number_of_item_per_page * $current_page;
    }

    $show_start = 0;
    if ($total_number_of_items > 0) {
        $show_start = $total_number_of_item_per_page * $previous_page + 1;
    }
@endphp
<div class="row ">
    <div class="col-12">
        <div class="box_header d-flex flex-wrap align-items-center justify-content-between">
            <h5 class="font_16 f_w_500 mr_10 mb-0">{{ __('defaultTheme.showing') }} @if ($show_start == $show_end) {{ getNumberTranslate($show_end) }}
                @else
                    {{ getNumberTranslate($show_start) }} - {{ getNumberTranslate($show_end) }} @endif {{ __('defaultTheme.out_of_total') }} {{ getNumberTranslate($total_number_of_items) }}
                {{ __('common.merchants') }}</h5>
            <div class="box_header_right ">
                <div class="short_select d-flex align-items-center gap_10 flex-wrap">

                    <div class="shorting_box d-none d-md-block">
                        <select name="paginate_by" class="amaz_select sellers_filter" id="paginate_by">
                            <option value="9" @if (request('paginate') == '9') selected @endif>
                                {{ __('common.show') }} {{ getNumberTranslate(9) }} {{ __('common.item’s') }}</option>
                            <option value="12" @if (request('paginate') == '12') selected @endif>
                                {{ __('common.show') }} {{ getNumberTranslate(12) }} {{ __('common.item’s') }}
                            </option>
                            <option value="16" @if (request('paginate') == '16') selected @endif>
                                {{ __('common.show') }} {{ getNumberTranslate(16) }} {{ __('common.item’s') }}
                            </option>
                            <option value="25" @if (request('paginate') == '25') selected @endif>
                                {{ __('common.show') }} {{ getNumberTranslate(25) }} {{ __('common.item’s') }}
                            </option>
                            <option value="30" @if (request('paginate') == '30') selected @endif>
                                {{ __('common.show') }} {{ getNumberTranslate(30) }} {{ __('common.item’s') }}
                            </option>
                        </select>
                    </div>
                    <div class="shorting_box">
                        <select class="amaz_select sellers_filter" name="sort_by" id="product_short_list">
                            <option disabled selected>{{ __('amazy.Sorting by') }}</option>
                            <option value="alpha_asc" @if (request('sort_by') == 'alpha_asc') selected @endif>
                                {{ __('defaultTheme.name_a_to_z') }}</option>
                            <option value="alpha_desc" @if (request('sort_by') == 'alpha_desc') selected @endif>
                                {{ __('defaultTheme.name_z_to_a') }}</option>

                        </select>
                    </div>
                    <div class="flex-fill text-end">
                        <div class="category_toggler d-inline-block d-lg-none  gj-cursor-pointer">
                            <svg  width="19.5" height="13" viewBox="0 0 19.5 13">
                                <g id="filter-icon" transform="translate(28)">
                                    <rect id="Rectangle_1" data-name="Rectangle 1" width="19.5" height="2"
                                          rx="1" transform="translate(-28)" fill="#fd4949" />
                                    <rect id="Rectangle_2" data-name="Rectangle 2" width="15.5" height="2"
                                          rx="1" transform="translate(-26 5.5)" fill="#fd4949" />
                                    <rect id="Rectangle_3" data-name="Rectangle 3" width="5" height="2"
                                          rx="1" transform="translate(-20.75 11)" fill="#fd4949" />
                                </g>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="tab-content mb_30" id="myTabContent">
    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
        <!-- content  -->
        <div class="row custom_rowProduct">
            @if (count($products) > 0)
                @foreach ($products as $product)
                        <div class="col-xl-3 col-md-6 col-sm-6 col-6 d-flex">
                            <div class="product_widget5 vendor_widget mb_30 style5 w-100">
                                <div class="product_thumb_upper">
                                    <a href=" @if ($product->slug)
                                            {{route('frontend.seller',['seller_id'=>$product->slug,'vendor_id'=>@$product->SellerAccount->vendor_id])}}
                                        @else
                                            {{route('frontend.seller',base64_encode($product->id))}}
                                        @endif"
                                       class="thumb">
                                        <img data-src="{{$product->SellerAccount->banner?showImage($product->SellerAccount->banner):showImage('frontend/default/img/breadcrumb_bg.png')}}" src="{{$product->SellerAccount->banner?showImage($product->SellerAccount->banner):showImage('frontend/default/img/breadcrumb_bg.png')}}"
                                             alt="{{ @$product->SellerAccount->seller_shop_display_name }}" title="{{ @$product->seller_shop_display_name }}"
                                             class="lazyload">
                                    </a>


                                </div>
                                <div class="product_star mx-auto">
                                    @php
                                        $total_review = $product->sellerReviews->where('status',1)->sum('rating');
                                        $review_count = $product->sellerReviews->where('status',1)->count();
                                        if($total_review != 0){
                                            $review = round($total_review /$review_count,0);
                                        }else{
                                            $review = 1;
                                        }
                                    @endphp
                                    <x-rating :rating="$review" />
                                </div>
                                <div class="product__meta  text-center">
{{--                                    <div><img style="height: 50px;--}}
{{--    width: 50px;--}}
{{--    border-radius: 50%;--}}
{{--    border: 1px solid black;--}}
{{--    padding: 5px;" src="{{$product->photo?showImage($product->photo):showImage('frontend/default/img/avatar.jpg')}}" alt="#"></div>--}}
                                    <a href="{{route('frontend.seller',['seller_id'=>$product->slug,'vendor_id'=>@$product->SellerAccount->vendor_id])}}">
                                        <h4>@if ($product->SellerAccount->seller_shop_display_name) {{ textLimit(@$product->SellerAccount->seller_shop_display_name, 50) }} @else {{ textLimit(@$product->SellerAccount->seller_shop_display_name, 50) }} @endif</h4>
                                    </a>
                                    @if(isset($product->SellerAccount) && is_null($product->SellerAccount->parent_seller_id))
                                        <div class="mt-1">
                                            @php
                                                $shopSlug = $product->slug ?: base64_encode($product->id);
                                            @endphp
                                            <a class="theme_btn_small" href="{{ route('frontend.shop.vendors', $shopSlug) }}">View Sellers</a>
                                        </div>
                                    @endif
                                    @if(!empty(@$product->SellerAccount->about_seller))
                                        <div class="mt-1">
                                            <span class="product_banding">{{ __('seller.about_seller') }}: {{ textLimit(strip_tags(@$product->SellerAccount->about_seller), 100) }}</span>
                                        </div>
                                    @endif
                                    @if(@$product->SellerAccount && @$product->SellerAccount->vendor_id)
                                        <div class="mt-1">
                                            <span class="product_banding">Vendor ID: {{ @$product->SellerAccount->vendor_id }}</span>
                                        </div>
                                    @endif
                                    <div class="mt-1">
                                        <span class="product_banding">Seller: {{ textLimit($product->name, 60) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                @endforeach
            @else
                <div class="col-12">
                    <div class="text-center alert alert-danger">
                        {{ __('defaultTheme.no_product_found') }}
                    </div>
                </div>
            @endif
        </div>
        <!--/ content  -->
    </div>
    <input type="hidden" name="filterCatCol" class="filterCatCol" value="0">
    <!--/ content  -->
    @if ($products->lastPage() > 1)
        <x-pagination-component :items="$products" type="" />
    @endif
</div>
