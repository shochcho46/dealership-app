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
    .web-img-grid { display: flex; flex-wrap: wrap; gap: 12px; }
    .web-img-option { position: relative; cursor: pointer; margin: 0; }
    .web-img-option input { position: absolute; opacity: 0; pointer-events: none; }
    .web-img-option img {
        width: 110px; height: 110px; object-fit: cover; border-radius: 10px;
        border: 3px solid #dee2e6; transition: border-color .2s, box-shadow .2s;
    }
    .web-img-option input:checked + img { border-color: #0FB5A6; box-shadow: 0 0 0 3px rgba(15,181,166,.25); }
    .web-img-option input:focus-visible + img { outline: 2px solid #0B1F3A; outline-offset: 2px; }
    .web-img-option .web-img-check {
        position: absolute; top: 6px; right: 6px; width: 24px; height: 24px; border-radius: 50%;
        background: #0FB5A6; color: #fff; display: none; align-items: center; justify-content: center; font-size: 16px;
    }
    .web-img-option input:checked ~ .web-img-check { display: flex; }
    .web-img-option small { display: block; text-align: center; margin-top: 4px; color: #6c757d; }
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

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('error') || $errors->has('website_media_id'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') ?: $errors->first('website_media_id') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

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

                        @php
                            $webImages = $product->allImages();
                            $mainMediaId = optional($product->getFirstMedia('product_image'))->id;
                            $chosenId = optional($product->websiteMedia())->id;
                            $onWebsite = $product->status && $product->total_available_quantity > 0;
                        @endphp
                        <div class="card card-success card-outline mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div class="card-title"><span class="mdi mdi-web"></span> Website Image</div>
                                <div class="ms-auto">
                                    @if($product->hero_position)
                                        <span class="badge bg-warning text-dark" title="Pinned in the website hero banner">Hero slot {{ $product->hero_position }}</span>
                                    @endif
                                    @if($onWebsite)
                                        <a href="{{ route('products.show', [$product->id, \Illuminate\Support\Str::slug($product->name) ?: 'product']) }}"
                                           target="_blank" rel="noopener" class="badge bg-success text-decoration-none">
                                            <span class="mdi mdi-eye"></span> Showing on website
                                        </a>
                                    @else
                                        <span class="badge bg-secondary" title="Only active products with stock are shown on the website">
                                            <span class="mdi mdi-eye-off"></span>
                                            Hidden ({{ $product->status ? 'no stock' : 'inactive' }})
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                @if($webImages->isEmpty())
                                    <p class="text-muted mb-0">This product has no images yet. Upload images from <a href="{{ route('admin.productEdit', $product) }}">Edit</a> — the website will show a placeholder until then.</p>
                                @else
                                    <p class="text-muted small mb-3">Choose the photo customers see on the website product card, as the first photo on the product page and in the hero banner. You can also choose it on the Edit page.</p>
                                    <form action="{{ route('admin.productWebsiteImage', $product) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="web-img-grid mb-3">
                                            @foreach($webImages as $media)
                                                <label class="web-img-option">
                                                    <input type="radio" name="website_media_id" value="{{ $media->id }}" @checked($media->id === $chosenId)>
                                                    <img src="{{ $media->getUrl() }}" alt="Image {{ $loop->iteration }}" loading="lazy">
                                                    <span class="web-img-check"><span class="mdi mdi-check"></span></span>
                                                    <small>{{ $media->id === $mainMediaId ? 'Main image' : 'Image ' . $loop->iteration }}</small>
                                                </label>
                                            @endforeach
                                        </div>
                                        <button type="submit" class="btn btn-success">
                                            <span class="mdi mdi-content-save"></span> Save website image
                                        </button>
                                    </form>
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
