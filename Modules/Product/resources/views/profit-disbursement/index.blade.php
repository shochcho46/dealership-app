@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Profit Disbursements</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Profit Disbursements</li>
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
                    <h3 class="card-title">Filter Disbursements</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.profitDisbursementCreate') }}" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-plus"></i> Add Disbursement
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.profitDisbursementIndex') }}" id="filterForm">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control form-control-sm" id="start_date" name="start_date"
                                           value="{{ request('start_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control form-control-sm" id="end_date" name="end_date"
                                           value="{{ request('end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="investor_id">Investor</label>
                                    <select class="form-control form-control-sm" id="investor_id" name="investor_id">
                                        <option value="">All Investors</option>
                                        @foreach($investors as $investor)
                                            <option value="{{ $investor->id }}" {{ request('investor_id') == $investor->id ? 'selected' : '' }}>
                                                {{ $investor->name }} {{ $investor->company ? '(' . $investor->company . ')' : '' }}
                                            </option>
                                        @endforeach
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
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <a href="{{ route('admin.profitDisbursementIndex') }}" class="btn btn-secondary btn-sm btn-block">
                                            <i class="mdi mdi-refresh"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Disbursement List</h3>
                </div>
                <div class="card-body">
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

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="disbursementTable">
                            <thead>
                                <tr>
                                    <th width="5%">SL</th>
                                    <th>Investor</th>
                                    <th>Company</th>
                                    <th>Disbursement Date</th>
                                    <th>Amount</th>
                                    <th>Note</th>
                                    <th width="12%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($disbursements as $key => $disbursement)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $disbursement->investor->name }}</td>
                                    <td>{{ $disbursement->investor->company ?? 'N/A' }}</td>
                                    <td>{{ date('d M Y', strtotime($disbursement->disbursement_date)) }}</td>
                                    <td>৳ {{ number_format($disbursement->amount, 2) }}</td>
                                    <td>{{ Str::limit($disbursement->note, 50) ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('admin.profitDisbursementEdit', $disbursement->id) }}"
                                           class="btn btn-warning btn-sm" title="Edit">
                                            <i class="mdi mdi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm delete-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteModal"
                                                data-url="{{ route('admin.profitDisbursementDestroy', $disbursement->id) }}"
                                                title="Delete">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No disbursements found</td>
                                </tr>
                                @endforelse
                                @include('components.delete')
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-right">Total:</th>
                                    <th>৳ {{ number_format($disbursements->sum('amount'), 2) }}</th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('custome-js')
<script>
$(document).ready(function() {
    // Initialize DataTable

    // Delete Button - Set form action dynamically
    $('.delete-btn').on('click', function() {
        const deleteUrl = $(this).data('url');
        $('#deleteForm').attr('action', deleteUrl);
    });
});
</script>
@endpush
