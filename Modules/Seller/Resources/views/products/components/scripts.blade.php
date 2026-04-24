@push('scripts')
<script type="text/javascript">
    (function($){
        "use strict";
        $(document).ready(function(){
            productDatatable();
            mainProductList();
            alertProductDatatable();
            stockOutProductDatatable();
            disableProductDatatable();
            initBulkProductSync();
        $(document).on('submit', '#item_delete_form', function(event) {
            event.preventDefault();
            $('#pre-loader').removeClass('d-none');
            $('#deleteItemModal').modal('hide');
            var formData = new FormData();
            formData.append('_token', "{{ csrf_token() }}");
            formData.append('id', $('#delete_item_id').val());
            let id = $('#delete_item_id').val();
            $.ajax({
                url: "{{ route('seller.product.delete') }}",
                type: "POST",
                cache: false,
                contentType: false,
                processData: false,
                data: formData,
                success: function(response) {
                    if(response.msg){
                        toastr.warning(response.msg);
                        $("#pre-loader").addClass('d-none');
                    }else{
                        resetAfterChange(response);
                        toastr.success("{{__('common.deleted_successfully')}}","{{__('common.success')}}")
                        $('#pre-loader').addClass('d-none');
                    }
                },
                error: function(response) {
                if(response.responseJSON.error){
                    toastr.error(response.responseJSON.error ,"{{__('common.error')}}");
                    $('#pre-loader').addClass('d-none');
                    return false;
                }
                    toastr.error("{{__('common.error_message')}}","{{__('common.error')}}");
                }
            });
        });
        $(document).on('submit', '#product_delete_form', function(event) {
            event.preventDefault();
            $('#product_delete_modal').modal('hide');
            $('#pre-loader').removeClass('d-none');
            var formData = new FormData();
            formData.append('_token', "{{ csrf_token() }}");
            formData.append('id', $('#product_delete_id').val());
            $.ajax({
                url: "{{ route('product.destroy') }}",
                type: "POST",
                cache: false,
                contentType: false,
                processData: false,
                data: formData,
                success: function(response) {
                    mainProductList();
                    toastr.success("{{__('common.deleted_successfully')}}", "{{__('common.success')}}");
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

        $(document).on('click', '.product_detail', function(event){
            event.preventDefault();
            let id = $(this).data('id');
            $('#pre-loader').removeClass('d-none');
            $.post('{{ route('product.show') }}', {_token:'{{ csrf_token() }}', id:id}, function(data){
                $('#product_detail_view_div').html(data);
                $('#productDetails').modal('show');
                $('#pre-loader').addClass('d-none');
            });
        });
        $(document).on('change', '.sku_status_change', function(event){
            let id = $(this).val();
            let status = 0;
            if($(this).prop('checked')){
                status = 1;
            }
            else{
                status = 0;
            }
            var formData = new FormData();
            formData.append('_token', "{{ csrf_token() }}");
            formData.append('id', id);
            formData.append('status', status);
            $.ajax({
                url: "{{ route('seller.product.sku.status') }}",
                type: "POST",
                cache: false,
                contentType: false,
                processData: false,
                data: formData,
                success: function(response) {
                    resetAfterChange(response);
                    toastr.success("{{__('common.updated_successfully')}}", "{{__('common.success')}}");
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
            $(document).on('click', '.seller_product_delete', function(event){
                event.preventDefault();
                let id = $(this).data('id');
                $('#delete_item_id').val(id);
                $('#deleteItemModal').modal('show');
            });
            $(document).on('click', '.seller_product_view', function(event){
                event.preventDefault();
                let id = $(this).data('id');
                seller_product_show(id);
            });
            $(document).on('change', '.product_status_change', function(){
                update_active_status($(this)[0]);
            });
            function seller_product_show(el){
                $.post('{{ route('seller.admin_product.show') }}', {_token:'{{ csrf_token() }}', id:el}, function(data){
                    $('#product_detail_view_div').empty();
                    $('#product_detail_view_div').html(data);
                    $('#productDetails').modal('show');
                });
            }
            function productDatatable(){
                $('#sellerProductTable').DataTable({
                    processing: true,
                    serverSide: true,
                    stateSave: true,
                    "ajax": ( {
                        url: "{{route('seller.product.get-data')}}"
                    }),
                    "initComplete":function(json){
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'id',render:function(data){
                            return numbertrans(data)
                        }},
                        { data: 'product_name', name: 'product_name' },
                        { data: 'brand', name: 'brand',searchable:false,orderable:false},
                        { data: 'logo', name: 'logo' },
                        { data: 'stock', name: 'stock' },
                        { data: 'status', name: 'status' },
                        { data: 'action', name: 'action',searchable:false,orderable:false}
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
            }
            function alertProductDatatable(){
                var base_url = $('#url').val();
                var url = "{{route('seller.product.get-data')}}" + '?table=alert';
                $('#alertProductTable').DataTable({
                    processing: true,
                    serverSide: true,
                    stateSave: true,
                    "ajax": ( {
                        url: url
                    }),
                    "initComplete":function(json){
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'id',render:function(data){
                            return numbertrans(data)
                        }},
                        { data: 'product_name', name: 'product_name' },
                        { data: 'brand', name: 'brand',searchable:false,orderable:false},
                        { data: 'logo', name: 'logo' },
                        { data: 'stock', name: 'stock' },
                        { data: 'status', name: 'status' },
                        { data: 'action', name: 'action',searchable:false,orderable:false}
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
            }
            function stockOutProductDatatable(){
                var base_url = $('#url').val();
                var url = "{{route('seller.product.get-data')}}" + '?table=stockout';
                $('#stockoutProductTable').DataTable({
                    processing: true,
                    serverSide: true,
                    stateSave: true,
                    "ajax": ( {
                        url: url
                    }),
                    "initComplete":function(json){
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'id',render:function(data){
                            return numbertrans(data)
                        }},
                        { data: 'product_name', name: 'product_name' },
                        { data: 'brand', name: 'brand',searchable:false,orderable:false },
                        { data: 'logo', name: 'logo' },
                        { data: 'stock', name: 'stock' },
                        { data: 'status', name: 'status' },
                        { data: 'action', name: 'action',searchable:false,orderable:false }
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
            }
            function disableProductDatatable(){
                var base_url = $('#url').val();
                var url = "{{route('seller.product.get-data')}}" + '?table=disable';
                $('#disableProductTable').DataTable({
                    processing: true,
                    serverSide: true,
                    stateSave: true,
                    "ajax": ( {
                        url: url
                    }),
                    "initComplete":function(json){
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'id',render:function(data){
                            return numbertrans(data)
                        }},
                        { data: 'product_name', name: 'product_name' },
                        { data: 'brand', name: 'brand',searchable:false,orderable:false },
                        { data: 'logo', name: 'logo' },
                        { data: 'stock', name: 'stock' },
                        { data: 'status', name: 'status' },
                        { data: 'action', name: 'action',searchable:false,orderable:false }
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
            }
            function mainProductList(){
                $('#mainProductTable').DataTable({
                    processing: true,
                    serverSide: true,
                    stateSave: true,
                    "ajax": ( {
                        url: "{{route('product.get-data')}}"
                    }),
                    "initComplete":function(json){
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'id',render:function(data){
                            return numbertrans(data)
                        }},
                        { data: 'product_name', name: 'product_name' },
                        { data: 'product_type', name: 'product_type' },
                        { data: 'brand', name: 'brand',searchable:false,orderable:false },
                        { data: 'logo', name: 'logo' },
                        { data: 'status', name: 'status' },
                        { data: 'action', name: 'action',searchable:false,orderable:false }

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
            }

            let syncPollTimer = null;
            const syncRoutes = {
                start: "{{ route('seller.product.sync.start') }}",
                status: "{{ route('seller.product.sync.status') }}",
                cancel: "{{ route('seller.product.sync.cancel') }}"
            };

            function initBulkProductSync() {
                if (!$('#shop_product_sync_btn').length) {
                    return;
                }

                $(document).on('click', '#shop_product_sync_btn', function (event) {
                    event.preventDefault();
                    startBulkProductSync();
                });

                $(document).on('click', '#shop_product_sync_cancel_btn', function (event) {
                    event.preventDefault();
                    cancelBulkProductSync();
                });

                fetchBulkSyncStatus(false);
            }

            function startBulkProductSync() {
                const state = ($('#shop_product_sync_btn').data('state') || 'idle').toString();
                if (state === 'running' || state === 'cancelling') {
                    return;
                }

                const formData = new FormData();
                formData.append('_token', "{{ csrf_token() }}");

                $('#shop_product_sync_btn').prop('disabled', true);
                $.ajax({
                    url: syncRoutes.start,
                    type: 'POST',
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: formData,
                    success: function (response) {
                        if (!response || response.success === false) {
                            toastr.error((response && response.message) ? response.message : "{{ __('common.error_message') }}", "{{ __('common.error') }}");
                            $('#shop_product_sync_btn').prop('disabled', false);
                            return;
                        }

                        renderBulkSyncState(response.data || null);
                        if (response.message) {
                            toastr.success(response.message, "{{ __('common.success') }}");
                        }
                        ensureBulkSyncPolling();
                    },
                    error: function (response) {
                        const message = (response && response.responseJSON && response.responseJSON.message)
                            ? response.responseJSON.message
                            : "{{ __('common.error_message') }}";
                        toastr.error(message, "{{ __('common.error') }}");
                        $('#shop_product_sync_btn').prop('disabled', false);
                    }
                });
            }

            function cancelBulkProductSync() {
                const formData = new FormData();
                formData.append('_token', "{{ csrf_token() }}");

                $('#shop_product_sync_cancel_btn').prop('disabled', true).text('Cancelling...');
                $.ajax({
                    url: syncRoutes.cancel,
                    type: 'POST',
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: formData,
                    success: function (response) {
                        if (!response || response.success === false) {
                            toastr.error((response && response.message) ? response.message : "{{ __('common.error_message') }}", "{{ __('common.error') }}");
                        } else {
                            renderBulkSyncState(response.data || null);
                            toastr.success(response.message || 'Sync cancelled.', "{{ __('common.success') }}");
                        }

                        ensureBulkSyncPolling();
                    },
                    error: function (response) {
                        const message = (response && response.responseJSON && response.responseJSON.message)
                            ? response.responseJSON.message
                            : "{{ __('common.error_message') }}";
                        toastr.error(message, "{{ __('common.error') }}");
                        $('#shop_product_sync_cancel_btn').prop('disabled', false).text('Cancel');
                    }
                });
            }

            function fetchBulkSyncStatus(withLoader) {
                $.ajax({
                    url: syncRoutes.status,
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        if (!response || response.success === false) {
                            if (withLoader) {
                                stopBulkSyncPolling();
                            }
                            return;
                        }

                        renderBulkSyncState(response.data || null);
                        const currentState = response && response.data ? response.data.state : null;
                        if (isSyncActive(currentState)) {
                            ensureBulkSyncPolling();
                        } else {
                            stopBulkSyncPolling();
                        }
                    },
                    error: function () {
                        if (withLoader) {
                            stopBulkSyncPolling();
                        }
                    }
                });
            }

            function ensureBulkSyncPolling() {
                if (syncPollTimer) {
                    return;
                }

                syncPollTimer = setInterval(function () {
                    fetchBulkSyncStatus(true);
                }, 2000);
            }

            function stopBulkSyncPolling() {
                if (!syncPollTimer) {
                    return;
                }
                clearInterval(syncPollTimer);
                syncPollTimer = null;
            }

            function isSyncActive(state) {
                return state === 'running' || state === 'cancelling';
            }

            function renderBulkSyncState(state) {
                const syncState = state && state.state ? state : { state: 'idle', total: 0, succeeded: 0, remaining: 0 };
                const stateName = (syncState.state || 'idle').toString();
                const total = Number(syncState.total || 0);
                const succeeded = Number(syncState.succeeded || 0);
                const failed = Number(syncState.failed || 0);
                const remaining = Number(syncState.remaining || Math.max(0, total - (syncState.processed || 0)));
                const processed = Number(syncState.processed || 0);

                const $control = $('#shop_product_sync_control');
                const $btn = $('#shop_product_sync_btn');
                const $btnText = $('#shop_product_sync_btn_text');
                const $badge = $('#shop_product_sync_badge');
                const $cancel = $('#shop_product_sync_cancel_btn');

                $btn.data('state', stateName);

                if (stateName === 'running') {
                    $btnText.text('Syncing Products...');
                    $btn.prop('disabled', true);
                    $control.addClass('is-running').removeClass('is-cancelling');
                    $cancel.prop('disabled', false).text('Cancel');
                } else if (stateName === 'cancelling') {
                    $btnText.text('Cancelling...');
                    $btn.prop('disabled', true);
                    $control.addClass('is-running is-cancelling');
                    $cancel.prop('disabled', true).text('Cancelling...');
                } else {
                    $btnText.text('Sync Products');
                    $btn.prop('disabled', false);
                    $control.removeClass('is-running is-cancelling');
                    $cancel.prop('disabled', false).text('Cancel');
                }

                if (total > 0 || processed > 0 || stateName === 'completed' || stateName === 'cancelled' || stateName === 'failed') {
                    const badgeText = 'Synced ' + succeeded + ' | Remaining ' + remaining + ' / ' + total + (failed > 0 ? (' | Failed ' + failed) : '');
                    $badge.text(badgeText).removeClass('d-none');
                } else {
                    $badge.addClass('d-none').text('');
                }
            }

            function resetAfterChange(response) {
                $('#product_list_div').html(response.ProductList);
                $('#alert_div').html(response.AlertList);
                $('#stock_div').html(response.StockList);
                $('#disabled_div').html(response.DisabledList);
                productDatatable();
                mainProductList();
                alertProductDatatable();
                stockOutProductDatatable();
                disableProductDatatable();
            }
            function update_active_status(el){
                if(el.checked){
                    var status = 1;
                }
                else{
                    var status = 0;
                }
                var formData = new FormData();
                formData.append('_token', "{{ csrf_token() }}");
                formData.append('id', el.value);
                formData.append('status', status);
                $.ajax({
                    url: "{{ route('seller.product.update-status') }}",
                    type: "POST",
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: formData,
                    success: function(response) {
                        resetAfterChange(response);
                        toastr.success("{{__('common.updated_successfully')}}","{{__('common.success')}}")
                    },
                    error: function(response) {
                    if(response.responseJSON.error){
                        toastr.error(response.responseJSON.error ,"{{__('common.error')}}");
                        $('#pre-loader').addClass('d-none');
                        return false;
                    }
                        toastr.error("{{__('common.error_message')}}","{{__('common.error')}}");
                    }
                });
            }
            $(document).on('click', '.delete_product', function(event){
                event.preventDefault();
                let id = $(this).data('id');
                $('#product_delete_id').val(id);
                $('#product_delete_modal').modal('show');
            });
        });
    })(jQuery);
</script>
@endpush
