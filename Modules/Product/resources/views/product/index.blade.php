@extends('layouts.app')

@push('custome-css')
<style>
    .product-image {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        border: 2px solid #dee2e6;
        object-fit: cover;
    }
    .color-preview {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 1px solid #dee2e6;
        display: inline-block;
        vertical-align: middle;
        margin-right: 5px;
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
                    <li class="breadcrumb-item active" aria-current="page">Products</li>
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
                    <h1 class="mt-3">Product List</h1>
                    <div class="text-end">
                        <a href="{{ route('admin.productCreate') }}" class="btn btn-outline-primary">
                            <span class="mdi mdi-plus"></span> Product
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

                <div class="card card-primary card-outline mb-4">
                    <div class="card-header">
                        <div class="card-title">All Products</div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Image</th>
                                        <th>Product Name</th>
                                        <th>Color</th>
                                        <th>Sale Unit</th>
                                        <th>Measurement</th>
                                        <th>Package</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($products as $product)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <img src="{{ $product->product_image_thumb_url }}" alt="Product Image" class="product-image">
                                            </td>
                                            <td>{{ $product->name }}</td>
                                            <td>
                                                @if($product->color)
                                                    <div class="color-preview" style="background-color: {{ $product->color->code }}"></div>
                                                    {{ $product->color->name }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>{{ $product->unit->name ?? 'N/A' }}</td>
                                            <td>
                                                @if($product->measurement_unit_name)
                                                    {{ $product->measurement_unit_number }} {{ $product->measurement_unit_name }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>
                                                @if($product->package_unit_name)
                                                    {{ $product->package_unit_quantity }} {{ $product->package_unit_name }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>
                                                @if($product->status)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.productShow', $product) }}" class="btn btn-sm btn-outline-info">
                                                        <span class="mdi mdi-eye"></span>
                                                    </a>
                                                    <a href="{{ route('admin.productEdit', $product) }}" class="btn btn-sm btn-outline-primary">
                                                        <span class="mdi mdi-pencil"></span>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-bs-toggle="modal" data-bs-target="#deleteModal" data-url="{{ route('admin.productDestroy', $product) }}">
                                                        <span class="mdi mdi-delete"></span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center">No products found</td>
                                        </tr>
                                    @endforelse
                                    @include('components.delete')
                                </tbody>
                            </table>
                        </div>
                        @if($products->hasPages())
                            <div class="mt-3">
                                {{ $products->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('custome-js')

@endpush
