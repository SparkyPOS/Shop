@push('scripts')
<script type="text/javascript">
(function($){
        "use strict";
        var Table = '';
    $(document).ready(function(){
            var columnData = [
                { data: 'DT_RowIndex', name: 'id' },
                { data: 'product_name', name: 'product_name' },
                { data: 'bid_starting_amount', name: 'auction_products' },
                { data: 'auction_start_date', name: 'auction_start_date' },
                { data: 'auction_end_date', name: 'auction_end_date' },
                { data: 'total_bids', name: 'total_bids' },
                { data: 'stock', name: 'stock' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action' },
            ]
            inhouseAuctionProductDataTable();

            function inhouseAuctionProductDataTable(){
            Table = $('#inhousAProductDataTable').DataTable({
                processing: true,
                serverSide: true,
                "stateSave": true,
                "ajax": $.fn.dataTable.pipeline({
                    url: '{{route('auctionproducts.auction-product-inhouse-getData')}}',
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
    
   
})(jQuery);
</script>
@endpush
