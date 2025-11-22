@extends('layouts.main', [
    'title' => 'Dashboard',
    'pageTitle' => 'Dashboard',
    'firstSegment' => 'Dashboard',
    'secondSegment' => 'Beranda',
])

@section('content')
    <!-- Filter Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="year-filter" class="form-label">Tahun</label>
                            <select id="year-filter" class="form-select">
                                @for ($y = 2020; $y <= date('Y') + 1; $y++)
                                    <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>
                                        {{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="month-filter" class="form-label">Bulan (Opsional)</label>
                            <select id="month-filter" class="form-select">
                                <option value="">Semua Bulan</option>
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $m == $currentMonth ? 'selected' : '' }}>
                                        {{ Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-primary" onclick="applyFilters()">
                                <i class="mdi mdi-filter"></i> Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Widgets -->
    <div class="row">
        <div class="col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="widget-first">
                        <div class="d-flex align-items-center mb-2">
                            <div class="p-2 border border-primary border-opacity-10 bg-primary-subtle rounded-2 me-2">
                                <div class="bg-primary rounded-circle widget-size text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                        viewBox="0 0 24 24">
                                        <path fill="#ffffff"
                                            d="M19 3H5c-1.11 0-2 .89-2 2v14c0 1.11.89 2 2 2h14c1.11 0 2-.89 2-2V5c0-1.11-.89-2-2-2m0 16H5V7h14v12Z" />
                                    </svg>
                                </div>
                            </div>
                            <p class="mb-0 text-dark fs-15">Total Order Bulan Ini</p>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0 fs-22 text-dark me-3">{{ number_format($monthlyOrderNow) }}</h3>
                            <div class="text-center">
                                <p class="text-dark fs-13 mb-0">{{ $currentMonthName }} {{ $currentYear }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="widget-first">
                        <div class="d-flex align-items-center mb-2">
                            <div class="p-2 border border-success border-opacity-10 bg-success-subtle rounded-2 me-2">
                                <div class="bg-success rounded-circle widget-size text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                        viewBox="0 0 24 24">
                                        <path fill="#ffffff"
                                            d="M12 4a4 4 0 0 1 4 4a4 4 0 0 1-4 4a4 4 0 0 1-4-4a4 4 0 0 1 4-4m0 10c4.42 0 8 1.79 8 4v2H4v-2c0-2.21 3.58-4 8-4" />
                                    </svg>
                                </div>
                            </div>
                            <p class="mb-0 text-dark fs-15">Total Customer Aktif</p>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0 fs-22 text-dark me-3">{{ number_format($topCustomers->count()) }}</h3>
                            <div class="text-center">
                                <p class="text-dark fs-13 mb-0">Yang pernah order</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="widget-first">
                        <div class="d-flex align-items-center mb-2">
                            <div class="p-2 border border-warning border-opacity-10 bg-warning-subtle rounded-2 me-2">
                                <div class="bg-warning rounded-circle widget-size text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                        viewBox="0 0 24 24">
                                        <path fill="#ffffff"
                                            d="M18.92 5.01C18.72 4.42 18.16 4 17.5 4h-11c-.66 0-1.22.42-1.42 1.01L3 11v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99M6.5 15c-.83 0-1.5-.67-1.5-1.5S5.67 12 6.5 12s1.5.67 1.5 1.5S7.33 15 6.5 15m11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5s1.5.67 1.5 1.5s-.67 1.5-1.5 1.5M5 10l1.5-4.5h11L19 10H5Z" />
                                    </svg>
                                </div>
                            </div>
                            <p class="mb-0 text-dark fs-15">Total Fleet Aktif</p>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0 fs-22 text-dark me-3">{{ number_format($topFleets->count()) }}</h3>
                            <div class="text-center">
                                <p class="text-dark fs-13 mb-0">Yang pernah digunakan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="widget-first">
                        <div class="d-flex align-items-center mb-2">
                            <div class="p-2 border border-danger border-opacity-10 bg-danger-subtle rounded-2 me-2">
                                <div class="bg-danger rounded-circle widget-size text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                        viewBox="0 0 24 24">
                                        <path fill="#ffffff"
                                            d="M14 2H6c-1.11 0-2 .89-2 2v16c0 1.11.89 2 2 2h12c1.11 0 2-.89 2-2V8l-6-6m4 18H6V4h7v5h5v11Z" />
                                    </svg>
                                </div>
                            </div>
                            <p class="mb-0 text-dark fs-15">Invoice Jatuh Tempo</p>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0 fs-22 text-dark me-3">{{ number_format($overdueInvoices->count()) }}</h3>
                            <div class="text-center">
                                <p class="text-dark fs-13 mb-0">Perlu perhatian</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Main Widgets -->

    <!-- Chart Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Statistik Order Tahun <span id="chart-year">{{ $currentYear }}</span></h5>
                </div>
                <div class="card-body">
                    <canvas id="orderChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Customers and Fleets Section -->
    <div class="row mb-4">
        <!-- Top Customers -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Customer Teratas</h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshCustomerStats()">
                        <i class="mdi mdi-refresh"></i> Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="customers-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Customer</th>
                                    <th>Jumlah Order</th>
                                    <th>Terakhir Order</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($topCustomers as $index => $customer)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs me-2">
                                                    <div class="avatar-title rounded-circle bg-primary text-white">
                                                        {{ substr($customer->name, 0, 1) }}
                                                    </div>
                                                </div>
                                                {{ $customer->name }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary">
                                                {{ number_format($customer->orders_count) }} order
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $customer->latest_order ? \Carbon\Carbon::parse($customer->latest_order)->diffForHumans() : 'Belum ada order' }}
                                            </small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Fleets -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Fleet Teratas</h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshFleetStats()">
                        <i class="mdi mdi-refresh"></i> Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="fleets-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Fleet</th>
                                    <th>Jumlah Order</th>
                                    <th>Terakhir Digunakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($topFleets as $index => $fleet)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs me-2">
                                                    <div class="avatar-title rounded-circle bg-warning text-white">
                                                        <i class="mdi mdi-truck"></i>
                                                    </div>
                                                </div>
                                                {{ $fleet->name }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning-subtle text-warning">
                                                {{ number_format($fleet->orders_count) }} order
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $fleet->latest_order ? \Carbon\Carbon::parse($fleet->latest_order)->diffForHumans() : 'Belum digunakan' }}
                                            </small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice Due Dates Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Status Invoice Jatuh Tempo</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No Invoice</th>
                                    <th>Customer</th>
                                    <th>Tanggal Invoice</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Sisa Hari</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($overdueInvoices as $invoice)
                                    <tr>
                                        <td>
                                            <strong>{{ $invoice->invoice_number }}</strong>
                                        </td>
                                        <td>{{ $invoice->customer_name }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}
                                        </td>
                                        <td>
                                            @if ($invoice->days_remaining > 7)
                                                <span class="badge bg-success">{{ $invoice->days_remaining }} hari</span>
                                            @elseif($invoice->days_remaining >= 0)
                                                <span class="badge bg-warning">{{ $invoice->days_remaining }} hari</span>
                                            @else
                                                <span class="badge bg-danger">Lewat {{ abs($invoice->days_remaining) }}
                                                    hari</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($invoice->days_remaining > 7)
                                                <span class="badge bg-success-subtle text-success">Aman</span>
                                            @elseif($invoice->days_remaining >= 0)
                                                <span class="badge bg-warning-subtle text-warning">Segera Jatuh
                                                    Tempo</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">Telat Bayar</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                    type="button" data-bs-toggle="dropdown">
                                                    Aksi
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#">Lihat Detail</a></li>
                                                    <li><a class="dropdown-item" href="#">Kirim Reminder</a></li>
                                                    <li><a class="dropdown-item" href="#">Print Invoice</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                @if ($overdueInvoices->count() == 0)
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="mdi mdi-check-circle text-success fs-24"></i>
                                                <p class="mt-2">Semua invoice masih dalam batas waktu yang aman</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <!-- Chart JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        let orderChart;

        // Initialize chart on page load
        document.addEventListener('DOMContentLoaded', function() {
            initOrderChart();
        });

        // Initialize Order Statistics Chart
        function initOrderChart() {
            const ctx = document.getElementById('orderChart').getContext('2d');

            // Data from controller
            const monthlyOrderData = @json($monthlyOrderData);

            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'
            ];

            const data = months.map((month, index) => {
                return monthlyOrderData[index + 1] || 0;
            });

            orderChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Jumlah Order',
                        data: data,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Statistik Order per Bulan'
                        },
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        // Apply filters function
        function applyFilters() {
            const year = document.getElementById('year-filter').value;
            const month = document.getElementById('month-filter').value;

            // Update chart year display
            document.getElementById('chart-year').textContent = year;

            // Fetch new data based on filters
            fetchOrderStatsByYear(year);

            if (month) {
                // If month is selected, also update customer and fleet stats
                refreshCustomerStats(year, month);
                refreshFleetStats(year, month);
            }
        }

        // Fetch order statistics by year
        function fetchOrderStatsByYear(year) {
            fetch(`{{ route('dashboard.order-stats-year') }}?year=${year}`)
                .then(response => response.json())
                .then(data => {
                    updateChart(data);
                })
                .catch(error => {
                    console.error('Error fetching order stats:', error);
                });
        }

        // Update chart with new data
        function updateChart(monthlyData) {
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'
            ];

            const data = months.map((month, index) => {
                return monthlyData[index + 1] || 0;
            });

            orderChart.data.datasets[0].data = data;
            orderChart.update();
        }

        // Refresh customer statistics
        function refreshCustomerStats(year = null, month = null) {
            let url = `{{ route('dashboard.customer-stats') }}`;
            let params = new URLSearchParams();

            if (year) params.append('year', year);
            if (month) params.append('month', month);

            if (params.toString()) {
                url += '?' + params.toString();
            }

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    updateCustomerTable(data);
                })
                .catch(error => {
                    console.error('Error fetching customer stats:', error);
                });
        }

        // Refresh fleet statistics
        function refreshFleetStats(year = null, month = null) {
            let url = `{{ route('dashboard.fleet-stats') }}`;
            let params = new URLSearchParams();

            if (year) params.append('year', year);
            if (month) params.append('month', month);

            if (params.toString()) {
                url += '?' + params.toString();
            }

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    updateFleetTable(data);
                })
                .catch(error => {
                    console.error('Error fetching fleet stats:', error);
                });
        }

        // Update customer table
        function updateCustomerTable(customers) {
            const tbody = document.querySelector('#customers-table tbody');
            tbody.innerHTML = '';

            customers.forEach((customer, index) => {
                const row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-xs me-2">
                                    <div class="avatar-title rounded-circle bg-primary text-white">
                                        ${customer.name.charAt(0)}
                                    </div>
                                </div>
                                ${customer.name}
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary">
                                ${new Intl.NumberFormat('id-ID').format(customer.orders_count)} order
                            </span>
                        </td>
                        <td>
                            <small class="text-muted">
                                ${customer.latest_order ? formatTimeAgo(customer.latest_order) : 'Belum ada order'}
                            </small>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        }

        // Update fleet table
        function updateFleetTable(fleets) {
            const tbody = document.querySelector('#fleets-table tbody');
            tbody.innerHTML = '';

            fleets.forEach((fleet, index) => {
                const row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-xs me-2">
                                    <div class="avatar-title rounded-circle bg-warning text-white">
                                        <i class="mdi mdi-truck"></i>
                                    </div>
                                </div>
                                ${fleet.name}
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-warning-subtle text-warning">
                                ${new Intl.NumberFormat('id-ID').format(fleet.orders_count)} order
                            </span>
                        </td>
                        <td>
                            <small class="text-muted">
                                ${fleet.latest_order ? formatTimeAgo(fleet.latest_order) : 'Belum digunakan'}
                            </small>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        }

        // Format time ago (simple implementation)
        function formatTimeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffInSeconds = Math.floor((now - date) / 1000);

            if (diffInSeconds < 60) return 'Baru saja';
            if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + ' menit lalu';
            if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + ' jam lalu';
            if (diffInSeconds < 2592000) return Math.floor(diffInSeconds / 86400) + ' hari lalu';
            if (diffInSeconds < 31536000) return Math.floor(diffInSeconds / 2592000) + ' bulan lalu';
            return Math.floor(diffInSeconds / 31536000) + ' tahun lalu';
        }
    </script>
@endpush
