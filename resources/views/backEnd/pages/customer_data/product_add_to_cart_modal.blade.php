<div class="modal fade theme_modal quick_view_modal_custom" id="theme_modal" tabindex="-1" role="dialog" aria-labelledby="theme_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered quick_view_modal_dialog" role="document">
        <div class="modal-content quick_view_modal_content">
            <div class="modal-header justify-content-end quick_view_modal_header">
                <button type="button" class="close quick_view_modal_close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="{{ __('common.close') }}">
                    <i class="ti-close"></i>
                </button>
            </div>

            <div class="modal-body p-0">
                <div class="product_quick_view quick_view_modal_grid">
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

                            <h5 class="prise_text d-flex align-items-center gradient-color2">
                                {{ getProductDiscountedPrice($product) }}
                            </h5>

                            <div class="pro_details_disPrise d-flex align-items-center gap_15">
                                <h4 class="discount_prise m-0">
                                    <span class="text-decoration-line-through">
                                        @if($product->hasDeal || $product->hasDiscount == 'yes')
                                            <span>{{ single_price($product->skus->max('selling_price')) }}</span>
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
                                                            id="modal_radio_{{ $product->id }}_{{ $ks }}"
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
                                    {{ selling_price($product->skus->first()->selling_price, $product->hasDeal->discount_type, $product->hasDeal->discount) }}
                                @else
                                    @if($product->hasDiscount == 'yes')
                                        {{ selling_price($product->skus->first()->selling_price, $product->discount_type, $product->discount) }}
                                    @else
                                        {{ $product->skus->first()->selling_price }}
                                    @endif
                                @endif
                            ">

                            <input type="hidden" name="final_price" id="final_price_modal" value="
                                @if(@$product->hasDeal)
                                    {{ selling_price($product->skus->first()->selling_price, $product->hasDeal->discount_type, $product->hasDeal->discount) }}
                                @else
                                    @if($product->hasDiscount == 'yes')
                                        {{ selling_price($product->skus->first()->selling_price, $product->discount_type, $product->discount) }}
                                    @else
                                        {{ $product->skus->first()->selling_price }}
                                    @endif
                                @endif
                            ">

                            <input type="hidden" value="{{ textLimit($product->product_name, 28) }}" id="product_name_modal">
                            <input type="hidden" value="{{ singleProductURL(@$product->seller->slug, @$product->slug) }}" id="product_url_modal">
                            <input type="hidden" name="thumb_image" id="thumb_image_modal" value="@if ($product->thum_img != null) {{ showImage($product->thum_img) }} @else {{ showImage($product->product->thumbnail_image_source) }} @endif">

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

                                <div class="row mt_20">
                                    <h4>
                                        <span>{{ __('common.total') }}:</span>
                                        <span id="total_price_modal">
                                            @if(@$product->hasDeal)
                                                {{ single_price(selling_price(@$product->skus->first()->selling_price, @$product->hasDeal->discount_type, @$product->hasDeal->discount) * $product->product->minimum_order_qty) }}
                                            @else
                                                @if($product->hasDiscount == 'yes')
                                                    {{ single_price(selling_price(@$product->skus->first()->selling_price, @$product->discount_type, @$product->discount) * $product->product->minimum_order_qty) }}
                                                @else
                                                    {{ single_price(@$product->skus->first()->selling_price * $product->product->minimum_order_qty) }}
                                                @endif
                                            @endif
                                        </span>
                                    </h4>
                                </div>

                                <div class="row mt_30" id="add_to_cart_div_modal">
                                    @if ($product->stock_manage == 1 && $product->skus->first()->product_stock >= $product->product->minimum_order_qty || $product->stock_manage == 0)
                                        <div class="col-md-6">
                                            <a href="" id="add_to_cart_btn_modal" class="fix-gr-bg mr-10 primary-btn radius_5px text-center d-block py-2">{{ __('common.add_to_cart') }}</a>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="#" class="fix-gr-bg primary-btn radius_5px text-center d-block py-2 buy_now_btn_modal" data-id="{{ $product->id }}" data-type="product">{{ __('common.buy_now') }}</a>
                                        </div>
                                    @else
                                        <div class="col-md-6">
                                            <button type="button" disabled class="amaz_primary_btn style2 mb_20 add_to_cart text-uppercase flex-fill text-center w-100">{{ __('defaultTheme.out_of_stock') }}</button>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if(!empty(trim(strip_tags(@$product->product->description))))
                                <div class="quick_view_description mt_30">
                                    <h4 class="font_16 f_w_700 mb_10">{{ __('common.description') }}</h4>
                                    <div class="quick_view_description_text">
                                        {!! @$product->product->description !!}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #theme_modal.quick_view_modal_custom .quick_view_modal_dialog {
        max-width: 1040px;
        width: calc(100% - 40px);
    }

    #theme_modal.quick_view_modal_custom .quick_view_modal_content {
        border: 0;
        border-radius: 0;
        overflow: hidden;
        background: #ffffff;
    }

    #theme_modal.quick_view_modal_custom .quick_view_modal_header {
        position: absolute;
        top: 0;
        right: 0;
        z-index: 20;
        border: 0;
        background: transparent;
        padding: 22px 24px 0 0;
    }

    #theme_modal.quick_view_modal_custom .quick_view_modal_close {
        width: 36px;
        height: 36px;
        border: 0;
        background: transparent;
        color: #101828;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        cursor: pointer;
    }

    #theme_modal.quick_view_modal_custom .quick_view_modal_grid {
        display: grid;
        grid-template-columns: minmax(0, 52%) minmax(0, 48%);
        min-height: 620px;
        background: #ffffff;
    }

    #theme_modal.quick_view_modal_custom .quick_view_product_gallery {
        padding: 34px;
        background: #f8fafc;
        border-right: 1px solid #eef2f6;
        display: flex;
        flex-direction: column;
        gap: 16px;
        min-width: 0;
    }

    #theme_modal.quick_view_modal_custom .quick_view_tab_content {
        width: 100%;
        flex: 1 1 auto;
        min-height: 470px;
        background: #ffffff;
        border: 1px solid #eef2f6;
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
        height: 470px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #ffffff;
    }

    #theme_modal.quick_view_modal_custom .quick_view_main_img {
        width: 100% !important;
        height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        object-fit: contain !important;
        display: block !important;
    }

    #theme_modal.quick_view_modal_custom .quick_view_tab_thumb {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin: 0;
        padding: 0;
    }

    #theme_modal.quick_view_modal_custom .quick_view_tab_thumb .nav-item {
        width: 100%;
        margin: 0;
    }

    #theme_modal.quick_view_modal_custom .quick_view_tab_thumb .nav-link {
        width: 100%;
        height: 92px;
        padding: 6px;
        border: 1px solid #d9e1ea;
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #theme_modal.quick_view_modal_custom .quick_view_tab_thumb .nav-link.active {
        border-color: #081225;
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

    #theme_modal.quick_view_modal_custom .product_details_wrapper {
        padding: 48px 44px 34px;
        max-height: 82vh;
        overflow-y: auto;
        min-width: 0;
    }

    #theme_modal.quick_view_modal_custom .quick_view_description {
        padding-top: 22px;
        border-top: 1px solid #eef2f6;
    }

    #theme_modal.quick_view_modal_custom .quick_view_description h4 {
        color: #101828;
        line-height: 1.3;
    }

    #theme_modal.quick_view_modal_custom .quick_view_description_text {
        max-height: 140px;
        overflow-y: auto;
        color: #475467;
        font-size: 13.5px;
        line-height: 1.65;
    }

    #theme_modal.quick_view_modal_custom .quick_view_description_text p {
        margin-bottom: 8px;
    }

    @media (max-width: 991.98px) {
        #theme_modal.quick_view_modal_custom .quick_view_modal_dialog {
            width: calc(100% - 24px);
            max-width: 720px;
        }

        #theme_modal.quick_view_modal_custom .quick_view_modal_grid {
            grid-template-columns: 1fr;
            min-height: auto;
        }

        #theme_modal.quick_view_modal_custom .quick_view_product_gallery {
            border-right: 0;
            border-bottom: 1px solid #eef2f6;
            padding: 28px;
        }

        #theme_modal.quick_view_modal_custom .quick_view_tab_content {
            min-height: 400px;
        }

        #theme_modal.quick_view_modal_custom .quick_view_img_div {
            height: 400px;
        }

        #theme_modal.quick_view_modal_custom .product_details_wrapper {
            max-height: none;
            padding: 32px 28px 28px;
        }
    }

    @media (max-width: 575.98px) {
        #theme_modal.quick_view_modal_custom .quick_view_modal_dialog {
            width: calc(100% - 18px);
        }

        #theme_modal.quick_view_modal_custom .quick_view_modal_header {
            padding: 12px 12px 0 0;
        }

        #theme_modal.quick_view_modal_custom .quick_view_product_gallery {
            padding: 18px;
            gap: 12px;
        }

        #theme_modal.quick_view_modal_custom .quick_view_tab_content {
            min-height: 300px;
        }

        #theme_modal.quick_view_modal_custom .quick_view_img_div {
            height: 300px;
        }

        #theme_modal.quick_view_modal_custom .quick_view_tab_thumb {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 8px;
        }

        #theme_modal.quick_view_modal_custom .quick_view_tab_thumb .nav-link {
            height: 70px;
            padding: 4px;
        }

        #theme_modal.quick_view_modal_custom .product_details_wrapper {
            padding: 24px 18px 22px;
        }

        #theme_modal.quick_view_modal_custom .quick_view_description_text {
            max-height: 150px;
        }
    }
</style>

<script>
    (function ($) {
        "use strict";

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
            .off('click.quickViewBuyNow', '#theme_modal .buy_now_btn_modal')
            .on('click.quickViewBuyNow', '#theme_modal .buy_now_btn_modal', function (event) {
                event.preventDefault();

                buyNow(
                    $('#product_sku_id_modal').val(),
                    $('#seller_id_modal').val(),
                    $('#qty_modal').val(),
                    $('#base_sku_price_modal').val(),
                    $('#shipping_type').val(),
                    'product'
                );
            });
    })(jQuery);
</script>