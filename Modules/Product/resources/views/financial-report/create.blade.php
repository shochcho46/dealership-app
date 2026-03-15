@extends('layouts.app')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Add Financial Report</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.financialReportIndex') }}">Financial Reports</a></li>
                    <li class="breadcrumb-item active">Add</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Report Information</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.financialReportIndex') }}" class="btn btn-secondary btn-sm">
                        <i class="mdi mdi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            <form action="{{ route('admin.financialReportStore') }}" method="POST">
                @csrf
                <div class="card-body">
                    <!-- Period Section -->
                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="mb-3">Report Period</h5>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                <input type="date"
                                       class="form-control @error('start_date') is-invalid @enderror"
                                       id="start_date"
                                       name="start_date"
                                       value="{{ old('start_date') }}"
                                       required>
                                @error('start_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_date">End Date <span class="text-danger">*</span></label>
                                <input type="date"
                                       class="form-control @error('end_date') is-invalid @enderror"
                                       id="end_date"
                                       name="end_date"
                                       value="{{ old('end_date') }}"
                                       required>
                                @error('end_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Financial Figures Section -->
                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="mb-3">Financial Figures</h5>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="total_sales">Total Sales <span class="text-danger">*</span></label>
                                <input type="number"
                                       class="form-control @error('total_sales') is-invalid @enderror"
                                       id="total_sales"
                                       name="total_sales"
                                       step="0.01"
                                       value="{{ old('total_sales', 0) }}"
                                       placeholder="0.00"
                                       required>
                                @error('total_sales')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="actual_collected_amount">Actual Collected Amount</label>
                                <input type="number"
                                       class="form-control @error('actual_collected_amount') is-invalid @enderror"
                                       id="actual_collected_amount"
                                       name="actual_collected_amount"
                                       step="0.01"
                                       value="{{ old('actual_collected_amount', 0) }}"
                                       placeholder="0.00">
                                @error('actual_collected_amount')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="text-muted">Amount actually received from customers</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="amount_to_collect">Amount to Collect</label>
                                <input type="number"
                                       class="form-control bg-light"
                                       id="amount_to_collect"
                                       step="0.01"
                                       value="0.00"
                                       placeholder="0.00"
                                       readonly>
                                <small class="text-muted">Outstanding amount (auto-calculated)</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="total_purchase">Total Purchase <span class="text-danger">*</span></label>
                                <input type="number"
                                       class="form-control @error('total_purchase') is-invalid @enderror"
                                       id="total_purchase"
                                       name="total_purchase"
                                       step="0.01"
                                       value="{{ old('total_purchase', 0) }}"
                                       placeholder="0.00"
                                       required>
                                @error('total_purchase')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="total_expense">Total Expense <span class="text-danger">*</span></label>
                                <input type="number"
                                       class="form-control @error('total_expense') is-invalid @enderror"
                                       id="total_expense"
                                       name="total_expense"
                                       step="0.01"
                                       value="{{ old('total_expense', 0) }}"
                                       placeholder="0.00"
                                       required>
                                @error('total_expense')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="discount_amount">Discount Amount</label>
                                <input type="number"
                                       class="form-control @error('discount_amount') is-invalid @enderror"
                                       id="discount_amount"
                                       name="discount_amount"
                                       step="0.01"
                                       value="{{ old('discount_amount', 0) }}"
                                       placeholder="0.00">
                                @error('discount_amount')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="total_lost_amount">Total Lost Amount</label>
                                <input type="number"
                                       class="form-control @error('total_lost_amount') is-invalid @enderror"
                                       id="total_lost_amount"
                                       name="total_lost_amount"
                                       step="0.01"
                                       value="{{ old('total_lost_amount', 0) }}"
                                       placeholder="0.00">
                                @error('total_lost_amount')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="total_damage_amount">Total Damage Amount</label>
                                <input type="number"
                                       class="form-control @error('total_damage_amount') is-invalid @enderror"
                                       id="total_damage_amount"
                                       name="total_damage_amount"
                                       step="0.01"
                                       value="{{ old('total_damage_amount', 0) }}"
                                       placeholder="0.00">
                                @error('total_damage_amount')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="total_profit">Current Profit <span class="text-danger">*</span></label>
                                <input type="number"
                                       class="form-control @error('total_profit') is-invalid @enderror"
                                       id="total_profit"
                                       name="total_profit"
                                       step="0.01"
                                       value="{{ old('total_profit', 0) }}"
                                       placeholder="0.00"
                                       readonly>
                                @error('total_profit')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="text-muted">Collected - all expenses (auto-calculated)</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Profit Distribution Section -->
                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="mb-3">Profit Distribution</h5>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="profit_for_shareholders">Profit for Shareholders</label>
                                <input type="number"
                                       class="form-control @error('profit_for_shareholders') is-invalid @enderror"
                                       id="profit_for_shareholders"
                                       name="profit_for_shareholders"
                                       step="0.01"
                                       value="{{ old('profit_for_shareholders', 0) }}"
                                       placeholder="0.00">
                                @error('profit_for_shareholders')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="profit_for_sadaqah">Profit for Sadaqah</label>
                                <input type="number"
                                       class="form-control @error('profit_for_sadaqah') is-invalid @enderror"
                                       id="profit_for_sadaqah"
                                       name="profit_for_sadaqah"
                                       step="0.01"
                                       value="{{ old('profit_for_sadaqah', 0) }}"
                                       placeholder="0.00">
                                @error('profit_for_sadaqah')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="profit_to_retain">Profit to Retain</label>
                                <input type="number"
                                       class="form-control @error('profit_to_retain') is-invalid @enderror"
                                       id="profit_to_retain"
                                       name="profit_to_retain"
                                       step="0.01"
                                       value="{{ old('profit_to_retain', 0) }}"
                                       placeholder="0.00">
                                @error('profit_to_retain')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="remarks">Remarks</label>
                                <textarea class="form-control @error('remarks') is-invalid @enderror"
                                          id="remarks"
                                          name="remarks"
                                          rows="3"
                                          placeholder="Enter any additional notes">{{ old('remarks') }}</textarea>
                                @error('remarks')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save"></i> Save Report
                    </button>
                    <a href="{{ route('admin.financialReportIndex') }}" class="btn btn-secondary">
                        <i class="mdi mdi-cancel"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('custome-js')
<script>
$(document).ready(function() {
    // Calculate total profit automatically based on actual collected amount
    function calculateProfit() {
        const actualCollected = parseFloat($('#actual_collected_amount').val()) || 0;
        const purchase = parseFloat($('#total_purchase').val()) || 0;
        const expense = parseFloat($('#total_expense').val()) || 0;
        const discount = parseFloat($('#discount_amount').val()) || 0;
        const lost = parseFloat($('#total_lost_amount').val()) || 0;
        const damage = parseFloat($('#total_damage_amount').val()) || 0;

        const profit = actualCollected - purchase - expense - discount - lost - damage;
        $('#total_profit').val(profit.toFixed(2));
    }

    // Calculate amount to collect
    function calculateAmountToCollect() {
        const totalSales = parseFloat($('#total_sales').val()) || 0;
        const actualCollected = parseFloat($('#actual_collected_amount').val()) || 0;

        const toCollect = totalSales - actualCollected;
        $('#amount_to_collect').val(toCollect.toFixed(2));
    }

    // Attach event listeners
    $('#actual_collected_amount, #total_purchase, #total_expense, #discount_amount, #total_lost_amount, #total_damage_amount').on('input', calculateProfit);
    $('#total_sales, #actual_collected_amount').on('input', calculateAmountToCollect);

    // Calculate on page load
    calculateProfit();
    calculateAmountToCollect();
});
</script>
@endpush
@endsection
