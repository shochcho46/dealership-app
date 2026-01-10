@extends('layouts.app')

@section('title', 'Report Damage/Return/Lost')

@push('custome-css')
<style>
    .order-selector {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .order-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .order-item {
        background: #ffffff;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .order-item:hover {
        border-color: #007bff;
        box-shadow: 0 2px 8px rgba(0,123,255,0.15);
    }

    .order-item.selected {
        border-color: #28a745;
        background: #f8fff9;
        box-shadow: 0 2px 8px rgba(40,167,69,0.15);
    }

    .item-radio {
        transform: scale(1.3);
        margin-right: 10px;
    }

    .quantity-controls {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 15px 0;
    }

    .quantity-btn {
        width: 40px;
        height: 40px;
        border: none;
        border-radius: 50%;
        background: #007bff;
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .quantity-btn:hover {
        background: #0056b3;
        transform: scale(1.1);
    }

    .quantity-input {
        width: 80px;
        text-align: center;
        font-size: 1.1rem;
        font-weight: bold;
    }

    .evidence-upload {
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s ease;
    }

    .evidence-upload:hover {
        border-color: #007bff;
        background: #f0f8ff;
    }

    .evidence-preview {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 15px;
    }

    .evidence-thumb {
        position: relative;
        width: 100px;
        height: 100px;
        border-radius: 8px;
        overflow: hidden;
        border: 2px solid #dee2e6;
    }

    .evidence-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .evidence-remove {
        position: absolute;
        top: 5px;
        right: 5px;
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        font-size: 12px;
        cursor: pointer;
    }

    @media (max-width: 768px) {
        .order-item {
            padding: 10px;
        }

        .quantity-controls {
            justify-content: center;
        }

        .evidence-thumb {
            width: 80px;
            height: 80px;
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
                <h4 class="mb-sm-0">Report Damage/Return/Lost</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('damage-return-lost.index') }}">Damage/Return/Lost</a></li>
                        <li class="breadcrumb-item active">Report New Issue</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if(!$order)
        <!-- Order Selection -->
        <div class="card order-selector">
            <div class="card-body">
                <h5 class="card-title">Search Order</h5>
                <p class="text-muted">Search for shipped or delivered orders to report issues</p>
                <div class="row">
                    <div class="col-md-8">
                        <input type="text" class="form-control form-control-lg" id="orderSearch"
                               placeholder="Search by invoice ID or vendor name...">
                        <div id="orderSearchResults" class="mt-3"></div>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-primary btn-lg w-100" onclick="searchOrders()">
                            <i class="mdi mdi-magnify"></i> Search Orders
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Selected Order Info -->
        <div class="order-card">
            <div class="row">
                <div class="col-md-8">
                    <h4><i class="mdi mdi-receipt"></i> Order {{ $order->invoice_id }}</h4>
                    <p class="mb-1"><i class="mdi mdi-store"></i> {{ $order->vendor->shop_name }}</p>
                    <p class="mb-1"><i class="mdi mdi-phone"></i> {{ $order->vendor->mobile }}</p>
                    <p class="mb-0"><i class="mdi mdi-calendar"></i> {{ $order->created_at->format('M d, Y') }}</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="h4">Total: ৳{{ number_format($order->total_amount, 2) }}</div>
                    <div>{{ $orderItems->count() }} item(s)</div>
                    <div class="badge bg-{{ $order->orderStatus->name == 'Delivered' ? 'success' : 'primary' }}">
                        {{ $order->orderStatus->name }}
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('damage-return-lost.store') }}" enctype="multipart/form-data" id="reportForm">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order->id }}">
            <input type="hidden" name="vendor_id" value="{{ $order->vendor_id }}">
            <input type="hidden" name="order_item_id" id="selectedOrderItemId">
            <input type="hidden" name="stock_id" id="selectedStockId">

            <div class="row">
                <!-- Order Items Selection -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Select Product Item</h5>
                        </div>
                        <div class="card-body">
                            @if($orderItems->count() > 0)
                                @foreach($orderItems as $item)
                                    @php
                                        $processedQty = \Modules\Product\Models\DamageReturnLost::where('order_item_id', $item->id)->sum('quantity');
                                        $availableQty = $item->quantity - $processedQty;
                                    @endphp

                                    @if($availableQty > 0)
                                        <div class="order-item" onclick="selectOrderItem({{ $item->id }}, {{ $availableQty }}, {{ (($item->sell_price * $item->quantity) - $item?->discount_price)/($item->quantity) }})">
                                            <div class="row align-items-center">
                                                <div class="col-md-1">
                                                    <input type="radio" class="form-check-input item-radio"
                                                           name="selected_item" value="{{ $item->id }}"
                                                           id="item_{{ $item->id }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>{{ $item->product->name ?? 'N/A' }}</strong>
                                                    <br>
                                                    @if($item->product && $item->product->description)
                                                        <br><small class="text-muted">{{ Str::limit($item->product->description, 80) }}</small>
                                                    @endif
                                                </div>
                                                <div class="col-md-3">
                                                    <div><strong>Ordered:</strong> {{ number_format($item->quantity) }}</div>
                                                    @if($processedQty > 0)
                                                        <div><small class="text-warning">Processed: {{ number_format($processedQty) }}</small></div>
                                                    @endif
                                                    <div><strong class="text-success">Available: {{ number_format($availableQty) }}</strong></div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="text-end">
                                                        <strong>৳{{ number_format($item->sell_price, 2) }}</strong>
                                                        <br><small class="text-muted">per unit</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="order-item" style="opacity: 0.5; cursor: not-allowed;">
                                            <div class="row align-items-center">
                                                <div class="col-md-1">
                                                    <input type="radio" class="form-check-input" disabled>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>{{ $item->product->name ?? 'N/A' }}</strong>
                                                    <br><small class="text-muted">All quantities already processed</small>
                                                </div>
                                                <div class="col-md-3">
                                                    <div><strong>Ordered:</strong> {{ number_format($item->quantity) }}</div>
                                                    <div><small class="text-danger">Processed: {{ number_format($processedQty) }}</small></div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="text-end">
                                                        <strong>৳{{ number_format($item->sell_price, 2) }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @else
                                <div class="text-center py-4">
                                    <i class="mdi mdi-package-variant" style="font-size: 3rem; color: #ccc;"></i>
                                    <br>No items found in this order
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Report Form -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Report Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Issue Type *</label>
                                <select name="type" class="form-select" required>
                                    <option value="">Select Type</option>
                                    <option value="damage">Damage</option>
                                    <option value="return">Return</option>
                                    <option value="lost">Lost</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Quantity *</label>
                                <div class="quantity-controls">
                                    <button type="button" class="quantity-btn" onclick="changeQuantity(-1)">-</button>
                                    <input type="number" name="quantity" class="form-control quantity-input"
                                           id="quantityInput" value="1" min="1" max="1" required>
                                    <button type="button" class="quantity-btn" onclick="changeQuantity(1)">+</button>
                                </div>
                                <small class="text-muted" id="quantityHint">Select an item first</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Unit Price *</label>
                                <input type="number" name="unit_price" class="form-control"
                                       id="unitPriceInput" step="0.01" min="0" required readonly>
                                @error('unit_price')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Reason *</label>
                                <textarea name="reason" class="form-control" rows="4" required
                                        placeholder="Describe the issue in detail..."></textarea>
                                @error('reason')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="restock" class="form-check-input" id="restockCheck">
                                    <label class="form-check-label" for="restockCheck">
                                        Restock returned items
                                    </label>
                                    <small class="form-text text-muted">Only for returns in good condition</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Evidence Images</label>
                                <div class="evidence-upload" onclick="document.getElementById('evidenceInput').click()">
                                    <i class="mdi mdi-camera-plus" style="font-size: 2rem; color: #6c757d;"></i>
                                    <p class="mb-0">Click to upload evidence photos</p>
                                    <small class="text-muted">JPEG, PNG files only. Max 2MB each.</small>
                                </div>
                                <input type="file" name="images[]" id="evidenceInput" multiple
                                       accept="image/jpeg,image/png,image/jpg" style="display: none;"
                                       onchange="previewImages(this)">
                                <div id="evidencePreview" class="evidence-preview"></div>
                                @error('images.*')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-danger w-100" id="submitBtn" disabled>
                                <i class="mdi mdi-alert-circle"></i> Submit Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endif
</div>
@endsection

@push('custome-js')
<script>
let selectedOrderItem = null;
let maxQuantity = 0;
let selectedImages = [];

function searchOrders() {
    const search = document.getElementById('orderSearch').value;

    if (search.length < 1) {
        alert('Please enter at least 2 characters to search');
        return;
    }

    fetch(`{{ route('damage-return-lost.searchOrders') }}?search=${encodeURIComponent(search)}`)
        .then(response => response.json())
        .then(orders => {
            displayOrderResults(orders);
        })
        .catch(error => {
            console.error('Error searching orders:', error);
            alert('Error searching orders');
        });
}

function displayOrderResults(orders) {
    const resultsDiv = document.getElementById('orderSearchResults');

    if (orders.length === 0) {
        resultsDiv.innerHTML = '<div class="alert alert-info">No shipped/delivered orders found</div>';
        return;
    }

    let html = '<div class="row">';
    orders.forEach(order => {
        html += `
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-title">${order.invoice_id}</h6> <br>
                        <p class="card-text">
                            <small class="text-muted">
                                ${order.vendor_name}<br>
                                ${order.items_count} items - ৳${number_format(order.total_amount)}<br>
                                ${order.created_at}
                            </small>
                        </p>
                        <a href="{{ route('damage-return-lost.create') }}?order_id=${order.id}"
                           class="btn btn-primary btn-sm w-100">
                            Select Order
                        </a>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';

    resultsDiv.innerHTML = html;
}

function selectOrderItem(itemId, availableQty, unitPrice) {
    // Remove previous selection
    document.querySelectorAll('.order-item').forEach(item => {
        item.classList.remove('selected');
    });

    // Add selection to clicked item
    event.currentTarget.classList.add('selected');

    // Update form
    document.getElementById('selectedOrderItemId').value = itemId;
    document.getElementById('quantityInput').max = availableQty;
    document.getElementById('quantityInput').value = 1;
    document.getElementById('unitPriceInput').value = unitPrice.toFixed(2);
    document.getElementById('quantityHint').textContent = `Available: ${availableQty} items`;

    selectedOrderItem = itemId;
    maxQuantity = availableQty;

    // Enable submit button
    updateSubmitButton();
}

function changeQuantity(delta) {
    if (!selectedOrderItem) {
        alert('Please select an item first');
        return;
    }

    const input = document.getElementById('quantityInput');
    let newValue = parseInt(input.value) + delta;

    if (newValue < 1) newValue = 1;
    if (newValue > maxQuantity) newValue = maxQuantity;

    input.value = newValue;
}

function previewImages(input) {
    const preview = document.getElementById('evidencePreview');
    preview.innerHTML = '';
    selectedImages = [];

    Array.from(input.files).forEach((file, index) => {
        if (file.type.startsWith('image/')) {
            selectedImages.push(file);

            const reader = new FileReader();
            reader.onload = function(e) {
                const thumb = document.createElement('div');
                thumb.className = 'evidence-thumb';
                thumb.innerHTML = `
                    <img src="${e.target.result}" alt="Evidence ${index + 1}">
                    <button type="button" class="evidence-remove" onclick="removeImage(${index})">×</button>
                `;
                preview.appendChild(thumb);
            };
            reader.readAsDataURL(file);
        }
    });
}

function removeImage(index) {
    selectedImages.splice(index, 1);

    // Update file input
    const input = document.getElementById('evidenceInput');
    const dt = new DataTransfer();
    selectedImages.forEach(file => dt.items.add(file));
    input.files = dt.files;

    // Refresh preview
    previewImages(input);
}

function updateSubmitButton() {
    const submitBtn = document.getElementById('submitBtn');
    const typeSelected = document.querySelector('select[name="type"]').value;
    const reasonFilled = document.querySelector('textarea[name="reason"]').value.length > 5;

    if (selectedOrderItem && typeSelected && reasonFilled) {
        submitBtn.disabled = false;
    } else {
        submitBtn.disabled = true;
    }
}

function number_format(number, decimals = 2) {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    }).format(number);
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Add enter key support for order search
    const orderSearch = document.getElementById('orderSearch');
    if (orderSearch) {
        orderSearch.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchOrders();
            }
        });
        orderSearch.focus();
    }

    // Form validation listeners
    document.querySelector('select[name="type"]')?.addEventListener('change', updateSubmitButton);
    document.querySelector('textarea[name="reason"]')?.addEventListener('input', updateSubmitButton);

    // Form submission validation
    document.getElementById('reportForm')?.addEventListener('submit', function(e) {
        if (!selectedOrderItem) {
            e.preventDefault();
            alert('Please select an order item');
            return false;
        }

        const quantity = parseInt(document.getElementById('quantityInput').value);
        if (quantity < 1 || quantity > maxQuantity) {
            e.preventDefault();
            alert(`Quantity must be between 1 and ${maxQuantity}`);
            return false;
        }
    });
});
</script>
@endpush
