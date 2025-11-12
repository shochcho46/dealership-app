@extends('layouts.app')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Expense List Management</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.expenseListIndex') }}">Expense Lists</a>
                    </li>
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
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="mt-3">Create New Expense</h1>
                    <div class="text-end">
                        <a href="{{ route('admin.expenseListIndex') }}" class="btn btn-outline-primary">
                            <span class="mdi mdi-format-list-text"></span> View All Expenses
                        </a>
                    </div>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card card-primary card-outline mb-4">
                    <div class="card-header">
                        <div class="card-title">Expense Information</div>
                    </div>
                    <form action="{{ route('admin.expenseListStore') }}" method="post">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="expense_head_id" class="form-label">Expense Head <span class="text-danger">*</span></label>
                                        <select class="form-select @error('expense_head_id') is-invalid @enderror"
                                                id="expense_head_id" name="expense_head_id" required>
                                            <option value="">Select Expense Head</option>
                                            @foreach($expenseHeads as $expenseHead)
                                                <option value="{{ $expenseHead->id }}"
                                                        data-max-amount="{{ $expenseHead->max_amount }}"
                                                        data-total-expenses="{{ $expenseHead->total_expenses }}"
                                                        data-remaining="{{ $expenseHead->remaining_amount }}"
                                                        {{ old('expense_head_id') == $expenseHead->id ? 'selected' : '' }}>
                                                    {{ $expenseHead->title }}
                                                    (Max: ৳{{ number_format($expenseHead->max_amount, 2) }} |
                                                     Remaining: ৳{{ number_format($expenseHead->remaining_amount, 2) }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('expense_head_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small id="budgetInfo" class="form-text text-muted"></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Expense Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                                               id="title" name="title" placeholder="Enter expense title"
                                               value="{{ old('title') }}" required>
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror"
                                                  id="description" name="description" rows="3"
                                                  placeholder="Enter expense description">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="amount" class="form-label">Amount (৳) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0.01"
                                               class="form-control @error('amount') is-invalid @enderror"
                                               id="amount" name="amount" placeholder="Enter amount"
                                               value="{{ old('amount') }}" required>
                                        @error('amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small id="amountWarning" class="form-text text-danger"></small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="expense_date" class="form-label">Expense Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('expense_date') is-invalid @enderror"
                                               id="expense_date" name="expense_date"
                                               value="{{ old('expense_date', date('Y-m-d')) }}" required>
                                        @error('expense_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="reference_no" class="form-label">Reference No</label>
                                        <input type="text" class="form-control @error('reference_no') is-invalid @enderror"
                                               id="reference_no" name="reference_no" placeholder="Enter reference number"
                                               value="{{ old('reference_no') }}">
                                        @error('reference_no')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                            <option value="">Select Status</option>
                                            <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <span class="mdi mdi-content-save"></span> Save Expense
                            </button>
                            <a href="{{ route('admin.expenseListIndex') }}" class="btn btn-secondary">
                                <span class="mdi mdi-cancel"></span> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Update budget info when expense head is selected
        $('#expense_head_id').on('change', function() {
            const selectedOption = $(this).find(':selected');
            const remaining = parseFloat(selectedOption.data('remaining')) || 0;
            const maxAmount = parseFloat(selectedOption.data('max-amount')) || 0;

            if (selectedOption.val()) {
                $('#budgetInfo').html(`
                    <i class="mdi mdi-information"></i>
                    Maximum Budget: ৳${maxAmount.toFixed(2)} |
                    Available: ৳${remaining.toFixed(2)}
                `);
                if (remaining <= 0) {
                    $('#budgetInfo').removeClass('text-muted').addClass('text-danger');
                } else {
                    $('#budgetInfo').removeClass('text-danger').addClass('text-muted');
                }
                checkAmount();
            } else {
                $('#budgetInfo').html('');
            }
        });

        // Check amount against remaining budget
        $('#amount').on('input', function() {
            checkAmount();
        });

        function checkAmount() {
            const selectedOption = $('#expense_head_id').find(':selected');
            const remaining = parseFloat(selectedOption.data('remaining')) || 0;
            const amount = parseFloat($('#amount').val()) || 0;

            if (selectedOption.val() && amount > 0) {
                if (amount > remaining) {
                    $('#amountWarning').html(`
                        <i class="mdi mdi-alert"></i>
                        Warning: This amount exceeds the available budget by ৳${(amount - remaining).toFixed(2)}
                    `);
                } else {
                    $('#amountWarning').html('');
                }
            } else {
                $('#amountWarning').html('');
            }
        }

        // Trigger on page load if old value exists
        if ($('#expense_head_id').val()) {
            $('#expense_head_id').trigger('change');
        }
    });
</script>
@endsection
