@extends('layouts.app')

@section('title', 'Sell Summary Report')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Sell Summary Report</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Sell Summary</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Sell Summary Report</h3>
                    </div>
                    <div class="card-body">
                        <!-- Filter Form -->
                        <form method="GET" action="{{ route('admin.reportSellSummary') }}" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Date From</label>
                                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Date To</label>
                                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Product</label>
                                    <select name="product_id" class="form-select select2">
                                        <option value="">All Products</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="mdi mdi-filter"></i> Filter
                                        </button>
                                        <a href="{{ route('admin.reportSellSummary') }}" class="btn btn-sm btn-secondary">
                                            <i class="mdi mdi-refresh"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Summary Cards -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0">Total Purchase Price</h6>
                                                <h3 class="mb-0">৳{{ number_format($totalPurchasePrice, 2) }}</h3>
                                            </div>
                                            <div>
                                                <i class="mdi mdi-cart-arrow-down" style="font-size: 3rem; opacity: 0.5;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0">Total Sell Price</h6>
                                                <h3 class="mb-0">৳{{ number_format($totalSellPrice, 2) }}</h3>
                                            </div>
                                            <div>
                                                <i class="mdi mdi-cart-arrow-up" style="font-size: 3rem; opacity: 0.5;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0">Total Collected</h6>
                                                <h3 class="mb-0">৳{{ number_format($totalCollected, 2) }}</h3>
                                            </div>
                                            <div>
                                                <i class="mdi mdi-cash-multiple" style="font-size: 3rem; opacity: 0.5;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0">Tentative Profit</h6>
                                                <h3 class="mb-0">৳{{ number_format($tentativeProfit, 2) }}</h3>
                                                <small>Before Discount</small>
                                            </div>
                                            <div>
                                                <i class="mdi mdi-chart-line" style="font-size: 3rem; opacity: 0.5;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">

                            <div class="col-md-3">
                                <div class="card bg-secondary text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0">Damage/Return/Lost</h6>
                                                <h3 class="mb-0">৳{{ number_format($totalDamageReturnLostCost, 2) }}</h3>
                                                <small>Product Loss Cost</small>
                                            </div>
                                            <div>
                                                <i class="mdi mdi-package-variant-closed-remove" style="font-size: 3rem; opacity: 0.5;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card" style="background-color: #361caa; color: white;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0">Total Expenses</h6>
                                                <h3 class="mb-0">৳{{ number_format($totalExpenses, 2) }}</h3>
                                                <small>Operating Costs</small>
                                            </div>
                                            <div>
                                                <i class="mdi mdi-cash-remove" style="font-size: 3rem; opacity: 0.5;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-dark text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0">Final Profit</h6>
                                                <h3 class="mb-0">৳{{ number_format($actualProfitAfterDeductions, 2) }}</h3>
                                                <small>After All Deductions</small>
                                            </div>
                                            <div>
                                                <i class="mdi mdi-trending-up" style="font-size: 3rem; opacity: 0.5;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-3">
                                <a href="{{ route('admin.reportDueOrdersList') }}?date_from={{ $dateFrom }}&date_to={{ $dateTo }}&product_id={{ request('product_id') }}" class="text-decoration-none">
                                    <div class="card bg-danger text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-0">Total Due Amount</h6>
                                                    <h3 class="mb-0">৳{{ number_format($totalDue, 2) }}</h3>
                                                    <small>After All Deductions</small>
                                                </div>
                                                <div>
                                                    <i class="mdi mdi-alert-circle" style="font-size: 3rem; opacity: 0.5;"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>

                            </div>

                        </div>

                        <div class="row g-3 mb-4">

                        </div>

                        <!-- Export Button -->
                        <div class="mb-3">
                            <button type="button" class="btn btn-success btn-sm" onclick="exportSummaryToExcel()">
                                <i class="mdi mdi-file-excel"></i> Export Summary to Excel
                            </button>
                        </div>

                        <!-- Summary Table -->
                        <div class="table-responsive">
                            <table id="summaryTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Metric</th>
                                        <th class="text-end">Amount (৳)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Total Purchase Price</strong></td>
                                        <td class="text-end">{{ number_format($totalPurchasePrice, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total Sell Price</strong></td>
                                        <td class="text-end">{{ number_format($totalSellPrice, 2) }}</td>
                                    </tr>

                                     <tr class="table-primary">
                                        <td><strong>Total Return</strong></td>
                                        <td class="text-end text-primary">({{ number_format($totalReturnCost, 2) }})</td>
                                    </tr>
                                    <tr class="table-warning">
                                        <td><strong>(-) Total Discount</strong></td>
                                        <td class="text-end text-danger">({{ number_format($totalDiscount, 2) }})</td>
                                    </tr>
                                    <tr class="table-warning">
                                        <td><strong>(-) Total Damage</strong></td>
                                        <td class="text-end text-danger">({{ number_format($totalDamageCost, 2) }})</td>
                                    </tr>

                                    <tr>
                                        <td><strong>Total Collected Money</strong></td>
                                        <td class="text-end">{{ number_format($totalCollected, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tentative Profit</strong> <small>(Before Discount)</small></td>
                                        <td class="text-end">{{ number_format($tentativeProfit, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Actual Profit</strong> <small>(After Discount)</small></td>
                                        <td class="text-end">{{ number_format($actualProfit, 2) }}</td>
                                    </tr>

                                    <tr class="table-warning">
                                        <td><strong>(-) Total Expenses</strong></td>
                                        <td class="text-end text-danger">({{ number_format($totalExpenses, 2) }})</td>
                                    </tr>
                                    <tr class="table-success">
                                        <td><strong>Final Profit</strong> <small>(After All Deductions)</small></td>
                                        <td class="text-end"><strong>{{ number_format($actualProfitAfterDeductions, 2) }}</strong></td>
                                    </tr>
                                    <tr class="table-danger">
                                        <td><strong>Total Money Due</strong></td>
                                        <td class="text-end"><strong>{{ number_format($totalDue, 2) }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .clickable-card {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .clickable-card:hover {
        transform: scale(1.02);
        box-shadow: 0 8px 16px rgba(0,0,0,0.3);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/table2excel@1.0.4/dist/table2excel.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    });

    function exportSummaryToExcel() {
        var table2excel = new Table2Excel();
        table2excel.export(document.getElementById('summaryTable'), 'Sell_Summary_Report');
    }
</script>
@endpush
