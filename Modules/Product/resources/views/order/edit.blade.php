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

@section('title', 'Edit Order')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Edit Order - {{ $order->invoice_id }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('orders.index') }}">Orders</a></li>
                        <li class="breadcrumb-item active">Edit</li>
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
                    <form action="{{ route('orders.update', $order) }}" method="POST" id="orderForm">
                        @csrf
                        @method('PUT')

                        <!-- Order Basic Info -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Invoice ID</label>
                                <input type="text" class="form-control" value="{{ $order->invoice_id }}" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Vendor <span class="text-danger">*</span></label>
                                <select name="vendor_id" class="form-select" required>
                                    <option value="">Select Vendor</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ $order->vendor_id == $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('vendor_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Order Status <span class="text-danger">*</span></label>
                                <select name="order_status_id" class="form-select" required>
                                    <option value="">Select Status</option>
                                    @foreach($orderStatuses as $status)
                                        <option value="{{ $status->id }}" {{ $order->order_status_id == $status->id ? 'selected' : '' }}>
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
                        <div class="order-summary" id="orderSummary">
                            <h5 class="mb-3">Order Summary</h5>
                            <div class="summary-item">
                                <span>Total Items:</span>
                                <span id="totalItems">{{ $order->total_quantity }}</span>
                            </div>
                            <div class="summary-item">
                                <span>Subtotal:</span>
                                <span>৳<span id="subtotal">{{ number_format($order->total_amount + $order->total_discount_amount, 2) }}</span></span>
                            </div>
                            <div class="summary-item">
                                <span>Total Discount:</span>
                                <span>৳<span id="totalDiscount">{{ number_format($order->total_discount_amount, 2) }}</span></span>
                            </div>
                            <div class="summary-item">
                                <span>Net Total:</span>
                                <span>৳<span id="netTotal">{{ number_format($order->net_amount, 2) }}</span></span>
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
                                @foreach($order->orderItems as $index => $item)
                                    <div class="item-content order-item-entry">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="text-dark mb-0 order-item-title">Item #{{ $index + 1 }}</h6>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label text-dark">Product <span class="text-danger">*</span></label>
                                                <select data-name="product_id" class="form-control product-select" required>
                                                    <option value="">Select Product</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}"
                                                                data-image="{{ $product->product_image_thumb_url ?? asset('images/no-image.png') }}"
                                                                {{ $item->product_id == $product->id ? 'selected' : '' }}>
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
                                                <input type="number" data-name="sell_price" class="form-control sell-price"
                                                       step="0.01" min="0" value="{{ $item->sell_price }}" required>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label text-dark">Quantity <span class="text-danger">*</span></label>
                                                <input type="number" data-name="quantity" class="form-control quantity"
                                                       min="1" value="{{ $item->quantity }}" required>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label text-dark">Discount</label>
                                                <input type="number" data-name="discount_price" class="form-control discount-price"
                                                       step="0.01" min="0" value="{{ $item->discount_price }}">
                                            </div>

                                            <div class="col-12">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="total-display">Total: ৳<span class="item-total">{{ number_format($item->net_price, 2) }}</span></div>
                                                    <button type="button" class="btn btn-outline-danger btn-sm remove-btn">
                                                        <i class="mdi mdi-delete me-1"></i>Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Hidden field to store selected stock_id -->
                                        <input type="hidden" class="selected-stock-id" value="{{ $item->stock_id }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <a href="{{ route('orders.show', $order) }}" class="btn btn-secondary me-2">
                                <i class="mdi mdi-arrow-left me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-content-save me-1"></i>Update Order
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
            // Load existing product stocks
            loadExistingStocks();
        }
    });

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

    function loadExistingStocks() {
        $("#repeater .items").each(function() {
            let $row = $(this);
            let productId = $row.find(".product-select").val();
            let selectedStockId = $row.find(".selected-stock-id").val();

            if (productId) {
                loadProductStocks($row, productId, selectedStockId);
            }
        });
    }

    function loadProductStocks($row, productId, selectedStockId = null) {
        let $stockSelect = $row.find(".stock-select");
        let $availableStock = $row.find(".available-stock");

        $.get('{{ route("orders.getProductDetails") }}', {product_id: productId})
            .done(function(data) {
                $availableStock.text(`Available: ${data.available_quantity} units`);

                // Populate stock options
                $stockSelect.html('<option value="">Select Stock</option>');
                data.stocks.forEach(function(stock) {
                    let selected = selectedStockId == stock.id ? 'selected' : '';
                    $stockSelect.append(`<option value="${stock.id}" data-price="${stock.sell_price}" data-available="${stock.available_quantity}" ${selected}>
                        Batch: ${stock.batch_id} (৳${stock.sell_price}) - ${stock.available_quantity} available
                    </option>`);
                });

                // If no stock was preselected, auto-select highest price stock
                if (!selectedStockId && data.stocks.length > 0) {
                    let highestPriceStock = data.stocks.reduce((prev, current) =>
                        (prev.sell_price > current.sell_price) ? prev : current
                    );
                    $stockSelect.val(highestPriceStock.id);
                }

                $stockSelect.trigger('change');
            })
            .fail(function() {
                alert('Error loading product details');
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

        if (productId) {
            loadProductStocks($row, productId);
        } else {
            $row.find(".stock-select").html('<option value="">Select Stock</option>');
            $row.find(".available-stock").text('');
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

            // Update price only if it's not manually set
            if (!$sellPrice.data('manual-price')) {
                $sellPrice.val(price);
            }

            $quantity.attr('max', available);

            // Update quantity if it exceeds available
            if (parseInt($quantity.val()) > available) {
                $quantity.val(available);
            }
        } else {
            $quantity.removeAttr('max');
        }

        calculateItemTotal($row);
    });

    // Mark price as manually set when edited
    $(document).on("input", ".sell-price", function(){
        $(this).data('manual-price', true);
        let $row = $(this).closest(".order-item-entry");
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
    }

    // Calculate total on input change
    $(document).on("input", ".quantity, .discount-price", function(){
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

    // Initial calls
    updateRepeater();
    updateOrderSummary();
});
</script>
@endpush
