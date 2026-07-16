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
@endpush

@section('content')
<div class="col-sm-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>{{ $title }} Data</h4>
            <div>
                @if (Auth::user()->roleCode === 'SPRADMIN')
                    <button type="button" class="btn btn-warning me-2" id="btn-recalculate-all">
                        <i class="mdi mdi-sync me-1"></i> Hitung Ulang Semua Invoice
                    </button>
                @endif
                <a href="{{ route($view . 'create') }}" class="btn btn-primary">{{ __('general.add_data') }}</a>
            </div>
        </div>
        <div class="card-body">
            @include('partials.alert')
            <div class="table-responsive custom-scrollbar">
                <table class="table table-striped w-100 nowrap" id="dt">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>No</th>
                            <th>{{ __('menu_invoice.invoice_no') }}</th>
                            {{-- <th>{{ __('menu_invoice.receipt_no') }}</th> --}}
                            <th>{{ __('menu_invoice.customer_name') }}</th>
                            <th>{{ __('menu_invoice.invoice_dates') }}</th>
                            <th>{{ __('menu_invoice.total_order') }}</th>
                            <th>{{ __('menu_invoice.price') }}</th>
                            <th>{{ __('menu_invoice.ppn') }}</th>
                            <th>PPh</th>
                            <th>{{ __('menu_invoice.total_billing') }}</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pembayaran Invoice -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <form id="payment-form" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary-subtle py-3 px-4 border-0">
                    <h5 class="modal-title fw-bold text-primary" id="paymentModalLabel">
                        <i class="mdi mdi-cash-register me-2 fs-18"></i>Proses Pembayaran Invoice
                    </h5>
                    <button type="button" class="btn-close text-primary" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="invoiceId" id="invoiceId">
                    <input type="hidden" name="invoiceCode" id="invoiceCode">

                    <!-- Info Invoice Header -->
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div>
                            <span class="text-muted d-block" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Nomor Invoice</span>
                            <span class="fw-bold fs-15 text-primary" id="invoiceNumberText">-</span>
                        </div>
                        <div class="text-end">
                            <span class="text-muted d-block" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Tanggal</span>
                            <span class="fw-semibold text-dark" id="currentDateText">-</span>
                        </div>
                    </div>

                    <!-- Summary Perhitungan (Subtotal, PPN, PPh, Grand Total, Terbayar, Sisa) -->
                    <div class="bg-light rounded p-3 mb-4 border" style="border-radius: 8px;">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Sub Total</span>
                            <span class="fw-semibold text-dark" id="summarySubtotal">0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2" id="summaryPpnRow">
                            <span class="text-muted" id="labelPpn">PPN</span>
                            <span class="fw-semibold text-success" id="summaryPpn">+0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2" id="summaryPphRow">
                            <span class="text-muted" id="labelPph">PPh</span>
                            <span class="fw-semibold text-danger" id="summaryPph">-0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pt-2 border-top">
                            <span class="text-muted">Grand Total</span>
                            <span class="fw-semibold text-dark" id="summaryGrandTotal">0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Terbayar</span>
                            <span class="fw-semibold text-success" id="summaryTotalPaid">0</span>
                        </div>
                        <div class="d-flex justify-content-between pt-2 border-top">
                            <span class="fw-bold text-dark">Sisa Tagihan</span>
                            <span class="fw-bold text-primary fs-16" id="summaryRemaining">0</span>
                        </div>
                    </div>

                    <!-- Form Inputs -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="paymentDate">Tanggal Pembayaran <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="paymentDate" id="paymentDate" required style="border-radius: 6px;">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="amount_display">Jumlah Pembayaran <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0" style="border-radius: 6px 0 0 6px;">Rp</span>
                                <input type="text" class="form-control border-start-0" id="amount_display" required style="border-radius: 0 6px 6px 0;">
                                <input type="hidden" name="amount" id="amount" min="1">
                            </div>
                            <div class="form-text fs-11 text-muted">Maksimal sisa tagihan: <span id="maxAmountText">0</span></div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" for="userBankCode">Bank Tujuan <span class="text-danger">*</span></label>
                            <select class="form-select" name="userBankCode" id="userBankCode" required style="border-radius: 6px;">
                                <option value="">Pilih Bank</option>
                                <option value="" disabled>-- Loading data bank --</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" for="description">Keterangan / Catatan</label>
                            <textarea class="form-control" name="description" id="description" rows="2" placeholder="Tulis catatan pembayaran disini (opsional)..." style="border-radius: 6px;"></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" for="paymentReceipt">Bukti Pembayaran</label>
                            <input type="file" class="form-control" name="paymentReceipt" id="paymentReceipt" style="border-radius: 6px;">
                            <div class="form-text fs-12 text-muted">Upload file bukti pembayaran jika ada (Format: jpg, png, pdf).</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 px-4">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal" style="border-radius: 6px;">Batal</button>
                    <button type="submit" class="btn btn-primary px-4" style="border-radius: 6px;">Proses Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="delete-form" method="post">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('script')
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
{{-- <script src="../assets/js/sweet-alert/app.js"></script> --}}

