@extends('layouts.app')

@push('custome-css')
<style>
    .account-summary-card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s;
    }

    .account-summary-card:hover {
        transform: translateY(-5px);
    }

    .summary-icon {
        font-size: 2rem;
        opacity: 0.8;
    }

    .summary-amount {
        font-size: 1.4rem;
        word-break: break-word;
    }

    .summary-title {
        font-size: 0.75rem;
    }

    .invoice-link {
        color: #007bff;
        text-decoration: none;
        font-weight: 500;
    }

    .invoice-link:hover {
        text-decoration: underline;
    }

    .vendor-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .date-filter-card {
        border-radius: 10px;
        background: #f8f9fa;
    }
</style>
@endpush

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Vendor Account</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.vendorIndex') }}">Vendors</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Account</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <!-- Vendor Info Header -->
        <div class="vendor-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-1">{{ $vendor->shop_name }}</h2>
                    <p class="mb-0">
                        <i class="mdi mdi-phone"></i> {{ $vendor->mobile }}
                        @if($vendor->email)
                            | <i class="mdi mdi-email"></i> {{ $vendor->email }}
                        @endif
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('admin.vendorIndex') }}" class="btn btn-light">
                        <i class="mdi mdi-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Date Filter -->
        <div class="card date-filter-card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.vendorAccount', $vendor->uuid) }}" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                               value="{{ $startDate }}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                               value="{{ $endDate }}" required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.vendorAccount', $vendor->uuid) }}" class="btn btn-secondary">
                            <i class="mdi mdi-refresh"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Period Summary Cards -->
        <h5 class="mb-3"><i class="mdi mdi-calendar-range"></i> Period Summary ({{ date('d M, Y', strtotime($startDate)) }} - {{ date('d M, Y', strtotime($endDate)) }})</h5>
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card account-summary-card text-white bg-danger">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white mb-1 summary-title">Period Debit</h6><br>
                                <h5 class="mb-0 summary-amount">৳{{ number_format($totalDebit, 2) }}</h5>
                            </div>
                            <i class="mdi mdi-arrow-up-bold summary-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card account-summary-card text-white bg-success">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white mb-1 summary-title">Period Credit</h6><br>
                                <h5 class="mb-0 summary-amount">৳{{ number_format($totalCredit, 2) }}</h5>
                            </div>
                            <i class="mdi mdi-arrow-down-bold summary-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card account-summary-card text-white {{ $balance >= 0 ? 'bg-info' : 'bg-warning' }}">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white mb-1 summary-title">Period Balance</h6><br>
                                <h5 class="mb-0 summary-amount">৳{{ number_format($balance, 2) }}</h5>
                            </div>
                            <i class="mdi mdi-calculator summary-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card account-summary-card text-white bg-secondary">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white mb-1 summary-title">Period Records</h6><br>
                                <h5 class="mb-0 summary-amount">{{ $accounts->count() }}</h5>
                            </div>
                            <i class="mdi mdi-file-document-multiple summary-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- All-Time Summary Cards -->
        <h5 class="mb-3"><i class="mdi mdi-clock-outline"></i> All-Time Summary</h5>
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card account-summary-card text-white" style="background: linear-gradient(135deg, #d63031 0%, #e17055 100%);">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white mb-1 summary-title">All-Time Debit</h6><br>
                                <h5 class="mb-0 summary-amount">৳{{ number_format($allTimeDebit, 2) }}</h5>
                            </div>
                            <i class="mdi mdi-trending-up summary-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card account-summary-card text-white" style="background: linear-gradient(135deg, #00b894 0%, #00cec9 100%);">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white mb-1 summary-title">All-Time Credit</h6><br>
                                <h5 class="mb-0 summary-amount">৳{{ number_format($allTimeCredit, 2) }}</h5>
                            </div>
                            <i class="mdi mdi-trending-down summary-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card account-summary-card text-white {{ $allTimeBalance >= 0 ? 'bg-primary' : 'bg-dark' }}">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white mb-1 summary-title">Overall Balance</h6><br>
                                <h5 class="mb-0 summary-amount">৳{{ number_format($allTimeBalance, 2) }}</h5>
                            </div>
                            <i class="mdi mdi-wallet summary-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card account-summary-card text-white" style="background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%);">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white mb-1 summary-title">Total Transactions</h6><br>
                                <h5 class="mb-0 summary-amount">{{ number_format($totalTransactions) }}</h5>
                            </div>
                            <i class="mdi mdi-chart-line summary-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Transaction History ({{ $accounts->count() }} records)</h3>

                    <div class="card-tools">
                        @can('vendor_account_list')

                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addInvestmentModal">
                                <i class="mdi mdi-plus"></i> Add account
                            </button>
                        @endcan
                        {{-- <a href="{{ route('admin.investorIndex') }}" class="btn btn-secondary btn-sm">
                            <i class="mdi mdi-arrow-left"></i> Back
                        </a> --}}
                    </div>
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
                    <table class="table table-striped table-hover">
                        <thead class="">
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Invoice/Order</th>
                                <th>Type</th>
                                <th>Payment Method</th>
                                <th>Amount</th>
                                <th>Note</th>
                                <th>Created By</th>
                                <th>Deposited By</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($accounts as $account)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $account->collection_date ? $account->collection_date->format('d M, Y') : $account->created_at->format('d M, Y') }}</td>
                                    <td>
                                        @if($account->order)
                                            <a href="{{ route('invoices.preview', $account->order->id) }}"
                                               class="invoice-link"
                                               target="_blank"
                                               title="Click to view invoice">
                                                {{ $account->order->invoice_id }}
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $account->type_badge_class }}">
                                            {{ $account->type_text }}
                                        </span>
                                    </td>
                                    <td>{{ $account->paymentMethod->account_name ?? 'N/A' }}</td>
                                    <td>
                                        <strong class="{{ $account->type == 1 ? 'text-danger' : 'text-success' }}">
                                            {{ $account->type == 1 ? '-' : '+' }}৳{{ number_format($account->amount, 2) }}
                                        </strong>
                                    </td>
                                    <td>
                                        @if($account->note)
                                            <span class="text-truncate d-inline-block" style="max-width: 150px;"
                                                  title="{{ $account->note }}">
                                                {{ $account->note }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $account->createdBy->name ?? 'N/A' }}</td>
                                    <td>{{ $account->depositeBy->name ?? 'N/A' }}</td>

                                    <td>
                                        @can('vendor_account_list')

                                            <button type="button" class="btn btn-danger btn-sm delete-btn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal"
                                                    data-url="{{ route('admin.destroyVendorAccount', $account->id) }}"
                                                    title="Delete">
                                                <i class="mdi mdi-delete"></i>
                                            </button>

                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="mdi mdi-information-outline" style="font-size: 2rem;"></i>
                                        <p class="mb-0 mt-2">No transactions found for the selected date range.</p>
                                    </td>
                                </tr>
                            @endforelse

                             @include('components.delete')
                        </tbody>
                        @if($accounts->count() > 0)
                            <tfoot class="table-secondary">
                                <tr>
                                    <th colspan="5" class="text-end">Totals:</th>
                                    <th colspan="5">
                                        <span class="text-danger">Debit: ৳{{ number_format($totalDebit, 2) }}</span>
                                        <span class="mx-2">|</span>
                                        <span class="text-success">Credit: ৳{{ number_format($totalCredit, 2) }}</span>
                                        <span class="mx-2">|</span>
                                        <span class="{{ $balance >= 0 ? 'text-primary' : 'text-warning' }}">
                                            Balance: ৳{{ number_format($balance, 2) }}
                                        </span>
                                    </th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
                 <div class="mt-3">
                    {{ $accounts->links() }}
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Add Investment Modal -->
<div class="modal fade" id="addInvestmentModal" tabindex="-1" role="dialog" aria-labelledby="addInvestmentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.storeVendorAccount') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addInvestmentModalLabel">Add account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="amount">Amount <span class="text-danger">*</span></label>
                        <input type="number"
                               class="form-control @error('amount') is-invalid @enderror"
                               id="amount"
                               name="amount"
                               step="0.01"
                               value="{{ old('amount') }}"
                               placeholder="Enter amount"
                               required>
                        @error('amount')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="type">Type <span class="text-danger">*</span></label>
                        <select
                            class="form-control @error('type') is-invalid @enderror"
                            id="type"
                            name="type"
                            required>
                            <option value="">Select Type</option>
                            <option value="1" {{ old('type') == 1 ? 'selected' : '' }}>Debit</option>
                            <option value="2" {{ old('type') == 2 ? 'selected' : '' }}>Credit</option>
                        </select>
                        @error('type')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="deposite_by">Deposite By <span class="text-danger">*</span></label>
                        <select name="deposite_by" id="deposite_by" class="form-control @error('deposite_by') is-invalid @enderror" required>
                            <option value="">Select Admin</option>
                            @foreach($admins as $admin)
                                <option value="{{ $admin->id }}" {{ old('deposite_by', Auth::guard('admin')->id()) == $admin->id ? 'selected' : '' }}>
                                    {{ $admin->name }} ({{ $admin->roles_string }})
                                </option>
                            @endforeach
                        </select>
                        @error('deposite_by')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                    </div>

                    <input type="hidden" name="vendor_id" value="{{ $vendor->id }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="mdi mdi-cancel"></i> Close
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save"></i> Save Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('custome-js')
<script>
    // Set max date to today for date inputs
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('start_date').setAttribute('max', today);
        document.getElementById('end_date').setAttribute('max', today);
    });

    $(document).ready(function() {
     // Show modal if there are validation errors
        @if($errors->any())
            var addModal = new bootstrap.Modal(document.getElementById('addInvestmentModal'));
            addModal.show();
        @endif
    });
    // Delete Investment Button - Set form action dynamically
</script>
@endpush
