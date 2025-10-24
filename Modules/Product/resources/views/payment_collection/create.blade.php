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
    max-height: 300px;
    overflow-y: auto;
    width: 100%;
    z-index: 1000;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.vendor-item {
    padding: 10px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
}

.vendor-item:hover {
    background-color: #f8f9fa;
}

.vendor-item:last-child {
    border-bottom: none;
}

.vendor-name {
    font-weight: bold;
    color: #333;
}

.vendor-mobile {
    color: #666;
    font-size: 0.9em;
}
</style>
@endpush

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Collect Payment</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('payment-collections.index') }}">Payment Collection</a></li>
                    <li class="breadcrumb-item active">Collect Payment</li>
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
                    <h1 class="mt-3">Collect Payment</h1>
                    <div class="text-end">
                        <a href="{{ route('payment-collections.index') }}" class="btn btn-outline-primary">
                            <span class="mdi mdi-format-list-text"></span> View All Collections
                        </a>
                    </div>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card card-primary card-outline mb-4">
                    <div class="card-header">
                        <div class="card-title">Payment Collection Form</div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('payment-collections.store') }}" method="POST" id="paymentCollectionForm" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="row">
                                <!-- Vendor Selection -->
                                <div class="col-md-6">
                                    <div class="mb-3 vendor-search-wrapper">
                                        <label for="vendor_search" class="form-label">Vendor <span class="text-danger">*</span></label>
                                        <input type="text" id="vendor_search" class="form-control" placeholder="Search vendor by name or mobile..." autocomplete="off">
                                        <input type="hidden" name="vendor_id" id="vendor_id" value="{{ request('vendor_id') }}">
                                        <div id="vendor_results" class="vendor-results"></div>
                                        @error('vendor_id')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Payment Method -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="payment_method_id" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                        <select name="payment_method_id" id="payment_method_id" class="form-select" required>
                                            <option value="">Select Payment Method</option>
                                            @foreach($paymentMethods as $method)
                                                <option value="{{ $method->id }}">
                                                    {{ $method->name }}
                                                    @if($method->account_name)
                                                        - {{ $method->account_name }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('payment_method_id')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Vendor Info Display -->
                            <div id="vendor_info" class="alert alert-info mb-3" style="display: none;">
                                <h6>Vendor Information:</h6>
                                <div id="vendor_details"></div>
                            </div>

                            <!-- Pending Orders Display -->
                            <div id="pending_orders" class="mb-4" style="display: none;">
                                <h6>Pending Orders:</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Select</th>
                                                <th>Invoice</th>
                                                <th>Date</th>
                                                <th>Total Amount</th>
                                                <th>Paid Amount</th>
                                                <th>Due Amount</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="orders_list">
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Amount -->
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                        <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="0" value="{{ old('amount') }}" placeholder="Enter amount">
                                        @error('amount')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Collection Date -->
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="collection_date" class="form-label">Collection Date <span class="text-danger">*</span></label>
                                        <input type="date" name="collection_date" id="collection_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                        @error('collection_date')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Deposited By -->
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="deposite_by" class="form-label">Deposited By <span class="text-danger">*</span></label>
                                        <select name="deposite_by" id="deposite_by" class="form-select" required>
                                            <option value="">Select Admin</option>
                                            @foreach($admins as $admin)
                                                <option value="{{ $admin->id }}" {{ old('deposite_by') == $admin->id ? 'selected' : '' }}>
                                                    {{ $admin->name }} - {{ $admin->email }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('deposite_by')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Note -->
                            <div class="mb-3">
                                <label for="note" class="form-label">Note</label>
                                <textarea name="note" id="note" class="form-control" rows="3" placeholder="Optional note about the payment...">{{ old('note') }}</textarea>
                                @error('note')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Payment Document Upload -->
                            <div class="mb-3">
                                <label for="payment_document" class="form-label">Payment Document <span class="text-muted">(Optional)</span></label>
                                <input type="file" name="payment_document" id="payment_document" class="form-control" accept="image/*,.pdf">
                                <small class="text-muted">Upload payment receipt, invoice or any supporting document (Max: 5MB, Formats: JPG, PNG, PDF)</small>
                                @error('payment_document')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <div id="document_preview" class="mt-2"></div>
                            </div>

                            <!-- Selected Orders (Hidden) -->
                            <div id="selected_orders_input"></div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="mdi mdi-check"></i> Collect Payment
                                </button>
                                <a href="{{ route('payment-collections.index') }}" class="btn btn-secondary">
                                    <i class="mdi mdi-arrow-left"></i> Back to List
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('custome-js')
<script>
$(document).ready(function() {
    let searchTimeout;

    // Auto-select vendor if vendor_id is provided in URL
    @if(request('vendor_id'))
        $.ajax({
            url: "{{ route('admin.vendors.search') }}",
            data: { id: '{{ request('vendor_id') }}' },
            success: function(response) {
                if (response && response.length > 0) {
                    selectVendor(response[0]);
                }
            }
        });
    @endif

    // Vendor search functionality
    $('#vendor_search').on('input', function() {
        const query = $(this).val().trim();
        clearTimeout(searchTimeout);

        if (query.length < 2) {
            $('#vendor_results').empty().hide();
            return;
        }

        searchTimeout = setTimeout(function() {
            $.ajax({
                url: "{{ route('admin.vendors.search') }}",
                data: { q: query },
                success: function(vendors) {
                    displayVendorResults(vendors);
                },
                error: function(xhr) {
                    console.error('Vendor search error:', xhr);
                    $('#vendor_results').html('<div class="vendor-item text-danger">Error searching vendors</div>').show();
                }
            });
        }, 300);
    });

    function displayVendorResults(vendors) {
        let html = '';

        if (!Array.isArray(vendors) || vendors.length === 0) {
            html = '<div class="vendor-item text-muted">No vendors found</div>';
        } else {
            vendors.forEach(function(vendor) {
                html += `
                    <div class="vendor-item" data-vendor='${JSON.stringify(vendor)}'>
                        <div class="vendor-name">${vendor.shop_name || ''}</div>
                        <div class="vendor-mobile">Mobile: ${vendor.mobile || ''} | Contact: ${vendor.contact_person || ''}</div>
                        <div class="vendor-address">Address: ${vendor.full_address || ''}</div>
                    </div>
                `;
            });
        }

        $('#vendor_results').html(html).show();
    }

    // Handle vendor selection
    $(document).on('click', '.vendor-item[data-vendor]', function() {
        const vendor = JSON.parse($(this).attr('data-vendor'));
        selectVendor(vendor);
    });

    function selectVendor(vendor) {
        $('#vendor_search').val(vendor.shop_name || '');
        $('#vendor_id').val(vendor.id || '');
        $('#vendor_results').empty().hide();

        // Display vendor info
        const vendorInfo = `
            <strong>Shop:</strong> ${vendor.shop_name || ''}<br>
            <strong>Mobile:</strong> ${vendor.mobile || 'N/A'}<br>
            <strong>Address:</strong> ${vendor.full_address || vendor.full_address || 'N/A'}
        `;
        $('#vendor_details').html(vendorInfo);
        $('#vendor_info').show();

        // Load pending orders
        if (vendor.id) {
            loadPendingOrders(vendor.id);
        }
    }

    function loadPendingOrders(vendorId) {
        $.ajax({
            url: "{{ route('admin.vendors.pending-orders') }}",
            data: { vendor_id: vendorId },
            success: function(orders) {
                displayPendingOrders(orders);
            },
            error: function(xhr) {
                console.error('Error loading pending orders:', xhr);
                $('#pending_orders').hide();
            }
        });
    }

    function displayPendingOrders(orders) {
        if (!Array.isArray(orders) || orders.length === 0) {
            $('#pending_orders').hide();
            return;
        }

        let html = '';
        orders.forEach(function(order) {
            const total = parseFloat(order.total_amount || 0);
            const paid = parseFloat(order.paid_amount || 0);
            const dueAmount = total - paid;
            const statusBadge = order.payment_status == 0 ? 'bg-danger' : 'bg-warning';
            const statusText = order.payment_status == 0 ? 'Unpaid' : 'Partially Paid';
            const createdAt = order.created_at ? new Date(order.created_at).toLocaleDateString() : '';

            html += `
                <tr>
                    <td>
                        <input type="checkbox" class="order-checkbox" data-order-id="${order.id}" data-due-amount="${dueAmount}">
                    </td>
                    <td>${order.invoice_id || ''}</td>
                    <td>${createdAt}</td>
                    <td>৳${total.toFixed(2)}</td>
                    <td>৳${paid.toFixed(2)}</td>
                    <td>৳${dueAmount.toFixed(2)}</td>
                    <td><span class="badge ${statusBadge}">${statusText}</span></td>
                </tr>
            `;
        });

        $('#orders_list').html(html);
        $('#pending_orders').show();
    }

    // Handle order selection for amount calculation
    $(document).on('change', '.order-checkbox', function() {
        updateSelectedOrders();
        calculateTotalDue();
    });

    function updateSelectedOrders() {
        let selectedOrders = [];
        $('.order-checkbox:checked').each(function() {
            selectedOrders.push($(this).data('order-id'));
        });

        let html = '';
        selectedOrders.forEach(function(orderId) {
            html += `<input type="hidden" name="order_ids[]" value="${orderId}">`;
        });
        $('#selected_orders_input').html(html);
    }

    function calculateTotalDue() {
        let totalDue = 0;
        $('.order-checkbox:checked').each(function() {
            totalDue += parseFloat($(this).data('due-amount') || 0);
        });

        if (totalDue > 0) {
            $('#amount').val(totalDue.toFixed(2));
        }
    }

    // Hide vendor results when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#vendor_search, #vendor_results').length) {
            $('#vendor_results').hide();
        }
    });

    // Form validation
    $('#paymentCollectionForm').on('submit', function(e) {
        if (!$('#vendor_id').val()) {
            e.preventDefault();
            alert('Please select a vendor');
            $('#vendor_search').focus();
            return false;
        }

        if (!$('#payment_method_id').val()) {
            e.preventDefault();
            alert('Please select a payment method');
            $('#payment_method_id').focus();
            return false;
        }

        if (!$('#amount').val() || parseFloat($('#amount').val()) <= 0) {
            e.preventDefault();
            alert('Please enter a valid amount');
            $('#amount').focus();
            return false;
        }
    });

    // Document preview
    $('#payment_document').on('change', function(e) {
        const file = e.target.files[0];
        const preview = $('#document_preview');
        preview.empty();

        if (file) {
            const fileType = file.type;
            const fileName = file.name;
            const fileSize = (file.size / 1024 / 1024).toFixed(2); // Convert to MB

            if (fileType.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.html(`
                        <div class="alert alert-info">
                            <img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px;" class="img-thumbnail">
                            <p class="mb-0 mt-2"><strong>${fileName}</strong> (${fileSize} MB)</p>
                        </div>
                    `);
                };
                reader.readAsDataURL(file);
            } else if (fileType === 'application/pdf') {
                preview.html(`
                    <div class="alert alert-info">
                        <i class="mdi mdi-file-pdf fa-3x text-danger"></i>
                        <p class="mb-0"><strong>${fileName}</strong> (${fileSize} MB)</p>
                    </div>
                `);
            }
        }
    });
});
</script>
@endpush