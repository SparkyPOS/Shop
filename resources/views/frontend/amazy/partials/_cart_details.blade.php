@php
    $all_select_count = 0;
    $subtotal = 0;
    $tax = 0;
    $discount = 0;
    $actual_price = 0;
    $shipping_cost = 0;
    $sellect_seller = 0;
    $selected_product_check  = 0;

    foreach ($cartData as $key => $items) {
        $all_select_count += count($items);
        $sellect_seller  = $key;
        $p = 0;
        foreach ($items as $key => $data) {
            $tax_amount = !empty($data->product) && !empty($data->product->product) && !empty($data->product->product->tax) ? $data->product->product->tax :0;
            $tax += ($tax_amount * $data->qty) ;
            if ($data->is_select == 1) {
                $all_select_count = $all_select_count - 1;
                $selected_product_check ++;
                $p = 1;
            }
        }
        if($p == 1){
            $shipping_cost += 20;

        }
    }
@endphp
<div class="checkout_v3_area">
    <form id="cart_form">
        <div class="checkout_v3_left d-flex justify-content-end mb-0">
            @if(count($cartData) > 0)

                <div class="checkout_v3_inner w-100">

                    @if(!isModuleActive('MultiVendor'))
                        @if($free_shipping)
                            <div class="free_shipping_message">
                                <h5>{{__('shipping.shipping_charge_free_from')}} <span>{{single_price($free_shipping->minimum_shopping)}}</span></h5>
                            </div>
                        @endif

                        <div class="amazy_table4">
                            <div class="amazy_table4_head mb_20 d-none d-lg-flex ">
                                <div class="row d-none d-lg-flex flex-fill">
                                    <div class="col-md-5 fw-600"> <h4 class="font_14 f_w_700 m-0 text-nowrap priamry_text text-uppercase">{{__('common.products')}}</h4> </div>
                                    <div class="col fw-600"> <h4 class="font_14 f_w_700 m-0 text-nowrap priamry_text text-uppercase">{{__('common.price')}}</h4> </div>
                                    <div class="col fw-600"> <h4 class="font_14 f_w_700 m-0 text-nowrap priamry_text text-uppercase">{{__('common.quantity')}}</h4> </div>
                                    <div class="col fw-600"> <h4 class="font_14 f_w_700 m-0 text-nowrap priamry_text text-uppercase">{{__('common.subtotal')}}</h4> </div>
                                    <div class="col-auto fw-600"> <h4 class="font_14 f_w_700 m-0 text-nowrap priamry_text text-uppercase">{{__('common.remove')}}</h4> </div>
                                </div>
                            </div>
                            <ul class="amazy_table4_body">
                                @foreach($cartData as $admin_id => $items)
                                    @foreach($items as $key => $cart)
                                        @if($cart->product_type == 'product')
                                            @if($cart->is_select == 1)
                                                @php
                                                    $pro_price = 0;
                                                    if (isModuleActive('WholeSale')){
                                                        $w_main_price = 0;
                                                        $wholeSalePrices = $cart->product->wholeSalePrices;
                                                        if($wholeSalePrices->count()){
                                                            foreach ($wholeSalePrices as $w_p){
                                                                if ( ($w_p->min_qty<=$cart->qty) && ($w_p->max_qty >=$cart->qty) ){
                                                                    $w_main_price = $w_p->sell_price;
                                                                }
                                                                elseif($w_p->max_qty < $cart->qty){
                                                                    $w_main_price = $w_p->sell_price;
                                                                }
                                                            }
                                                        }

                                                        if ($w_main_price!=0){
                                                            $subtotal += $w_main_price * $cart->qty;
                                                            $pro_price = $w_main_price;
                                                        }else{
                                                            $subtotal += $cart->product->sell_price * $cart->qty;
                                                            $pro_price = $cart->product->sell_price;
                                                        }
                                                    }else{
                                                        $subtotal += $cart->product->sell_price * $cart->qty;
                                                        $pro_price = $cart->product->sell_price;
                                                    }

                                                @endphp
                                            @endif
                                            <li class="list-group-item px-0 px-lg-3 mb_10">
                                                <div class="row gutters-5 align-items-center m-0">
                                                    <div class="col-lg-5 d-flex p-0">
                                                        <a href="{{singleProductURL(@$cart->seller->slug, @$cart->product->product->slug)}}" class="d-flex align-items-center gap_20 cart_thumb_div">
                                                            <div class="thumb">
                                                                <img src="
                                                                @if(@$cart->product->product->product->product_type == 1)
                                                                {{showImage(@$cart->product->product->product->thumbnail_image_source)}}
                                                                @else
                                                                {{showImage(@$cart->product->sku->variant_image?@$cart->product->sku->variant_image:@$cart->product->product->product->thumbnail_image_source)}}
                                                                @endif
                                                                " alt="{{ textLimit(@$cart->product->product->product_name, 30) }}" title="{{ textLimit(@$cart->product->product->product_name, 30) }}">
                                                            </div>
                                                            <div class="summery_pro_content">
                                                                <h4 class="font_16 f_w_700 m-0 theme_hover">{{ textLimit(@$cart->product->product->product_name, 30) }}</h4>
                                                                <p class="font_14 f_w_400 m-0 ">
                                                                    @if(@$cart->product->product->product->product_type == 2)
                                                                        @foreach(@$cart->product->product_variations as $key => $combination)
                                                                            @php
                                                                                $attrName = (string) (@$combination->attribute->name ?? '');
                                                                                $attrNameLower = strtolower($attrName);
                                                                                $rawValue = trim((string) (@$combination->attribute_value->value ?? ''));
                                                                                $rawTitle = trim((string) (@$combination->attribute_value->title ?? ''));
                                                                                $colorName = trim((string) (@$combination->attribute_value->color->name ?? ''));
                                                                                $displayValue = $rawValue;
                                                                                if (str_contains($attrNameLower, 'color')) {
                                                                                    $displayValue = $colorName !== '' ? $colorName : ($rawTitle !== '' ? $rawTitle : $rawValue);
                                                                                } else {
                                                                                    $displayValue = (preg_match('/^\d+$/', $rawValue) && $rawTitle !== '' && !preg_match('/^\d+$/', $rawTitle))
                                                                                        ? $rawTitle
                                                                                        : ($rawValue !== '' ? $rawValue : $rawTitle);
                                                                                }
                                                                            @endphp
                                                                            {{ @$combination->attribute->name }}: {{ $displayValue }}
                                                                            @if($key < count(@$cart->product->product_variations)-1),@endif

                                                                        @endforeach
                                                                    @endif
                                                                </p>
                                                            </div>
                                                        </a>
                                                        <div class="mobile_title_full d-lg-none mt-2 w-100">
                                                            <div class="mobile_full_title">{{ @$cart->product->product->product_name }}</div>
                                                        </div>
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-10 mt-2 d-lg-none mobile_qty_price_row">
                                                            <div class="d-flex align-items-center mobile_title_qty_row w-100">
                                                                <div class="product_number_count style_4 ms-auto" data-target="amount-3">
                                                                    <button class="count_single_item inumber_decrement change_qty" data-qty_id="#qty_{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                        data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="{{@$cart->product->product_stock}}" data-stock_manage="{{@$cart->product->product->stock_manage}}" data-wholesale="#getWholesalePrice_{{$cart->id}}" data-cart_id="{{$cart->id}}" type="button" value="-"> <i class="ti-minus"></i></button>
                                                                        <input name="qty[]" id="qty_{{$cart->id}}" maxlength="12" data-value="{{$cart->qty}}" value="{{getNumberTranslate($cart->qty)}}" class="count_single_item input-number qty" type="text" data-qty_id="#qty_{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                        data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="{{@$cart->product->product_stock}}" data-stock_manage="{{@$cart->product->product->stock_manage}}" data-wholesale="#getWholesalePrice_{{$cart->id}}" data-cart_id="{{$cart->id}}">
                                                                        <input type="hidden" value="{{$cart->id}}" name="cart_id[]">
                                                                        <input type="hidden" id="maximum_qty_{{$cart->id}}" value="{{@$cart->product->product->product->max_order_qty}}">
                                                                        <input type="hidden" id="minimum_qty_{{$cart->id}}" value="{{@$cart->product->product->product->minimum_order_qty}}">
                                                                        <button class="count_single_item number_increment change_qty" data-qty_id="#qty_{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                            data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="{{@$cart->product->product_stock}}" data-stock_manage="{{@$cart->product->product->stock_manage}}" data-wholesale="#getWholesalePrice_{{$cart->id}}" data-cart_id="{{$cart->id}}" type="button" value="+"> <i class="ti-plus"></i></button>
                                                                        @if(isModuleActive('WholeSale'))
                                                                            <input type="hidden" id="getWholesalePrice_{{$cart->id}}" value="@if(@$cart->product->wholeSalePrices->count()){{ json_encode(@$cart->product->wholeSalePrices) }} @else 0 @endif">
                                                                        @endif
                                                                </div>
                                                            </div>
                                                            <div class="d-inline mobile_price"><h4 class="font_16 f_w_700 m-0 lh-1 text-nowrap">{{single_price($cart->total_price)}}</h4></div>
                                                            <span class="close_icon style_2 lh-1 cart_item_delete_btn cursor_pointer mobile_delete" data-id="{{$cart->id}}" data-product_id="{{$cart->product_id}}" data-unique_id="#delete_item_{{$cart->id}}">
                                                                <svg  width="12.249" height="15.076" viewBox="0 0 12.249 15.076">
                                                                    <g  transform="translate(-48)">
                                                                        <path  data-name="Path 1449" d="M59.071,1.884H56.48V1.413A1.415,1.415,0,0,0,55.067,0H53.182a1.415,1.415,0,0,0-1.413,1.413v.471H49.178A1.179,1.179,0,0,0,48,3.062V4.711a.471.471,0,0,0,.471.471h.257l.407,8.547a1.412,1.412,0,0,0,1.412,1.346H57.7a1.412,1.412,0,0,0,1.412-1.346l.407-8.547h.257a.471.471,0,0,0,.471-.471V3.062A1.179,1.179,0,0,0,59.071,1.884Zm-6.36-.471a.472.472,0,0,1,.471-.471h1.884a.472.472,0,0,1,.471.471v.471H52.711ZM48.942,3.062a.236.236,0,0,1,.236-.236h9.893a.236.236,0,0,1,.236.236V4.24H48.942Zm9.23,10.623a.471.471,0,0,1-.471.449H50.547a.471.471,0,0,1-.471-.449l-.4-8.5h8.905Z" fill="#00124e"></path>
                                                                        <path  data-name="Path 1450" d="M240.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,1,0-.942,0V214.6A.471.471,0,0,0,240.471,215.067Z" transform="translate(-186.347 -201.875)" fill="#00124e"></path>
                                                                        <path  data-name="Path 1451" d="M320.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,1,0-.942,0V214.6A.471.471,0,0,0,320.471,215.067Z" transform="translate(-263.991 -201.875)" fill="#00124e"></path>
                                                                        <path  data-name="Path 1452" d="M160.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,0,0-.942,0V214.6A.471.471,0,0,0,160.471,215.067Z" transform="translate(-108.702 -201.875)" fill="#00124e"></path>
                                                                    </g>
                                                                </svg>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg col-4 order-1 order-lg-0 my-3 my-lg-0 d-none d-lg-block">
                                                        <span class="opacity-60 font_12 d-block d-lg-none">{{__('common.price')}}</span>
                                                        @if($cart->product->product->hasDeal)
                                                            @if($cart->product->product->hasDeal->discount > 0)
                                                                @if($cart->product->product->hasDeal->discount_type == 0)
                                                                    <span class="green_badge text-nowrap">-{{getNumberTranslate($cart->product->product->hasDeal->discount)}}%</span>
                                                                @else
                                                                    <span class="green_badge text-nowrap">-{{single_price($cart->product->product->hasDeal->discount)}}</span>
                                                                @endif
                                                            @endif
                                                        @else
                                                            @if(@$cart->product->product->hasDiscount == 'yes')
                                                                @if($cart->product->product->discount_type == 0)
                                                                    <span class="green_badge text-nowrap">-{{getNumberTranslate($cart->product->product->discount)}}%</span>
                                                                @else
                                                                    <span class="green_badge text-nowrap">-{{single_price($cart->product->product->discount)}}</span>
                                                                @endif
                                                            @endif
                                                        @endif
                                                        <h4 class="font_16 f_w_700 m-0 text-nowrap set_base_price{{$cart->id}}">{{single_price(isset($pro_price)?$pro_price:@$cart->product->sell_price)}}</h4>
                                                        <input type="hidden" class="get_base_price{{$cart->id}}" value="{{single_price(isset($pro_price)?$pro_price:@$cart->product->sell_price)}}">
                                                    </div>
                                                    <div class="col-lg col-6 order-4 order-lg-0 d-none d-lg-block">
                                                        <div class="product_number_count style_4" data-target="amount-3">
                                                            <button class="count_single_item inumber_decrement change_qty" data-qty_id="#qty_{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="{{@$cart->product->product_stock}}" data-stock_manage="{{@$cart->product->product->stock_manage}}" data-wholesale="#getWholesalePrice_{{$cart->id}}" data-cart_id="{{$cart->id}}" type="button" value="-"> <i class="ti-minus"></i></button>
                                                            <input name="qty[]" id="qty_{{$cart->id}}" maxlength="12" data-value="{{$cart->qty}}" value="{{getNumberTranslate($cart->qty)}}" class="count_single_item input-number qty" type="text" data-qty_id="#qty_{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                            data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="{{@$cart->product->product_stock}}" data-stock_manage="{{@$cart->product->product->stock_manage}}" data-wholesale="#getWholesalePrice_{{$cart->id}}" data-cart_id="{{$cart->id}}">
                                                            <input type="hidden" value="{{$cart->id}}" name="cart_id[]">
                                                            <input type="hidden" id="maximum_qty_{{$cart->id}}" value="{{@$cart->product->product->product->max_order_qty}}">
                                                            <input type="hidden" id="minimum_qty_{{$cart->id}}" value="{{@$cart->product->product->product->minimum_order_qty}}">
                                                            <button class="count_single_item number_increment change_qty" data-qty_id="#qty_{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="{{@$cart->product->product_stock}}" data-stock_manage="{{@$cart->product->product->stock_manage}}" data-wholesale="#getWholesalePrice_{{$cart->id}}" data-cart_id="{{$cart->id}}" type="button" value="+"> <i class="ti-plus"></i></button>

                                                            <!-- for wholesale module -->
                                                            @if(isModuleActive('WholeSale'))
                                                                <input type="hidden" id="getWholesalePrice_{{$cart->id}}" value="@if(@$cart->product->wholeSalePrices->count()){{ json_encode(@$cart->product->wholeSalePrices) }} @else 0 @endif">
                                                            @endif

                                                        </div>
                                                    </div>
                                                    <div class="col-lg col-4 order-3 order-lg-0 my-3 my-lg-0 d-none d-lg-block">
                                                        <span class="opacity-60 font_12 d-none d-sm-block d-lg-none">{{__('common.total')}}</span>
                                                        <h4 class="font_16 f_w_700 m-0 lh-1 d-none d-lg-block">
                                                            {{single_price($cart->total_price)}}
                                                        </h4>
                                                    </div>
                                                    <div class="col-lg-auto order-5 order-lg-0 text-end d-none d-lg-block">

                                                        <span class="close_icon style_2 lh-1 cart_item_delete_btn cursor_pointer" data-id="{{$cart->id}}" data-product_id="{{$cart->product_id}}" data-unique_id="#delete_item_{{$cart->id}}" id="delete_item_{{$cart->id}}">
                                                            <svg  width="12.249" height="15.076" viewBox="0 0 12.249 15.076">
                                                                <g  transform="translate(-48)">
                                                                    <path  data-name="Path 1449" d="M59.071,1.884H56.48V1.413A1.415,1.415,0,0,0,55.067,0H53.182a1.415,1.415,0,0,0-1.413,1.413v.471H49.178A1.179,1.179,0,0,0,48,3.062V4.711a.471.471,0,0,0,.471.471h.257l.407,8.547a1.412,1.412,0,0,0,1.412,1.346H57.7a1.412,1.412,0,0,0,1.412-1.346l.407-8.547h.257a.471.471,0,0,0,.471-.471V3.062A1.179,1.179,0,0,0,59.071,1.884Zm-6.36-.471a.472.472,0,0,1,.471-.471h1.884a.472.472,0,0,1,.471.471v.471H52.711ZM48.942,3.062a.236.236,0,0,1,.236-.236h9.893a.236.236,0,0,1,.236.236V4.24H48.942Zm9.23,10.623a.471.471,0,0,1-.471.449H50.547a.471.471,0,0,1-.471-.449l-.4-8.5h8.905Z" fill="#00124e"></path>
                                                                    <path  data-name="Path 1450" d="M240.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,1,0-.942,0V214.6A.471.471,0,0,0,240.471,215.067Z" transform="translate(-186.347 -201.875)" fill="#00124e"></path>
                                                                    <path  data-name="Path 1451" d="M320.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,1,0-.942,0V214.6A.471.471,0,0,0,320.471,215.067Z" transform="translate(-263.991 -201.875)" fill="#00124e"></path>
                                                                    <path  data-name="Path 1452" d="M160.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,0,0-.942,0V214.6A.471.471,0,0,0,160.471,215.067Z" transform="translate(-108.702 -201.875)" fill="#00124e"></path>
                                                                </g>
                                                            </svg>
                                                        </span>
                                                    </div>
                                                </div>
                                            </li>
                                        @else
                                            @if($cart->is_select == 1)
                                                @php
                                                    $subtotal += $cart->giftCard->sell_price * $cart->qty;
                                                @endphp
                                            @endif

                                            <li class="list-group-item px-0 px-lg-3 mb_10">
                                                <div class="row gutters-5 align-items-center">
                                                    <div class="col-lg-5 d-flex">
                                                        <a href="{{route('frontend.gift-card.show',$cart->giftCard->sku)}}" class="d-flex align-items-center gap_20 cart_thumb_div">
                                                            <div class="thumb">
                                                                <img src="{{showImage(@$cart->giftCard->thumbnail_image)}}" alt="{{ textLimit(@$cart->giftCard->name, 30) }}" title="{{ textLimit(@$cart->giftCard->name, 30) }}">
                                                            </div>
                                                            <div class="summery_pro_content">
                                                                <h4 class="font_16 f_w_700 m-0 theme_hover">{{ textLimit(@$cart->giftCard->name, 30) }}</h4>
                                                            </div>
                                                        </a>
                                                    </div>

                                                    <div class="col-lg col-4 order-1 order-lg-0 my-3 my-lg-0">
                                                        <span class="opacity-60 font_12 d-block d-lg-none">{{__('common.price')}}</span>

                                                        <h4 class="font_16 f_w_700 m-0 text-nowrap">{{single_price($cart->price)}}</h4>
                                                    </div>
                                                    <div class="col-lg col-6 order-4 order-lg-0">
                                                        <div class="product_number_count style_4" data-target="amount-3">
                                                            <input type="hidden" value="{{$cart->id}}" name="cart_id[]">
                                                            <input type="hidden" id="maximum_qty_{{$cart->id}}" value="">
                                                            <input type="hidden" id="minimum_qty_{{$cart->id}}" value="1">
                                                            <button class="count_single_item inumber_decrement change_qty" data-qty_id="#qty_{{$cart->id}}" data-cart_id="{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="0" data-stock_manage="0" data-wholesale="#getWholesalePrice_{{$cart->id}}" type="button" value="-"> <i class="ti-minus"></i></button>
                                                            <input name="qty[]" id="qty_{{$cart->id}}" data-value="{{$cart->qty}}" value="{{getNumberTranslate($cart->qty)}}" class="count_single_item input-number qty" type="text" data-qty_id="#qty_{{$cart->id}}" data-cart_id="{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                            data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="0" data-stock_manage="0" data-wholesale="#getWholesalePrice_{{$cart->id}}">
                                                            <button class="count_single_item number_increment change_qty" data-qty_id="#qty_{{$cart->id}}" data-cart_id="{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="0" data-stock_manage="0" data-wholesale="#getWholesalePrice_{{$cart->id}}" type="button" value="+"> <i class="ti-plus"></i></button>
                                                            <!-- for wholesale module -->
                                                            @if(isModuleActive('WholeSale'))
                                                                <input type="hidden" id="getWholesalePrice_{{$cart->id}}" value="0">
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-lg col-4 order-3 order-lg-0 my-3 my-lg-0">
                                                        <span class="opacity-60 font_12 d-block d-lg-none">{{__('common.total')}}</span>
                                                        <h4 class="font_16 f_w_700 m-0 lh-1">
                                                            {{single_price($cart->total_price)}}
                                                        </h4>
                                                    </div>
                                                    <div class="col-lg-auto col-6 order-5 order-lg-0 text-end d-none d-lg-block">
                                                        <span class="close_icon style_2 lh-1 cart_item_delete_btn cursor_pointer" data-id="{{$cart->id}}" data-product_id="{{$cart->product_id}}" data-unique_id="#delete_item_{{$cart->id}}" id="delete_item_{{$cart->id}}">
                                                            <svg  width="12.249" height="15.076" viewBox="0 0 12.249 15.076">
                                                                <g  transform="translate(-48)">
                                                                    <path  data-name="Path 1449" d="M59.071,1.884H56.48V1.413A1.415,1.415,0,0,0,55.067,0H53.182a1.415,1.415,0,0,0-1.413,1.413v.471H49.178A1.179,1.179,0,0,0,48,3.062V4.711a.471.471,0,0,0,.471.471h.257l.407,8.547a1.412,1.412,0,0,0,1.412,1.346H57.7a1.412,1.412,0,0,0,1.412-1.346l.407-8.547h.257a.471.471,0,0,0,.471-.471V3.062A1.179,1.179,0,0,0,59.071,1.884Zm-6.36-.471a.472.472,0,0,1,.471-.471h1.884a.472.472,0,0,1,.471.471v.471H52.711ZM48.942,3.062a.236.236,0,0,1,.236-.236h9.893a.236.236,0,0,1,.236.236V4.24H48.942Zm9.23,10.623a.471.471,0,0,1-.471.449H50.547a.471.471,0,0,1-.471-.449l-.4-8.5h8.905Z" fill="#00124e"></path>
                                                                    <path  data-name="Path 1450" d="M240.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,1,0-.942,0V214.6A.471.471,0,0,0,240.471,215.067Z" transform="translate(-186.347 -201.875)" fill="#00124e"></path>
                                                                    <path  data-name="Path 1451" d="M320.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,1,0-.942,0V214.6A.471.471,0,0,0,320.471,215.067Z" transform="translate(-263.991 -201.875)" fill="#00124e"></path>
                                                                    <path  data-name="Path 1452" d="M160.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,0,0-.942,0V214.6A.471.471,0,0,0,160.471,215.067Z" transform="translate(-108.702 -201.875)" fill="#00124e"></path>
                                                                </g>
                                                            </svg>
                                                        </span>
                                                    </div>
                                                    <div class="col-12 d-lg-none mt-2 text-end">

                                                        <span class="close_icon style_2 lh-1 cart_item_delete_btn cursor_pointer" data-id="{{$cart->id}}" data-product_id="{{$cart->product_id}}" data-unique_id="#delete_item_{{$cart->id}}">
                                                            <svg  width="12.249" height="15.076" viewBox="0 0 12.249 15.076">
                                                                <g  transform="translate(-48)">
                                                                    <path  data-name="Path 1449" d="M59.071,1.884H56.48V1.413A1.415,1.415,0,0,0,55.067,0H53.182a1.415,1.415,0,0,0-1.413,1.413v.471H49.178A1.179,1.179,0,0,0,48,3.062V4.711a.471.471,0,0,0,.471.471h.257l.407,8.547a1.412,1.412,0,0,0,1.412,1.346H57.7a.412.412,0,0,0,1.412-1.346l.407-8.547h.257a.471.471,0,0,0,.471-.471V3.062A1.179,1.179,0,0,0,59.071,1.884Zm-6.36-.471a.472.472,0,0,1,.471-.471h1.884a.472.472,0,0,1,.471.471v.471H52.711ZM48.942,3.062a.236.236,0,0,1,.236-.236h9.893a.236.236,0,0,1,.236.236V4.24H48.942Zm9.23,10.623a.471.471,0,0,1-.471.449H50.547a.471.471,0,0,1-.471-.449l-.4-8.5h8.905Z" fill="#00124e"></path>
                                                                    <path  data-name="Path 1450" d="M240.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,1,0-.942,0V214.6A.471.471,0,0,0,240.471,215.067Z" transform="translate(-186.347 -201.875)" fill="#00124e"></path>
                                                                    <path  data-name="Path 1451" d="M320.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,1,0-.942,0V214.6A.471.471,0,0,0,320.471,215.067Z" transform="translate(-263.991 -201.875)" fill="#00124e"></path>
                                                                    <path  data-name="Path 1452" d="M160.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,0,0-.942,0V214.6A.471.471,0,0,0,160.471,215.067Z" transform="translate(-108.702 -201.875)" fill="#00124e"></path>
                                                                </g>
                                                            </svg>
                                                        </span>
                                                    </div>
                                                </div>
                                            </li>
                                        @endif
                                        @if($cart->is_select == 1)
                                            @php
                                                $actual_price += $cart->total_price;
                                            @endphp
                                        @endif
                                    @endforeach
                                @endforeach

                            </ul>
                        </div>
                    @else
                        <div class="amazy_table4">
                            <div class="amazy_table4_head cart-table-head mb_20 d-none d-lg-grid px-0">
                            <div class="cart-table-head-cell cart-table-product-head">
                                <h4 class="font_14 f_w_700 m-0 text-nowrap priamry_text text-uppercase">
                                    {{__('common.products')}}
                                </h4>
                            </div>

                            <div class="cart-table-head-cell">
                                <h4 class="font_14 f_w_700 m-0 text-nowrap priamry_text text-uppercase">
                                    {{__('common.price')}}
                                </h4>
                            </div>

                            <div class="cart-table-head-cell">
                                <h4 class="font_14 f_w_700 m-0 text-nowrap priamry_text text-uppercase">
                                    {{__('common.quantity')}}
                                </h4>
                            </div>

                            <div class="cart-table-head-cell">
                                <h4 class="font_14 f_w_700 m-0 text-nowrap priamry_text text-uppercase">
                                    {{__('common.subtotal')}}
                                </h4>
                            </div>

                            <div class="cart-table-head-cell"></div>
                        </div>
                            @foreach($cartData as $seller_id => $cartItems)
                                @php
                                    $seller = App\Models\User::where('id',$seller_id)->first();
                                    $select_count = count($cartItems);
                                @endphp
                                @foreach($cartItems as $m => $data)
                                    @php
                                        if($data->is_select == 1){
                                                $select_count = $select_count - 1;
                                        }else{
                                            $select_count = $select_count;
                                        }
                                    @endphp
                                @endforeach
                                <div class="checkout_shiped_box mb_20">
                                    <ul class="amazy_table4_body cart-items-list">
                                        @foreach($cartItems as $key => $cart)
                                            @if($cart->product_type == 'product')
                                                @if($cart->is_select == 1)
                                                    @php
                                                        $pro_price = 0;
                                                        if (isModuleActive('WholeSale')){
                                                            $w_main_price = 0;
                                                            $wholeSalePrices = @$cart->product->wholeSalePrices;
                                                            if($wholeSalePrices->count()){
                                                                foreach ($wholeSalePrices as $w_p){
                                                                    if ( ($w_p->min_qty<=$cart->qty) && ($w_p->max_qty >=$cart->qty) ){
                                                                        $w_main_price = $w_p->sell_price;
                                                                    }
                                                                    elseif($w_p->max_qty < $cart->qty){
                                                                        $w_main_price = $w_p->sell_price;
                                                                    }
                                                                }
                                                            }
                                                            if ($w_main_price!=0){
                                                                $subtotal += $w_main_price * $cart->qty;
                                                                $pro_price = $w_main_price;
                                                            }else{
                                                                $subtotal += $cart->product->sell_price * $cart->qty;
                                                                $pro_price = $cart->product->sell_price;
                                                            }
                                                        }else{
                                                            $subtotal += $cart->product->sell_price * $cart->qty;
                                                            $pro_price = $cart->product->sell_price;
                                                        }
                                                        $productName = @$cart->product->product->product_name;
                                                        $productUrl = singleProductURL(@$cart->seller->slug, @$cart->product->product->slug);

                                                        if (@$cart->product->product->product->product_type == 1) {
                                                            $productImage = showImage(@$cart->product->product->product->thumbnail_image_source);
                                                        } else {
                                                            $productImage = showImage(
                                                                @$cart->product->sku->variant_image
                                                                    ? @$cart->product->sku->variant_image
                                                                    : @$cart->product->product->product->thumbnail_image_source
                                                            );
                                                        }

                                                        $vendorId = @$cart->seller->sellerAccount->vendor_id;
                                                        $storeName = parentStoreName($cart->seller ?? null);
                                                    @endphp
                                                @endif

                                                <li class="list-group-item cart-item-card mb_10">
                                                    <div class="cart-item-inner">

                                                        {{-- Main product area --}}
                                                        <div class="cart-item-main">
                                                            <a href="{{ $productUrl }}" class="cart-item-thumb-link">
                                                                <div class="cart-item-thumb">
                                                                    <img
                                                                        src="{{ $productImage }}"
                                                                        alt="{{ textLimit($productName, 35) }}"
                                                                        title="{{ textLimit($productName, 35) }}"
                                                                        loading="lazy"
                                                                    >
                                                                </div>
                                                            </a>

                                                            <div class="cart-item-content">
                                                                <a href="{{ $productUrl }}" class="cart-item-title-link">
                                                                    <h4 class="cart-item-title theme_hover">
                                                                        {{ $productName }}
                                                                    </h4>
                                                                </a>

                                                                @if(@$cart->product->product->product->product_type == 2)
                                                                    <div class="cart-item-attributes">
                                                                        @foreach(@$cart->product->product_variations as $variationKey => $combination)
                                                                            @php
                                                                                $attrName = (string) (@$combination->attribute->name ?? '');
                                                                                $attrNameLower = strtolower($attrName);

                                                                                $rawValue = trim((string) (@$combination->attribute_value->value ?? ''));
                                                                                $rawTitle = trim((string) (@$combination->attribute_value->title ?? ''));
                                                                                $colorName = trim((string) (@$combination->attribute_value->color->name ?? ''));

                                                                                if (str_contains($attrNameLower, 'color')) {
                                                                                    $displayValue = $colorName !== ''
                                                                                        ? $colorName
                                                                                        : ($rawTitle !== '' ? $rawTitle : $rawValue);
                                                                                } else {
                                                                                    $displayValue = (preg_match('/^\d+$/', $rawValue) && $rawTitle !== '' && !preg_match('/^\d+$/', $rawTitle))
                                                                                        ? $rawTitle
                                                                                        : ($rawValue !== '' ? $rawValue : $rawTitle);
                                                                                }
                                                                            @endphp

                                                                            @if($attrName !== '' || $displayValue !== '')
                                                                                <span class="cart-item-chip">
                                                                                    <span class="cart-item-chip-label">{{ $attrName }}:</span>
                                                                                    <span class="cart-item-chip-value">{{ $displayValue }}</span>
                                                                                </span>
                                                                            @endif
                                                                        @endforeach
                                                                    </div>
                                                                @endif

                                                                <div class="cart-item-meta">
                                                                    <div class="cart-item-meta-row">
                                                                        <span class="cart-item-meta-icon" aria-hidden="true">
                                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                                                                <path d="M20 7L12 3L4 7V17L12 21L20 17V7Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                                                                <path d="M12 12L20 7" stroke="currentColor" stroke-width="1.8"/>
                                                                                <path d="M12 12V21" stroke="currentColor" stroke-width="1.8"/>
                                                                                <path d="M12 12L4 7" stroke="currentColor" stroke-width="1.8"/>
                                                                            </svg>
                                                                        </span>
                                                                        <span class="cart-item-meta-label">{{ __('Vendor') }}:</span>
                                                                        <strong class="cart-item-meta-value">{{ $vendorId }}</strong>
                                                                    </div>

                                                                    <div class="cart-item-meta-row">
                                                                        <span class="cart-item-meta-icon" aria-hidden="true">
                                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                                                                <path d="M4 10H20L19 21H5L4 10Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                                                                <path d="M7 10V7C7 4.8 8.8 3 11 3H13C15.2 3 17 4.8 17 7V10" stroke="currentColor" stroke-width="1.8"/>
                                                                            </svg>
                                                                        </span>
                                                                        <span class="cart-item-meta-label">{{ __('common.store') }}:</span>
                                                                        <strong class="cart-item-meta-value">{{ $storeName }}</strong>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- Action row --}}
                                                        <div class="cart-item-action-row">

                                                            <div class="cart-item-qty-wrap">
                                                                <div class="product_number_count style_4 cart-item-qty" data-target="amount-3">
                                                                    <button
                                                                        class="count_single_item inumber_decrement change_qty"
                                                                        data-qty_id="#qty_{{$cart->id}}"
                                                                        data-change_amount="1"
                                                                        data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                        data-minimum_qty="#minimum_qty_{{$cart->id}}"
                                                                        data-product_stock="{{$cart->product->product_stock}}"
                                                                        data-stock_manage="{{$cart->product->product->stock_manage}}"
                                                                        data-wholesale="#getWholesalePrice_{{$cart->id}}"
                                                                        data-cart_id="{{$cart->id}}"
                                                                        type="button"
                                                                        value="-"
                                                                        aria-label="{{ __('Decrease quantity') }}"
                                                                    >
                                                                        <i class="ti-minus"></i>
                                                                    </button>

                                                                    <input
                                                                        name="qty[]"
                                                                        id="qty_{{$cart->id}}"
                                                                        maxlength="12"
                                                                        data-value="{{$cart->qty}}"
                                                                        value="{{getNumberTranslate($cart->qty)}}"
                                                                        class="count_single_item input-number qty"
                                                                        type="text"
                                                                        data-qty_id="#qty_{{$cart->id}}"
                                                                        data-change_amount="1"
                                                                        data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                        data-minimum_qty="#minimum_qty_{{$cart->id}}"
                                                                        data-product_stock="{{$cart->product->product_stock}}"
                                                                        data-stock_manage="{{$cart->product->product->stock_manage}}"
                                                                        data-wholesale="#getWholesalePrice_{{$cart->id}}"
                                                                        data-cart_id="{{$cart->id}}"
                                                                        aria-label="{{ __('Quantity') }}"
                                                                    >

                                                                    <input type="hidden" value="{{$cart->id}}" name="cart_id[]">
                                                                    <input type="hidden" id="maximum_qty_{{$cart->id}}" value="{{$cart->product->product->product->max_order_qty}}">
                                                                    <input type="hidden" id="minimum_qty_{{$cart->id}}" value="{{$cart->product->product->product->minimum_order_qty}}">

                                                                    <button
                                                                        class="count_single_item number_increment change_qty"
                                                                        data-qty_id="#qty_{{$cart->id}}"
                                                                        data-change_amount="1"
                                                                        data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                        data-minimum_qty="#minimum_qty_{{$cart->id}}"
                                                                        data-product_stock="{{$cart->product->product_stock}}"
                                                                        data-stock_manage="{{$cart->product->product->stock_manage}}"
                                                                        data-wholesale="#getWholesalePrice_{{$cart->id}}"
                                                                        data-cart_id="{{$cart->id}}"
                                                                        type="button"
                                                                        value="+"
                                                                        aria-label="{{ __('Increase quantity') }}"
                                                                    >
                                                                        <i class="ti-plus"></i>
                                                                    </button>

                                                                    @if(isModuleActive('WholeSale'))
                                                                        <input
                                                                            type="hidden"
                                                                            id="getWholesalePrice_{{$cart->id}}"
                                                                            value="@if(@$cart->product->wholeSalePrices->count()){{ json_encode(@$cart->product->wholeSalePrices) }} @else 0 @endif"
                                                                        >
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <div class="cart-item-price-wrap">
                                                                <div class="cart-item-unit-price d-none d-lg-block">
                                                                    @if($cart->product->product->hasDeal)
                                                                        @if($cart->product->product->hasDeal->discount > 0)
                                                                            @if($cart->product->product->hasDeal->discount_type == 0)
                                                                                <span class="green_badge text-nowrap">
                                                                                    -{{getNumberTranslate($cart->product->product->hasDeal->discount)}}%
                                                                                </span>
                                                                            @else
                                                                                <span class="green_badge text-nowrap">
                                                                                    -{{single_price($cart->product->product->hasDeal->discount)}}
                                                                                </span>
                                                                            @endif
                                                                        @endif
                                                                    @else
                                                                        @if(@$cart->product->product->hasDiscount == 'yes')
                                                                            @if($cart->product->product->discount_type == 0)
                                                                                <span class="green_badge text-nowrap">
                                                                                    -{{getNumberTranslate($cart->product->product->discount)}}%
                                                                                </span>
                                                                            @else
                                                                                <span class="green_badge text-nowrap">
                                                                                    -{{single_price($cart->product->product->discount)}}
                                                                                </span>
                                                                            @endif
                                                                        @endif
                                                                    @endif

                                                                    <span class="cart-item-price-label">{{ __('common.price') }}</span>
                                                                    <strong class="set_base_price{{$cart->id}}">
                                                                        {{single_price(isset($pro_price) ? $pro_price : @$cart->product->sell_price)}}
                                                                    </strong>

                                                                    <input
                                                                        type="hidden"
                                                                        class="get_base_price{{$cart->id}}"
                                                                        value="{{single_price(isset($pro_price) ? $pro_price : @$cart->product->sell_price)}}"
                                                                    >
                                                                </div>

                                                                <div class="cart-item-total-price">
                                                                    <span class="cart-item-price-label d-none d-lg-block">
                                                                        {{ __('common.total') }}
                                                                    </span>
                                                                    <strong>
                                                                        {{single_price($cart->total_price)}}
                                                                    </strong>
                                                                </div>
                                                            </div>

                                                            <button
                                                                type="button"
                                                                class="cart-item-delete close_icon style_2 cart_item_delete_btn cursor_pointer"
                                                                data-id="{{$cart->id}}"
                                                                data-product_id="{{$cart->product_id}}"
                                                                data-unique_id="#delete_item_{{$cart->id}}"
                                                                id="delete_item_{{$cart->id}}"
                                                                aria-label="{{ __('Remove item') }}"
                                                            >
                                                                <svg width="16" height="18" viewBox="0 0 12.249 15.076" aria-hidden="true">
                                                                    <g transform="translate(-48)">
                                                                        <path data-name="Path 1449" d="M59.071,1.884H56.48V1.413A1.415,1.415,0,0,0,55.067,0H53.182a1.415,1.415,0,0,0-1.413,1.413v.471H49.178A1.179,1.179,0,0,0,48,3.062V4.711a.471.471,0,0,0,.471.471h.257l.407,8.547a1.412,1.412,0,0,0,1.412,1.346H57.7a1.412,1.412,0,0,0,1.412-1.346l.407-8.547h.257a.471.471,0,0,0,.471-.471V3.062A1.179,1.179,0,0,0,59.071,1.884Zm-6.36-.471a.472.472,0,0,1,.471-.471h1.884a.472.472,0,0,1,.471.471v.471H52.711ZM48.942,3.062a.236.236,0,0,1,.236-.236h9.893a.236.236,0,0,1,.236.236V4.24H48.942Zm9.23,10.623a.471.471,0,0,1-.471.449H50.547a.471.471,0,0,1-.471-.449l-.4-8.5h8.905Z" fill="currentColor"></path>
                                                                        <path data-name="Path 1450" d="M240.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,1,0-.942,0V214.6A.471.471,0,0,0,240.471,215.067Z" transform="translate(-186.347 -201.875)" fill="currentColor"></path>
                                                                        <path data-name="Path 1451" d="M320.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,1,0-.942,0V214.6A.471.471,0,0,0,320.471,215.067Z" transform="translate(-263.991 -201.875)" fill="currentColor"></path>
                                                                        <path data-name="Path 1452" d="M160.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,0,0-.942,0V214.6A.471.471,0,0,0,160.471,215.067Z" transform="translate(-108.702 -201.875)" fill="currentColor"></path>
                                                                    </g>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </li>
                                            @else
                                                @if($cart->is_select == 1)
                                                    @if(is_null($cart->giftCard->type))
                                                        @php
                                                            $subtotal += $cart->giftCard->sell_price * $cart->qty;
                                                        @endphp
                                                    @else
                                                        @php
                                                            $subtotal += $cart->giftCard->addGiftCardInfo->gift_selling_price * $cart->qty;
                                                        @endphp
                                                    @endif
                                                @endif

                                                <li class="list-group-item cart-item-card mb_10">
                                                    <div class="cart-item-inner">

                                                        <div class="cart-item-main">
                                                            <a href="{{ $giftCardUrl }}" class="cart-item-thumb-link">
                                                                <div class="cart-item-thumb">
                                                                    <img
                                                                        src="{{ $giftCardImage }}"
                                                                        alt="{{ textLimit($giftCardName, 35) }}"
                                                                        title="{{ textLimit($giftCardName, 35) }}"
                                                                        loading="lazy"
                                                                    >
                                                                </div>
                                                            </a>

                                                            <div class="cart-item-content">
                                                                <a href="{{ $giftCardUrl }}" class="cart-item-title-link">
                                                                    <h4 class="cart-item-title theme_hover">
                                                                        {{ $giftCardName }}
                                                                    </h4>
                                                                </a>

                                                                <div class="cart-item-attributes">
                                                                    <span class="cart-item-chip">
                                                                        <span class="cart-item-chip-value">{{ __('Gift Card') }}</span>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="cart-item-action-row">
                                                            <div class="cart-item-qty-wrap">
                                                                <div class="product_number_count style_4 cart-item-qty" data-target="amount-1">
                                                                    <input type="hidden" value="{{$cart->id}}" name="cart_id[]">
                                                                    <input type="hidden" id="maximum_qty_{{$cart->id}}" value="">
                                                                    <input type="hidden" id="minimum_qty_{{$cart->id}}" value="1">

                                                                    <button
                                                                        class="count_single_item inumber_decrement change_qty"
                                                                        data-qty_id="#qty_{{$cart->id}}"
                                                                        data-cart_id="{{$cart->id}}"
                                                                        data-change_amount="1"
                                                                        data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                        data-minimum_qty="#minimum_qty_{{$cart->id}}"
                                                                        data-product_stock="0"
                                                                        data-stock_manage="0"
                                                                        data-wholesale="#getWholesalePrice_{{$cart->id}}"
                                                                        type="button"
                                                                        value="-"
                                                                        aria-label="{{ __('Decrease quantity') }}"
                                                                    >
                                                                        <i class="ti-minus"></i>
                                                                    </button>

                                                                    <input
                                                                        name="qty[]"
                                                                        id="qty_{{$cart->id}}"
                                                                        data-value="{{$cart->qty}}"
                                                                        value="{{getNumberTranslate($cart->qty)}}"
                                                                        class="count_single_item input-number qty"
                                                                        type="text"
                                                                        data-qty_id="#qty_{{$cart->id}}"
                                                                        data-cart_id="{{$cart->id}}"
                                                                        data-change_amount="1"
                                                                        data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                        data-minimum_qty="#minimum_qty_{{$cart->id}}"
                                                                        data-product_stock="0"
                                                                        data-stock_manage="0"
                                                                        data-wholesale="#getWholesalePrice_{{$cart->id}}"
                                                                        aria-label="{{ __('Quantity') }}"
                                                                    >

                                                                    <button
                                                                        class="count_single_item number_increment change_qty"
                                                                        data-qty_id="#qty_{{$cart->id}}"
                                                                        data-cart_id="{{$cart->id}}"
                                                                        data-change_amount="1"
                                                                        data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                        data-minimum_qty="#minimum_qty_{{$cart->id}}"
                                                                        data-product_stock="0"
                                                                        data-stock_manage="0"
                                                                        data-wholesale="#getWholesalePrice_{{$cart->id}}"
                                                                        type="button"
                                                                        value="+"
                                                                        aria-label="{{ __('Increase quantity') }}"
                                                                    >
                                                                        <i class="ti-plus"></i>
                                                                    </button>

                                                                    @if(isModuleActive('WholeSale'))
                                                                        <input type="hidden" id="getWholesalePrice_{{$cart->id}}" value="0">
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <div class="cart-item-price-wrap">
                                                                <div class="cart-item-unit-price d-none d-lg-block">
                                                                    <span class="cart-item-price-label">{{ __('common.price') }}</span>
                                                                    <strong>{{single_price($cart->price)}}</strong>
                                                                </div>

                                                                <div class="cart-item-total-price">
                                                                    <span class="cart-item-price-label d-none d-lg-block">
                                                                        {{ __('common.total') }}
                                                                    </span>
                                                                    <strong>{{single_price($cart->total_price)}}</strong>
                                                                </div>
                                                            </div>

                                                            <button
                                                                type="button"
                                                                class="cart-item-delete close_icon style_2 cart_item_delete_btn cursor_pointer"
                                                                data-id="{{$cart->id}}"
                                                                data-product_id="{{$cart->product_id}}"
                                                                data-unique_id="#delete_item_{{$cart->id}}"
                                                                id="delete_item_{{$cart->id}}"
                                                                aria-label="{{ __('Remove item') }}"
                                                            >
                                                                <svg width="16" height="18" viewBox="0 0 12.249 15.076" aria-hidden="true">
                                                                    <g transform="translate(-48)">
                                                                        <path data-name="Path 1449" d="M59.071,1.884H56.48V1.413A1.415,1.415,0,0,0,55.067,0H53.182a1.415,1.415,0,0,0-1.413,1.413v.471H49.178A1.179,1.179,0,0,0,48,3.062V4.711a.471.471,0,0,0,.471.471h.257l.407,8.547a1.412,1.412,0,0,0,1.412,1.346H57.7a1.412,1.412,0,0,0,1.412-1.346l.407-8.547h.257a.471.471,0,0,0,.471-.471V3.062A1.179,1.179,0,0,0,59.071,1.884Zm-6.36-.471a.472.472,0,0,1,.471-.471h1.884a.472.472,0,0,1,.471.471v.471H52.711ZM48.942,3.062a.236.236,0,0,1,.236-.236h9.893a.236.236,0,0,1,.236.236V4.24H48.942Zm9.23,10.623a.471.471,0,0,1-.471.449H50.547a.471.471,0,0,1-.471-.449l-.4-8.5h8.905Z" fill="currentColor"></path>
                                                                        <path data-name="Path 1450" d="M240.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,1,0-.942,0V214.6A.471.471,0,0,0,240.471,215.067Z" transform="translate(-186.347 -201.875)" fill="currentColor"></path>
                                                                        <path data-name="Path 1451" d="M320.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,1,0-.942,0V214.6A.471.471,0,0,0,320.471,215.067Z" transform="translate(-263.991 -201.875)" fill="currentColor"></path>
                                                                        <path data-name="Path 1452" d="M160.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,0,0-.942,0V214.6A.471.471,0,0,0,160.471,215.067Z" transform="translate(-108.702 -201.875)" fill="currentColor"></path>
                                                                    </g>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </li>
                                            @endif
                                            @if($cart->is_select == 1)
                                                @php
                                                    $actual_price += $cart->total_price;
                                                @endphp
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <div class="d-flex gap_10 align-items-center flex-wrap mt_20">
                        <div class="d-none d-lg-flex align-items-center gap_10 flex-fill flex-wrap">
                            <a href="{{url('/')}}" class="amaz_primary_btn2 style3">{{__('defaultTheme.continue_shopping')}}</a>
                        </div>
                        <a class="amaz_primary_btn min_200 style2 cursor_pointer @if (count($cartData) > 0) process_to_checkout_check @endif" data-value="{{$selected_product_check}}">{{__('defaultTheme.proceed_to_checkout')}}</a>
                    </div>
                </div>
            <!-- for wholesale module -->
            <input type="hidden" id="isWholeSaleActive" value="{{isModuleActive('WholeSale')}}">
            <!-- for wholesale module -->
            @endif
        </div>
        @if(count($cartData) < 1)
            <div class="col-lg-12 text-center mb_50">
                <span class="product_not_found">{{ __('defaultTheme.no_product_found') }}</span>
            </div>
        @endif
    </form>
    <div class="checkout_v3_right d-flex justify-content-start checkout_summery_div">
        @php
            $grand_total = $actual_price;
            $discount = $subtotal -$actual_price;
        @endphp
        <div class="order_sumery_box flex-fill">
            <h3 class="check_v3_title mb_25">{{__('common.order_summary')}}</h3>
            <div class="subtotal_lists">
                <div class="single_total_list d-flex align-items-center">
                    <div class="single_total_left flex-fill">
                        <h4>{{ __('common.subtotal') }}</h4>
                    </div>
                    <div class="single_total_right">
                        @if (app('general_setting')->price_with_vat)
                        <span>+ {{single_price($subtotal - $tax)}}</span>
                        @else
                            <span>+ {{single_price($subtotal)}}</span>
                        @endif
                    </div>
                </div>
                <div class="single_total_list d-flex align-items-center flex-wrap">
                    <div class="single_total_left flex-fill">
                        <h4>{{__('common.shipping_charge')}}</h4>
                    </div>
                    <div class="single_total_right">
                        <span>{{__('defaultTheme.calculated_at_next_step')}}</span>
                    </div>
                </div>
                <div class="single_total_list d-flex align-items-center flex-wrap">
                    <div class="single_total_left flex-fill">
                        <h4>{{__('common.discount')}}</h4>
                    </div>
                    <div class="single_total_right">
                        <span>- {{single_price($discount)}}</span>
                    </div>
                </div>
                <div class="single_total_list d-flex align-items-center flex-wrap">
                    <div class="single_total_left flex-fill">
                        <h4>{{__('common.vat/tax/gst')}}</h4>
                    </div>
                    <div class="single_total_right">
                        @if (app('general_setting')->price_with_vat)
                            <span>+ {{single_price($tax)}}</span>
                        @else
                            <span>{{__('defaultTheme.calculated_at_next_step')}}</span>
                        @endif
                    </div>
                </div>
                <div class="total_amount d-flex align-items-center flex-wrap">
                    <div class="single_total_left flex-fill">
                        <span class="total_text">{{__('common.total')}}</span>
                    </div>
                    <div class="single_total_right">
                        <span class="total_text" id="grand_total" data-amount='{{ str_replace(',','',number_format($grand_total,2)) }}'><span>{{single_price($grand_total)}}</span></span>
                    </div>
                </div>

                <div class="total_amount d-flex align-items-center flex-wrap mt-2 mb-2">
                   <div id="TabbyPromo"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('styles')
