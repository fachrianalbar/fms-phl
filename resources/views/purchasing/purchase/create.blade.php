@extends('layouts.main', [
'title' => $title,
'pageTitle' => $title,
'firstSegment' => $title,
'secondSegment' => __('general.add'),
])

@push('style')
<link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

<link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
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

<style>
    /* ===== Card Enhancements ===== */
    .purchase-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        overflow: hidden;
        transition: box-shadow 0.3s ease;
    }
    .purchase-card:hover {
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    .purchase-card .card-header {
        background: linear-gradient(135deg, #f8f9fe 0%, #eef1f8 100%);
        border-bottom: 1px solid #e8ecf3;
        padding: 1rem 1.5rem;
    }
    .purchase-card .card-header h4 {
        font-size: 1.05rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .purchase-card .card-header h4 .header-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
    .header-icon-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
    }
    .header-icon-info {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        color: #fff;
    }
    .purchase-card .card-body {
        padding: 1.5rem;
    }

    /* ===== Form Enhancements ===== */
    .form-label-custom {
        font-size: 0.8rem;
        font-weight: 600;
        color: #596780;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 0.4rem;
    }
    .form-label-custom .required-dot {
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #e74c3c;
        margin-left: 4px;
        vertical-align: middle;
    }
    .form-control-custom {
        border: 1.5px solid #e0e5ec;
        border-radius: 8px;
        padding: 0.55rem 0.85rem;
        font-size: 0.92rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        background: #fafbfc;
    }
    .form-control-custom:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102,126,234,0.12);
        background: #fff;
    }
    .form-control-custom:read-only {
        background: #f0f2f5;
        color: #6c757d;
    }

    /* ===== Badge ===== */
    .badge-new {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.3em 0.8em;
        border-radius: 20px;
        letter-spacing: 0.3px;
    }

    /* ===== Table Enhancements ===== */
    .purchase-table-wrapper {
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #e8ecf3;
    }
    .purchase-table {
        margin-bottom: 0;
        border-collapse: collapse;
    }
    .purchase-table thead th {
        background: linear-gradient(135deg, #f0f3ff 0%, #e8ecf8 100%);
        border-bottom: 2px solid #d5dbe8;
        font-size: 0.78rem;
        font-weight: 700;
        color: #4a5568;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.75rem 0.6rem;
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 2;
    }
    .purchase-table tbody tr {
        transition: background 0.2s ease;
        border-bottom: 1px solid #f0f2f5;
    }
    .purchase-table tbody tr:hover {
        background: #f8f9ff;
    }
    .purchase-table tbody td {
        padding: 0.55rem 0.6rem;
        vertical-align: middle;
        font-size: 0.9rem;
    }
    .purchase-table .row-number {
        width: 40px;
        height: 28px;
        border-radius: 6px;
        background: #eef1f8;
        color: #596780;
        font-weight: 700;
        font-size: 0.8rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .purchase-table .form-control {
        border-radius: 6px;
        border: 1.5px solid #e0e5ec;
        font-size: 0.88rem;
        padding: 0.4rem 0.6rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .purchase-table .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 2px rgba(102,126,234,0.1);
    }
    .purchase-table .form-control[readonly] {
        background: #f0f4ff;
        color: #4a5568;
        font-weight: 600;
        border-color: #d5dbe8;
    }

    /* ===== Delete button ===== */
    .btn-delete-row {
        width: 30px;
        height: 30px;
        border-radius: 6px;
        border: none;
        background: #fff1f0;
        color: #e74c3c;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-delete-row:hover {
        background: #e74c3c;
        color: #fff;
        transform: scale(1.1);
    }

    /* ===== Add Item Button ===== */
    .btn-add-item {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: #fff;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.5rem 1.2rem;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(102,126,234,0.3);
    }
    .btn-add-item:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(102,126,234,0.4);
        color: #fff;
    }
    .btn-add-item .plus-icon {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: rgba(255,255,255,0.25);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        font-weight: 700;
    }

    /* ===== Summary Panel ===== */
    .summary-panel {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        margin-top: 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .summary-item {
        text-align: center;
        flex: 1;
        min-width: 140px;
    }
    .summary-item .summary-label {
        font-size: 0.72rem;
        font-weight: 600;
        color: rgba(255,255,255,0.7);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 0.3rem;
    }
    .summary-item .summary-value {
        font-size: 1.4rem;
        font-weight: 800;
        color: #fff;
        line-height: 1.2;
        transition: all 0.3s ease;
    }
    .summary-item .summary-value.updated {
        transform: scale(1.08);
    }
    .summary-divider {
        width: 1px;
        height: 40px;
        background: rgba(255,255,255,0.2);
    }
    .grand-total-highlight {
        background: rgba(255,255,255,0.15);
        border-radius: 10px;
        padding: 0.5rem 1.2rem;
    }
    .grand-total-highlight .summary-value {
        font-size: 1.6rem;
    }

    /* ===== Submit Button ===== */
    .btn-submit-purchase {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        border: none;
        color: #1a5c32;
        font-weight: 700;
        font-size: 0.95rem;
        padding: 0.7rem 2rem;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        box-shadow: 0 3px 12px rgba(67,233,123,0.3);
    }
    .btn-submit-purchase:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(67,233,123,0.4);
        color: #1a5c32;
    }
    .btn-submit-purchase:active {
        transform: translateY(0);
    }

    /* ===== Back Button ===== */
    .btn-back {
        background: #fff;
        border: 1.5px solid #e0e5ec;
        color: #596780;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.45rem 1rem;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: all 0.2s ease;
    }
    .btn-back:hover {
        background: #f8f9fe;
        border-color: #667eea;
        color: #667eea;
    }

    /* ===== Row animation ===== */
    @keyframes slideInRow {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .purchase-table tbody tr.new-row {
        animation: slideInRow 0.35s ease forwards;
    }

    /* ===== Select2 Override ===== */
    .purchase-table .select2-container--default .select2-selection--single {
        border: 1.5px solid #e0e5ec;
        border-radius: 6px;
        height: 34px;
    }
    .purchase-table .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 32px;
        font-size: 0.88rem;
    }
    .purchase-table .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 32px;
    }

    /* ===== Empty state ===== */
    .empty-state-text {
        text-align: center;
        color: #a0aec0;
        font-size: 0.9rem;
        padding: 2rem 1rem;
    }
