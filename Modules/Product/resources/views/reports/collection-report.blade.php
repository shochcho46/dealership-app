@extends('layouts.app')

@section('title', 'Collection Report')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Collection Report</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Collection Report</li>
                </ol>
            </div>
        </div>
    </div>
</div>


<div class="app-content">
    <div class="container-fluid">

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Collection Report</h3>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="GET" action="{{ route('admin.reportCollection') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label">Date From</label>
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date To</label>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Vendor</label>
                                <select name="vendor_id" class="form-select select2">
                                    <option value="">All Vendors</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->shop_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Pay Method</label>
                                <select name="payment_method_id" class="form-select select2">
                                    <option value="">All Methods</option>
                                    @foreach($paymentMethods as $pm)
                                        <option value="{{ $pm->id }}" {{ request('payment_method_id') == $pm->id ? 'selected' : '' }}>
                                            {{ $pm->account_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Deposited By</label>
                                <select name="deposite_by" class="form-select select2">
                                    <option value="">All Users</option>
                                    @foreach($admins as $admin)
                                        <option value="{{ $admin->id }}" {{ request('deposite_by') == $admin->id ? 'selected' : '' }}>
                                            {{ $admin->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">Limit</label>
                                <select name="limit" class="form-select">
                                    <option value="10" {{ request('limit') == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ request('limit') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('limit') == 50 || !request('limit') ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('limit') == 100 ? 'selected' : '' }}>100</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="mdi mdi-filter"></i> Filter
                                </button>
                                <a href="{{ route('admin.reportCollection') }}" class="btn btn-sm btn-secondary">
                                    <i class="mdi mdi-refresh"></i> Reset
                                </a>
                                <button type="button" class="btn btn-sm btn-success" onclick="exportTableToExcel('collectionTable', 'Collection_Report')">
                                    <i class="mdi mdi-file-excel"></i> Export to Excel
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Report Table -->
                    <div class="table-responsive">
                        <table id="collectionTable" class="table table-bordered table-striped table-hover table-sm">
                            <thead class="table">
                                <tr>
                                    <th width="5%">SL</th>
                                    <th width="10%">Date</th>
                                    <th width="20%">Vendor</th>
                                    <th width="15%">Pay Method</th>
                                    <th width="12%" class="text-end">Amount</th>
                                    <th width="15%">Deposited By</th>
                                    <th width="23%">Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accounts as $key => $account)
                                    <tr>
                                        <td>{{ $accounts->firstItem() + $key }}</td>
                                        <td>{{ optional($account->collection_date)->format('d M Y') ?? $account->created_at->format('d M Y') }}</td>
                                        <td>{{ $account->vendor->shop_name ?? 'N/A' }}</td>
                                        <td>{{ $account->paymentMethod->account_name ?? 'N/A' }}</td>
                                        <td class="text-end">৳{{ number_format($account->amount, 2) }}</td>
                                        <td>{{ $account->depositeBy->name ?? 'N/A' }}</td>
                                        <td>{{ $account->note ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No collection records found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if($accounts->count() > 0)
                            <tfoot class="table-secondary">
                                <tr>
                                    <th colspan="4" class="text-end">Page Total:</th>
                                    <th class="text-end">৳{{ number_format($pageTotal, 2) }}</th>
                                    <th colspan="2"></th>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">Filtered Total:</th>
                                    <th class="text-end">৳{{ number_format($filteredTotal, 2) }}</th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Showing {{ $accounts->firstItem() ?? 0 }} to {{ $accounts->lastItem() ?? 0 }} of {{ $accounts->total() }} entries
                        </div>
                        <div>
                            {{ $accounts->withQueryString()->links() }}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/table2excel@1.0.4/dist/table2excel.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    });

    function exportTableToExcel(tableID, filename = '') {
        var table2excel = new Table2Excel();
        table2excel.export(document.getElementById(tableID), filename);
    }
</script>
@endpush
