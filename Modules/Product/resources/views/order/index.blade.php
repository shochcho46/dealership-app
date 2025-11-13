@extends('layouts.app')

@section('title', 'Orders')

@push('custome-css')
<style>
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

    .order-checkbox {
        transform: scale(1.2);
        margin-right: 10px;
    }

    .status-badge {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }

    .table-responsive {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .summary-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .summary-card .number {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .summary-card .label {
        font-size: 0.9rem;
        opacity: 0.9;
    }

    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.85rem;
        }

        .bulk-actions {
            padding: 10px;
        }

        .btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
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
                <h4 class="mb-sm-0">Orders Management</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Orders</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="summary-card">
                <div class="number">{{ number_format($totalOrders) }}</div>
                <div class="label">Total Orders</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="summary-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="number">৳{{ number_format($totalAmount, 2) }}</div>
                <div class="label">Total Amount</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="summary-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="number">{{ number_format($pendingOrders) }}</div>
                <div class="label">Pending Orders</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="summary-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="number">{{ number_format($completedOrders) }}</div>
                <div class="label">Completed Orders</div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('orders.index') }}">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Invoice Search</label>
                        <input type="text" name="invoice_search" class="form-control"
                               value="{{ request('invoice_search') }}" placeholder="Search by invoice ID">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status Filter</label>
                        <select name="status_filter" class="form-select">
                            <option value="">All Status</option>
                            @foreach($orderStatuses as $status)
                                <option value="{{ $status->id }}"
                                    {{ request('status_filter') == $status->id ? 'selected' : '' }}>
                                    {{ $status->name }}
                                </option>
                            @endforeach
                        </select>
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
                        <label class="form-label">Place By Filter</label>
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
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="mdi mdi-magnify"></i> Filter
                        </button>
                        <a href="{{ route('orders.index') }}" class="btn btn-secondary">
                            <i class="mdi mdi-refresh"></i> Reset
                        </a>
                        <a href="{{ route('orders.create') }}" class="btn btn-outline-success float-end mt-2">
                            <i class="mdi mdi-plus"></i> Create Order
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
                <span id="selectedCount">0</span> order(s) selected
            </div>
            <div class="col-md-6">
                <div class="btn-group float-end">
                    <select id="bulkStatusSelect" class="form-select" style="width: 200px;">
                        <option value="">Change Status To...</option>
                        @foreach($orderStatuses as $status)
                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-primary" onclick="updateBulkStatus()">
                        <i class="mdi mdi-check-all"></i> Update Status
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="clearSelection()">
                        <i class="mdi mdi-close"></i> Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>Invoice ID</th>
                            <th>Vendor</th>
                            <th>Place By</th>
                            <th>Status</th>
                            <th>Payment Status</th>
                            <th>Items</th>
                            <th>Total Amount</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input order-checkbox"
                                           value="{{ $order->id }}" onchange="updateBulkSelection()">
                                </td>
                                <td>
                                    <strong>{{ $order->invoice_id }}</strong>
                                    <br><small class="text-muted">{{ $order->created_at->format('M d, Y h:i A') }}</small>
                                </td>
                                <td>
                                    <strong>{{ $order->vendor->shop_name ?? 'N/A' }}</strong>
                                    <br><small class="text-muted">{{ $order->vendor->mobile ?? 'N/A' }}</small>
                                </td>

                                <td>
                                    <strong>{{ $order->placeBy->name ?? 'N/A' }}</strong>
                                    <br><small class="text-muted">{{ $order?->placeBy?->phone ?? 'N/A' }}</small>
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
                                    <strong>৳{{ number_format($order->total_amount, 2) }}</strong>
                                    @if($order->total_discount_amount > 0)
                                        <br><small class="text-success">Discount: ৳{{ number_format($order->total_discount_amount, 2) }}</small>
                                    @endif
                                </td>
                                <td>
                                    {{ $order->created_at->format('M d, Y') }}
                                    <br><small class="text-muted">{{ $order->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('orders.show', $order) }}" class="btn btn-outline-info" title="View">
                                            <i class="mdi mdi-eye"></i>
                                        </a>
                                        @if($order->canBeCancelled())
                                            <a href="{{ route('orders.edit', $order) }}" class="btn btn-outline-primary" title="Edit">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                        @endif
                                        @if(in_array($order->order_status_id, [4, 5])) <!-- Shipped or Delivered -->
                                            <button type="button" class="btn btn-outline-primary" title="Generate Invoice"
                                                    onclick="generateInvoice({{ $order->id }})">
                                                <i class="mdi mdi-invoice"></i>
                                            </button>
                                        @endif
                                        @if($order->canBeCancelled())
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-bs-toggle="modal" data-bs-target="#deleteModal" data-url="{{ route('orders.cancel', $order->id) }}">
                                                        <span class="mdi mdi-cancel"></span>
                                            </button>

                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="mdi mdi-cart-outline" style="font-size: 3rem; color: #ccc;"></i>
                                    <br>No orders found
                                </td>
                            </tr>
                        @endforelse
                        @include('components.delete')
                    </tbody>
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
let selectedOrders = [];

function updateBulkSelection() {
    selectedOrders = [];
    document.querySelectorAll('.order-checkbox:checked').forEach(checkbox => {
        selectedOrders.push(checkbox.value);
    });

    document.getElementById('selectedCount').textContent = selectedOrders.length;

    if (selectedOrders.length > 0) {
        document.getElementById('bulkActions').classList.add('show');
    } else {
        document.getElementById('bulkActions').classList.remove('show');
    }

    // Update select all checkbox
    const totalCheckboxes = document.querySelectorAll('.order-checkbox').length;
    const selectAllCheckbox = document.getElementById('selectAll');

    if (selectedOrders.length === 0) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = false;
    } else if (selectedOrders.length === totalCheckboxes) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = true;
    } else {
        selectAllCheckbox.indeterminate = true;
    }
}

// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.order-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateBulkSelection();
});

function clearSelection() {
    document.querySelectorAll('.order-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    updateBulkSelection();
}

function updateBulkStatus() {
    const statusId = document.getElementById('bulkStatusSelect').value;

    if (!statusId) {
        alert('Please select a status');
        return;
    }

    if (selectedOrders.length === 0) {
        alert('Please select orders to update');
        return;
    }

    // if (!confirm(`Are you sure you want to update ${selectedOrders.length} order(s) status?`)) {
    //     return;
    // }

    // Show loading
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Updating...';
    button.disabled = true;

    fetch('{{ route("orders.bulkUpdateStatus") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            order_ids: selectedOrders,
            status_id: statusId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            // alert(data.message);
             toastr.success(data.message, '', { timeOut: 1500 });
                setTimeout(() => {
                    location.reload();
                }, 1600);
        } else {
            alert(data.error || 'An error occurred');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating orders');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function cancelOrder(orderId) {
    if (!confirm('Are you sure you want to cancel this order?')) {
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/order/${orderId}/cancel`;

    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    form.appendChild(csrfToken);
    document.body.appendChild(form);
    form.submit();
}

function generateInvoice(orderId) {
    window.open(`/admin/invoice/${orderId}/generate`, '_blank');
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateBulkSelection();
});
</script>
@endpush
