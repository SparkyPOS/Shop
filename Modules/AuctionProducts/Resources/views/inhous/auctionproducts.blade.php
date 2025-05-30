@extends('backEnd.master')
@section('styles')
<link rel="stylesheet" href="{{asset(asset_path('modules/seller/css/index.css'))}}" />
@endsection
@section('mainContent')
<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-xl-12">
                <div class="white_box_30px mb_30">
                    <div class="tab-content">
                        @if (permissionCheck('product.get-data'))
                            <div role="tabpanel" class="tab-pane fade active show" id="order_processing_data">
                                <div class="box_header common_table_header ">
                                    <div class="main-title d-md-flex">
                                        <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('product.in_house_products') }}</h3>
                                    </div>
                                </div>
                                <div class="QA_section QA_section_heading_custom check_box_table">
                                    <div class="QA_table">
                                        <!-- table-responsive -->
                                        <div class="" id="stock_div">
                                            @include('auctionproducts::inhous.inhouse_auction_products_list')
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="module_check" value="{{isModuleActive('MultiVendor')?'true':'false'}}">
</section>
    <div id="product_detail_view_div"></div>
    @include('backEnd.partials._deleteModalForAjax',['item_name' => __('common.product '),'form_id' =>
'product_delete_form','modal_id' => 'product_delete_modal', 'delete_item_id' => 'product_delete_id'])
@endsection
@include('auctionproducts::inhous.scripts')
