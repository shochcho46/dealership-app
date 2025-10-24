@extends('layouts.app')

@push('custome-css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.6.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />

<style>
    .order-item-entry {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 15px;
        background-color: #f8f9fa;
    }
    .order-item-entry .form-control, .order-item-entry .form-select {
        height: calc(2.25rem + 2px);
    }
    .total-display {
        font-size: 1.1rem;
        font-weight: bold;
        color: #495057;
        margin-top: 8px;
    }
    .select2-container--bootstrap4 .select2-selection {
        height: calc(2.25rem + 2px);
        padding: .375rem .75rem;
        font-size: 1rem;
    }
    .select2-results__option img,
    .select2-selection__rendered img {
        width: 25px;
        height: 25px;
        object-fit: cover;
        border-radius: 4px;
        margin-right: 6px;
        vertical-align: middle;
    }
    .form-label {
        font-weight: 500;
    }
    .available-stock {
        font-size: 0.85rem;
        color: #6c757d;
        font-style: italic;
    }
    .order-summary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .summary-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    .summary-item:last-child {
        margin-bottom: 0;
        border-top: 1px solid rgba(255,255,255,0.2);
        padding-top: 8px;
        font-weight: bold;
        font-size: 1.1rem;
    }
</style>
@endpush

@section('title', 'Create Order')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Create Order</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('orders.index') }}">Orders</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Order Information</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('orders.store') }}" method="POST" id="orderForm">
                        @csrf

                        <!-- Order Basic Info -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Vendor <span class="text-danger">*</span></label>
                                <select name="vendor_id" class="form-select" required>
                                    <option value="">Select Vendor</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->shop_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('vendor_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Order Status <span class="text-danger">*</span></label>
                                <select name="order_status_id" class="form-select" required>
                                    <option value="">Select Status</option>
                                    @foreach($orderStatuses as $status)
                                        <option value="{{ $status->id }}" {{ old('order_status_id') == $status->id ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('order_status_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Order Summary -->
                        <div class="order-summary" id="orderSummary" style="display: none;">
                            <h5 class="mb-3">Order Summary</h5>
                            <div class="summary-item">
                                <span>Total Items:</span>
                                <span id="totalItems">0</span>
                            </div>
                            <div class="summary-item">
                                <span>Subtotal:</span>
                                <span>৳<span id="subtotal">0.00</span></span>
                            </div>
                            <div class="summary-item">
                                <span>Total Discount:</span>
                                <span>৳<span id="totalDiscount">0.00</span></span>
                            </div>
                            <div class="summary-item">
                                <span>Net Total:</span>
                                <span>৳<span id="netTotal">0.00</span></span>
                            </div>
                        </div>

                        <!-- Order Items Repeater -->
                        <div id="repeater">
                            <div class="repeater-heading d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Order Items</h6>
                                <button type="button" class="btn btn-success btn-sm repeater-add-btn">
                                    <i class="mdi mdi-plus me-1"></i>Add Item
                                </button>
                            </div>

                            <div class="items" data-group="items">
                                <div class="item-content order-item-entry">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h6 class="text-dark mb-0 order-item-title">Item #1</h6>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label text-dark">Product <span class="text-danger">*</span></label>
                                            <select data-name="product_id" class="form-control product-select" required>
                                                <option value="" selected disabled>Select Product</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}"
                                                            data-image="{{ $product->product_image_thumb_url ?? asset('images/no-image.png') }}">
                                                        {{ $product->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="available-stock mt-1"></div>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="form-label text-dark">Stock Batch</label>
                                            <select data-name="stock_id" class="form-select stock-select" required>
                                                <option value="">Select Stock</option>
                                            </select>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="form-label text-dark">Sell Price <span class="text-danger">*</span></label>
                                            <input type="number" data-name="sell_price" class="form-control sell-price" step="0.01" min="0" required readonly>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="form-label text-dark">Quantity <span class="text-danger">*</span></label>
                                            <input type="number" data-name="quantity" class="form-control quantity" min="1" required>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="form-label text-dark">Discount</label>
                                            <input type="number" data-name="discount_price" class="form-control discount-price" step="0.01" min="0" value="0">
                                        </div>

                                        <div class="col-12">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="total-display">Total: ৳<span class="item-total">0.00</span></div>
                                                <button type="button" class="btn btn-outline-danger btn-sm remove-btn">
                                                    <i class="mdi mdi-delete me-1"></i>Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <a href="{{ route('orders.index') }}" class="btn btn-secondary me-2">
                                <i class="mdi mdi-arrow-left me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-content-save me-1"></i>Create Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('custome-js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('js/repeater.js') }}"></script>

<script>
$(document).ready(function () {
    // Initialize repeater
    $("#repeater").createRepeater({
        showFirstItemToDefault: true,
        ready: function () {
            updateRepeater();
        }
    });

    // Function to initialize Select2 and update input names
    function updateRepeater() {
        $("#repeater .items").each(function(index){
            // Assign proper name attributes for submission
            $(this).find("[data-name]").each(function(){
                let field = $(this).data("name");
                $(this).attr("name", `items[${index}][${field}]`);
            });

            // Initialize Select2 for product select if not already initialized
            let $productSelect = $(this).find(".product-select");
            if (!$productSelect.hasClass("select2-hidden-accessible")) {
                $productSelect.select2({
                    theme: "bootstrap4",
                    placeholder: "Search product...",
                    allowClear: false,
                    templateResult: formatProductOption,
                    templateSelection: formatProductSelection,
                    escapeMarkup: function(m) { return m; }
                });
            }
        });

        // Update row titles
        $("#repeater .items").each(function(i){
            $(this).find(".order-item-title").text("Item #" + (i+1));
        });

        // Show/hide remove buttons
        $("#repeater .items").each(function(i){
            $(this).find(".remove-btn").toggle($("#repeater .items").length > 1);
        });
    }

    function formatProductOption(option) {
        if (!option.id) return option.text;
        let img = $(option.element).data("image") || '';
        return `<span><img src="${img}"> ${option.text}</span>`;
    }

    function formatProductSelection(option) {
        if (!option.id) return option.text;
        let img = $(option.element).data("image") || '';
        return `<span><img src="${img}"> ${option.text}</span>`;
    }

    // Product selection change
    $(document).on("change", ".product-select", function(){
        let $row = $(this).closest(".order-item-entry");
        let productId = $(this).val();
        let $stockSelect = $row.find(".stock-select");
        let $sellPrice = $row.find(".sell-price");
        let $availableStock = $row.find(".available-stock");

        // Reset dependent fields
        $stockSelect.html('<option value="">Select Stock</option>').val('');
        $sellPrice.val('');
        $availableStock.text('');

        if (productId) {
            // Get product details
            $.get('{{ route("orders.getProductDetails") }}', {product_id: productId})
                .done(function(data) {
                    $availableStock.text(`Available: ${data.available_quantity} units`);

                    // Populate stock options
                    data.stocks.forEach(function(stock) {
                        $stockSelect.append(`<option value="${stock.id}" data-price="${stock.sell_price}" data-available="${stock.available_quantity}">
                            Batch: ${stock.batch_id} (৳${stock.sell_price}) - ${stock.available_quantity} available
                        </option>`);
                    });

                    // Auto-select highest price stock
                    if (data.stocks.length > 0) {
                        let highestPriceStock = data.stocks.reduce((prev, current) =>
                            (prev.sell_price > current.sell_price) ? prev : current
                        );
                        $stockSelect.val(highestPriceStock.id).trigger('change');
                    }
                })
                .fail(function() {
                    alert('Error loading product details');
                });
        }
    });

    // Stock selection change
    $(document).on("change", ".stock-select", function(){
        let $row = $(this).closest(".order-item-entry");
        let $sellPrice = $row.find(".sell-price");
        let $quantity = $row.find(".quantity");
        let selectedOption = $(this).find('option:selected');

        if (selectedOption.val()) {
            let price = selectedOption.data('price');
            let available = selectedOption.data('available');

            $sellPrice.val(price);
            $quantity.attr('max', available);

            // Update quantity if it exceeds available
            if (parseInt($quantity.val()) > available) {
                $quantity.val(available);
            }
        } else {
            $sellPrice.val('');
            $quantity.removeAttr('max');
        }

        calculateItemTotal($row);
    });

    // Calculate item total
    function calculateItemTotal($row) {
        let sellPrice = parseFloat($row.find(".sell-price").val()) || 0;
        let quantity = parseInt($row.find(".quantity").val()) || 0;
        let discount = parseFloat($row.find(".discount-price").val()) || 0;

        let total = (sellPrice * quantity) - discount;
        $row.find(".item-total").text(total.toFixed(2));

        updateOrderSummary();
    }

    // Update order summary
    function updateOrderSummary() {
        let totalItems = 0;
        let subtotal = 0;
        let totalDiscount = 0;

        $("#repeater .items").each(function() {
            let quantity = parseInt($(this).find(".quantity").val()) || 0;
            let sellPrice = parseFloat($(this).find(".sell-price").val()) || 0;
            let discount = parseFloat($(this).find(".discount-price").val()) || 0;

            totalItems += quantity;
            subtotal += (sellPrice * quantity);
            totalDiscount += discount;
        });

        let netTotal = subtotal - totalDiscount;

        $("#totalItems").text(totalItems);
        $("#subtotal").text(subtotal.toFixed(2));
        $("#totalDiscount").text(totalDiscount.toFixed(2));
        $("#netTotal").text(netTotal.toFixed(2));

        // Show/hide summary
        if (totalItems > 0) {
            $("#orderSummary").show();
        } else {
            $("#orderSummary").hide();
        }
    }

    // Calculate total on input change
    $(document).on("input", ".sell-price, .quantity, .discount-price", function(){
        let $row = $(this).closest(".order-item-entry");
        calculateItemTotal($row);
    });

    // Add/Remove row
    $(document).on("click", ".repeater-add-btn, .remove-btn", function(){
        setTimeout(function(){
            updateRepeater();
            updateOrderSummary();
        }, 100);
    });

    // Form validation
    $("#orderForm").on("submit", function(e) {
        let hasItems = false;

        $("#repeater .items").each(function() {
            let productId = $(this).find(".product-select").val();
            let stockId = $(this).find(".stock-select").val();
            let quantity = $(this).find(".quantity").val();

            if (productId && stockId && quantity) {
                hasItems = true;
            }
        });

        if (!hasItems) {
            e.preventDefault();
            alert('Please add at least one valid order item.');
            return false;
        }

        // Validate stock availability
        let isValid = true;
        $("#repeater .items").each(function() {
            let $quantity = $(this).find(".quantity");
            let max = parseInt($quantity.attr('max'));
            let val = parseInt($quantity.val());

            if (max && val > max) {
                isValid = false;
                $quantity.addClass('is-invalid');
            } else {
                $quantity.removeClass('is-invalid');
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Please check quantity limits for all items.');
            return false;
        }
    });

    // Initial call
    updateRepeater();
    updateOrderSummary();
});
</script>
@endpush
