@extends('layouts.app')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Admin User Details</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.adminUserIndex') }}">Admin Users</a></li>
                    <li class="breadcrumb-item active" aria-current="page">View</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Admin User Information</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.adminUserEdit', $admin->id) }}" class="btn btn-sm btn-warning">
                                <span class="mdi mdi-pencil"></span> Edit
                            </a>
                            <a href="{{ route('admin.adminUserIndex') }}" class="btn btn-sm btn-secondary">
                                <span class="mdi mdi-arrow-left"></span> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Name -->
                            <div class="col-md-3 col-sm-6 mb-3">
                                <label class="form-label fw-bold">Name</label>
                                <div class="p-2 bg-light rounded">{{ $admin->name }}</div>
                            </div>

                            <!-- Email -->
                            <div class="col-md-3 col-sm-6 mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <div class="p-2 bg-light rounded">
                                    <a href="mailto:{{ $admin->email }}">{{ $admin->email }}</a>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="col-md-3 col-sm-6 mb-3">
                                <label class="form-label fw-bold">Phone</label>
                                <div class="p-2 bg-light rounded">
                                    @if($admin->phone)
                                        <a href="tel:{{ $admin->phone }}">{{ $admin->phone }}</a>
                                    @else
                                        <span class="text-muted">Not provided</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Role(s) -->
                            <div class="col-md-3 col-sm-6 mb-3">
                                <label class="form-label fw-bold">Role(s)</label>
                                <div class="p-2">
                                    @if($admin->roles->isNotEmpty())
                                        @foreach($admin->roles as $role)
                                            <span class="badge bg-info">{{ $role->name }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">No role assigned</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="col-md-3 col-sm-6 mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <div class="p-2">
                                    @if($admin->status == 1)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Created At -->
                            <div class="col-md-3 col-sm-6 mb-3">
                                <label class="form-label fw-bold">Created At</label>
                                <div class="p-2 bg-light rounded">{{ $admin->created_at->format('d M Y, H:i A') }}</div>
                            </div>

                            <!-- Last Updated -->
                            <div class="col-md-3 col-sm-6 mb-3">
                                <label class="form-label fw-bold">Last Updated</label>
                                <div class="p-2 bg-light rounded">{{ $admin->updated_at->format('d M Y, H:i A') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permissions Card (if you want to show assigned permissions) -->
                @if($admin->roles->isNotEmpty())
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Assigned Permissions</h3>
                    </div>
                    <div class="card-body">
                        @php
                            $permissions = $admin->getAllPermissions();
                        @endphp
                        @if($permissions->isNotEmpty())
                            <div class="row">
                                @foreach($permissions->chunk(2) as $permissionChunk)
                                    @foreach($permissionChunk as $permission)
                                        <div class="col-md-2 mb-2">
                                            <span class="badge bg-secondary">{{ $permission->name }}</span>
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">No permissions assigned.</p>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
