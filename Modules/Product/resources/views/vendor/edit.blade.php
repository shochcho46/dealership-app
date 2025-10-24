@extends('layouts.app')

@push('custome-css')
<style>
    .image-preview {
        width: 150px;
        height: 150px;
        border-radius: 8px;
        border: 2px solid #dee2e6;
        object-fit: cover;
    }
</style>
@endpush

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Vendor Management</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.vendorIndex') }}">Vendors</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
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
                    <h1 class="mt-3">Edit Vendor: {{ $vendor->shop_name }}</h1>
                    <div class="text-end">
                        <a href="{{ route('admin.vendorIndex') }}" class="btn btn-outline-primary">
                            <span class="mdi mdi-format-list-text"></span> View All Vendors
                        </a>
                    </div>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card card-primary card-outline mb-4">
                    <div class="card-header">
                        <div class="card-title">Vendor Information</div>
                    </div>
                    <form action="{{ route('admin.vendorUpdate', $vendor) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="shop_name" class="form-label">Shop Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('shop_name') is-invalid @enderror"
                                               id="shop_name" name="shop_name" placeholder="Enter shop name"
                                               value="{{ old('shop_name', $vendor->shop_name) }}" required>
                                        @error('shop_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="mobile" class="form-label">Mobile <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('mobile') is-invalid @enderror"
                                               id="mobile" name="mobile" placeholder="Enter mobile number"
                                               value="{{ old('mobile', $vendor->mobile) }}" required>
                                        @error('mobile')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contact_person" class="form-label">Contact Person</label>
                                        <input type="text" class="form-control @error('contact_person') is-invalid @enderror"
                                               id="contact_person" name="contact_person" placeholder="Enter contact person name"
                                               value="{{ old('contact_person', $vendor->contact_person) }}">
                                        @error('contact_person')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                               id="email" name="email" placeholder="Enter email address"
                                               value="{{ old('email', $vendor->email) }}">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                            <option value="">Select Status</option>
                                            <option value="1" {{ old('status', $vendor->status) == '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('status', $vendor->status) == '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="full_address" class="form-label">Full Address</label>
                                        <textarea class="form-control @error('full_address') is-invalid @enderror"
                                                  id="full_address" name="full_address" rows="3"
                                                  placeholder="Enter full address">{{ old('full_address', $vendor->full_address) }}</textarea>
                                        @error('full_address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="lat" class="form-label">Latitude</label>
                                        <input type="number" step="any" class="form-control @error('lat') is-invalid @enderror"
                                               id="lat" name="lat" placeholder="Enter latitude"
                                               value="{{ old('lat', $vendor->lat) }}">
                                        @error('lat')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="long" class="form-label">Longitude</label>
                                        <input type="number" step="any" class="form-control @error('long') is-invalid @enderror"
                                               id="long" name="long" placeholder="Enter longitude"
                                               value="{{ old('long', $vendor->long) }}">
                                        @error('long')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="vendor_image" class="form-label">Vendor Image</label>
                                        <input type="file" class="form-control @error('vendor_image') is-invalid @enderror"
                                               id="vendor_image" name="vendor_image" accept="image/*">
                                        @error('vendor_image')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Max file size: 10MB. Accepted formats: JPG, PNG, GIF, WebP</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Current/Preview Image</label>
                                        <div>
                                            <img id="imagePreview" src="{{ $vendor->vendor_image_thumb_url }}" class="image-preview" alt="Vendor Image">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <span class="mdi mdi-content-save"></span> Update Vendor
                            </button>
                            <a href="{{ route('admin.vendorIndex') }}" class="btn btn-secondary">
                                <span class="mdi mdi-cancel"></span> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('custome-js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const imageInput = document.getElementById('vendor_image');
        const imagePreview = document.getElementById('imagePreview');

        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    });
</script>
@endpush
