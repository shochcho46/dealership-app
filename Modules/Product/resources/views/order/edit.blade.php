@extends('layouts.app')

@push('custome-css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.6.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />

<style>
    /* Mobile-first responsive design */
    .order-container {
        padding: 10px;
    }

    @media (min-width: 768px) {
        .order-container {
            padding: 20px;
        }
    }

    .vendor-search-box {
        position: relative;
        margin-bottom: 20px;
    }

    .vendor-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-radius: 5px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
    }

    .vendor-option {
        padding: 10px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .vendor-option:hover {
        background-color: #f8f9fa;
    }

    .vendor-option:last-child {
        border-bottom: none;
    }

    .vendor-info {
        font-size: 14px;
        color: #666;
        margin-top: 5px;
    }

    .order-item-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        background-color: #f8f9fa;
        position: relative;
    }

    @media (max-width: 767px) {
        .order-item-card {
            padding: 10px;
        }
    }

    .remove-item-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        font-size: 14px;
        cursor: pointer;
         z-index: 100;
    }

    .product-search-box {
        position: relative;
        margin-bottom: 15px;
    }

    .product-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-radius: 5px;
        max-height: 150px;
        overflow-y: auto;
        z-index: 999;
        display: none;
    }

    .product-option {
        padding: 10px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .product-option:hover {
        background-color: #f8f9fa;
    }

    .product-image {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 5px;
        border: 1px solid #ddd;
    }

    .product-info {
        flex: 1;
    }

    .product-name {
        font-weight: 500;
        color: #333;
    }

    .product-details {
        font-size: 12px;
        color: #666;
        margin-top: 2px;
    }

    .quantity-controls {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .quantity-btn {
        background: #007bff;
        color: white;
        border: none;
        border-radius: 5px;
        width: 35px;
        height: 35px;
        font-size: 18px;
        cursor: pointer;
    }

    .quantity-input {
        width: 80px;
        text-align: center;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 8px;
    }

    .form-row {
        margin-bottom: 15px;
    }

    .form-row label {
        display: block;
        font-weight: 500;
        margin-bottom: 5px;
        font-size: 14px;
    }

    .form-row input, .form-row select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 16px; /* Prevent zoom on iOS */
    }

    .order-summary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
        position: sticky;
        bottom: 0;
        z-index: 100;
    }

    @media (max-width: 767px) {
        .order-summary {
            padding: 15px;
            margin: 15px -10px 0;
            border-radius: 0;
        }
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .summary-row:last-child {
        margin-bottom: 0;
        font-weight: bold;
        font-size: 18px;
        border-top: 1px solid rgba(255,255,255,0.3);
        padding-top: 10px;
    }

    .add-item-btn {
        background: #28a745;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 12px 20px;
        font-size: 16px;
        cursor: pointer;
        width: 100%;
        margin-bottom: 20px;
    }

    .submit-order-btn {
        background: #007bff;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 15px 30px;
        font-size: 18px;
        cursor: pointer;
        width: 100%;
    }

    .loading {
        opacity: 0.7;
        pointer-events: none;
    }

    .error-message {
        color: #dc3545;
        font-size: 14px;
        margin-top: 5px;
    }

    .available-stock-info {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }

    @media (max-width: 576px) {
        .form-row {
            margin-bottom: 10px;
        }

        .quantity-controls {
            gap: 5px;
        }

        .quantity-btn {
            width: 30px;
            height: 30px;
            font-size: 16px;
        }

        .quantity-input {
            width: 60px;
            padding: 6px;
        }
    }
</style>
@endpush

@section('content')
<div class="order-container">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Edit Order - {{ $order->invoice_id }}</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('orders.index') }}">Orders</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <form id="orderForm" action="{{ route('orders.update', $order) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Vendor Selection -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">Vendor Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="vendor-search-box">
                            <label for="vendor_search">Search Vendor by Name or Mobile</label>
                            <input type="text" id="vendor_search" class="form-control"
                                   placeholder="Type vendor name or mobile number..."
                                   autocomplete="off" required
                                   value="{{ $order->vendor->shop_name ?? '' }}">
                            <div class="vendor-dropdown" id="vendor_dropdown"></div>
                        </div>

                        <input type="hidden" name="vendor_id" id="selected_vendor_id" value="{{ $order->vendor_id }}" required>
                        <div id="selected_vendor_info" class="alert alert-info mt-2">
                            <strong>Selected Vendor:</strong> <span id="vendor_display_name">{{ $order->vendor->shop_name ?? '' }}</span><br>
                            <small>Mobile: <span id="vendor_display_mobile">{{ $order->vendor->mobile ?? 'N/A' }}</span> | Address: <span id="vendor_display_address">{{ $order->vendor->full_address ?? 'N/A' }}</span></small>
                        </div>

                        <div class="mt-3">
                            <label for="place_by">Order Placed By <span class="text-danger">*</span></label>
                            <select name="place_by" id="place_by" class="form-control @error('place_by') is-invalid @enderror" required>
                                <option value="">Select Admin</option>
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->id }}" {{ old('place_by', $order->place_by) == $admin->id ? 'selected' : '' }}>
                                        {{ $admin->name }} ({{ $admin->roles_string }})
                                    </option>
                                @endforeach
                            </select>
                            @error('place_by')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Order Items</h5>
                        {{-- <button type="button" class="btn btn-sm btn-success" onclick="addOrderItem()">
                            <i class="mdi mdi-plus"></i> Add Item
                        </button> --}}
                    </div>
                    <div class="card-body">
                        <div id="order_items_container">
                            <!-- Existing order items will be loaded here -->
                        </div>

                        <button type="button" class="add-item-btn" onclick="addOrderItem()">
                            <i class="mdi mdi-plus"></i> Add Another Product
                        </button>
                    </div>
                </div>

                <!-- Order Summary (Sticky Footer on Mobile) -->
                <div class="order-summary">
                    <div class="summary-row">
                        <span>Total Items:</span>
                        <span id="total_items">{{ $order->total_quantity ?? 0 }}</span>
                    </div>
                    <div class="summary-row">
                        <span>Total Quantity:</span>
                        <span id="total_quantity">{{ $order->total_quantity ?? 0 }}</span>
                    </div>
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span id="subtotal">৳{{ number_format(($order->total_amount ?? 0) + ($order->total_discount_amount ?? 0), 2) }}</span>
                    </div>
                    <div class="summary-row">
                        <span>Discount:</span>
                        <span id="total_discount">৳{{ number_format($order->total_discount_amount ?? 0, 2) }}</span>
                    </div>
                    <div class="summary-row">
                        <span>Total Amount:</span>
                        <span id="total_amount">৳{{ number_format($order->total_amount ?? 0, 2) }}</span>
                    </div>
                </div>

                <!-- Submit Button -->
                {{-- <div class="text-center mb-4 mt-2">
                    <a href="{{ route('orders.show', $order) }}" class="btn btn-secondary me-2">
                        <i class="mdi mdi-arrow-left me-1"></i>Cancel
                    </a>
                    <button type="submit" class="submit-order-btn" id="submit_order_btn">
                        <i class="mdi mdi-content-save"></i> Update Order
                    </button>
                </div> --}}

                <div class="d-flex justify-content-center align-items-center gap-2 mb-4 mt-2">
                    <a href="{{ route('orders.show', $order) }}" class="btn btn-secondary">
                        <i class="mdi mdi-arrow-left me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary" id="submit_order_btn">
                        <i class="mdi mdi-content-save"></i> Update Order
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Order Item Template -->
<template id="order_item_template">
    <div class="mb-3 order-item-card" data-index="">
        <button type="button" class="remove-item-btn" title="Remove Item">
            ×
        </button>

        <div class="mt-3 product-search-box">
            <label>Search Product</label>
            <input type="text" class="form-control product-search" placeholder="Type product name..." autocomplete="off">
            <div class="product-dropdown"></div>
        </div>

        <input type="hidden" name="items[][product_id]" class="product-id" required>

        <div class="row">
            <div class="col-md-3">
                <div class="form-row">
                    <label>Sell Price (per unit)</label>
                    <input type="number" name="items[][sell_price]" class="form-control sell-price" step="0.01" min="0" required readonly>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-row">
                    <label>Quantity</label>
                    <div class="quantity-controls">
                        <button type="button" class="quantity-btn quantity-decrease">-</button>
                        <input type="number" name="items[][quantity]" class="quantity-input" value="1" min="1" required>
                        <button type="button" class="quantity-btn quantity-increase">+</button>
                    </div>
                    <div class="available-stock-info"></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-row">
                    <label>Discount Amount</label>
                    <input type="number" name="items[][discount_price]" class="form-control discount-price" step="0.01" min="0" value="0">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-row">
                    <label>Item Total</label>
                    <input type="text" class="form-control item-total" readonly>
                </div>
            </div>
        </div>

        <div class="error-message" style="display: none;"></div>
    </div>
