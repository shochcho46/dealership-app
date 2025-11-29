@extends('layouts.app')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Edit Bank</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.bankIndex') }}">Banks</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Bank Information</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.bankIndex') }}" class="btn btn-secondary btn-sm">
                        <i class="mdi mdi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            <form action="{{ route('admin.bankUpdate', $bank) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bank_name">Bank Name</label>
                                <input type="text"
                                       class="form-control @error('bank_name') is-invalid @enderror"
                                       id="bank_name"
                                       name="bank_name"
                                       value="{{ old('bank_name', $bank->bank_name) }}"
                                       placeholder="Enter bank name">
                                @error('bank_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="account_name">Account Name</label>
                                <input type="text"
                                       class="form-control @error('account_name') is-invalid @enderror"
                                       id="account_name"
                                       name="account_name"
                                       value="{{ old('account_name', $bank->account_name) }}"
                                       placeholder="Enter account name">
                                @error('account_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="account_number">Account Number</label>
                                <input type="text"
                                       class="form-control @error('account_number') is-invalid @enderror"
                                       id="account_number"
                                       name="account_number"
                                       value="{{ old('account_number', $bank->account_number) }}"
                                       placeholder="Enter account number">
                                @error('account_number')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="branch_name">Branch Name</label>
                                <input type="text"
                                       class="form-control @error('branch_name') is-invalid @enderror"
                                       id="branch_name"
                                       name="branch_name"
                                       value="{{ old('branch_name', $bank->branch_name) }}"
                                       placeholder="Enter branch name">
                                @error('branch_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox"
                                           class="custom-control-input"
                                           id="status"
                                           name="status"
                                           value="1"
                                           {{ old('status', $bank->status) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="status">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save"></i> Update
                    </button>
                    <a href="{{ route('admin.bankIndex') }}" class="btn btn-secondary">
                        <i class="mdi mdi-cancel"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
