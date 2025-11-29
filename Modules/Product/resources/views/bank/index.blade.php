@extends('layouts.app')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Bank Management</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Banks</li>
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
                    <h1 class="mt-3">Bank List</h1>
                    <div class="text-end">
                        <a href="{{ route('admin.bankCreate') }}" class="btn btn-primary">
                            <span class="mdi mdi-plus"></span> Add New Bank
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

                <!-- Filter Card -->
                <div class="card card-primary card-outline mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Filter Banks</h3>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.bankIndex') }}">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select class="form-control form-control-sm" id="status" name="status">
                                            <option value="">All Status</option>
                                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div>
                                            <button type="submit" class="btn btn-primary btn-sm btn-block">
                                                <i class="mdi mdi-filter"></i> Filter
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div>
                                            <a href="{{ route('admin.bankIndex') }}" class="btn btn-secondary btn-sm btn-block">
                                                <i class="mdi mdi-refresh"></i> Reset
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card card-primary card-outline mb-4">
                    <div class="card-header">
                        <div class="card-title">All Banks</div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Bank Name</th>
                                        <th>Account Name</th>
                                        <th>Account Number</th>
                                        <th>Branch</th>
                                        <th>Balance</th>
                                        <th>Transactions</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($banks as $bank)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><strong>{{ $bank->bank_name ?? 'N/A' }}</strong></td>
                                            <td>{{ $bank->account_name ?? 'N/A' }}</td>
                                            <td>{{ $bank->account_number ?? 'N/A' }}</td>
                                            <td>{{ $bank->branch_name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge {{ $bank->balance >= 0 ? 'bg-success' : 'bg-danger' }}">
                                                    ৳{{ number_format($bank->balance, 2) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $bank->bank_account_details_count }}</span>
                                            </td>
                                            <td>
                                                @if($bank->status)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.bankAccountDetailIndex', ['bank_id' => $bank->id]) }}" class="btn btn-sm btn-outline-info">
                                                        <span class="mdi mdi-eye"></span>
                                                    </a>
                                                    <a href="{{ route('admin.bankEdit', $bank) }}" class="btn btn-sm btn-outline-primary">
                                                        <span class="mdi mdi-pencil"></span>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-bs-toggle="modal" data-bs-target="#deleteModal" data-url="{{ route('admin.bankDestroy', $bank) }}">
                                                        <span class="mdi mdi-delete"></span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center">No banks found</td>
                                        </tr>
                                    @endforelse
                                    @include('components.delete')
                                </tbody>
                            </table>
                        </div>
                        @if($banks->hasPages())
                            <div class="mt-3">
                                {{ $banks->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Delete Button - Set form action dynamically
    $('.delete-btn').on('click', function() {
        const deleteUrl = $(this).data('url');
        $('#deleteForm').attr('action', deleteUrl);
    });
});
</script>
@endpush
