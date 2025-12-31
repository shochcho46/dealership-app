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
                    <li class="breadcrumb-item active" aria-current="page">Payment Methods</li>
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
                    <h1 class="mt-3">Payment Method List</h1>
                    <div class="text-end">
                        <a href="{{ route('admin.paymentMethodCreate') }}" class="btn btn-primary">
                            <span class="mdi mdi-plus"></span> Add New Payment Method
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
                        <div class="card-title">All Payment Methods</div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Account Name</th>
                                        <th>Account Number</th>
                                        <th>Institute Name</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($paymentMethods as $paymentMethod)
                                        <tr>
                                            <td>{{  $paymentMethods->firstItem() + $loop->index }}</td>
                                            <td>{{ $paymentMethod->account_name }}</td>
                                            <td>{{ $paymentMethod->account_number }}</td>
                                            <td>{{ $paymentMethod->institute_name }}</td>
                                            <td>
                                                @if($paymentMethod->status)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>{{ $paymentMethod->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.paymentMethodEdit', $paymentMethod) }}" class="btn btn-sm btn-outline-primary">
                                                        <span class="mdi mdi-pencil"></span>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-bs-toggle="modal" data-bs-target="#deleteModal" data-url="{{ route('admin.paymentMethodDestroy', $paymentMethod) }}">
                                                        <span class="mdi mdi-delete"></span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No payment methods found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($paymentMethods->hasPages())
                        <div class="card-footer">
                            {{ $paymentMethods->links() }}
                        </div>
                    @endif
                </div>
                @include('components.delete')
            </div>
        </div>
    </div>
</div>
@endsection

@push('custome-js')

@endpush