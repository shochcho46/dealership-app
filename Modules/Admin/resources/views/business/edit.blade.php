@extends('layouts.app')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Business Settings</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Business Settings</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
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
                    <div class="card-header">
                        <h3 class="card-title">Business Information</h3>
                    </div>
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h4 class="alert-heading">Validation Error!</h4>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('admin.businessUpdate', $business) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <!-- Company Name -->
                                <div class="col-md-6 mb-3">
                                    <label for="company_name" class="form-label">Company Name</label>
                                    <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                                           id="company_name" name="company_name" placeholder="Enter company name"
                                           value="{{ old('company_name', $business->company_name) }}">
                                    @error('company_name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Brand Name -->
                                <div class="col-md-6 mb-3">
                                    <label for="brand_name" class="form-label">Brand Name</label>
                                    <input type="text" class="form-control @error('brand_name') is-invalid @enderror"
                                           id="brand_name" name="brand_name" placeholder="Enter brand name"
                                           value="{{ old('brand_name', $business->brand_name) }}">
                                    @error('brand_name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" placeholder="Enter email"
                                           value="{{ old('email', $business->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Mobile One -->
                                <div class="col-md-3 mb-3">
                                    <label for="mobile_one" class="form-label">Mobile One</label>
                                    <input type="text" class="form-control @error('mobile_one') is-invalid @enderror"
                                           id="mobile_one" name="mobile_one" placeholder="Enter mobile number"
                                           value="{{ old('mobile_one', $business->mobile_one) }}">
                                    @error('mobile_one')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Mobile Two -->
                                <div class="col-md-3 mb-3">
                                    <label for="mobile_two" class="form-label">Mobile Two</label>
                                    <input type="text" class="form-control @error('mobile_two') is-invalid @enderror"
                                           id="mobile_two" name="mobile_two" placeholder="Enter mobile number"
                                           value="{{ old('mobile_two', $business->mobile_two) }}">
                                    @error('mobile_two')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Address -->
                                <div class="col-md-12 mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror"
                                              id="address" name="address" rows="3"
                                              placeholder="Enter business address">{{ old('address', $business->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Current Logo -->
                                @if($business->hasMedia('logo'))
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Current Logo</label>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ $business->getFirstMediaUrl('logo') }}"
                                             alt="Company Logo"
                                             class="img-thumbnail"
                                             style="max-width: 200px; max-height: 200px;">
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteLogoModal">
                                            <i class="mdi mdi-delete"></i> Delete Logo
                                        </button>
                                    </div>
                                </div>
                                @endif

                                <!-- Logo -->
                                <div class="col-md-12 mb-3">
                                    <label for="logo" class="form-label">
                                        {{ $business->hasMedia('logo') ? 'Change Logo' : 'Upload Logo' }}
                                    </label>
                                    <input type="file" class="form-control @error('logo') is-invalid @enderror"
                                           id="logo" name="logo" accept="image/jpeg,image/png,image/jpg,image/webp">
                                    <small class="text-muted">Accepted formats: JPEG, PNG, JPG, WEBP. Max size: 2MB</small>
                                    @error('logo')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                                    <i class="mdi mdi-arrow-left"></i> Back
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-content-save"></i> Update Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Logo Modal -->
@if($business->hasMedia('logo'))
<div class="modal fade" id="deleteLogoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the company logo?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.businessDeleteLogo', $business->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@endsection
