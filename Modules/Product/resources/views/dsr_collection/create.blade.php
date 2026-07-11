@extends('layouts.app')

@push('custome-css')
<style>
.vendor-search-wrapper { position: relative; }
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
.vendor-item { padding: 10px; border-bottom: 1px solid #eee; cursor: pointer; }
.vendor-item:hover { background-color: #f8f9fa; }
.vendor-item:last-child { border-bottom: none; }
.vendor-name { font-weight: bold; color: #333; }
.vendor-mobile { color: #666; font-size: 0.9em; }
.vendor-address { color: #666; font-size: 0.9em; }
.vendor-due { color: #d9534f; font-weight: bold; }
</style>
@endpush

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Add Vendor Collection</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('dsr-collections.index') }}">Vendor Collection</a></li>
                    <li class="breadcrumb-item active">Add Collection</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="{{ route('dsr-collections.index') }}" class="btn btn-outline-primary">
                        <span class="mdi mdi-format-list-text"></span> View All Collections
                    </a>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <div class="card-title">Vendor Collection Form</div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('dsr-collections.store') }}" method="POST" id="dsrCollectionForm">
                            @csrf

                            <div class="row">
                                <!-- Vendor Search -->
                                <div class="col-md-6">
                                    <div class="mb-3 vendor-search-wrapper">
                                        <label class="form-label">Vendor <span class="text-danger">*</span></label>
                                        <input type="text" id="vendor_search" class="form-control"
                                            placeholder="Search vendor by name or mobile..."
                                            autocomplete="off">
                                        <input type="hidden" name="vendor_id" id="vendor_id" value="{{ old('vendor_id') }}">
                                        <div id="vendor_results" class="vendor-results"></div>
                                        @error('vendor_id')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Payment Method -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                        <select name="payment_method_id" id="payment_method_id" class="form-select" required>
                                            <option value="">Select Payment Method</option>
                                            @foreach($paymentMethods as $method)
                                                <option value="{{ $method->id }}" {{ old('payment_method_id') == $method->id ? 'selected' : '' }}>
                                                    {{ $method->name }}
                                                    @if($method->account_name) — {{ $method->account_name }} @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('payment_method_id')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Vendor info panel (shown after selection) -->
                            <div id="vendor_info" class="alert alert-info mb-3" style="display:none;">
                                <h6 class="mb-1">Selected Vendor:</h6>
                                <div id="vendor_details"></div>
                            </div>

                            <div class="row">
                                <!-- Amount -->
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                                        <input type="number" name="amount" id="amount" class="form-control"
                                            step="0.01" min="0.01" value="{{ old('amount') }}"
                                            placeholder="0.00" required>
                                        @error('amount')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Collection Date -->
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Collection Date <span class="text-danger">*</span></label>
                                        @if($canEditDate)
                                            <input type="date" name="collection_date" id="collection_date" class="form-control"
                                                value="{{ old('collection_date', date('Y-m-d')) }}" required>
                                        @else
                                            <input type="date" name="collection_date" id="collection_date" class="form-control"
                                                value="{{ date('Y-m-d') }}" readonly>
                                        @endif
                                        @error('collection_date')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Deposited By (auto-filled from logged-in admin) -->
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Deposited By</label>
                                        <input type="hidden" name="deposite_by" value="{{ $currentAdmin->id }}">
                                        <input type="text" class="form-control bg-light" value="{{ $currentAdmin->name }}" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Note -->
                            <div class="mb-3">
                                <label class="form-label">Note <span class="text-muted">(Optional)</span></label>
                                <textarea name="note" id="note" class="form-control" rows="3"
                                    placeholder="Optional remarks about this collection...">{{ old('note') }}</textarea>
                                @error('note')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="mdi mdi-check"></i> Save Collection
                                </button>
                                <a href="{{ route('dsr-collections.index') }}" class="btn btn-secondary">
                                    <i class="mdi mdi-arrow-left"></i> Cancel
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
$(document).ready(function () {
    let searchTimeout;

    $('#vendor_search').on('input', function () {
        const q = $(this).val().trim();
        clearTimeout(searchTimeout);

        if (q.length < 2) {
            $('#vendor_results').empty().hide();
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
                            html += `<div class="vendor-item" data-vendor='${JSON.stringify(v)}'>
                                        <div class="vendor-name">${v.shop_name || ''}</div>
                                        <div class="vendor-mobile">Mobile: ${v.mobile || ''} | Contact: ${v.contact_person || ''}</div>
                                        <div class="vendor-address">Address: ${v.full_address || '-'}</div>
                                        <div class="vendor-due">Total Due: ৳${v.due_balance || '0.00'}</div>
                                     </div>`;
                        });
                    }
                    $('#vendor_results').html(html).show();
                },
                error: function () {
                    $('#vendor_results').html('<div class="vendor-item text-danger">Error searching vendors</div>').show();
                }
            });
        }, 300);
    });

    $(document).on('click', '.vendor-item[data-vendor]', function () {
        const vendor = JSON.parse($(this).attr('data-vendor'));
        $('#vendor_search').val(vendor.shop_name || '');
        $('#vendor_id').val(vendor.id || '');
        $('#vendor_results').empty().hide();

        $('#vendor_details').html(
            `<strong>Shop:</strong> ${vendor.shop_name || ''}<br>
             <strong>Mobile:</strong> ${vendor.mobile || 'N/A'}<br>
             <strong>Address:</strong> ${vendor.full_address || 'N/A'}<br>
             <strong class="vendor-due">Total Due: ৳${vendor.due_balance || '0.00'}</strong>`
        );
        $('#vendor_info').show();
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('#vendor_search, #vendor_results').length) {
            $('#vendor_results').hide();
        }
    });

    $('#dsrCollectionForm').on('submit', function (e) {
        if (!$('#vendor_id').val()) {
            e.preventDefault();
            alert('Please select a vendor.');
            $('#vendor_search').focus();
            return false;
        }
        if (!$('#payment_method_id').val()) {
            e.preventDefault();
            alert('Please select a payment method.');
            $('#payment_method_id').focus();
            return false;
        }
        const amt = parseFloat($('#amount').val());
        if (!amt || amt <= 0) {
            e.preventDefault();
            alert('Please enter a valid amount.');
            $('#amount').focus();
            return false;
        }
    });
});
</script>
@endpush
