@extends('layouts.app')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Payment Collection</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Payment Collection</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-lg-4 col-md-6">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4>৳{{ number_format($totalCollected, 2) }}</h4>
                                <p class="mb-0">Total Collected</p>
                            </div>
                            <div class="align-self-center">
                                <i class="mdi mdi-cash-multiple fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4>৳{{ number_format($totalPending, 2) }}</h4>
                                <p class="mb-0">Total Pending</p>
                            </div>
                            <div class="align-self-center">
                                <i class="mdi mdi-clock-outline fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4>৳{{ number_format($pendingCollect, 2) }}</h4>
                                <p class="mb-0">Need to Collect</p>
                            </div>
                            <div class="align-self-center">
                                <i class="mdi mdi-currency-usd fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Actions -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Payment Collections</h5>
                    <a href="{{ route('payment-collections.create') }}" class="btn btn-success">
                        <i class="mdi mdi-plus"></i> Collect Payment
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <form method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-2">
                            <select name="vendor_filter" class="form-select">
                                <option value="">All Vendors</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ request('vendor_filter') == $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->shop_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="payment_method_filter" class="form-select">
                                <option value="">All Payment Methods</option>
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method->id }}" {{ request('payment_method_filter') == $method->id ? 'selected' : '' }}>
                                        {{ $method->account_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="type_filter" class="form-select">
                                <option value="">All Types</option>
                                <option value="1" {{ request('type_filter') == '1' ? 'selected' : '' }}>Debit</option>
                                <option value="2" {{ request('type_filter') == '2' ? 'selected' : '' }}>Credit</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="From Date">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="To Date">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="mdi mdi-filter"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Collections Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Vendor</th>
                                <th>Order</th>
                                <th>Payment Method</th>
                                <th>Amount</th>
                                <th>Note</th>
                                <th>Type</th>
                                <th>Created By</th>
                                <th>Deposited By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($collections as $collection)
                                <tr>
                                    <td>{{ $collection->collection_date ? $collection->collection_date->format('M d, Y') : '-' }}</td>
                                    <td>
                                        <strong>{{ $collection->vendor->shop_name ?? 'N/A' }}</strong><br>
                                        <small>{{ $collection->vendor->mobile ?? '' }}</small>
                                    </td>
                                    <td>
                                        @if($collection->order)
                                            <span class="badge bg-info">{{ $collection->order->invoice_id }}</span>
                                        @else
                                            <span class="text-muted">General Collection</span>
                                        @endif
                                    </td>
                                    <td>{{ $collection->paymentMethod->account_name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge {{ $collection->type_badge_class }}">
                                            ৳{{ number_format($collection->amount, 2) }}
                                        </span>
                                    </td>
                                    <td>{{ $collection->note ?? '-' }}</td>
                                    <td>
                                        <span class="badge {{ $collection->type_badge_class }}">
                                            {{ $collection->type_text }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($collection->createdBy)
                                            <strong>{{ $collection->createdBy->name }}</strong><br>
                                            <small class="text-muted">{{ $collection->createdBy->email }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($collection->depositeBy)
                                            <strong>{{ $collection->depositeBy->name }}</strong><br>
                                            <small class="text-muted">{{ $collection->depositeBy->email }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('payment-collections.show', $collection) }}" class="btn btn-sm btn-outline-info" title="View Details">
                                                <span class="mdi mdi-eye"></span>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-bs-toggle="modal" data-bs-target="#deleteModal" data-url="{{ route('payment-collections.destroy', $collection) }}" title="Delete">
                                                <span class="mdi mdi-delete"></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center">No payment collections found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                {{ $collections->links() }}
            </div>
        </div>
        @include('components.delete')
    </div>
</div>
@endsection