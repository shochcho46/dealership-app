@extends('layouts.app')

@push('custome-css')
<style>
    .badge-paid { background-color: #28a745; }
    .badge-partial { background-color: #ffc107; }
    .badge-unpaid { background-color: #dc3545; }
</style>
@endpush

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Company Order Management</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Company Orders</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="mt-3">Order List</h1>
                    <div class="text-end">
                        <a href="{{ route('admin.companyOrderCreate') }}" class="btn btn-primary">
                            <span class="mdi mdi-plus"></span> Add New Order
                        </a>
                    </div>
                </div>

                <!-- Filter Form -->
                <div class="card mb-3">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.companyOrderIndex') }}">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="company_id" class="form-label">Company</label>
                                    <select name="company_id" id="company_id" class="form-control">
                                        <option value="">All Companies</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                                {{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="payment_status" class="form-label">Payment Status</label>
                                    <select name="payment_status" id="payment_status" class="form-control">
                                        <option value="">All Payment</option>
                                        <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                        <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="status" class="form-label">Order Status</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="">All Status</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <span class="mdi mdi-filter"></span> Filter
                                        </button>
                                        <a href="{{ route('admin.companyOrderIndex') }}" class="btn btn-secondary">
                                            <span class="mdi mdi-refresh"></span> Reset
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Order Number</th>
                                    <th>Company</th>
                                    <th>Total Amount</th>
                                    <th>Paid Amount</th>
                                    <th>Payment Status</th>
                                    <th>Order Status</th>
                                    <th>Order Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                <tr>
                                    <td>{{ $order->order_number }}</td>
                                    <td>{{ $order->company->name ?? 'N/A' }}</td>
                                    <td>৳{{ number_format($order->total_amount, 2) }}</td>
                                    <td>৳{{ number_format($order->paid_amount, 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $order->payment_status }}">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $order->status === 'received' ? 'success' : 'warning' }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $order->created_at->format('d M Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.companyOrderShow', $order) }}"
                                           class="btn btn-sm btn-info">
                                            <span class="mdi mdi-eye"></span>
                                        </a>
                                        <a href="{{ route('admin.companyOrderEdit', $order) }}"
                                           class="btn btn-sm btn-primary">
                                            <span class="mdi mdi-pencil"></span>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger delete-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteModal"
                                                data-url="{{ route('admin.companyOrderDestroy', $order) }}">
                                            <span class="mdi mdi-delete"></span>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No orders found</td>
                                </tr>
                                @endforelse
                                @include('components.delete')
                            </tbody>
                        </table>

                        <div class="d-flex justify-content-center mt-3">
                            {{ $orders->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
