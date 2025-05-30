
@extends('backEnd.master')
@section('styles')
    <link rel="stylesheet" href="{{asset(asset_path('modules/product/css/product_index.css'))}}">
@endsection
@section('mainContent')
    <section class="admin-visitor-area up_st_admin_visitor">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-xl-12">
                    <div class="white_box_30px mb_30">
                        <div class="tab-content">

                                <div role="tabpanel" class="tab-pane fade active show" id="order_processing_data">
                                    <div class="box_header common_table_header ">
                                        <div class="main-title d-md-flex">
                                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('auctionproduct.view_all_bids') }}</h3>
                                            @if (permissionCheck('auctionproducts.auction-product'))
                                                <ul class="d-flex">
                                                    <li><a class="primary-btn radius_30px mr-10 fix-gr-bg" href="{{route("auctionproducts.auction-product")}}"><i class="ti-angle-double-left"></i>{{__('auctionproduct.auction_list')}}</a></li>
                                                </ul>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="QA_section QA_section_heading_custom check_box_table">
                                        <div class="QA_table">
                                            <!-- table-responsive -->
                                            <div class="" id="product_list_div">
                                                @include('auctionproducts::auction_all_bids_list')
                                            </div>
                                        </div>
                                    </div>
                                </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="id" value="{{$id}}">
        <input type="hidden" id="module_check" value="{{isModuleActive('MultiVendor')?'true':'false'}}">
    </section>
    <div class="product_detail_view_div">
    </div>
@include('backEnd.partials._deleteModalForAjax',['item_name' => __('common.product '),'form_id' =>
'product_delete_form','modal_id' => 'product_delete_modal', 'delete_item_id' => 'product_delete_id'])
@endsection

@includeIf('auctionproducts::components.bid_alert_award')

@push('scripts')
@php
// $url = route('auctionproducts.get.view.all.bids.data',$id);
@endphp
<script type="text/javascript">
(function($){
        "use strict";
        var Table = '';
    $(document).ready(function(){
            var columnData = [
                { data: 'DT_RowIndex', name: 'id' },
                { data: 'customer_name', name: 'customer_name'},
                { data: 'email', name: 'email' },
                { data: 'phone', name: 'phone'},
                 {data: 'reserve_price', name:'reserve_price' },
                { data: 'bid_count', name: 'bid_count'},
                { data: 'bid_amount', name: 'bid_amount'},
                // { data: 'confirm_order', name: 'confirm_order'},
                { data: 'cancel_order', name: 'cancel_order'},
                { data: 'date', name: 'date'},
                { data: 'action', name: 'action'}
            ]
            auctionProductDataTable();

            function auctionProductDataTable(){
            Table = $('#auctionBidTable').DataTable({
                processing: true,
                serverSide: true,
                "stateSave": true,
                "ajax": $.fn.dataTable.pipeline({
                    url: '{{route('auctionproducts.get.view.all.bids.data',$id)}}',
                data: function () {
                    //pass variable
                },
                pages: 5 // number of pages to cache
            }),
                "initComplete":function(json){

                },
                columns: columnData,
                bLengthChange: false,
                "bDestroy": true,
                language: {
                    search: "<i class='ti-search'></i>",
                    searchPlaceholder: trans('common.quick_search'),
                    paginate: {
                        next: "<i class='ti-arrow-right'></i>",
                        previous: "<i class='ti-arrow-left'></i>"
                    }
                },
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'copyHtml5',
                        text: '<i class="fa fa-files-o"></i>',
                        title: $("#header_title").text(),
                        titleAttr: 'Copy',
                        exportOptions: {
                            columns: ':visible',
                            columns: ':not(:last-child)',
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fa fa-file-excel-o"></i>',
                        titleAttr: 'Excel',
                        title: $("#header_title").text(),
                        margin: [10, 10, 10, 0],
                        exportOptions: {
                            columns: ':visible',
                            columns: ':not(:last-child)',
                        },

                    },
                    {
                        extend: 'csvHtml5',
                        text: '<i class="fa fa-file-text-o"></i>',
                        titleAttr: 'CSV',
                        exportOptions: {
                            columns: ':visible',
                            columns: ':not(:last-child)',
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fa fa-file-pdf-o"></i>',
                        title: $("#header_title").text(),
                        titleAttr: 'PDF',
                        exportOptions: {
                            columns: ':visible',
                            columns: ':not(:last-child)',
                        },
                        orientation: 'landscape',
                        pageSize: 'A4',
                        margin: [0, 0, 0, 0],
                        alignment: 'center',
                        header: true,
                        customize : function(doc){
                            var colCount = new Array();
                            var tbl = $('#mainProductTable');
                            $(tbl).find('tbody tr:first-child td').each(function(){
                                if($(this).attr('colspan')){
                                    for(var i=1;i<=$(this).attr('colspan');$i++){
                                        colCount.push('*');
                                    }
                                }else{ colCount.push('*'); }
                            });
                            doc.content[1].table.widths = colCount;
                        }

                    },
                    {
                        extend: 'print',
                        text: '<i class="fa fa-print"></i>',
                        titleAttr: 'Print',
                        title: $("#header_title").text(),
                        exportOptions: {
                            columns: ':not(:last-child)',
                        }
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fa fa-columns"></i>',
                        postfixButtons: ['colvisRestore']
                    }
                ],
                columnDefs: [{
                        visible: false
                }],
                    responsive: true,
            });
        }
    });

    $(document).on('click','.alert-award',function(){
        let auction_id = $(this).attr('data-auction-id');
        let bid_id = $(this).attr('data-bid-id');
        $("#bid_id_input").val(bid_id);
        $("#auction_id_input").val(auction_id);
        $("#bidAwardAlert").modal('show');
    });

})(jQuery);
</script>
@endpush

