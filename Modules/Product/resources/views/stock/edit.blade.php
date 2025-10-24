@extends('layouts.app')

@push('custome-css')
<style>
    .calculation-card {
        background-color: #f8f9fa;
        border-left: 4px solid #007bff;
    }
</style>
@endpush

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Edit Stock</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.stockIndex') }}">Stock</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <span class="mdi mdi-package-variant-edit"></span>
                            Edit Stock Entry
                        </h5>
                         <div class="ms-auto">
                            <a href="{{ route('admin.stockIndex') }}" class="btn btn-secondary">
                                <span class="mdi mdi-arrow-left"></span> Back to List
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('admin.stockUpdate', $stock) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="batch_id" class="form-label text-dark">Batch ID</label>
                                        <input type="text"
                                               class="form-control"
                                               id="batch_id"
                                               value="{{ $stock->batch_id }}"
                                               readonly>
                                        <small class="text-muted">Batch ID cannot be changed</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="product_id" class="form-label text-dark">Product <span class="text-danger">*</span></label>
                                        <select name="product_id" id="product_id" class="form-control" required>
                                            <option value="">Select Product</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}"
                                                        {{ $stock->product_id == $product->id ? 'selected' : '' }}
                                                        data-color="{{ $product->color->name ?? 'N/A' }}"
                                                        data-unit="{{ $product->unit->name ?? 'N/A' }}">
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small id="product-info" class="text-muted">
                                            @if($stock->product)
                                                Color: {{ $stock->product->color->name ?? 'N/A' }} |
                                                Unit: {{ $stock->product->unit->name ?? 'N/A' }}
                                            @endif
                                        </small>
                                        @error('product_id')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="purchase_price" class="form-label text-dark">Purchase Price <span class="text-danger">*</span></label>
                                        <input type="number"
                                               name="purchase_price"
                                               id="purchase_price"
                                               class="form-control"
                                               step="0.01"
                                               min="0"
                                               value="{{ old('purchase_price', $stock->purchase_price) }}"
                                               required>
                                        @error('purchase_price')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="quantity" class="form-label text-dark">Quantity <span class="text-danger">*</span></label>
                                        <input type="number"
                                               name="quantity"
                                               id="quantity"
                                               class="form-control"
                                               min="1"
                                               value="{{ old('quantity', $stock->quantity) }}"
                                               required>
                                        @error('quantity')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="sell_price" class="form-label text-dark">Sale Price <span class="text-danger">*</span></label>
                                        <input type="number"
                                               name="sell_price"
                                               id="sell_price"
                                               class="form-control"
                                               step="0.01"
                                               min="0"
                                               value="{{ old('sell_price', $stock->sell_price) }}"
                                               required>
                                        @error('sell_price')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="damage_quantity" class="form-label text-dark">Damage Quantity</label>
                                        <input type="number"
                                               name="damage_quantity"
                                               id="damage_quantity"
                                               class="form-control"
                                               min="0"
                                               value="{{ old('damage_quantity', $stock->damage_quantity) }}">
                                        @error('damage_quantity')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="sold_quantity" class="form-label text-dark">Sold Quantity</label>
                                        <input type="number"
                                               name="sold_quantity"
                                               id="sold_quantity"
                                               class="form-control"
                                               min="0"
                                               value="{{ old('sold_quantity', $stock->sold_quantity) }}">
                                        @error('sold_quantity')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="stolen_quantity" class="form-label text-dark">Stolen Quantity</label>
                                        <input type="number"
                                               name="stolen_quantity"
                                               id="stolen_quantity"
                                               class="form-control"
                                               min="0"
                                               value="{{ old('stolen_quantity', $stock->stolen_quantity) }}">
                                        @error('stolen_quantity')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="transfer_quantity" class="form-label text-dark">Transfer Quantity</label>
                                        <input type="number"
                                               name="transfer_quantity"
                                               id="transfer_quantity"
                                               class="form-control"
                                               min="0"
                                               value="{{ old('transfer_quantity', $stock->transfer_quantity) }}">
                                        @error('transfer_quantity')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="froze_quantity" class="form-label text-dark">Froze Quantity</label>
                                        <input type="number"
                                               name="froze_quantity"
                                               id="froze_quantity"
                                               class="form-control"
                                               min="0"
                                               value="{{ old('froze_quantity', $stock->froze_quantity) }}">
                                        @error('froze_quantity')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card calculation-card">
                                        <div class="card-body p-3">
                                            <h6 class="card-title text-dark">Stock Summary</h6>
                                            <div class="row">
                                                <div class="col-6">
                                                    <small class="text-muted">Total Price:</small><br>
                                                    <strong id="total-price" class="text-dark">৳{{ number_format($stock->total_price, 2) }}</strong>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted">Remaining:</small><br>
                                                    <strong id="remaining-qty" class="text-info">{{ $stock->remaining_quantity }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-content-save"></i> Update Stock
                                </button>
                                <a href="{{ route('admin.stockIndex') }}" class="btn btn-secondary">
                                    <i class="mdi mdi-cancel"></i> Cancel
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
    // Calculate total price and remaining quantity
    function updateCalculations() {
        const purchasePrice = parseFloat($('#purchase_price').val()) || 0;
        const quantity = parseInt($('#quantity').val()) || 0;
        const damageQty = parseInt($('#damage_quantity').val()) || 0;
        const soldQty = parseInt($('#sold_quantity').val()) || 0;
        const stolenQty = parseInt($('#stolen_quantity').val()) || 0;
        const transferQty = parseInt($('#transfer_quantity').val()) || 0;

        const totalPrice = purchasePrice * quantity;
        const remainingQty = quantity - damageQty - soldQty - stolenQty - transferQty;

        $('#total-price').text('$' + totalPrice.toFixed(2));
        $('#remaining-qty').text(remainingQty);

        // Change color based on remaining quantity
        if (remainingQty < 0) {
            $('#remaining-qty').removeClass('text-info text-success').addClass('text-danger');
        } else if (remainingQty === 0) {
            $('#remaining-qty').removeClass('text-info text-danger').addClass('text-warning');
        } else {
            $('#remaining-qty').removeClass('text-danger text-warning').addClass('text-success');
        }
    }

    // Update product info when product is changed
    $('#product_id').change(function() {
        const option = $(this).find('option:selected');
        const infoContainer = $('#product-info');

        if (option.val()) {
            const color = option.data('color');
            const unit = option.data('unit');
            infoContainer.text(`Color: ${color} | Unit: ${unit}`);
        } else {
            infoContainer.text('');
        }
    });

    // Bind calculation update to relevant fields
    $('#purchase_price, #quantity, #damage_quantity, #sold_quantity, #stolen_quantity, #transfer_quantity').on('input', updateCalculations);

    // Initial calculation
    updateCalculations();
});
</script>
@endpush

