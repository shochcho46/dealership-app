@extends('layouts.app')

@push('custome-css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .product-item {
        border: 1px solid #dee2e6;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 5px;
        background-color: #f8f9fa;
    }
    .remove-item {
        cursor: pointer;
    }
</style>
@endpush

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Create Company Order</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.companyOrderIndex') }}">Orders</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <h4>Order Details</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.companyOrderStore') }}" method="POST">
                            @csrf

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="company_id" class="form-label">Company <span class="text-danger">*</span></label>
                                    <select name="company_id" id="company_id" class="form-control select2" required>
                                        <option value="">Select Company</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                                {{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('company_id')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                                </div>
                            </div>

                            <hr>
                            <h5>Order Items</h5>
                            <div id="items-container">
                                <!-- Items will be added here dynamically -->
                            </div>

                            <button type="button" class="btn btn-secondary mb-3" id="add-item-btn" disabled>
                                <span class="mdi mdi-plus"></span> Add Product
                            </button>

                            <hr>
                            <div class="text-end">
                                <h4>Total Amount: ৳<span id="total-amount">0.00</span></h4>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <a href="{{ route('admin.companyOrderIndex') }}" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Create Order</button>
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    let itemIndex = 0;
    let products = [];

    // Initialize Select2
    $('.select2').select2();

    // Load products when company is selected
    $('#company_id').change(function() {
        const companyId = $(this).val();
        if (companyId) {
            $.ajax({
                url: `/admin/company-order/products/${companyId}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        products = response.products;
                        $('#add-item-btn').prop('disabled', false);
                    }
                },
                error: function() {
                    alert('Failed to load products');
                }
            });
        } else {
            products = [];
            $('#add-item-btn').prop('disabled', true);
            $('#items-container').html('');
        }
    });

    // Add item
    $('#add-item-btn').click(function() {
        if (products.length === 0) {
            alert('Please select a company first');
            return;
        }

        let productOptions = '<option value="">Select Product</option>';
        products.forEach(product => {
            productOptions += `<option value="${product.id}"
                data-measurement="${product.measurement_unit_name} (${product.measurement_unit_number})"
                data-package="${product.package_unit_name} (${product.package_unit_quantity})">
                ${product.name}
            </option>`;
        });

        const itemHtml = `
            <div class="product-item" data-index="${itemIndex}">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Product <span class="text-danger">*</span></label>
                        <select name="items[${itemIndex}][product_id]" class="form-control product-select" required>
                            ${productOptions}
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Measurement Unit</label>
                        <input type="text" class="form-control measurement-unit" readonly>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Package Unit</label>
                        <input type="text" class="form-control package-unit" readonly>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity" step="0.01" min="0.01" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Price <span class="text-danger">*</span></label>
                        <input type="number" name="items[${itemIndex}][price]" class="form-control price" step="0.01" min="0" required>
                    </div>
                </div>
                <div class="text-end">
                    <button type="button" class="btn btn-sm btn-danger remove-item">
                        <span class="mdi mdi-delete"></span> Remove
                    </button>
                </div>
            </div>
        `;

        $('#items-container').append(itemHtml);
        itemIndex++;
    });

    // Product selection - show details
    $(document).on('change', '.product-select', function() {
        const selected = $(this).find(':selected');
        const item = $(this).closest('.product-item');

        item.find('.measurement-unit').val(selected.data('measurement') || '');
        item.find('.package-unit').val(selected.data('package') || '');
    });

    // Calculate total when quantity or price changes
    $(document).on('input', '.quantity, .price', function() {
        calculateTotal();
    });

    // Remove item
    $(document).on('click', '.remove-item', function() {
        $(this).closest('.product-item').remove();
        calculateTotal();
    });

    function calculateTotal() {
        let total = 0;
        $('.product-item').each(function() {
            const quantity = parseFloat($(this).find('.quantity').val()) || 0;
            const price = parseFloat($(this).find('.price').val()) || 0;
            total += quantity * price;
        });
        $('#total-amount').text(total.toFixed(2));
    }
});
</script>
@endpush
