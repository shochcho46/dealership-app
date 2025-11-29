@extends('layouts.app')

@section('title', 'Profitable Product Report')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Profitable Product</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Profitable Product</li>
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
                    <h3 class="card-title">Profitable Product Report</h3>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="GET" action="{{ route('admin.reportProfitableProduct') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Date From</label>
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date To</label>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="mdi mdi-filter"></i> Filter
                                    </button>
                                    <a href="{{ route('admin.reportProfitableProduct') }}" class="btn btn-secondary">
                                        <i class="mdi mdi-refresh"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Export Button -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-success btn-sm" onclick="exportTableToExcel('profitableProductTable', 'Profitable_Products')">
                            <i class="mdi mdi-file-excel"></i> Export to Excel
                        </button>
                    </div>

                    <!-- Report Table -->
                    <div class="table-responsive">
                        <table id="profitableProductTable" class="table table-bordered table-striped table-hover">
                            <thead class="">
                                <tr>
                                    <th>Rank</th>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>Total Sold Qty</th>
                                    <th>Total Revenue</th>
                                    <th>Total Cost</th>
                                    <th>Total Profit</th>
                                    <th>Profit Margin %</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalQty = 0;
                                    $totalRevenue = 0;
                                    $totalCost = 0;
                                    $totalProfit = 0;
                                @endphp
                                @forelse($products as $key => $product)
                                    @php
                                        $totalQty += $product['total_sold_qty'];
                                        $totalRevenue += $product['total_revenue'];
                                        $totalCost += $product['total_cost'];
                                        $totalProfit += $product['total_profit'];

                                        // Determine badge color based on profit margin
                                        if ($product['profit_margin'] >= 30) {
                                            $badgeClass = 'bg-success';
                                            $statusText = 'Excellent';
                                        } elseif ($product['profit_margin'] >= 20) {
                                            $badgeClass = 'bg-info';
                                            $statusText = 'Good';
                                        } elseif ($product['profit_margin'] >= 10) {
                                            $badgeClass = 'bg-warning';
                                            $statusText = 'Average';
                                        } else {
                                            $badgeClass = 'bg-danger';
                                            $statusText = 'Low';
                                        }
                                    @endphp
                                    <tr>
                                        <td class="text-center text-dark">
                                            @if($key < 3)
                                                <span class="badge bg-gradient-{{ $key == 0 ? 'warning' : ($key == 1 ? 'secondary' : 'info') }} text-dark">
                                                    #{{ $key + 1 }}
                                                </span>
                                            @else
                                                <span class="badge text-dark">#{{ $key + 1 }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                        </td>
                                        <td>{{ $product['name'] }}</td>
                                        <td class="text-end">{{ bd_number_format($product['total_sold_qty'], 0) }}</td>
                                        <td class="text-end">৳{{ bd_number_format($product['total_revenue'], 2) }}</td>
                                        <td class="text-end">৳{{ bd_number_format($product['total_cost'], 2) }}</td>
                                        <td class="text-end fw-bold text-success">৳{{ bd_number_format($product['total_profit'], 2) }}</td>
                                        <td class="text-end">
                                            <span class="badge {{ $badgeClass }}">
                                                {{ number_format($product['profit_margin'], 2) }}%
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $badgeClass }}">{{ $statusText }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No data found for the selected period</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-secondary">
                                <tr>
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th class="text-end">{{ number_format($totalQty, 0) }}</th>
                                    <th class="text-end">৳{{ bd_number_format($totalRevenue, 2) }}</th>
                                    <th class="text-end">৳{{ bd_number_format($totalCost, 2) }}</th>
                                    <th class="text-end fw-bold text-success">৳{{ bd_number_format($totalProfit, 2) }}</th>
                                    <th class="text-end">
                                        @if($totalRevenue > 0)
                                            {{ number_format(($totalProfit / $totalRevenue) * 100, 2) }}%
                                        @else
                                            0.00%
                                        @endif
                                    </th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Summary Cards -->
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Products Sold</h5><br>
                                    <h2>{{ $products->count() }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Revenue</h5><br>
                                    <h2>৳{{ bd_number_format($totalRevenue, 2) }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Cost</h5><br>
                                    <h2>৳{{ bd_number_format($totalCost, 2) }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Profit</h5><br>
                                    <h2>৳{{ bd_number_format($totalProfit, 2) }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>
    </div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/table2excel@1.0.4/dist/table2excel.min.js"></script>
<script>
    $(document).ready(function() {
        $('#profitableProductTable').DataTable({
            pageLength: 25,
            order: [[6, 'desc']], // Order by profit by default
            columnDefs: [
                { orderable: false, targets: [1, 8] }
            ]
        });
    });

    function exportTableToExcel(tableID, filename = '') {
        var table2excel = new Table2Excel();
        table2excel.export(document.getElementById(tableID), filename);
    }
</script>
@endpush
