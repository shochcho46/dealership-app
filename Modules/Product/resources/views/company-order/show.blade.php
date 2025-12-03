@extends('layouts.app')

@push('custome-css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .badge-paid { background-color: #28a745; color: white; }
    .badge-partial { background-color: #ffc107; color: black; }
    .badge-unpaid { background-color: #dc3545; color: white; }
</style>
@endpush

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Company Order Details</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.companyOrderIndex') }}">Orders</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Details</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
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

                <!-- Order Header -->
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Order {{ $companyOrder->order_number }}</h4>
                        <div>
                            <span class="badge badge-{{ $companyOrder->payment_status }} fs-6 me-2">
                                {{ ucfirst($companyOrder->payment_status) }}
                            </span>
                            <span class="badge bg-{{ $companyOrder->status === 'received' ? 'success' : 'warning' }} fs-6">
                                {{ ucfirst($companyOrder->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Company:</strong> {{ $companyOrder->company->name ?? 'N/A' }}</p>
                                <p><strong>Order Date:</strong> {{ $companyOrder->created_at->format('d M Y') }}</p>
                                <p><strong>Status:</strong>
                                    <form action="{{ route('admin.companyOrderUpdateStatus', $companyOrder) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                            <option value="pending" {{ $companyOrder->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="received" {{ $companyOrder->status === 'received' ? 'selected' : '' }}>Received</option>
                                        </select>
                                    </form>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Total Amount:</strong> ৳{{ number_format($companyOrder->total_amount, 2) }}</p>
                                <p><strong>Paid Amount:</strong> ৳{{ number_format($companyOrder->paid_amount, 2) }}</p>
                                <p><strong>Due Amount:</strong> ৳{{ number_format($companyOrder->total_amount - $companyOrder->paid_amount, 2) }}</p>
                            </div>
                            @if($companyOrder->notes)
                            <div class="col-12 mt-2">
                                <p><strong>Notes:</strong> {{ $companyOrder->notes }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Order Items</h4>
                        <div>
                            <a href="{{ route('admin.companyOrderPdf', $companyOrder) }}" target="_blank" class="btn btn-sm btn-danger">
                                <span class="mdi mdi-file-pdf"></span> View PDF
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-sm" id="orderItemsTable">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>M. Unit</th>
                                    <th>P. Unit</th>
                                    <th>Qty</th>
                                    <th>Damage</th>
                                    <th>Lost</th>
                                    <th>Price</th>
                                    <th>Dmg Price</th>
                                    <th>Lost Price</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($companyOrder->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td>{{ $item->measurement_unit }}</td>
                                    <td>{{ $item->package_unit }}</td>
                                    <td>{{ number_format($item->quantity, 2) }}</td>
                                    <td class="text-danger">{{ $item->damage_quantity }}</td>
                                    <td class="text-danger">{{ $item->lost_quantity }}</td>
                                    <td>৳{{ number_format($item->price, 2) }}</td>
                                    <td class="text-danger">৳{{ number_format($item->damage_price, 2) }}</td>
                                    <td class="text-danger">৳{{ number_format($item->lost_price, 2) }}</td>
                                    <td><strong>৳{{ number_format($item->total_price, 2) }}</strong></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#damageLostModal{{ $item->id }}">
                                            <i class="mdi mdi-pencil"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="9" class="text-end">Grand Total:</th>
                                    <th colspan="2"><strong>৳{{ number_format($companyOrder->total_amount, 2) }}</strong></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Payment History -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h4>Payment History</h4>
                    </div>
                    <div class="card-body">
                        @if($companyOrder->payments->count() > 0)
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Payment Method</th>
                                    <th>Amount</th>
                                    <th>Notes</th>
                                    <th>Payment Slip</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($companyOrder->payments as $payment)
                                <tr>
                                    <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                    <td>{{ $payment->paymentMethod->account_name ?? 'N/A' }}</td>
                                    <td>৳{{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ $payment->notes ?? '-' }}</td>
                                    <td>
                                        @if($payment->hasMedia('payment_slip'))
                                            @php
                                                $media = $payment->getFirstMedia('payment_slip');
                                                $extension = $media->extension;
                                            @endphp
                                            @if(in_array($extension, ['jpg', 'jpeg', 'png']))
                                                <a href="{{ $media->getUrl() }}" target="_blank">
                                                    <img src="{{ $media->getUrl() }}" alt="Payment Slip" style="width: 50px; height: 50px; object-fit: cover;">
                                                </a>
                                            @elseif($extension == 'pdf')
                                                <a href="{{ $media->getUrl() }}" target="_blank" class="btn btn-sm btn-info">
                                                    <i class="mdi mdi-file-pdf"></i> View PDF
                                                </a>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $payment->creator->name ?? 'N/A' }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editPaymentModal{{ $payment->id }}">
                                            <span class="mdi mdi-pencil"></span>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deletePaymentModal{{ $payment->id }}">
                                            <span class="mdi mdi-delete"></span>
                                        </button>

                                        <!-- Edit Payment Modal -->
                                        <div class="modal fade" id="editPaymentModal{{ $payment->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('admin.companyOrderUpdatePayment', [$companyOrder, $payment]) }}" method="POST" enctype="multipart/form-data">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Payment</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                                                <select name="payment_method_id" class="form-control" required>
                                                                    @foreach($paymentMethods as $method)
                                                                        <option value="{{ $method->id }}" {{ $payment->payment_method_id == $method->id ? 'selected' : '' }}>
                                                                            {{ $method->account_name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                                                <input type="number" name="amount" class="form-control" value="{{ $payment->amount }}" step="0.01" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Payment Date</label>
                                                                <input type="date" name="payment_date" class="form-control" value="{{ $payment->payment_date->format('Y-m-d') }}">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Notes</label>
                                                                <textarea name="notes" class="form-control" rows="2">{{ $payment->notes }}</textarea>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Payment Slip (Image/PDF)</label>
                                                                <input type="file" name="payment_slip" class="form-control" accept="image/jpeg,image/png,image/jpg,application/pdf">
                                                                @if($payment->hasMedia('payment_slip'))
                                                                    <small class="text-muted">Current file exists. Upload new file to replace.</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">Update Payment</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Delete Payment Modal -->
                                        <div class="modal fade" id="deletePaymentModal{{ $payment->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Confirm Delete</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Are you sure you want to delete this payment of ৳{{ number_format($payment->amount, 2) }}?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <form action="{{ route('admin.companyOrderDeletePayment', [$companyOrder, $payment]) }}" method="POST" class="d-inline">
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
                                @endforeach
                            </tbody>
                        </table>
                        @else
                        <p class="text-muted">No payments yet.</p>
                        @endif
                    </div>
                </div>

                <!-- Add Payment Form -->
                @if($companyOrder->payment_status != 'paid')
                <div class="card">
                    <div class="card-header">
                        <h4>Add Payment</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.companyOrderAddPayment', $companyOrder) }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="payment_method_id" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                    <select name="payment_method_id" id="payment_method_id" class="form-control select2" required>
                                        <option value="">Select Payment Method</option>
                                        @foreach($paymentMethods as $method)
                                            <option value="{{ $method->id }}">{{ $method->account_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('payment_method_id')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                    <input type="number" name="amount" id="amount" class="form-control"
                                           step="0.01" min="0.01" max="{{ $companyOrder->total_amount - $companyOrder->paid_amount }}" required>
                                    <small class="text-muted">Remaining: ৳{{ number_format($companyOrder->total_amount - $companyOrder->paid_amount, 2) }}</small>
                                    @error('amount')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="payment_date" class="form-label">Payment Date</label>
                                    <input type="date" name="payment_date" id="payment_date" class="form-control"
                                           value="{{ date('Y-m-d') }}">
                                    @error('payment_date')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="payment_slip" class="form-label">Payment Slip (Image/PDF)</label>
                                    <input type="file" name="payment_slip" id="payment_slip" class="form-control"
                                           accept="image/jpeg,image/png,image/jpg,application/pdf">
                                    @error('payment_slip')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="1"></textarea>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-success">Add Payment</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                <div class="mt-3">
                    <a href="{{ route('admin.companyOrderIndex') }}" class="btn btn-secondary">Back to List</a>
                    <a href="{{ route('admin.companyOrderEdit', $companyOrder) }}" class="btn btn-primary">Edit Order</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Damage/Lost Modals -->
@foreach($companyOrder->items as $item)
<div class="modal fade" id="damageLostModal{{ $item->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.companyOrderItemDamageLost', [$companyOrder, $item]) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Update Damage/Lost - {{ $item->product_name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mb-2">
                            <p><strong>Total Quantity:</strong> {{ number_format($item->quantity, 2) }}</p>
                            <p><strong>Unit Price:</strong> ৳{{ number_format($item->price, 2) }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Damage Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="damage_quantity" class="form-control"
                                   value="{{ $item->damage_quantity }}" min="0" max="{{ $item->quantity }}" required>
                            <small class="text-muted">Max: {{ number_format($item->quantity, 2) }}</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lost Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="lost_quantity" class="form-control"
                                   value="{{ $item->lost_quantity }}" min="0" max="{{ $item->quantity }}" required>
                            <small class="text-muted">Max: {{ number_format($item->quantity, 2) }}</small>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-info">
                                <small>
                                    <strong>Note:</strong> Damage and lost quantities will be subtracted from total quantity.<br>
                                    Effective Quantity = Total Qty - Damage - Lost<br>
                                    Final Price = (Effective Qty × Price) + (Damage × Price) + (Lost × Price)
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection

@push('custome-js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2').select2();
});
</script>
@endpush
