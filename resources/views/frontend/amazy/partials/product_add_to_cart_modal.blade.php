<!-- Modal::start  -->
<div class="modal fade theme_modal quick_view_modal_custom" id="theme_modal" tabindex="-1" role="dialog" aria-labelledby="theme_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered quick_view_modal_dialog" role="document">
        <div class="modal-content quick_view_modal_content">
            <div class="modal-body p-0">
                <div class="product_quick_view quick_view_modal_grid">
                    <button type="button" class="close_modal_icon quick_view_modal_close" data-bs-dismiss="modal" data-dismiss="modal">
                        <i class="ti-close"></i>
                    </button>

                    <div class="product_details_img quick_view_product_gallery">
                        <div class="tab-content tab_content quick_view_tab_content" id="quickViewTabContent_{{ $product->id }}">
                            @if(count($product->product->gallary_images) > 0)
                                @foreach($product->product->gallary_images as $image)
                                    <div class="gallary_img tab-pane fade {{ $product->product->gallary_images->first()->id == $image->id ? 'show active' : '' }}" id="quick_view_thumb_{{ $image->id }}" role="tabpanel">
                                        <div class="img_div quick_view_img_div">
                                            <img
                                                data-zoom-image="{{ showImage($image->images_source) }}"
                                                src="{{ showImage($image->images_source) }}"
                                                alt="{{ $product->product_name }}"
                                                title="{{ $product->product_name }}"
                                                class="img-fluid varintImg quick_view_main_img"
                                            >
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="tab-pane fade show active" id="quick_view_thumb_{{ $product->id }}" role="tabpanel">
                                    <div class="img_div quick_view_img_div">
                                        <img
                                            @if ($product->thum_img != null)
                                                data-zoom-image="{{ showImage($product->thum_img) }}"
                                                src="{{ showImage($product->thum_img) }}"
                                            @else
                                                data-zoom-image="{{ showImage($product->product->thumbnail_image_source) }}"
                                                src="{{ showImage($product->product->thumbnail_image_source) }}"
                                            @endif
                                            alt="{{ $product->product_name }}"
                                            title="{{ $product->product_name }}"
                                            class="img-fluid varintImg quick_view_main_img"
                                        >
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="quick_view_thumb_area">
                            <button type="button" class="quick_view_thumb_arrow quick_view_thumb_prev" aria-label="Previous image">
                                <i class="ti-angle-left"></i>
                            </button>

                            <ul class="nav tab_thumb quick_view_tab_thumb" id="quickViewTab_{{ $product->id }}" role="tablist">
                                @if(count($product->product->gallary_images) > 0)
                                    @foreach($product->product->gallary_images as $i => $image)
                                        <li class="nav-item thumb_small_m">
                                            <a
                                                class="nav-link {{ $i == 0 ? 'active' : '' }} small-img"
                                                id="quick_view_thumb_{{ $image->id }}_tab"
                                                data-img="{{ showImage($image->images_source) }}"
                                                data-toggle="tab"
                                                href="#quick_view_thumb_{{ $image->id }}"
                                                role="tab"
                                                aria-controls="quick_view_thumb_{{ $image->id }}"
                                                aria-selected="{{ $i == 0 ? 'true' : 'false' }}"
                                            >
                                                <div class="thamb_img quick_view_thumb_img">
                                                    <img
                                                        src="{{ showImage($image->images_source) }}"
                                                        alt="{{ $product->product_name }}"
                                                        title="{{ $product->product_name }}"
                                                        class="img-fluid selector-img"
                                                        data-id="#quick_view_thumb_{{ $image->id }}_tab"
                                                    >
                                                </div>
                                            </a>
                                        </li>
                                    @endforeach
                                @else
                                    <li class="nav-item thumb_small_m">
                                        <a
                                            class="nav-link active small-img"
                                            id="quick_view_thumb_{{ $product->id }}_tab"
                                            data-toggle="tab"
                                            href="#quick_view_thumb_{{ $product->id }}"
                                            role="tab"
                                            aria-controls="quick_view_thumb_{{ $product->id }}"
                                            aria-selected="true"
                                        >
                                            <div class="thamb_img quick_view_thumb_img">
                                                <img
                                                    @if ($product->thum_img != null)
                                                        src="{{ showImage($product->thum_img) }}"
                                                    @else
                                                        src="{{ showImage($product->product->thumbnail_image_source) }}"
                                                    @endif
                                                    alt="{{ $product->product_name }}"
                                                    title="{{ $product->product_name }}"
                                                    class="img-fluid selector-img"
                                                    data-id="#quick_view_thumb_{{ $product->id }}_tab"
                                                >
                                            </div>
                                        </a>
                                    </li>
                                @endif
                            </ul>

                            <button type="button" class="quick_view_thumb_arrow quick_view_thumb_next" aria-label="Next image">
                                <i class="ti-angle-right"></i>
                            </button>
                        </div>
                    </div>

                    <div class="product_details_wrapper">
                        <div class="product_content_details mb_30">
                            <p>
                                <span>{{ __('defaultTheme.sku') }}:</span>
                                <span id="sku_id_li_modal" class="stock_text">{{ @$product->skus->first()->sku->sku ?? '-' }}</span>
                            </p>

                            @php
                                $stock = 0;
                            @endphp

                            @if ($product->stock_manage == 1)
                                <p>
                                    <span>{{ __('defaultTheme.availability') }}:</span>
                                    <span class="stock_text" id="availability_modal">{{ $product->skus->first()->product_stock }}</span>
                                    <span class="stock_text">{{ __('common.in_stock') }}</span>
                                </p>
                            @else
                                <p class="stock_text">
                                    <span>{{ __('defaultTheme.availability') }}:</span>
                                    {{ __('defaultTheme.unlimited') }}
                                </p>
                            @endif

                            <h3>{{ $product->product_name }}</h3>

                            <h5 class="prise_text d-flex align-items-center">
                                {{ getProductDiscountedPrice($product) }}
                            </h5>

                            <div class="pro_details_disPrise d-flex align-items-center gap_15">
                                <h4 class="discount_prise m-0">
                                    <span class="text-decoration-line-through">
                                        @if($product->hasDeal || $product->hasDiscount == 'yes')
                                            <span>{{ single_price($product->skus->max('sell_price')) }}</span>
                                        @endif
                                    </span>
                                </h4>

                                <span class="diccount_percents">
                                    @if(@$product->hasDeal)
                                        @if(@$product->hasDeal->discount > 0)
                                            @if(@$product->hasDeal->discount_type == 0)
                                                -{{ @$product->hasDeal->discount }}%
                                            @else
                                                -{{ single_price(@$product->hasDeal->discount) }}
                                            @endif
                                        @endif
                                    @else
                                        @if(@$product->hasDiscount == 'yes')
                                            @if($product->discount > 0)
                                                @if($product->discount_type == 0)
                                                    -{{ getNumberTranslate($product->discount) }}%
                                                @else
                                                    -{{ single_price($product->discount) }}
                                                @endif
                                            @endif
                                        @endif
                                    @endif
                                </span>
                            </div>

                            <div class="product_ratings">
                                <div class="stars justify-content-center">
                                    <x-rating :rating="$rating"/>
                                </div>
                                <span>({{ $total_review }} {{ __('defaultTheme.review') }})</span>
                            </div>

                            @if($product->product->product_type == 2)
                                @foreach (session()->get('item_details') as $key => $item)
                                    @if ($item['attr_id'] === 1)
                                        <div class="product_color_varient mb_20">
                                            <h5 class="font_14 f_w_500 theme_text3 text-capitalize d-block mb_10">{{ $item['name'] }}:</h5>

                                            <div class="color_List d-flex gap_5 flex-wrap">
                                                <input type="hidden" class="attr_value_name" name="attr_val_name_modal[]" value="{{ $item['value'][0] }}">
                                                <input type="hidden" class="attr_value_id" name="attr_val_id_modal[]" value="{{ $item['id'][0] }}-{{ $item['attr_id'] }}">

                                                @foreach ($item['value'] as $ks => $value_name)
                                                    <label class="round_checkbox d-flex">
                                                        <input
                                                            id="modal_color_radio_{{ $product->id }}_{{ $ks }}"
                                                            name="color_filt_modal_{{ $product->id }}"
                                                            class="attr_val_name"
                                                            type="radio"
                                                            color="color"
                                                            @if ($ks === 0) checked @endif
                                                            data-value="{{ $item['id'][$ks] }}"
                                                            data-value-key="{{ $item['attr_id'] }}"
                                                            value="{{ $value_name }}"
                                                        >
                                                        <span class="checkmark modal_colors_{{ $ks }} class_color_{{ $item['code'][$ks] }}">
                                                            <div class="check_bg_color"></div>
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if ($item['attr_id'] != 1)
                                        <div class="product_color_varient mb_20">
                                            <h5 class="font_14 f_w_500 theme_text3 text-capitalize d-block mb_10">{{ $item['name'] }}:</h5>

                                            <div class="color_List d-flex gap_5 flex-wrap">
                                                <input type="hidden" class="attr_value_name" name="attr_val_name_modal[]" value="{{ $item['value'][0] }}">
                                                <input type="hidden" class="attr_value_id" name="attr_val_id_modal[]" value="{{ $item['id'][0] }}-{{ $item['attr_id'] }}">

                                                @foreach ($item['value'] as $m => $value_name)
                                                    <a
                                                        class="attr_val_name size_btn not_111 @if ($m === 0) selected_btn @endif"
                                                        color="not"
                                                        data-value-key="{{ $item['attr_id'] }}"
                                                        data-value="{{ $item['id'][$m] }}"
                                                    >
                                                        {{ $value_name }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif

                            <input type="hidden" name="product_sku_id" id="product_sku_id_modal" value="{{ $product->product->product_type == 1 ? $product->skus->first()->id : $product->skus->first()->id }}">
                            <input type="hidden" name="seller_id" id="seller_id_modal" value="{{ $product->user_id }}">
                            <input type="hidden" name="stock_manage_status" id="stock_manage_status_modal" value="{{ $product->stock_manage }}">
                            <input type="hidden" id="product_id_modal" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" id="maximum_order_qty_modal" value="{{ @$product->product->max_order_qty }}">
                            <input type="hidden" id="minimum_order_qty_modal" value="{{ @$product->product->minimum_order_qty }}">
                            <input type="hidden" name="product_type" class="product_type" value="{{ $product->product->product_type }}">

                            <input type="hidden" name="base_sku_price" id="base_sku_price_modal" value="
                                @if(@$product->hasDeal)
                                    {{ selling_price($product->skus->first()->sell_price, $product->hasDeal->discount_type, $product->hasDeal->discount) }}
                                @else
                                    @if($product->hasDiscount == 'yes')
                                        {{ selling_price($product->skus->first()->sell_price, $product->discount_type, $product->discount) }}
                                    @else
                                        {{ $product->skus->first()->sell_price }}
                                    @endif
                                @endif
                            ">

                            <input type="hidden" name="final_price" id="final_price_modal" value="
                                @if(@$product->hasDeal)
                                    {{ selling_price($product->skus->first()->sell_price, $product->hasDeal->discount_type, $product->hasDeal->discount) }}
                                @else
                                    @if($product->hasDiscount == 'yes')
                                        {{ selling_price($product->skus->first()->sell_price, $product->discount_type, $product->discount) }}
                                    @else
                                        {{ $product->skus->first()->sell_price }}
                                    @endif
                                @endif
                            ">

                            <input type="hidden" value="{{ textLimit($product->product_name, 28) }}" id="product_name_modal">
                            <input type="hidden" value="{{ singleProductURL(@$product->seller->slug, @$product->slug) }}" id="product_url_modal">
                            <input type="hidden" name="thumb_image" id="thumb_image_modal" value="@if ($product->thum_img != null) {{ showImage($product->thum_img) }} @else {{ showImage($product->product->thumbnail_image_source) }} @endif">

                            @if(!empty(trim(strip_tags(@$product->product->description))))
                                <div class="quick_view_description">
                                    <h4 class="font_14 f_w_700 mb_8">{{ __('common.description') }}</h4>
                                    <div class="quick_view_description_text">
                                        {!! \Illuminate\Support\Str::limit(strip_tags(@$product->product->description), 190) !!}
                                    </div>
                                </div>
                            @endif

                            <div class="product_info">
                                <div class="single_pro_varient">
                                    <h5 class="font_14 f_w_500 theme_text3">{{ __('common.quantity') }}:</h5>

                                    <div class="product_number_count mr_5" data-target="amount-10">
                                        <button class="count_single_item inumber_decrement cart-qty-minus-modal qtyChangeMinus" value="-">
                                            <i class="ti-minus"></i>
                                        </button>

                                        <input
                                            id="qty_modal"
                                            name="qty"
                                            class="count_single_item input-number qty"
                                            type="text"
                                            data-value="{{ @$product->product->minimum_order_qty }}"
                                            value="{{ getNumberTranslate(@$product->product->minimum_order_qty) }}"
                                            readonly
                                        >

                                        <button class="count_single_item number_increment qtyChangePlus cart-qty-plus-modal" value="+">
                                            <i class="ti-plus"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="row mt_15">
                                    <h4>
                                        <span>{{ __('common.total') }}:</span>
                                        <span id="total_price_modal">
                                            @if(@$product->hasDeal)
                                                {{ single_price(selling_price(@$product->skus->first()->sell_price, @$product->hasDeal->discount_type, @$product->hasDeal->discount) * $product->product->minimum_order_qty) }}
                                            @else
                                                @if($product->hasDiscount == 'yes')
                                                    {{ single_price(selling_price(@$product->skus->first()->sell_price, @$product->discount_type, @$product->discount) * $product->product->minimum_order_qty) }}
                                                @else
                                                    {{ single_price(@$product->skus->first()->sell_price * $product->product->minimum_order_qty) }}
                                                @endif
                                            @endif
                                        </span>
                                    </h4>
                                </div>

                                <div class="row mt_20" id="add_to_cart_div_modal">
                                    @if ($product->stock_manage == 1 && $product->skus->first()->product_stock >= $product->product->minimum_order_qty || $product->stock_manage == 0)
                                        <div class="col-md-6">
                                            <a href="" id="add_to_cart_btn_modal" class="home10_primary_btn2 mb_15 w-100 text-center add_to_cart text-uppercase flex-fill text-center">{{ __('common.add_to_cart') }}</a>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="#" class="home10_primary_btn4 w-100 radius_5px mb_15 w-100 text-center justify-content-center text-uppercase buy_now_btn_modal" data-id="{{ $product->id }}" data-type="product">{{ __('common.buy_now') }}</a>
                                        </div>
                                    @else
                                        <div class="col-md-6">
                                            <button type="button" disabled class="amaz_primary_btn style2 mb_15 add_to_cart text-uppercase flex-fill text-center w-100">{{ __('defaultTheme.out_of_stock') }}</button>
                                        </div>
                                    @endif
                                </div>

                                <div class="add_wish_compare d-flex alingn-items-center mb-0">
                                    <a href="#" class="single_wish_compare add_to_wishlist_modal" id="wishlist_btn" data-product_id="{{ $product->id }}" data-seller_id="{{ $product->user_id }}">
                                        <i class="far fa-heart"></i> {{ __('defaultTheme.add_to_wishlist') }}
                                    </a>
                                    <a href="#" class="single_wish_compare" id="add_to_compare_btn" data-product_sku_id="#product_sku_id_modal" data-product_type="{{ $product->product->product_type }}">
                                        <i class="ti-control-shuffle"></i> {{ __('defaultTheme.add_to_compare') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(@$product->hasDeal)
        <input type="hidden" id="discount_type_modal" value="{{ @$product->hasDeal->discount_type }}">
        <input type="hidden" id="discount_modal" value="{{ @$product->hasDeal->discount }}">
    @else
        @if(@$product->hasDiscount == 'yes')
            <input type="hidden" id="discount_type_modal" value="{{ $product->discount_type }}">
            <input type="hidden" id="discount_modal" value="{{ $product->discount }}">
        @else
            <input type="hidden" id="discount_type_modal" value="{{ $product->discount_type }}">
            <input type="hidden" id="discount_modal" value="0">
        @endif
    @endif

    @if(isModuleActive('WholeSale'))
        <input type="hidden" id="getWholesalePriceModal" value="@if(@$product->skus->first()->wholeSalePrices->count()){{ json_encode(@$product->skus->first()->wholeSalePrices) }} @else 0 @endif">
    @endif

    <input type="hidden" id="isWholeSaleActiveModal" value="{{ isModuleActive('WholeSale') }}">
    <input type="hidden" id="owner_modal" value="{{ encrypt($product->user_id) }}">
    <input type="hidden" id="vendor_id_modal" value="{{ @$product->seller->SellerAccount->vendor_id }}">
</div>
<!-- Modal::end  -->

<style>
    #theme_modal.quick_view_modal_custom .quick_view_modal_dialog {
        max-width: 960px;
        width: calc(100% - 40px);
        margin: 1.75rem auto;
    }

    #theme_modal.quick_view_modal_custom .quick_view_modal_content {
        border: 0;
        border-radius: 0;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 18px 50px rgba(0, 0, 0, 0.12);
    }

    #theme_modal.quick_view_modal_custom .quick_view_modal_grid {
        display: grid;
        grid-template-columns: minmax(0, 44%) minmax(0, 56%);
        background: #ffffff;
        min-height: auto;
    }

    #theme_modal.quick_view_modal_custom .quick_view_modal_close {
        position: absolute;
        top: 18px;
        right: 18px;
        z-index: 20;
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #222222;
        background: transparent;
    }

    #theme_modal.quick_view_modal_custom .quick_view_product_gallery {
        padding: 28px;
        background: #f8fafc;
        border-right: 1px solid #eef2f6;
        display: flex;
        flex-direction: column;
        gap: 12px;
        min-width: 0;
    }

    #theme_modal.quick_view_modal_custom .quick_view_tab_content {
        width: 100%;
        height: 390px;
        background: #ffffff;
        border: 1px solid #e8edf3;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #theme_modal.quick_view_modal_custom .quick_view_tab_content .tab-pane.active {
        width: 100%;
        height: 100%;
    }

    #theme_modal.quick_view_modal_custom .quick_view_img_div {
        width: 100%;
        height: 390px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #ffffff;
        padding: 14px;
    }

    #theme_modal.quick_view_modal_custom .quick_view_main_img {
        width: 100% !important;
        height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        object-fit: contain !important;
        display: block !important;
    }

    #theme_modal.quick_view_modal_custom .quick_view_thumb_area {
        position: relative;
    }

    #theme_modal.quick_view_modal_custom .quick_view_tab_thumb {
        display: flex;
        flex-wrap: nowrap;
        gap: 10px;
        margin: 0;
        padding: 0;
        overflow-x: auto;
        overflow-y: hidden;
        scroll-behavior: smooth;
        scrollbar-width: none;
    }

    #theme_modal.quick_view_modal_custom .quick_view_tab_thumb::-webkit-scrollbar {
        display: none;
    }

    #theme_modal.quick_view_modal_custom .quick_view_tab_thumb .nav-item {
        flex: 0 0 78px;
        width: 78px;
        margin: 0;
    }

    #theme_modal.quick_view_modal_custom .quick_view_tab_thumb .nav-link {
        width: 78px;
        height: 78px;
        padding: 4px;
        border: 1px solid #d9e1ea;
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all .2s ease;
    }

    #theme_modal.quick_view_modal_custom .quick_view_tab_thumb .nav-link.active {
        border-color: #081225;
        box-shadow: inset 0 0 0 1px #081225;
    }

    #theme_modal.quick_view_modal_custom .quick_view_thumb_img {
        width: 100%;
        height: 100%;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #theme_modal.quick_view_modal_custom .quick_view_thumb_img img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
    }

    #theme_modal.quick_view_modal_custom .quick_view_thumb_arrow {
        position: absolute;
        top: 50%;
        width: 30px;
        height: 30px;
        border: 0;
        border-radius: 50%;
        background: #081225;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        z-index: 5;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-50%);
        transition: opacity .2s ease, visibility .2s ease;
        cursor: pointer;
    }

    #theme_modal.quick_view_modal_custom .quick_view_thumb_prev {
        left: -8px;
    }

    #theme_modal.quick_view_modal_custom .quick_view_thumb_next {
        right: -8px;
    }

    #theme_modal.quick_view_modal_custom .quick_view_thumb_area:hover .quick_view_thumb_arrow {
        opacity: 1;
        visibility: visible;
    }

    #theme_modal.quick_view_modal_custom .product_details_wrapper {
        padding: 34px 40px 30px;
        min-width: 0;
        overflow: visible;
        max-height: none;
    }

    #theme_modal.quick_view_modal_custom .product_content_details {
        margin-bottom: 0 !important;
    }

    #theme_modal.quick_view_modal_custom .product_content_details > p {
        margin-bottom: 5px;
        font-size: 13px;
        line-height: 1.45;
        color: #5d6679;
    }

    #theme_modal.quick_view_modal_custom .product_content_details > p span:first-child {
        color: #667085;
    }

    #theme_modal.quick_view_modal_custom .product_content_details h3 {
        margin: 14px 0 10px;
        font-size: 20px;
        line-height: 1.3;
        font-weight: 700;
        color: #111827;
    }

    #theme_modal.quick_view_modal_custom .prise_text {
        margin-bottom: 8px;
        font-size: 18px;
        font-weight: 700;
        line-height: 1.3;
        color: #111827;
    }

    #theme_modal.quick_view_modal_custom .pro_details_disPrise {
        margin-bottom: 10px;
    }

    #theme_modal.quick_view_modal_custom .product_ratings {
        margin: 8px 0 18px;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    #theme_modal.quick_view_modal_custom .product_ratings span {
        font-size: 13px;
        color: #667085;
    }

    #theme_modal.quick_view_modal_custom .product_color_varient {
        margin-bottom: 16px !important;
    }

    #theme_modal.quick_view_modal_custom .product_color_varient h5 {
        margin-bottom: 8px !important;
        font-size: 14px;
        font-weight: 600;
        color: #111827;
    }

    #theme_modal.quick_view_modal_custom .color_List {
        gap: 8px !important;
    }

    #theme_modal.quick_view_modal_custom .size_btn,
    #theme_modal.quick_view_modal_custom .color_List .size_btn {
        min-width: 56px;
        height: 38px;
        padding: 0 14px;
        border: 1px solid #d0d5dd;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        font-size: 14px;
        font-weight: 500;
        white-space: nowrap;
    }

    #theme_modal.quick_view_modal_custom .selected_btn {
        border-color: #111827 !important;
    }

    #theme_modal.quick_view_modal_custom .quick_view_description {
        margin: 18px 0 18px;
        padding-top: 14px;
        border-top: 1px solid #eef2f6;
    }

    #theme_modal.quick_view_modal_custom .quick_view_description h4 {
        margin-bottom: 8px !important;
        font-size: 15px;
        font-weight: 700;
        line-height: 1.3;
        color: #111827;
    }

    #theme_modal.quick_view_modal_custom .quick_view_description_text {
        font-size: 13px;
        line-height: 1.7;
        color: #4b5563;
    }

    #theme_modal.quick_view_modal_custom .product_info {
        margin-top: 0;
    }

    #theme_modal.quick_view_modal_custom .product_info .single_pro_varient {
        margin-bottom: 14px;
    }

    #theme_modal.quick_view_modal_custom .product_info .single_pro_varient h5 {
        margin-bottom: 8px !important;
        font-size: 14px;
        font-weight: 600;
        color: #111827;
    }

    #theme_modal.quick_view_modal_custom .product_number_count {
        display: inline-flex;
        align-items: center;
        border: 1px solid #d0d5dd;
        border-radius: 0;
        overflow: hidden;
        background: #ffffff;
    }

    #theme_modal.quick_view_modal_custom .product_number_count .count_single_item {
        width: 38px;
        height: 38px;
        border: 0;
        background: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #344054;
        font-size: 14px;
    }

    #theme_modal.quick_view_modal_custom .product_number_count .input-number.qty {
        width: 44px;
        height: 38px;
        border: 0;
        border-left: 1px solid #d0d5dd;
        border-right: 1px solid #d0d5dd;
        text-align: center;
        font-size: 14px;
        color: #111827;
        background: #ffffff;
    }

    #theme_modal.quick_view_modal_custom .product_info .row.mt_15 h4,
    #theme_modal.quick_view_modal_custom .product_info .row.mt_20 h4 {
        margin-bottom: 0;
    }

    #theme_modal.quick_view_modal_custom #total_price_modal {
        font-weight: 700;
        color: #111827;
    }

    #theme_modal.quick_view_modal_custom #add_to_cart_div_modal {
        margin-top: 18px !important;
        margin-bottom: 10px !important;
    }

    #theme_modal.quick_view_modal_custom #add_to_cart_div_modal .col-md-6 {
        display: flex;
        margin-bottom: 10px;
    }

    #theme_modal.quick_view_modal_custom #add_to_cart_div_modal .home10_primary_btn2,
    #theme_modal.quick_view_modal_custom #add_to_cart_div_modal .home10_primary_btn4,
    #theme_modal.quick_view_modal_custom #add_to_cart_div_modal .amaz_primary_btn {
        width: 100%;
        min-height: 48px;
        padding: 0 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        font-size: 14px;
        font-weight: 600;
        line-height: 1;
        white-space: nowrap;
        letter-spacing: 0.2px;
        border-radius: 2px;
    }

    #theme_modal.quick_view_modal_custom .add_wish_compare {
        margin-top: 4px;
        gap: 18px;
        flex-wrap: wrap;
        align-items: center;
    }

    #theme_modal.quick_view_modal_custom .add_wish_compare .single_wish_compare {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 13px;
        line-height: 1.4;
        color: #344054;
        white-space: nowrap;
    }

    #theme_modal.quick_view_modal_custom .add_wish_compare .single_wish_compare i {
        font-size: 13px;
    }

    @media (max-width: 991.98px) {
        #theme_modal.quick_view_modal_custom .quick_view_modal_dialog {
            max-width: 720px;
            width: calc(100% - 24px);
        }

        #theme_modal.quick_view_modal_custom .quick_view_modal_grid {
            grid-template-columns: 1fr;
        }

        #theme_modal.quick_view_modal_custom .quick_view_product_gallery {
            border-right: 0;
            border-bottom: 1px solid #eef2f6;
            padding: 22px;
        }

        #theme_modal.quick_view_modal_custom .quick_view_tab_content,
        #theme_modal.quick_view_modal_custom .quick_view_img_div {
            height: 340px;
        }

        #theme_modal.quick_view_modal_custom .product_details_wrapper {
            padding: 28px 24px 24px;
        }
    }

    @media (max-width: 575.98px) {
        #theme_modal.quick_view_modal_custom .quick_view_modal_dialog {
            width: calc(100% - 18px);
            margin: 1rem auto;
        }

        #theme_modal.quick_view_modal_custom .quick_view_modal_close {
            top: 12px;
            right: 12px;
        }

        #theme_modal.quick_view_modal_custom .quick_view_product_gallery {
            padding: 16px;
            gap: 10px;
        }

        #theme_modal.quick_view_modal_custom .quick_view_tab_content,
        #theme_modal.quick_view_modal_custom .quick_view_img_div {
            height: 280px;
            padding: 10px;
        }

        #theme_modal.quick_view_modal_custom .quick_view_tab_thumb .nav-item {
            flex: 0 0 68px;
            width: 68px;
        }

        #theme_modal.quick_view_modal_custom .quick_view_tab_thumb .nav-link {
            width: 68px;
            height: 68px;
        }

        #theme_modal.quick_view_modal_custom .product_details_wrapper {
            padding: 22px 18px 22px;
        }

        #theme_modal.quick_view_modal_custom .product_content_details h3 {
            font-size: 18px;
        }

        #theme_modal.quick_view_modal_custom .prise_text {
            font-size: 17px;
        }

        #theme_modal.quick_view_modal_custom #add_to_cart_div_modal .home10_primary_btn2,
        #theme_modal.quick_view_modal_custom #add_to_cart_div_modal .home10_primary_btn4,
        #theme_modal.quick_view_modal_custom #add_to_cart_div_modal .amaz_primary_btn {
            min-height: 46px;
            font-size: 13px;
            padding: 0 14px;
        }

        #theme_modal.quick_view_modal_custom .add_wish_compare {
            gap: 12px;
        }

        #theme_modal.quick_view_modal_custom .add_wish_compare .single_wish_compare {
            font-size: 12px;
        }
    }