<style>
    .cart-table-head {
        display: none;
    }

    @media (min-width: 992px) {
        .cart-table-head {
            display: grid !important;
            grid-template-columns: minmax(0, 5fr) minmax(120px, 2fr) minmax(150px, 2fr) minmax(130px, 2fr) 64px;
            align-items: center;
            min-height: 58px;
            padding: 0 18px !important;
            border: 1px solid #edf1f5;
            background: #ffffff;
        }

        .cart-table-head-cell {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 0;
        }

        .cart-table-product-head {
            justify-content: center;
        }
    }
    .cart-items-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        padding: 0;
        margin: 0;
        list-style: none;
    }

    .cart-item-card {
        padding: 0 !important;
        border: 0 !important;
        background: transparent !important;
    }

    .cart-item-inner {
        background: #ffffff;
        border: 1px solid #e8edf3;
        border-radius: 14px;
        padding: 16px;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
    }

    .cart-item-main {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        min-width: 0;
    }

    .cart-item-thumb-link {
        display: block;
        flex: 0 0 auto;
        text-decoration: none;
    }

    .cart-item-thumb {
        width: 86px;
        height: 110px;
        border-radius: 12px;
        overflow: hidden;
        background: #f7f9fc;
        border: 1px solid #edf1f5;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cart-item-thumb img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
    }

    .cart-item-content {
        flex: 1 1 auto;
        min-width: 0;
        padding-top: 1px;
    }

    .cart-item-title-link {
        display: block;
        color: inherit;
        text-decoration: none;
    }

    .cart-item-title {
        margin: 0;
        color: #101828;
        font-size: 15px;
        font-weight: 700;
        line-height: 1.28;
        letter-spacing: -0.01em;

        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .cart-item-attributes {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 9px;
    }

    .cart-item-chip {
        display: inline-flex;
        align-items: center;
        max-width: 100%;
        gap: 4px;
        min-height: 28px;
        padding: 5px 9px;
        border-radius: 999px;
        background: #f3f6fa;
        border: 1px solid #e3e9f1;
        color: #344054;
        font-size: 12px;
        line-height: 1.2;
        white-space: nowrap;
    }

    .cart-item-chip-label {
        color: #667085;
        font-weight: 500;
    }

    .cart-item-chip-value {
        color: #1d2939;
        font-weight: 600;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .cart-item-meta {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-top: 10px;
    }

    .cart-item-meta-row {
        display: flex;
        align-items: center;
        gap: 6px;
        min-width: 0;
        color: #475467;
        font-size: 12.5px;
        line-height: 1.25;
    }

    .cart-item-meta-icon {
        flex: 0 0 auto;
        color: #667085;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .cart-item-meta-label {
        flex: 0 0 auto;
        color: #667085;
        font-weight: 500;
    }

    .cart-item-meta-value {
        min-width: 0;
        color: #1d2939;
        font-weight: 700;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .cart-item-action-row {
        display: grid;
        grid-template-columns: minmax(116px, auto) 1fr 44px;
        align-items: center;
        gap: 12px;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid #eef2f6;
    }

    .cart-item-qty-wrap {
        min-width: 0;
    }

    .cart-item-qty.product_number_count {
        display: inline-flex !important;
        align-items: center;
        height: 42px;
        min-width: 124px;
        border: 1px solid #d9e1ea;
        border-radius: 10px;
        overflow: hidden;
        background: #ffffff;
    }

    .cart-item-qty .count_single_item {
        width: 42px;
        height: 42px;
        min-width: 42px;
        border: 0;
        border-radius: 0;
        background: #ffffff;
        color: #344054;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        line-height: 1;
        box-shadow: none;
        outline: none;
    }

    .cart-item-qty button.count_single_item {
        cursor: pointer;
        transition: background-color 0.18s ease, color 0.18s ease;
    }

    .cart-item-qty button.count_single_item:hover {
        background: #f6f8fb;
        color: #101828;
    }

    .cart-item-qty .input-number {
        width: 42px;
        text-align: center;
        font-size: 15px;
        font-weight: 700;
        color: #101828;
        border-left: 1px solid #d9e1ea;
        border-right: 1px solid #d9e1ea;
        -moz-appearance: textfield;
    }

    .cart-item-qty .input-number::-webkit-outer-spin-button,
    .cart-item-qty .input-number::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .cart-item-price-wrap {
        min-width: 0;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 18px;
    }

    .cart-item-unit-price,
    .cart-item-total-price {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 3px;
        min-width: 0;
    }

    .cart-item-price-label {
        color: #667085;
        font-size: 11px;
        font-weight: 500;
        line-height: 1.1;
    }

    .cart-item-total-price strong {
        color: #101828;
        font-size: 20px;
        font-weight: 800;
        line-height: 1;
        letter-spacing: -0.02em;
        white-space: nowrap;
    }

    .cart-item-unit-price strong {
        color: #344054;
        font-size: 14px;
        font-weight: 700;
        white-space: nowrap;
    }

    .cart-item-delete {
        width: 42px;
        height: 42px;
        min-width: 42px;
        border: 1px solid #fee4e2;
        border-radius: 10px;
        background: #fffafa;
        color: #d92d20;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        line-height: 1;
        transition: background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease;
    }

    .cart-item-delete:hover {
        background: #fef3f2;
        border-color: #fda29b;
        color: #b42318;
    }

    .cart-item-delete svg {
        display: block;
    }

    @media (max-width: 575.98px) {
        .cart-item-inner {
            padding: 14px;
            border-radius: 13px;
        }

        .cart-item-main {
            gap: 12px;
        }

        .cart-item-thumb {
            width: 80px;
            height: 104px;
            border-radius: 11px;
        }

        .cart-item-title {
            font-size: 14px;
            line-height: 1.3;
        }

        .cart-item-chip {
            min-height: 26px;
            padding: 5px 8px;
            font-size: 11.5px;
        }

        .cart-item-meta-row {
            font-size: 12px;
        }

        .cart-item-action-row {
            grid-template-columns: auto 1fr 42px;
            gap: 10px;
            margin-top: 13px;
            padding-top: 13px;
        }

        .cart-item-qty.product_number_count {
            min-width: 116px;
            height: 40px;
        }

        .cart-item-qty .count_single_item {
            width: 38px;
            height: 40px;
            min-width: 38px;
        }

        .cart-item-qty .input-number {
            width: 40px;
            min-width: 40px;
            font-size: 14px;
        }

        .cart-item-total-price strong {
            font-size: 18px;
        }

        .cart-item-delete {
            width: 40px;
            height: 40px;
            min-width: 40px;
        }
    }

    @media (max-width: 374.98px) {
        .cart-item-inner {
            padding: 12px;
        }

        .cart-item-main {
            gap: 10px;
        }

        .cart-item-thumb {
            width: 72px;
            height: 96px;
        }

        .cart-item-action-row {
            grid-template-columns: 1fr auto;
            grid-template-areas:
                "qty delete"
                "price price";
            align-items: center;
        }

        .cart-item-qty-wrap {
            grid-area: qty;
        }

        .cart-item-price-wrap {
            grid-area: price;
            justify-content: flex-start;
            margin-top: 2px;
        }

        .cart-item-delete {
            grid-area: delete;
            justify-self: end;
        }

        .cart-item-total-price {
            align-items: flex-start;
        }

        .cart-item-total-price strong {
            font-size: 19px;
        }
    }

    @media (min-width: 992px) {
        .cart-items-list {
            gap: 12px;
        }

        .cart-item-inner {
            display: grid;
            grid-template-columns: minmax(0, 5fr) minmax(120px, 2fr) minmax(150px, 2fr) minmax(130px, 2fr) 64px;
            align-items: center;
            gap: 0;
            padding: 18px;
            border-radius: 16px;
            min-height: 124px;
        }

        .cart-item-main {
            grid-column: 1;
            display: flex;
            align-items: center;
            gap: 16px;
            min-width: 0;
            padding-right: 20px;
        }

        .cart-item-thumb {
            width: 74px;
            height: 86px;
            border-radius: 12px;
        }

        .cart-item-content {
            min-width: 0;
        }

        .cart-item-title {
            font-size: 15px;
            line-height: 1.3;
            max-width: 100%;
        }

        .cart-item-attributes {
            margin-top: 8px;
            gap: 6px;
        }

        .cart-item-chip {
            min-height: 26px;
            padding: 5px 9px;
            font-size: 11.5px;
        }

        .cart-item-meta {
            margin-top: 9px;
            gap: 5px;
        }

        .cart-item-meta-row {
            font-size: 12px;
        }

        /*
        Desktop trick:
        The action row should not behave like one grouped flex row.
        Its children need to become real grid columns matching the header.
        */
        .cart-item-action-row {
            display: contents;
            margin-top: 0;
            padding-top: 0;
            border-top: 0;
        }

        .cart-item-price-wrap {
            display: contents;
        }

        .cart-item-unit-price {
            grid-column: 2;
            display: flex !important;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            min-width: 0;
            text-align: center;
        }

        .cart-item-qty-wrap {
            grid-column: 3;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 0;
        }

        .cart-item-total-price {
            grid-column: 4;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            min-width: 0;
            text-align: center;
        }

        .cart-item-delete {
            grid-column: 5;
            justify-self: center;
            align-self: center;
        }

        .cart-item-price-label {
            color: #667085;
            font-size: 11px;
            font-weight: 500;
            line-height: 1;
        }

        .cart-item-unit-price strong {
            color: #101828;
            font-size: 14px;
            font-weight: 800;
            line-height: 1.1;
            white-space: nowrap;
        }

        .cart-item-total-price strong {
            color: #101828;
            font-size: 18px;
            font-weight: 800;
            line-height: 1.1;
            white-space: nowrap;
        }

        .cart-item-qty.product_number_count {
            min-width: 126px;
            height: 40px;
            border-radius: 10px;
        }

        .cart-item-qty .count_single_item {
            width: 40px;
            height: 40px;
            min-width: 40px;
        }

        .cart-item-qty .input-number {
            width: 46px;
            min-width: 46px;
            font-size: 14px;
        }

        .cart-item-delete {
            width: 42px;
            height: 42px;
            min-width: 42px;
        }
    }
</style>
@endpush