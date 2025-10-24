@extends('layouts.app')

@section('title', 'Orders')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Orders</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Orders</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <h5 class="font-size-20 mb-1">{{ number_format($totalOrders) }}</h5>
                            <p class="text-muted mb-0">Total Orders</p>
                        </div>
                        <div class="avatar-sm rounded-circle bg-primary align-self-center mini-stat-icon">
                            <span class="avatar-title rounded-circle bg-primary">
                                <i class="mdi mdi-cart font-size-20"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <h5 class="font-size-20 mb-1">৳{{ number_format($totalAmount, 2) }}</h5>
                            <p class="text-muted mb-0">Total Amount</p>
                        </div>
                        <div class="avatar-sm rounded-circle bg-success align-self-center mini-stat-icon">
                            <span class="avatar-title rounded-circle bg-success">
                                <i class="mdi mdi-currency-bdt font-size-20"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <h5 class="font-size-20 mb-1">{{ number_format($pendingOrders) }}</h5>
                            <p class="text-muted mb-0">Pending Orders</p>
                        </div>
                        <div class="avatar-sm rounded-circle bg-warning align-self-center mini-stat-icon">
                            <span class="avatar-title rounded-circle bg-warning">
                                <i class="mdi mdi-clock-outline font-size-20"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <h5 class="font-size-20 mb-1">{{ number_format($completedOrders) }}</h5>
                            <p class="text-muted mb-0">Completed Orders</p>
                        </div>
                        <div class="avatar-sm rounded-circle bg-info align-self-center mini-stat-icon">
                            <span class="avatar-title rounded-circle bg-info">
                                <i class="mdi mdi-check-circle font-size-20"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title mb-0">Orders List</h4>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex gap-2">
                                <a href="{{ route('orders.cancelled') }}" class="btn btn-outline-danger">
                                    <i class="mdi mdi-cancel me-1"></i>Cancelled Orders
                                </a>
                                <a href="{{ route('orders.create') }}" class="btn btn-primary">
                                    <i class="mdi mdi-plus me-1"></i>Create Order
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search and Filter Form -->
                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('orders.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Search Invoice</label>
                            <input type="text" name="invoice_search" class="form-control"
                                   placeholder="Search by invoice ID..."
                                   value="{{ request('invoice_search') }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status_filter" class="form-select">
                                <option value="">All Status</option>
                                @foreach($orderStatuses as $status)
                                    <option value="{{ $status->id }}"
                                            {{ request('status_filter') == $status->id ? 'selected' : '' }}>
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
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
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>
                                                <strong class="text-primary">{{ $order->invoice_id }}</strong>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $order->vendor->name ?? 'N/A' }}</strong>
                                                    <br>
                                                    <small class="text-muted">By: {{ $order->admin->name ?? 'N/A' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $order->total_quantity }} items</span>
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
                                                <strong class="text-success">৳{{ number_format($order->net_amount, 2) }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge {{ $order->status_badge_class }}">
                                                    {{ $order->orderStatus->name ?? 'Unknown' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    {{ $order->created_at->format('d M Y') }}
                                                    <br>
                                                    <small class="text-muted">{{ $order->created_at->format('h:i A') }}</small>
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
                                                        @if($order->canBeCancelled())
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('orders.edit', $order) }}">
                                                                    <i class="mdi mdi-pencil me-2"></i>Edit
                                                                </a>
                                                            </li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <form action="{{ route('orders.cancel', $order) }}"
                                                                      method="POST" class="d-inline">
                                                                    @csrf
                                                                    <button type="submit" class="dropdown-item text-danger"
                                                                            onclick="return confirm('Are you sure you want to cancel this order? Stock will be restored.')">
                                                                        <i class="mdi mdi-cancel me-2"></i>Cancel Order
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        @endif
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
                                    of {{ $orders->total() }} orders
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
                                <div class="avatar-title bg-light text-primary rounded-circle">
                                    <i class="mdi mdi-cart-outline display-4"></i>
                                </div>
                            </div>
                            <h5>No Orders Found</h5>
                            <p class="text-muted">No orders match your search criteria.</p>
                            <a href="{{ route('orders.create') }}" class="btn btn-primary">
                                <i class="mdi mdi-plus me-1"></i>Create First Order
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
