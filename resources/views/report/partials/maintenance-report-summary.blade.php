{{-- KPI ringkasan laporan perawatan. $summary dari MaintenancePaymentStatusService::summarize(). --}}
<div class="row g-3 mb-4">
    <div class="col-xl col-md-4 col-sm-6">
        <div class="summary-card summary-primary">
            <i class="mdi mdi-wrench-clock-outline float-end fs-24 opacity-50"></i>
            <h5>Total Maint</h5>
            <h3>{{ number_format($summary['totalMaintenance'], 0, ',', '.') }} <span class="fs-14 fw-normal opacity-75">Transaksi</span></h3>
        </div>
    </div>
    <div class="col-xl col-md-4 col-sm-6">
        <div class="summary-card summary-success">
            <i class="mdi mdi-package-variant-closed float-end fs-24 opacity-50"></i>
            <h5>Total Qty</h5>
            <h3>{{ number_format($summary['totalQty'], 1, ',', '.') }} <span class="fs-14 fw-normal opacity-75">Item</span></h3>
        </div>
    </div>
    <div class="col-xl col-md-4 col-sm-6">
        <div class="summary-card summary-warning">
            <i class="mdi mdi-cash-multiple float-end fs-24 opacity-50"></i>
            <h5>Total Pembayaran</h5>
            <h3>Rp {{ number_format($summary['totalCost'], 0, ',', '.') }}</h3>
        </div>
    </div>
    <div class="col-xl col-md-6 col-sm-6">
        <div class="summary-card summary-danger">
            <i class="mdi mdi-calendar-alert float-end fs-24 opacity-50"></i>
            <h5>Utang Jth Tempo</h5>
            <h3>Rp {{ number_format($summary['utangJatuhTempo'], 0, ',', '.') }}</h3>
        </div>
    </div>
    <div class="col-xl col-md-6 col-sm-12">
        <div class="summary-card summary-orange">
            <i class="mdi mdi-credit-card-clock-outline float-end fs-24 opacity-50"></i>
            <h5>Total Utang</h5>
            <h3>Rp {{ number_format($summary['totalUtang'], 0, ',', '.') }}</h3>
        </div>
    </div>
</div>
