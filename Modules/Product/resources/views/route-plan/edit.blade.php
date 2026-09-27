@extends('layouts.app')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3 class="mb-0">Route Plan Management</h3></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.routePlanIndex') }}">Route Plans</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="mt-3">Edit Route Plan: {{ $routePlan->name }}</h1>
            <a href="{{ route('admin.routePlanIndex') }}" class="btn btn-outline-primary">
                <span class="mdi mdi-format-list-text"></span> View All Route Plans
            </a>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card card-primary card-outline mb-4">
            <div class="card-header"><div class="card-title">Route Plan Information</div></div>
            <form action="{{ route('admin.routePlanUpdate', $routePlan) }}" method="post">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $routePlan->name) }}" maxlength="255" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $routePlan->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><span class="mdi mdi-content-save"></span> Update Route Plan</button>
                    <a href="{{ route('admin.routePlanIndex') }}" class="btn btn-secondary"><span class="mdi mdi-cancel"></span> Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('custome-js')
@endpush
