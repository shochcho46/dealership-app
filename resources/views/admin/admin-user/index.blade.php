@extends('layouts.app')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Admin User Management</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Admin Users</li>
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
                    <h1 class="mt-3">Admin Users List</h1>
                    <div class="text-end">
                        <a href="{{ route('admin.adminUserCreate') }}" class="btn btn-primary">
                            <span class="mdi mdi-plus"></span> Add New Admin
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

                <!-- Main Table Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">All Admin Users</h3>
                    </div>
                    <div class="card-body">
                        <!-- Search Filter -->
                        <div class="mb-3">
                            <form action="{{ route('admin.adminUserIndex') }}" method="GET" class="row g-2">
                                <div class="col-md-8">
                                    <input type="text" name="search" class="form-control form-control-sm"
                                           placeholder="Search by name, email, or phone..."
                                           value="{{ $search ?? '' }}">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary btn-sm ">
                                        <span class="mdi mdi-magnify"></span> Search
                                    </button>
                                    <a href="{{ route('admin.adminUserIndex') }}" class="btn btn-secondary btn-sm ml-2">
                                        <span class="mdi mdi-close"></span> Clear
                                    </a>
                                </div>
                            </form>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Role(s)</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($admins as $admin)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <strong>{{ $admin->name }}</strong>
                                            </td>
                                            <td>{{ $admin->email }}</td>
                                            <td>
                                                @if($admin->phone)
                                                    {{ $admin->phone }}
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($admin->roles->isNotEmpty())
                                                    @foreach($admin->roles as $role)
                                                        <span class="badge bg-info">{{ $role->name }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">No role assigned</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($admin->status == 1)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.adminUserShow', $admin->id) }}"
                                                   class="btn btn-sm btn-info" title="View">
                                                    <span class="mdi mdi-eye"></span>
                                                </a>
                                                <a href="{{ route('admin.adminUserEdit', $admin->id) }}"
                                                   class="btn btn-sm btn-warning" title="Edit">
                                                    <span class="mdi mdi-pencil"></span>
                                                </a>
                                                <form action="{{ route('admin.adminUserToggleStatus', $admin->id) }}"
                                                      method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit"
                                                            class="btn btn-sm {{ $admin->status ? 'btn-dark' : 'btn-success' }}"
                                                            title="{{ $admin->status ? 'Deactivate' : 'Activate' }}">
                                                        <span class="mdi {{ $admin->status ? 'mdi-block-helper' : 'mdi-check-circle' }}"></span>
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-sm btn-danger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteModal{{ $admin->id }}"
                                                        title="Delete">
                                                    <span class="mdi mdi-delete"></span>
                                                </button>

                                                <!-- Delete Modal -->
                                                <div class="modal fade" id="deleteModal{{ $admin->id }}" tabindex="-1"
                                                     aria-labelledby="deleteModalLabel{{ $admin->id }}" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteModalLabel{{ $admin->id }}">
                                                                    Confirm Delete
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to delete the admin user "<strong>{{ $admin->name }}</strong>"?
                                                                This action cannot be undone.
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <form action="{{ route('admin.adminUserDestroy', $admin->id) }}"
                                                                      method="POST" style="display: inline;">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">No admin users found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-3">
                            {{ $admins->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
