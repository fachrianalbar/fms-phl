@extends('layouts.main', [
'title' => $title,
'pageTitle' => $title,
'firstSegment' => $title,
'secondSegment' => __('general.add'),
])

@push('style')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
<link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

<link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">
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
@endpush

@section('content')
<form class="row g-3 mt-1" method="post" action="{{ route($view . 'store') }}">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} {{ __('general.add_data') }}</h4>
                <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>
            </div>

            <div class="card-body col-md-12">
                @csrf
                <div class="row g-3">
                    <div class="row">
                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="customerCode">
                                {{ __('menu_invoice.customer_name') }} <i class="mdi mdi-information text-danger"></i>
                            </label>
                            <select class="js-example-basic-single" name="customerCode" id="customerCode" required>
                                <option selected disabled value="">{{ __('general.choose') }}...</option>
                                @foreach ($customer as $item)
                                <option value="{{ $item->code }}" data-id="{{ $item->id }}">
                                    {{ $item->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="invoiceNumber">
                                {{ __('menu_invoice.invoice_number') }} <i class="mdi mdi-information text-danger"></i>
                            </label>
                            <input class="form-control" name="invoiceNumber" id="invoiceNumber" type="text" required
                                readonly placeholder="{{ __('menu_invoice.invoice_number') }}">
                        </div>
                    </div>

                    {{-- <div class="row mt-4">
                            <div class="col-md-6">
                                <label class="form-label" for="receiptNumber">
                                    {{ __('menu_invoice.receipt_number') }} <i class="mdi mdi-information text-danger"></i>
                    </label>
                    <input class="form-control" name="receiptNumber" id="receiptNumber" type="text" required
                        placeholder="{{ __('menu_invoice.receipt_number') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="poNumber">{{ __('menu_invoice.po_number') }}</label>
                    <input class="form-control" name="poNumber" id="poNumber" type="text"
                        placeholder="{{ __('menu_invoice.po_number') }}">
                </div>
            </div> --}}

            <div class="row mt-4">
                <div class="col-md-6">
                    <label class="form-label" for="invoiceDate">
                        {{ __('menu_invoice.invoice_date') }} <i class="mdi mdi-information text-danger"></i>
                    </label>
                    <input class="form-control" name="invoiceDate" id="invoiceDate" type="date" required
                        placeholder="{{ __('menu_invoice.invoice_date') }}"
                        value="{{ now()->toDateString() }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="overdueDate">{{ __('menu_invoice.overdue_date') }}</label>
                    <input class="form-control" name="overdueDate" id="overdueDate" type="date" readonly
                        placeholder="{{ __('menu_invoice.overdue_date') }}"
                        value="{{ now()->toDateString() }}">
                </div>
            </div>

            <div class="row mt-4">
                <hr>
            </div>

            <div class="row">
                {{-- <div class="col-md-6">
                                <label class="form-label"
                                    for="billingAddress">{{ __('menu_invoice.billing_address') }}</label>
                <textarea class="form-control" placeholder="{{ __('menu_invoice.billing_address') }}" id="billingAddress"
                    rows="4" disabled readonly></textarea>
            </div> --}}

            <div class="col-md-6">
                <label class="form-label" for="notes">{{ __('menu_invoice.notes') }}</label>
                <textarea class="form-control" name="notes" id="notes" placeholder="{{ __('menu_invoice.notes') }}"
                    rows="4"></textarea>
            </div>
        </div>

        <div class="d-none" id="picContainer">
            <label class="form-label" for="picName">{{ __('menu_invoice.data_pic') }}</label>
            <div id="listPic"></div>
        </div>

        <div class="col-12">
            <button class="btn btn-primary" type="button"
                id="openModalButton">{{ __('menu_invoice.add') }}</button>
        </div>
    </div>
    </div>
    </div>

    <div class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">{{ __('menu_invoice.data_order_title') }}</h4>
                    <button class="btn-close py-0" type="button" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="card">
                    <div class="card-body col-md-12">
                        <div class="row g-3">
                            <table class="table table-striped w-100 nowrap" id="dt">
                                <thead>
                                    <tr>
                                        <th>{{ __('menu_invoice.hash') }}</th>
                                        <th>{{ __('menu_invoice.no') }}</th>
                                        <th>{{ __('menu_invoice.order_date') }}</th>
                                        <th>{{ __('menu_invoice.origin') }}</th>
                                        <th>{{ __('menu_invoice.destination') }}</th>
                                        <th>{{ __('menu_invoice.shipment_no') }}</th>
                                        <th>{{ __('menu_invoice.plate_no') }}</th>
                                        {{-- <th>{{ __('menu_invoice.total_cost') }}</th> --}}
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer justify-content-start">
                    <button type="submit" id="saveInvoice"
                        class="btn btn-primary">{{ __('menu_invoice.save') }}</button>
                </div>
            </div>
        </div>
    </div>
    </div>
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
<script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
<script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
<script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>
<script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
<script src=" {{ asset('assets/js/helper.js') }}"></script>

<script>
    $(document).ready(function() {
        $('input[name="invoiceDate"]').on('change', function() {
            const customerCode = $('#customerCode').select2('val');

            if (customerCode) {
                $.get("{{ url('ajax/customer-invoice') }}/" + customerCode, function(data) {
                    let invoiceDate = new Date($("input[name='invoiceDate']").val());
                    if (!isNaN(invoiceDate.getTime())) {
                        let overdueDate = new Date(invoiceDate);
                        overdueDate.setDate(overdueDate.getDate() + data.dueDateDuration);

                        let formattedDate = overdueDate.toISOString().split('T')[0];
                        $("input[name='overdueDate']").val(formattedDate);
                    }
                });
            }
        });

        // Inisialisasi saat halaman dimuat
        if ($("input[name='invoiceDate']").val()) {
            $("input[name='invoiceDate']").trigger("change");
        }
    });
</script>

<script>
    let selectedOrders = [];

    $(document).ready(function() {
        $('#openModalButton').click(function() {

            // Validate form fields
            let isValid = true;
            $('form input[required]').each(function() {
                if ($(this).val() === '') {

                    isValid = false;

                    swal({
                        title: "{{ __('general.warning') }}",
                        text: "Please fill all required fields",
                        icon: "warning",
                    })
                    return;
                }
            });
            table.ajax.reload();

            // If form is valid, show the modal
            if (isValid) {
                $('.bd-example-modal-xl').modal('show');
            }
        });

        // Event handler untuk checkbox
        $(document).on('change', '.order-checkbox', function() {
            const orderId = $(this).val();
            if ($(this).is(':checked')) {
                if (!selectedOrders.includes(orderId)) {
                    selectedOrders.push(orderId);
                }
            } else {
                selectedOrders = selectedOrders.filter(id => id !== orderId);
            }

        });

        // Simpan state saat DataTable di-reload (misalnya saat pindah halaman)
        $('#dt').on('draw.dt', function() {
            $('.order-checkbox').each(function() {
                const orderId = $(this).val();
                if (selectedOrders.includes(orderId)) {
                    $(this).prop('checked', true);
                }
            });
        });

        const table = $('#dt').DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "ajax": {
                "url": "{{ route('dt.invoice-order') }}",
                "data": function(d) {
                    d.customerCode = $('select[name="customerCode"]').val();
                }
            },
            "columns": [{
                    "data": 'action'
                },
                {
                    "data": 'DT_RowIndex'
                },
                {
                    "data": 'orderDate'
                },
                {
                    "data": 'route.originLocation.name'
                },
                {
                    "data": 'route.destinationLocation.name'
                },
                {
                    "data": 'shipmentNumber'
                },
                {
                    "data": 'fleet.plateNumber'
                },
                // {
                //     "data": 'fleet.type.name'
                // },
                // {
                //     "data": 'orderType.name'
                // },
                // {
                //     "data": 'allowance'
                // },
                // {
                //     "data": 'qty'
                // },
                // {
                //     "data": 'cost',
                // },
                // {
                //     "data": 'bonus'
                // },
                // {
                //     "data": 'addCost'
                // },
                // {
                //     "data": 'totalPrice'
                // },
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
                [3, 'desc']
            ]
        })
    });

    function customerInvoice() {
        const customerCode = $('#customerCode').select2('val');
        const listPic = document.getElementById('listPic');
        const picContainer = document.getElementById('picContainer');
        listPic.innerHTML = '';

        let hasValidData = false;


        $.get("{{ url('ajax/customer-invoice') }}/" + customerCode, function(data) {
            $('#picName').val(data.picName)
            $('#picPhone').val(data.phone)
            $('#billingAddress').val(data.billingAddress)
            let invoiceDate = new Date($("input[name='invoiceDate']").val());

            if (!isNaN(invoiceDate.getTime())) {
                let overdueDate = new Date(invoiceDate);
                overdueDate.setDate(overdueDate.getDate() + data.dueDateDuration);

                let formattedDate = overdueDate.toISOString().split('T')[0];
                $("input[name='overdueDate']").val(formattedDate);
            }


            data.pic.forEach((item, i) => {
                if (item.picName && item.phone) {
                    console.log("andi");
                    hasValidData = true;

                    const html = `
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <input class="form-control" type="text" readonly value="${item.picName}" placeholder="To Pic Name" disabled>
                                </div>
                                <div class="col-md-6">
                                    <input class="form-control" type="text" readonly value="${item.phone}" placeholder="To Pic Phone Number" disabled>
                                </div>
                            </div>
                        `;

                    listPic.innerHTML += html;
                }
            })

            if (hasValidData) {
                picContainer.classList.remove('d-none');
            } else {
                picContainer.classList.add('d-none');
            }
        });;



    }

    function generateInvoiceNumber() {
        const customerCode = $('#customerCode').select2('val');
        let customerId = $('#customerCode option:selected').data('id');
        let invoiceDate = $('input[name="invoiceDate"]').val();

        if (customerId) {
            $.get("{{ url('ajax/invoice-number-format') }}/" + customerId, {
                invoiceDate: invoiceDate
            }, function(data) {
                $('#invoiceNumber').val(data);
            });
        }
    }

    $('#customerCode').on('change', function() {
        $('body').append(`
            <div class="loader-wrapper">
                <div class="loader">
                    <div class="loader4"></div>
                </div>
            </div>
        `)
        customerInvoice();
        generateInvoiceNumber();

        setTimeout(() => {
            $('.loader-wrapper').remove();
        }, 1000);
    });

    // Event untuk saat invoiceDate berubah
    $('input[name="invoiceDate"]').on('change', function() {
        generateInvoiceNumber();
    });

    $('#saveInvoice').click(function(e) {
        // Get all checkboxes
        if (selectedOrders.length === 0) {
            event.preventDefault();
            swal({
                title: "{{ __('general.warning') }}",
                text: "Please select at least one item",
                icon: "warning",
            });
            return;
        }

        // Tambahkan array ke form
        $('<input>').attr({
            type: 'hidden',
            name: 'selectedOrders',
            value: JSON.stringify(selectedOrders)
        }).appendTo('form');
    });
</script>
@endpush