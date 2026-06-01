@extends('layouts.main', [
'title' => $title,
'pageTitle' => $title,
'firstSegment' => 'Report',
'secondSegment' => $title,
])

@push('style')
<link rel="stylesheet" type="text/css"
    href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
<link rel="stylesheet" type="text/css"
    href="{{ asset('assets/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
<link rel="stylesheet" type="text/css"
    href="{{ asset('assets/libs/datatables.net-keytable-bs5/css/keyTable.bootstrap5.min.css') }}">
<link rel="stylesheet" type="text/css"
    href="{{ asset('assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
<link rel="stylesheet" type="text/css"
    href="{{ asset('assets/libs/datatables.net-select-bs5/css/select.bootstrap5.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
<link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">
<link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
<style>
    /* Override DataTables responsive plugin to prevent horizontal scroll */
    .dataTables_wrapper {
        width: 100% !important;
        overflow: visible !important;
    }

    .table-responsive {
        overflow-x: visible !important;
        overflow-y: auto !important;
        max-height: 70vh !important;
    }

    /* Disable DataTables responsive (hide child rows) */
    table.dt-responsive tbody>tr>td.child {
        display: none !important;
    }

    /* Force table cells to not wrap based on responsive plugin */
    table.table-order {
        width: 100% !important;
        table-layout: auto !important;
    }

    /* Allow table cells to wrap and adjust height automatically */
    table.table-order td,
    table.table-order th {
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        white-space: normal !important;
        max-width: none !important;
    }

    /* Cost detail: display as multiline vertical list */
    table.table-order td .cost-detail-list {
        display: block !important;
        line-height: 1.6 !important;
    }

    table.table-order td .cost-detail-item {
        display: block !important;
        margin-bottom: 4px !important;
    }
</style>
@endpush

