@extends('layouts.app')

@section('title', 'Due Orders List')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Due Orders List</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reportSellSummary') }}">Sell Summary</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Due Orders</li>
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
                        <h3 class="card-title">Due Orders List</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.reportSellSummary') }}" class="btn btn-sm btn-secondary">
                                <i class="mdi mdi-arrow-left"></i> Back to Summary
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Summary Cards -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h6 class="mb-0">Total Order Amount</h6>
                                        <h3 class="mb-0">৳{{ number_format($totalAmount, 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h6 class="mb-0">Total Paid Amount</h6>
                                        <h3 class="mb-0">৳{{ number_format($totalPaid, 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-danger text-white">
                                    <div class="card-body">
                                        <h6 class="mb-0">Total Due Amount</h6>
                                        <h3 class="mb-0">৳{{ number_format($totalDue, 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Form -->
                        <form method="GET" action="{{ route('admin.reportDueOrdersList') }}" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Date From</label>
                                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Date To</label>
                                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                                </div>
                                <div class="col-md-3">
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
                                    <label class="form-label">Limit</label>
                                    <select name="limit" class="form-select">
                                        <option value="10" {{ request('limit') == 10 ? 'selected' : '' }}>10</option>
                                        <option value="25" {{ request('limit') == 25 ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ request('limit') == 50 || !request('limit') ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('limit') == 100 ? 'selected' : '' }}>100</option>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-flex gap-2 flex-column">
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="mdi mdi-filter"></i> Filter
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <a href="{{ route('admin.reportDueOrdersList') }}" class="btn btn-sm btn-secondary">
                                        <i class="mdi mdi-refresh"></i> Reset
                                    </a>
                                    <button type="button" class="btn btn-sm btn-success" onclick="exportTableToExcel()">
                                        <i class="mdi mdi-file-excel"></i> Export to Excel
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Due Orders Table -->
                        <div class="table-responsive">
                            <table id="dueOrdersTable" class="table table-bordered table-striped table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>SL</th>
                                        <th>Invoice ID</th>
                                        <th>Date</th>
                                        <th>Vendor</th>
                                        <th>Placed By</th>
                                        <th class="text-end">Total Amount</th>
                                        <th class="text-end">Paid Amount</th>
                                        <th class="text-end">Due Amount</th>
                                        <th>Payment Status</th>
                                        <th>Order Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($dueOrders as $key => $order)
                                        @php
                                            $dueAmount = $order->total_amount - ($order->paid_amount ?? 0);
                                        @endphp
                                        <tr>
                                            <td>{{ $dueOrders->firstItem() + $key }}</td>
                                            <td>
                                                <a href="{{ route('invoices.preview', $order->id) }}" target="_blank">
                                                    {{ $order->invoice_id }}
                                                </a>
                                            </td>
                                            <td>{{ $order->created_at->format('d M Y') }}</td>
                                            <td>{{ $order->vendor->shop_name ?? 'N/A' }}</td>
                                            <td>{{ $order->placeBy->name ?? 'N/A' }}</td>
                                            <td class="text-end">৳{{ number_format($order->total_amount, 2) }}</td>
                                            <td class="text-end">৳{{ number_format($order->paid_amount ?? 0, 2) }}</td>
                                            <td class="text-end text-danger"><strong>৳{{ number_format($dueAmount, 2) }}</strong></td>
                                            <td>
                                                <span class="badge {{ $order->payment_status_badge_class }}">
                                                    {{ $order->payment_status_text }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $order->status_badge_class }}">
                                                    {{ $order->orderStatus->name ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('invoices.preview', $order->id) }}" target="_blank" class="btn btn-sm btn-primary">
                                                    <i class="mdi mdi-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center">No due orders found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if($dueOrders->count() > 0)
                                <tfoot class="table-secondary">
                                    <tr>
                                        <th colspan="5" class="text-end">Page Total:</th>
                                        <th class="text-end">৳{{ number_format($pageTotal, 2) }}</th>
                                        <th class="text-end">৳{{ number_format($pagePaid, 2) }}</th>
                                        <th class="text-end text-danger">৳{{ number_format($pageDue, 2) }}</th>
                                        <th colspan="3"></th>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-end">Total:</th>
                                        <th class="text-end">৳{{ number_format($totalAmount, 2) }}</th>
                                        <th class="text-end">৳{{ number_format($totalPaid, 2) }}</th>
                                        <th class="text-end text-danger"><strong>৳{{ number_format($totalDue, 2) }}</strong></th>
                                        <th colspan="3"></th>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>

                        @if($dueOrders->hasPages())
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    Showing {{ $dueOrders->firstItem() ?? 0 }} to {{ $dueOrders->lastItem() ?? 0 }} of {{ $dueOrders->total() }} entries
                                </div>
                                <div>
                                    {{ $dueOrders->withQueryString()->links() }}
                                </div>
                            </div>
                        @endif

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

    function exportTableToExcel() {
        var table2excel = new Table2Excel();
        table2excel.export(document.getElementById('dueOrdersTable'), 'Due_Orders_Report');
    }
</script>
@endpush
