<div class="checkout_v3_area">
    @if(count($cartData) > 0)
        @foreach($cartData as $seller_id => $cartItems)
            @php
                $all_select_count = 0;
                $subtotal = 0;
                $discount = 0;
                $actual_price = 0;
                $shipping_cost = 0;
                $sellect_seller = 0;
                $selected_product_check = 0;

                $seller = App\Models\User::where('id', $seller_id)->first();
                $select_count = count($cartItems);
            @endphp

            @foreach($cartItems as $m => $data)
                @php
                    if ($data->is_select == 1) {
                        $select_count = $select_count - 1;
                        $selected_product_check++;
                    }
                @endphp
            @endforeach

            <form id="cart_form">
                <div class="checkout_v3_left d-flex justify-content-end mb-0">
                    <div class="checkout_v3_inner w-100">
                        <div class="cart_package_box mb_20">
                            @php
                                $cartStoreName = parentStoreName($seller ?? null);
                                $cartVendorId = data_get($seller, 'sellerAccount,vendor_id', ($seller->name ?? $seller->first_name ?? ''));
                                $cartSellerUrl = ($seller && $seller->slug) ? route('frontend.seller', $seller->slug) : route('frontend.seller', base64_encode($seller_id));
                            @endphp
                            <div class="cart_package_head">
                                <div class="cart_package_head_inner">
                                    <a href="{{ $cartSellerUrl }}" class="cart_package_label cart_store_label">
                                        {{ __('common.store') }}: {{ $cartStoreName }}
                                    </a>

                                    <span class="cart_package_divider">|</span>

                                    <span class="cart_package_label cart_vendor_label">
                                        {{ __('Vendor') }}: {{ $cartVendorId }}
                                    </span>
                                </div>
                            </div>

                            <div class="cart_package_table_wrap">
                                <table class="cart_package_table">
                                    <colgroup>
                                        <col class="cart_product_col">
                                        <col class="cart_price_col">
                                        <col class="cart_qty_col">
                                        <col class="cart_subtotal_col">
                                        <col class="cart_remove_col">
                                    </colgroup>

                                    <thead>
                                        <tr>
                                            <th>{{ getNumberTranslate(count($cartItems)) }} {{ __('common.items') }}</th>
                                            <th class="cart_text_center">{{ __('common.price') }}</th>
                                            <th class="cart_text_center">{{ __('common.quantity') }}</th>
                                            <th class="cart_text_center">{{ __('common.subtotal') }}</th>
                                            <th class="cart_text_right">{{ __('common.remove') }}</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach($cartItems as $key => $cart)
                                            @if($cart->product_type == 'product')
                                                @php
                                                    $pro_price = 0;

                                                    if (isModuleActive('WholeSale')) {
                                                        $w_main_price = 0;
                                                        $wholeSalePrices = @$cart->product->wholeSalePrices;

                                                        if ($wholeSalePrices && $wholeSalePrices->count()) {
                                                            foreach ($wholeSalePrices as $w_p) {
                                                                if (($w_p->min_qty <= $cart->qty) && ($w_p->max_qty >= $cart->qty)) {
                                                                    $w_main_price = $w_p->sell_price;
                                                                } elseif ($w_p->max_qty < $cart->qty) {
                                                                    $w_main_price = $w_p->sell_price;
                                                                }
                                                            }
                                                        }

                                                        $pro_price = $w_main_price != 0 ? $w_main_price : @$cart->product->sell_price;
                                                    } else {
                                                        $pro_price = @$cart->product->sell_price;
                                                    }

                                                    if ($cart->is_select == 1) {
                                                        $subtotal += $pro_price * $cart->qty;
                                                    }

                                                    $productName = @$cart->product->product->product_name;
                                                    $productUrl = singleProductURL(@$cart->seller->slug, @$cart->product->product->slug);
                                                    $productImage = @$cart->product->product->product->product_type == 1
                                                        ? showImage(@$cart->product->product->product->thumbnail_image_source)
                                                        : showImage(@$cart->product->sku->variant_image ? @$cart->product->sku->variant_image : @$cart->product->product->product->thumbnail_image_source);
                                                @endphp

                                                <tr>
                                                    <td class="cart_product_cell">
                                                        <a href="{{ $productUrl }}" class="cart_product_link">
                                                            <span class="cart_product_thumb">
                                                                <img
                                                                    src="{{ $productImage }}"
                                                                    alt="{{ textLimit($productName, 35) }}"
                                                                    title="{{ textLimit($productName, 35) }}"
                                                                >
                                                            </span>

                                                            <span class="cart_product_info">
                                                                <span class="cart_product_name theme_hover">
                                                                    {{ textLimit($productName, 35) }}
                                                                </span>

                                                                @if(@$cart->product->product->product->product_type == 2)
                                                                    <span class="cart_product_meta">
                                                                        @foreach(@$cart->product->product_variations as $variationKey => $combination)
                                                                            @php
                                                                                $attrName = trim((string) (@$combination->attribute->name ?? ''));
                                                                                $attrNameLower = strtolower($attrName);
                                                                                $rawValue = trim((string) (@$combination->attribute_value->value ?? ''));
                                                                                $rawTitle = trim((string) (@$combination->attribute_value->title ?? ''));
                                                                                $colorName = trim((string) (@$combination->attribute_value->color->name ?? ''));

                                                                                if (str_contains($attrNameLower, 'color')) {
                                                                                    $displayValue = $colorName !== '' ? $colorName : ($rawTitle !== '' ? $rawTitle : $rawValue);
                                                                                } else {
                                                                                    $displayValue = (preg_match('/^\d+$/', $rawValue) && $rawTitle !== '' && !preg_match('/^\d+$/', $rawTitle))
                                                                                        ? $rawTitle
                                                                                        : ($rawValue !== '' ? $rawValue : $rawTitle);
                                                                                }
                                                                            @endphp

                                                                            @if($attrName !== '' || $displayValue !== '')
                                                                                {{ $attrName }}: {{ $displayValue }}@if($variationKey < count(@$cart->product->product_variations) - 1), @endif
                                                                            @endif
                                                                        @endforeach
                                                                    </span>
                                                                @endif
                                                            </span>
                                                        </a>
                                                    </td>

                                                    <td class="cart_text_center cart_price_cell">
                                                        @if($cart->product->product->hasDeal)
                                                            @if($cart->product->product->hasDeal->discount > 0)
                                                                @if($cart->product->product->hasDeal->discount_type == 0)
                                                                    <span class="green_badge text-nowrap">-{{ getNumberTranslate($cart->product->product->hasDeal->discount) }}%</span>
                                                                @else
                                                                    <span class="green_badge text-nowrap">-{{ single_price($cart->product->product->hasDeal->discount) }}</span>
                                                                @endif
                                                            @endif
                                                        @else
                                                            @if(@$cart->product->product->hasDiscount == 'yes')
                                                                @if($cart->product->product->discount_type == 0)
                                                                    <span class="green_badge text-nowrap">-{{ getNumberTranslate($cart->product->product->discount) }}%</span>
                                                                @else
                                                                    <span class="green_badge text-nowrap">-{{ single_price($cart->product->product->discount) }}</span>
                                                                @endif
                                                            @endif
                                                        @endif

                                                        <h4 class="font_16 f_w_700 m-0 text-nowrap set_base_price{{$cart->id}}">
                                                            {{ single_price($pro_price) }}
                                                        </h4>
                                                        <input type="hidden" class="get_base_price{{$cart->id}}" value="{{ single_price($pro_price) }}">
                                                    </td>

                                                    <td class="cart_text_center cart_qty_cell">
                                                        <div class="product_number_count style_4 cart_quantity_control" data-target="amount-3">
                                                            <button
                                                                class="count_single_item inumber_decrement change_qty"
                                                                data-qty_id="#qty_{{$cart->id}}"
                                                                data-change_amount="1"
                                                                data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                data-minimum_qty="#minimum_qty_{{$cart->id}}"
                                                                data-product_stock="{{ @$cart->product->product_stock }}"
                                                                data-stock_manage="{{ @$cart->product->product->stock_manage }}"
                                                                data-wholesale="#getWholesalePrice_{{$cart->id}}"
                                                                data-cart_id="{{$cart->id}}"
                                                                type="button"
                                                                value="-"
                                                            >
                                                                <i class="ti-minus"></i>
                                                            </button>

                                                            <input
                                                                name="qty[]"
                                                                id="qty_{{$cart->id}}"
                                                                maxlength="12"
                                                                value="{{ getNumberTranslate($cart->qty) }}"
                                                                class="count_single_item input-number qty"
                                                                readonly
                                                                type="text"
                                                            >

                                                            <input type="hidden" value="{{$cart->id}}" name="cart_id[]">
                                                            <input type="hidden" id="maximum_qty_{{$cart->id}}" value="{{ @$cart->product->product->product->max_order_qty }}">
                                                            <input type="hidden" id="minimum_qty_{{$cart->id}}" value="{{ @$cart->product->product->product->minimum_order_qty }}">

                                                            <button
                                                                class="count_single_item number_increment change_qty"
                                                                data-qty_id="#qty_{{$cart->id}}"
                                                                data-change_amount="1"
                                                                data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                data-minimum_qty="#minimum_qty_{{$cart->id}}"
                                                                data-product_stock="{{ @$cart->product->product_stock }}"
                                                                data-stock_manage="{{ @$cart->product->product->stock_manage }}"
                                                                data-wholesale="#getWholesalePrice_{{$cart->id}}"
                                                                data-cart_id="{{$cart->id}}"
                                                                type="button"
                                                                value="+"
                                                            >
                                                                <i class="ti-plus"></i>
                                                            </button>

                                                            @if(isModuleActive('WholeSale'))
                                                                <input type="hidden" id="getWholesalePrice_{{$cart->id}}" value="@if(@$cart->product->wholeSalePrices->count()){{ json_encode(@$cart->product->wholeSalePrices) }} @else 0 @endif">
                                                            @endif
                                                        </div>
                                                    </td>

                                                    <td class="cart_text_center cart_subtotal_cell">
                                                        <h4 class="font_16 f_w_700 m-0 lh-1 text-nowrap">
                                                            {{ single_price($cart->total_price) }}
                                                        </h4>
                                                    </td>

                                                    <td class="cart_text_right cart_remove_cell">
                                                        <button
                                                            type="button"
                                                            class="cart_remove_btn close_icon style_2 lh-1 cart_item_delete_btn cursor_pointer"
                                                            data-id="{{$cart->id}}"
                                                            data-product_id="{{$cart->product_id}}"
                                                            data-unique_id="#delete_item_{{$cart->id}}"
                                                            id="delete_item_{{$cart->id}}"
                                                            aria-label="{{ __('common.remove') }}"
                                                        >
                                                            <svg width="12.249" height="15.076" viewBox="0 0 12.249 15.076" aria-hidden="true">
                                                                <g transform="translate(-48)">
                                                                    <path data-name="Path 1449" d="M59.071,1.884H56.48V1.413A1.415,1.415,0,0,0,55.067,0H53.182a1.415,1.415,0,0,0-1.413,1.413v.471H49.178A1.179,1.179,0,0,0,48,3.062V4.711a.471.471,0,0,0,.471.471h.257l.407,8.547a1.412,1.412,0,0,0,1.412,1.346H57.7a1.412,1.412,0,0,0,1.412-1.346l.407-8.547h.257a.471.471,0,0,0,.471-.471V3.062A1.179,1.179,0,0,0,59.071,1.884Zm-6.36-.471a.472.472,0,0,1,.471-.471h1.884a.472.472,0,0,1,.471.471v.471H52.711ZM48.942,3.062a.236.236,0,0,1,.236-.236h9.893a.236.236,0,0,1,.236.236V4.24H48.942Zm9.23,10.623a.471.471,0,0,1-.471.449H50.547a.471.471,0,0,1-.471-.449l-.4-8.5h8.905Z" fill="#00124e"></path>
                                                                    <path data-name="Path 1450" d="M240.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,1,0-.942,0V214.6A.471.471,0,0,0,240.471,215.067Z" transform="translate(-186.347 -201.875)" fill="#00124e"></path>
                                                                    <path data-name="Path 1451" d="M320.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,1,0-.942,0V214.6A.471.471,0,0,0,320.471,215.067Z" transform="translate(-263.991 -201.875)" fill="#00124e"></path>
                                                                    <path data-name="Path 1452" d="M160.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,0,0-.942,0V214.6A.471.471,0,0,0,160.471,215.067Z" transform="translate(-108.702 -201.875)" fill="#00124e"></path>
                                                                </g>
                                                            </svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @else
                                                @php
                                                    $giftCardPrice = $cart->price ?? @$cart->giftCard->sell_price;

                                                    if ($cart->is_select == 1) {
                                                        $subtotal += $giftCardPrice * $cart->qty;
                                                    }
                                                @endphp

                                                <tr>
                                                    <td class="cart_product_cell">
                                                        <a href="{{ route('frontend.gift-card.show', $cart->giftCard->sku) }}" class="cart_product_link">
                                                            <span class="cart_product_thumb">
                                                                <img
                                                                    src="{{ showImage(@$cart->giftCard->thumbnail_image) }}"
                                                                    alt="{{ textLimit(@$cart->giftCard->name, 35) }}"
                                                                    title="{{ textLimit(@$cart->giftCard->name, 35) }}"
                                                                >
                                                            </span>

                                                            <span class="cart_product_info">
                                                                <span class="cart_product_name theme_hover">
                                                                    {{ textLimit(@$cart->giftCard->name, 35) }}
                                                                </span>
                                                            </span>
                                                        </a>
                                                    </td>

                                                    <td class="cart_text_center cart_price_cell">
                                                        <h4 class="font_16 f_w_700 m-0 text-nowrap">
                                                            {{ single_price($giftCardPrice) }}
                                                        </h4>
                                                    </td>

                                                    <td class="cart_text_center cart_qty_cell">
                                                        <div class="product_number_count style_4 cart_quantity_control" data-target="amount-1">
                                                            <input type="hidden" value="{{$cart->id}}" name="cart_id[]">
                                                            <input type="hidden" id="maximum_qty_{{$cart->id}}" value="">
                                                            <input type="hidden" id="minimum_qty_{{$cart->id}}" value="1">

                                                            <button class="count_single_item inumber_decrement change_qty" data-qty_id="#qty_{{$cart->id}}" data-cart_id="{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}" data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="0" data-stock_manage="0" data-wholesale="#getWholesalePrice_{{$cart->id}}" type="button" value="-">
                                                                <i class="ti-minus"></i>
                                                            </button>

                                                            <input name="qty[]" id="qty_{{$cart->id}}" value="{{ getNumberTranslate($cart->qty) }}" class="count_single_item input-number qty" type="text">

                                                            <button class="count_single_item number_increment change_qty" data-qty_id="#qty_{{$cart->id}}" data-cart_id="{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}" data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="0" data-stock_manage="0" data-wholesale="#getWholesalePrice_{{$cart->id}}" type="button" value="+">
                                                                <i class="ti-plus"></i>
                                                            </button>

                                                            @if(isModuleActive('WholeSale'))
                                                                <input type="hidden" id="getWholesalePrice_{{$cart->id}}" value="0">
                                                            @endif
                                                        </div>
                                                    </td>

                                                    <td class="cart_text_center cart_subtotal_cell">
                                                        <h4 class="font_16 f_w_700 m-0 lh-1 text-nowrap">
                                                            {{ single_price($cart->total_price) }}
                                                        </h4>
                                                    </td>

                                                    <td class="cart_text_right cart_remove_cell">
                                                        <button type="button" class="cart_remove_btn close_icon style_2 lh-1 cart_item_delete_btn cursor_pointer" data-id="{{$cart->id}}" data-product_id="{{$cart->product_id}}" data-unique_id="#delete_item_{{$cart->id}}" id="delete_item_{{$cart->id}}" aria-label="{{ __('common.remove') }}">
                                                            <svg width="12.249" height="15.076" viewBox="0 0 12.249 15.076" aria-hidden="true">
                                                                <g transform="translate(-48)">
                                                                    <path data-name="Path 1449" d="M59.071,1.884H56.48V1.413A1.415,1.415,0,0,0,55.067,0H53.182a1.415,1.415,0,0,0-1.413,1.413v.471H49.178A1.179,1.179,0,0,0,48,3.062V4.711a.471.471,0,0,0,.471.471h.257l.407,8.547a1.412,1.412,0,0,0,1.412,1.346H57.7a1.412,1.412,0,0,0,1.412-1.346l.407-8.547h.257a.471.471,0,0,0,.471-.471V3.062A1.179,1.179,0,0,0,59.071,1.884Zm-6.36-.471a.472.472,0,0,1,.471-.471h1.884a.472.472,0,0,1,.471.471v.471H52.711ZM48.942,3.062a.236.236,0,0,1,.236-.236h9.893a.236.236,0,0,1,.236.236V4.24H48.942Zm9.23,10.623a.471.471,0,0,1-.471.449H50.547a.471.471,0,0,1-.471-.449l-.4-8.5h8.905Z" fill="#00124e"></path>
                                                                    <path data-name="Path 1450" d="M240.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,1,0-.942,0V214.6A.471.471,0,0,0,240.471,215.067Z" transform="translate(-186.347 -201.875)" fill="#00124e"></path>
                                                                    <path data-name="Path 1451" d="M320.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,1,0-.942,0V214.6A.471.471,0,0,0,320.471,215.067Z" transform="translate(-263.991 -201.875)" fill="#00124e"></path>
                                                                    <path data-name="Path 1452" d="M160.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,0,0-.942,0V214.6A.471.471,0,0,0,160.471,215.067Z" transform="translate(-108.702 -201.875)" fill="#00124e"></path>
                                                                </g>
                                                            </svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endif

                                            @if($cart->is_select == 1)
                                                @php
                                                    $actual_price += $cart->total_price;
                                                @endphp
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="d-flex gap_10 align-items-center flex-wrap mt_20">
                            <div class="d-none d-lg-flex align-items-center gap_10 flex-fill flex-wrap">
                                <a href="{{ url('/') }}" class="amaz_primary_btn2 style3">{{ __('defaultTheme.continue_shopping') }}</a>
                            </div>
                            <a class="amaz_primary_btn min_200 style2 cursor_pointer @if (count($cartData) > 0) process_to_checkout_check @endif" data-value="{{$selected_product_check}}" data-id="{{encrypt($seller_id)}}">{{ __('defaultTheme.proceed_to_checkout') }}</a>
                        </div>
                    </div>

                    <input type="hidden" id="isWholeSaleActive" value="{{ isModuleActive('WholeSale') }}">
                </div>
            </form>

            <div class="checkout_v3_right d-flex justify-content-start checkout_summery_div mb_10">
                @php
                    $grand_total = $actual_price;
                    $discount = $subtotal - $actual_price;
                @endphp
                <div class="order_sumery_box flex-fill">
                    <h3 class="check_v3_title mb_25">{{ __('common.order_summary') }}</h3>
                    <div class="subtotal_lists">
                        <div class="single_total_list d-flex align-items-center">
                            <div class="single_total_left flex-fill">
                                <h4>{{ __('common.subtotal') }}</h4>
                            </div>
                            <div class="single_total_right">
                                <span>+ {{ single_price($subtotal) }}</span>
                            </div>
                        </div>
                        <div class="single_total_list d-flex align-items-center flex-wrap">
                            <div class="single_total_left flex-fill">
                                <h4>{{ __('common.shipping_charge') }}</h4>
                            </div>
                            <div class="single_total_right">
                                <span>{{ __('defaultTheme.calculated_at_next_step') }}</span>
                            </div>
                        </div>
                        <div class="single_total_list d-flex align-items-center flex-wrap">
                            <div class="single_total_left flex-fill">
                                <h4>{{ __('common.discount') }}</h4>
                            </div>
                            <div class="single_total_right">
                                <span>- {{ single_price($discount) }}</span>
                            </div>
                        </div>
                        <div class="single_total_list d-flex align-items-center flex-wrap">
                            <div class="single_total_left flex-fill">
                                <h4>{{ __('common.vat/tax/gst') }}</h4>
                            </div>
                            <div class="single_total_right">
                                <span>{{ __('defaultTheme.calculated_at_next_step') }}</span>
                            </div>
                        </div>
                        <div class="total_amount d-flex align-items-center flex-wrap">
                            <div class="single_total_left flex-fill">
                                <span class="total_text">{{ __('common.total') }}</span>
                            </div>
                            <div class="single_total_right">
                                <span class="total_text"><span>{{ single_price($grand_total) }}</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <form id="cart_form">
            <div class="checkout_v3_left d-flex justify-content-end mb-0"></div>
            <div class="col-lg-12 text-center mb_50">
                <span class="product_not_found">{{ __('defaultTheme.no_product_found') }}</span>
            </div>
        </form>

        <div class="checkout_v3_right d-flex justify-content-start checkout_summery_div mb_10">
            <div class="order_sumery_box flex-fill">
                <h3 class="check_v3_title mb_25">{{ __('common.order_summary') }}</h3>
                <div class="subtotal_lists">
                    <div class="single_total_list d-flex align-items-center">
                        <div class="single_total_left flex-fill"><h4>{{ __('common.subtotal') }}</h4></div>
                        <div class="single_total_right"><span>+ {{ single_price(0) }}</span></div>
                    </div>
                    <div class="single_total_list d-flex align-items-center flex-wrap">
                        <div class="single_total_left flex-fill"><h4>{{ __('common.shipping_charge') }}</h4></div>
                        <div class="single_total_right"><span>{{ __('defaultTheme.calculated_at_next_step') }}</span></div>
                    </div>
                    <div class="single_total_list d-flex align-items-center flex-wrap">
                        <div class="single_total_left flex-fill"><h4>{{ __('common.discount') }}</h4></div>
                        <div class="single_total_right"><span>- {{ single_price(0) }}</span></div>
                    </div>
                    <div class="single_total_list d-flex align-items-center flex-wrap">
                        <div class="single_total_left flex-fill"><h4>{{ __('common.vat/tax/gst') }}</h4></div>
                        <div class="single_total_right"><span>{{ __('defaultTheme.calculated_at_next_step') }}</span></div>
                    </div>
                    <div class="total_amount d-flex align-items-center flex-wrap">
                        <div class="single_total_left flex-fill"><span class="total_text">{{ __('common.total') }}</span></div>
                        <div class="single_total_right"><span class="total_text"><span>{{ single_price(0) }}</span></span></div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
    .cart_package_box {
        width: 100%;
        max-width: 100%;
        border: 1px solid #ececec;
        border-radius: 8px;
        background: #fff;
        overflow: hidden;
    }

    .cart_package_head {
        display: flex;
        align-items: center;
        min-height: 45px;
        padding: 12px 16px;
        background: #f9fafb;
        border-bottom: 1px solid #ececec;
    }

    .cart_package_head_inner {
        width: 100%;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        min-width: 0;
    }

    .cart_package_label,
    .cart_package_label:hover,
    .cart_package_label:focus {
        display: inline-flex !important;
        align-items: center;
        width: auto !important;
        max-width: 100%;
        padding: 0 !important;
        margin: 0 !important;
        color: #111827 !important;
        background: transparent !important;
        background-image: none !important;
        border: 0 !important;
        outline: 0 !important;
        box-shadow: none !important;
        text-decoration: none !important;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.35;
        white-space: nowrap;
    }

    .cart_package_label::before,
    .cart_package_label::after {
        display: none !important;
        content: none !important;
    }

    .cart_package_divider {
        color: #9ca3af;
        font-weight: 700;
        line-height: 1;
    }

    @media (max-width: 767px) {
        .cart_package_head {
            padding: 14px 16px;
        }

        .cart_package_head_inner {
            display: block;
        }

        .cart_package_label {
            display: block !important;
            width: 100% !important;
            white-space: normal !important;
            margin-bottom: 8px !important;
        }

        .cart_package_label:last-child {
            margin-bottom: 0 !important;
        }

        .cart_package_divider {
            display: none !important;
        }
    }

    .cart_package_table_wrap {
        width: 100%;
        max-width: 100%;
        padding: 8px 12px 10px;
        overflow: visible;
    }

    .cart_package_table {
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100% !important;
        table-layout: fixed;
        border-collapse: separate;
        border-spacing: 0 10px;
        margin: -10px 0 0;
    }

    .cart_package_table col.cart_product_col { width: 43%; }
    .cart_package_table col.cart_price_col { width: 15%; }
    .cart_package_table col.cart_qty_col { width: 18%; }
    .cart_package_table col.cart_subtotal_col { width: 14%; }
    .cart_package_table col.cart_remove_col { width: 10%; }

    .cart_package_table thead th {
        border: 0;
        border-bottom: 1px solid #ececec;
        background: #fbfcfd;
        color: #111827;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.3;
        text-transform: uppercase;
        padding: 12px 16px;
        vertical-align: middle;
        white-space: nowrap;
    }

    .cart_package_table thead th:first-child {
        text-align: left;
    }

    .cart_package_table thead th:last-child {
        padding-right: 14px;
    }

    .cart_package_table tbody tr {
        background: #fff;
    }

    .cart_package_table tbody td {
        height: 124px;
        padding: 18px 16px;
        vertical-align: middle;
        border-top: 1px solid #ececec;
        border-bottom: 1px solid #ececec;
        background: #fff;
    }

    .cart_package_table tbody td:first-child {
        border-left: 1px solid #ececec;
    }

    .cart_package_table tbody td:last-child {
        border-right: 1px solid #ececec;
    }

    .cart_text_center {
        text-align: center;
    }

    .cart_text_right {
        text-align: right;
    }

    .cart_product_cell {
        padding-left: 16px !important;
        padding-right: 16px !important;
    }

    .cart_product_link {
        display: flex !important;
        align-items: center !important;
        gap: 18px !important;
        width: 100%;
        min-width: 0;
        color: inherit;
        text-decoration: none !important;
    }

    .cart_product_thumb {
        flex: 0 0 76px;
        width: 76px;
        height: 96px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #fff;
    }

    .cart_product_thumb img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        display: block;
    }

    .cart_product_info {
        display: block;
        flex: 1 1 auto;
        min-width: 0;
        text-align: left;
    }

    .cart_product_name {
        display: block;
        width: 100%;
        margin: 0 0 6px !important;
        color: #111827;
        font-size: 16px;
        font-weight: 700;
        line-height: 1.25;
        text-align: left !important;
        white-space: normal !important;
        overflow-wrap: anywhere;
    }

    .cart_product_meta {
        display: block;
        margin: 0 !important;
        color: #6b7280;
        font-size: 14px;
        font-weight: 400;
        line-height: 1.35;
        text-align: left !important;
        white-space: normal !important;
    }

    .cart_price_cell,
    .cart_subtotal_cell,
    .cart_qty_cell,
    .cart_remove_cell {
        white-space: nowrap;
    }

    .cart_quantity_control {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 0 auto !important;
    }

    .cart_remove_cell {
        padding-left: 8px !important;
        padding-right: 12px !important;
    }

    .cart_remove_btn,
    .cart_remove_btn:hover,
    .cart_remove_btn:focus {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        padding: 0 !important;
        margin: 0 !important;
        background: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
        outline: 0 !important;
    }

    @media (max-width: 767px) {
        .cart_package_table_wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .cart_package_table {
            min-width: 700px !important;
        }
    }
</style>
@endpush