</template>
@endsection

@push('custome-js')
<script>
let orderItemIndex = 0;
let availableProducts = [];
let existingOrderItems = @json($order->orderItems);

$(document).ready(function() {
    // Load products
    loadProducts();

    // Load existing order items
    loadExistingOrderItems();

    // Vendor search functionality
    $('#vendor_search').on('input', function() {
        const query = $(this).val().trim();
        if (query.length >= 2) {
            searchVendors(query);
        } else {
            $('#vendor_dropdown').hide();
        }
    });

    // Event delegation for remove buttons
    $(document).on('click', '.remove-item-btn', function() {
        removeOrderItem(this);
    });

    // Event delegation for quantity buttons
    $(document).on('click', '.quantity-decrease', function() {
        changeQuantity(this, -1);
    });

    $(document).on('click', '.quantity-increase', function() {
        changeQuantity(this, 1);
    });

    // Hide dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.vendor-search-box').length) {
            $('#vendor_dropdown').hide();
        }
        if (!$(e.target).closest('.product-search-box').length) {
            $('.product-dropdown').hide();
        }
    });
});

function loadProducts() {
    $.get('{{ route("orders.getProductDetails") }}', {product_id: 'all'})
        .done(function(data) {
            availableProducts = data.products || [];
            console.log('Products loaded:', availableProducts.length);
        })
        .fail(function(xhr, status, error) {
            console.error('Failed to load products:', error);
            availableProducts = [];
        });
}

