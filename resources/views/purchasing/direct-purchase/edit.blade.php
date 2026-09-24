@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Supplier',
    'secondSegment' => __('general.edit'),
])

@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">

    <style>
        .direct-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            background: #ffffff;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        .direct-card .card-header {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.4rem;
        }
        .direct-card .card-body {
            padding: 1.4rem;
        }
        .header-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }
        .header-icon-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: #ffffff;
        }
        .header-icon-info {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
        }
        .form-label-custom {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            margin-bottom: 0.4rem;
        }
        .form-label-custom .required-star {
            color: #ef4444;
            font-weight: bold;
        }
        .form-control-custom {
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            padding: 0.52rem 0.85rem;
            font-size: 0.9rem;
            background: #fafbfc;
            transition: all 0.2s ease;
        }
        .form-control-custom:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
            background: #ffffff;
        }
        .form-control-custom:read-only,
        .form-control-custom:disabled {
            background: #f1f5f9;
            color: #64748b;
        }

        /* ── Table Styling ── */
        .items-table-wrapper {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }
        .items-table {
            width: 100%;
            margin-bottom: 0;
        }
        .items-table thead th {
            background: #f8fafc;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 12px;
            border-bottom: 2px solid #e2e8f0;
            vertical-align: middle;
        }
        .items-table tbody td {
            padding: 8px 10px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
        .row-number {
            width: 26px;
            height: 26px;
            border-radius: 6px;
            background: #f1f5f9;
            color: #475569;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
        }
        .btn-delete-row {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: none;
            background: #fee2e2;
            color: #dc2626;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-delete-row:hover {
            background: #dc2626;
            color: #ffffff;
        }
        .btn-add-item {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
            color: #ffffff;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.45rem 1.1rem;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            box-shadow: 0 2px 6px rgba(59, 130, 246, 0.25);
            transition: all 0.2s ease;
        }
        .btn-add-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.35);
            color: #ffffff;
        }

        /* ── Summary Panel ── */
        .summary-panel {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border-radius: 12px;
            padding: 1.1rem 1.4rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
        }
        .summary-item {
            text-align: center;
            flex: 1;
            min-width: 130px;
        }
        .summary-item .summary-label {
            font-size: 0.72rem;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.2rem;
        }
        .summary-item .summary-value {
            font-size: 1.25rem;
            font-weight: 800;
            color: #ffffff;
        }
        .grand-total-box {
            background: rgba(59, 130, 246, 0.2);
            border: 1px solid rgba(59, 130, 246, 0.4);
            border-radius: 10px;
            padding: 0.4rem 1.2rem;
        }
        .grand-total-box .summary-value {
            font-size: 1.45rem;
            color: #60a5fa;
        }

        /* ── Dark Mode ── */
        html[data-bs-theme="dark"] .direct-card {
            background: var(--bs-card-bg);
            border-color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] .direct-card .card-header {
            background: var(--bs-secondary-bg);
            border-bottom-color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] .form-label-custom {
            color: var(--bs-body-color);
        }
        html[data-bs-theme="dark"] .form-control-custom {
            background: var(--bs-secondary-bg);
            border-color: var(--bs-border-color);
            color: var(--bs-body-color);
        }
        html[data-bs-theme="dark"] .form-control-custom:read-only,
        html[data-bs-theme="dark"] .form-control-custom:disabled {
            background: var(--bs-tertiary-bg);
            color: var(--bs-secondary-color);
        }
        html[data-bs-theme="dark"] .items-table-wrapper {
            border-color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] .items-table thead th {
            background: var(--bs-tertiary-bg);
            border-bottom-color: var(--bs-border-color);
            color: var(--bs-body-color);
        }
        html[data-bs-theme="dark"] .items-table tbody td {
            border-bottom-color: var(--bs-border-color);
            color: var(--bs-body-color);
        }
        html[data-bs-theme="dark"] .row-number {
            background: var(--bs-tertiary-bg);
            color: var(--bs-body-color);
        }
    </style>
@endpush

