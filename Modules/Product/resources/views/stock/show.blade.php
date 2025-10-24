@extends('layouts.app')

@push('custome-css')
<style>
    .info-card {
        background-color: #f8f9fa;
        border-left: 4px solid #007bff;
    }
    .stat-card {
        text-align: center;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
    }
    .stat-value {
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }
    .stat-label {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>
@endpush

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Stock Details</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.stockIndex') }}">Stock</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Details</li>
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
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <span class="mdi mdi-package-variant"></span>
                            Stock Information
                        </h5>
                        <div>
                            <a href="{{ route('admin.stockEdit', $stock) }}" class="btn btn-warning">
                                <span class="mdi mdi-pencil"></span> Edit
                            </a>
                            <a href="{{ route('admin.stockIndex') }}" class="btn btn-secondary">
                                <span class="mdi mdi-arrow-left"></span> Back to List
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <div class="card info-card">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="mdi mdi-information"></i> Basic Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong class="text-dark">Stock ID:</strong></td>
                                                <td class="text-dark">{{ $stock->id }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong class="text-dark">Batch ID:</strong></td>
                                                <td><span class="badge bg-info">{{ $stock->batch_id }}</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong class="text-dark">Product:</strong></td>
                                                <td>
                                                    <strong class="text-dark">{{ $stock->product->name }}</strong><br>
                                                    <small class="text-muted">
                                                        Color: {{ $stock->product->color->name ?? 'N/A' }}<br>
                                                        Unit: {{ $stock->product->unit->name ?? 'N/A' }}
                                                    </small>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong class="text-dark">Status:</strong></td>
                                                <td>
                                                    @if($stock->status)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-danger">Inactive</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong class="text-dark">Created Date:</strong></td>
                                                <td class="text-dark">{{ $stock->created_at->format('M d, Y h:i A') }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong class="text-dark">Last Updated:</strong></td>
                                                <td class="text-dark">{{ $stock->updated_at->format('M d, Y h:i A') }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Financial Information -->
                            <div class="col-md-6">
                                <div class="card info-card">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="mdi mdi-currency-usd"></i> Financial Details</h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong class="text-dark">Purchase Price:</strong></td>
                                                <td class="text-end text-dark">${{ number_format($stock->purchase_price, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong class="text-dark">Sell Price:</strong></td>
                                                <td class="text-end text-dark">${{ number_format($stock->sell_price, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong class="text-dark">Total Investment:</strong></td>
                                                <td class="text-end"><strong class="text-dark">${{ number_format($stock->total_price, 2) }}</strong></td>
                                            </tr>
                                            <tr class="border-top">
                                                <td><strong class="text-dark">Profit per Unit:</strong></td>
                                                <td class="text-end">
                                                    @php
                                                        $profitPerUnit = $stock->sell_price - $stock->purchase_price;
                                                    @endphp
                                                    <span class="{{ $profitPerUnit >= 0 ? 'text-success' : 'text-danger' }}">
                                                        ${{ number_format($profitPerUnit, 2) }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong class="text-dark">Profit Margin:</strong></td>
                                                <td class="text-end">
                                                    @php
                                                        $profitMargin = $stock->purchase_price > 0 ? (($stock->sell_price - $stock->purchase_price) / $stock->purchase_price) * 100 : 0;
                                                    @endphp
                                                    <span class="{{ $profitMargin >= 0 ? 'text-success' : 'text-danger' }}">
                                                        {{ number_format($profitMargin, 2) }}%
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <!-- Quantity Information -->
                            <div class="col-md-8">
                                <div class="card info-card">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0"><i class="mdi mdi-package-variant"></i> Quantity Tracking</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <table class="table table-borderless">
                                                    <tr>
                                                        <td><strong class="text-dark">Initial Quantity:</strong></td>
                                                        <td class="text-end text-dark">{{ number_format($stock->quantity) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong class="text-dark">Sold Quantity:</strong></td>
                                                        <td class="text-end text-success">{{ number_format($stock->sold_quantity) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong class="text-dark">Damage Quantity:</strong></td>
                                                        <td class="text-end text-warning">{{ number_format($stock->damage_quantity) }}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <table class="table table-borderless">
                                                    <tr>
                                                        <td><strong class="text-dark">Stolen Quantity:</strong></td>
                                                        <td class="text-end text-danger">{{ number_format($stock->stolen_quantity) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong class="text-dark">Transfer Quantity:</strong></td>
                                                        <td class="text-end text-info">{{ number_format($stock->transfer_quantity) }}</td>
                                                    </tr>
                                                    <tr class="border-top">
                                                        <td><strong class="text-dark">Remaining Quantity:</strong></td>
                                                        <td class="text-end">
                                                            <span class="badge {{ $stock->remaining_quantity > 0 ? 'bg-success' : ($stock->remaining_quantity == 0 ? 'bg-warning' : 'bg-danger') }} fs-6">
                                                                {{ number_format($stock->remaining_quantity) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Stats -->
                            <div class="col-md-4">
                                <div class="card info-card">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0"><i class="mdi mdi-chart-line"></i> Quick Stats</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="stat-card bg-light">
                                            <div class="stat-value text-primary">${{ number_format($stock->remaining_quantity * $stock->sell_price, 2) }}</div>
                                            <div class="stat-label text-muted">Remaining Value</div>
                                        </div>
                                        <div class="stat-card bg-light">
                                            <div class="stat-value text-success">
                                                {{ $stock->quantity > 0 ? number_format(($stock->sold_quantity / $stock->quantity) * 100, 1) : 0 }}%
                                            </div>
                                            <div class="stat-label text-muted">Sold Percentage</div>
                                        </div>
                                        <div class="stat-card bg-light">
                                            @if($stock->remaining_quantity > ($stock->quantity * 0.5))
                                                <span class="badge bg-success fs-6">Well Stocked</span>
                                            @elseif($stock->remaining_quantity > 0)
                                                <span class="badge bg-warning fs-6">Low Stock</span>
                                            @else
                                                <span class="badge bg-danger fs-6">Out of Stock</span>
                                            @endif
                                            <div class="stat-label text-muted mt-2">Stock Status</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($stock->product->getMedia('product_image')->count() > 0 || $stock->product->getMedia('product_other_image')->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card info-card">
                                    <div class="card-header bg-secondary text-white">
                                        <h6 class="mb-0"><i class="mdi mdi-image"></i> Product Images</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach($stock->product->getMedia('product_image') as $media)
                                            <div class="col-md-3 mb-3">
                                                <div class="card">
                                                    <img src="{{ $media->getUrl() }}" class="card-img-top" alt="Product Image" style="height: 200px; object-fit: cover;">
                                                    <div class="card-body p-2">
                                                        <small class="text-muted">Main Image</small>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach

                                            @foreach($stock->product->getMedia('product_other_image') as $media)
                                            <div class="col-md-3 mb-3">
                                                <div class="card">
                                                    <img src="{{ $media->getUrl() }}" class="card-img-top" alt="Product Image" style="height: 200px; object-fit: cover;">
                                                    <div class="card-body p-2">
                                                        <small class="text-muted">Additional Image</small>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('admin.stockEdit', $stock) }}" class="btn btn-warning">
                                    <i class="mdi mdi-pencil"></i> Edit Stock
                                </a>
                                <button type="button" class="btn btn-outline-danger delete-btn" data-bs-toggle="modal" data-bs-target="#deleteModal" data-url="{{ route('admin.stockDestroy', $stock) }}">
                                    <i class="mdi mdi-delete"></i> Delete Stock
                                </button>
                            </div>
                            <div>
                                <a href="{{ route('admin.stockIndex') }}" class="btn btn-secondary">
                                    <i class="mdi mdi-arrow-left"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('components.delete')

@endsection

@push('custome-js')

@endpush

@section('title', 'Stock Details')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Stock Details</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.stockIndex') }}">Stock</a></li>
                    <li class="breadcrumb-item active">Details</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="mdi mdi-package-variant"></i>
                            Stock Information
                        </h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.stockEdit', $stock) }}" class="btn btn-warning">
                                <i class="mdi mdi-pencil"></i> Edit
                            </a>
                            <a href="{{ route('admin.stockIndex') }}" class="btn btn-secondary">
                                <i class="mdi mdi-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0"><i class="mdi mdi-information"></i> Basic Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Stock ID:</strong></td>
                                                <td>{{ $stock->id }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Batch ID:</strong></td>
                                                <td><span class="badge badge-info">{{ $stock->batch_id }}</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Product:</strong></td>
                                                <td>
                                                    <strong>{{ $stock->product->name }}</strong><br>
                                                    <small class="text-muted">
                                                        Color: {{ $stock->product->color->name ?? 'N/A' }}<br>
                                                        Unit: {{ $stock->product->unit->name ?? 'N/A' }}
                                                    </small>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Status:</strong></td>
                                                <td>
                                                    @if($stock->status)
                                                        <span class="badge badge-success">Active</span>
                                                    @else
                                                        <span class="badge badge-danger">Inactive</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Created Date:</strong></td>
                                                <td>{{ $stock->created_at->format('M d, Y h:i A') }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Last Updated:</strong></td>
                                                <td>{{ $stock->updated_at->format('M d, Y h:i A') }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Financial Information -->
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0"><i class="mdi mdi-currency-usd"></i> Financial Details</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Purchase Price:</strong></td>
                                                <td class="text-right">${{ number_format($stock->purchase_price, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Sell Price:</strong></td>
                                                <td class="text-right">${{ number_format($stock->sell_price, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Total Investment:</strong></td>
                                                <td class="text-right"><strong>${{ number_format($stock->total_price, 2) }}</strong></td>
                                            </tr>
                                            <tr class="border-top">
                                                <td><strong>Profit per Unit:</strong></td>
                                                <td class="text-right">
                                                    @php
                                                        $profitPerUnit = $stock->sell_price - $stock->purchase_price;
                                                    @endphp
                                                    <span class="{{ $profitPerUnit >= 0 ? 'text-success' : 'text-danger' }}">
                                                        ${{ number_format($profitPerUnit, 2) }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Profit Margin:</strong></td>
                                                <td class="text-right">
                                                    @php
                                                        $profitMargin = $stock->purchase_price > 0 ? (($stock->sell_price - $stock->purchase_price) / $stock->purchase_price) * 100 : 0;
                                                    @endphp
                                                    <span class="{{ $profitMargin >= 0 ? 'text-success' : 'text-danger' }}">
                                                        {{ number_format($profitMargin, 2) }}%
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <!-- Quantity Information -->
                            <div class="col-md-8">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0"><i class="mdi mdi-package-variant"></i> Quantity Tracking</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <table class="table table-borderless">
                                                    <tr>
                                                        <td><strong>Initial Quantity:</strong></td>
                                                        <td class="text-right">{{ number_format($stock->quantity) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Sold Quantity:</strong></td>
                                                        <td class="text-right text-success">{{ number_format($stock->sold_quantity) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Damage Quantity:</strong></td>
                                                        <td class="text-right text-warning">{{ number_format($stock->damage_quantity) }}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <table class="table table-borderless">
                                                    <tr>
                                                        <td><strong>Stolen Quantity:</strong></td>
                                                        <td class="text-right text-danger">{{ number_format($stock->stolen_quantity) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Transfer Quantity:</strong></td>
                                                        <td class="text-right text-info">{{ number_format($stock->transfer_quantity) }}</td>
                                                    </tr>
                                                    <tr class="border-top">
                                                        <td><strong>Remaining Quantity:</strong></td>
                                                        <td class="text-right">
                                                            <span class="badge {{ $stock->remaining_quantity > 0 ? 'badge-success' : ($stock->remaining_quantity == 0 ? 'badge-warning' : 'badge-danger') }} badge-lg">
                                                                {{ number_format($stock->remaining_quantity) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Stats -->
                            <div class="col-md-4">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <h5 class="mb-0"><i class="mdi mdi-chart-line"></i> Quick Stats</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center">
                                            <div class="col-12 mb-3">
                                                <h6 class="text-muted">Stock Value</h6>
                                                <h4 class="text-primary">${{ number_format($stock->remaining_quantity * $stock->sell_price, 2) }}</h4>
                                                <small class="text-muted">Remaining Value</small>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <h6 class="text-muted">Stock Turnover</h6>
                                                <h4 class="text-success">
                                                    {{ $stock->quantity > 0 ? number_format(($stock->sold_quantity / $stock->quantity) * 100, 1) : 0 }}%
                                                </h4>
                                                <small class="text-muted">Sold Percentage</small>
                                            </div>
                                            <div class="col-12">
                                                <h6 class="text-muted">Stock Status</h6>
                                                @if($stock->remaining_quantity > ($stock->quantity * 0.5))
                                                    <span class="badge badge-success badge-lg">Well Stocked</span>
                                                @elseif($stock->remaining_quantity > 0)
                                                    <span class="badge badge-warning badge-lg">Low Stock</span>
                                                @else
                                                    <span class="badge badge-danger badge-lg">Out of Stock</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($stock->product->getMedia('product_image')->count() > 0 || $stock->product->getMedia('product_other_image')->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border-secondary">
                                    <div class="card-header bg-secondary text-white">
                                        <h5 class="mb-0"><i class="mdi mdi-image"></i> Product Images</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach($stock->product->getMedia('product_image') as $media)
                                            <div class="col-md-3 mb-3">
                                                <div class="card">
                                                    <img src="{{ $media->getUrl() }}" class="card-img-top" alt="Product Image" style="height: 200px; object-fit: cover;">
                                                    <div class="card-body p-2">
                                                        <small class="text-muted">Main Image</small>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach

                                            @foreach($stock->product->getMedia('product_other_image') as $media)
                                            <div class="col-md-3 mb-3">
                                                <div class="card">
                                                    <img src="{{ $media->getUrl() }}" class="card-img-top" alt="Product Image" style="height: 200px; object-fit: cover;">
                                                    <div class="card-body p-2">
                                                        <small class="text-muted">Additional Image</small>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('admin.stockEdit', $stock) }}" class="btn btn-warning">
                                    <i class="mdi mdi-pencil"></i> Edit Stock
                                </a>
                                <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                    <i class="mdi mdi-delete"></i> Delete Stock
                                </button>
                            </div>
                            <div class="col-md-6 text-right">
                                <a href="{{ route('admin.stockIndex') }}" class="btn btn-secondary">
                                    <i class="mdi mdi-arrow-left"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this stock entry?</p>
                <div class="alert alert-warning">
                    <strong>Batch ID:</strong> {{ $stock->batch_id }}<br>
                    <strong>Product:</strong> {{ $stock->product->name }}<br>
                    <strong>Remaining Quantity:</strong> {{ $stock->remaining_quantity }}
                </div>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.stockDestroy', $stock) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function confirmDelete() {
    $('#deleteModal').modal('show');
}
</script>
@endpush