</style>
@endpush

@section('content')
<form method="post" action="{{ route($view . 'store') }}">
    @csrf
    <div class="col-sm-12">

        {{-- ===== HEADER CARD: Purchase Info ===== --}}
        <div class="card purchase-card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>
                    <span class="header-icon header-icon-primary">
                        <i class="mdi mdi-cart-plus"></i>
                    </span>
                    {{ $title }} — {{ __('general.add_data') }}
                    <span class="badge-new ms-2">New</span>
                </h4>
                <a href="{{ route($view . 'index') }}" class="btn-back">
                    <i class="mdi mdi-arrow-left"></i>
                    {{ __('general.back_to_list') }}
                </a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    {{-- Code --}}
                    <div class="col-md-4">
                        <label class="form-label form-label-custom" for="code">
                            Code <span class="required-dot"></span>
                        </label>
                        <input class="form-control form-control-custom" type="text" placeholder="Auto-generated" id="code_display" readonly disabled>
                        <input type="hidden" name="code" id="code_hidden">
                    </div>

                    {{-- Date --}}
                    <div class="col-md-4">
                        <label class="form-label form-label-custom" for="date">
                            {{ __('menu_purchase.date') }} <span class="required-dot"></span>
                        </label>
                        <input class="form-control form-control-custom" name="date" id="datetime-local" type="date" required
                            placeholder="Purchase Date" value="{{ now()->toDateString() }}">
                    </div>

                    {{-- Time --}}
                    <div class="col-md-4">
                        <label class="form-label form-label-custom">
                            {{ __('menu_purchase.time') }}
                        </label>
                        <input class="form-control form-control-custom digits" name="time" type="time"
                            value="{{ now()->setTimezone('Asia/Jakarta')->format('H:i') }}">
                    </div>

                    {{-- Supplier --}}
                    <div class="col-md-6">
                        <label class="form-label form-label-custom" for="supplierCode">
                            Supplier <span class="required-dot"></span>
                        </label>
                        <select class="js-example-basic-single" name="supplierCode" id="supplierCode" required>
                            <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                            @foreach ($supplier as $item)
                            <option value="{{ $item->code }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Due Date --}}
                    <div class="col-md-6">
                        <label class="form-label form-label-custom" for="dueDate">
                            {{ __('menu_purchase.due_date') }} <span class="required-dot"></span>
                        </label>
                        <input class="form-control form-control-custom" name="dueDate" id="dueDate" type="date" required
                            placeholder="{{ __('menu_purchase.due_date') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== DETAIL CARD: Items Table ===== --}}
        <div class="card purchase-card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>
                    <span class="header-icon header-icon-info">
                        <i class="mdi mdi-format-list-bulleted"></i>
                    </span>
                    Detail Purchasing
                </h4>
                <button class="btn-add-item" type="button" id="save">
                    <span class="plus-icon">+</span>
                    {{ __('general.add_data') }}
                </button>
            </div>
            <div class="card-body">
                <div class="purchase-table-wrapper">
                    <table class="table purchase-table" id="purchase-items-table" style="width: 100%">
                        <thead>
                            <tr>
                                <th style="width: 50px" class="text-center">#</th>
                                <th style="width: 35%">Item / Part</th>
                                <th style="width: 10%" class="text-center">Qty</th>
                                <th style="width: 15%" class="text-end">{{ __('menu_maintenance.prices') }}</th>
                                <th style="width: 15%">Description</th>
                                <th style="width: 15%" class="text-end">{{ __('menu_maintenance.total_prices') }}</th>
                                <th style="width: 50px" class="text-center"></th>
                            </tr>
                        </thead>
                        <tbody id="purchaseDetails">
                            <tr>
                                <td class="text-center">
                                    <span class="row-number">1</span>
                                </td>
                                <td>
                                    <select class="js-example-basic-single" name="itemCode[]" id="itemCode_1" required
                                        onchange="loadItemDetails(1)">
                                        <option selected="" disabled="" value="">
                                            {{ __('general.choose') }}...
                                        </option>
                                        @foreach ($items as $it)
                                        <option value="{{ $it->code }}" data-name="{{ $it->name }}"
                                            data-price="{{ $it->latestPurchase->price ?? 0 }}">
                                            {{ $it->code . ' - ' . $it->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input class="form-control text-center" type="number" min="0.5" step="0.5" name="qty[]"
                                        id="qty_1" value="1" required oninput="updateTotalPrice(1)" onchange="updateTotalPrice(1)">
                                </td>
                                <td>
                                    <input class="form-control text-end" type="text" id="price_1"
                                        oninput="formatAngka(this); updateTotalPrice(1)" onchange="updateTotalPrice(1)" name="price[]"
                                        required value="0">
                                </td>
                                <td>
                                    <input class="form-control" type="text" name="description[]" id="description_1" placeholder="Keterangan...">
                                </td>
                                <td>
                                    <input class="form-control text-end" type="text" id="totalPrice_1" readonly
                                        value="0">
                                </td>
                                <td class="text-center">
                                    {{-- First row: no delete --}}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- ===== SUMMARY PANEL ===== --}}
                <div class="summary-panel" id="summaryPanel">
                    <div class="summary-item">
                        <div class="summary-label">Total Items</div>
                        <div class="summary-value" id="summaryTotalItems">1</div>
                    </div>
                    <div class="summary-divider d-none d-md-block"></div>
                    <div class="summary-item">
                        <div class="summary-label">Total Qty</div>
                        <div class="summary-value" id="summaryTotalQty">1</div>
                    </div>
                    <div class="summary-divider d-none d-md-block"></div>
                    <div class="summary-item grand-total-highlight">
                        <div class="summary-label">Grand Total</div>
                        <div class="summary-value" id="summaryGrandTotal">Rp 0</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== SUBMIT CARD ===== --}}
        <div class="card purchase-card">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div class="text-muted" style="font-size: 0.85rem;">
                    <i class="mdi mdi-information-outline me-1"></i>
                    Pastikan semua item sudah benar sebelum menyimpan.
                </div>
                <button class="btn-submit-purchase" id="submit" type="submit">
                    <i class="mdi mdi-check-circle-outline"></i>
                    {{ __('general.save_changes') }}
                </button>
            </div>
        </div>

    </div>