function loadExistingOrderItems() {
    // Load existing order items
    existingOrderItems.forEach(function(item, index) {
        addOrderItem(item);
    });

    // If no existing items, add one empty item
    if (existingOrderItems.length === 0) {
        addOrderItem();
    }

    // Initial update to make sure everything is calculated correctly
    updateOrderSummary();
    updateSubmitButton();
}

function searchVendors(query) {
    $.get('{{ route("orders.searchVendors") }}', {query: query})
        .done(function(vendors) {
            let html = '';
            vendors.forEach(function(vendor) {
                html += `
                    <div class="vendor-option" onclick="selectVendor(${vendor.id}, '${vendor.shop_name}', '${vendor.mobile}', '${vendor.full_address}')">
                        <strong>${vendor.shop_name || 'N/A'}</strong>
                        <div class="vendor-info">
                            Mobile: ${vendor.mobile || 'N/A'} | Contact: ${vendor.contact_person || 'N/A'}<br>
                            Address: ${vendor.full_address || 'N/A'}
                        </div>
                    </div>
                `;
            });

            $('#vendor_dropdown').html(html).show();
        })
        .fail(function() {
            $('#vendor_dropdown').html('<div class="vendor-option">Error loading vendors</div>').show();
        });
}

function selectVendor(id, name, mobile, address) {
    $('#selected_vendor_id').val(id);
    $('#vendor_search').val(name);
    $('#vendor_display_name').text(name);
    $('#vendor_display_mobile').text(mobile || 'N/A');
    $('#vendor_display_address').text(address || 'N/A');
    $('#selected_vendor_info').show();
    $('#vendor_dropdown').hide();
    updateSubmitButton();
}

