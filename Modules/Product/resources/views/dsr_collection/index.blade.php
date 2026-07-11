@extends('layouts.app')

@push('custome-css')
<style>
.vendor-search-wrapper {
    position: relative;
}
.vendor-results {
    position: absolute;
    background: white;
    border: 1px solid #ddd;
    border-top: none;
    max-height: 280px;
    overflow-y: auto;
    width: 100%;
    z-index: 1000;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.vendor-item {
    padding: 8px 12px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
}
.vendor-item:hover { background-color: #f8f9fa; }
.vendor-item:last-child { border-bottom: none; }
.vendor-name { font-weight: bold; color: #333; }
.vendor-mobile { color: #666; font-size: 0.85em; }
</style>
@endpush

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Vendor Collection</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Vendor Collection</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-lg-6 col-md-6">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4>৳{{ number_format($filteredTotal, 2) }}</h4>
                                <p class="mb-0">Total Collection (Filtered)</p>
                            </div>
                            <div class="align-self-center">
                                <i class="mdi mdi-account-cash fa-2x" style="font-size:2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4>৳{{ number_format($totalAll, 2) }}</h4>
                                <p class="mb-0">Total Collection (All Time)</p>
                            </div>
                            <div class="align-self-center">
                                <i class="mdi mdi-cash-multiple fa-2x" style="font-size:2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Vendor Collections</h5>
                    @can('dsr_collection')
                    <a href="{{ route('dsr-collections.create') }}" class="btn btn-success">
                        <i class="mdi mdi-plus"></i> Add Collection
                    </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">

                <!-- Filters -->
                <form method="GET" id="filterForm" class="mb-4">
                    <div class="row g-2">
                        <!-- Vendor search -->
                        <div class="col-md-3">
                            <div class="vendor-search-wrapper">
                                <input type="text" id="filter_vendor_search" class="form-control"
                                    placeholder="Search vendor by name / mobile..."
                                    value="{{ $selectedVendor ? $selectedVendor->shop_name : '' }}"
                                    autocomplete="off">
                                <input type="hidden" name="vendor_id" id="filter_vendor_id" value="{{ request('vendor_id') }}">
                                <div id="filter_vendor_results" class="vendor-results"></div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select name="payment_method_filter" class="form-select">
                                <option value="">All Methods</option>
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method->id }}" {{ request('payment_method_filter') == $method->id ? 'selected' : '' }}>
                                        {{ $method->account_name ?? $method->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date_from" class="form-control"
                                value="{{ request('date_from') }}" placeholder="From Date">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date_to" class="form-control"
                                value="{{ request('date_to') }}" placeholder="To Date">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="mdi mdi-filter"></i>
                            </button>
                        </div>
                        <div class="col-md-1">
                            <a href="{{ route('dsr-collections.index') }}" class="btn btn-outline-secondary w-100">
                                 <i class="mdi mdi-refresh"></i>
                            </a>
                        </div>
                    </div>
                </form>

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

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Vendor</th>
                                <th>Payment Method</th>
                                <th>Amount</th>
                                <th>Deposited By</th>
                                <th>Created By</th>
                                <th>Note</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($collections as $collection)
                                <tr>
                                    <td>{{ $loop->iteration + ($collections->currentPage() - 1) * $collections->perPage() }}</td>
                                    <td>{{ $collection->collection_date ? $collection->collection_date->format('d M Y') : '-' }}</td>
                                    <td>
                                        <strong>{{ $collection->vendor->shop_name ?? 'N/A' }}</strong><br>
                                        <small class="text-muted">{{ $collection->vendor->mobile ?? '' }}</small>
                                    </td>
                                    <td>{{ $collection->paymentMethod->account_name ?? ($collection->paymentMethod->name ?? 'N/A') }}</td>
                                    <td>
                                        <span class="badge bg-success fs-6">
                                            ৳{{ number_format($collection->amount, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($collection->depositeBy)
                                            <strong>{{ $collection->depositeBy->name }}</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($collection->createdBy)
                                            {{ $collection->createdBy->name }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($collection->note)
                                            <span title="{{ $collection->note }}">
                                                {{ Str::limit($collection->note, 30) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(auth()->guard('admin')->user()->hasAnyRole(['SuperAdmin', 'admin']))
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('dsr-collections.show', $collection) }}"
                                                   class="btn btn-sm btn-outline-info" title="View">
                                                    <span class="mdi mdi-eye"></span>
                                                </a>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-danger delete-btn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal"
                                                    data-url="{{ route('dsr-collections.destroy', $collection) }}"
                                                    title="Delete">
                                                    <span class="mdi mdi-delete"></span>
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">No collection records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($collections->hasPages())
                    <div class="mt-3">
                        {{ $collections->links() }}
                    </div>
                @endif
            </div>
        </div>

        @include('components.delete')
    </div>
</div>
@endsection

@push('custome-js')
<script>
$(document).ready(function () {
    let searchTimeout;

    $('#filter_vendor_search').on('input', function () {
        const q = $(this).val().trim();
        clearTimeout(searchTimeout);

        if (q.length < 2) {
            $('#filter_vendor_results').empty().hide();
            if (!q) { $('#filter_vendor_id').val(''); }
            return;
        }

        searchTimeout = setTimeout(function () {
            $.ajax({
                url: "{{ route('dsr.vendors.search') }}",
                data: { q: q },
                success: function (vendors) {
                    let html = '';
                    if (!vendors.length) {
                        html = '<div class="vendor-item text-muted">No vendors found</div>';
                    } else {
                        vendors.forEach(function (v) {
                            html += `<div class="vendor-item" data-id="${v.id}" data-name="${v.shop_name}">
                                        <div class="vendor-name">${v.shop_name}</div>
                                        <div class="vendor-mobile">Mobile: ${v.mobile || ''}</div>
                                     </div>`;
                        });
                    }
                    $('#filter_vendor_results').html(html).show();
                }
            });
        }, 300);
    });

    $(document).on('click', '#filter_vendor_results .vendor-item[data-id]', function () {
        $('#filter_vendor_id').val($(this).data('id'));
        $('#filter_vendor_search').val($(this).data('name'));
        $('#filter_vendor_results').empty().hide();
        $('#filterForm').submit();
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('#filter_vendor_search, #filter_vendor_results').length) {
            $('#filter_vendor_results').hide();
        }
    });
});
</script>
@endpush
