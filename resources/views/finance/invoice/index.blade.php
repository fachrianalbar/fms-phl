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

            <a href="{{ route($view . 'create') }}" class="btn btn-primary">{{ __('general.add_data') }}</a>

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
        <div class="modal-content">
            <form id="payment-form" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Proses Pembayaran Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="invoiceId" id="invoiceId">
                    <input type="hidden" name="invoiceCode" id="invoiceCode">

                    <div class="mb-3">
                        <label class="form-label">Invoice Number</label>
                        <input type="text" class="form-control" id="invoiceNumber" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Total Tagihan (Invoice Amount + PPN)</label>
                        <input type="text" class="form-control" id="totalBilling" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="paymentDate">Tanggal Pembayaran <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="paymentDate" id="paymentDate" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="amount">Jumlah Pembayaran <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="amount" id="amount" readonly required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="userBankCode">Bank Tujuan <span class="text-danger">*</span></label>
                        <select class="form-select" name="userBankCode" id="userBankCode" required>
                            <option value="">Pilih Bank</option>
                            <option value="" disabled>-- Loading data bank --</option>
                        </select>
                        <small class="form-text text-muted">Data bank akan dimuat otomatis</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="description">Keterangan</label>
                        <textarea class="form-control" name="description" id="description" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="paymentReceipt">Bukti Pembayaran</label>
                        <input type="file" class="form-control" name="paymentReceipt" id="paymentReceipt">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Proses Pembayaran</button>
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
            "order": [
                [2, 'asc']
            ]
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
                console.log('Bank data loaded:', response);
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
        var total = $(this).data('total');

        $('#invoiceId').val(invoiceId);
        $('#invoiceCode').val(invoiceCode);
        $('#invoiceNumber').val(invoiceNumber);
        $('#totalBilling').val(new Intl.NumberFormat('id-ID').format(total));
        $('#amount').val(total);
        $('#paymentDate').val(new Date().toISOString().split('T')[0]);
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
                confirm: "Ya, Hitung Ulang"
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
</script>
@endpush