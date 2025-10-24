@extends('layouts.app')

@push('custome-css')
<style>
    .product-main-image {
        width: 400px;
        height: 400px;
        border-radius: 8px;
        border: 2px solid #dee2e6;
        object-fit: cover;
    }
    .product-other-image {
        width: 120px;
        height: 120px;
        border-radius: 8px;
        border: 2px solid #dee2e6;
        object-fit: cover;
        margin: 5px;
        cursor: pointer;
        transition: transform 0.3s ease;
    }
    .product-other-image:hover {
        transform: scale(1.05);
    }
    .color-preview {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 2px solid #dee2e6;
        display: inline-block;
        vertical-align: middle;
        margin-right: 8px;
    }
    .info-card {
        background: #f8f9fa;
        border-left: 4px solid #007bff;
        padding: 15px;
        margin-bottom: 15px;
    }
    .other-images-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 15px;
    }
    .status-badge {
        font-size: 1.1em;
        padding: 8px 16px;
    }
</style>
@endpush

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Product Management</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.productIndex') }}">Products</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
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
                    <h1 class="mt-3">Product Details: {{ $product->name }}</h1>
                    <div class="text-end">
                        <a href="{{ route('admin.productEdit', $product) }}" class="btn btn-primary">
                            <span class="mdi mdi-pencil"></span> Edit
                        </a>
                        <a href="{{ route('admin.productIndex') }}" class="btn btn-outline-secondary">
                            <span class="mdi mdi-format-list-text"></span> Back to List
                        </a>
                    </div>
                </div>

                <div class="row">
                    <!-- Product Images -->
                    <div class="col-md-6">
                        <div class="card card-primary card-outline mb-4">
                            <div class="card-header">
                                <div class="card-title">Product Images</div>
                            </div>
                            <div class="card-body text-center">
                                <!-- Main Product Image -->
                                <div class="mb-3">
                                    <img src="{{ $product->product_image_url }}" alt="{{ $product->name }}" class="product-main-image" id="mainImage">
                                </div>

                                <!-- Other Images -->
                                @if($product->other_images->count() > 0)
                                    <div class="other-images-container">
                                        <img src="{{ $product->product_image_url }}" alt="Main Image" class="product-other-image" onclick="changeMainImage(this.src)">
                                        @foreach($product->other_images as $image)
                                            <img src="{{ $image->getUrl() }}" alt="Product Image" class="product-other-image" onclick="changeMainImage(this.src)">
                                        @endforeach
                                    </div>
                                    <small class="text-muted">Click on thumbnails to view larger image</small>
                                @else
                                    <p class="text-muted">No additional images available</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Product Information -->
                    <div class="col-md-6">
                        <div class="card card-primary card-outline mb-4">
                            <div class="card-header">
                                <div class="card-title">Product Information</div>
                            </div>
                            <div class="card-body">
                                <!-- Basic Info -->
                                <div class="info-card">
                                    <h5><span class="mdi mdi-tag"></span> Basic Information</h5>
                                    <div class="row">
                                        <div class="col-sm-6"><strong>Product Name:</strong></div>
                                        <div class="col-sm-6">{{ $product->name }}</div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-sm-6"><strong>Status:</strong></div>
                                        <div class="col-sm-6">
                                            @if($product->status)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-sm-6"><strong>Created:</strong></div>
                                        <div class="col-sm-6">{{ $product->created_at->format('M d, Y \a\t h:i A') }}</div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-sm-6"><strong>Last Updated:</strong></div>
                                        <div class="col-sm-6">{{ $product->updated_at->format('M d, Y \a\t h:i A') }}</div>
                                    </div>
                                </div>

                                <!-- Color & Unit -->
                                <div class="info-card">
                                    <h5><span class="mdi mdi-palette"></span> Color & Unit</h5>
                                    <div class="row">
                                        <div class="col-sm-6"><strong>Color:</strong></div>
                                        <div class="col-sm-6">
                                            @if($product->color)
                                                <div class="color-preview" style="background-color: {{ $product->color->code }}"></div>
                                                {{ $product->color->name }}
                                                <small class="text-muted">({{ $product->color->code }})</small>
                                            @else
                                                <span class="text-muted">No color assigned</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-sm-6"><strong>Sell Unit:</strong></div>
                                        <div class="col-sm-6">
                                            <span class="badge bg-info">{{ $product->unit->name ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Measurement Details -->
                                @if($product->measurement_unit_name || $product->measurement_unit_number)
                                <div class="info-card">
                                    <h5><span class="mdi mdi-ruler"></span> Measurement Details</h5>
                                    @if($product->measurement_unit_name)
                                        <div class="row">
                                            <div class="col-sm-6"><strong>Unit Name:</strong></div>
                                            <div class="col-sm-6">{{ $product->measurement_unit_name }}</div>
                                        </div>
                                    @endif
                                    @if($product->measurement_unit_number)
                                        <div class="row mt-2">
                                            <div class="col-sm-6"><strong>Unit Number:</strong></div>
                                            <div class="col-sm-6">{{ $product->measurement_unit_number }}</div>
                                        </div>
                                    @endif
                                    @if($product->measurement_unit_number && $product->measurement_unit_name)
                                        <div class="row mt-2">
                                            <div class="col-sm-6"><strong>Full Measurement:</strong></div>
                                            <div class="col-sm-6">
                                                <span class="badge bg-secondary">{{ $product->measurement_unit_number }} {{ $product->measurement_unit_name }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                @endif

                                <!-- Package Details -->
                                @if($product->package_unit_name || $product->package_unit_quantity)
                                <div class="info-card">
                                    <h5><span class="mdi mdi-package-variant"></span> Package Details</h5>
                                    @if($product->package_unit_name)
                                        <div class="row">
                                            <div class="col-sm-6"><strong>Package Unit:</strong></div>
                                            <div class="col-sm-6">{{ $product->package_unit_name }}</div>
                                        </div>
                                    @endif
                                    @if($product->package_unit_quantity)
                                        <div class="row mt-2">
                                            <div class="col-sm-6"><strong>Package Quantity:</strong></div>
                                            <div class="col-sm-6">{{ $product->package_unit_quantity }}</div>
                                        </div>
                                    @endif
                                    @if($product->package_unit_quantity && $product->package_unit_name)
                                        <div class="row mt-2">
                                            <div class="col-sm-6"><strong>Full Package:</strong></div>
                                            <div class="col-sm-6">
                                                <span class="badge bg-warning text-dark">{{ $product->package_unit_quantity }} {{ $product->package_unit_name }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                @endif

                                <!-- Image Stats -->
                                <div class="info-card">
                                    <h5><span class="mdi mdi-image-multiple"></span> Image Statistics</h5>
                                    <div class="row">
                                        <div class="col-sm-6"><strong>Thumbnail:</strong></div>
                                        <div class="col-sm-6">
                                            @if($product->getFirstMedia('product_image'))
                                                <span class="badge bg-success">Available</span>
                                            @else
                                                <span class="badge bg-secondary">Default</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-sm-6"><strong>Other Images:</strong></div>
                                        <div class="col-sm-6">
                                            <span class="badge bg-info">{{ $product->other_images->count() }} image(s)</span>
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
</div>
@endsection

@push('custome-js')
<script>
    function changeMainImage(src) {
        document.getElementById('mainImage').src = src;
    }
</script>
@endpush