function addOrderItem(existingItem = null) {
    const template = document.getElementById('order_item_template');
    const clone = template.content.cloneNode(true);

    // Set unique index
    const orderItem = clone.querySelector('.order-item-card');
    orderItem.setAttribute('data-index', orderItemIndex);

    // Update input names with correct index
    clone.querySelectorAll('input[name]').forEach(input => {
        const name = input.getAttribute('name');
        input.setAttribute('name', name.replace('[]', `[${orderItemIndex}]`));
    });

    // If existing item data provided, populate fields
    if (existingItem) {
        // Set product search field
        const productSearch = clone.querySelector('.product-search');
        productSearch.value = existingItem.product ? existingItem.product.name : '';

        // Set hidden product ID
        const productIdInput = clone.querySelector('.product-id');
        productIdInput.value = existingItem.product_id;

        // Set sell price
        const sellPriceInput = clone.querySelector('.sell-price');
        sellPriceInput.value = existingItem.sell_price;
        sellPriceInput.removeAttribute('readonly'); // Allow editing for existing items

        // Set quantity
        const quantityInput = clone.querySelector('.quantity-input');
        quantityInput.value = existingItem.quantity;

        // Set discount
        const discountInput = clone.querySelector('.discount-price');
        discountInput.value = existingItem.discount_price || 0;

        // Calculate and display item total
        const itemTotal = clone.querySelector('.item-total');
        const total = (existingItem.sell_price * existingItem.quantity) - (existingItem.discount_price || 0);
        itemTotal.value = `৳${total.toFixed(2)}`;
    }

    document.getElementById('order_items_container').appendChild(clone);

    // Add event listeners
    const container = document.querySelector(`[data-index="${orderItemIndex}"]`);
    setupProductSearch(container);
    setupPriceCalculation(container);

    // If existing item, load product details for stock info
    if (existingItem) {
        loadProductDetailsForExisting(container, existingItem.product_id);
    }

    orderItemIndex++;
    updateOrderSummary();
}

function loadProductDetailsForExisting(container, productId) {
    $.get('{{ route("orders.getProductDetails") }}', {product_id: productId})
        .done(function(data) {
            const stockInfo = container.querySelector('.available-stock-info');
            stockInfo.innerHTML = `Available: ${data.available_quantity} units`;
        })
        .fail(function() {
            const stockInfo = container.querySelector('.available-stock-info');
            stockInfo.innerHTML = 'Error loading stock info';
        });
}

function removeOrderItem(button) {
    console.log('Remove button clicked'); // Debug log
    try {
        const orderItem = button.closest('.order-item-card');
        console.log('Found order item:', orderItem); // Debug log
        if (orderItem) {
            orderItem.remove();
            updateOrderSummary();
            updateSubmitButton();

            // If no items left, add one
            if (document.querySelectorAll('.order-item-card').length === 0) {
                addOrderItem();
            }
            console.log('Order item removed successfully'); // Debug log
        }
    } catch (error) {
        console.error('Error removing order item:', error);
    }
}

function setupProductSearch(container) {
    const searchInput = container.querySelector('.product-search');
    const dropdown = container.querySelector('.product-dropdown');
    let searchTimeout;

    searchInput.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();

        // Clear previous timeout
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        if (query.length >= 2) {
            // Debounce search to prevent too many calls
            searchTimeout = setTimeout(() => {
                const filteredProducts = availableProducts.filter(product =>
                    product.name.toLowerCase().includes(query)
                );

                let html = '';
                filteredProducts.slice(0, 10).forEach(product => { // Limit to 10 results
                    const imageUrl = product.image_url && product.image_url !== '' ? product.image_url : '/assets/images/default-product.png';
                    const productName = product.name.replace(/'/g, "&apos;");
                    html += `
                        <div class="product-option" data-product-id="${product.id}" data-product-name="${productName}">
                            <img src="${imageUrl}" alt="${product.name}" class="product-image" onerror="this.src='/assets/images/default-product.png'">
                            <div class="product-info">
                                <div class="product-name">${product.name}</div>
                                <div class="product-details">
                                     Stock: ${product.available_quantity || 0}
                                </div>
                            </div>
                        </div>
                    `;
                });

                dropdown.innerHTML = html;
                dropdown.style.display = html ? 'block' : 'none';

                // Add click event listeners to options
                dropdown.querySelectorAll('.product-option').forEach(option => {
                    option.addEventListener('click', function() {
                        const productId = this.dataset.productId;
                        const productName = this.dataset.productName;
                        selectProduct(productId, productName, this);
                    });
                });
            }, 300); // 300ms delay
        } else {
            dropdown.style.display = 'none';
        }
    });
}

