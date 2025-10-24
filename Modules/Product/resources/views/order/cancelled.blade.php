@extends('layouts.app')

@section('title', 'Cancelled Orders')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Cancelled Orders</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('orders.index') }}">Orders</a></li>
                        <li class="breadcrumb-item active">Cancelled</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancelled Orders List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title mb-0">Cancelled Orders</h4>
                            <p class="text-muted mb-0">List of all cancelled orders with stock restoration history</p>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('orders.index') }}" class="btn btn-primary">
                                <i class="mdi mdi-arrow-left me-1"></i>Back to Active Orders
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Search and Filter Form -->
                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('orders.cancelled') }}" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Search Invoice</label>
                            <input type="text" name="invoice_search" class="form-control"
                                   placeholder="Search by invoice ID..."
                                   value="{{ request('invoice_search') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Vendor</label>
                            <select name="vendor_filter" class="form-select">
                                <option value="">All Vendors</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}"
                                            {{ request('vendor_filter') == $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">From Date</label>
                            <input type="date" name="date_from" class="form-control"
                                   value="{{ request('date_from') }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">To Date</label>
                            <input type="date" name="date_to" class="form-control"
                                   value="{{ request('date_to') }}">
                        </div>

                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-magnify"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-body">
                    @if($orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice ID</th>
                                        <th>Customer/Vendor</th>
                                        <th>Items</th>
                                        <th>Total Amount</th>
                                        <th>Discount</th>
                                        <th>Net Amount</th>
                                        <th>Order Date</th>
                                        <th>Cancelled Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>
                                                <strong class="text-danger">{{ $order->invoice_id }}</strong>
                                                <br>
                                                <span class="badge bg-danger">Cancelled</span>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $order->vendor->name ?? 'N/A' }}</strong>
                                                    <br>
                                                    <small class="text-muted">By: {{ $order->admin->name ?? 'N/A' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $order->total_quantity }} items</span>
                                                <br>
                                                <small class="text-muted">{{ $order->orderItems->count() }} products</small>
                                            </td>
                                            <td>
                                                <strong>৳{{ number_format($order->total_amount, 2) }}</strong>
                                            </td>
                                            <td>
                                                @if($order->total_discount_amount > 0)
                                                    <span class="text-danger">৳{{ number_format($order->total_discount_amount, 2) }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong class="text-muted">৳{{ number_format($order->net_amount, 2) }}</strong>
                                                <br>
                                                <small class="text-success">Stock Restored</small>
                                            </td>
                                            <td>
                                                <div>
                                                    {{ $order->created_at->format('d M Y') }}
                                                    <br>
                                                    <small class="text-muted">{{ $order->created_at->format('h:i A') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    {{ $order->updated_at->format('d M Y') }}
                                                    <br>
                                                    <small class="text-muted">{{ $order->updated_at->format('h:i A') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-light border dropdown-toggle"
                                                            type="button" data-bs-toggle="dropdown">
                                                        Actions
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('orders.show', $order) }}">
                                                                <i class="mdi mdi-eye me-2"></i>View Details
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item" onclick="showCancellationDetails('{{ $order->invoice_id }}', '{{ $order->updated_at->format('d M Y, h:i A') }}', {{ $order->orderItems->count() }}, {{ $order->total_quantity }})">
                                                                <i class="mdi mdi-information me-2"></i>Cancellation Info
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="row mt-3">
                            <div class="col-sm-6">
                                <div class="text-muted">
                                    Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }}
                                    of {{ $orders->total() }} cancelled orders
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex justify-content-end">
                                    {{ $orders->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="avatar-lg mx-auto mb-4">
                                <div class="avatar-title bg-light text-secondary rounded-circle">
                                    <i class="mdi mdi-cancel display-4"></i>
                                </div>
                            </div>
                            <h5>No Cancelled Orders Found</h5>
                            <p class="text-muted">No cancelled orders match your search criteria.</p>
                            <a href="{{ route('orders.index') }}" class="btn btn-primary">
                                <i class="mdi mdi-arrow-left me-1"></i>Back to Active Orders
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancellation Details Modal -->
<div class="modal fade" id="cancellationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancellation Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <tr>
                            <td class="fw-bold">Order ID:</td>
                            <td id="modalInvoiceId"></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Cancelled Date:</td>
                            <td id="modalCancelDate"></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Products Restored:</td>
                            <td id="modalProductCount"></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Quantity Restored:</td>
                            <td id="modalQuantityCount"></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Stock Status:</td>
                            <td><span class="badge bg-success">Fully Restored</span></td>
                        </tr>
                    </table>
                </div>
                <div class="alert alert-success">
                    <i class="mdi mdi-check-circle me-2"></i>
                    All stock quantities have been automatically restored to inventory when this order was cancelled.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('custome-js')
<script>
function showCancellationDetails(invoiceId, cancelDate, productCount, quantityCount) {
    document.getElementById('modalInvoiceId').textContent = invoiceId;
    document.getElementById('modalCancelDate').textContent = cancelDate;
    document.getElementById('modalProductCount').textContent = productCount + ' products';
    document.getElementById('modalQuantityCount').textContent = quantityCount + ' units';

    var modal = new bootstrap.Modal(document.getElementById('cancellationModal'));
    modal.show();
}
</script>
@endpush