@section('content')
<div class="col-sm-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>{{ __('menu_order_detail.order_detail_report') }}</h4>

            <div class="d-flex align-items-center gap-3">
                <div class="accordion-item ">
                    <a href="#" class="btn btn-icon btn-sm bg-dark-subtle" data-bs-toggle="collapse"
                        data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        <i class="mdi mdi-magnify fs-14 text-dark"></i>
                    </a>
                </div>

                <a href="{{ route($view . 'excel-order-detail') }}" target="_blank" id="export-excel"
                    class="btn btn-sm btn-success">
                    <i class="mdi mdi-file-excel"></i> Export Excel
                </a>

                <a href="{{ route($view . 'pdf-order-detail') }}" target="_blank" id="export-pdf"
                    class="btn btn-sm btn-danger">
                    <i class="mdi mdi-file-pdf"></i> Export PDF
                </a>
            </div>
        </div>

        <div class="card-header">
            <div class="accordion-collapse collapse" id="collapseTwo" aria-labelledby="headingTwo"
                data-bs-parent="#simpleaccordion">
                <div class="accordion-body col-md-12">
                    <form id="filterForm" class=" g-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label" for="name">{{ __('menu_order.plate_number') }}</label>
                                <select class="js-example-basic-single" name="plateNumber" id="plateNumber">
                                    <option selected="" value="">{{ __('general.choose') }}...</option>
                                    @foreach ($fleet as $item)
                                    <option value="{{ $item->plateNumber }}">
                                        {{ $item->plateNumber }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="name">{{ __('menu_order.driver') }}</label>
                                <select class="js-example-basic-single" name="driverName" id="driverName">
                                    <option selected="" value="">{{ __('general.choose') }}...</option>
                                    @foreach ($driver as $item)
                                    <option value="{{ $item->name }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <label class="form-label" for="name">{{ __('menu_order.customer') }}</label>
                                <select class="js-example-basic-single" name="customerName" id="customerName">
                                    <option selected="" value="">{{ __('general.choose') }}...</option>
                                    @foreach ($customer as $item)
                                    <option value="{{ $item->name }}">{{ $item->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="name">{{ __('menu_order.fleet_type') }}</label>
                                <select class="js-example-basic-single" name="fleetTypeName" id="fleetTypeName">
                                    <option selected="" value="">{{ __('general.choose') }}...</option>
                                    @foreach ($fleetType as $item)
                                    <option value="{{ $item->name }}">{{ $item->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <label class="form-label" for="name">{{ __('menu_order.shipment_no') }}</label>
                                <input class="form-control" name="shipmentNumber" type="text"
                                    placeholder="{{ __('menu_order.shipment_no') }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label" for="name">{{ __('menu_order.order_date') }}</label>
                                <input class="form-control" name="startDate" id="datetime-local" type="date"
                                    placeholder="{{ __('menu_order_detail.start_date') }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label" for="name"></label>
                                <input class="form-control" name="endDate" id="datetime-local" type="date"
                                    placeholder="{{ __('menu_order_detail.end_date') }}">
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <label class="form-label" for="name">{{ __('menu_order.origin') }}</label>
                                <select class="js-example-basic-single" name="origin" id="origin">
                                    <option selected="" value="">{{ __('general.choose') }}...</option>
                                    @foreach ($location as $item)
                                    <option value="{{ $item->name }}">{{ $item->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="name">{{ __('menu_order.destination') }}</label>
                                <select class="js-example-basic-single" name="destination" id="destination">
                                    <option selected="" value="">{{ __('general.choose') }}...</option>
                                    @foreach ($location as $item)
                                    <option value="{{ $item->name }}">{{ $item->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <button class="btn btn-primary" type="submit">{{ __('menu_order_detail.filter') }}</button>
                            <button type="button" id="resetFilter" class="btn btn-secondary">{{ __('general.reset') }}</button>
                        </div>

                    </form>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body">
            @include('partials.alert')
            <div class="table-responsive ">
                <table class="table table-order table-bordered nowrap" id="dt">
                    <thead>
                        <tr>
                            <th>{{ __('menu_order_detail.no') }}</th>
                            <th>{{ __('menu_order.shipment_no') }}</th>
                            <th>{{ __('menu_order.order_date') }}</th>
                            <th>{{ __('menu_order.customer') }}</th>
                            <th>{{ __('menu_order.origin') }}</th>
                            <th>{{ __('menu_order.destination') }}</th>
                            <th>{{ __('menu_order.plate_number') }}</th>
                            <th>{{ __('menu_order.driver') }}</th>
                            <th>{{ __('menu_order_detail.sales') }}</th>
                            <th>{{ __('menu_order_detail.income') }}</th>
                            <th>{{ __('menu_order_detail.cost_detail') }}</th>
                            <th>{{ __('menu_order_detail.total_cost') }}</th>
                            <th>{{ __('menu_order_detail.profit') }}</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
    </div>
    
    {{-- Elegant Fullscreen Loader for Export --}}
    <div id="exportLoader" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(8px); z-index: 99999; align-items: center; justify-content: center; transition: all 0.3s ease;">
        <div class="text-center p-5" style="background: #ffffff; border-radius: 20px; box-shadow: 0 20px 50px rgba(0,0,0,0.3); max-width: 420px; width: 90%; border: 1px solid rgba(255,255,255,0.1);">
            <div class="d-inline-block position-relative mb-4">
                <div class="spinner-border text-success" role="status" style="width: 4.5rem; height: 4.5rem; border-width: 0.35em;"></div>
                <div class="position-absolute top-50 start-50 translate-middle">
                    <i class="mdi mdi-file-excel text-success" style="font-size: 32px; line-height: 1;"></i>
                </div>
            </div>
            <h4 class="fw-bold text-dark mb-2">Mengunduh Laporan Excel</h4>
            <p class="text-muted mb-4" style="font-size: 13.5px; line-height: 1.6;">Mohon tunggu, server sedang mengompilasi dan menstrukturkan baris-baris detail biaya Anda...</p>
            
            <div class="progress mb-3" style="height: 8px; border-radius: 10px; background-color: #f1f5f9; overflow: hidden;">
                <div id="exportProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%; transition: width 0.4s ease;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="d-flex justify-content-between text-muted" style="font-size: 12px; font-weight: 600;">
                <span id="exportStatusText">Preparing data...</span>
                <span id="exportPercentText">0%</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-keytable-bs5/js/keyTable.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-select-bs5/js/select.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
<script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
<script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
<script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>
<script>
    $(document).ready(function() {
        const table = $('#dt').DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "ajax": {
                "url": "{{ route('dt.order-detail') }}",
                "data": function(d) {
                    d.plateNumber = $('select[name="plateNumber"]').val();
                    d.customerName = $('select[name="customerName"]').val();
                    d.driverName = $('select[name="driverName"]').val();
                    d.fleetTypeName = $('select[name="fleetTypeName"]').val();
                    d.shipmentNumber = $('input[name="shipmentNumber"]').val();
                    d.startDate = $('input[name="startDate"]').val();
                    d.endDate = $('input[name="endDate"]').val();
                    d.origin = $('select[name="origin"]').val();
                    d.destination = $('select[name="destination"]').val();
                    d.orderTypeCode = $('select[name="orderTypeCode"]').val();
                }
            },
            "columns": [{
                    "data": 'DT_RowIndex'
                },
                {
                    "data": "shipmentNumber"
                },
                {
                    "data": 'orderDate'
                },
                {
                    "data": 'customer_name'
                },
                {
                    "data": 'route.originLocation.name'
                },
                {
                    "data": 'route.destinationLocation.name'
                },
                {
                    "data": 'fleet.plateNumber'
                },
                {
                    "data": 'driver.name'
                },
                {
                    "data": 'sales'
                },
                {
                    "data": 'income'
                },
                {
                    "data": 'cost_detail'
                },
                {
                    "data": "total_cost"
                },
                {
                    "data": 'profit'
                }
            ],
            "columnDefs": [{
                    "searchable": false,
                    "targets": [0]
                },
                {
                    "orderable": false,
                    "targets": [0]
                }
            ],
            "order": [
                [2, 'desc']
            ]
        })

        // Event untuk form filter
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            let queryParams = $(this).serialize();

            let exportExcelUrl = "{{ route($view . 'excel-order-detail') }}" + "?" + queryParams;
            let exportPdfUrl = "{{ route($view . 'pdf-order-detail') }}" + "?" + queryParams;

            $('#export-excel').attr('href', exportExcelUrl);
            $('#export-pdf').attr('href', exportPdfUrl);

            table.ajax.reload();
        });

        // Reset filter button
        $('#resetFilter').on('click', function() {
            // Clear plain inputs
            $('input[name="shipmentNumber"]').val('');
            $('input[name="startDate"]').val('');
            $('input[name="endDate"]').val('');

            // Clear select2/select fields and trigger change for select2
            var selects = ['plateNumber', 'driverName', 'customerName', 'fleetTypeName', 'origin', 'destination', 'orderTypeCode'];
            selects.forEach(function(name) {
                var el = $('select[name="' + name + '"]');
                if (el.length) {
                    el.val('');
                    try {
                        el.trigger('change');
                    } catch (e) {}
                }
            });

            // Reset export links to base (no query)
            $('#export-excel').attr('href', "{{ route($view . 'excel-order-detail') }}");
            $('#export-pdf').attr('href', "{{ route($view . 'pdf-order-detail') }}");

            table.ajax.reload();
        });

        // ============================================================
        // Excel Export Progress Loader Handler (Chunk-based with Cookie validation)
        // ============================================================
        $('#export-excel').on('click', function(e) {
            e.preventDefault();
            
            const btn = $(this);
            const href = btn.attr('href');
            if (!href || href === '#') return;
            
            // Clean up any stale cookies from previous downloads first
            expireCookie('download_token');
            
            // Extract the filters query parameters
            const queryParams = href.includes('?') ? href.split('?')[1] : '';
            
            // Show Loader and Reset progress
            $('#exportProgressBar').css('width', '0%');
            $('#exportPercentText').text('0%');
            $('#exportStatusText').text('Menginisialisasi ekspor...');
            $('#exportLoader').css('display', 'flex').hide().fadeIn(200);
            
            // Step 1: Initialize Export Chunking on Server
            const initUrl = "{{ route('report.order-detail.excel-order-detail-init') }}" + (queryParams ? '?' + queryParams : '');
            
            $.ajax({
                url: initUrl,
                method: 'GET',
                success: function(response) {
                    const downloadId = response.download_id;
                    const total = response.total;
                    
                    if (total === 0) {
                        $('#exportLoader').fadeOut(200);
                        swal('Informasi', 'Tidak ada data order yang cocok dengan filter untuk diekspor.', 'info');
                        return;
                    }
                    
                    $('#exportStatusText').text('Menemukan ' + total + ' baris data. Memulai pemrosesan...');
                    
                    // Step 2: Sequential Chunk Requests
                    const chunkSize = 100; // Batch size
                    let offset = 0;
                    
                    function fetchNextChunk() {
                        if (offset < total) {
                            // Phase 1: Database chunk loading represents 0% to 85% of progress
                            const percent = Math.round((offset / total) * 85);
                            $('#exportProgressBar').css('width', percent + '%');
                            $('#exportPercentText').text(percent + '%');
                            $('#exportStatusText').text('Memuat data ' + offset + ' sampai ' + Math.min(offset + chunkSize, total) + ' dari ' + total + '...');
                            
                            const chunkUrl = "{{ route('report.order-detail.excel-order-detail-chunk') }}?" + 
                                (queryParams ? queryParams + '&' : '') + 
                                'download_id=' + downloadId + 
                                '&offset=' + offset + 
                                '&limit=' + chunkSize;
                                
                            $.ajax({
                                url: chunkUrl,
                                method: 'GET',
                                success: function() {
                                    offset += chunkSize;
                                    fetchNextChunk();
                                },
                                error: function() {
                                    $('#exportLoader').fadeOut(200);
                                    swal('Error', 'Gagal memproses ekspor di baris ' + offset + '. Silakan coba lagi.', 'error');
                                }
                            });
                        } else {
                            // Completed chunk loading! Transition to Excel compiling phase (90%)
                            $('#exportProgressBar').css('width', '90%');
                            $('#exportPercentText').text('90%');
                            $('#exportStatusText').text('Data berhasil dimuat. Sedang menyusun lembar kerja Excel & merender sel...');
                            
                            // Start slow visual tick to keep progress alive during heavy PhpSpreadsheet compile
                            let compileProgress = 90;
                            const compileInterval = setInterval(function() {
                                if (compileProgress < 98) {
                                    compileProgress++;
                                    $('#exportProgressBar').css('width', compileProgress + '%');
                                    $('#exportPercentText').text(compileProgress + '%');
                                }
                            }, 1200);
                            
                            // Generate unique download token for the final file stream
                            const downloadToken = 'dl_' + new Date().getTime();
                            const downloadUrl = "{{ route('report.order-detail.excel-order-detail-download') }}?download_id=" + downloadId + "&download_token=" + downloadToken;
                            
                            // Use Fetch API to download the compiled Excel file as a Blob.
                            // This guarantees 100% real-time completion detection across all modern browsers,
                            // bypassing iframe sandboxing, cookie encryption, and SameSite policy limitations.
                            fetch(downloadUrl)
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error('Gagal menyusun atau mengunduh file Excel.');
                                    }
                                    // Get content-disposition for the exact filename
                                    const disposition = response.headers.get('content-disposition');
                                    let filename = 'Order-Detail-Report.xlsx';
                                    if (disposition && disposition.indexOf('attachment') !== -1) {
                                        const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                                        const matches = filenameRegex.exec(disposition);
                                        if (matches != null && matches[1]) { 
                                            filename = matches[1].replace(/['"]/g, '');
                                        }
                                    }
                                    return response.blob().then(blob => ({ blob, filename }));
                                })
                                .then(({ blob, filename }) => {
                                    clearInterval(compileInterval);
                                    
                                    // Excel compiled successfully and streamed! Set to 100%!
                                    $('#exportProgressBar').css('width', '100%');
                                    $('#exportPercentText').text('100%');
                                    $('#exportStatusText').text('Sukses! Pengunduhan file dimulai.');
                                    
                                    // Trigger native browser download using Blob URL
                                    const url = window.URL.createObjectURL(blob);
                                    const a = document.createElement('a');
                                    a.style.display = 'none';
                                    a.href = url;
                                    a.download = filename;
                                    document.body.appendChild(a);
                                    a.click();
                                    
                                    // Cleanup DOM and revoke blob URL
                                    setTimeout(() => {
                                        document.body.removeChild(a);
                                        window.URL.revokeObjectURL(url);
                                    }, 100);
                                    
                                    // Fade out loader after success message is read
                                    setTimeout(function() {
                                        $('#exportLoader').fadeOut(300);
                                    }, 1500);
                                })
                                .catch(error => {
                                    clearInterval(compileInterval);
                                    $('#exportLoader').fadeOut(200);
                                    swal('Error', 'Gagal memproses dan mengunduh Excel. Silakan coba lagi.', 'error');
                                });
                        }
                    }
                    
                    // Start chunk fetching
                    fetchNextChunk();
                },
                error: function() {
                    $('#exportLoader').fadeOut(200);
                    swal('Error', 'Gagal menginisialisasi ekspor. Silakan hubungi admin.', 'error');
                }
            });
        });

        // Helper functions for Cookies
        function getCookie(name) {
            const parts = ('; ' + document.cookie).split('; ' + name + '=');
            if (parts.length === 2) return parts.pop().split(';').shift();
        }
        
        function expireCookie(name) {
            document.cookie = name + '=; Max-Age=-99999999; path=/;';
        }
    });
</script>
@endpush