function selectProduct(productId, productName, element) {
    const container = element.closest('.order-item-card');
    const searchInput = container.querySelector('.product-search');
    const productIdInput = container.querySelector('.product-id');
    const sellPriceInput = container.querySelector('.sell-price');
    const stockInfo = container.querySelector('.available-stock-info');

    // Set values
    searchInput.value = productName;
    productIdInput.value = productId;

    // Hide dropdown
    container.querySelector('.product-dropdown').style.display = 'none';

    // Get product details and highest sell price
    $.get('{{ route("orders.getProductDetails") }}', {product_id: productId})
        .done(function(data) {
            sellPriceInput.value = data.highest_sell_price || 0;
            sellPriceInput.removeAttribute('readonly'); // Allow editing
            stockInfo.innerHTML = `Available: ${data.available_quantity} units`;
            updateItemTotal(container);
            updateSubmitButton();
        })
        .fail(function() {
            stockInfo.innerHTML = 'Error loading stock info';
        });
}

function changeQuantity(button, change) {
    const container = button.closest('.order-item-card');
    const quantityInput = container.querySelector('.quantity-input');
    let currentValue = parseInt(quantityInput.value) || 1;
    let newValue = Math.max(1, currentValue + change);
    quantityInput.value = newValue;

    updateItemTotal(container);
}

function setupPriceCalculation(container) {
    const inputs = container.querySelectorAll('.quantity-input, .sell-price, .discount-price');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            updateItemTotal(container);
        });
    });
}

function updateItemTotal(container) {
    const quantity = parseFloat(container.querySelector('.quantity-input').value) || 0;
    const sellPrice = parseFloat(container.querySelector('.sell-price').value) || 0;
    const discount = parseFloat(container.querySelector('.discount-price').value) || 0;

    const total = (quantity * sellPrice) - discount;
    container.querySelector('.item-total').value = `৳${total.toFixed(2)}`;

    updateOrderSummary();
}

function updateOrderSummary() {
    let totalItems = 0;
    let totalQuantity = 0;
    let subtotal = 0;
    let totalDiscount = 0;

    document.querySelectorAll('.order-item-card').forEach(container => {
        const quantity = parseFloat(container.querySelector('.quantity-input').value) || 0;
        const sellPrice = parseFloat(container.querySelector('.sell-price').value) || 0;
        const discount = parseFloat(container.querySelector('.discount-price').value) || 0;
        const productId = container.querySelector('.product-id').value;

        if (productId && quantity > 0 && sellPrice > 0) {
            totalItems++;
            totalQuantity += quantity;
            subtotal += quantity * sellPrice;
            totalDiscount += discount;
        }
    });

    const totalAmount = subtotal - totalDiscount;

    document.getElementById('total_items').textContent = totalItems;
    document.getElementById('total_quantity').textContent = totalQuantity;
    document.getElementById('subtotal').textContent = `৳${subtotal.toFixed(2)}`;
    document.getElementById('total_discount').textContent = `৳${totalDiscount.toFixed(2)}`;
    document.getElementById('total_amount').textContent = `৳${totalAmount.toFixed(2)}`;
}

function updateSubmitButton() {
    const vendorSelected = document.getElementById('selected_vendor_id').value;
    const hasValidItems = document.querySelectorAll('.order-item-card .product-id[value!=""]').length > 0;

    document.getElementById('submit_order_btn').disabled = !(vendorSelected && hasValidItems);
}

// Form submission
document.getElementById('orderForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submit_order_btn');
    const vendorSelected = document.getElementById('selected_vendor_id').value;
    const orderItems = document.querySelectorAll('.order-item-card');

    // Validate order items
    let hasValidItems = false;
    let validationErrors = [];

    orderItems.forEach((item, index) => {
        const productId = item.querySelector('.product-id').value;
        const quantity = parseFloat(item.querySelector('.quantity-input').value) || 0;
        const sellPrice = parseFloat(item.querySelector('.sell-price').value) || 0;

        if (productId && quantity > 0 && sellPrice > 0) {
            hasValidItems = true;
        } else if (productId || quantity > 0 || sellPrice > 0) {
            // Partially filled item
            validationErrors.push(`Item ${index + 1}: Please complete all required fields`);
        }
    });

    if (!hasValidItems) {
        e.preventDefault();
        alert('Please add at least one valid product to the order');
        return false;
    }

    if (validationErrors.length > 0) {
        e.preventDefault();
        alert('Validation errors:\n' + validationErrors.join('\n'));
        return false;
    }

    // Disable submit button and show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Updating Order...';

    // Allow form submission to proceed
    return true;
});
</script>
@endpush