<script>
    $(document).ready(function() {
        $('#dt').DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "ajax": {
                "url": "{{ route('dt.invoice') }}",
            },
            "columns": [{
                    "data": 'action'
                }, {
                    "data": 'DT_RowIndex'
                },
                {
                    "data": 'invoiceNumber'
                },
                {
                    "data": 'customer.name'
                },
                {
                    "data": 'invoiceDate'
                },
                {
                    "data": 'orderCount'
                },
                {
                    "data": 'price'
                },
                {
                    "data": 'ppn'
                },
                {
                    "data": 'pph'
                },
                {
                    "data": 'totalBilling'
                },
                {
                    "data": 'status'
                },
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
            "order": []
        })
    });

    function deleteData(uuid) {
        var url = "{{ route('finance.invoice.index') }}" + '/' + uuid;
        $('#delete-form').attr('action', url);

        swal({
            title: "{{ __('general.are_you_sure') }}",
            text: "{{ __('general.want_to_delete_this_data') }}",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                $('#delete-form').submit();
            } else {
                swal("{{ __('general.your_data_is_save') }}");
            }
        });
    }

    // Load bank data saat halaman dimuat
    $(document).ready(function() {
        loadBankData();
    });

    function loadBankData() {
        $.ajax({
            url: "{{ route('api.user-bank.company') }}",
            type: "GET",
            success: function(response) {
                let options = '<option value="">Pilih Bank</option>';
                if (response && response.length > 0) {
                    response.forEach(function(bank) {
                        let bankLabel = bank.bank_name || 'Unknown Bank';
                        options += `<option value="${bank.code}">${bankLabel} - ${bank.account_number} (${bank.account_name})</option>`;
                    });
                } else {
                    console.warn('Tidak ada data bank yang ditemukan');
                    options += '<option value="" disabled>Tidak ada data bank</option>';
                }
                $('#userBankCode').html(options);
            },
            error: function(xhr) {
                console.error('Gagal memuat data bank:', xhr.status, xhr.statusText);
                console.error('Response:', xhr.responseText);
                let options = '<option value="">Pilih Bank</option>';
                options += '<option value="" disabled>Error memuat data</option>';
                $('#userBankCode').html(options);
            }
        });
    }

    // Handle tombol pembayaran
    $(document).on('click', '.btn-payment', function() {
        var invoiceId = $(this).data('id');
        var invoiceCode = $(this).data('invoice-code');
        var invoiceNumber = $(this).data('invoice-number');
        var subtotal = parseFloat($(this).data('subtotal') || 0);
        var ppn = parseFloat($(this).data('ppn') || 0);
        var pph = parseFloat($(this).data('pph') || 0);
        var total = parseFloat($(this).data('total') || 0);
        var totalPaid = parseFloat($(this).data('total-paid') || 0);
        var remaining = parseFloat($(this).data('remaining') || 0);

        $('#invoiceId').val(invoiceId);
        $('#invoiceCode').val(invoiceCode);
        $('#invoiceNumberText').text(invoiceNumber);
        
        // Formatter angka id-ID (tanpa Rp)
        let numFormatter = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
        
        $('#summarySubtotal').text(numFormatter.format(subtotal));
        
        if (ppn > 0) {
            $('#summaryPpnRow').show();
            $('#summaryPpn').text('+' + numFormatter.format(ppn));
        } else {
            $('#summaryPpnRow').hide();
        }
        
        if (pph > 0) {
            $('#summaryPphRow').show();
            $('#summaryPph').text('-' + numFormatter.format(pph));
        } else {
            $('#summaryPphRow').hide();
        }
        
        $('#summaryGrandTotal').text(numFormatter.format(total));
        $('#summaryTotalPaid').text(numFormatter.format(totalPaid));
        $('#summaryRemaining').text(numFormatter.format(remaining));
        
        // Set default amount to remaining balance
        $('#amount').val(remaining);
        $('#amount').attr('max', remaining);
        $('#amount_display').val(numFormatter.format(remaining));
        $('#maxAmountText').text(numFormatter.format(remaining));
        
        let today = new Date().toISOString().split('T')[0];
        $('#paymentDate').val(today);
        
        // Format display date
        let dateOptions = { year: 'numeric', month: 'long', day: 'numeric' };
        $('#currentDateText').text(new Date().toLocaleDateString('id-ID', dateOptions));
        
        // Handle real-time input formatting for payment amount
        $(document).off('input', '#amount_display').on('input', '#amount_display', function() {
            let val = $(this).val().replace(/\D/g, '');
            if (val === '') {
                $('#amount').val('');
                $(this).val('');
                return;
            }
            
            let num = parseInt(val, 10) || 0;
            let maxVal = parseFloat($('#amount').attr('max') || 0);
            if (num > maxVal) {
                num = maxVal;
            }
            
            $('#amount').val(num);
            $(this).val(new Intl.NumberFormat('id-ID').format(num));
        });
        
        $('#description').val('');
        $('#paymentReceipt').val('');

        $('#paymentModal').modal('show');
    });

    // Handle submit form pembayaran
    $('#payment-form').on('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(this);
        var invoiceId = $('#invoiceId').val();
        var url = "{{ route('finance.invoice.index') }}/" + invoiceId + "/payment";

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#paymentModal').modal('hide');
                swal({
                    title: "Berhasil!",
                    text: "Pembayaran berhasil diproses",
                    icon: "success",
                }).then(function() {
                    location.reload();
                });
            },
            error: function(xhr) {
                var errorMsg = 'Terjadi kesalahan saat memproses pembayaran';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                swal({
                    title: "Gagal!",
                    text: errorMsg,
                    icon: "error",
                });
            }
        });
    });

    function recalculateInvoice(id) {
        swal({
            title: "Hitung Ulang Invoice?",
            text: "Proses ini akan membatalkan SEMUA pembayaran untuk invoice ini dan mengembalikan status ke CREATE. Anda harus input ulang pembayaran invoice.",
            icon: "warning",
            buttons: {
                cancel: "Batal",
                confirm: {
                    text: "Ya, Hitung Ulang",
                    closeModal: false
                }
            },
            dangerMode: true,
        }).then((willRecalculate) => {
            if (willRecalculate) {
                $.ajax({
                    url: "{{ route('finance.invoice.recalculate', ':id') }}".replace(':id', id),
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        swal({
                            title: "Berhasil!",
                            text: response.message,
                            icon: "success",
                        }).then(function() {
                            $('#dt').DataTable().ajax.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMsg = 'Terjadi kesalahan saat menghitung ulang invoice';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        swal({
                            title: "Gagal!",
                            text: errorMsg,
                            icon: "error",
                        });
                    }
                });
            }
        });
    }

    $(document).on('click', '.btn-suggest-number', function() {
        var id = $(this).data('id');
        var currentNumber = $(this).data('invoice-number');

        swal({
            title: "Memuat...",
            text: "Sedang menghitung nomor invoice yang tidak konflik.",
            icon: "info",
            buttons: false,
            closeOnClickOutside: false,
            closeOnEsc: false
        });

        $.ajax({
            url: "{{ route('ajax.invoice.suggest-number', ':id') }}".replace(':id', id),
            type: "GET",
            success: function(response) {
                swal.close();
                if (response.success) {
                    var suggested = response.suggestedNumber;
                    swal({
                        title: "Saran Nomor Invoice Baru",
                        text: "Nomor saat ini:\n" + currentNumber + "\n\nSaran nomor baru (bebas konflik):\n" + suggested + "\n\nApakah Anda ingin memperbarui nomor invoice ini?",
                        icon: "info",
                        buttons: {
                            cancel: "Batal",
                            confirm: {
                                text: "Ya, Perbarui",
                                value: true
                            }
                        }
                    }).then((willUpdate) => {
                        if (willUpdate) {
                            $.ajax({
                                url: "{{ route('finance.invoice.update-number', ':id') }}".replace(':id', id),
                                type: "POST",
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    invoiceNumber: suggested
                                },
                                success: function(updateResponse) {
                                    swal({
                                        title: "Berhasil!",
                                        text: updateResponse.message,
                                        icon: "success",
                                    }).then(function() {
                                        $('#dt').DataTable().ajax.reload();
                                    });
                                },
                                error: function(xhr) {
                                    let errorMsg = 'Terjadi kesalahan saat mengubah nomor invoice';
                                    if (xhr.responseJSON && xhr.responseJSON.message) {
                                        errorMsg = xhr.responseJSON.message;
                                    }
                                    swal({
                                        title: "Gagal!",
                                        text: errorMsg,
                                        icon: "error",
                                    });
                                }
                            });
                        }
                    });
                } else {
                    swal("Gagal!", "Gagal memuat saran nomor invoice.", "error");
                }
            },
            error: function(xhr) {
                swal.close();
                let errorMsg = 'Terjadi kesalahan saat memuat saran nomor';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                swal("Gagal!", errorMsg, "error");
            }
        });
    });

    @if (Auth::user()->roleCode === 'SPRADMIN')
    $('#btn-recalculate-all').click(function() {
        swal({
            title: "Apakah Anda yakin?",
            text: "Tindakan ini akan menghitung ulang seluruh subtotal, PPN, dan PPh semua invoice berdasarkan data order dan customer terbaru, serta menyesuaikan kembali status pembayaran mereka.",
            icon: "warning",
            buttons: {
                cancel: "Batal",
                confirm: {
                    text: "Ya, Hitung Ulang!",
                    closeModal: false
                }
            },
            dangerMode: true,
        }).then((willRecalculate) => {
            if (willRecalculate) {
                $.ajax({
                    url: "{{ route('finance.invoice.recalculate-all') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        swal("Berhasil!", response.message, "success").then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        let msg = "Terjadi kesalahan saat menghitung ulang invoice.";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        swal("Gagal!", msg, "error");
                    }
                });
            }
        });
    });
    @endif
</script>
@endpush