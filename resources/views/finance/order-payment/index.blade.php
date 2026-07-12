@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Finance',
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

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Data</h4>
            </div>
            <div class="card-body">
                @include('partials.alert')
                <div class="table-responsive custom-scrollbar">
                    <table class="table table-striped w-100 nowrap" id="dt">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Aksi</th>
                                <th>No Order</th>
                                <th>{{ __('menu_vendor_payment.order_date') }}</th>
                                <th>{{ __('menu_vendor_payment.plate_number') }}</th>
                                <th>{{ __('menu_vendor_payment.driver') }}</th>
                                <th>{{ __('menu_vendor_payment.shipment_no') }}</th>
                                <th>{{ __('menu_vendor_payment.customer') }}</th>
                                <th>{{ __('menu_vendor_payment.origin') }}</th>
                                <th>{{ __('menu_vendor_payment.destination') }}</th>
                                <th>Harga</th>
                                <th>Biaya Tambahan</th>
                                <th>PPN</th>
                                <th>PPH</th>
                                <th>Total Harga</th>
                                <th>Jumlah Pembayaran</th>
                                <th>Sisa Pembayaran</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <form class="row g-3" id="payment-form" method="post" action="{{ route($view . 'store') }}">
            @csrf
            <div class="modal fade bd-example-modal-lg" id="payment-modal" tabindex="-1" role="dialog"
                aria-labelledby="myLargeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content border-0 shadow-lg">
                        <input type="hidden" name="orderCode" id="orderCode">
                        
                        <div class="modal-header bg-primary text-white py-3">
                            <h5 class="modal-title fw-bold text-white d-flex align-items-center" id="myLargeModalLabel">
                                <i class="mdi mdi-credit-card-outline me-2 fs-4"></i> Form Pembayaran Order
                            </h5>
                            <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <!-- Left Column: Bill Calculation Summary -->
                                <div class="col-md-5 border-end pe-md-4 mb-3 mb-md-0">
                                    <div class="bg-light p-3 rounded-3 h-100">
                                        <h6 class="fw-bold text-dark mb-3">
                                            <i class="mdi mdi-calculator me-1 text-primary"></i>Kalkulasi Tagihan
                                        </h6>
                                        
                                        <div class="list-group list-group-flush bg-transparent">
                                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-2 bg-transparent">
                                                <span class="text-muted small">Harga Rute</span>
                                                <span class="fw-semibold text-dark small" id="cost_label">Rp 0</span>
                                            </div>
                                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-2 bg-transparent">
                                                <span class="text-muted small">Biaya Tambahan</span>
                                                <span class="fw-semibold text-dark small" id="additional_cost_label">Rp 0</span>
                                            </div>
                                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-2 bg-transparent">
                                                <span class="fw-bold text-dark small">Subtotal</span>
                                                <span class="fw-bold text-dark small" id="subtotal_label">Rp 0</span>
                                            </div>
                                            
                                            <hr class="my-2 text-muted opacity-25">
                                            
                                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-2 bg-transparent">
                                                <span class="text-muted small">PPN</span>
                                                <span class="fw-semibold text-success small" id="ppn_label">+ Rp 0</span>
                                            </div>
                                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-2 bg-transparent">
                                                <span class="text-muted small">PPH</span>
                                                <span class="fw-semibold text-danger small" id="pph_label">- Rp 0</span>
                                            </div>
                                            
                                            <hr class="my-2 text-muted opacity-25">
                                            
                                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-2 bg-transparent">
                                                <span class="fw-bold text-dark">Total Tagihan</span>
                                                <span class="fw-bold text-dark" id="grand_total_label">Rp 0</span>
                                            </div>
                                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-2 bg-transparent">
                                                <span class="text-muted small">Sudah Dibayar</span>
                                                <span class="fw-semibold text-dark small" id="payment_label">Rp 0</span>
                                            </div>
                                            
                                            <hr class="my-2 text-muted opacity-25">
                                            
                                            <div class="p-3 rounded-3 text-center" id="sisa_tagihan_container">
                                                <span class="small d-block fw-bold mb-1">Sisa Tagihan</span>
                                                <span class="fs-5 fw-bold" id="sisa_tagihan_value">Rp 0</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Right Column: Payment Inputs -->
                                <div class="col-md-7 ps-md-4">
                                    <h6 class="fw-bold text-dark mb-3">
                                        <i class="mdi mdi-cash-multiple me-1 text-primary"></i>Input Pembayaran
                                    </h6>
                                    
                                    <!-- Hidden Inputs expected by service / controller -->
                                    <input type="hidden" name="cost" id="costHidden">
                                    <input type="hidden" name="additional_cost" id="additional_costHidden">
                                    <input type="hidden" name="ppn" id="ppnHidden">
                                    <input type="hidden" name="pph" id="pphHidden">
                                    <input type="hidden" name="total" id="totalHidden">
                                    <input type="hidden" name="type" id="type" value="Full">
                                    <input type="hidden" name="paymentAmount" id="paymentAmountHidden">
                                    
                                    <div class="row g-3">
                                        <!-- Switches for PPN & PPH -->
                                        <div class="col-12 d-flex gap-3 mb-2 flex-wrap">
                                            <div id="ppn-checkbox-container" style="display: none;">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="usePpn" checked>
                                                    <label class="form-check-label fw-semibold text-dark small" for="usePpn">Gunakan PPN</label>
                                                </div>
                                            </div>
                                            <div id="pph-checkbox-container" style="display: none;">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="usePph" checked>
                                                    <label class="form-check-label fw-semibold text-dark small" for="usePph">Gunakan PPH</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-semibold text-dark small mb-1" for="userBankCode">
                                                Bank Tujuan Transfer <span class="text-danger">*</span>
                                            </label>
                                            <select class="js-example-basic" name="userBankCode" id="userBankCode" required>
                                                <option value="">{{ __('general.choose') }}...</option>
                                                @foreach ($userBank as $item)
                                                    <option value="{{ $item->code }}">
                                                        {{ $item->accountNumber . ' - ' . $item->bank->name . ' - ' . $item->accountName }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label class="form-label fw-semibold text-dark small mb-1" for="date">
                                                Tanggal Pembayaran <span class="text-danger">*</span>
                                            </label>
                                            <input class="form-control" name="date" id="date" type="date" required value="{{ date('Y-m-d') }}">
                                        </div>
                                        
                                        <div class="col-12">
                                            <label class="form-label fw-semibold text-dark small mb-1" for="nominalInput">
                                                Nominal Pembayaran <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light text-muted fw-bold">Rp</span>
                                                <input class="form-control" id="nominalInput" type="text"
                                                    placeholder="Masukkan nominal" oninput="formatAngka(this)" required>
                                                <button class="btn btn-primary fw-bold" type="button" id="btn-pay-all">
                                                    Bayar Lunas
                                                </button>
                                            </div>
                                            <div class="form-text text-muted small mt-1">
                                                Tekan tombol <strong class="text-primary">Bayar Lunas</strong> untuk mengisi sisa tagihan secara otomatis.
                                            </div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label class="form-label fw-semibold text-dark small mb-1" for="description">
                                                Keterangan / Catatan
                                            </label>
                                            <textarea class="form-control" name="description" id="description" rows="2"
                                                placeholder="Tambahkan catatan transfer (opsional)"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="modal-footer bg-light py-3 d-flex justify-content-end gap-2">
                            <button class="btn btn-outline-secondary px-4" type="button" data-bs-dismiss="modal">Batal</button>
                            <button class="btn btn-primary px-4 fw-bold" type="submit">{{ __('general.save') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>



    <form id="delete-form" method="post">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('script')
    <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>

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
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script src=" {{ asset('assets/js/helper.js') }}"></script>

    <script>
        let currentCost = 0;
        let currentAdditionalCost = 0;
        let originalPpn = 0;
        let originalPph = 0;
        let currentPaid = 0;

        $(document).ready(function() {
            $('#dt').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('dt.order-payment') }}",
                },
                "columns": [
                    { "data": 'DT_RowIndex' },
                    { "data": 'action', "orderable": false, "searchable": false },
                    { "data": 'code' },
                    { "data": 'orderDate' },
                    { "data": 'fleet.plateNumber' },
                    { "data": 'driver.name' },
                    { "data": 'shipmentNumber' },
                    { "data": 'customer.name' },
                    { "data": 'route.originLocation.name' },
                    { "data": 'route.destinationLocation.name' },
                    { "data": 'cost' },
                    { "data": 'additional_cost' },
                    { "data": 'ppn' },
                    { "data": 'pph' },
                    { "data": 'grand_total' },
                    { "data": 'paymentAmount' },
                    { "data": 'total' },
                    { "data": 'paymentStatus' }
                ],
                "columnDefs": [{
                        "searchable": false,
                        "targets": [0, 1]
                    },
                    {
                        "orderable": false,
                        "targets": [0, 1]
                    }
                ],
                "order": [
                    [2, 'asc']
                ]
            });
        });

        function showModal(code) {
            $('#payment-modal').modal('show');
            $('.js-example-basic').select2({
                dropdownParent: $('#payment-modal'),
                width: "100%",
            });
            $('#orderCode').val(code);
            getorderPaymentDetail(code);
        }

        function formatAngkaValue(value) {
            if (value == null) return ""; // hindari error kalau undefined/null
            let angka = Math.round(value).toString().replace(/\./g, "");
            return new Intl.NumberFormat("id-ID").format(angka);
        }

        function getorderPaymentDetail(orderCode) {
            $.get("/ajax/order-detail-payment/" + orderCode, function(data) {
                currentCost = parseFloat(data.cost) || 0;
                currentAdditionalCost = parseFloat(data.additional_cost) || 0;
                originalPpn = parseFloat(data.ppn) || 0;
                originalPph = parseFloat(data.pph) || 0;
                currentPaid = parseFloat(data.payment) || 0;

                // Show/hide usePpn container based on whether there is PPN in the data
                if (originalPpn > 0) {
                    $('#ppn-checkbox-container').show();
                    $('#usePpn').prop('checked', true);
                } else {
                    $('#ppn-checkbox-container').hide();
                    $('#usePpn').prop('checked', false);
                }

                // Show/hide usePph container based on whether there is PPH in the data
                if (originalPph > 0) {
                    $('#pph-checkbox-container').show();
                    $('#usePph').prop('checked', true);
                } else {
                    $('#pph-checkbox-container').hide();
                    $('#usePph').prop('checked', false);
                }

                updateCalculatedTotal();

                // Auto-fill nominalInput with the sisaTagihan by default
                let activePpn = $('#usePpn').is(':checked') ? originalPpn : 0;
                let activePph = $('#usePph').is(':checked') ? originalPph : 0;
                let subtotal = currentCost + currentAdditionalCost;
                let sisaTagihan = subtotal + activePpn - activePph - currentPaid;
                if (sisaTagihan < 0) sisaTagihan = 0;
                $('#nominalInput').val(formatAngkaValue(sisaTagihan));
            });
        }

        function updateCalculatedTotal() {
            let activePpn = 0;
            if ($('#usePpn').is(':checked')) {
                activePpn = originalPpn;
            }

            let activePph = 0;
            if ($('#usePph').is(':checked')) {
                activePph = originalPph;
            }

            let subtotal = currentCost + currentAdditionalCost;
            let grandTotal = subtotal + activePpn - activePph;
            let sisaTagihan = grandTotal - currentPaid;

            // Update labels
            $('#cost_label').text('Rp ' + formatAngkaValue(currentCost));
            $('#additional_cost_label').text('Rp ' + formatAngkaValue(currentAdditionalCost));
            $('#subtotal_label').text('Rp ' + formatAngkaValue(subtotal));
            $('#ppn_label').text('+ Rp ' + formatAngkaValue(activePpn));
            $('#pph_label').text('- Rp ' + formatAngkaValue(activePph));
            $('#grand_total_label').text('Rp ' + formatAngkaValue(grandTotal));
            $('#payment_label').text('Rp ' + formatAngkaValue(currentPaid));

            if (sisaTagihan < 0) {
                $('#sisa_tagihan_container')
                    .removeClass('bg-danger-subtle text-danger border-danger-subtle bg-success-subtle text-success border-success-subtle')
                    .addClass('bg-info-subtle text-info border-info-subtle');
                $('#sisa_tagihan_container span.small').text('Kelebihan Bayar');
                $('#sisa_tagihan_value').text('Rp ' + formatAngkaValue(Math.abs(sisaTagihan)));
            } else if (sisaTagihan > 0) {
                $('#sisa_tagihan_container')
                    .removeClass('bg-success-subtle text-success border-success-subtle bg-info-subtle text-info border-info-subtle')
                    .addClass('bg-danger-subtle text-danger border-danger-subtle');
                $('#sisa_tagihan_container span.small').text('Sisa Tagihan');
                $('#sisa_tagihan_value').text('Rp ' + formatAngkaValue(sisaTagihan));
            } else {
                $('#sisa_tagihan_container')
                    .removeClass('bg-danger-subtle text-danger border-danger-subtle bg-info-subtle text-info border-info-subtle')
                    .addClass('bg-success-subtle text-success border-success-subtle');
                $('#sisa_tagihan_container span.small').text('Lunas');
                $('#sisa_tagihan_value').text('Rp 0');
            }

            // Update hidden fields
            $('#costHidden').val(currentCost);
            $('#additional_costHidden').val(currentAdditionalCost);
            $('#ppnHidden').val(activePpn);
            $('#pphHidden').val(activePph);
            $('#totalHidden').val(sisaTagihan < 0 ? 0 : sisaTagihan);
        }

        // Handle PPN switch change
        $(document).on('change', '#usePpn', function() {
            updateCalculatedTotal();
        });

        // Handle PPH switch change
        $(document).on('change', '#usePph', function() {
            updateCalculatedTotal();
        });

        // Handle Bayar Lunas button click
        $(document).on('click', '#btn-pay-all', function() {
            let activePpn = $('#usePpn').is(':checked') ? originalPpn : 0;
            let activePph = $('#usePph').is(':checked') ? originalPph : 0;
            let subtotal = currentCost + currentAdditionalCost;
            let sisaTagihan = subtotal + activePpn - activePph - currentPaid;
            if (sisaTagihan < 0) sisaTagihan = 0;
            $('#nominalInput').val(formatAngkaValue(sisaTagihan));
        });

        function validateAndFormatForm(paymentAmountId) {
            const total = parseInt(document.getElementById("totalHidden").value) || 0;
            const nominalInput = document.getElementById("nominalInput");
            const paymentAmount = parseInt(nominalInput.value.replace(/\./g, "")) || 0;

            if (paymentAmount <= 0) {
                swal({
                    title: "{{ __('general.warning') }}",
                    text: "Nominal pembayaran harus lebih besar dari 0",
                    icon: "warning",
                });
                return false;
            }

            // Set hidden fields
            document.getElementById("paymentAmountHidden").value = paymentAmount;
            if (paymentAmount >= total) {
                document.getElementById("type").value = "Full";
            } else {
                document.getElementById("type").value = "Dp";
            }

            return true; // Lanjut submit
        }

        $(document).on('submit', '#payment-form', function(e) {
            e.preventDefault();

            if (!validateAndFormatForm()) {
                return;
            }

            let form = $(this);
            let url = form.attr('action');
            let data = form.serialize();

            $.ajax({
                type: 'POST',
                url: url,
                data: data,
                success: function(response) {
                    $('#payment-modal').modal('hide');
                    form.trigger('reset');
                    $('#userBankCode').val('').trigger('change');

                    // Reload DataTable
                    $('#dt').DataTable().ajax.reload(null, false);

                    swal({
                        title: "Berhasil!",
                        text: "Pembayaran berhasil disimpan.",
                        icon: "success",
                        button: "OK",
                    });
                },
                error: function(xhr) {
                    let errorMessage = "Terjadi kesalahan saat menyimpan pembayaran.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    swal({
                        title: "Gagal!",
                        text: errorMessage,
                        icon: "error",
                        button: "OK",
                    });
                }
            });
        });
    </script>
@endpush