</style>

<script>
    (function($){
        "use strict";

        $(document).ready(function(){
            var productType = $('.product_type').val();

            if (productType == 2) {
                '@if (session()->has('item_details'))'+
                    '@foreach (session()->get('item_details') as $key => $item)'+
                        '@if ($item['attr_id'] === 1)'+
                            '@foreach ($item['value'] as $k => $value_name)'+
                                $(".modal_colors_{{$k}}").css("background", "{{ $item['code'][$k] }}");
                            '@endforeach'+
                        '@endif'+
                    '@endforeach'+
                '@endif'
            }
        });

        $(document)
            .off('click.quickViewSelectorImage', '#theme_modal .selector-img')
            .on('click.quickViewSelectorImage', '#theme_modal .selector-img', function () {
                let a_id = $(this).attr('data-id');
                let image = $(this).attr('src');

                $('#theme_modal .varintImg').attr('src', image);
                $('#theme_modal .varintImg').attr('data-zoom-image', image);
                $('#theme_modal .small-img').removeClass('active');
                $(a_id).addClass('active');
                $('#theme_modal #thumb_image_modal').val(image);
            });

        $(document)
            .off('click.quickViewThumbPrev', '#theme_modal .quick_view_thumb_prev')
            .on('click.quickViewThumbPrev', '#theme_modal .quick_view_thumb_prev', function () {
                const $thumbs = $('#theme_modal .quick_view_tab_thumb');
                $thumbs.animate({ scrollLeft: $thumbs.scrollLeft() - 110 }, 180);
            });

        $(document)
            .off('click.quickViewThumbNext', '#theme_modal .quick_view_thumb_next')
            .on('click.quickViewThumbNext', '#theme_modal .quick_view_thumb_next', function () {
                const $thumbs = $('#theme_modal .quick_view_tab_thumb');
                $thumbs.animate({ scrollLeft: $thumbs.scrollLeft() + 110 }, 180);
            });
    })(jQuery);
</script>