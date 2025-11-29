@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Profit Distribution</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Profit Distribution</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Filter Card -->
            <div class="card card-primary card-outline mb-3">
                <div class="card-header">
                    <h3 class="card-title">Filter Profit Distributions</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.profitDistributeCreate') }}" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-plus"></i> Add Profit Distribution
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.profitDistributeIndex') }}" id="filterForm">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="year">Year</label>
                                    <select class="form-control form-control-sm" id="year" name="year">
                                        <option value="">All Years</option>
                                        @for($y = date('Y'); $y >= 2020; $y--)
                                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                                                {{ $y }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="month">Month</label>
                                    <select class="form-control form-control-sm" id="month" name="month">
                                        <option value="">All Months</option>
                                        <option value="1" {{ request('month') == 1 ? 'selected' : '' }}>January</option>
                                        <option value="2" {{ request('month') == 2 ? 'selected' : '' }}>February</option>
                                        <option value="3" {{ request('month') == 3 ? 'selected' : '' }}>March</option>
                                        <option value="4" {{ request('month') == 4 ? 'selected' : '' }}>April</option>
                                        <option value="5" {{ request('month') == 5 ? 'selected' : '' }}>May</option>
                                        <option value="6" {{ request('month') == 6 ? 'selected' : '' }}>June</option>
                                        <option value="7" {{ request('month') == 7 ? 'selected' : '' }}>July</option>
                                        <option value="8" {{ request('month') == 8 ? 'selected' : '' }}>August</option>
                                        <option value="9" {{ request('month') == 9 ? 'selected' : '' }}>September</option>
                                        <option value="10" {{ request('month') == 10 ? 'selected' : '' }}>October</option>
                                        <option value="11" {{ request('month') == 11 ? 'selected' : '' }}>November</option>
                                        <option value="12" {{ request('month') == 12 ? 'selected' : '' }}>December</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
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
                                        <a href="{{ route('admin.profitDistributeIndex') }}" class="btn btn-secondary btn-sm btn-block">
                                            <i class="mdi mdi-refresh"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
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

            <div class="row">
                @forelse($profitDistributes as $distribute)
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12">
                    <div class="card card-outline {{ $distribute->status ? 'card-success' : 'card-secondary' }}" style="margin-bottom: 10px;">
                        <div class="card-header p-2">
                            <h6 class="card-title mb-0" style="font-size: 0.85rem;">
                                <i class="mdi mdi-calendar"></i> {{ $distribute->period_text }}
                            </h6>
                            <div class="card-tools">
                                <span class="badge bg-{{ $distribute->status ? 'success' : 'secondary' }}" style="font-size: 0.65rem;">
                                    {{ $distribute->status ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-2" style="font-size: 0.8rem;">
                            <h6 class="font-weight-bold mb-2" style="font-size: 0.8rem;">{{ Str::limit($distribute->title, 25) }}</h6>

                            <div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Total:</span>
                                    <span class="font-weight-bold">৳ {{ number_format($distribute->total_amount, 0) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-success">Credit:</span>
                                    <span class="text-success">৳ {{ number_format($distribute->total_credit, 0) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-danger">Debit:</span>
                                    <span class="text-danger">৳ {{ number_format($distribute->total_debit, 0) }}</span>
                                </div>
                                <hr class="my-1">
                                <div class="d-flex justify-content-between">
                                    <span class="font-weight-bold">Balance:</span>
                                    <span class="font-weight-bold {{ $distribute->balance >= 0 ? 'text-success' : 'text-danger' }}">
                                        ৳ {{ number_format($distribute->balance, 0) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer p-1">
                            <div class="btn-group btn-group-sm w-100">
                                <a href="{{ route('admin.profitDistributeDetails', $distribute->id) }}"
                                   class="btn btn-info btn-sm" title="View Details" style="font-size: 0.7rem; padding: 0.2rem 0.4rem;">
                                    <i class="mdi mdi-eye"></i>
                                </a>
                                <a href="{{ route('admin.profitDistributeEdit', $distribute->id) }}"
                                   class="btn btn-warning btn-sm" title="Edit" style="font-size: 0.7rem; padding: 0.2rem 0.4rem;">
                                    <i class="mdi mdi-pencil"></i>
                                </a>
                                <button type="button"
                                        class="btn btn-danger btn-sm delete-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteModal"
                                        data-url="{{ route('admin.profitDistributeDestroy', $distribute->id) }}"
                                        title="Delete"
                                        style="font-size: 0.7rem; padding: 0.2rem 0.4rem;">
                                    <i class="mdi mdi-delete"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <i class="mdi mdi-information"></i> No profit distributions found.
                        <a href="{{ route('admin.profitDistributeCreate') }}">Create one now</a>
                    </div>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($profitDistributes->hasPages())
                <div class="row">
                    <div class="col-md-12">
                        {{ $profitDistributes->links() }}
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>

@include('components.delete')
@endsection

@push('custome-js')
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
