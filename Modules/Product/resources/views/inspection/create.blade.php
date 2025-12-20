@extends('layouts.app')

@push('custome-css')
<style>
    .product-image {
        width: 40px;
        height: 40px;
        border-radius: 5px;
        object-fit: cover;
    }

    .form-control-sm {
        font-size: 0.875rem;
    }

    .table-responsive {
        max-height: 600px;
    }

    .sticky-header thead {
        position: sticky;
        top: 0;
        background: white;
        z-index: 10;
    }

    .summary-card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .auto-calc {
        background-color: #e9ecef;
    }

    .qty-input {
        width: 100px;
    }

    .amount-display {
        font-weight: bold;
        color: #dc3545;
    }
</style>
@endpush

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">New Stock Inspection</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.inspectionIndex') }}">Inspections</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('admin.inspectionStore') }}" method="POST" id="inspectionForm">
            @csrf

            <!-- Inspection Details -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="mdi mdi-information"></i> Inspection Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="inspection_date" class="form-label">Inspection Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('inspection_date') is-invalid @enderror"
                                       id="inspection_date" name="inspection_date"
                                       value="{{ old('inspection_date', now()->format('Y-m-d')) }}"
                                       max="{{ now()->format('Y-m-d') }}" required>
                                @error('inspection_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror"
                                          id="notes" name="notes" rows="2"
                                          placeholder="Add any inspection notes here...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card summary-card bg-danger text-white">
                        <div class="card-body">
                            <h6>Total Damage Qty</h6>
                            <h3 id="total_damage_qty">0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card summary-card bg-warning text-white">
                        <div class="card-body">
                            <h6>Total Lost Qty</h6>
                            <h3 id="total_lost_qty">0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card summary-card bg-info text-white">
                        <div class="card-body">
                            <h6>Total Damage Amount</h6>
                            <h3>৳<span id="total_damage_amount">0.00</span></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card summary-card bg-dark text-white">
                        <div class="card-body">
                            <h6>Total Lost Amount</h6>
                            <h3>৳<span id="total_lost_amount">0.00</span></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stocks Table -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="mdi mdi-package-variant"></i> Stock-Wise Inspection ({{ $stocks->count() }} Stock Items)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover sticky-header" id="inspectionTable">
                            <thead class="">
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th style="width: 50px;">Image</th>
                                    <th style="width: 180px;">Product Name</th>
                                    <th style="width: 80px;">Stock ID</th>
                                    <th style="width: 100px;">Purchase Price</th>
                                    <th style="width: 90px;">System Qty</th>
                                    <th style="width: 100px;">Physical Qty</th>
                                    <th style="width: 80px;">Variance</th>
                                    <th style="width: 90px;">Damage Qty</th>
                                    <th style="width: 110px;">Damage Amt</th>
                                    <th style="width: 80px;">Lost Qty</th>
                                    <th style="width: 110px;">Lost Amt</th>
                                    <th style="width: 150px;">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stocks as $index => $stock)
                                    <tr data-index="{{ $index }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <img src="{{ $stock['product_image'] }}" alt="{{ $stock['product_name'] }}" class="product-image">
                                        </td>
                                        <td>
                                            <strong>{{ $stock['product_name'] }}</strong>
                                            @if($stock['manufacture_date'] || $stock['expire_date'])
                                                <br>
                                                <small class="text-muted">
                                                    @if($stock['manufacture_date'])
                                                        <span class="badge bg-secondary">Mfg: {{ $stock['manufacture_date']->format('d/m/y') }}</span>
                                                    @endif
                                                    @if($stock['expire_date'])
                                                        <span class="badge bg-warning">Exp: {{ $stock['expire_date']->format('d/m/y') }}</span>
                                                    @endif
                                                </small>
                                            @endif
                                            <input type="hidden" name="stocks[{{ $index }}][stock_id]" value="{{ $stock['stock_id'] }}">
                                            <input type="hidden" name="stocks[{{ $index }}][product_id]" value="{{ $stock['product_id'] }}">
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $stock['stock_id'] }}</span>
                                        </td>
                                        <td>
                                            <input type="number"
                                                   class="form-control form-control-sm auto-calc text-end purchase-price"
                                                   name="stocks[{{ $index }}][purchase_price]"
                                                   value="{{ number_format($stock['purchase_price'], 2, '.', '') }}"
                                                   step="0.01"
                                                   data-index="{{ $index }}"
                                                   readonly>
                                        </td>
                                        <td>
                                            <input type="number"
                                                   class="form-control form-control-sm auto-calc text-center"
                                                   name="stocks[{{ $index }}][system_qty]"
                                                   value="{{ $stock['system_qty'] }}"
                                                   readonly>
                                        </td>
                                        <td>
                                            <input type="number"
                                                   class="form-control form-control-sm text-center physical-qty"
                                                   name="stocks[{{ $index }}][physical_qty]"
                                                   value="{{ $stock['system_qty'] }}"
                                                   min="0"
                                                   data-index="{{ $index }}"
                                                   required>
                                        </td>
                                        <td>
                                            <input type="number"
                                                   class="form-control form-control-sm auto-calc text-center variance-qty"
                                                   data-index="{{ $index }}"
                                                   value="0"
                                                   readonly>
                                        </td>
                                        <td>
                                            <input type="number"
                                                   class="form-control form-control-sm text-center damage-qty"
                                                   name="stocks[{{ $index }}][damage_qty]"
                                                   value="0"
                                                   min="0"
                                                   data-index="{{ $index }}">
                                        </td>
                                        <td>
                                            <input type="number"
                                                   class="form-control form-control-sm auto-calc damage-amount amount-display text-end"
                                                   data-index="{{ $index }}"
                                                   value="0"
                                                   step="0.01"
                                                   readonly>
                                        </td>
                                        <td>
                                            <input type="number"
                                                   class="form-control form-control-sm text-center lost-qty"
                                                   name="stocks[{{ $index }}][lost_qty]"
                                                   value="0"
                                                   min="0"
                                                   data-index="{{ $index }}">
                                        </td>
                                        <td>
                                            <input type="number"
                                                   class="form-control form-control-sm auto-calc lost-amount amount-display text-end"
                                                   data-index="{{ $index }}"
                                                   value="0"
                                                   step="0.01"
                                                   readonly>
                                        </td>
                                        <td>
                                            <input type="text"
                                                   class="form-control form-control-sm"
                                                   name="stocks[{{ $index }}][remarks]"
                                                   placeholder="Optional remarks">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.inspectionIndex') }}" class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-content-save"></i> Save Inspection
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('custome-js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Calculate variance, amounts and totals
        function calculateRow(index) {
            const row = document.querySelector(`tr[data-index="${index}"]`);
            const systemQty = parseFloat(row.querySelector(`[name="stocks[${index}][system_qty]"]`).value) || 0;
            const physicalQty = parseFloat(row.querySelector(`[name="stocks[${index}][physical_qty]"]`).value) || 0;
            const damageQty = parseFloat(row.querySelector(`[name="stocks[${index}][damage_qty]"]`).value) || 0;
            const lostQty = parseFloat(row.querySelector(`[name="stocks[${index}][lost_qty]"]`).value) || 0;
            const purchasePrice = parseFloat(row.querySelector(`[name="stocks[${index}][purchase_price]"]`).value) || 0;

            // Calculate variance
            const variance = physicalQty - systemQty;
            row.querySelector(`.variance-qty[data-index="${index}"]`).value = variance;

            // Calculate damage amount
            const damageAmount = damageQty * purchasePrice;
            row.querySelector(`.damage-amount[data-index="${index}"]`).value = damageAmount.toFixed(2);

            // Calculate lost amount
            const lostAmount = lostQty * purchasePrice;
            row.querySelector(`.lost-amount[data-index="${index}"]`).value = lostAmount.toFixed(2);

            calculateTotals();
        }

        function calculateTotals() {
            let totalDamageQty = 0;
            let totalLostQty = 0;
            let totalDamageAmount = 0;
            let totalLostAmount = 0;

            document.querySelectorAll('.damage-qty').forEach(input => {
                totalDamageQty += parseFloat(input.value) || 0;
            });

            document.querySelectorAll('.lost-qty').forEach(input => {
                totalLostQty += parseFloat(input.value) || 0;
            });

            document.querySelectorAll('.damage-amount').forEach(input => {
                totalDamageAmount += parseFloat(input.value) || 0;
            });

            document.querySelectorAll('.lost-amount').forEach(input => {
                totalLostAmount += parseFloat(input.value) || 0;
            });

            // Update summary cards
            document.getElementById('total_damage_qty').textContent = totalDamageQty;
            document.getElementById('total_lost_qty').textContent = totalLostQty;
            document.getElementById('total_damage_amount').textContent = totalDamageAmount.toFixed(2);
            document.getElementById('total_lost_amount').textContent = totalLostAmount.toFixed(2);
        }

        // Add event listeners
        document.querySelectorAll('.physical-qty, .damage-qty, .lost-qty').forEach(input => {
            input.addEventListener('input', function() {
                const index = this.dataset.index;
                calculateRow(index);
            });
        });

        // Initial calculation
        document.querySelectorAll('tr[data-index]').forEach(row => {
            const index = row.dataset.index;
            calculateRow(index);
        });

        // Form validation
        document.getElementById('inspectionForm').addEventListener('submit', function(e) {
            const totalDamageQty = parseFloat(document.getElementById('total_damage_qty').textContent) || 0;
            const totalLostQty = parseFloat(document.getElementById('total_lost_qty').textContent) || 0;

            if (totalDamageQty === 0 && totalLostQty === 0) {
                e.preventDefault();
                alert('Please enter at least one damage or lost quantity to create an inspection.');
                return false;
            }
        });
    });
</script>
@endpush