@section('title', 'Edit Stock')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Edit Stock</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.stockIndex') }}">Stock</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="mdi mdi-package-variant-edit"></i>
                            Edit Stock Entry
                        </h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.stockIndex') }}" class="btn btn-secondary">
                                <i class="mdi mdi-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>

                    <form action="{{ route('admin.stockUpdate', $stock) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="batch_id" class="form-label">Batch ID</label>
                                        <input type="text"
                                               class="form-control"
                                               id="batch_id"
                                               value="{{ $stock->batch_id }}"
                                               readonly>
                                        <small class="text-muted">Batch ID cannot be changed</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="product_id" class="form-label">Product <span class="text-danger">*</span></label>
                                        <select name="product_id" id="product_id" class="form-control" required>
                                            <option value="">Select Product</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}"
                                                        {{ $stock->product_id == $product->id ? 'selected' : '' }}
                                                        data-color="{{ $product->color->name ?? 'N/A' }}"
                                                        data-unit="{{ $product->unit->name ?? 'N/A' }}">
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small id="product-info" class="text-muted">
                                            @if($stock->product)
                                                Color: {{ $stock->product->color->name ?? 'N/A' }} |
                                                Unit: {{ $stock->product->unit->name ?? 'N/A' }}
                                            @endif
                                        </small>
                                        @error('product_id')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="purchase_price" class="form-label">Purchase Price <span class="text-danger">*</span></label>
                                        <input type="number"
                                               name="purchase_price"
                                               id="purchase_price"
                                               class="form-control"
                                               step="0.01"
                                               min="0"
                                               value="{{ old('purchase_price', $stock->purchase_price) }}"
                                               required>
                                        @error('purchase_price')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                        <input type="number"
                                               name="quantity"
                                               id="quantity"
                                               class="form-control"
                                               min="1"
                                               value="{{ old('quantity', $stock->quantity) }}"
                                               required>
                                        @error('quantity')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="sell_price" class="form-label">Sell Price <span class="text-danger">*</span></label>
                                        <input type="number"
                                               name="sell_price"
                                               id="sell_price"
                                               class="form-control"
                                               step="0.01"
                                               min="0"
                                               value="{{ old('sell_price', $stock->sell_price) }}"
                                               required>
                                        @error('sell_price')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="damage_quantity" class="form-label">Damage Quantity</label>
                                        <input type="number"
                                               name="damage_quantity"
                                               id="damage_quantity"
                                               class="form-control"
                                               min="0"
                                               value="{{ old('damage_quantity', $stock->damage_quantity) }}">
                                        @error('damage_quantity')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="sold_quantity" class="form-label">Sold Quantity</label>
                                        <input type="number"
                                               name="sold_quantity"
                                               id="sold_quantity"
                                               class="form-control"
                                               min="0"
                                               value="{{ old('sold_quantity', $stock->sold_quantity) }}">
                                        @error('sold_quantity')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="stolen_quantity" class="form-label">Stolen Quantity</label>
                                        <input type="number"
                                               name="stolen_quantity"
                                               id="stolen_quantity"
                                               class="form-control"
                                               min="0"
                                               value="{{ old('stolen_quantity', $stock->stolen_quantity) }}">
                                        @error('stolen_quantity')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="transfer_quantity" class="form-label">Transfer Quantity</label>
                                        <input type="number"
                                               name="transfer_quantity"
                                               id="transfer_quantity"
                                               class="form-control"
                                               min="0"
                                               value="{{ old('transfer_quantity', $stock->transfer_quantity) }}">
                                        @error('transfer_quantity')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="hidden" name="status" value="0">
                                            <input type="checkbox"
                                                   name="status"
                                                   value="1"
                                                   class="form-check-input"
                                                   id="status"
                                                   {{ old('status', $stock->status) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="status">
                                                Active Status
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-info">
                                        <div class="card-body p-3">
                                            <h6 class="card-title">Stock Summary</h6>
                                            <div class="row">
                                                <div class="col-6">
                                                    <small class="text-muted">Total Price:</small><br>
                                                    <strong id="total-price">${{ number_format($stock->total_price, 2) }}</strong>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted">Remaining:</small><br>
                                                    <strong id="remaining-qty" class="text-info">{{ $stock->remaining_quantity }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-content-save"></i> Update Stock
                            </button>
                            <a href="{{ route('admin.stockIndex') }}" class="btn btn-secondary">
                                <i class="mdi mdi-cancel"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Calculate total price and remaining quantity
    function updateCalculations() {
        const purchasePrice = parseFloat($('#purchase_price').val()) || 0;
        const quantity = parseInt($('#quantity').val()) || 0;
        const damageQty = parseInt($('#damage_quantity').val()) || 0;
        const soldQty = parseInt($('#sold_quantity').val()) || 0;
        const stolenQty = parseInt($('#stolen_quantity').val()) || 0;
        const transferQty = parseInt($('#transfer_quantity').val()) || 0;

        const totalPrice = purchasePrice * quantity;
        const remainingQty = quantity - damageQty - soldQty - stolenQty - transferQty;

        $('#total-price').text('$' + totalPrice.toFixed(2));
        $('#remaining-qty').text(remainingQty);

        // Change color based on remaining quantity
        if (remainingQty < 0) {
            $('#remaining-qty').removeClass('text-info text-success').addClass('text-danger');
        } else if (remainingQty === 0) {
            $('#remaining-qty').removeClass('text-info text-danger').addClass('text-warning');
        } else {
            $('#remaining-qty').removeClass('text-danger text-warning').addClass('text-success');
        }
    }

    // Update product info when product is changed
    $('#product_id').change(function() {
        const option = $(this).find('option:selected');
        const infoContainer = $('#product-info');

        if (option.val()) {
            const color = option.data('color');
            const unit = option.data('unit');
            infoContainer.text(`Color: ${color} | Unit: ${unit}`);
        } else {
            infoContainer.text('');
        }
    });

    // Bind calculation update to relevant fields
    $('#purchase_price, #quantity, #damage_quantity, #sold_quantity, #stolen_quantity, #transfer_quantity').on('input', updateCalculations);

    // Initial calculation
    updateCalculations();
});
</script>
@endpush
