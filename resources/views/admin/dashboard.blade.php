@extends('layouts.app')

@push('custome-css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    .stat-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    .chart-container {
        position: relative;
        min-height: 300px;
    }
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }
    .skeleton-loader {
        animation: skeleton-loading 1s linear infinite alternate;
    }
    @keyframes skeleton-loading {
        0% { background-color: hsl(200, 20%, 80%); }
        100% { background-color: hsl(200, 20%, 95%); }
    }
    .filter-card {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0"><i class="fas fa-chart-line"></i> Dashboard</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- Date Filter Section -->
        <div class="filter-card">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0"><i class="fas fa-filter"></i> Filter Dashboard Data</h5>
                </div>
                <div class="col-md-6">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <select class="form-select form-select-sm" id="filterPeriod">
                                <option value="all">All Time</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month" selected>This Month</option>
                                <option value="year">This Year</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>
                        <div class="col-md-4 custom-date-fields" style="display:none;">
                            <input type="date" class="form-control form-control-sm" id="startDate">
                        </div>
                        <div class="col-md-4 custom-date-fields" style="display:none;">
                            <input type="date" class="form-control form-control-sm" id="endDate">
                        </div>
                        <div class="col-md-4">
                            <select class="form-select form-select-sm" id="chartPeriod">
                                <option value="day">Daily</option>
                                <option value="month" selected>Monthly</option>
                                <option value="year">Yearly</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        @php
            $userRoles = Auth::guard('admin')->user()->getRoleNames();
            $isRestrictedUser = $userRoles->contains('dsr') || $userRoles->contains('sr');
        @endphp

        @if(!$isRestrictedUser)
        <div class="row" id="statsCards">
            <!-- Income Card -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card stat-card text-white bg-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-1">Total Income</h6>
                                <h3 class="mb-0">৳ <span id="statIncome" class="skeleton-loader">0</span></h3>
                            </div>
                            <div class="text-white-50">
                                <i class="fas fa-money-bill-wave fa-3x"></i>
                            </div>
                        </div>
                        <small class="mt-2 d-block"><i class="fas fa-arrow-up"></i> From vendor payments</small>
                    </div>
                </div>
            </div>

            <!-- Expenses Card -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card stat-card text-white bg-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-1">Total Expenses</h6>
                                <h3 class="mb-0">৳ <span id="statExpenses" class="skeleton-loader">0</span></h3>
                            </div>
                            <div class="text-white-50">
                                <i class="fas fa-shopping-cart fa-3x"></i>
                            </div>
                        </div>
                        <small class="mt-2 d-block"><i class="fas fa-arrow-down"></i> All expenses</small>
                    </div>
                </div>
            </div>

            <!-- Revenue Card -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card stat-card text-white bg-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-1">Net Revenue</h6>
                                <h3 class="mb-0">৳ <span id="statRevenue" class="skeleton-loader">0</span></h3>
                            </div>
                            <div class="text-white-50">
                                <i class="fas fa-chart-line fa-3x"></i>
                            </div>
                        </div>
                        <small class="mt-2 d-block"><i class="fas fa-equals"></i> Income - Expenses</small>
                    </div>
                </div>
            </div>

            <!-- Profit Card -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card stat-card text-white bg-danger">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-1">Total Profit</h6>
                                <h3 class="mb-0">৳ <span id="statProfit" class="skeleton-loader">0</span></h3>
                            </div>
                            <div class="text-white-50">
                                <i class="fas fa-coins fa-3x"></i>
                            </div>
                        </div>
                        <small class="mt-2 d-block"><i class="fas fa-percentage"></i> From orders</small>
                    </div>
                </div>
            </div>

            <!-- Additional Stats Row -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card stat-card text-white bg-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-1">Total Products</h6>
                                <h3 class="mb-0"><span id="statProducts" class="skeleton-loader">0</span></h3>
                            </div>
                            <div class="text-white-50">
                                <i class="fas fa-box fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card stat-card text-white bg-secondary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-1">Stock Quantity</h6>
                                <h3 class="mb-0"><span id="statStockQty" class="skeleton-loader">0</span></h3>
                            </div>
                            <div class="text-white-50">
                                <i class="fas fa-warehouse fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card stat-card text-white bg-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-1">Stock Value</h6>
                                <h3 class="mb-0">৳ <span id="statStockValue" class="skeleton-loader">0</span></h3>
                            </div>
                            <div class="text-white-50">
                                <i class="fas fa-dollar-sign fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card stat-card text-white" style="background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-1">Pending Payments</h6>
                                <h3 class="mb-0">৳ <span id="statPending" class="skeleton-loader">0</span></h3>
                            </div>
                            <div class="text-white-50">
                                <i class="fas fa-hourglass-half fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Charts Row 1 -->
        <div class="row">
            <!-- Sales Chart -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-area"></i> Sales Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <div class="loading-overlay" id="salesChartLoader">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <div id="salesChart"></div>
                        </div>
                    </div>
                </div>
            </div>

            @if(!$isRestrictedUser)
            <!-- Revenue vs Expenses Chart -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-balance-scale"></i> Income Trend</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <div class="loading-overlay" id="revenueChartLoader">
                                <div class="spinner-border text-success" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <div id="revenueChart"></div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Charts Row 2 -->
        @if(!$isRestrictedUser)
        <div class="row">
            <!-- Profit Chart -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line"></i> Profit Trend</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <div class="loading-overlay" id="profitChartLoader">
                                <div class="spinner-border text-danger" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <div id="profitChart"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expenses by Category Chart -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Expenses by Category</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <div class="loading-overlay" id="expensesChartLoader">
                                <div class="spinner-border text-warning" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <div id="expensesChart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Charts Row 3 -->
        <div class="row">
            <!-- Top Selling Products -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-trophy"></i> Top Selling Products</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <div class="loading-overlay" id="topProductsChartLoader">
                                <div class="spinner-border text-info" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <div id="topProductsChart"></div>
                        </div>
                    </div>
                </div>
            </div>

            @if(!$isRestrictedUser)
            <!-- Stock by Warehouse -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-warehouse"></i> Stock by Warehouse</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <div class="loading-overlay" id="warehouseChartLoader">
                                <div class="spinner-border text-secondary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <div id="warehouseChart"></div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- SR/DSR Performance Charts -->
        <div class="row">
            <!-- Orders by User Chart -->
            <div class="col-lg-12 mb-4">
                <div class="card">
                    <div class="card-header" style="background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);" class="text-white">
                        <h5 class="mb-0 text-white"><i class="fas fa-user-chart"></i> Orders Amount by Sales Team</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <div class="loading-overlay" id="userOrdersChartLoader">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <div id="userOrdersChart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Collection by User Chart -->
            <div class="col-lg-12 mb-4">
                <div class="card">
                    <div class="card-header" style="background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%);" class="text-white">
                        <h5 class="mb-0 text-white"><i class="fas fa-wallet"></i> Collection Amount by Sales Team</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <div class="loading-overlay" id="userCollectionChartLoader">
                                <div class="spinner-border text-success" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <div id="userCollectionChart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables Row -->
        <div class="row">
            <!-- Recent Orders -->
            <div class="col-lg-7 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-shopping-bag"></i> Recent Orders</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Invoice</th>
                                        <th>Vendor</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody id="recentOrdersTable">
                                    <tr><td colspan="5" class="text-center">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Products Table -->
            <div class="col-lg-5 mb-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-star"></i> Best Sellers</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Sold</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody id="topProductsTable">
                                    <tr><td colspan="3" class="text-center">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Damage/Loss Statistics -->
        @if(!$isRestrictedUser)
        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Damage/Return/Lost Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="p-3 border rounded">
                                    <h4 class="text-danger"><i class="fas fa-broken-image"></i></h4>
                                    <h3 id="statDamage" class="skeleton-loader">0</h3>
                                    <p class="text-muted">Damaged Items</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded">
                                    <h4 class="text-warning"><i class="fas fa-undo"></i></h4>
                                    <h3 id="statReturn" class="skeleton-loader">0</h3>
                                    <p class="text-muted">Returned Items</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded">
                                    <h4 class="text-dark"><i class="fas fa-question-circle"></i></h4>
                                    <h3 id="statLost" class="skeleton-loader">0</h3>
                                    <p class="text-muted">Lost Items</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

@endsection

@push('custome-js')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"></script>
<script>
    // Chart instances
    let salesChart, revenueChart, profitChart, expensesChart, topProductsChart, warehouseChart, userOrdersChart, userCollectionChart;
    const isRestrictedUser = {{ $isRestrictedUser ? 'true' : 'false' }};

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Load all data after page loads
        setTimeout(() => {
            if (!isRestrictedUser) {
                loadDashboardStats();
            }
            loadSalesChart();
            if (!isRestrictedUser) {
                loadRevenueChart();
                loadProfitChart();
                loadExpensesChart();
            }
            loadProductsCharts();
            loadRecentOrders();
            loadTopProducts();
            loadUserPerformanceCharts();
        }, 100);

        // Filter change listeners
        document.getElementById('filterPeriod').addEventListener('change', function() {
            if (this.value === 'custom') {
                document.querySelectorAll('.custom-date-fields').forEach(el => el.style.display = 'block');
            } else {
                document.querySelectorAll('.custom-date-fields').forEach(el => el.style.display = 'none');
                refreshAllData();
            }
        });

        document.getElementById('chartPeriod').addEventListener('change', function() {
            refreshCharts();
        });

        document.getElementById('startDate').addEventListener('change', refreshAllData);
        document.getElementById('endDate').addEventListener('change', refreshAllData);
    });

    function getFilterParams() {
        const filter = document.getElementById('filterPeriod').value;
        const params = new URLSearchParams({ filter: filter });

        if (filter === 'custom') {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            if (startDate) params.append('start_date', startDate);
            if (endDate) params.append('end_date', endDate);
        }

        return params.toString();
    }

    function getChartParams() {
        const period = document.getElementById('chartPeriod').value;
        return new URLSearchParams({ period: period }).toString();
    }

    function loadDashboardStats() {
        fetch(`{{ route('admin.dashboard.stats') }}?${getFilterParams()}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('statIncome').textContent = data.data.income;
                    document.getElementById('statExpenses').textContent = data.data.expenses;
                    document.getElementById('statRevenue').textContent = data.data.revenue;
                    document.getElementById('statProfit').textContent = data.data.profit;
                    document.getElementById('statProducts').textContent = data.data.products;
                    document.getElementById('statStockQty').textContent = data.data.stock_quantity;
                    document.getElementById('statStockValue').textContent = data.data.stock_value;
                    document.getElementById('statPending').textContent = data.data.pending_payments;
                    document.getElementById('statDamage').textContent = data.data.damage_count;
                    document.getElementById('statReturn').textContent = data.data.return_count;
                    document.getElementById('statLost').textContent = data.data.lost_count;

                    // Remove skeleton loading
                    document.querySelectorAll('.skeleton-loader').forEach(el => el.classList.remove('skeleton-loader'));
                }
            })
            .catch(error => console.error('Error loading stats:', error));
    }

    function loadSalesChart() {
        fetch(`{{ route('admin.dashboard.charts.sales') }}?${getFilterParams()}&${getChartParams()}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('salesChartLoader').style.display = 'none';

                if (data.success) {
                    const options = {
                        series: [
                            { name: 'Orders', data: data.data.orders },
                            { name: 'Sales', data: data.data.sales },
                            { name: 'Paid', data: data.data.paid }
                        ],
                        chart: { type: 'area', height: 350, toolbar: { show: true } },
                        dataLabels: { enabled: false },
                        stroke: { curve: 'smooth', width: 2 },
                        xaxis: { categories: data.data.labels },
                        colors: ['#0d6efd', '#198754', '#ffc107'],
                        legend: { position: 'top' },
                        tooltip: { shared: true, intersect: false }
                    };

                    if (salesChart) salesChart.destroy();
                    salesChart = new ApexCharts(document.querySelector("#salesChart"), options);
                    salesChart.render();
                }
            })
            .catch(error => {
                document.getElementById('salesChartLoader').style.display = 'none';
                console.error('Error loading sales chart:', error);
            });
    }

    function loadRevenueChart() {
        fetch(`{{ route('admin.dashboard.charts.revenue') }}?${getFilterParams()}&${getChartParams()}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('revenueChartLoader').style.display = 'none';

                if (data.success) {
                    const options = {
                        series: [{
                            name: 'Income',
                            data: data.data.income
                        }],
                        chart: { type: 'line', height: 300, toolbar: { show: false } },
                        stroke: { curve: 'smooth', width: 3 },
                        xaxis: { categories: data.data.labels },
                        colors: ['#198754'],
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.7,
                                opacityTo: 0.3,
                                stops: [0, 90, 100]
                            }
                        }
                    };

                    if (revenueChart) revenueChart.destroy();
                    revenueChart = new ApexCharts(document.querySelector("#revenueChart"), options);
                    revenueChart.render();
                }
            })
            .catch(error => {
                document.getElementById('revenueChartLoader').style.display = 'none';
                console.error('Error loading revenue chart:', error);
            });
    }

    function loadProfitChart() {
        fetch(`{{ route('admin.dashboard.charts.profit') }}?${getFilterParams()}&${getChartParams()}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('profitChartLoader').style.display = 'none';

                if (data.success) {
                    const options = {
                        series: [{ name: 'Profit', data: data.data.profit }],
                        chart: { type: 'bar', height: 350, toolbar: { show: true } },
                        plotOptions: { bar: { borderRadius: 4, horizontal: false } },
                        dataLabels: { enabled: false },
                        xaxis: { categories: data.data.labels },
                        colors: ['#dc3545'],
                        fill: { type: 'gradient', gradient: { shade: 'dark', type: 'vertical', shadeIntensity: 0.5, opacityFrom: 1, opacityTo: 0.7 } }
                    };

                    if (profitChart) profitChart.destroy();
                    profitChart = new ApexCharts(document.querySelector("#profitChart"), options);
                    profitChart.render();
                }
            })
            .catch(error => {
                document.getElementById('profitChartLoader').style.display = 'none';
                console.error('Error loading profit chart:', error);
            });
    }

    function loadExpensesChart() {
        fetch(`{{ route('admin.dashboard.charts.expenses') }}?${getFilterParams()}&${getChartParams()}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('expensesChartLoader').style.display = 'none';

                if (data.success && data.data.by_category) {
                    const options = {
                        series: data.data.by_category.values,
                        chart: { type: 'pie', height: 350 },
                        labels: data.data.by_category.labels,
                        colors: ['#ffc107', '#fd7e14', '#dc3545', '#6f42c1', '#0dcaf0'],
                        legend: { position: 'bottom' },
                        responsive: [{
                            breakpoint: 480,
                            options: { chart: { width: 200 }, legend: { position: 'bottom' } }
                        }]
                    };

                    if (expensesChart) expensesChart.destroy();
                    expensesChart = new ApexCharts(document.querySelector("#expensesChart"), options);
                    expensesChart.render();
                }
            })
            .catch(error => {
                document.getElementById('expensesChartLoader').style.display = 'none';
                console.error('Error loading expenses chart:', error);
            });
    }

    function loadProductsCharts() {
        fetch(`{{ route('admin.dashboard.charts.products') }}?${getFilterParams()}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('topProductsChartLoader').style.display = 'none';
                if (document.getElementById('warehouseChartLoader')) {
                    document.getElementById('warehouseChartLoader').style.display = 'none';
                }

                if (data.success) {
                    // Top Selling Products Chart
                    const topProductsOptions = {
                        series: [{ name: 'Quantity Sold', data: data.data.top_selling.quantities }],
                        chart: { type: 'bar', height: 350 },
                        plotOptions: { bar: { borderRadius: 4, horizontal: true } },
                        dataLabels: { enabled: true },
                        xaxis: { categories: data.data.top_selling.labels },
                        colors: ['#0dcaf0']
                    };

                    if (topProductsChart) topProductsChart.destroy();
                    topProductsChart = new ApexCharts(document.querySelector("#topProductsChart"), topProductsOptions);
                    topProductsChart.render();

                    // Warehouse Stock Chart (only if element exists)
                    if (document.getElementById('warehouseChart')) {
                        const warehouseOptions = {
                            series: [
                                { name: 'Quantity', data: data.data.by_warehouse.quantities },
                                { name: 'Value', data: data.data.by_warehouse.values }
                            ],
                            chart: { type: 'bar', height: 350 },
                            plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
                            dataLabels: { enabled: false },
                            xaxis: { categories: data.data.by_warehouse.labels },
                            colors: ['#6c757d', '#198754'],
                            legend: { position: 'top' }
                        };

                        if (warehouseChart) warehouseChart.destroy();
                        warehouseChart = new ApexCharts(document.querySelector("#warehouseChart"), warehouseOptions);
                        warehouseChart.render();
                    }
                }
            })
            .catch(error => {
                document.getElementById('topProductsChartLoader').style.display = 'none';
                if (document.getElementById('warehouseChartLoader')) {
                    document.getElementById('warehouseChartLoader').style.display = 'none';
                }
                console.error('Error loading products charts:', error);
            });
    }

    function loadRecentOrders() {
        fetch(`{{ route('admin.dashboard.recentOrders') }}?limit=10`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let html = '';
                    data.data.forEach(order => {
                        html += `
                            <tr>
                                <td><small><strong>${order.invoice_id}</strong></small></td>
                                <td><small>${order.vendor}</small></td>
                                <td><small>৳${order.total_amount}</small></td>
                                <td><small><span class="badge bg-info">${order.status}</span></small></td>
                                <td><small>${order.created_at}</small></td>
                            </tr>
                        `;
                    });
                    document.getElementById('recentOrdersTable').innerHTML = html || '<tr><td colspan="5" class="text-center">No orders found</td></tr>';
                }
            })
            .catch(error => {
                document.getElementById('recentOrdersTable').innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error loading orders</td></tr>';
                console.error('Error loading recent orders:', error);
            });
    }

    function loadTopProducts() {
        fetch(`{{ route('admin.dashboard.topProducts') }}?${getFilterParams()}&limit=5`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let html = '';
                    data.data.forEach(product => {
                        html += `
                            <tr>
                                <td><small>${product.product_name}</small></td>
                                <td><small><strong>${product.total_sold}</strong></small></td>
                                <td><small>৳${product.total_revenue}</small></td>
                            </tr>
                        `;
                    });
                    document.getElementById('topProductsTable').innerHTML = html || '<tr><td colspan="3" class="text-center">No data found</td></tr>';
                }
            })
            .catch(error => {
                document.getElementById('topProductsTable').innerHTML = '<tr><td colspan="3" class="text-center text-danger">Error loading data</td></tr>';
                console.error('Error loading top products:', error);
            });
    }

    function loadUserPerformanceCharts() {
        // Load Orders by User Chart
        fetch(`{{ route('admin.dashboard.charts.userOrders') }}?${getFilterParams()}&${getChartParams()}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('userOrdersChartLoader').style.display = 'none';

                if (data.success) {
                    const options = {
                        series: data.data.series,
                        chart: { type: 'bar', height: 350, toolbar: { show: true }, stacked: false },
                        plotOptions: { bar: { borderRadius: 4, horizontal: false, columnWidth: '60%', dataLabels: { position: 'top' } } },
                        dataLabels: { enabled: true, formatter: function(val) { return '৳' + val.toFixed(0); }, offsetY: -20, style: { fontSize: '10px', colors: ['#304758'] } },
                        xaxis: { categories: data.data.labels, labels: { rotate: -45, rotateAlways: false } },
                        colors: ['#667eea', '#764ba2', '#f093fb', '#4facfe', '#43e97b', '#fa709a', '#fee140', '#30cfd0'],
                        legend: { position: 'top', horizontalAlign: 'left' },
                        title: { text: 'Orders Amount by Sales Team (৳)', align: 'center' },
                        yaxis: { labels: { formatter: function(value) { return '৳' + value.toFixed(0); } } },
                        tooltip: { y: { formatter: function(value) { return '৳' + value.toFixed(2); } } }
                    };

                    if (userOrdersChart) userOrdersChart.destroy();
                    userOrdersChart = new ApexCharts(document.querySelector("#userOrdersChart"), options);
                    userOrdersChart.render();
                }
            })
            .catch(error => {
                document.getElementById('userOrdersChartLoader').style.display = 'none';
                console.error('Error loading user orders chart:', error);
            });

        // Load Collection by User Chart
        fetch(`{{ route('admin.dashboard.charts.userCollection') }}?${getFilterParams()}&${getChartParams()}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('userCollectionChartLoader').style.display = 'none';

                if (data.success) {
                    const options = {
                        series: data.data.series,
                        chart: { type: 'line', height: 350, toolbar: { show: true } },
                        stroke: { curve: 'smooth', width: 3 },
                        dataLabels: { enabled: false },
                        xaxis: { categories: data.data.labels, labels: { rotate: -45, rotateAlways: false } },
                        colors: ['#f5576c', '#f093fb', '#4facfe', '#43e97b', '#fa709a', '#fee140', '#30cfd0', '#667eea'],
                        legend: { position: 'top', horizontalAlign: 'left' },
                        markers: { size: 5, hover: { size: 7 } },
                        title: { text: 'Collection Amount by Sales Team (৳)', align: 'center' },
                        yaxis: { labels: { formatter: function(value) { return '৳' + value.toFixed(0); } } },
                        tooltip: { y: { formatter: function(value) { return '৳' + value.toFixed(2); } } }
                    };

                    if (userCollectionChart) userCollectionChart.destroy();
                    userCollectionChart = new ApexCharts(document.querySelector("#userCollectionChart"), options);
                    userCollectionChart.render();
                }
            })
            .catch(error => {
                document.getElementById('userCollectionChartLoader').style.display = 'none';
                console.error('Error loading user collection chart:', error);
            });
    }

    function refreshAllData() {
        if (!isRestrictedUser) {
            loadDashboardStats();
        }
        refreshCharts();
        loadRecentOrders();
        loadTopProducts();
        loadUserPerformanceCharts();
    }

    function refreshCharts() {
        loadSalesChart();
        if (!isRestrictedUser) {
            loadRevenueChart();
            loadProfitChart();
            loadExpensesChart();
        }
        loadProductsCharts();
    }
</script>
@endpush