@section('content')
<form method="POST" action="{{ route($view . 'update', $data->id) }}" id="formDirectPurchase">
    @csrf
    @method('PUT')
    <div class="col-sm-12">
        @include('partials.alert')

        {{-- Card 1: Informasi Pembelian & Armada --}}
        <div class="card direct-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="header-icon header-icon-primary">
                        <i class="mdi mdi-pencil-box-outline"></i>
                    </span>
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">{{ $title }} — Edit Data</h5>
                        <small class="text-muted">Perubahan data akan otomatis memperbarui data pemeliharaan armada dan sinkronisasi stok</small>
                    </div>
                </div>
                <a href="{{ route($view . 'index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="mdi mdi-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    {{-- Kode Transaksi (Readonly) --}}
                    <div class="col-md-3">
                        <label class="form-label form-label-custom" for="code_display">
                            No. Transaksi
                        </label>
                        <input class="form-control form-control-custom fw-bold font-monospace" type="text"
                            value="{{ $data->code }}" id="code_display" readonly disabled>
                    </div>

                    {{-- Tanggal --}}
                    <div class="col-md-3">
                        <label class="form-label form-label-custom" for="date">
                            Tanggal Pembelian <span class="required-star">*</span>
                        </label>
                        <input class="form-control form-control-custom" name="date" id="date" type="date"
                            required value="{{ old('date', $data->date) }}">
                    </div>

                    {{-- Jam --}}
                    <div class="col-md-2">
                        <label class="form-label form-label-custom" for="time">
                            Jam <span class="required-star">*</span>
                        </label>
                        <input class="form-control form-control-custom" name="time" id="time" type="time"
                            required value="{{ old('time', $data->time) }}">
                    </div>

                    {{-- Pilihan Armada (Wajib) --}}
                    <div class="col-md-4">
                        <label class="form-label form-label-custom" for="fleetCode">
                            Mobil / Plat Nomor Armada <span class="required-star">*</span>
                        </label>
                        <select class="form-select select2-basic" name="fleetCode" id="fleetCode" required>
                            <option value="">Pilih Mobil / Armada...</option>
                            @foreach ($fleet as $f)
                                <option value="{{ $f->code }}" {{ (old('fleetCode', $data->fleetCode) == $f->code) ? 'selected' : '' }}>
                                    {{ $f->plateNumber }} {{ $f->brand ? ' - ' . $f->brand->name : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Supplier / Toko --}}
                    <div class="col-md-6">
                        <label class="form-label form-label-custom" for="supplierCode">
                            Supplier / Toko Spare Part <span class="required-star">*</span>
                        </label>
                        <select class="form-select select2-basic" name="supplierCode" id="supplierCode" required>
                            <option value="">Pilih Supplier...</option>
                            @foreach ($supplier as $s)
                                <option value="{{ $s->code }}" {{ (old('supplierCode', $data->supplierCode) == $s->code) ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Sumber Dana / Akun Kas-Bank --}}
                    <div class="col-md-6">
                        <label class="form-label form-label-custom" for="userBankCode">
                            Sumber Dana (Kas / Bank)
                        </label>
                        <select class="form-select select2-basic" name="userBankCode" id="userBankCode">
                            <option value="">-- Pembayaran Tunai / Langsung Lunas --</option>
                            @foreach ($userBank as $ub)
                                @php
                                    $bankName = optional($ub->bank)->name ?? 'Bank';
                                    $balance = optional($ub->liveMutation)->balance ?? 0;
                                @endphp
                                <option value="{{ $ub->code }}" {{ (old('userBankCode', $data->userBankCode) == $ub->code) ? 'selected' : '' }}>
                                    {{ $bankName }} - {{ $ub->accountName }} (Saldo: Rp {{ number_format($balance, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Hidden Gudang Logistik --}}
                    <input type="hidden" name="warehouseCode" id="warehouseCode" value="{{ $data->warehouseCode }}">
                </div>
            </div>
        </div>

        {{-- Card 2: Rincian Suku Cadang / Spare Part --}}
        <div class="card direct-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="header-icon header-icon-info">
                        <i class="mdi mdi-wrench"></i>
                    </span>
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">Rincian Suku Cadang / Spare Part</h5>
                        <small class="text-muted">Daftar spare part yang dipasang pada armada</small>
                    </div>
                </div>
                <button type="button" class="btn-add-item" id="btnAddRow">
                    <i class="mdi mdi-plus"></i> Tambah Baris
                </button>
            </div>
            <div class="card-body">
                <div class="items-table-wrapper">
                    <table class="table items-table" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width: 40px" class="text-center">#</th>
                                <th style="width: 35%">Item Suku Cadang / Spare Part <span class="required-star">*</span></th>
                                <th style="width: 12%" class="text-center">Qty <span class="required-star">*</span></th>
                                <th style="width: 18%" class="text-end">Harga Satuan (Rp) <span class="required-star">*</span></th>
                                <th style="width: 18%">Keterangan</th>
                                <th style="width: 17%" class="text-end">Subtotal (Rp)</th>
                                <th style="width: 40px" class="text-center"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            @forelse ($data->details as $index => $detail)
                                @php $rowId = $index + 1; @endphp
                                <tr id="row_{{ $rowId }}">
                                    <td class="text-center">
                                        <span class="row-number">{{ $rowId }}</span>
                                    </td>
                                    <td>
                                        <select class="form-select select2-item" name="itemCode[]" id="itemCode_{{ $rowId }}" required onchange="onItemChange({{ $rowId }})">
                                            <option value="">Pilih Suku Cadang...</option>
                                            @foreach ($items as $it)
                                                <option value="{{ $it->code }}"
                                                    {{ $detail->itemCode == $it->code ? 'selected' : '' }}
                                                    data-price="{{ $it->latestPurchase->price ?? ($it->price ?? 0) }}"
                                                    data-name="{{ $it->name }}">
                                                    {{ $it->code }} - {{ $it->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input class="form-control form-control-custom text-center" type="number"
                                            min="0.5" step="0.5" name="qty[]" id="qty_{{ $rowId }}" value="{{ $detail->qty }}" required
                                            oninput="recalculateRow({{ $rowId }})" onchange="recalculateRow({{ $rowId }})">
                                    </td>
                                    <td>
                                        <input class="form-control form-control-custom text-end" type="text"
                                            name="price[]" id="price_{{ $rowId }}" value="{{ number_format($detail->price, 0, ',', '.') }}" required
                                            oninput="formatRupiahInput(this); recalculateRow({{ $rowId }})" onchange="recalculateRow({{ $rowId }})">
                                    </td>
                                    <td>
                                        <input class="form-control form-control-custom" type="text"
                                            name="description[]" id="description_{{ $rowId }}" value="{{ $detail->description }}" placeholder="Keterangan...">
                                    </td>
                                    <td>
                                        <input class="form-control form-control-custom text-end font-monospace fw-bold"
                                            type="text" id="subtotal_{{ $rowId }}" value="Rp {{ number_format($detail->qty * $detail->price, 0, ',', '.') }}" readonly disabled>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn-delete-row" onclick="removeRow({{ $rowId }})" title="Hapus Baris">
                                            <i class="mdi mdi-delete-outline"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr id="row_1">
                                    <td class="text-center"><span class="row-number">1</span></td>
                                    <td>
                                        <select class="form-select select2-item" name="itemCode[]" id="itemCode_1" required onchange="onItemChange(1)">
                                            <option value="">Pilih Suku Cadang...</option>
                                            @foreach ($items as $it)
                                                <option value="{{ $it->code }}" data-price="{{ $it->price }}" data-name="{{ $it->name }}">
                                                    {{ $it->code }} - {{ $it->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input class="form-control form-control-custom text-center" type="number" min="0.5" step="0.5" name="qty[]" id="qty_1" value="1" required oninput="recalculateRow(1)">
                                    </td>
                                    <td>
                                        <input class="form-control form-control-custom text-end" type="text" name="price[]" id="price_1" value="0" required oninput="formatRupiahInput(this); recalculateRow(1)">
                                    </td>
                                    <td>
                                        <input class="form-control form-control-custom" type="text" name="description[]" id="description_1" placeholder="Keterangan...">
                                    </td>
                                    <td>
                                        <input class="form-control form-control-custom text-end font-monospace fw-bold" type="text" id="subtotal_1" value="Rp 0" readonly disabled>
                                    </td>
                                    <td class="text-center"></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Summary Panel --}}
                <div class="summary-panel">
                    <div class="summary-item">
                        <div class="summary-label">Total Jenis Item</div>
                        <div class="summary-value" id="summaryTotalItems">{{ count($data->details) }}</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Total Kuantitas (Qty)</div>
                        <div class="summary-value" id="summaryTotalQty">{{ number_format($data->details->sum('qty'), 1, ',', '.') }}</div>
                    </div>
                    <div class="summary-item grand-total-box">
                        <div class="summary-label" style="color: #93c5fd;">Grand Total Biaya</div>
                        <div class="summary-value" id="summaryGrandTotal">Rp {{ number_format($data->nominal, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit Button Card --}}
        <div class="card direct-card">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fs-12">
                        <i class="mdi mdi-information-outline me-1"></i>Perubahan akan otomatis memperbarui histori stok & pemeliharaan armada
                    </span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route($view . 'index') }}" class="btn btn-secondary px-4 rounded-pill">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill fw-bold" id="btnSubmit">
                        <i class="mdi mdi-content-save me-1"></i> Perbarui Data
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('script')
    <script src="{{ asset('assets/js/sweet-alert/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>

    {{-- Flash message server → SweetAlert2 --}}
    @include('purchasing.purchase.partials.flash-swal')

    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>

    <script>
        let rowCount = {{ max(1, count($data->details)) }};

        const itemsData = @json($items->map(fn($it) => [
            'code' => $it->code,
            'name' => $it->name,
            'price' => $it->latestPurchase->price ?? ($it->price ?? 0)
        ]));

        $(document).ready(function() {
            // Select2 Init
            $('.select2-basic').select2({
                placeholder: 'Pilih opsi...',
                allowClear: true,
                width: '100%'
            });

            $('.select2-item').each(function() {
                $(this).select2({
                    placeholder: 'Pilih Suku Cadang...',
                    allowClear: true,
                    width: '100%'
                });
            });

            updateSummary();

            // Add row
            $('#btnAddRow').on('click', function() {
                addRow();
            });

            // Form Submit Protection
            $('#formDirectPurchase').on('submit', function(e) {
                const totalRows = $('#itemsBody tr').length;
                if (totalRows === 0) {
                    e.preventDefault();
                    Swal.fire('Peringatan', 'Minimal harus memasukkan 1 baris suku cadang.', 'warning');
                    return false;
                }

                $('#btnSubmit').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span> Memperbarui...');
            });
        });

        function initSelect2Item(id) {
            $(`#itemCode_${id}`).select2({
                placeholder: 'Pilih Suku Cadang...',
                allowClear: true,
                width: '100%'
            });
        }

        function onItemChange(id) {
            const select = $(`#itemCode_${id}`);
            const selectedOpt = select.find('option:selected');
            const price = selectedOpt.data('price') || 0;

            if (price > 0) {
                $(`#price_${id}`).val(numberFormat(price));
            }
            recalculateRow(id);
        }

        function formatRupiahInput(input) {
            let val = input.value.replace(/[^0-9]/g, '');
            input.value = val ? numberFormat(parseInt(val, 10)) : '0';
        }

        function numberFormat(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        }

        function recalculateRow(id) {
            const qty = parseFloat($(`#qty_${id}`).val()) || 0;
            const priceRaw = $(`#price_${id}`).val().replace(/[^0-9]/g, '');
            const price = parseInt(priceRaw, 10) || 0;
            const subtotal = qty * price;

            $(`#subtotal_${id}`).val('Rp ' + numberFormat(subtotal));
            updateSummary();
        }

        function updateSummary() {
            let totalItems = 0;
            let totalQty = 0;
            let grandTotal = 0;

            $('#itemsBody tr').each(function() {
                const rowId = $(this).attr('id').replace('row_', '');
                const itemCode = $(`#itemCode_${rowId}`).val();
                if (itemCode) {
                    totalItems++;
                }

                const qty = parseFloat($(`#qty_${rowId}`).val()) || 0;
                totalQty += qty;

                const priceRaw = $(`#price_${rowId}`).val().replace(/[^0-9]/g, '');
                const price = parseInt(priceRaw, 10) || 0;
                grandTotal += (qty * price);
            });

            $('#summaryTotalItems').text(totalItems);
            $('#summaryTotalQty').text(totalQty.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 1 }));
            $('#summaryGrandTotal').text('Rp ' + numberFormat(grandTotal));
        }

        function addRow() {
            rowCount++;
            const id = rowCount;

            let optionsHtml = '<option value="">Pilih Suku Cadang...</option>';
            itemsData.forEach(function(item) {
                optionsHtml += `<option value="${item.code}" data-price="${item.price}" data-name="${item.name}">${item.code} - ${item.name}</option>`;
            });

            const html = `
                <tr id="row_${id}">
                    <td class="text-center">
                        <span class="row-number">${$('#itemsBody tr').length + 1}</span>
                    </td>
                    <td>
                        <select class="form-select select2-item" name="itemCode[]" id="itemCode_${id}" required onchange="onItemChange(${id})">
                            ${optionsHtml}
                        </select>
                    </td>
                    <td>
                        <input class="form-control form-control-custom text-center" type="number"
                            min="0.5" step="0.5" name="qty[]" id="qty_${id}" value="1" required
                            oninput="recalculateRow(${id})" onchange="recalculateRow(${id})">
                    </td>
                    <td>
                        <input class="form-control form-control-custom text-end" type="text"
                            name="price[]" id="price_${id}" value="0" required
                            oninput="formatRupiahInput(this); recalculateRow(${id})" onchange="recalculateRow(${id})">
                    </td>
                    <td>
                        <input class="form-control form-control-custom" type="text"
                            name="description[]" id="description_${id}" placeholder="Keterangan...">
                    </td>
                    <td>
                        <input class="form-control form-control-custom text-end font-monospace fw-bold"
                            type="text" id="subtotal_${id}" value="Rp 0" readonly disabled>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn-delete-row" onclick="removeRow(${id})" title="Hapus Baris">
                            <i class="mdi mdi-delete-outline"></i>
                        </button>
                    </td>
                </tr>
            `;

            $('#itemsBody').append(html);
            initSelect2Item(id);
            renumberRows();
            updateSummary();
        }

        function removeRow(id) {
            if ($('#itemsBody tr').length <= 1) {
                Swal.fire('Info', 'Minimal harus ada 1 baris suku cadang.', 'info');
                return;
            }
            $(`#row_${id}`).remove();
            renumberRows();
            updateSummary();
        }

        function renumberRows() {
            $('#itemsBody tr').each(function(index) {
                $(this).find('.row-number').text(index + 1);
            });
        }
    </script>
@endpush
