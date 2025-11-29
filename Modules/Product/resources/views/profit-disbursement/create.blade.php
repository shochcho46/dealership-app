@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Add Profit Disbursement</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.profitDisbursementIndex') }}">Profit Disbursements</a></li>
                        <li class="breadcrumb-item active">Add</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Disbursement Information</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.profitDisbursementIndex') }}" class="btn btn-secondary btn-sm">
                            <i class="mdi mdi-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <form action="{{ route('admin.profitDisbursementStore') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="investor_id">Investor <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('investor_id') is-invalid @enderror"
                                            id="investor_id"
                                            name="investor_id"
                                            required>
                                        <option value="">Select Investor</option>
                                        @foreach($investors as $investor)
                                            <option value="{{ $investor->id }}"
                                                    {{ old('investor_id') == $investor->id ? 'selected' : '' }}>
                                                {{ $investor->name }} {{ $investor->company ? '(' . $investor->company . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('investor_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount">Amount <span class="text-danger">*</span></label>
                                    <input type="number"
                                           class="form-control @error('amount') is-invalid @enderror"
                                           id="amount"
                                           name="amount"
                                           step="0.01"
                                           value="{{ old('amount') }}"
                                           placeholder="Enter amount"
                                           required>
                                    @error('amount')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="disbursement_date">Disbursement Date <span class="text-danger">*</span></label>
                                    <input type="date"
                                           class="form-control @error('disbursement_date') is-invalid @enderror"
                                           id="disbursement_date"
                                           name="disbursement_date"
                                           value="{{ old('disbursement_date', date('Y-m-d')) }}"
                                           required>
                                    @error('disbursement_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="note">Note</label>
                                    <textarea class="form-control @error('note') is-invalid @enderror"
                                              id="note"
                                              name="note"
                                              rows="1"
                                              placeholder="Enter note">{{ old('note') }}</textarea>
                                    @error('note')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Investor Info Display -->
                        <div id="investorInfo" class="row" style="display: none;">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <h6>Investor Information:</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Total Investment:</strong> <span id="totalInvestment">৳ 0.00</span>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Total Disbursed:</strong> <span id="totalDisbursed">৳ 0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-content-save"></i> Save
                        </button>
                        <a href="{{ route('admin.profitDisbursementIndex') }}" class="btn btn-secondary">
                            <i class="mdi mdi-cancel"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection

@push('custome-js')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    // Investor change event - fetch investor details
    $('#investor_id').on('change', function() {
        const investorId = $(this).val();

        if (investorId) {
            $.ajax({
                url: `/admin/investor/${investorId}`,
                type: 'GET',
                success: function(response) {
                    $('#totalInvestment').text('৳ ' + parseFloat(response.total_investment).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    $('#totalDisbursed').text('৳ ' + parseFloat(response.total_disbursed_profit).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    $('#investorInfo').slideDown();
                },
                error: function(xhr) {
                    $('#investorInfo').hide();
                }
            });
        } else {
            $('#investorInfo').hide();
        }
    });
});
</script>
@endpush
