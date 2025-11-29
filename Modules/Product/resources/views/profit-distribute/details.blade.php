@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Profit Distribution Details - {{ $profitDistribute->period_text }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.profitDistributeIndex') }}">Profit Distribution</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            <!-- Summary Card -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">{{ $profitDistribute->title }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Period:</strong> {{ $profitDistribute->period_text }}
                                </div>
                                <div class="col-md-2">
                                    <strong>Total Amount:</strong> ৳ {{ number_format($profitDistribute->total_amount, 2) }}
                                </div>
                                <div class="col-md-2">
                                    <strong class="text-success">Total Credit:</strong>
                                    <span class="text-success">৳ {{ number_format($profitDistribute->total_credit, 2) }}</span>
                                </div>
                                <div class="col-md-2">
                                    <strong class="text-danger">Total Debit:</strong>
                                    <span class="text-danger">৳ {{ number_format($profitDistribute->total_debit, 2) }}</span>
                                </div>
                                <div class="col-md-2">
                                    <strong>Balance:</strong>
                                    <span class="{{ $profitDistribute->balance >= 0 ? 'text-success' : 'text-danger' }}">
                                        ৳ {{ number_format($profitDistribute->balance, 2) }}
                                    </span>
                                </div>
                                <div class="col-md-1">
                                    <span class="badge bg-{{ $profitDistribute->status ? 'success' : 'secondary' }}">
                                        {{ $profitDistribute->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Details List Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Transaction Details</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDetailModal">
                            <i class="mdi mdi-plus"></i> Add Detail
                        </button>
                        <a href="{{ route('admin.profitDistributeIndex') }}" class="btn btn-secondary btn-sm">
                            <i class="mdi mdi-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="detailsTable">
                            <thead>
                                <tr>
                                    <th width="5%">SL</th>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th width="10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($details as $key => $detail)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ date('d M Y', strtotime($detail->date)) }}</td>
                                    <td>{{ $detail->description ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge {{ $detail->type_badge_class }}">
                                            {{ $detail->type_text }}
                                        </span>
                                    </td>
                                    <td class="{{ $detail->type == 1 ? 'text-success' : 'text-danger' }}">
                                        ৳ {{ number_format($detail->amount, 2) }}
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm delete-btn"
                                                data-url="{{ route('admin.profitDistributeDetailDestroy',  $detail->id) }}"
                                                title="Delete">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No details found</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">Total Credit:</th>
                                    <th class="text-success">৳ {{ number_format($details->where('type', 1)->sum('amount'), 2) }}</th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">Total Debit:</th>
                                    <th class="text-danger">৳ {{ number_format($details->where('type', 2)->sum('amount'), 2) }}</th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">Balance:</th>
                                    <th class="{{ $profitDistribute->balance >= 0 ? 'text-success' : 'text-danger' }}">
                                        ৳ {{ number_format($profitDistribute->balance, 2) }}
                                    </th>
                                    <th></th>
                                </tr>
                            </tfoot>
                            @include('components.delete')
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<!-- Add Detail Modal -->
<div class="modal fade" id="addDetailModal" tabindex="-1" aria-labelledby="addDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.profitDistributeDetailStore', $profitDistribute->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addDetailModalLabel">Add Transaction Detail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                            <option value="">Select Type</option>
                            <option value="1" {{ old('type') == 1 ? 'selected' : '' }}>Credit (+)</option>
                            <option value="2" {{ old('type') == 2 ? 'selected' : '' }}>Debit (-)</option>
                        </select>
                        @error('type')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" step="0.01" value="{{ old('amount') }}" placeholder="Enter amount" required>
                        @error('amount')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('date') is-invalid @enderror" id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                        @error('date')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Enter description">{{ old('description') }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="mdi mdi-cancel"></i> Close
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save"></i> Save Detail
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection

@push('custome-js')
<script>
$(document).ready(function() {

     $('.delete-btn').on('click', function() {
        const deleteUrl = $(this).data('url');
        const form = $('#deleteForm');
        form.attr('action', deleteUrl);
        form.submit();
    });

    // Show modal if there are validation errors
    @if($errors->any())
        var addModal = new bootstrap.Modal(document.getElementById('addDetailModal'));
        addModal.show();
    @endif

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

});


</script>
@endpush
