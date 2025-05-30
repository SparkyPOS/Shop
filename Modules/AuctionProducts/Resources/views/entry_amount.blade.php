@extends('backEnd.master')
@section('styles')
<style>
    table.dataTable thead .sorting_asc::after {
      content: '\e62a';
      font-family: 'themify';
      position: absolute;
      top: 11px;
      left: 4px;
      -webkit-transition: all 0.2s ease-in-out;
      transition: all 0.2s ease-in-out;
    }
    table.dataTable thead .sorting::after {
      content: '\e62a';
      font-family: 'themify';
      position: absolute;
      top: 11px;
      left: 4px;
      -webkit-transition: all 0.2s ease-in-out;
      transition: all 0.2s ease-in-out;
    }
    .sorting_1{
        text-align:center !important;
    }
</style>
@endsection
@section('mainContent')
    @if(isModuleActive('FrontendMultiLang'))
        @php
            $LanguageList = getLanguageList();
        @endphp
    @endif
<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid p-0">
        <div class="row">
                <div class="col-lg-8">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="main-title">
                                <h3 class="mb-30">Entry Amount</h3>
                            </div>
                        </div>
                    </div>

                </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                  <div class="box_header common_table_header ">
                        <div class="main-title d-md-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __("auctionproduct.all_payments") }}</h3>
                        </div>
                    </div>
                    <div class="QA_section QA_section_heading_custom check_box_table">
                        <table class="table" id="allpayments">
                            <thead>
                                <tr>
                                    <th>{{__('common.sl')}}</th>
                                    <th>{{ __('common.customer') }}</th>
                                    <th>{{ __('auctionproduct.title') }}</th>
                                    <th>{{ __('auctionproduct.amount') }}</th>
                                    <th>{{ __('auctionproduct.payment_method') }}</th>
                                    <th>{{ __('auctionproduct.status') }}</th>
                                    <th>{{ __("common.action") }}</th>
                                </tr>
                            </thead>

                        </table>

                    </div>
            </div>
        </div>
    </div>
</section>

<div id="details_id"></div>


@endsection
@push('scripts')
    <script>
        $(document).ready(function(){
            $(document).on('click','.details',function(event){
                event.preventDefault();
                let url = $(this).attr('href');
                $.ajax({
                    url:url,
                    method : "get",
                }).done(function(response){
                    if(response.status == 1)
                    {
                        $("#details_id").html(response.html);
                        $("#detailsModal").modal('show');
                    }
                });
            });
            $('#allpayments').DataTable({
                    processing: true,
                    serverSide: true,
                    stateSave: true,
                    "ajax": ( {
                        url: "{{ route('auctionproducts.entryAmountData') }}"
                    }),
                    "initComplete":function(json){

                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'id' ,render:function(data){
                            return numbertrans(data)
                        }},
                        { data: 'customer', name: 'customer.last_name' },
                        { data: 'auction', name: 'auction.auction_title' },
                        { data: 'amount', name: 'amount' },
                        { data: 'paymentMethod', name: 'paymentMethod.method' },
                        { data: 'status', name: 'status' },
                        { data: 'action', name: 'action' }

                    ],

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
                            pageSize: 'A4',
                            margin: [0, 0, 0, 0],
                            alignment: 'center',
                            header: true,

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
        });
    </script>
@endpush
