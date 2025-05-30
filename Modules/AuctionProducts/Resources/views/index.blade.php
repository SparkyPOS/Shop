
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
                                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('product.auction_product_list') }}</h3>
                                            @if (permissionCheck('auctionproducts.auction.create'))
                                                <ul class="d-flex">
                                                    <li><a class="primary-btn radius_30px mr-10 fix-gr-bg" href="{{route("auctionproducts.auction.create")}}"><i class="ti-plus"></i>{{__('product.add_new_auction_product')}}</a></li>
                                                </ul>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="QA_section QA_section_heading_custom check_box_table">
                                        <div class="QA_table">
                                            <!-- table-responsive -->
                                            <div class="" id="product_list_div">
                                                @include('auctionproducts::auction_product_list')
                                            </div>
                                        </div>
                                    </div>
                                </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" id="module_check" value="{{isModuleActive('MultiVendor')?'true':'false'}}">
    </section>
    <div class="product_detail_view_div">
    </div>
@include('backEnd.partials._deleteModalForAjax',['item_name' => __('auctionproduct.delete_auction '),'form_id' =>
'auction_delete_form','modal_id' => 'auction_delete_modal', 'delete_item_id' => 'auction_delete_id'])
@endsection
@push('scripts')
@php
$url = route('auctionproducts.auction-get-product');
@endphp
<script type="text/javascript">
(function($){
        "use strict";
        var Table = '';
    $(document).ready(function(){
            var columnData = [
                { data: 'DT_RowIndex', name: 'id' },
                { data: 'auction_title', name: 'auction_title'},
                { data: 'product_name', name: 'product_name'},
                { data: 'owner', name: 'owner' },
                { data: 'bid_starting_amount', name: 'auction_products '},
                { data: 'auction_start_date', name: 'auction_start_date'},
                { data: 'auction_end_date', name: 'auction_end_date'},
                { data: 'total_bids', name: 'total_bids'},
                { data: 'status', name: 'status'},
                { data: 'action', name: 'action'}
            ]
            auctionProductDataTable();

            function auctionProductDataTable(){
            Table = $('#auctionProductTable').DataTable({
                processing: true,
                serverSide: true,
                "stateSave": true,
                "ajax": $.fn.dataTable.pipeline({
                    url: '{{route('auctionproducts.auction-get-product')}}',
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

    $(document).on('click', '.delete_auction', function(event){
            event.preventDefault();
            let type = $(this).data('type');
            let id = $(this).data('id');
            if(type == 'admin'){
                $('#auction_delete_id').val(id);
                $('#auction_delete_modal').modal('show');
            }else{
                $('#auction_delete_id').val(id);
                $('#auction_delete_modal').modal('show');
            }
    });
    $(document).on('submit', '#auction_delete_form', function(event) {
            event.preventDefault();
            $('#auction_delete_modal').modal('hide');
            $('#pre-loader').removeClass('d-none');
            var formData = new FormData();
            formData.append('_token', "{{ csrf_token() }}");
            formData.append('id', $('#auction_delete_id').val());
            $.ajax({
                url: "{{ route('auctionproducts.destroy') }}",
                type: "POST",
                cache: false,
                contentType: false,
                processData: false,
                data: formData,
                success: function(response) {
                    if(response.msg){
                        window.location.reload();
                        toastr.info(response.msg);
                    }else {
                        window.location.reload();
                        toastr.success("{{__('common.deleted_successfully')}}", "{{__('common.success')}}");
                    }
                    $('#pre-loader').addClass('d-none');
                },
                error: function(response) {
                    if(response.responseJSON.error){
                        toastr.error(response.responseJSON.error ,"{{__('common.error')}}");
                        $('#pre-loader').addClass('d-none');
                        return false;
                    }
                    toastr.error("{{__('common.error_message')}}", "{{__('common.error')}}");
                }
            });
        });


})(jQuery);
</script>
@endpush

