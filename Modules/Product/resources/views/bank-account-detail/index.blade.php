@extends('layouts.app')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Bank Transactions</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Bank Transactions</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <!-- Filter Card -->
        <div class="card card-primary card-outline mb-3">
            <div class="card-header">
                <h3 class="card-title">Filter Transactions</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.bankAccountDetailCreate') }}" class="btn btn-primary btn-sm">
                        <i class="mdi mdi-plus"></i> Add Transaction
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.bankAccountDetailIndex') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="bank_id">Bank</label>
                                <select class="form-control form-control-sm" id="bank_id" name="bank_id">
                                    <option value="">All Banks</option>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->id }}" {{ request('bank_id') == $bank->id ? 'selected' : '' }}>
                                            {{ $bank->bank_name }} - {{ $bank->account_number }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="type">Type</label>
                                <select class="form-control form-control-sm" id="type" name="type">
                                    <option value="">All Types</option>
                                    <option value="1" {{ request('type') == 1 ? 'selected' : '' }}>Credit</option>
                                    <option value="2" {{ request('type') == 2 ? 'selected' : '' }}>Debit</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="date" class="form-control form-control-sm" id="start_date" name="start_date"
                                       value="{{ request('start_date') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="date" class="form-control form-control-sm" id="end_date" name="end_date"
                                       value="{{ request('end_date') }}">
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
                                    <a href="{{ route('admin.bankAccountDetailIndex') }}" class="btn btn-secondary btn-sm btn-block">
                                        <i class="mdi mdi-refresh"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="small-box bg-success text-white">
                    <div class="inner">
                        <h3>৳{{ number_format($totalCredit, 2) }}</h3>
                        <p>Total Credit</p>
                    </div>
                    <div class="icon">
                        <i class="mdi mdi-arrow-down"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-danger text-white">
                    <div class="inner">
                        <h3>৳{{ number_format($totalDebit, 2) }}</h3>
                        <p>Total Debit</p>
                    </div>
                    <div class="icon">
                        <i class="mdi mdi-arrow-up"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box {{ $balance >= 0 ? 'bg-info' : 'bg-warning' }} text-white">
                    <div class="inner">
                        <h3>৳{{ number_format($balance, 2) }}</h3>
                        <p>Balance</p>
                    </div>
                    <div class="icon">
                        <i class="mdi mdi-cash"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-primary text-white">
                    <div class="inner">
                        <h3>{{ $details->total() }}</h3>
                        <p>Total Transactions</p>
                    </div>
                    <div class="icon">
                        <i class="mdi mdi-swap-horizontal"></i>
                    </div>
                </div>
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
                <div class="card-title">Transaction List</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Bank</th>
                                <th>Account Number</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Note</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($details as $detail)
                                <tr>
                                    <td>{{ $details->firstItem() + $loop->index }}</td>
                                    <td>{{ $detail->transaction_date->format('d M Y') }}</td>
                                    <td>{{ $detail->bank->bank_name ?? 'N/A' }}</td>
                                    <td>{{ $detail->bank->account_number ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge {{ $detail->type_badge_class }}">
                                            {{ $detail->type_text }}
                                        </span>
                                    </td>
                                    <td class="{{ $detail->type == 1 ? 'text-success' : 'text-danger' }}">
                                        ৳{{ number_format($detail->amount, 2) }}
                                    </td>
                                    <td>{{ Str::limit($detail->note, 30) ?? 'N/A' }}</td>
                                    <td>{{ $detail->creator->name ?? 'System' }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.bankAccountDetailEdit', $detail) }}" class="btn btn-sm btn-outline-primary">
                                                <span class="mdi mdi-pencil"></span>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-bs-toggle="modal" data-bs-target="#deleteModal" data-url="{{ route('admin.bankAccountDetailDestroy', $detail) }}">
                                                <span class="mdi mdi-delete"></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No transactions found</td>
                                </tr>
                            @endforelse
                            @include('components.delete')
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-right">Totals:</th>
                                <th class="text-success">Credit: ৳{{ number_format($totalCredit, 2) }}</th>
                                <th class="text-danger">Debit: ৳{{ number_format($totalDebit, 2) }}</th>
                                <th colspan="2">Balance: ৳{{ number_format($balance, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @if($details->hasPages())
                    <div class="mt-3">
                        {{ $details->links() }}
                    </div>
                @endif
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
