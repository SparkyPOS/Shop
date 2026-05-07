<style>
    .refresh_btn{
        background-color: #f8f9fa;
        border-color: #f8f9fa;
    }
    .refresh_btn:hover{
        background-color: #fff;
        border-color: #fff;
    }
    .filter_accordion_block {
        border-top: 1px solid #d8dde6;
    }

    .filter_accordion_head {
        width: 100%;
        border: 0;
        background: transparent;
        padding: 18px 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: #111827;
        cursor: pointer;
    }

    .filter_accordion_head h4 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        line-height: 1.3;
    }

    .filter_accordion_icon {
        font-size: 22px;
        line-height: 1;
        font-weight: 300;
    }

    .filter_accordion_body {
        display: none;
        padding: 0 0 22px;
    }

    .filter_accordion_block.is-open > .filter_accordion_body {
        display: block;
    }

    .filter_accordion_icon::before {
        content: "+";
        display: inline-block;
        font-size: 22px;
        line-height: 1;
        font-weight: 300;
    }

    .filter_accordion_block.is-open .filter_accordion_icon::before {
        content: "−";
    }

    .filter_check_list {
        display: flex;
        flex-direction: column;
        gap: 14px;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .filter_option_label {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0;
        cursor: pointer;
        color: #667085;
        font-size: 14px;
        font-weight: 400;
    }

    .filter_option_label input {
        display: none;
    }

    .filter_fake_checkbox {
        width: 22px;
        height: 22px;
        border: 1px solid #d6dce5;
        border-radius: 4px;
        background: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 22px;
        position: relative;
    }

    .filter_option_label input:checked + .filter_fake_checkbox {
        border-color: #081225;
        background: #081225;
    }

    .filter_option_label input:checked + .filter_fake_checkbox::after {
        content: "";
        width: 6px;
        height: 11px;
        border: solid #fff;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
        margin-top: -2px;
    }

    .filter_color_dot {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 1px solid #d6dce5;
        flex: 0 0 16px;
        display: inline-block;
    }

    .filter_color_dot.is-white {
        background: #ffffff;
    }

    .filter_color_dot.is-multi {
        background: linear-gradient(135deg, #ef4444 0%, #f59e0b 20%, #facc15 40%, #22c55e 60%, #3b82f6 80%, #8b5cf6 100%);
    }
</style>
<div class="col-lg-4 col-xl-3">
    <div id="product_category_chose" class="product_category_chose mb_30 mt-1">
        <div class="course_title mb_15 d-flex align-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="19.5" height="13" viewBox="0 0 19.5 13">
                <g id="filter-icon" transform="translate(28)">
                    <rect id="Rectangle_1" data-name="Rectangle 1" width="19.5" height="2" rx="1" transform="translate(-28)" fill="#fd4949"/>
                    <rect id="Rectangle_2" data-name="Rectangle 2" width="15.5" height="2" rx="1" transform="translate(-26 5.5)" fill="#fd4949"/>
                    <rect id="Rectangle_3" data-name="Rectangle 3" width="5" height="2" rx="1" transform="translate(-20.75 11)" fill="#fd4949"/>
                </g>
            </svg>
            <h5 class="font_16 f_w_700 mb-0 ">{{__('amazy.Filter Products')}}</h5>
            <div class="catgory_sidebar_closeIcon flex-fill justify-content-end d-flex d-lg-none">
                <button id="catgory_sidebar_closeIcon" class="home10_primary_btn2 gj-cursor-pointer mb-0 small_btn">{{__('amazy.close')}}</button>
            </div>
            <button type="button" class="btn btn-sm btn-outline-light text-dark refresh_btn" id="refresh_btn">{{__('amazy.refresh')}}</button>
        </div>
        <div class="course_category_inner">
          @php
          	$link_value=null;
            $link_type=null;
            $sigment = request()->segment(2);
          	if(isset($_GET['item']) && $_GET['item']=='brand' && $sigment != null)
            {

          		$link_type = 'brand';
          		$data_item =  DB::table('brands')->where('slug',$sigment)->first();
          		$link_value = !empty($data_item) ? $data_item->id:null;

            }

          $url = route('frontend.product_filter_by_type',[ 'filter' =>$link_type, 'filter_value' => $link_value]);
          @endphp

          @if($link_type != null && $link_value != null)
          <input type='hidden' id="filterUrl" value="{{$url}}">
          @else
          <input type='hidden' id="filterUrl" value="{{route('frontend.product_filter_by_type')}}" >
          @endif


            @isset($CategoryList)
                @if (count($CategoryList) > 0)
                    @foreach($CategoryList as $key => $category)
                    <div class="single_pro_categry">
                        <h4 class="font_18 f_w_700 getProductByChoice cursor_pointer" data-id="cat" data-value="{{ $category->id }}">
                            {{$category->name}}
                        </h4>
                        <ul class="Check_sidebar mb_35">
                            @if (count($category->subCategories) > 0)
                                @foreach($category->subCategories as $key => $subCategory)
                                <li>
                                    <label class="primary_checkbox d-flex">
                                        <input type="checkbox" class="getProductByChoice attr_checkbox" data-id="cat"
                                        data-value="{{ $subCategory->id }}">
                                        <span class="checkmark mr_10"></span>
                                        <span class="label_name">{{$subCategory->name}}</span>
                                    </label>
                                </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                    @endforeach
                @endif
            @endisset

            <div class="single_pro_categry">
                <h4 class="font_18 f_w_700">Filter By Stores</h4>
                <div class="mb_15">
                    <input type="text" id="store_query" class="primary_input4 w-100" placeholder="Store Name" />
                </div>
                <h4 class="font_18 f_w_700">Filter By Vendors</h4>
                <div class="mb_35">
                    <input type="text" id="vendor_query" class="primary_input4 w-100" placeholder="Vendor ID Or Name" />
                </div>
            </div>
            <script>
                (function($){
                    let debounceTimer = null;
                    function applySellerQuery(type, value){
                        try{ if(typeof filterType === 'undefined'){ window.filterType = []; } }catch(e){ window.filterType = []; }
                        const idx = filterType.findIndex(function(obj){ return obj.filterTypeId === type; });
                        if(idx >= 0){ filterType.splice(idx,1); }
                        const val = (value || '').trim();
                        if(val.length){ filterType.push({ filterTypeId: type, filterTypeValue: [val] }); }
                        const requestItem = $('#item_request').val();
                        const requestItemType = $('#item_request_type').val();
                        $('#pre-loader').show();
                        $.post($('#filterUrl').val(), {
                            _token: '{{ csrf_token() }}',
                            filterType: filterType,
                            requestItem: requestItem,
                            requestItemType: requestItemType
                        }, function(data){
                            $('#dataWithPaginate').html(data);
                            if($.fn.niceSelect){ $('#product_short_list').niceSelect(); $('#paginate_by').niceSelect(); }
                            $('#pre-loader').hide();
                            if(typeof activeTab === 'function'){ activeTab(); }
                            if(typeof initLazyload === 'function'){ initLazyload(); }
                        });
                    }
                    $('#vendor_query').on('keyup', function(){
                        clearTimeout(debounceTimer);
                        const v = $(this).val();
                        debounceTimer = setTimeout(function(){ applySellerQuery('vendor_query', v); }, 400);
                    });
                    $('#store_query').on('keyup', function(){
                        clearTimeout(debounceTimer);
                        const v = $(this).val();
                        debounceTimer = setTimeout(function(){ applySellerQuery('store_query', v); }, 400);
                    });
                })(jQuery);
            </script>
            <!-- @isset($color)
                @if ($color != null && $color->id == 1)
                    <div class="single_pro_categry">
                        <h4 class="font_18 f_w_700">
                            {{ $color->name }}
                        </h4>
                        <div class="color_filter">
                            @foreach ($color->values as $k => $color_name)
                                <div class="single_coulorFilter">
                                    <label class="round_checkbox d-flex" for="checkbox-{{$k}}">
                                        <input id="checkbox-{{$k}}" name="color[]" id="color" type="checkbox" color="color" data-id="{{ $color->id }}" data-value="{{ $color_name->id }}" class="getProductByChoice" value="{{ $color_name->color->name }}"/>
                                        <span class="checkmark colors_{{$k}}"></span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endisset -->
            @isset($size)
                @if($size != null && $size->values && $size->values->count() > 0)
                    <div class="filter_accordion_block" data-filter-accordion>
                        <button type="button" class="filter_accordion_head">
                            <h4>{{ is_array($size->name) ? ($size->name['en'] ?? reset($size->name)) : (json_decode($size->name, true)['en'] ?? $size->name) }}</h4>
                            <span class="filter_accordion_icon"></span>
                        </button>

                        <div class="filter_accordion_body">
                            <ul class="filter_check_list">
                                @foreach($size->values as $sizeValue)
                                    <li class="filter_check_item">
                                        <label class="filter_option_label">
                                            <input
                                                type="checkbox"
                                                name="size[]"
                                                class="getProductByChoice filter_attr_checkbox"
                                                data-id="{{ $size->id }}"
                                                data-value="{{ $sizeValue->id }}"
                                                value="{{ $sizeValue->value }}"
                                            >
                                            <span class="filter_fake_checkbox"></span>
                                            <span>{{ $sizeValue->value }}</span>
                                        </label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            @endisset

            @isset($color)
                @if($color != null && $color->values && $color->values->count() > 0)
                    @php
                        $colorName = $color->name;

                        if (is_string($colorName)) {
                            $decodedColorName = json_decode($colorName, true);

                            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedColorName)) {
                                $colorName = $decodedColorName['en'] ?? reset($decodedColorName);
                            }
                        }

                        $knownColorMap = [
                            'black' => '#000000',
                            'coral orange' => '#ff7f50',
                            'ecru' => '#cdb891',
                            'green' => '#778d2d',
                            'hot pink' => '#ff69b4',
                            'mauve' => '#b784a7',
                            'olive' => '#808000',
                            'peach' => '#f3c6a7',
                            'pink' => '#f472a6',
                            'plum' => '#673147',
                            'seafoam' => '#93d4c2',
                            'taupe' => '#9f8f83',
                            'dk indigo' => '#263b5e',
                            'dk.indigo' => '#263b5e',
                            'dark indigo' => '#263b5e',
                            'indigo' => '#2f4f7f',
                            'lt.indigo wash' => '#7892ad',
                            'lt indigo wash' => '#7892ad',
                            'indigo blush' => '#536f8f',
                            'raw denim' => '#1f334d',
                            'tied wash light' => '#9caec0',
                            'daisy light wash' => '#aab9c6',
                            'geometric light' => '#a7b3be',
                            'geometric indigo' => '#314c73',
                            'lily light' => '#a8b8c5',
                            'lily stone wash' => '#7f8d99',
                            'dark clove' => '#4a3730',
                            'carbenet' => '#5d1f2f',
                            'cabernet' => '#5d1f2f',
                            'greenfinch' => '#6f7f2a',
                            'caramel' => '#b7793f',
                            'orchid light' => '#b9a6c8',
                            'orchid stone wash' => '#9d8ca9',
                            'silver grey' => '#b8bec4',
                            'dk.indigowash' => '#263b5e',
                            'dk indigo wash' => '#263b5e',
                            'indigowash' => '#2f4f7f',
                            'indigo wash' => '#2f4f7f',
                            'vintagewash' => '#6f7f8f',
                            'vintage wash' => '#6f7f8f',
                            'lt.indigowash' => '#7892ad',
                            'lt indigo wash' => '#7892ad',
                            'classicblue' => '#315f92',
                            'classic blue' => '#315f92',
                            'blueblush' => '#5e7fa7',
                            'blue blush' => '#5e7fa7',
                            'snowwash' => '#d6dde5',
                            'snow wash' => '#d6dde5',
                            'camo' => '#6b705c',
                            'orange' => '#ff8a2a',
                        ];

                        $hexNameMap = [
                            '#000000' => 'Black',
                            '#000' => 'Black',
                            '#ffffff' => 'White',
                            '#fff' => 'White',
                            '#ff0000' => 'Red',
                            '#00ff00' => 'Green',
                            '#0000ff' => 'Blue',
                            '#ffff00' => 'Yellow',
                            '#ffa500' => 'Orange',
                            '#ffc0cb' => 'Pink',
                            '#800080' => 'Purple',
                            '#808080' => 'Grey',
                            '#a52a2a' => 'Brown',
                        ];

                        $colorOptions = [];

                        foreach ($color->values as $colorValue) {
                            $rawColorName = trim((string) $colorValue->value);

                            if ($rawColorName == '') {
                                continue;
                            }

                            $normalizedColorName = strtolower($rawColorName);
                            $normalizedColorName = str_replace(['  '], ' ', $normalizedColorName);

                            $isHexColor = substr($normalizedColorName, 0, 1) == '#';
                            $isMulti = in_array($normalizedColorName, ['multi-colored', 'multicolored', 'multi color', 'multi-color']);

                            if ($isMulti) {
                                $displayName = 'Multi-Colored';
                                $colorCode = '';
                                $dedupeKey = 'multi-colored';
                            } elseif ($isHexColor) {
                                $normalizedHex = strtolower($rawColorName);

                                if (strlen($normalizedHex) == 4) {
                                    $normalizedHex = '#' . $normalizedHex[1] . $normalizedHex[1] . $normalizedHex[2] . $normalizedHex[2] . $normalizedHex[3] . $normalizedHex[3];
                                }

                                $displayName = $hexNameMap[$normalizedHex] ?? strtoupper($rawColorName);
                                $colorCode = $normalizedHex;
                                $dedupeKey = 'hex_' . $normalizedHex;
                            } else {
                                $displayName = $rawColorName;
                                $colorCode = $knownColorMap[$normalizedColorName] ?? '#d1d5db';

                                if (isset($knownColorMap[$normalizedColorName])) {
                                    $dedupeKey = 'color_' . strtolower($normalizedColorName);
                                } else {
                                    $dedupeKey = 'name_' . preg_replace('/[^a-z0-9]+/', '_', $normalizedColorName);
                                }
                            }

                            if (!isset($colorOptions[$dedupeKey])) {
                                $colorOptions[$dedupeKey] = [
                                    'ids' => [],
                                    'display_name' => $displayName,
                                    'color_code' => $colorCode,
                                    'is_white' => in_array(strtolower($colorCode), ['#ffffff', '#fff']),
                                    'is_multi' => $isMulti,
                                ];
                            }

                            $colorOptions[$dedupeKey]['ids'][] = $colorValue->id;
                        }

                        $colorSortOrder = [
                            'White' => 1,
                            'Ecru' => 2,
                            'Silver Grey' => 3,
                            'Black' => 4,
                            'Pink' => 5,
                            'Hot Pink' => 6,
                            'Coral Orange' => 7,
                            'Peach' => 8,
                            'Green' => 9,
                            'Olive' => 10,
                            'Seafoam' => 11,
                            'Dk Indigo' => 12,
                            'Lt.Indigo Wash' => 13,
                            'Indigo Blush' => 14,
                            'Raw Denim' => 15,
                            'Plum' => 16,
                            'Mauve' => 17,
                            'Taupe' => 18,
                            'Multi-Colored' => 99,
                        ];

                        uasort($colorOptions, function ($a, $b) use ($colorSortOrder) {
                            $aOrder = $colorSortOrder[$a['display_name']] ?? 500;
                            $bOrder = $colorSortOrder[$b['display_name']] ?? 500;

                            if ($aOrder == $bOrder) {
                                return strcmp($a['display_name'], $b['display_name']);
                            }

                            return $aOrder <=> $bOrder;
                        });
                    @endphp

                    @if(count($colorOptions) > 0)
                        <div class="filter_accordion_block" data-filter-accordion>
                            <button type="button" class="filter_accordion_head">
                                <h4>{{ $colorName }}</h4>
                                <span class="filter_accordion_icon" aria-hidden="true"></span>
                            </button>

                            <div class="filter_accordion_body">
                                <ul class="filter_check_list">
                                    @foreach($colorOptions as $colorOption)
                                        <li class="filter_check_item">
                                            <label class="filter_option_label">
                                                <input
                                                    type="checkbox"
                                                    name="color[]"
                                                    class="getProductByChoice filter_attr_checkbox"
                                                    color="color"
                                                    data-id="{{ $color->id }}"
                                                    data-value="{{ implode(',', array_unique($colorOption['ids'])) }}"
                                                    value="{{ $colorOption['display_name'] }}"
                                                >
                                                <span class="filter_fake_checkbox"></span>
                                                <span
                                                    class="filter_color_dot {{ $colorOption['is_white'] ? 'is-white' : '' }} {{ $colorOption['is_multi'] ? 'is-multi' : '' }}"
                                                    @if(!$colorOption['is_multi'])
                                                        style="background: {{ $colorOption['color_code'] }};"
                                                    @endif
                                                ></span>
                                                <span>{{ $colorOption['display_name'] }}</span>
                                            </label>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                @endif
            @endisset
            <div class="single_pro_categry">
                <h4 class="font_18 f_w_700">
                {{__('common.filter_by_rating')}}
                </h4>
                <ul class="rating_lists mb_35">
                    <li>
                        <div class="ratings">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <label class="primary_checkbox d-flex filter-by-rating-one">
                                <input type="checkbox" name="attr_value[]" class="getProductByChoice attr_checkbox" data-id="rating" data-value="5" id="attr_value">
                                <span class="checkmark mr_10"></span>
                            </label>
                        </div>
                    </li>
                    <li>
                        <div class="ratings">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star unrated"></i>
                            <span>{{__('defaultTheme.and_up')}}</span>
                            <label class="primary_checkbox d-flex filter-by-ratings">
                                <input type="checkbox" name="attr_value[]" class="getProductByChoice attr_checkbox" data-id="rating" data-value="4" id="attr_value">
                                <span class="checkmark mr_10"></span>
                            </label>
                        </div>
                    </li>
                    <li>
                        <div class="ratings">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star unrated"></i>
                            <i class="fas fa-star unrated"></i>
                            <span>{{__('defaultTheme.and_up')}}</span>
                            <label class="primary_checkbox d-flex filter-by-ratings">
                                <input type="checkbox" name="attr_value[]" class="getProductByChoice attr_checkbox" data-id="rating" data-value="3" id="attr_value">
                                <span class="checkmark mr_10"></span>
                            </label>
                        </div>
                    </li>
                    <li>
                        <div class="ratings">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star unrated"></i>
                            <i class="fas fa-star unrated"></i>
                            <i class="fas fa-star unrated"></i>
                            <span>{{__('defaultTheme.and_up')}}</span>
                            <label class="primary_checkbox d-flex filter-by-ratings">
                                <input type="checkbox" name="attr_value[]" class="getProductByChoice attr_checkbox" data-id="rating" data-value="2" id="attr_value">
                                <span class="checkmark mr_10"></span>
                            </label>
                        </div>
                    </li>
                    <li>
                        <div class="ratings">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star unrated"></i>
                            <i class="fas fa-star unrated"></i>
                            <i class="fas fa-star unrated"></i>
                            <i class="fas fa-star unrated"></i>
                            <span>{{__('defaultTheme.and_up')}}</span>
                            <label class="primary_checkbox d-flex filter-by-ratings">
                                <input type="checkbox" name="attr_value[]" class="getProductByChoice attr_checkbox" data-id="rating" data-value="1" id="attr_value">
                                <span class="checkmark mr_10"></span>
                            </label>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="single_pro_categry">
                <h4 class="font_18 f_w_700">
                {{__('common.filter_by_price')}}
                </h4>
                <div class="filter_wrapper">
                    <input type="hidden" id="min_price" value="{{ $min_price_lowest }}" />
                    <input type="hidden" id="max_price" value="{{ $max_price_highest }}" />
                    <div id="slider-range"></div>
                    <div class="d-flex align-items-center prise_line">
                        <button class="home10_primary_btn2 mr_20 mb-0 small_btn js-range-slider-0">{{__('common.filter')}}</button>
                        <span>{{__('common.price')}}: </span> <input type="text" id="amount" readonly >
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('click', function (event) {
        var accordionHead = event.target.closest('.filter_accordion_head');

        if (!accordionHead) {
            return;
        }

        var accordionBlock = accordionHead.closest('[data-filter-accordion]');

        if (!accordionBlock) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        accordionBlock.classList.toggle('is-open');
    });
</script>