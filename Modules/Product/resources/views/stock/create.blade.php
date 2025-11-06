@extends('layouts.app')

@push('custome-css')
<style>
    .stock-entry-item {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 15px;
        background-color: #f8f9fa;
    }
    .stock-entry-item .form-control {
        height: calc(2.25rem + 2px); /* uniform height for all inputs */
    }
    .total-display {
        font-size: 1.1rem;
        font-weight: bold;
        color: #495057;
        margin-top: 8px;
    }
    .form-label {
        font-weight: 500;
    }
    .product-search-wrapper {
        position: relative;
    }
    .product-search-results {
        position: absolute;
        background: white;
        border: 1px solid #ddd;
        border-top: none;
        max-height: 400px;
        overflow-y: auto;
        width: 100%;
        z-index: 1000;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        display: none;
    }
    .product-search-item {
        padding: 12px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .product-search-item:hover {
        background-color: #f8f9fa;
    }
    .product-search-item:last-child {
        border-bottom: none;
    }
    .product-search-item img {
        width: 35px;
        height: 35px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #ddd;
    }
    .product-search-item .product-info {
        flex: 1;
    }
    .product-search-item .product-name {
        font-weight: bold;
        color: #333;
        font-size: 12px;
    }
    .product-search-item .product-details {
        color: #666;
        font-size: 8px;
        margin-top: 2px;
    }
    .product-search-item .stock-badge {
        font-size: 10px;
        padding: 2px 4px;
    }
    .selected-product-display {
        display: none;
        padding: 1px;
        background: white;
        border: 1px solid #0009ab;
        border-radius: 4px;
        margin-top: 2px;
    }
    .selected-product-display img {
         width: 35px;
        height: 35px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #ddd;
    }
    .selected-product-display .product-info {
        flex: 1;
        margin-left: 12px;
    }
    .selected-product-display .selected-product-name {
        font-weight: bold;
        color: #333;
        font-size: 12 px;
    }
    .selected-product-display .selected-product-details {
        color: #666;
        font-size: 8px;
        margin-top: 2px;
    }
    .selected-product-display .stock-badge {
        font-size: 0.8rem;
        padding: 2px 4px;
    }
    .selected-product-display .clear-selection {
        cursor: pointer;
        color: #dc3545;
        font-size: 1.5rem;
        font-weight: bold;
        line-height: 1;
        padding: 0 5px;
    }
    .selected-product-display .clear-selection:hover {
        color: #c82333;
    }
</style>
@endpush

@section('content')
<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.stockStore') }}" method="POST">
                            @csrf

                            <div id="repeater">
                                <div class="repeater-heading d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">Stock Entries</h6>
                                    <button type="button" class="btn btn-success btn-sm repeater-add-btn">
                                        <i class="mdi mdi-plus"></i> Add Row
                                    </button>
                                </div>

                                <div class="items" data-group="stocks">
                                    <div class="item-content stock-entry-item">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="text-dark mb-0 stock-entry-title">Stock Entry #1</h6>
                                        </div>

                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-3">
                                                <label class="form-label text-dark">Product <span class="text-danger">*</span></label>
                                                <div class="product-search-wrapper">
                                                    <input type="text"
                                                           class="form-control product-search-input"
                                                           placeholder="Search product..."
                                                           autocomplete="off">
                                                    <input type="hidden" data-name="product_id" class="product-id-input" required>
                                                    <div class="product-search-results"></div>
                                                    <div class="selected-product-display">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <div class="d-flex align-items-center flex-grow-1">
                                                                <img src="" alt="" class="selected-product-image">
                                                                <div class="product-info">
                                                                    <div class="selected-product-name"></div>
                                                                    <div class="selected-product-details"></div>
                                                                </div>
                                                                <span class="badge stock-badge selected-stock-badge"></span>
                                                            </div>
                                                            <span class="clear-selection" title="Clear selection">×</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-md-3">
                                                <label class="form-label text-dark">Quantity <span class="text-danger">*</span></label>
                                                <input type="number" data-name="quantity" class="form-control quantity" min="1" required>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label text-dark">Purchase Price <span class="text-danger">*</span></label>
                                                <input type="number" data-name="purchase_price" class="form-control purchase-price" step="0.01" min="0" required>
                                            </div>


                                            <div class="col-md-3">
                                                <label class="form-label text-dark">Sell Price <span class="text-danger">*</span></label>
                                                <input type="number" data-name="sell_price" class="form-control" step="0.01" min="0" required>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label text-dark">Damage Qty</label>
                                                <input type="number" data-name="damage_quantity" class="form-control" min="0" value="0">
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label text-dark">Stolen Qty</label>
                                                <input type="number" data-name="stolen_quantity" class="form-control" min="0" value="0">
                                            </div>

                                            <div class="col-md-6 text-end repeater-remove-btn mt-2">
                                                <button type="button" class="btn btn-outline-danger btn-sm remove-btn">
                                                    <i class="mdi mdi-delete"></i> Remove
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Total Row -->
                                        <div class="row mt-2">
                                            <div class="col-12 text-end">
                                                <div class="total-display">Total: $<span class="total-price">0.00</span></div>
                                            </div>
                                        </div>

                                        <input type="hidden" data-name="sold_quantity" value="0">
                                    </div>
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-content-save"></i> Save Stock Entries
                                </button>
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
<script src="{{ asset('js/repeater.js') }}"></script>

<script>
$(document).ready(function () {
    let searchTimeout;

    // Initialize repeater
    $("#repeater").createRepeater({
        showFirstItemToDefault: true,
        ready: function () {
            updateRepeater();
        }
    });

    // Function to update input names
    function updateRepeater() {
        $("#repeater .items").each(function(index){
            // Assign proper name attributes for submission
            $(this).find("[data-name]").each(function(){
                let field = $(this).data("name");
                $(this).attr("name", `stocks[${index}][${field}]`);
            });
        });

        // Update row titles
        $("#repeater .items").each(function(i){
            $(this).find(".stock-entry-title").text("Stock Entry #" + (i+1));
        });

        // Show/hide remove buttons
        $("#repeater .items").each(function(i){
            $(this).find(".repeater-remove-btn").toggle(i !== 0);
        });
    }

    // Product search functionality
    $(document).on('input', '.product-search-input', function() {
        const $input = $(this);
        const $wrapper = $input.closest('.product-search-wrapper');
        const $results = $wrapper.find('.product-search-results');
        const query = $input.val().trim();

        clearTimeout(searchTimeout);

        if (query.length < 2) {
            $results.hide();
            return;
        }

        searchTimeout = setTimeout(function() {
            $.ajax({
                url: "{{ route('admin.stock.searchProducts') }}",
                method: 'GET',
                data: { q: query },
                success: function(products) {
                    displayProductResults($wrapper, products);
                },
                error: function(xhr) {
                    console.error('Product search error:', xhr);
                    $results.html('<div class="product-search-item text-danger">Error searching products</div>').show();
                }
            });
        }, 300);
    });

    function displayProductResults($wrapper, products) {
        const $results = $wrapper.find('.product-search-results');
        let html = '';

        if (!Array.isArray(products) || products.length === 0) {
            html = '<div class="product-search-item text-muted">No products found</div>';
        } else {
            products.forEach(function(product) {
                const stockBadgeClass = product.total_stock > 10 ? 'bg-success' : (product.total_stock > 0 ? 'bg-warning' : 'bg-danger');
                const imageSrc = product.image || 'https://via.placeholder.com/50x50?text=No+Image';

                html += `
                    <div class="product-search-item" data-product='${JSON.stringify(product)}'>
                        <img src="${imageSrc}" alt="${product.name}">
                        <div class="product-info">
                            <div class="product-name">${product.name}</div>
                            <div class="product-details">
                                ${product.color ? product.color + ' | ' : ''}${product.unit || ''}
                            </div>
                        </div>
                        <div class="badge ${stockBadgeClass} stock-badge">
                            Stock: ${product.total_stock}
                        </div>
                    </div>
                `;
            });
        }

        $results.html(html).show();
    }

    // Handle product selection
    $(document).on('click', '.product-search-item[data-product]', function() {
        const product = JSON.parse($(this).attr('data-product'));
        const $wrapper = $(this).closest('.product-search-wrapper');

        selectProduct($wrapper, product);
    });

    function selectProduct($wrapper, product) {
        const imageSrc = product.image || 'https://via.placeholder.com/50x50?text=No+Image';
        const stockBadgeClass = product.total_stock > 10 ? 'bg-success' : (product.total_stock > 0 ? 'bg-warning' : 'bg-danger');

        // Set hidden input value
        $wrapper.find('.product-id-input').val(product.id);

        // Hide search input and results
        $wrapper.find('.product-search-input').hide();
        $wrapper.find('.product-search-results').hide();

        // Show selected product display
        const $display = $wrapper.find('.selected-product-display');
        $display.find('.selected-product-image').attr('src', imageSrc).attr('alt', product.name);
        $display.find('.selected-product-name').text(product.name);
        $display.find('.selected-product-details').text(
            (product.color ? product.color + ' | ' : '') + (product.unit || '')
        );
        $display.find('.selected-stock-badge')
            .removeClass('bg-success bg-warning bg-danger')
            .addClass(stockBadgeClass)
            .text('Stock: ' + product.total_stock);
        $display.show();
    }

    // Clear selection
    $(document).on('click', '.clear-selection', function() {
        const $wrapper = $(this).closest('.product-search-wrapper');

        $wrapper.find('.product-id-input').val('');
        $wrapper.find('.product-search-input').val('').show();
        $wrapper.find('.selected-product-display').hide();
        $wrapper.find('.product-search-results').hide();
    });

    // Hide results when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.product-search-wrapper').length) {
            $('.product-search-results').hide();
        }
    });

    // Calculate total
    $(document).on("input", ".purchase-price, .quantity", function(){
        let $row = $(this).closest(".stock-entry-item");
        let price = parseFloat($row.find(".purchase-price").val()) || 0;
        let qty = parseInt($row.find(".quantity").val()) || 0;
        $row.find(".total-price").text((price * qty).toFixed(2));
    });

    // Add/Remove row
    $(document).on("click", ".repeater-add-btn, .remove-btn", function(){
        setTimeout(function(){
            updateRepeater();
        }, 100);
    });

    // Initial call
    updateRepeater();

});
</script>
@endpush
