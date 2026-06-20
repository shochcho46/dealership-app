@extends('layouts.app')

@section('title', 'Invoice Management')

@push('custome-css')
<style>
    .invoice-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .invoice-card .number {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .invoice-card .label {
        font-size: 0.9rem;
        opacity: 0.9;
    }

    .bulk-actions {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 20px;
        display: none;
    }

    .bulk-actions.show {
        display: block;
    }

    .invoice-checkbox {
        transform: scale(1.2);
        margin-right: 10px;
    }

    .status-badge {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }

    @media (max-width: 768px) {
        .invoice-card .number {
            font-size: 1.5rem;
        }

        .table-responsive {
            font-size: 0.85rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Invoice Management</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Invoice Management</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="invoice-card">
                <div class="number">{{ number_format($totalInvoices) }}</div>
                <div class="label">Total Invoices</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="invoice-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="number">৳{{ number_format($totalInvoiceAmount, 2) }}</div>
                <div class="label">Total Invoice Amount</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="invoice-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="number">{{ number_format($paidInvoices) }}</div>
                <div class="label">Paid Invoices</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="invoice-card" style="background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);">
                <div class="number">{{ number_format($unpaidInvoices) }}</div>
                <div class="label">Unpaid Invoices</div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('invoices.index') }}">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Invoice Search</label>
                        <input type="text" name="invoice_search" class="form-control"
                               value="{{ request('invoice_search') }}" placeholder="Search by invoice ID">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Vendor Filter</label>
                        <select name="vendor_filter" class="form-select">
                            <option value="">All Vendors</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}"
                                    {{ request('vendor_filter') == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->shop_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Place By</label>
                        <select name="place_by_filter" class="form-select">
                            <option value="">All Place By</option>
                            @foreach($placeBys as $placeBy)
                                <option value="{{ $placeBy->id }}"
                                    {{ request('place_by_filter') == $placeBy->id ? 'selected' : '' }}>
                                    {{ $placeBy->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                     <div class="col-md-2">
                                <label class="form-label">Payment Status</label>
                                <select name="payment_status_filter" class="form-select">
                                    <option value="" {{ request('payment_status_filter') === null || request('payment_status_filter') === '' ? 'selected' : '' }}>All Status</option>
                                    <option value="0" {{ request('payment_status_filter') == '0' ? 'selected' : '' }}>unpaid</option>
                                    <option value="1" {{ request('payment_status_filter') == '1' ? 'selected' : '' }}>partial paid</option>
                                    <option value="2" {{ request('payment_status_filter') == '2' ? 'selected' : '' }}>paid</option>
                                </select>
                    </div>




                    <div class="col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    {{-- <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="mdi mdi-magnify"></i> Filter
                            </button>
                        </div>
                    </div> --}}





                    <div class="col-12">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="mdi mdi-magnify"></i> Filter
                        </button>
                        <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
                            <i class="mdi mdi-refresh"></i> Reset
                        </a>

                    </div>
                </div>

            </form>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="bulk-actions" id="bulkActions">
        <div class="row align-items-center">
            <div class="col-md-6">
                <span id="selectedCount">0</span> invoice(s) selected
            </div>
            <div class="col-md-6">
                <div class="btn-group float-end">
                    <button type="button" class="btn btn-primary" onclick="downloadBulkInvoices()">
                        <i class="mdi mdi-download"></i> Download Selected
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="clearSelection()">
                        <i class="mdi mdi-close"></i> Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Generated Invoices</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table">
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>Invoice ID</th>
                            <th>Place By</th>
                            <th>Vendor</th>
                            <th>Order Status</th>
                            <th>Payment Status</th>
                            <th>Items</th>
                            <th>Amount</th>
                            <th>Paid</th>
                            <th>Due</th>

                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input invoice-checkbox"
                                           value="{{ $order->id }}" onchange="updateBulkSelection()">
                                </td>
                                <td>
                                    <strong>{{ $order->invoice_id }}</strong>
                                    <br><small class="text-muted">{{ $order->created_at->format('M d, Y h:i A') }}</small>
                                    <br><small class="text-muted">{{ $order->created_at->diffForHumans() }}</small>
                                </td>

                                <td>
                                    <strong>{{ $order->placeBy->name }}</strong>
                                </td>
                                <td>
                                    <strong>{{ $order->vendor->shop_name ?? 'N/A' }}</strong>
                                    <br><small class="text-muted">{{ $order->vendor->mobile ?? 'N/A' }}</small>
                                    <br><small class="text-danger">Total Due: ৳{{ number_format($order?->vendor?->due_balance, 2) ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <span class="badge {{ $order->status_badge_class }} status-badge">
                                        {{ $order->orderStatus->name ?? 'Unknown' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $order->payment_status_badge_class }} status-badge">
                                        {{ $order->payment_status_text }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $order->orderItems->count() }}</strong> products
                                    <br><small class="text-muted">{{ $order->total_quantity }} items</small>
                                </td>
                                <td>
                                    <strong class="text-primary">৳{{ number_format($order->total_amount, 2) }}</strong>
                                    @if($order->total_discount_amount > 0)
                                        <br><small class="text-warning">Discount: ৳{{ number_format($order->total_discount_amount, 2) }}</small>
                                    @endif
                                </td>
                                <td>
                                     <strong class="text-success">৳{{ number_format($order->order_payment, 2) }}</strong>
                                </td>
                                <td>
                                     <strong class="text-danger">৳{{ number_format($order->order_due, 2) }}</strong>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('invoices.preview', $order) }}" class="btn btn-outline-info"
                                           title="Preview Invoice" target="_blank">
                                            <i class="mdi mdi-eye"></i>
                                        </a>
                                        <a href="{{ route('invoices.generate', $order) }}" class="btn btn-outline-primary"
                                           title="Download PDF">
                                            <i class="mdi mdi-invoice"></i>
                                        </a>
                                        @can('payment_collection_list')
                                            @if($order->payment_status < 2)
                                                <a href="{{ route('payment-collections.create', ['vendor_id' => $order->vendor_id]) }}"
                                                class="btn btn-outline-success" title="Collect Payment">
                                                    <i class="mdi mdi-cash"></i>
                                                </a>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="mdi mdi-file-document-outline" style="font-size: 3rem; color: #ccc;"></i>
                                    <br>No invoices found
                                    <br><small class="text-muted">No shipped or delivered orders available for invoicing</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    <tfoot class="table-secondary">
                                <tr>
                                    <th colspan="7" class="text-end">Page Total:</th>
                                    <th class="text-primary">৳{{ number_format($pageTotalAmount, 2) }}</th>
                                    <th class="text-success">৳{{ number_format($pageTotalPaidAmount, 2) }}</th>
                                    <th class="text-danger">৳{{ number_format($pageTotalDueAmount, 2) }}</th>
                                    <th colspan="2"></th>
                                </tr>
                                <tr>
                                    <th colspan="7" class="text-end">Filtered Total:</th>
                                    <th class="text-primary">৳{{ number_format($filteredTotalAmount, 2) }}</th>
                                    <th class="text-success">৳{{ number_format($filteredTotalPaidAmount, 2) }}</th>
                                    <th class="text-danger">৳{{ number_format($filteredTotalDueAmount, 2) }}</th>
                                    <th colspan="2"></th>
                                </tr>
                    </tfoot>
                </table>
            </div>

            @if($orders->hasPages())
                <div class="mt-3">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('custome-js')
<script>
// Bulk selection functionality
let selectedInvoices = [];

function updateBulkSelection() {
    selectedInvoices = [];
    document.querySelectorAll('.invoice-checkbox:checked').forEach(checkbox => {
        selectedInvoices.push(checkbox.value);
    });

    document.getElementById('selectedCount').textContent = selectedInvoices.length;

    if (selectedInvoices.length > 0) {
        document.getElementById('bulkActions').classList.add('show');
    } else {
        document.getElementById('bulkActions').classList.remove('show');
    }

    // Update select all checkbox
    const totalCheckboxes = document.querySelectorAll('.invoice-checkbox').length;
    const selectAllCheckbox = document.getElementById('selectAll');

    if (selectedInvoices.length === 0) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = false;
    } else if (selectedInvoices.length === totalCheckboxes) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = true;
    } else {
        selectAllCheckbox.indeterminate = true;
    }
}

// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.invoice-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateBulkSelection();
});

function clearSelection() {
    document.querySelectorAll('.invoice-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    updateBulkSelection();
}

function downloadBulkInvoices() {
    if (selectedInvoices.length === 0) {
        alert('Please select invoices to download');
        return;
    }

    if (!confirm(`Are you sure you want to download ${selectedInvoices.length} invoice(s) as a ZIP file?`)) {
        return;
    }

    // Show loading
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Preparing...';
    button.disabled = true;

    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("invoices.bulk") }}';

    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    form.appendChild(csrfToken);

    selectedInvoices.forEach(invoiceId => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'order_ids[]';
        input.value = invoiceId;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();

    // Reset button after a delay
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
        document.body.removeChild(form);
    }, 3000);
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateBulkSelection();
});
</script>
@endpush
