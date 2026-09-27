@extends('layouts.app')

@section('title', 'Order Report')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Order Management</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Orders</li>
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
                    <h3 class="card-title">Order Report</h3>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="GET" action="{{ route('admin.reportOrderReport') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label">Date From</label>
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date To</label>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Limit</label>
                                <select name="limit" class="form-select">
                                    <option value="25" {{ request('limit') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('limit', 50) == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('limit') == 100 ? 'selected' : '' }}>100</option>
                                    <option value="200" {{ request('limit') == 200 ? 'selected' : '' }}>200</option>
                                    <option value="all" {{ request('limit') == 'all' ? 'selected' : '' }}>All</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Company</label>
                                <select name="company_id[]" class="form-select select2" multiple>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" {{ collect(request('company_id'))->contains($company->id) ? 'selected' : '' }}>
                                            {{ $company->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Vendor</label>
                                <select name="vendor_id[]" class="form-select select2" multiple>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ collect(request('vendor_id'))->contains($vendor->id) ? 'selected' : '' }}>
                                            {{ $vendor->shop_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Product</label>
                                <select name="product_id[]" class="form-select select2" multiple>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ collect(request('product_id'))->contains($product->id) ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-2">
                                <label class="form-label">Placed By</label>
                                <select name="place_by[]" class="form-select select2" multiple>
                                    @foreach($admins as $admin)
                                        <option value="{{ $admin->id }}" {{ collect(request('place_by'))->contains($admin->id) ? 'selected' : '' }}>
                                            {{ $admin->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Status Filter</label>
                                <select name="status_filter[]" class="form-select select2" multiple>
                                    @foreach($filterorderStatuses as $status)
                                        <option value="{{ $status->id }}" {{ collect(request('status_filter'))->contains($status->id) ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Payment Status</label>
                                <select name="payment_status_filter[]" class="form-select select2" multiple>
                                    <option value="0" {{ collect(request('payment_status_filter'))->contains('0') ? 'selected' : '' }}>unpaid</option>
                                    <option value="1" {{ collect(request('payment_status_filter'))->contains('1') ? 'selected' : '' }}>partial paid</option>
                                    <option value="2" {{ collect(request('payment_status_filter'))->contains('2') ? 'selected' : '' }}>paid</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="mdi mdi-filter"></i> Filter
                                    </button>
                                    <a href="{{ route('admin.reportOrderReport') }}" class="btn btn-sm btn-secondary">
                                        <i class="mdi mdi-refresh"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Export Button -->
                    <div class="mb-3">
                        <a href="{{ route('admin.reportOrderReportExport', request()->all()) }}" class="btn btn-success btn-sm">
                            <i class="mdi mdi-file-excel"></i> Export to Excel
                        </a>
                    </div>

                    <!-- Report Table -->
                    <div class="table-responsive">
                        <table id="orderReportTable" class="table table-bordered table-striped table-hover table-sm">
                            <thead class="">
                                <tr>
                                    <th>SL</th>
                                    <th>invoice</th>
                                    <th>Date</th>
                                    <th>Vendor</th>
                                    <th>Product</th>
                                    <th>Order By</th>
                                    <th>Quantity</th>
                                    <th>Purchase Price</th>
                                    <th>Total Purchase</th>
                                    <th>Sell Price</th>
                                    <th>Total Sell</th>
                                    <th>Discount</th>
                                    <th>Profit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orderItems as $key => $item)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            <a href="{{ route('invoices.preview', $item->order_id) }}" target="_blank">
                                               {{ $item?->order?->invoice_id  }}
                                            </a>

                                            <br>
                                            <span class="mt-1 badge {{ $item?->order?->payment_status_badge_class }} status-badge">
                                                    {{ $item?->order?->payment_status_text }}
                                            </span>

                                        </td>
                                        <td>{{ $item?->order?->created_at->format('d M Y') }}</td>
                                        <td>{{ $item?->order?->vendor->shop_name ?? 'N/A' }}</td>
                                        <td>{{ $item?->product?->name }}</td>
                                        <td>{{ $item?->order?->placeBy->name ?? 'N/A' }}</td>
                                        <td class="">
                                           Total: {{ number_format($item->quantity, 0) }}

                                            <span>Dam: {{ number_format($item->damage_quantity, 0) }}</span><br>
                                            <span>Ret: {{ number_format($item->return_quantity, 0) }}</span><br>
                                            <span>Lost: {{ number_format($item->lost_quantity, 0) }}</span><br>

                                            <span><b>Actual sell: {{ number_format($item->quantity - $item->return_quantity, 0) }}</b></span>

                                        </td>

                                        {{-- <td class="text-end">৳{{ number_format($avgPurchasePrice, 2) }}</td> --}}
                                        <td class="text-end">৳{{ number_format($item->purchase_price, 2) }}</td>
                                        <td class="text-end">
                                            ৳{{ number_format($item->total_purchase, 2) }}
                                        </td>
                                         <td class="text-end">৳{{ number_format($item->sell_price, 2) }}</td>
                                        <td class="text-end">
                                            ৳{{ number_format($item->total_sell, 2) }}
                                        </td>
                                        {{-- <td class="text-end">৳{{ number_format($item->total_price, 2) }}</td> --}}
                                        <td class="text-end">৳{{ number_format($item->discount_price, 2) }}</td>
                                        <td class="text-end">৳{{ number_format($item->item_total_profit, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center">No orders found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-secondary">

                                <tr>
                                    <th colspan="6" class="text-end">Current Page Total:</th>
                                    <th class="text-end">{{ number_format($currentQuantityPage, 0) }}</th>

                                    <th colspan="1"></th>
                                    <th class="text-end">৳{{ number_format($currentPurchasePage, 2) }}</th>
                                    <th colspan="1"></th>
                                    <th class="text-end">৳{{ number_format($currentSellPricePage, 2) }}</th>

                                    <th class="text-end">৳{{ number_format($currentDiscountPage, 2) }}</th>
                                    <th class="text-end">৳{{ number_format($currentProfitPage, 2) }}</th>
                                </tr>

                                <tr>
                                    <th colspan="6" class="text-end">Total:</th>
                                    <th class="text-end">{{ number_format($totalQuantity, 0) }}</th>

                                    <th colspan="1"></th>
                                    <th class="text-end">৳{{ number_format($totalPurchase, 2) }}</th>
                                    <th colspan="1"></th>
                                    <th class="text-end">৳{{ number_format($totalSellPrice, 2) }}</th>

                                    <th class="text-end">৳{{ number_format($totalDiscount, 2) }}</th>
                                    <th class="text-end">৳{{ number_format($totalProfit, 2) }}</th>
                                </tr>

                            </tfoot>
                        </table>
                    </div>
                    @if($orderItems->hasPages())
                        <div class="mt-3">
                            {{ $orderItems->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    </div>
</div>

@endsection

@push('custome-css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@push('custome-js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/table2excel@1.0.4/dist/table2excel.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Select',
            allowClear: true
        });
    });


</script>
@endpush
