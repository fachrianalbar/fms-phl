@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Report',
    'secondSegment' => 'Driver Salary',
    'thirdSegment' => 'Detail',
])

@push('style')
    <style>
        /* ── Back & Action Buttons ── */
        .btn-action-back {
            background-color: #ffffff;
            color: #4f46e5;
            border: 1px solid #cbd5e1;
            font-weight: 600;
            border-radius: 8px;
            padding: 8px 16px;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-action-back:hover {
            background-color: #f8fafc;
            color: #4338ca;
            border-color: #cbd5e1;
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

        /* ── Profile & Info Card ── */
        .premium-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
            background: #ffffff;
            overflow: hidden;
            margin-bottom: 24px;
            transition: all 0.3s ease;
            position: relative;
        }
        .premium-card:hover {
            box-shadow: 0 15px 35px rgba(0,0,0,0.07);
        }
        .premium-card-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            padding: 20px 24px;
            border-bottom: none;
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
        .driver-avatar-badge {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            border: 1px solid rgba(255,255,255,0.15);
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
            transition: all 0.2s ease;
            height: 100%;
        }
        .info-grid-item:hover {
            border-color: #cbd5e1;
            background-color: #ffffff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        }

        /* ── Summary Boxes ── */
        .summary-card {
            border-radius: 16px;
            padding: 24px 20px;
            text-align: center;
            border: 1px solid transparent;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px rgba(0,0,0,0.02);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .summary-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.06);
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
            display: inline-block;
            opacity: 0.95;
        }
        
        .summary-salary { 
            background: linear-gradient(135deg, #eef2ff, #e0e7ff); 
            border-color: #c7d2fe; 
            color: #3730a3; 
        }
        .summary-adj { 
            background: linear-gradient(135deg, #ecfdf5, #d1fae5); 
            border-color: #a7f3d0; 
            color: #065f46; 
        }
        .summary-grand { 
            background: linear-gradient(135deg, #fefce8, #fef9c3); 
            border-color: #fde68a; 
            color: #92400e; 
        }

        /* ── Tables ── */
        .table-premium {
            font-size: 13.5px;
        }
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
        .table-premium tbody tr {
            transition: background-color 0.15s ease;
        }
        .table-premium tbody tr:hover {
            background-color: #f8fafc;
        }
        
        /* ── Badges ── */
        .badge-premium {
            padding: 6px 12px;
            font-size: 10px;
            font-weight: 700;
            border-radius: 30px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .badge-premium-addition {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-premium-deduction {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        {{-- Back & Action buttons --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="{{ route('report.driver-salary.index') }}" class="btn btn-action-back">
                <i class="mdi mdi-arrow-left"></i> Kembali Ke List
            </a>
            <a href="{{ route('report.driver-salary.pdf-processed', $salary->id) }}" target="_blank" class="btn btn-action-pdf">
                <i class="mdi mdi-file-pdf-box"></i> Download Slip Gaji PDF
            </a>
        </div>

        {{-- Driver Info Card --}}
        <div class="card premium-card">
            <div class="premium-card-header d-flex align-items-center justify-content-between text-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="driver-avatar-badge">
                        {{ strtoupper(substr($salary->driver->name ?? 'D', 0, 2)) }}
                    </div>
                    <div>
                        <h4 class="mb-0 text-white fw-bold">{{ $salary->driver->name ?? '-' }}</h4>
                        <span class="text-white text-opacity-75" style="font-size: 12.5px;">
                            <i class="mdi mdi-card-account-details-outline me-1"></i> Driver Code: {{ $salary->driverCode }}
                        </span>
                    </div>
                </div>
                <div>
                    <span class="badge bg-white text-primary fw-bold" style="border-radius: 8px; padding: 6px 12px; font-size: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
                        <i class="mdi mdi-check-circle me-1"></i> Status: Processed
                    </span>
                </div>
            </div>
            
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="info-grid-item">
                            <div class="info-label"><i class="mdi mdi-barcode text-primary"></i> Kode Gaji</div>
                            <div class="info-value">{{ $salary->code }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-grid-item">
                            <div class="info-label"><i class="mdi mdi-calendar-range text-primary"></i> Periode Gaji</div>
                            <div class="info-value">
                                {{ \Carbon\Carbon::parse($salary->startDate)->format('d-m-Y') }} s/d {{ \Carbon\Carbon::parse($salary->endDate)->format('d-m-Y') }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-grid-item">
                            <div class="info-label"><i class="mdi mdi-clock-check-outline text-primary"></i> Tanggal Proses</div>
                            <div class="info-value">{{ $salary->created_at->format('d-m-Y H:i') }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-grid-item">
                            <div class="info-label"><i class="mdi mdi-calculator text-primary"></i> Grand Total</div>
                            <div class="info-value text-success fw-bold">Rp {{ number_format($salary->grandTotal, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
                
                @if($salary->notes)
                    <div class="mt-4 p-3 bg-light rounded-3" style="border-left: 4px solid #4f46e5;">
                        <div class="info-label mb-1 text-primary"><i class="mdi mdi-comment-text-outline me-1"></i> Catatan</div>
                        <div class="info-value text-muted" style="font-weight: normal; font-size: 14px;">{{ $salary->notes }}</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="summary-card summary-salary">
                    <span class="summary-icon"><i class="mdi mdi-cash-multiple"></i></span>
                    <h5>Total Gaji Order</h5>
                    <h3>Rp {{ number_format($salary->totalSalary, 0, ',', '.') }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-card summary-adj">
                    <span class="summary-icon">
                        @if($salary->totalAdjustment >= 0)
                            <i class="mdi mdi-plus-circle-outline"></i>
                        @else
                            <i class="mdi mdi-minus-circle-outline"></i>
                        @endif
                    </span>
                    <h5>Total Penyesuaian</h5>
                    <h3>{{ $salary->totalAdjustment >= 0 ? '+' : '' }}Rp {{ number_format(abs($salary->totalAdjustment), 0, ',', '.') }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-card summary-grand">
                    <span class="summary-icon"><i class="mdi mdi-wallet"></i></span>
                    <h5>Grand Total Gaji</h5>
                    <h3>Rp {{ number_format($salary->grandTotal, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>

        {{-- Orders Table --}}
        <div class="card premium-card mb-4">
            <div class="card-header bg-white py-3" style="border-bottom: 1px solid #e2e8f0;">
                <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="mdi mdi-truck-delivery-outline text-primary fs-18"></i>
                    Daftar Order yang Dibayarkan
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive custom-scrollbar">
                    <table class="table table-premium mb-0">
                        <thead>
                            <tr>
                                <th style="width: 6%;" class="text-center">No</th>
                                <th style="width: 15%;">Kode Order</th>
                                <th style="width: 14%;">Tanggal Order</th>
                                <th style="width: 15%;">No. Polisi</th>
                                <th style="width: 35%;">Rute Perjalanan</th>
                                <th style="width: 15%;" class="text-end">Gaji Order</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $orderTotal = 0; @endphp
                            @foreach($orders as $index => $order)
                                @php $orderTotal += $order->salaryAmount; @endphp
                                <tr>
                                    <td class="text-center fw-semibold text-muted">{{ $index + 1 }}</td>
                                    <td class="fw-bold text-primary">{{ $order->code }}</td>
                                    <td>{{ \Carbon\Carbon::parse($order->orderDate)->format('d-m-Y') }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border fw-semibold" style="font-size: 11.5px; border-radius: 6px; padding: 4px 8px;">
                                            {{ $order->fleet->plateNumber ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($order->route)
                                            <span class="fw-semibold text-dark">{{ $order->route->name }}</span>
                                            <br>
                                            <small class="text-muted">
                                                {{ $order->route->originLocation->name ?? '' }} &rarr; {{ $order->route->destinationLocation->name ?? '' }}
                                            </small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-dark">Rp {{ number_format($order->salaryAmount, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #f8fafc; border-top: 2px solid #e2e8f0;">
                                <td colspan="5" class="text-end fw-bold text-secondary py-3">Total Gaji dari Order:</td>
                                <td class="text-end py-3" style="font-size: 15px; color: #4f46e5; font-weight: 800;">
                                    Rp {{ number_format($orderTotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Adjustments Table --}}
        @if($salary->details->count() > 0)
            <div class="card premium-card">
                <div class="card-header bg-white py-3" style="border-bottom: 1px solid #e2e8f0;">
                    <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="mdi mdi-plus-minus-box-outline text-primary fs-18"></i>
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
                                @foreach($salary->details as $index => $detail)
                                    <tr>
                                        <td class="text-center fw-semibold text-muted">{{ $index + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($detail->date)->format('d-m-Y') }}</td>
                                        <td class="fw-semibold text-dark">{{ $detail->description }}</td>
                                        <td class="text-center">
                                            @if($detail->type === 'addition')
                                                <span class="badge-premium badge-premium-addition">
                                                    <i class="mdi mdi-plus-circle me-1"></i> Penambah
                                                </span>
                                            @else
                                                <span class="badge-premium badge-premium-deduction">
                                                    <i class="mdi mdi-minus-circle me-1"></i> Pengurang
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold">
                                            @if($detail->type === 'addition')
                                                <span class="text-success">+Rp {{ number_format($detail->nominal, 0, ',', '.') }}</span>
                                            @else
                                                <span class="text-danger">-Rp {{ number_format($detail->nominal, 0, ',', '.') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="background-color: #f8fafc; border-top: 2px solid #e2e8f0;">
                                    <td colspan="4" class="text-end fw-bold text-secondary py-3">Total Penyesuaian:</td>
                                    <td class="text-end py-3 fw-bold" style="font-size: 15px; color: {{ $salary->totalAdjustment >= 0 ? '#059669' : '#dc2626' }};">
                                        {{ $salary->totalAdjustment >= 0 ? '+' : '-' }}Rp {{ number_format(abs($salary->totalAdjustment), 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
