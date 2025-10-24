@extends('layouts.app')

@push('custome-css')
<style>
    .batch-badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }
    .stock-summary {
        font-size: 0.85rem;
    }
    .stock-status-badge {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }
    .remaining-quantity {
        font-weight: bold;
    }
    .search-card {
        background-color: #f8f9fa;
        border-left: 4px solid #007bff;
    }
</style>
@endpush

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Stock Management</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Stock</li>
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
                    <h1 class="mt-3">Stock List</h1>
                    <div class="text-end">
                        <a href="{{ route('admin.stockCreate') }}" class="btn btn-primary">
                            <span class="mdi mdi-plus"></span> Add New Stock
                        </a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Search and Filter Section -->
                <div class="card search-card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="mdi mdi-magnify"></i> Search & Filter Stocks
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.stockIndex') }}" class="row g-3">
                            <div class="col-md-3">
                                <label for="product_search" class="form-label">Product Name</label>
                                <input type="text"
                                       class="form-control"
                                       id="product_search"
                                       name="product_search"
                                       value="{{ request('product_search') }}"
                                       placeholder="Search by product name">
                            </div>
                            <div class="col-md-3">
                                <label for="batch_search" class="form-label">Batch ID</label>
                                <input type="text"
                                       class="form-control"
                                       id="batch_search"
                                       name="batch_search"
                                       value="{{ request('batch_search') }}"
                                       placeholder="Search by batch ID">
                            </div>
                            <div class="col-md-2">
                                <label for="date_from" class="form-label">Date From</label>
                                <input type="date"
                                       class="form-control"
                                       id="date_from"
                                       name="date_from"
                                       value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="date_to" class="form-label">Date To</label>
                                <input type="date"
                                       class="form-control"
                                       id="date_to"
                                       name="date_to"
                                       value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="">All Status</option>
                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-info">
                                    <i class="mdi mdi-magnify"></i> Search
                                </button>
                                <a href="{{ route('admin.stockIndex') }}" class="btn btn-secondary">
                                    <i class="mdi mdi-refresh"></i> Reset
                                </a>
                                @if(request()->hasAny(['product_search', 'batch_search', 'date_from', 'date_to', 'status']))
                                    <span class="badge bg-primary ms-2">
                                        Filtered Results: {{ $stocks->total() }} items
                                    </span>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card card-primary card-outline mb-4">
                    <div class="card-header">
                        <div class="card-title">All Stocks</div>
                        <div class="card-tools">
                            <span class="badge bg-info">Total: {{ $stocks->total() }} stocks</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Product Info</th>
                                        <th>Batch ID</th>
                                        <th>Purchase Price</th>
                                        <th>Quantity</th>
                                        <th>Remaining</th>
                                        <th>Sell Price</th>
                                        <th>Total Value</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($stocks as $stock)
                                        <tr>
                                            <td>{{ $loop->iteration + ($stocks->currentPage() - 1) * $stocks->perPage() }}</td>
                                            <td>
                                                <div class="stock-summary">
                                                    <strong>{{ $stock->product->name }}</strong><br>

                                                    <div>
                                                        @if($stock->product->media && $stock->product->media->count())
                                                            <img src="{{ $stock->product->product_image_thumb_url }}"
                                                                 alt="Product Image"
                                                                 class="img-thumbnail"
                                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                                        @else

                                                        @endif
                                                    </div>
                                                    <small class="text-muted">
                                                        <i class="mdi mdi-palette"></i> {{ $stock->product->color->name ?? 'N/A' }} |
                                                        <i class="mdi mdi-scale-balance"></i> {{ $stock->product->unit->name ?? 'N/A' }}
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info batch-badge">{{ $stock->batch_id }}</span>
                                            </td>
                                            <td>
                                                <strong>৳{{ number_format($stock->purchase_price, 2) }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ number_format($stock->quantity) }}</span>
                                            </td>
                                            <td>
                                                <span class="badge remaining-quantity {{ $stock->remaining_quantity > 0 ? 'bg-success' : ($stock->remaining_quantity == 0 ? 'bg-warning' : 'bg-danger') }}">
                                                    {{ number_format($stock->remaining_quantity) }}
                                                </span>
                                                @if($stock->remaining_quantity <= 0)
                                                    <br><small class="text-danger">Out of Stock</small>
                                                @elseif($stock->remaining_quantity <= ($stock->quantity * 0.2))
                                                    <br><small class="text-warning">Low Stock</small>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>৳{{ number_format($stock->sell_price, 2) }}</strong>
                                                @php
                                                    $profitMargin = $stock->purchase_price > 0 ? (($stock->sell_price - $stock->purchase_price) / $stock->purchase_price) * 100 : 0;
                                                @endphp
                                                <br><small class="text-{{ $profitMargin >= 0 ? 'success' : 'danger' }}">
                                                    {{ number_format($profitMargin, 1) }}% margin
                                                </small>
                                            </td>
                                            <td>
                                                <strong>৳{{ number_format($stock->total_price, 2) }}</strong>
                                                <br><small class="text-muted">
                                                    Current: ৳{{ number_format($stock->remaining_quantity * $stock->sell_price, 2) }}
                                                </small>
                                            </td>
                                            <td>
                                                @if($stock->status)
                                                    <span class="badge bg-success stock-status-badge">
                                                        <i class="mdi mdi-check-circle"></i> Active
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger stock-status-badge">
                                                        <i class="mdi mdi-close-circle"></i> Inactive
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $stock->created_at->format('M d, Y') }}</span>
                                                <br><small class="text-muted">{{ $stock->created_at->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('admin.stockShow', $stock) }}"
                                                       class="btn btn-sm btn-outline-info"
                                                       title="View Details">
                                                        <span class="mdi mdi-eye"></span>
                                                    </a>
                                                    @if($stock->sold_quantity == 0 && $stock->froze_quantity == 0)
                                                        <a href="{{ route('admin.stockEdit', $stock) }}"
                                                           class="btn btn-sm btn-outline-warning"
                                                           title="Edit Stock">
                                                            <span class="mdi mdi-pencil"></span>
                                                        </a>
                                                    @else
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-secondary"
                                                                title="Cannot edit (Sold: {{ $stock->sold_quantity }}, Frozen: {{ $stock->froze_quantity }})"
                                                                disabled>
                                                            <span class="mdi mdi-lock"></span>
                                                        </button>
                                                    @endif
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-danger delete-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteModal"
                                                            data-url="{{ route('admin.stockDestroy', $stock) }}"
                                                            title="Delete Stock">
                                                        <span class="mdi mdi-delete"></span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center py-4">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="mdi mdi-package-variant-closed mdi-48px text-muted mb-3"></i>
                                                    <h5 class="text-muted">No stocks found</h5>
                                                    <p class="text-muted">
                                                        @if(request()->hasAny(['product_search', 'batch_search', 'date_from', 'date_to', 'status']))
                                                            No stocks match your search criteria. Try adjusting your filters.
                                                        @else
                                                            Get started by adding your first stock entry.
                                                        @endif
                                                    </p>
                                                    @if(!request()->hasAny(['product_search', 'batch_search', 'date_from', 'date_to', 'status']))
                                                        <a href="{{ route('admin.stockCreate') }}" class="btn btn-primary">
                                                            <i class="mdi mdi-plus"></i> Add First Stock
                                                        </a>
                                                    @else
                                                        <a href="{{ route('admin.stockIndex') }}" class="btn btn-secondary">
                                                            <i class="mdi mdi-refresh"></i> Clear Filters
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                    @include('components.delete')
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($stocks->hasPages())
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted">
                                    Showing {{ $stocks->firstItem() }} to {{ $stocks->lastItem() }} of {{ $stocks->total() }} results
                                </div>
                                <div>
                                    {{ $stocks->withQueryString()->links() }}

                                </div>
                            </div>
                        </div>
                    @endif
                </div>


            </div>
        </div>
    </div>
</div>
@endsection

@push('custome-js')

@endpush
