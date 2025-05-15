@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => 'Show',
])

@php
    use Carbon\Carbon;
@endphp

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
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Data</h4>

                <a href="{{ route($view . 'index') }}" class="btn btn-info">Back To List</a>

            </div>
            <div class="card-body col-md-12">
                <form class="row g-3" method="post" action="{{ route($view . 'store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex gap-5 justify-content-between">
                                <span class="h6">Name</span>
                                <span
                                    class="h6">{{ isset($data->userBank->accountName) ? $data->userBank->accountName : '' }}</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex gap-5 justify-content-between">
                                <span class="h6">Credit</span>
                                <span class="h6">{{ number_format($data->credit, 0, ',', '.') }}</span>
                            </div>
                        </div>


                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="d-flex gap-5 justify-content-between">
                                <span class="h6">Debit</span>
                                <span class="h6">{{ number_format($data->debit, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex gap-5 justify-content-between">
                                <span class="h6">Balance</span>
                                <span class="h6">{{ number_format($data->balance, 0, ',', '.') }}</span>
                            </div>
                        </div>


                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">


            </div>
            <div class="card-body col-md-12">
                <table class="display " id="dt">
                    <thead>
                        <tr>
                            <th colspan="5"></th>
                            <th colspan="2" class="text-center">Nominal</th>
                        </tr>
                        <tr>
                            <th>No</th>
                            <th>Transaction Date</th>
                            <th>Transaction Type</th>
                            <th>Description</th>
                            <th>Credit</th>
                            <th>Debit</th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mutation as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    {{ Carbon::parse($item->created_at)->format('d-M-Y H:i') }}
                                </td>
                                <td>{{ isset($item->transactionType->name) ? $item->transactionType->name : '' }}</td>
                                <td>{{ $item->description }}</td>
                                <td>
                                    @if ($item->type == 'Out')
                                        {{ number_format($item->nominal, 0, ',', '.') }}
                                    @elseif ($item->type == 'In')
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if ($item->type == 'In')
                                        {{ number_format($item->nominal, 0, ',', '.') }}
                                    @elseif ($item->type == 'Out')
                                        0
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>


    </div>
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
    <script>
        $('#dt').DataTable({})
    </script>
@endpush
