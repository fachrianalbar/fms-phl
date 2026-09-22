@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => __('general.add'),
])

@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">

    <style>
        /* ===== Custom Card & Header ===== */
        .maintenance-card {
            border: 1px solid #e8ecf3;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(30, 41, 59, 0.04);
            background: #fff;
            overflow: hidden;
            transition: box-shadow 0.2s ease;
        }
        .maintenance-card:hover {
            box-shadow: 0 4px 16px rgba(30, 41, 59, 0.07);
        }
        .maintenance-card .card-header {
            background: #fbfcfe;
            border-bottom: 1px solid #edf1f7;
            padding: 1rem 1.4rem;
        }
        .maintenance-card .card-body {
            padding: 1.4rem;
        }

        .header-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        .header-icon-primary {
            background: linear-gradient(135deg, #7669D3 0%, #5a4db8 100%);
            color: #fff;
            box-shadow: 0 3px 8px rgba(118, 105, 211, 0.3);
        }
        .header-icon-info {
            background: linear-gradient(135deg, #38bdf8 0%, #0284c7 100%);
            color: #fff;
            box-shadow: 0 3px 8px rgba(2, 132, 199, 0.25);
        }

        /* ===== Form Controls & Labels ===== */
        .form-label-custom {
            font-size: 0.78rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.4rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        .form-label-custom .required-star {
            color: #ef4444;
            font-weight: bold;
        }

        .form-control-custom {
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.52rem 0.85rem;
            font-size: 0.9rem;
            color: #1e293b;
            background: #fafbfc;
            transition: all 0.2s ease;
        }
        .form-control-custom:focus {
            border-color: #7669D3;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(118, 105, 211, 0.14);
        }
        .form-control-custom:read-only,
        .form-control-custom:disabled {
            background: #f1f5f9;
            color: #64748b;
            border-color: #e2e8f0;
        }

        .input-group-custom .input-group-text {
            border: 1.5px solid #e2e8f0;
            border-right: none;
            background: #f8fafc;
            color: #64748b;
            border-radius: 8px 0 0 8px;
            padding: 0 0.75rem;
        }
        .input-group-custom .form-control {
            border-radius: 0 8px 8px 0 !important;
        }

        /* ===== Select2 Modern Customization ===== */
        .select2-container .select2-selection--single {
            height: 40px !important;
            border: 1.5px solid #e2e8f0 !important;
            border-radius: 8px !important;
            padding: 0.45rem 0.75rem !important;
            background: #fafbfc !important;
            transition: all 0.2s ease;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 24px !important;
            color: #1e293b !important;
            font-size: 0.9rem;
            padding-left: 0 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
            right: 8px !important;
        }
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #7669D3 !important;
            box-shadow: 0 0 0 3px rgba(118, 105, 211, 0.14) !important;
            background: #fff !important;
        }

        /* Select2 Multi-Select for PO */
        .select2-container .select2-selection--multiple {
            min-height: 42px !important;
            border: 1.5px solid #e2e8f0 !important;
            border-radius: 8px !important;
            padding: 0.25rem 0.5rem !important;
            background: #fafbfc !important;
            transition: all 0.2s ease;
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple,
        .select2-container--default.select2-container--open .select2-selection--multiple {
            border-color: #7669D3 !important;
            box-shadow: 0 0 0 3px rgba(118, 105, 211, 0.14) !important;
            background: #fff !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #eef2ff !important;
            border: 1px solid #c7d2fe !important;
            color: #4338ca !important;
            border-radius: 6px !important;
            font-size: 0.82rem !important;
            font-weight: 600 !important;
            padding: 2px 8px !important;
            margin-top: 3px !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #6366f1 !important;
            margin-right: 5px !important;
            font-weight: bold;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #dc2626 !important;
        }

        /* ===== Modern Table Styling ===== */
        .maintenance-table-wrapper {
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e8ecf3;
            background: #fff;
        }
        .maintenance-table {
            margin-bottom: 0;
            width: 100%;
            min-width: 1080px;
            table-layout: fixed !important;
            border-collapse: collapse;
        }

        /* Fixed static column widths */
        .col-w-no { width: 45px !important; min-width: 45px !important; max-width: 45px !important; }
        .col-w-item { width: 340px !important; min-width: 340px !important; max-width: 340px !important; }
        .col-w-desc { width: 210px !important; min-width: 180px !important; }
        .col-w-stock { width: 115px !important; min-width: 115px !important; max-width: 120px !important; }
        .col-w-qty { width: 95px !important; min-width: 95px !important; max-width: 100px !important; }
        .col-w-price { width: 125px !important; min-width: 125px !important; max-width: 135px !important; }
        .col-w-total { width: 135px !important; min-width: 135px !important; max-width: 145px !important; }
        .col-w-action { width: 45px !important; min-width: 45px !important; max-width: 45px !important; }

        /* Ensure Select2 inside the item column stays strictly static with text ellipsis */
        .col-w-item .select2-container {
            width: 100% !important;
            max-width: 100% !important;
        }
        .col-w-item .select2-selection--single {
            width: 100% !important;
            overflow: hidden !important;
        }
        .col-w-item .select2-selection__rendered {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            padding-right: 22px !important;
        }
        .maintenance-table thead th {
            background: linear-gradient(135deg, #f8faff 0%, #eef2f9 100%);
            border-bottom: 2px solid #dce3ed;
            font-size: 0.76rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.75rem 0.65rem;
            white-space: nowrap;
        }
        .maintenance-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s ease;
        }
        .maintenance-table tbody tr:hover {
            background: #f9fbff;
        }
        .maintenance-table tbody td {
            padding: 0.55rem 0.65rem;
            vertical-align: middle;
            font-size: 0.88rem;
        }

        .row-number-badge {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: #f1f5f9;
            color: #475569;
            font-weight: 700;
            font-size: 0.82rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Delete Button */
        .btn-delete-row {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            background: #fef2f2;
            color: #ef4444;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-delete-row:hover {
            background: #ef4444;
            color: #fff;
            transform: scale(1.08);
        }

        /* Add Item Button */
        .btn-add-item {
            background: linear-gradient(135deg, #7669D3 0%, #5a4db8 100%);
            border: none;
            color: #fff;
            font-weight: 600;
            font-size: 0.84rem;
            padding: 0.45rem 1rem;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(118, 105, 211, 0.3);
        }
        .btn-add-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(118, 105, 211, 0.4);
            color: #fff;
        }
        .btn-add-item:disabled {
            background: #94a3b8;
            box-shadow: none;
            cursor: not-allowed;
            transform: none;
        }

        /* ===== Summary Panel ===== */
        .summary-panel {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 12px;
            padding: 1.1rem 1.4rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.12);
        }
        .summary-item {
            text-align: center;
            flex: 1;
            min-width: 130px;
        }
        .summary-item .summary-label {
            font-size: 0.7rem;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 0.2rem;
        }
        .summary-item .summary-value {
            font-size: 1.15rem;
            font-weight: 700;
            color: #f8fafc;
        }
        .summary-divider {
            width: 1px;
            height: 38px;
            background: rgba(255, 255, 255, 0.12);
        }
        .grand-total-highlight {
            flex: 1.5;
            text-align: right;
            border-left: 2px solid rgba(118, 105, 211, 0.4);
            padding-left: 1.2rem;
        }
        .grand-total-highlight .summary-label {
            color: #a5b4fc;
        }
        .grand-total-highlight .summary-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: #38bdf8;
            letter-spacing: -0.5px;
        }

        /* ===== Action Buttons ===== */
        .btn-back {
            background: #fff;
            border: 1.5px solid #e2e8f0;
            color: #475569;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.45rem 0.95rem;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .btn-back:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #1e293b;
        }

        .btn-submit-maintenance {
            background: linear-gradient(135deg, #7669D3 0%, #5a4db8 100%);
            border: none;
            color: #fff;
            font-weight: 700;
            font-size: 0.92rem;
            padding: 0.65rem 1.6rem;
            border-radius: 9px;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            transition: all 0.25s ease;
            box-shadow: 0 3px 10px rgba(118, 105, 211, 0.35);
        }
        .btn-submit-maintenance:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(118, 105, 211, 0.45);
            color: #fff;
        }
        .btn-submit-maintenance:active {
            transform: translateY(0);
        }

        /* ===== Row Entry Animation ===== */
        @keyframes fadeInRow {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .maintenance-table tbody tr.new-row {
            animation: fadeInRow 0.25s ease forwards;
        }

        /* Stock Status Badge */
        .stock-pill {
            font-size: 0.78rem;
            font-weight: 700;
            padding: 0.25rem 0.55rem;
            border-radius: 6px;
            display: inline-block;
        }
        .stock-safe { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .stock-warn { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
        .stock-empty { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .stock-jasa { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
    </style>
@endpush

@section('content')
    <form method="post" action="{{ route($view . 'store') }}" id="formMaintenance">
        @csrf
        <div class="col-sm-12">

            <!-- Card 1: Informasi Maintenance -->
            <div class="card maintenance-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <span class="header-icon header-icon-primary">
                            <i class="mdi mdi-tools"></i>
                        </span>
                        <div>
                            <h5 class="m-0 fw-bold text-dark">{{ $title }} - {{ __('general.add_data') }}</h5>
                            <small class="text-muted">Pencatatan servis armada, pemakaian suku cadang, dan referensi PO</small>
                        </div>
                    </div>
                    <a href="{{ route($view . 'index') }}" class="btn-back">
                        <i class="mdi mdi-arrow-left"></i>
                        {{ __('general.back_to_list') }}
                    </a>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <!-- Code (Auto Generated) -->
                        <div class="col-md-4">
                            <label class="form-label-custom" for="code_display">
                                <i class="mdi mdi-barcode-scan text-muted"></i>
                                Kode Maintenance <span class="required-star">*</span>
                            </label>
                            <input class="form-control form-control-custom fw-bold" type="text"
                                placeholder="Auto Generated..." id="code_display" readonly disabled>
                            <input type="hidden" name="code" id="code_hidden">
                        </div>

                        <!-- Date -->
                        <div class="col-md-4">
                            <label class="form-label-custom" for="datetime-local">
                                <i class="mdi mdi-calendar text-muted"></i>
                                {{ __('menu_maintenance.date') }} <span class="required-star">*</span>
                            </label>
                            <input class="form-control form-control-custom" name="date" id="datetime-local"
                                type="date" required value="{{ now()->toDateString() }}">
                        </div>

                        <!-- Time -->
                        <div class="col-md-4">
                            <label class="form-label-custom" for="time">
                                <i class="mdi mdi-clock-outline text-muted"></i>
                                {{ __('menu_maintenance.time') }} <span class="required-star">*</span>
                            </label>
                            <input class="form-control form-control-custom" name="time" id="time"
                                type="time" required value="{{ now()->setTimezone('Asia/Jakarta')->format('H:i') }}">
                        </div>

                        <!-- Fleet -->
                        <div class="col-md-6">
                            <label class="form-label-custom" for="fleetCode">
                                <i class="mdi mdi-truck-outline text-muted"></i>
                                {{ __('menu_maintenance.fleet') }} / Plat Nomor <span class="required-star">*</span>
                            </label>
                            <select class="js-example-basic-single form-select" name="fleetCode" id="fleetCode" required>
                                <option selected="" disabled="" value="">{{ __('general.choose') }} Armada...</option>
                                @foreach ($fleet as $item)
                                    <option value="{{ $item->code }}">
                                        {{ $item->plateNumber }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Warehouse -->
                        <div class="col-md-6">
                            <label class="form-label-custom" for="warehouseCode">
                                <i class="mdi mdi-warehouse text-muted"></i>
                                Gudang Logistik <span class="required-star">*</span>
                            </label>
                            <select class="js-example-basic-single form-select" name="warehouseCode" id="warehouseCode" required>
                                <option selected="" disabled="" value="">{{ __('general.choose') }} Gudang...</option>
                                @foreach ($warehouse as $item)
                                    <option value="{{ $item->code }}">
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Purchase Order (No PO) Multi-Select -->
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label-custom m-0" for="purchase_ids">
                                    <i class="mdi mdi-file-document-outline text-muted"></i>
                                    No. Purchase Order (PO) <span class="required-star">*</span>
                                    <span id="po-count-badge" class="badge bg-primary-subtle text-primary border border-primary-subtle ms-2" style="display: none;">0 PO</span>
                                    <span id="po-loading-spinner" class="spinner-border spinner-border-sm text-primary ms-2" style="display: none;" role="status"></span>
                                </label>
                                <span class="text-muted small" id="po-status-hint">Pilih Gudang terlebih dahulu</span>
                            </div>
                            <select class="form-select" name="purchase_ids[]" id="purchase_ids" multiple="multiple" required disabled>
                            </select>
                            <small class="text-muted d-block mt-1">
                                <i class="mdi mdi-information-outline me-1"></i>Pilih satu atau lebih PO asal pengadaan suku cadang ini. Daftar PO akan terisi otomatis sesuai Gudang.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Detail Items / Pemeliharaan -->
            <div class="card maintenance-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <span class="header-icon header-icon-info">
                            <i class="mdi mdi-format-list-bulleted-type"></i>
                        </span>
                        <div>
                            <h5 class="m-0 fw-bold text-dark">Detail Pemakaian Suku Cadang & Jasa</h5>
                            <small class="text-muted">Item suku cadang (part) mengurangi stok fisik gudang, sedangkan jasa tidak mengurangi stok</small>
                        </div>
                    </div>
                    <button class="btn-add-item" type="button" id="save" disabled>
                        <i class="mdi mdi-plus-circle-outline fs-16"></i>
                        Tambah Baris
                    </button>
                </div>

                <div class="card-body">
                    <!-- Alert Notice if warehouse not selected -->
                    <div id="warehouse-alert-box" class="alert alert-warning border-0 bg-warning-subtle text-warning-emphasis d-flex align-items-center gap-2 mb-3 py-2 px-3 rounded-3">
                        <i class="mdi mdi-alert-circle-outline fs-18"></i>
                        <span>Silakan pilih <strong>Gudang Logistik</strong> di atas untuk membuka pilihan suku cadang dan No PO.</span>
                    </div>

                    <div class="maintenance-table-wrapper">
                        <div class="table-responsive">
                            <table class="maintenance-table" id="maintenanceTable">
                                <thead>
                                    <tr>
                                        <th class="col-w-no text-center">#</th>
                                        <th class="col-w-item">Item / Suku Cadang / Jasa <span class="text-danger">*</span></th>
                                        <th class="col-w-desc">Deskripsi / Catatan</th>
                                        <th class="col-w-stock text-center">Stok Gudang</th>
                                        <th class="col-w-qty text-center">Qty Pakai <span class="text-danger">*</span></th>
                                        <th class="col-w-price text-end">Harga Satuan</th>
                                        <th class="col-w-total text-end">Total</th>
                                        <th class="col-w-action text-center"><i class="mdi mdi-trash-can-outline fs-16 text-muted"></i></th>
                                    </tr>
                                </thead>
                                <tbody id="purchaseDetails">
                                    <tr id="row_1">
                                        <td class="col-w-no text-center">
                                            <span class="row-number-badge">1</span>
                                        </td>
                                        <td class="col-w-item">
                                            <select class="js-example-basic-single form-select" name="itemCode[]" id="itemCode_1" required
                                                onchange="loadItemDetails(1)" disabled>
                                                <option selected="" disabled="" value="">
                                                    {{ __('general.choose') }} Item...
                                                </option>
                                            </select>
                                            <input type="hidden" name="item_type[]" id="item_type_1" value="">
                                        </td>
                                        <td class="col-w-desc">
                                            <input class="form-control form-control-custom" type="text"
                                                name="description[]" id="description_1" placeholder="Catatan perbaikan / posisi part...">
                                        </td>
                                        <td class="col-w-stock text-center">
                                            <div id="stock_display_1">
                                                <span class="stock-pill stock-empty">0</span>
                                            </div>
                                            <input type="hidden" name="qty_exist[]" id="qty_exist_1" value="0">
                                        </td>
                                        <td class="col-w-qty">
                                            <input class="form-control form-control-custom qty-input text-center fw-bold"
                                                type="number" name="qty[]" id="qty_1" required min="0.5" step="0.5" value="1">
                                        </td>
                                        <td class="col-w-price">
                                            <input class="form-control form-control-custom text-end bg-light fw-bold"
                                                type="text" name="price[]" id="price_1" readonly value="0">
                                        </td>
                                        <td class="col-w-total">
                                            <input class="form-control form-control-custom text-end bg-light fw-bold"
                                                type="text" name="total[]" id="total_1" readonly value="0">
                                        </td>
                                        <td class="col-w-action text-center">
                                            <!-- first row remove button starts hidden -->
                                            <button type="button" class="btn-delete-row" onclick="removeDetailRow(1)" style="display:none;" title="Hapus Baris">
                                                <i class="mdi mdi-delete-outline fs-16"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Summary Metrics Panel -->
                    <div class="summary-panel mt-4">
                        <div class="summary-item">
                            <div class="summary-label">Total Jenis Item</div>
                            <div class="summary-value" id="summaryTotalItems">0 Item</div>
                        </div>
                        <div class="summary-divider d-none d-md-block"></div>
                        <div class="summary-item">
                            <div class="summary-label">Total Kuantitas</div>
                            <div class="summary-value" id="summaryTotalQty">0 Unit</div>
                        </div>
                        <div class="summary-divider d-none d-md-block"></div>
                        <div class="summary-item grand-total-highlight">
                            <div class="summary-label">Estimasi Grand Total</div>
                            <div class="summary-value">Rp <span id="grand_total_display">0</span></div>
                            <input type="hidden" name="grand_total" id="grand_total" value="0">
                        </div>
                    </div>

                    <!-- Bottom Action Bar -->
                    <hr class="mt-4 mb-3 text-muted opacity-25">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <a href="{{ route($view . 'index') }}" class="btn-back">
                            <i class="mdi mdi-arrow-left"></i> Batal & Kembali
                        </a>
                        <div class="d-flex align-items-center gap-3">
                            <span class="text-muted small d-none d-sm-inline">
                                <i class="mdi mdi-shield-check-outline me-1"></i>Data transaksi akan tercatat di kartu stok gudang
                            </span>
                            <button class="btn-submit-maintenance" id="submit" type="submit">
                                <i class="mdi mdi-content-save-check-outline fs-18"></i>
                                Simpan Maintenance
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
@endsection

@push('script')
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/js/helper.js') }}"></script>

    <script>
        let dataItem = [];
        let selectedWarehouse = null;

        $(document).ready(function() {
            generateCode('input[name="date"]', '#code_display', '#code_hidden', '/ajax/maintenance-generate-code');

            // Initialize select2 for Purchase Order multi-select
            $('#purchase_ids').select2({
                placeholder: 'Pilih satu atau beberapa No PO...',
                width: '100%',
                allowClear: true
            });

            // Update badge when PO selection changes
            $('#purchase_ids').on('change', function() {
                const count = ($(this).val() || []).length;
                if (count > 0) {
                    $('#po-count-badge').text(count + ' PO terpilih').show();
                } else {
                    $('#po-count-badge').hide();
                }
            });

            // If warehouse already has value on page load
            const initWh = $('#warehouseCode').val();
            if (initWh) {
                $('#warehouseCode').trigger('change');
            }
        });

        $('input[name="date"]').on('change', function() {
            generateCode('input[name="date"]', '#code_display', '#code_hidden', '/ajax/maintenance-generate-code');
        });

        // When warehouse is selected, load POs and stock items
        $('#warehouseCode').on('change', function() {
            selectedWarehouse = $(this).val();

            if (!selectedWarehouse) {
                $('#warehouse-alert-box').slideDown();
                $('#save').prop('disabled', true);
                $('select[name="itemCode[]"]').prop('disabled', true);
                $('#purchase_ids').prop('disabled', true).val(null).trigger('change');
                $('#po-status-hint').text('Pilih Gudang terlebih dahulu');
                return;
            }

            // Hide alert banner
            $('#warehouse-alert-box').slideUp();
            $('#po-status-hint').text('Memuat daftar PO...');
            $('#po-loading-spinner').show();

            // 1. Load daftar PO untuk warehouse ini
            loadPurchasesByWarehouse(selectedWarehouse);

            // 2. Load stock items for this warehouse
            $.ajax({
                url: '/ajax/maintenance-stock-by-warehouse',
                method: 'GET',
                data: {
                    warehouseCode: selectedWarehouse
                },
                success: function(response) {
                    if (response.success) {
                        dataItem = response.data;

                        // Enable item selects and add button
                        $('select[name="itemCode[]"]').prop('disabled', false);
                        $('#save').prop('disabled', false);

                        // Populate item selects across all existing rows
                        $('#purchaseDetails tr').each(function() {
                            const select = $(this).find('select[name="itemCode[]"]');
                            const currentVal = select.val();

                            let html = '<option selected="" disabled="" value="">{{ __("general.choose") }} Item...</option>';
                            dataItem.forEach(i => {
                                const typeBadge = i.type === 'jasa' ? '[JASA]' : `[Stok: ${i.stock}]`;
                                html += `<option value="${i.code}" data-name="${i.name}" data-qty="${i.stock}" data-price="${i.price}" data-type="${i.type}">${i.code} - ${i.name} ${typeBadge}</option>`;
                            });

                            select.html(html);
                            if (currentVal) {
                                select.val(currentVal);
                            }
                            select.select2({ width: '100%' });
                        });
                    } else {
                        swal({
                            title: "{{ __('general.warning') }}",
                            text: response.message,
                            icon: "warning",
                        });
                    }
                },
                error: function() {
                    swal({
                        title: "{{ __('general.error') }}",
                        text: "Gagal memuat daftar suku cadang dari gudang.",
                        icon: "error",
                    });
                }
            });
        });

        // Load daftar PO (Purchase Order) berdasarkan warehouse terpilih
        function loadPurchasesByWarehouse(warehouseCode) {
            $.ajax({
                url: '/ajax/maintenance-purchases-by-warehouse',
                method: 'GET',
                data: {
                    warehouseCode: warehouseCode
                },
                success: function(response) {
                    $('#po-loading-spinner').hide();
                    let html = '';
                    const totalPo = (response.data || []).length;

                    if (response.success && totalPo > 0) {
                        response.data.forEach(function(p) {
                            let label = p.code;
                            if (p.supplierName) {
                                label += ' - ' + p.supplierName;
                            }
                            if (p.date) {
                                label += ' (' + p.date + ')';
                            }
                            html += `<option value="${p.id}">${label}</option>`;
                        });
                        $('#po-status-hint').text(`${totalPo} PO tersedia`);
                    } else {
                        $('#po-status-hint').text('Tidak ada PO pada gudang ini');
                    }

                    $('#purchase_ids').html(html).prop('disabled', false).trigger('change');
                },
                error: function() {
                    $('#po-loading-spinner').hide();
                    $('#po-status-hint').text('Gagal memuat PO');
                    swal({
                        title: "{{ __('general.error') }}",
                        text: 'Gagal memuat daftar Purchase Order.',
                        icon: "error",
                    });
                }
            });
        }

        // Load item details (name, price, stock)
        function loadItemDetails(row) {
            let selectElem = $(`#itemCode_${row}`);
            let itemCode = selectElem.val();
            let selectedOption = selectElem.find('option:selected');
            let itemName = selectedOption.data('name');
            let itemQty = parseFloat(selectedOption.data('qty')) || 0;
            let itemType = selectedOption.data('type');

            let foundItem = dataItem.find(i => i.code === itemCode || (i.item && i.item.code === itemCode));
            let itemPrice = selectedOption.data('price') ?? (foundItem ? (foundItem.price ?? (foundItem.item ? foundItem.item.price : 0)) : 0);
            let isJasa = itemType === 'jasa' || (foundItem && foundItem.type === 'jasa');

            $(`#item_type_${row}`).val(isJasa ? 'jasa' : 'part');
            $(`#qty_exist_${row}`).val(isJasa ? 999999 : itemQty);

            // Update visual stock badge
            let stockHtml = '';
            if (isJasa) {
                stockHtml = '<span class="stock-pill stock-jasa"><i class="mdi mdi-account-wrench me-1"></i>Jasa</span>';
            } else if (itemQty > 5) {
                stockHtml = `<span class="stock-pill stock-safe"><i class="mdi mdi-check-circle-outline me-1"></i>${itemQty}</span>`;
            } else if (itemQty > 0) {
                stockHtml = `<span class="stock-pill stock-warn"><i class="mdi mdi-alert-circle-outline me-1"></i>${itemQty} (Sisa)</span>`;
            } else {
                stockHtml = '<span class="stock-pill stock-empty"><i class="mdi mdi-close-circle-outline me-1"></i>Habis</span>';
            }
            $(`#stock_display_${row}`).html(stockHtml);

            // Price & subtotal
            $(`#price_${row}`).val(new Intl.NumberFormat('id-ID').format(itemPrice));
            let qty = parseFloat($(`#qty_${row}`).val()) || 0;
            let total = qty * parseFloat(itemPrice);
            $(`#total_${row}`).val(new Intl.NumberFormat('id-ID').format(total));

            // Validate current qty against stock
            validateRowQty(row);
            updateSummary();
        }

        // Live stock validation per row
        function validateRowQty(row) {
            const isJasa = $(`#item_type_${row}`).val() === 'jasa';
            const qtyInput = $(`#qty_${row}`);
            const qty = parseFloat(qtyInput.val()) || 0;
            const availableStock = parseFloat($(`#qty_exist_${row}`).val()) || 0;

            if (!isJasa && qty > availableStock) {
                qtyInput.addClass('is-invalid').css('border-color', '#ef4444');
            } else {
                qtyInput.removeClass('is-invalid').css('border-color', '');
            }
        }

        // Update total price based on quantity input
        $(document).on('input', '.qty-input', function() {
            let id = $(this).attr('id');
            let row = id.split('_')[1];
            let qty = parseFloat($(this).val()) || 0;
            let priceText = $(`#price_${row}`).val() || '0';
            let price = parseFloat(priceText.toString().replace(/\./g, '').replace(/,/g, '.')) || 0;
            let total = qty * price;
            $(`#total_${row}`).val(new Intl.NumberFormat('id-ID').format(total));

            validateRowQty(row);
            updateSummary();
        });

        // Update summary metrics
        function updateSummary() {
            let grandTotal = 0;
            let totalItems = 0;
            let totalQty = 0;

            $('#purchaseDetails tr').each(function(index) {
                let row = $(this).attr('id').split('_')[1];
                let itemCode = $(`#itemCode_${row}`).val();

                if (itemCode) {
                    totalItems++;
                    let qty = parseFloat($(`#qty_${row}`).val()) || 0;
                    totalQty += qty;

                    let totalText = $(`#total_${row}`).val() || '0';
                    let total = parseFloat(totalText.toString().replace(/\./g, '').replace(/,/g, '.')) || 0;
                    grandTotal += total;
                }
            });

            $('#summaryTotalItems').text(totalItems + ' Item');
            $('#summaryTotalQty').text((Math.round(totalQty * 10) / 10) + ' Unit');
            $('#grand_total_display').text(new Intl.NumberFormat('id-ID').format(grandTotal));
            $('#grand_total').val(grandTotal);
        }

        // Add new item row
        $('#save').on('click', function() {
            if (!selectedWarehouse) {
                swal({
                    title: "{{ __('general.warning') }}",
                    text: "Silakan pilih Gudang terlebih dahulu.",
                    icon: "warning",
                });
                return;
            }

            let nextRow = Date.now(); // unique row identifier

            let newRowHtml = `
                <tr id="row_${nextRow}" class="new-row">
                    <td class="col-w-no text-center">
                        <span class="row-number-badge">#</span>
                    </td>
                    <td class="col-w-item">
                        <select class="js-example-basic-single form-select" name="itemCode[]" id="itemCode_${nextRow}" required onchange="loadItemDetails(${nextRow})">
                            <option selected="" disabled="" value="">{{ __('general.choose') }} Item...</option>
                        </select>
                        <input type="hidden" name="item_type[]" id="item_type_${nextRow}" value="">
                    </td>
                    <td class="col-w-desc">
                        <input class="form-control form-control-custom" type="text" name="description[]" id="description_${nextRow}" placeholder="Catatan perbaikan / posisi part...">
                    </td>
                    <td class="col-w-stock text-center">
                        <div id="stock_display_${nextRow}">
                            <span class="stock-pill stock-empty">0</span>
                        </div>
                        <input type="hidden" name="qty_exist[]" id="qty_exist_${nextRow}" value="0">
                    </td>
                    <td class="col-w-qty">
                        <input class="form-control form-control-custom qty-input text-center fw-bold" type="number" name="qty[]" id="qty_${nextRow}" required min="0.5" step="0.5" value="1">
                    </td>
                    <td class="col-w-price">
                        <input class="form-control form-control-custom text-end bg-light fw-bold" type="text" name="price[]" id="price_${nextRow}" readonly value="0">
                    </td>
                    <td class="col-w-total">
                        <input class="form-control form-control-custom text-end bg-light fw-bold" type="text" name="total[]" id="total_${nextRow}" readonly value="0">
                    </td>
                    <td class="col-w-action text-center">
                        <button type="button" class="btn-delete-row" onclick="removeDetailRow(${nextRow})" title="Hapus Baris">
                            <i class="mdi mdi-delete-outline fs-16"></i>
                        </button>
                    </td>
                </tr>
            `;

            $('#purchaseDetails').append(newRowHtml);

            // Populate options from cached dataItem
            let optionsHtml = '<option selected="" disabled="" value="">{{ __("general.choose") }} Item...</option>';
            dataItem.forEach(i => {
                const typeBadge = i.type === 'jasa' ? '[JASA]' : `[Stok: ${i.stock}]`;
                optionsHtml += `<option value="${i.code}" data-name="${i.name}" data-qty="${i.stock}" data-price="${i.price}" data-type="${i.type}">${i.code} - ${i.name} ${typeBadge}</option>`;
            });

            $(`#itemCode_${nextRow}`).html(optionsHtml).select2({ width: '100%' });

            refreshRowNumbers();
        });

        // Remove row function
        function removeDetailRow(rowId) {
            $(`#row_${rowId}`).remove();
            refreshRowNumbers();
            updateSummary();
        }

        // Refresh sequential row badges and delete buttons visibility
        function refreshRowNumbers() {
            const rows = $('#purchaseDetails tr');
            rows.each(function(idx) {
                $(this).find('.row-number-badge').text(idx + 1);
                const deleteBtn = $(this).find('.btn-delete-row');
                if (rows.length > 1) {
                    deleteBtn.show();
                } else {
                    deleteBtn.hide();
                }
            });
        }

        // Form Submit Validation
        $('#submit').on('click', function(e) {
            e.preventDefault();

            const formElement = document.getElementById('formMaintenance');
            let isValid = true;
            let errorMessage = '';
            let codes = [];

            // 1. Validasi Armada
            if (!$('#fleetCode').val()) {
                swal({
                    title: "{{ __('general.warning') }}",
                    text: "Silakan pilih Armada / Plat Nomor terlebih dahulu.",
                    icon: "warning",
                });
                return;
            }

            // 2. Validasi Gudang
            if (!selectedWarehouse) {
                swal({
                    title: "{{ __('general.warning') }}",
                    text: "Silakan pilih Gudang Logistik terlebih dahulu.",
                    icon: "warning",
                });
                return;
            }

            // 3. Validasi Wajib pilih minimal satu No PO
            const selectedPos = $('select[name="purchase_ids[]"]').val();
            if (!selectedPos || selectedPos.length === 0) {
                swal({
                    title: "{{ __('general.warning') }}",
                    text: "Silakan pilih minimal satu No PO pengadaan terkait.",
                    icon: "warning",
                });
                return;
            }

            // 4. Validasi Tabel Item
            const itemRows = $('#purchaseDetails tr');
            if (itemRows.length === 0) {
                swal({
                    title: "{{ __('general.warning') }}",
                    text: "Minimal harus ada satu item suku cadang atau jasa.",
                    icon: "warning",
                });
                return;
            }

            itemRows.each(function() {
                let code = $(this).find('select[name="itemCode[]"]').val();
                let itemName = $(this).find('select[name="itemCode[]"] option:selected').data('name') || code;
                let qtyInput = parseFloat($(this).find('input[name="qty[]"]').val());
                let qtyExisting = parseFloat($(this).find('input[name="qty_exist[]"]').val());
                let itemType = $(this).find('input[name="item_type[]"]').val();

                if (!code) {
                    isValid = false;
                    errorMessage = 'Terdapat baris item yang belum dipilih suku cadangnya.';
                    return false;
                }

                if (!qtyInput || qtyInput <= 0) {
                    isValid = false;
                    errorMessage = `Jumlah kuantitas untuk item "${itemName}" harus lebih dari 0.`;
                    return false;
                }

                if (itemType !== 'jasa' && qtyInput > qtyExisting) {
                    isValid = false;
                    errorMessage = `Kuantitas item "${itemName}" (${qtyInput}) melebihi stok yang tersedia di gudang (${qtyExisting}).`;
                    return false;
                }

                if (codes.includes(code)) {
                    isValid = false;
                    errorMessage = `Item "${itemName}" dipilih lebih dari satu kali. Silakan gabungkan kuantitasnya dalam satu baris.`;
                    return false;
                }

                codes.push(code);
            });

            if (!isValid) {
                swal({
                    title: "{{ __('general.warning') }}",
                    text: errorMessage,
                    icon: "warning",
                });
                return;
            }

            swal({
                title: "{{ __('general.are_you_sure') }}",
                text: "Apakah Anda yakin ingin menyimpan data maintenance ini?",
                icon: "warning",
                buttons: {
                    cancel: "Batal",
                    confirm: {
                        text: "Ya, Simpan",
                        value: true,
                        visible: true,
                        className: "btn-primary"
                    }
                },
                dangerMode: false,
            }).then((willSave) => {
                if (willSave && formElement) {
                    HTMLFormElement.prototype.submit.call(formElement);
                }
            });
        });
    </script>
@endpush