</form>
@endsection

@push('script')
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
<script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>

<!-- dataTables.bootstrap5 -->
<script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>

<!-- dataTables.keyTable -->
<script src="{{ asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-keytable-bs5/js/keyTable.bootstrap5.min.js') }}"></script>

<!-- dataTable.responsive -->
<script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>

<!-- dataTables.select -->
<script src="{{ asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-select-bs5/js/select.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
<script src=" {{ asset('assets/js/helper.js') }}"></script>


<script>
    let dataItem;
    let itemsData = @json($items);
    let rowCounter = 1;

    $(document).ready(function() {
        generateCode('input[name="date"]', '#code_display', '#code_hidden', '/ajax/purchase-generate-code');
        updateGrandTotal();
    });

    $('input[name="date"]').on('change', function() {
        generateCode('input[name="date"]', '#code_display', '#code_hidden', '/ajax/purchase-generate-code');
    });


    // Load items based on supplier
    function itemBySupplier(supplierCode) {
        let html = '<option selected="" disabled="" value="">{{ __("general.choose") }}...</option>';
        $('#itemCode_1').html(html);

        $.get("{{ url('ajax/item-by-supplier') }}/" + supplierCode, function(data) {
            data.forEach(i => {
                html +=
                    `<option value="${i.code}" data-name="${i.name}" data-price="${i.price}">${i.code} - ${i.name}</option>`;
            });

            let row = $('#purchaseDetails tr').length + 1;

            // Update all itemCode select dropdowns with the loaded data
            for (let i = 2; i <= row; i++) {
                $(`#itemCode_${i}`).html(html);
                // Reinitialize select2 for newly added select elements
                $(`#itemCode_${i}`).select2();
            }

            $('#itemCode_1').html(html);
            $('#itemName_1').val("");
            $('#qty_1').val(1);
            $('#price_1').val(0)
            $('#totalPrice_1').val(0)

            // Reinitialize select2 for the first row
            $('#itemCode_1').select2();
            updateGrandTotal();
        });
    }

    // Attach validation to the save button
    $('#submit').on('click', function(e) {
        let isValid = true;
        let errorMessage = '';
        let codes = [];
        let isDuplicate = false;

        // Loop through each row to validate quantities
        $('#purchaseDetails tr').each(function() {
            let code = $(this).find('select[name="itemCode[]"]').val();
            let itemName = $(this).find('select[name="itemCode[]"] option:selected').data('name');

            // Check for duplicate codes
            if (codes.includes(code)) {
                isDuplicate = true;
                errorMessage =
                    `Duplicate entry detected for the item "${itemName}". Please remove the duplicate.`;
                isValid = false;
                return false; // Exit loop early
            }
            codes.push(code);
        });

        // If not valid, show alert and prevent form submission
        if (!isValid) {
            e.preventDefault();

            swal({
                title: "{{ __('general.warning') }}",
                text: errorMessage,
                icon: "warning",
            })
            return
        }
    });

    // Load item details (name, price)
    function loadItemDetails(row) {
        let itemCode = $(`#itemCode_${row}`).val();
        let itemName = $(`#itemCode_${row} option:selected`).data('name');
        let itemPrice = $(`#itemCode_${row} option:selected`).data('price');

        itemPrice = new Intl.NumberFormat('id-ID').format(Math.round(itemPrice));

        $(`#itemName_${row}`).val(itemName);
        $(`#price_${row}`).val(itemPrice);
        updateTotalPrice(row);
    }

    // Update total price based on quantity
    function updateTotalPrice(row) {
        let qty = parseFloat($(`#qty_${row}`).val()) || 0;
        let price = $(`#price_${row}`).val();
        let priceNumber = parseInt(price.replace(/\./g, '')) || 0;
        let totalPrice = qty * priceNumber;

        totalPrice = new Intl.NumberFormat('id-ID').format(Math.round(totalPrice));

        $(`#totalPrice_${row}`).val(totalPrice);
        updateGrandTotal();
    }

    // ===== REAL-TIME GRAND TOTAL =====
    function updateGrandTotal() {
        let totalItems = 0;
        let totalQty = 0;
        let grandTotal = 0;

        $('#purchaseDetails tr').each(function() {
            totalItems++;

            let qty = parseFloat($(this).find('input[name="qty[]"]').val()) || 0;
            totalQty += qty;

            let totalPriceStr = $(this).find('input[id^="totalPrice_"]').val() || '0';
            let totalPriceNum = parseInt(totalPriceStr.replace(/\./g, '')) || 0;
            grandTotal += totalPriceNum;
        });

        // Animate the update
        let $items = $('#summaryTotalItems');
        let $qty = $('#summaryTotalQty');
        let $grand = $('#summaryGrandTotal');

        $items.text(totalItems);
        $qty.text(totalQty % 1 === 0 ? totalItems === 0 ? '0' : totalQty : totalQty.toFixed(1));
        $grand.text('Rp ' + new Intl.NumberFormat('id-ID').format(grandTotal));

        // Pulse animation
        [$items, $qty, $grand].forEach($el => {
            $el.addClass('updated');
            setTimeout(() => $el.removeClass('updated'), 300);
        });

        // Update row numbers
        updateRowNumbers();
    }

    // Update row numbers after add/remove
    function updateRowNumbers() {
        $('#purchaseDetails tr').each(function(index) {
            $(this).find('.row-number').text(index + 1);
        });
    }


    // ===== ADD NEW ROW =====
    $('#save').on('click', function() {
        rowCounter++;
        let row = rowCounter;

        let options = '<option selected="" disabled="" value="">{{ __("general.choose") }}...</option>';

        // Mengisi options berdasarkan data items
        itemsData.forEach(item => {
            const price = item.latest_purchase?.price ?? 0;
            options += `<option value="${item.code}" data-name="${item.name}" data-price="${price}">
                        ${item.code} - ${item.name}
                    </option>`;
        });

        let newRow = `<tr class="new-row">
                         <td class="text-center">
                             <span class="row-number">${$('#purchaseDetails tr').length + 1}</span>
                         </td>
                         <td>
                             <select class="form-control js-example-basic-single" name="itemCode[]" id="itemCode_${row}" required onchange="loadItemDetails(${row})">
                             </select>
                         </td>
                         <td>
                             <input class="form-control text-center" type="number" min="0.5" step="0.5" name="qty[]" id="qty_${row}" value="1" oninput="updateTotalPrice(${row})" onchange="updateTotalPrice(${row})">
                         </td>
                         <td>
                             <input class="form-control text-end" type="text" id="price_${row}" name="price[]" onchange="updateTotalPrice(${row})" oninput="formatAngka(this); updateTotalPrice(${row})" required value="0">
                         </td>
                         <td>
                             <input class="form-control" type="text" name="description[]" id="description_${row}" placeholder="Keterangan...">
                         </td>
                         <td>
                             <input class="form-control text-end" type="text" id="totalPrice_${row}" readonly value="0">
                         </td>
                         <td class="text-center">
                             <button type="button" class="btn-delete-row" onclick="removeDetailRow(${row})" title="Hapus item">
                                 <i class="mdi mdi-delete-outline"></i>
                             </button>
                         </td>
                       </tr>`;

        $('#purchaseDetails').append(newRow);

        $(`#itemCode_${row}`).html(options);
        // Reinitialize select2 for newly added select elements
        $(`#itemCode_${row}`).select2();

        // Remove animation class after it completes
        setTimeout(() => {
            $(`#itemCode_${row}`).closest('tr').removeClass('new-row');
        }, 400);

        updateGrandTotal();
    });

    // Remove row from purchase detail
    function removeDetailRow(row) {
        $(`#itemCode_${row}`).closest('tr').remove();
        updateGrandTotal();
    }

    // Hide remove button on the first row
    $(document).on('click', '.remove-btn', function() {
        if ($('#purchaseDetails tr').length > 1) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
</script>
@endpush
