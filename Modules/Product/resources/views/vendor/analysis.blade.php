@extends('layouts.app')

@push('custome-css')
<style>
    .analysis-card {
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        border: none;
    }

    .analysis-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .metric-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .metric-value {
        font-size: 2rem;
        font-weight: bold;
        margin: 10px 0;
    }

    .metric-label {
        font-size: 0.875rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .percentage-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
        display: inline-block;
        margin-left: 8px;
    }

    .percentage-up {
        background-color: #d4edda;
        color: #155724;
    }

    .percentage-down {
        background-color: #f8d7da;
        color: #721c24;
    }

    .comparison-row {
        border-bottom: 1px solid #e9ecef;
        padding: 15px 0;
    }

    .comparison-row:last-child {
        border-bottom: none;
    }

    .period-label {
        font-size: 0.8rem;
        color: #6c757d;
        font-weight: 500;
        text-transform: uppercase;
    }

    .vendor-name-cell {
        color: #2c3e50;
        min-width: 150px;
    }

    .table-responsive {
        border-radius: 10px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    @media (max-width: 768px) {
        .table {
            font-size: 0.85rem;
        }

        .percentage-badge {
            font-size: 0.7rem;
            padding: 2px 6px;
        }
    }

    .filter-section {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 25px;
    }

    .summary-totals {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 25px;
    }

    .header-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
    }

    .progress-wrapper {
        position: relative;
        height: 8px;
        background-color: #e9ecef;
        border-radius: 10px;
        overflow: hidden;
        margin-top: 5px;
    }

    .progress-bar-custom {
        height: 100%;
        border-radius: 10px;
        transition: width 0.6s ease;
    }

    .bg-gradient-success {
        background: linear-gradient(90deg, #56ab2f 0%, #a8e063 100%);
    }

    .bg-gradient-danger {
        background: linear-gradient(90deg, #eb3349 0%, #f45c43 100%);
    }

    .badge-metric {
        padding: 8px 15px;
        font-size: 0.9rem;
        border-radius: 8px;
    }

    .icon-metric {
        font-size: 1.2rem;
        margin-right: 5px;
    }

    .all-time-badge {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    /* Compact table styling for small screens */
    .table-responsive {
        font-size: 0.9rem;
    }

    @media (max-width: 1400px) {
        .table-responsive {
            font-size: 0.85rem;
        }
        .percentage-badge {
            font-size: 0.75rem;
            padding: 3px 8px;
        }
    }

    .place-by-item {
        padding: 6px 10px;
        margin: 3px 0;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 8px;
        font-size: 0.85rem;
        border-left: 4px solid #28a745;
        transition: all 0.2s ease;
    }

    .place-by-item:hover {
        transform: translateX(3px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .place-by-name {
        color: #495057;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .place-by-count {
        color: #007bff;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .place-by-amount {
        color: #28a745;
        font-weight: 700;
        font-size: 0.9rem;
    }

    .place-by-details {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
</style>
@endpush

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Vendor Analysis Report</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.vendorIndex') }}">Vendors</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Analysis</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- Header with Period Info -->
        <div class="header-gradient">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2"><i class="mdi mdi-chart-line"></i> Vendor Performance Analysis</h2>
                    <p class="mb-1"><strong>Current Period:</strong> {{ $startDate->format('d M, Y') }} - {{ $endDate->format('d M, Y') }} ({{ $daysDiff }} days)</p>
                    <p class="mb-0"><strong>Previous Period:</strong> {{ $previousStartDate->format('d M, Y') }} - {{ $previousEndDate->format('d M, Y') }} ({{ $daysDiff }} days)</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="badge bg-white text-dark p-3" style="font-size: 1rem;">
                        <i class="mdi mdi-finance"></i> Comparative Analysis
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('admin.vendorAnalysis') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold"><i class="mdi mdi-calendar-start"></i> Start Date</label>
                        <input type="date" name="start_date" class="form-control"
                               value="{{ $startDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold"><i class="mdi mdi-calendar-end"></i> End Date</label>
                        <input type="date" name="end_date" class="form-control"
                               value="{{ $endDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold"><i class="mdi mdi-domain"></i> Vendors</label>
                        <select name="vendor_id[]" class="form-select select2" multiple>
                            @foreach($allVendors as $vendor)
                                <option value="{{ $vendor->id }}"
                                    {{ collect(request('vendor_id'))->contains($vendor->id) ? 'selected' : '' }}>
                                    {{ $vendor->shop_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold"><i class="mdi mdi-account-tie"></i> Placed By</label>
                        <select name="place_by[]" class="form-select select2" multiple>
                            @foreach($admins as $admin)
                                <option value="{{ $admin->id }}"
                                    {{ collect(request('place_by'))->contains($admin->id) ? 'selected' : '' }}>
                                    {{ $admin->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="mdi mdi-filter"></i> Apply Filters
                        </button>
                        <a href="{{ route('admin.vendorAnalysis') }}" class="btn btn-secondary btn-lg">
                            <i class="mdi mdi-refresh"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Pagination Limit Selector -->
        <div class="row mb-3">
            <div class="col-md-12">
                <form method="GET" action="{{ route('admin.vendorAnalysis') }}" class="d-inline-flex align-items-center gap-2">
                    @foreach(request()->except(['limit', 'page']) as $key => $value)
                        @if(is_array($value))
                            @foreach($value as $val)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $val }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <label class="form-label mb-0 fw-bold">Show:</label>
                    <select name="limit" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                        <option value="10" {{ $limit == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ $limit == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ $limit == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $limit == 100 ? 'selected' : '' }}>100</option>
                        <option value="500" {{ $limit == 500 ? 'selected' : '' }}>500</option>
                    </select>
                    <span class="text-muted">entries</span>
                </form>
            </div>
        </div>

        <!-- Summary Totals -->
        <div class="summary-totals">
            <h4 class="mb-4"><i class="mdi mdi-chart-box"></i> Overall Summary</h4>
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="period-label text-white">Current Period</div>
                        <div class="metric-value">{{ $totals['current']['order_count'] }}</div>
                        <div class="metric-label">Total Orders</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="period-label text-white">Current Period</div>
                        <div class="metric-value">৳{{ number_format($totals['current']['total_amount'], 2) }}</div>
                        <div class="metric-label">Order Amount</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="period-label text-white">Current Period</div>
                        <div class="metric-value">৳{{ number_format($totals['current']['collected'], 2) }}</div>
                        <div class="metric-label">Collected</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="period-label text-white">Current Period</div>
                        <div class="metric-value">৳{{ number_format($totals['current']['period_due'], 2) }}</div>
                        <div class="metric-label">Period Due Amount</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vendor Analysis Table -->
        <div class="card analysis-card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="mdi mdi-table-large"></i> Vendor-wise Performance Analysis</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th rowspan="2" class="align-middle text-center">SL</th>
                                <th rowspan="2" class="align-middle">Vendor Details</th>
                                <th colspan="2" class="text-center bg-info text-white">Orders</th>
                                <th colspan="2" class="text-center bg-warning">Order Amount</th>
                                <th colspan="2" class="text-center bg-success text-white">Collected</th>
                                <th colspan="2" class="text-center bg-danger text-white">Due Amount</th>
                                <th rowspan="2" class="text-center align-middle">Order & Collection<br><small>(Current Period)</small></th>
                                <th rowspan="2" class="text-center align-middle">All-Time Stats</th>
                            </tr>
                            <tr>
                                <th class="text-center">Current</th>
                                <th class="text-center">Previous</th>
                                <th class="text-center">Current</th>
                                <th class="text-center">Previous</th>
                                <th class="text-center">Current</th>
                                <th class="text-center">Previous</th>
                                <th class="text-center">Current</th>
                                <th class="text-center">Previous</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vendorAnalysis as $key => $analysis)
                                <tr>
                                    <td class="text-center">{{ ($vendorAnalysis->currentPage() - 1) * $vendorAnalysis->perPage() + $key + 1 }}</td>
                                    <td class="vendor-name-cell">
                                        <div class="fw-bold">
                                            <i class="mdi mdi-domain"></i> {{ $analysis['vendor']->shop_name }}
                                        </div>
                                        <small class="text-muted d-block">
                                            <i class="mdi mdi-phone"></i> {{ $analysis['vendor']->mobile }}
                                        </small>
                                        @if($analysis['vendor']->full_address)
                                            <small class="text-muted d-block">
                                                <i class="mdi mdi-map-marker"></i> {{ $analysis['vendor']->full_address }}
                                            </small>
                                        @endif
                                    </td>

                                    <!-- Orders -->
                                    <td class="text-center">
                                        <strong>{{ $analysis['current']['order_count'] }}</strong>
                                        @if($analysis['changes']['order_count'] != 0)
                                            <br>
                                            <span class="percentage-badge {{ $analysis['changes']['order_count'] >= 0 ? 'percentage-up' : 'percentage-down' }}">
                                                <i class="mdi mdi-{{ $analysis['changes']['order_count'] >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                                {{ number_format(abs($analysis['changes']['order_count']), 1) }}%
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center text-muted">{{ $analysis['previous']['order_count'] }}</td>

                                    <!-- Order Amount -->
                                    <td class="text-end">
                                        <strong>৳{{ number_format($analysis['current']['total_amount'], 2) }}</strong>
                                        @if($analysis['changes']['amount'] != 0)
                                            <br>
                                            <span class="percentage-badge {{ $analysis['changes']['amount'] >= 0 ? 'percentage-up' : 'percentage-down' }}">
                                                <i class="mdi mdi-{{ $analysis['changes']['amount'] >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                                {{ number_format(abs($analysis['changes']['amount']), 1) }}%
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end text-muted">
                                        ৳{{ number_format($analysis['previous']['total_amount'], 2) }}
                                    </td>

                                    <!-- Collected -->
                                    <td class="text-end">
                                        <strong class="text-success">৳{{ number_format($analysis['current']['collected'], 2) }}</strong>
                                        @if($analysis['changes']['collection'] != 0)
                                            <br>
                                            <span class="percentage-badge {{ $analysis['changes']['collection'] >= 0 ? 'percentage-up' : 'percentage-down' }}">
                                                <i class="mdi mdi-{{ $analysis['changes']['collection'] >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                                {{ number_format(abs($analysis['changes']['collection']), 1) }}%
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end text-muted">
                                        ৳{{ number_format($analysis['previous']['collected'], 2) }}
                                    </td>

                                    <!-- Due -->
                                    <td class="text-end">
                                        <strong class="text-danger">৳{{ number_format($analysis['current']['due'], 2) }}</strong>
                                        @if($analysis['changes']['due'] != 0)
                                            <br>
                                            <span class="percentage-badge {{ $analysis['changes']['due'] <= 0 ? 'percentage-up' : 'percentage-down' }}">
                                                <i class="mdi mdi-{{ $analysis['changes']['due'] >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                                {{ number_format(abs($analysis['changes']['due']), 1) }}%
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end text-muted">
                                        ৳{{ number_format($analysis['previous']['due'], 2) }}
                                    </td>

                                    <!-- Order & Collection Breakdown -->
                                    <td>
                                        <div class="d-flex flex-column gap-2">
                                            @if($analysis['place_by_breakdown']->count() > 0)
                                                <div>
                                                    <div class="text-primary fw-bold mb-2" style="font-size: 0.85rem;">
                                                        <i class="mdi mdi-cart"></i> Orders Placed:
                                                    </div>
                                                    @foreach($analysis['place_by_breakdown'] as $placer)
                                                        <div class="place-by-item" style="border-left-color: #007bff;">
                                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                                <span class="place-by-name">
                                                                    <i class="mdi mdi-account-tie"></i> {{ $placer['admin']->name ?? 'N/A' }}
                                                                </span>
                                                                <span class="place-by-count">
                                                                    {{ $placer['order_count'] }} <small class="text-muted">orders</small>
                                                                </span>
                                                            </div>
                                                            <div class="text-end">
                                                                <small class="text-muted">Amount:</small>
                                                                <strong class="text-warning">৳{{ number_format($placer['amount'], 0) }}</strong>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            @if($analysis['collection_breakdown']->count() > 0)
                                                <div>
                                                    <div class="text-success fw-bold mb-2 mt-2" style="font-size: 0.85rem;">
                                                        <i class="mdi mdi-cash-multiple"></i> Collections:
                                                    </div>
                                                    @foreach($analysis['collection_breakdown'] as $collector)
                                                        <div class="place-by-item">
                                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                                <span class="place-by-name">
                                                                    <i class="mdi mdi-account-cash"></i> {{ $collector['admin']->name ?? 'N/A' }}
                                                                </span>
                                                                <span class="place-by-count">
                                                                    {{ $collector['order_count'] }} <small class="text-muted">orders</small>
                                                                </span>
                                                            </div>
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <small class="text-muted">
                                                                    <i class="mdi mdi-swap-horizontal"></i> {{ $collector['transaction_count'] }} transactions
                                                                </small>
                                                                <span class="place-by-amount">
                                                                    ৳{{ number_format($collector['collected'], 0) }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            @if($analysis['place_by_breakdown']->count() == 0 && $analysis['collection_breakdown']->count() == 0)
                                                <span class="text-muted">No activity</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- All-Time Stats -->
                                    <td class="text-center">
                                        <div class="d-flex flex-column gap-1">
                                            @if($analysis['old_due'] > 0)
                                                <span class="badge badge-metric bg-warning text-dark" title="Old Due (Before Software)">
                                                    <i class="mdi mdi-history icon-metric"></i>৳{{ number_format($analysis['old_due'], 0) }} Old Due
                                                </span>
                                            @endif
                                            <span class="badge badge-metric bg-primary">
                                                <i class="mdi mdi-cart icon-metric"></i>{{ $analysis['all_time']['order_count'] }} Orders
                                            </span>
                                            <span class="badge badge-metric bg-info">
                                                <i class="mdi mdi-currency-usd icon-metric"></i>৳{{ number_format($analysis['all_time']['total_amount'], 0) }}
                                            </span>
                                            <span class="badge badge-metric bg-success">
                                                <i class="mdi mdi-cash-check icon-metric"></i>৳{{ number_format($analysis['all_time']['collected'], 0) }}
                                            </span>
                                            <span class="badge badge-metric bg-danger">
                                                <i class="mdi mdi-alert-circle icon-metric"></i>৳{{ number_format($analysis['all_time']['due'], 0) }} Total Due
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center py-5">
                                        <i class="mdi mdi-information-outline" style="font-size: 3rem; color: #6c757d;"></i>
                                        <p class="mt-2 text-muted">No vendor data found for the selected criteria</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($vendorAnalysis->count() > 0)
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="2" class="text-end">Grand Total:</th>
                                <th class="text-center">{{ $totals['current']['order_count'] }}</th>
                                <th class="text-center">{{ $totals['previous']['order_count'] }}</th>
                                <th class="text-end">৳{{ number_format($totals['current']['total_amount'], 2) }}</th>
                                <th class="text-end">৳{{ number_format($totals['previous']['total_amount'], 2) }}</th>
                                <th class="text-end">৳{{ number_format($totals['current']['collected'], 2) }}</th>
                                <th class="text-end">৳{{ number_format($totals['previous']['collected'], 2) }}</th>
                                <th class="text-end">৳{{ number_format($totals['current']['due'], 2) }}</th>
                                <th class="text-end">৳{{ number_format($totals['previous']['due'], 2) }}</th>
                                <th></th>
                                <th></th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        @if($vendorAnalysis->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Showing {{ $vendorAnalysis->firstItem() }} to {{ $vendorAnalysis->lastItem() }} of {{ $vendorAnalysis->total() }} entries
                </div>
                <div>
                    {{ $vendorAnalysis->appends(request()->query())->links() }}
                </div>
            </div>
        @endif

        <!-- Legend -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="mb-3"><i class="mdi mdi-information"></i> Legend & Features</h5>
                <div class="row">
                    <div class="col-md-4">
                        <h6 class="text-primary mb-2">Percentage Indicators</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2"><span class="percentage-badge percentage-up"><i class="mdi mdi-arrow-up"></i></span> Positive change (increase)</li>
                            <li class="mb-2"><span class="percentage-badge percentage-down"><i class="mdi mdi-arrow-down"></i></span> Negative change (decrease)</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-primary mb-2">Period Definitions</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2"><strong>Current Period:</strong> Selected date range</li>
                            <li class="mb-2"><strong>Previous Period:</strong> Same duration before current period</li>
                            <li class="mb-2"><strong>All-Time Stats:</strong> Lifetime vendor performance</li>
                            <li class="mb-2"><strong>Old Due:</strong> Balance from before software implementation (manual entries)</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-primary mb-2">Visual Features</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <span class="percentage-badge percentage-up"><i class="mdi mdi-arrow-up"></i> 25%</span>
                                Percentage badges showing change from previous to current period
                            </li>
                            <li class="mb-2"><span class="percentage-badge percentage-up"><i class="mdi mdi-arrow-up"></i></span> <strong>Green:</strong> Positive trend (increase in orders/collections, decrease in due)</li>
                            <li class="mb-2"><span class="percentage-badge percentage-down"><i class="mdi mdi-arrow-down"></i></span> <strong>Red:</strong> Negative trend (decrease in orders/collections, increase in due)</li>
                            <li class="mb-2"><strong>Responsive Design:</strong> Optimized for all screen sizes</li>
                            <li class="mb-2"><strong>Order & Collection:</strong> Shows both order placement and collection activity for each vendor</li>
                            <li class="mb-2 text-muted"><small>- Orders Placed: Who placed orders, count, and total amount</small></li>
                            <li class="mb-2 text-muted"><small>- Collections: Who collected payments, number of transactions, and total collected</small></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('custome-js')
<script>
    $(document).ready(function() {
        // Initialize Select2 for multi-select dropdowns
        $('.select2').select2({
            placeholder: 'Select...',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush
