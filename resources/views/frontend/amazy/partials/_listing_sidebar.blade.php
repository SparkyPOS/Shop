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

    .filter_color_list {
        max-height: 420px;
        overflow-y: auto;
        padding-right: 6px;
    }

    .filter_color_list::-webkit-scrollbar {
        width: 4px;
    }

    .filter_color_list::-webkit-scrollbar-thumb {
        background: #cfd4df;
        border-radius: 20px;
    }

    .filter_color_list::-webkit-scrollbar-track {
        background: transparent;
    }

    .filter_color_dot {
        box-shadow: inset 0 0 0 1px rgba(0, 0, 0, .04);
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

                        $normalizeColorKey = function ($value) {
                            $value = trim((string) $value);
                            $value = preg_replace('/([a-z])([A-Z])/', '$1 $2', $value);
                            $value = strtolower($value);
                            $value = str_replace(['.', '_', '-', '/'], ' ', $value);
                            $value = preg_replace('/\s+/', ' ', $value);
                            $value = trim($value);

                            return $value;
                        };

                        $compactColorKey = function ($value) use ($normalizeColorKey) {
                            $value = $normalizeColorKey($value);
                            return preg_replace('/[^a-z0-9#]+/', '', $value);
                        };

                        $formatColorLabel = function ($value) use ($normalizeColorKey) {
                            $value = $normalizeColorKey($value);

                            $labelMap = [
                                'dk indigo' => 'Dk Indigo',
                                'dk indigo wash' => 'Dk Indigo Wash',
                                'lt indigo' => 'Lt Indigo',
                                'lt indigo wash' => 'Lt Indigo Wash',
                                'med indigo' => 'Med Indigo',
                                'med wash' => 'Med Wash',
                                'raw denim' => 'Raw Denim',
                                'classic blue' => 'Classic Blue',
                                'classic red' => 'Classic Red',
                                'blue blush' => 'Blue Blush',
                                'indigo blush' => 'Indigo Blush',
                                'indigo wash' => 'Indigo Wash',
                                'indigo stone wash' => 'Indigo Stone Wash',
                                'rose light wash' => 'Rose Light Wash',
                                'rose stone wash' => 'Rose Stone Wash',
                                'daisy light wash' => 'Daisy Light Wash',
                                'daisy stone wash' => 'Daisy Stone Wash',
                                'geometric indigo' => 'Geometric Indigo',
                                'geometric light' => 'Geometric Light',
                                'lily light' => 'Lily Light',
                                'lily stone wash' => 'Lily Stone Wash',
                                'stone wash' => 'Stone Wash',
                                'stone wash dark' => 'Stone Wash Dark',
                                'stone wash indigo' => 'Stone Wash Indigo',
                                'stone wash light' => 'Stone Wash Light',
                                'stone wash med' => 'Stone Wash Med',
                                'tied wash dark' => 'Tied Wash Dark',
                                'tied wash light' => 'Tied Wash Light',
                                'tone indigo wash' => 'Tone Indigo Wash',
                                'two tone' => 'Two Tone',
                                'zone wash' => 'Zone Wash',
                                'black blush' => 'Black Blush',
                                'black snow' => 'Black Snow',
                                'black wash' => 'Black Wash',
                                'ebony black' => 'Ebony Black',
                                'cloud wash' => 'Cloud Wash',
                                'blush wash' => 'Blush Wash',
                                'snow wash' => 'Snow Wash',
                                'dark blue' => 'Dark Blue',
                                'dark clove' => 'Dark Clove',
                                'dark snow' => 'Dark Snow',
                                'dark stone wash' => 'Dark Stone Wash',
                                'dark wash' => 'Dark Wash',
                                'light snow' => 'Light Snow',
                                'light stone wash' => 'Light Stone Wash',
                                'orchid light' => 'Orchid Light',
                                'olive green' => 'Olive Green',
                                'emerald green' => 'Emerald Green',
                                'jade green' => 'Jade Green',
                                'spruce green' => 'Spruce Green',
                                'heather gray' => 'Heather Gray',
                                'terra cotta' => 'Terra Cotta',
                                'fire red' => 'Fire Red',
                                'sand blast' => 'Sand Blast',
                                'sand blush' => 'Sand Blush',
                                'sandblast blue wash' => 'Sandblast Blue Wash',
                            ];

                            if (isset($labelMap[$value])) {
                                return $labelMap[$value];
                            }

                            return ucwords($value);
                        };

                        $colorCodeByKey = [
                            'white' => '#f7f7f2',
                            'almond' => '#d8c7a7',
                            'amber' => '#c47f27',
                            'black' => '#000000',
                            'ebonyblack' => '#101010',
                            'blackblush' => '#2b2528',
                            'blacksnow' => '#3b3f45',
                            'blackwash' => '#2b2b2b',
                            'blue' => '#2563a8',
                            'blueblush' => '#5e7fa7',
                            'burgundy' => '#7d0019',
                            'caramel' => '#b7793f',
                            'carbenet' => '#5d1f2f',
                            'cabernet' => '#5d1f2f',
                            'cheetach' => '#b38855',
                            'chestnut' => '#7a4a2f',
                            'classicblue' => '#315f92',
                            'classicred' => '#a32020',
                            'cloudwash' => '#c4ccd6',
                            'coral' => '#ff7f6e',
                            'daisylightwash' => '#aab9c6',
                            'daisystonewash' => '#8493a0',
                            'darkblue' => '#183b67',
                            'darkclove' => '#4a3730',
                            'darksnow' => '#3b4654',
                            'darkstonewash' => '#596b7a',
                            'darkwash' => '#23384f',
                            'dkindigo' => '#263b5e',
                            'dkindigowash' => '#263b5e',
                            'dkindigo' => '#263b5e',
                            'dkwash' => '#263b5e',
                            'eggplant' => '#4b274f',
                            'emeraldgreen' => '#157a4b',
                            'firered' => '#d12828',
                            'fuchsia' => '#d9469a',
                            'geometricindigo' => '#314c73',
                            'geometriclight' => '#a7b3be',
                            'gray' => '#9ca3af',
                            'grey' => '#9ca3af',
                            'green' => '#778d2d',
                            'greenfinch' => '#6f7f2a',
                            'heathergray' => '#a7adb6',
                            'heathergrey' => '#a7adb6',
                            'honey' => '#d59b36',
                            'indigo' => '#2f4f7f',
                            'indigoblush' => '#536f8f',
                            'indigostonewash' => '#66798d',
                            'indigowash' => '#2f4f7f',
                            'jadegreen' => '#3f8f6b',
                            'khaki' => '#b6a16b',
                            'lightsnow' => '#d9e0e7',
                            'lightstonewash' => '#a0adba',
                            'lilylight' => '#a8b8c5',
                            'lilystonewash' => '#7f8d99',
                            'ltindigo' => '#6f8ead',
                            'ltindigowash' => '#7892ad',
                            'ltwash' => '#9baec2',
                            'medindigo' => '#41698e',
                            'medwash' => '#7f97ad',
                            'moss' => '#6d7652',
                            'mustard' => '#d6a51f',
                            'navy' => '#172c4f',
                            'oatmeal' => '#d8cdbb',
                            'olivegreen' => '#677238',
                            'orange' => '#ff8a2a',
                            'orchidlight' => '#b9a6c8',
                            'red' => '#d72d3a',
                            'roselightwash' => '#b8c3cf',
                            'rosestonewash' => '#8d99a6',
                            'royalblue' => '#1d4ed8',
                            'sandblast' => '#c5b79d',
                            'sandblush' => '#d6b2a6',
                            'sandblastbluewash' => '#8299b5',
                            'sandstone' => '#a89276',
                            'shadeswashed' => '#8e9aa8',
                            'snowwash' => '#d6dde5',
                            'sprucegreen' => '#315f4b',
                            'stonewash' => '#7f8d99',
                            'stonewashdark' => '#4f5f70',
                            'stonewashindigo' => '#526b86',
                            'stonewashlight' => '#a8b5c1',
                            'stonewashmed' => '#738496',
                            'tan' => '#c8a77a',
                            'teal' => '#0f766e',
                            'terracotta' => '#b9603a',
                            'tiedwashdark' => '#53606c',
                            'tiedwashlight' => '#9caec0',
                            'toneindigowash' => '#526f8e',
                            'twotone' => '#7c8896',
                            'vintage' => '#6f7f8f',
                            'vintagewash' => '#6f7f8f',
                            'wine' => '#6b102c',
                            'yam' => '#c46f2f',
                            'yellow' => '#ffd760',
                            'zonewash' => '#8192a4',
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
                            '#808080' => 'Gray',
                            '#a52a2a' => 'Brown',
                        ];

                        $colorOptions = [];

                        foreach ($color->values as $colorValue) {
                            $rawColorName = trim((string) $colorValue->value);

                            if ($rawColorName == '') {
                                continue;
                            }

                            $normalizedColorName = $normalizeColorKey($rawColorName);
                            $compactKey = $compactColorKey($rawColorName);
                            $isHexColor = substr($normalizedColorName, 0, 1) == '#';
                            $isMulti = in_array($compactKey, ['multicolored', 'multicolor']);

                            if ($isMulti) {
                                $displayName = 'Multi-Colored';
                                $colorCode = '';
                                $dedupeKey = 'multi_colored';
                            } elseif ($isHexColor) {
                                $normalizedHex = strtolower($rawColorName);

                                if (strlen($normalizedHex) == 4) {
                                    $normalizedHex = '#' . $normalizedHex[1] . $normalizedHex[1] . $normalizedHex[2] . $normalizedHex[2] . $normalizedHex[3] . $normalizedHex[3];
                                }

                                $displayName = $hexNameMap[$normalizedHex] ?? strtoupper($rawColorName);
                                $colorCode = $normalizedHex;
                                $dedupeKey = 'hex_' . $normalizedHex;
                            } else {
                                $displayName = $formatColorLabel($rawColorName);
                                $colorCode = $colorCodeByKey[$compactKey] ?? '#cfd4df';
                                $dedupeKey = 'color_' . $compactKey;
                            }

                            if (!isset($colorOptions[$dedupeKey])) {
                                $colorOptions[$dedupeKey] = [
                                    'ids' => [],
                                    'display_name' => $displayName,
                                    'color_code' => $colorCode,
                                    'is_white' => in_array(strtolower($colorCode), ['#ffffff', '#fff', '#f7f7f2']),
                                    'is_multi' => $isMulti,
                                ];
                            }

                            $colorOptions[$dedupeKey]['ids'][] = $colorValue->id;
                        }

                        uasort($colorOptions, function ($a, $b) {
                            return strcmp($a['display_name'], $b['display_name']);
                        });
                    @endphp

                    @if(count($colorOptions) > 0)
                        <div class="filter_accordion_block is-open" data-filter-accordion>
                            <button type="button" class="filter_accordion_head">
                                <h4>{{ $colorName }}</h4>
                                <span class="filter_accordion_icon" aria-hidden="true"></span>
                            </button>

                            <div class="filter_accordion_body">
                                <ul class="filter_check_list filter_color_list">
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