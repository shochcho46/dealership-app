@extends('layouts.app')

@push('custome-css')

@endpush

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Payment Method Management</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.paymentMethodIndex') }}">Payment Methods</a>
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
                    <h1 class="mt-3">Edit Payment Method</h1>
                    <div class="text-end">
                        <a href="{{ route('admin.paymentMethodIndex') }}" class="btn btn-outline-primary">
                            <span class="mdi mdi-format-list-text"></span> View All Payment Methods
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
                        <div class="card-title">Payment Method Information</div>
                    </div>
                    <form action="{{ route('admin.paymentMethodUpdate', $paymentMethod) }}" method="post">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="account_name" class="form-label">Account Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('account_name') is-invalid @enderror"
                                               id="account_name" name="account_name" placeholder="Enter account name"
                                               value="{{ old('account_name', $paymentMethod->account_name) }}" required>
                                        @error('account_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="account_number" class="form-label">Account Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('account_number') is-invalid @enderror"
                                               id="account_number" name="account_number" placeholder="Enter account number"
                                               value="{{ old('account_number', $paymentMethod->account_number) }}" required>
                                        @error('account_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="institute_name" class="form-label">Institute Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('institute_name') is-invalid @enderror"
                                               id="institute_name" name="institute_name" placeholder="Enter institute name (e.g., Bank name, Mobile Banking)"
                                               value="{{ old('institute_name', $paymentMethod->institute_name) }}" required>
                                        @error('institute_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Examples: Dutch Bangla Bank, bKash, Rocket, etc.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                            <option value="">Select Status</option>
                                            <option value="1" {{ old('status', $paymentMethod->status) == '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('status', $paymentMethod->status) == '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <span class="mdi mdi-content-save"></span> Update Payment Method
                            </button>
                            <a href="{{ route('admin.paymentMethodIndex') }}" class="btn btn-secondary">
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

@endpush