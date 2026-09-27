@extends('layouts.app')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Route Plan Management</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Route Plans</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="mt-3">Route Plan List</h1>
            <a href="{{ route('admin.routePlanCreate') }}" class="btn btn-primary">
                <span class="mdi mdi-plus"></span> Add Route Plan
            </a>
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
                <div class="card-title">All Route Plans</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Vendors</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($routePlans as $routePlan)
                                <tr>
                                    <td>{{ $routePlans->firstItem() + $loop->index }}</td>
                                    <td>{{ $routePlan->name }}</td>
                                    <td>{{ $routePlan->description ?: '-' }}</td>
                                    <td><span class="badge bg-secondary">{{ $routePlan->vendors_count }}</span></td>
                                    <td>{{ $routePlan->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.routePlanEdit', $routePlan) }}" class="btn btn-sm btn-outline-primary" title="Edit route plan">
                                                <span class="mdi mdi-pencil"></span>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-bs-toggle="modal" data-bs-target="#deleteModal" data-url="{{ route('admin.routePlanDestroy', $routePlan) }}" title="Delete route plan">
                                                <span class="mdi mdi-delete"></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No route plans found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($routePlans->hasPages())
                    <div class="mt-3">
                        {{ $routePlans->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('components.delete')
@endsection

@push('custome-js')
@endpush
