@extends('layouts.app')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Edit Transaction</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.bankAccountDetailIndex') }}">Transactions</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Transaction Information</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.bankAccountDetailIndex') }}" class="btn btn-secondary btn-sm">
                        <i class="mdi mdi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            <form action="{{ route('admin.bankAccountDetailUpdate', $bankAccountDetail) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bank_id">Bank <span class="text-danger">*</span></label>
                                <select class="form-control @error('bank_id') is-invalid @enderror"
                                        id="bank_id"
                                        name="bank_id"
                                        required>
                                    <option value="">Select Bank</option>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->id }}" {{ old('bank_id', $bankAccountDetail->bank_id) == $bank->id ? 'selected' : '' }}>
                                            {{ $bank->bank_name }} - {{ $bank->account_number }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('bank_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="type">Transaction Type <span class="text-danger">*</span></label>
                                <select class="form-control @error('type') is-invalid @enderror"
                                        id="type"
                                        name="type"
                                        required>
                                    <option value="">Select Type</option>
                                    <option value="1" {{ old('type', $bankAccountDetail->type) == 1 ? 'selected' : '' }}>Credit (+)</option>
                                    <option value="2" {{ old('type', $bankAccountDetail->type) == 2 ? 'selected' : '' }}>Debit (-)</option>
                                </select>
                                @error('type')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount">Amount <span class="text-danger">*</span></label>
                                <input type="number"
                                       class="form-control @error('amount') is-invalid @enderror"
                                       id="amount"
                                       name="amount"
                                       step="0.01"
                                       value="{{ old('amount', $bankAccountDetail->amount) }}"
                                       placeholder="Enter amount"
                                       required>
                                @error('amount')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="transaction_date">Transaction Date <span class="text-danger">*</span></label>
                                <input type="date"
                                       class="form-control @error('transaction_date') is-invalid @enderror"
                                       id="transaction_date"
                                       name="transaction_date"
                                       value="{{ old('transaction_date', $bankAccountDetail->transaction_date->format('Y-m-d')) }}"
                                       required>
                                @error('transaction_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="note">Note</label>
                                <textarea class="form-control @error('note') is-invalid @enderror"
                                          id="note"
                                          name="note"
                                          rows="3"
                                          placeholder="Enter transaction note">{{ old('note', $bankAccountDetail->note) }}</textarea>
                                @error('note')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <strong>Created By:</strong> {{ $bankAccountDetail->creator->name ?? 'System' }}
                                <br>
                                <strong>Created At:</strong> {{ $bankAccountDetail->created_at->format('d M Y h:i A') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save"></i> Update
                    </button>
                    <a href="{{ route('admin.bankAccountDetailIndex') }}" class="btn btn-secondary">
                        <i class="mdi mdi-cancel"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Type change event - update amount field color
    $('#type').on('change', function() {
        const type = $(this).val();
        const amountField = $('#amount');

        if (type == '1') {
            amountField.removeClass('border-danger').addClass('border-success');
        } else if (type == '2') {
            amountField.removeClass('border-success').addClass('border-danger');
        } else {
            amountField.removeClass('border-success border-danger');
        }
    });

    // Trigger on page load
    $('#type').trigger('change');
});
</script>
@endpush
