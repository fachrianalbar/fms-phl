@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Report',
    'secondSegment' => 'Laporan Komisi',
    'thirdSegment' => 'Detail',
])

@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
    <style>
        .btn-action-back {
            background-color: #ffffff;
            color: #4f46e5;
            border: 1px solid #cbd5e1;
            font-weight: 600;
            border-radius: 8px;
            padding: 8px 16px;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-action-back:hover {
            background-color: #f8fafc;
            color: #4338ca;
            transform: translateX(-2px);
        }
        .btn-action-pdf {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #ffffff;
            border: none;
            font-weight: 600;
            border-radius: 8px;
            padding: 8px 18px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-action-pdf:hover {
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(239, 68, 68, 0.35);
        }
        .btn-action-pay {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: #ffffff;
            border: none;
            font-weight: 600;
            border-radius: 8px;
            padding: 8px 16px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.25);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-action-pay:hover {
            color: #ffffff;
            transform: translateY(-1px);
        }

        .premium-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            background: #ffffff;
            overflow: hidden;
            margin-bottom: 24px;
            position: relative;
        }
        .premium-card-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            padding: 20px 24px;
            position: relative;
        }
        .premium-card-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #fbbf24, #f59e0b, #fbbf24);
        }
        .entity-avatar-badge {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .info-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            font-weight: 700;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .info-value {
            font-size: 15px;
            font-weight: 600;
            color: #1e293b;
        }
        .info-grid-item {
            padding: 16px;
            border-radius: 12px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            height: 100%;
        }

        .summary-card {
            border-radius: 16px;
            padding: 24px 20px;
            text-align: center;
            border: 1px solid transparent;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .summary-card h5 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            margin-bottom: 8px;
            opacity: 0.9;
        }
        .summary-card h3 {
            font-size: 24px;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .summary-card .summary-icon {
            font-size: 28px;
            margin-bottom: 8px;
        }
        .summary-commission { background: linear-gradient(135deg, #eef2ff, #e0e7ff); border-color: #c7d2fe; color: #3730a3; }
        .summary-adj { background: linear-gradient(135deg, #ecfdf5, #d1fae5); border-color: #a7f3d0; color: #065f46; }
        .summary-grand { background: linear-gradient(135deg, #fefce8, #fef9c3); border-color: #fde68a; color: #92400e; }

        .table-premium { font-size: 13.5px; }
        .table-premium thead th {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            font-weight: 700;
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #475569;
            padding: 12px 16px;
        }
        .table-premium tbody td {
            padding: 12px 16px;
            vertical-align: middle;
            color: #334155;
        }
        .table-premium tbody tr:hover { background-color: #f8fafc; }

        .badge-premium {
            padding: 6px 12px;
            font-size: 10px;
            font-weight: 700;
            border-radius: 30px;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .badge-premium-addition { background-color: #d1fae5; color: #065f46; }
        .badge-premium-deduction { background-color: #fee2e2; color: #991b1b; }

        /* Dark mode */
        html[data-bs-theme="dark"] .btn-action-back {
            background-color: var(--bs-tertiary-bg);
            border-color: var(--bs-border-color);
            color: #a5b4fc;
        }
        html[data-bs-theme="dark"] .premium-card {
            background-color: var(--bs-card-bg);
            border: 1px solid var(--bs-border-color);
        }
        html[data-bs-theme="dark"] .info-label { color: var(--bs-secondary-color); }
        html[data-bs-theme="dark"] .info-value { color: var(--bs-body-color); }
        html[data-bs-theme="dark"] .info-grid-item {
            background-color: var(--bs-tertiary-bg);
            border-color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] .summary-commission {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.22), rgba(99, 102, 241, 0.12));
            border-color: rgba(129, 140, 248, 0.35);
            color: #a5b4fc;
        }
        html[data-bs-theme="dark"] .summary-adj {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.22), rgba(5, 150, 105, 0.12));
            border-color: rgba(52, 211, 153, 0.35);
            color: #6ee7b7;
        }
        html[data-bs-theme="dark"] .summary-grand {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.22), rgba(217, 119, 6, 0.12));
            border-color: rgba(251, 191, 36, 0.35);
            color: #fde68a;
        }
        html[data-bs-theme="dark"] .table-premium thead th {
            background-color: var(--bs-tertiary-bg);
            color: var(--bs-body-color);
            border-color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] .table-premium tbody td {
            color: var(--bs-body-color);
            border-color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] .table-premium tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        html[data-bs-theme="dark"] .table-premium tfoot tr {
            background-color: var(--bs-tertiary-bg) !important;
            border-color: var(--bs-border-color) !important;
        }
        html[data-bs-theme="dark"] .badge-premium-addition {
            background-color: rgba(16, 185, 129, 0.18);
            color: #6ee7b7;
        }
        html[data-bs-theme="dark"] .badge-premium-deduction {
            background-color: rgba(239, 68, 68, 0.18);
            color: #fca5a5;
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <a href="{{ route('report.commission.index') }}" class="btn btn-action-back">
                <i class="mdi mdi-arrow-left"></i> Kembali Ke List
            </a>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-action-pay" data-bs-toggle="modal" data-bs-target="#detailPaymentDateModal">
                    <i class="mdi mdi-calendar-check"></i> Input / Ubah Tanggal Bayar
                </button>
                <a href="{{ route('report.commission.pdf-processed', $commission->id) }}" target="_blank" class="btn btn-action-pdf">
                    <i class="mdi mdi-file-pdf-box"></i> Download Slip Komisi PDF
                </a>
            </div>
        </div>

        {{-- Info Card --}}
        <div class="card premium-card">
            <div class="premium-card-header d-flex flex-wrap align-items-center justify-content-between gap-2 text-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="entity-avatar-badge">
                        {{ strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $commission->recipientName ?: 'KM'), 0, 2) ?: 'KM') }}
                    </div>
                    <div>
                        <h4 class="mb-0 text-white fw-bold">{{ $commission->recipientName ?? '-' }}</h4>
                        <span class="text-white text-opacity-75" style="font-size: 12.5px;">
                            <i class="mdi mdi-domain me-1"></i> Customer: {{ $commission->customer->name ?? 'Semua / Multi Customer' }}
                        </span>
                    </div>
                </div>
                <div>
                    @if ($commission->paymentDate)
                        <span class="badge bg-white text-success fw-bold" id="detailPaymentStatusBadge" style="border-radius: 8px; padding: 6px 12px; font-size: 12px;">
                            <i class="mdi mdi-check-circle me-1"></i> Dibayar: {{ \Carbon\Carbon::parse($commission->paymentDate)->format('d-m-Y') }}
                        </span>
                    @else
                        <span class="badge bg-warning text-dark fw-bold" id="detailPaymentStatusBadge" style="border-radius: 8px; padding: 6px 12px; font-size: 12px;">
                            <i class="mdi mdi-clock-outline me-1"></i> Belum Dibayar
                        </span>
                    @endif
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="info-grid-item">
                            <div class="info-label"><i class="mdi mdi-barcode text-primary"></i> Kode Komisi</div>
                            <div class="info-value font-monospace">{{ $commission->code }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-grid-item">
                            <div class="info-label"><i class="mdi mdi-calendar-range text-primary"></i> Periode Komisi</div>
                            <div class="info-value">
                                {{ $commission->startDate ? \Carbon\Carbon::parse($commission->startDate)->format('d-m-Y') : '-' }}
                                s/d
                                {{ $commission->endDate ? \Carbon\Carbon::parse($commission->endDate)->format('d-m-Y') : '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-grid-item">
                            <div class="info-label"><i class="mdi mdi-calendar-check text-primary"></i> Tanggal Bayar</div>
                            <div class="info-value" id="detailPaymentDateText">
                                {{ $commission->paymentDate ? \Carbon\Carbon::parse($commission->paymentDate)->format('d-m-Y') : 'Belum diisi' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-grid-item">
                            <div class="info-label"><i class="mdi mdi-calculator text-primary"></i> Grand Total</div>
                            <div class="info-value text-success fw-bold font-monospace">Rp {{ number_format((float) $commission->grandTotal, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>

                @if (! empty($componentNames))
                    <div class="mt-3">
                        <div class="info-label mb-2"><i class="mdi mdi-tag-multiple-outline text-primary"></i> Komponen Komisi Terkait</div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($componentNames as $compName)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="border-radius: 6px; font-size: 12px;">
                                    {{ $compName }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($commission->notes)
                    <div class="mt-4 p-3 bg-light rounded-3" style="border-left: 4px solid #4f46e5;">
                        <div class="info-label mb-1 text-primary"><i class="mdi mdi-comment-text-outline me-1"></i> Catatan</div>
                        <div class="info-value text-muted" style="font-weight: normal; font-size: 14px;">{{ $commission->notes }}</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="summary-card summary-commission">
                    <span class="summary-icon"><i class="mdi mdi-cash-multiple"></i></span>
                    <h5>Total Komisi Order</h5>
                    <h3 class="font-monospace">Rp {{ number_format((float) $commission->totalCommission, 0, ',', '.') }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-card summary-adj">
                    <span class="summary-icon">
                        @if ($commission->totalAdjustment >= 0)
                            <i class="mdi mdi-plus-circle-outline"></i>
                        @else
                            <i class="mdi mdi-minus-circle-outline"></i>
                        @endif
                    </span>
                    <h5>Total Penyesuaian</h5>
                    <h3 class="font-monospace">{{ $commission->totalAdjustment >= 0 ? '+' : '-' }}Rp {{ number_format(abs((float) $commission->totalAdjustment), 0, ',', '.') }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-card summary-grand">
                    <span class="summary-icon"><i class="mdi mdi-wallet"></i></span>
                    <h5>Grand Total Komisi</h5>
                    <h3 class="font-monospace">Rp {{ number_format((float) $commission->grandTotal, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>

        {{-- Orders Table --}}
        <div class="card premium-card mb-4">
            <div class="card-header py-3" style="border-bottom: 1px solid var(--bs-border-color, #e2e8f0);">
                <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="mdi mdi-truck-delivery-outline text-primary fs-18"></i>
                    Daftar Order dengan Komponen Komisi
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive custom-scrollbar">
                    <table class="table table-premium mb-0">
                        <thead>
                            <tr>
                                <th style="width: 5%;" class="text-center">No</th>
                                <th style="width: 18%;">No. Shipment / Order</th>
                                <th style="width: 11%;">Tanggal</th>
                                <th style="width: 11%;">No. Polisi</th>
                                <th style="width: 20%;">Komponen Komisi</th>
                                <th style="width: 22%;">Rute Perjalanan</th>
                                <th style="width: 13%;" class="text-end">Komisi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $orderTotal = 0; @endphp
                            @forelse ($orderCommissions as $index => $item)
                                @php
                                    $order = $item->order;
                                    $orderTotal += (float) $item->amount;
                                @endphp
                                <tr>
                                    <td class="text-center fw-semibold text-muted">{{ $index + 1 }}</td>
                                    <td class="fw-bold text-primary">
                                        @if ($order)
                                            {{ $order->shipmentNumber && $order->shipmentNumber !== '-' ? $order->shipmentNumber : $order->code }}
                                            @if ($order->shipmentNumber && $order->shipmentNumber !== '-' && $order->code)
                                                <br><small class="text-muted">{{ $order->code }}</small>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $order ? \Carbon\Carbon::parse($order->orderDate)->format('d-m-Y') : '-' }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border fw-semibold" style="font-size: 11.5px; border-radius: 6px; padding: 4px 8px;">
                                            {{ $order?->plateNumber ?: ($order?->fleet?->plateNumber ?? '-') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $item->orderCost?->componentName ?: ($item->costComponent?->name ?? '-') }}</span>
                                        @if ($order?->customer?->name)
                                            <br><small class="text-muted">{{ $order->customer->name }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($order?->route)
                                            <span class="fw-semibold">{{ $order->route->name }}</span>
                                            <br>
                                            <small class="text-muted">
                                                {{ $order->route->originLocation->name ?? '' }} &rarr; {{ $order->route->destinationLocation->name ?? '' }}
                                            </small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold font-monospace">Rp {{ number_format((float) $item->amount, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="mdi mdi-information-outline me-1"></i> Tidak ada order dengan komponen komisi pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #f8fafc; border-top: 2px solid #e2e8f0;">
                                <td colspan="6" class="text-end fw-bold text-secondary py-3">Total Komisi dari Order:</td>
                                <td class="text-end py-3 font-monospace" style="font-size: 15px; color: #4f46e5; font-weight: 800;">
                                    Rp {{ number_format($orderTotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Adjustments Table --}}
        @if ($commission->details->count() > 0)
            <div class="card premium-card">
                <div class="card-header py-3" style="border-bottom: 1px solid var(--bs-border-color, #e2e8f0);">
                    <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
                        <i class="mdi mdi-plus-minus-box text-primary fs-18"></i>
                        Penyesuaian (Penambah / Pengurang)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive custom-scrollbar">
                        <table class="table table-premium mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 6%;" class="text-center">No</th>
                                    <th style="width: 18%;">Tanggal</th>
                                    <th style="width: 40%;">Deskripsi Penyesuaian</th>
                                    <th style="width: 16%;" class="text-center">Tipe</th>
                                    <th style="width: 20%;" class="text-end">Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($commission->details as $index => $detail)
                                    <tr>
                                        <td class="text-center fw-semibold text-muted">{{ $index + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($detail->date)->format('d-m-Y') }}</td>
                                        <td class="fw-semibold">{{ $detail->description }}</td>
                                        <td class="text-center">
                                            @if ($detail->type === 'addition')
                                                <span class="badge-premium badge-premium-addition">
                                                    <i class="mdi mdi-plus-circle me-1"></i> Penambah
                                                </span>
                                            @else
                                                <span class="badge-premium badge-premium-deduction">
                                                    <i class="mdi mdi-minus-circle me-1"></i> Pengurang
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold font-monospace">
                                            @if ($detail->type === 'addition')
                                                <span class="text-success">+Rp {{ number_format((float) $detail->nominal, 0, ',', '.') }}</span>
                                            @else
                                                <span class="text-danger">-Rp {{ number_format((float) $detail->nominal, 0, ',', '.') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="background-color: #f8fafc; border-top: 2px solid #e2e8f0;">
                                    <td colspan="4" class="text-end fw-bold text-secondary py-3">Total Penyesuaian:</td>
                                    <td class="text-end py-3 fw-bold font-monospace" style="font-size: 15px; color: {{ $commission->totalAdjustment >= 0 ? '#059669' : '#dc2626' }};">
                                        {{ $commission->totalAdjustment >= 0 ? '+' : '-' }}Rp {{ number_format(abs((float) $commission->totalAdjustment), 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Modal: Input / Ubah Tanggal Bayar --}}
    <div class="modal fade" id="detailPaymentDateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 14px; overflow: hidden;">
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
                    <h5 class="modal-title text-white"><i class="mdi mdi-calendar-check me-2"></i> Input Tanggal Bayar</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <label class="form-label fw-semibold" for="detailPaymentDateInput">Tanggal Bayar</label>
                    <input type="text" id="detailPaymentDateInput" class="form-control"
                        value="{{ $commission->paymentDate ? \Carbon\Carbon::parse($commission->paymentDate)->format('Y-m-d') : '' }}"
                        placeholder="Pilih tanggal bayar">
                    <small class="text-muted d-block mt-2">Tanggal ini akan dicetak pada baris "Diterima Tanggal" di Slip Komisi PDF.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSaveDetailPaymentDate">
                        <i class="mdi mdi-content-save me-1"></i> Simpan Tanggal Bayar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ asset('assets/js/sweet-alert/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script>
        $(document).ready(function () {
            flatpickr('#detailPaymentDateInput', {
                dateFormat: 'Y-m-d',
                allowInput: true,
            });

            $('#btnSaveDetailPaymentDate').on('click', function () {
                const btn = $(this);
                btn.prop('disabled', true);

                $.ajax({
                    url: "{{ route('report.commission.payment-date', $commission->id) }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        paymentDate: $('#detailPaymentDateInput').val(),
                    },
                    success: function (res) {
                        $('#detailPaymentDateModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false,
                        }).then(() => location.reload());
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.message || 'Gagal menyimpan tanggal bayar.';
                        Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                    },
                    complete: function () {
                        btn.prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endpush
