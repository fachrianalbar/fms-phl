@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => 'Add',
])

@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/datatables.css') }}">
@endpush

@section('content')
    <form class="row g-3" method="post" action="{{ route($view . 'store') }}">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ $title }} Add Data</h4>

                    <a href="{{ route($view . 'index') }}" class="btn btn-info">Back To List</a>

                </div>
                <div class="card-body col-md-12">
                    @csrf
                    <div class="row g-3">
                        <div class="row">
                            <div class="col-md-6 position-relative">
                                <label class="form-label" for="customerCode">Customer
                                    Name <i class="icofont icofont-warning-alt text-danger"></i></label>
                                <select class="js-example-basic-single" name="customerCode" id="customerCode"
                                    required="">
                                    <option selected="" disabled="" value="">Choose...</option>
                                    @foreach ($customer as $item)
                                        <option value="{{ $item->code }}">{{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>`
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="invoiceNumber">Invoice Number <i
                                        class="icofont icofont-warning-alt text-danger"></i></label>
                                <input class="form-control" name="invoiceNumber" id="invoiceNumber" type="text" required
                                    placeholder="Invoice Number">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label" for="receiptNumber">Receipt Number <i
                                        class="icofont icofont-warning-alt text-danger"></i></label>
                                <input class="form-control" name="receiptNumber" id="receiptNumber" type="text" required
                                    placeholder="Receipt Number">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="poNumber">Po Number</label>
                                <input class="form-control" name="poNumber" id="poNumber" type="text"
                                    placeholder="Po Number">
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <label class="form-label" for="name">Invoice Date <i
                                        class="icofont icofont-warning-alt text-danger"></i></label>
                                <input class="form-control" name="invoiceDate" id="datetime-local" type="date" required
                                    placeholder="Invoice Date" value="{{ now()->toDateString() }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="name">Overdue Date </label>
                                <input class="form-control" name="overdueDate" readonly placeholder="Overdue Date"
                                    value="{{ now()->toDateString() }}">
                            </div>
                        </div>

                        <div class="row mt-4">
                            <hr>
                        </div>

                        <div class="row ">
                            <div class="col-md-6">
                                <label class="form-label" for="picName">To Pic Name </label>
                                <input class="form-control" name="picName" id="picName" type="text" readonly
                                    placeholder="To Pic Name">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="picPhone">To Pic Phone Number</label>
                                <input class="form-control" name="picPhone" id="picPhone" type="text" readonly
                                    placeholder="To Pic Phone Number">
                            </div>
                        </div>


                        <div class="row mt-4">
                            <div class="col-md-6">
                                <label class="form-label" for="invoiceAddress">Invoice Address To </label>
                                <textarea class="form-control" name="invoiceAddress" id="invoiceAddress" placeholder="Invoice Address To"
                                    rows="4" readonly></textarea>

                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="notes">Notes <i
                                        class="icofont icofont-warning-alt text-danger"></i></label>
                                <textarea class="form-control" name="notes" id="notes" placeholder="Notes" rows="4" required></textarea>
                            </div>
                        </div>


                        <div class="col-12">
                            <button class="btn btn-primary" type="button" id="openModalButton">Add</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog"
                aria-labelledby="myLargeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Data Order</h4>
                            <button class="btn-close py-0" type="button" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="card">
                            <div class="card-body col-md-12">
                                <div class="row g-3">
                                    <table class="display " id="dt">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>No</th>
                                                <th>Order Date</th>
                                                <th>Origin</th>
                                                <th>Destination</th>
                                                <th>Shipment No</th>
                                                <th>Plate No</th>
                                                <th>Total Cost</th>
                                                {{-- <th>Order Type</th> --}}
                                                {{-- <th>Allowance</th> --}}
                                                {{-- <th>Qty</th>
                                        <th>Cost</th>
                                        <th>Tonase</th>
                                        <th>Add Cost</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-start">
                            <button type="submit" id="saveInvoice" class="btn btn-primary">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('script')
    <script src="{{ asset('assets/js/datatable/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
    <script src=" {{ asset('assets/js/helper.js') }}"></script>

    <script>
        $(document).ready(function() {
            $("input[name='invoiceDate']").on("change", function() {
                let invoiceDate = new Date($(this).val());
                if (!isNaN(invoiceDate.getTime())) {
                    let overdueDate = new Date(invoiceDate);
                    overdueDate.setDate(overdueDate.getDate() + 2); // Tambahkan 2 hari

                    let formattedDate = overdueDate.toISOString().split('T')[0];
                    $("input[name='overdueDate']").val(formattedDate);
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
                            title: "Warning",
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
                    {
                        "data": 'totalPrice'
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
                    [3, 'desc']
                ]
            })
        });

        function customerInvoice() {
            const customerCode = $('#customerCode').select2('val');

            $.get("{{ url('ajax/customer-invoice') }}/" + customerCode, function(data) {
                $('#picName').val(data.picName)
                $('#picPhone').val(data.phone)
                $('#invoiceAddress').val(data.address1)
            });;

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

            setTimeout(() => {
                $('.loader-wrapper').remove();
            }, 1000);
        });

        $('#saveInvoice').click(function(e) {
            // Get all checkboxes
            if (selectedOrders.length === 0) {
                event.preventDefault();
                swal({
                    title: "Warning",
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
