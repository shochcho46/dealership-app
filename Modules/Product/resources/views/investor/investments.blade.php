@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Investment Details - {{ $investor->name }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.investorIndex') }}">Investors</a></li>
                        <li class="breadcrumb-item active">Investments</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Investor Summary Card -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Investor:</strong> {{ $investor->name }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Company:</strong> {{ $investor->company ?? 'N/A' }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Total Investment:</strong> ৳ {{ number_format($investor->total_investment, 2) }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Status:</strong>
                                    <span class="badge badge-{{ $investor->status ? 'success' : 'danger' }}">
                                        {{ $investor->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Investment List Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Investment List</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addInvestmentModal">
                            <i class="mdi mdi-plus"></i> Add Investment
                        </button>
                        <a href="{{ route('admin.investorIndex') }}" class="btn btn-secondary btn-sm">
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

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="investmentTable">
                            <thead>
                                <tr>
                                    <th width="5%">SL</th>
                                    <th>Investment Date</th>
                                    <th>Amount</th>
                                    <th>Invoice</th>
                                    <th width="10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($investments as $key => $investment)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ date('d M Y', strtotime($investment->investment_date)) }}</td>
                                    <td>৳ {{ number_format($investment->amount, 2) }}</td>
                                    <td>
                                        @if($investment->hasMedia('investment_invoice'))
                                            <a href="{{ $investment->getFirstMediaUrl('investment_invoice') }}"
                                               target="_blank"
                                               class="btn btn-sm btn-info">
                                                <i class="mdi mdi-file-document"></i> View
                                            </a>
                                        @else
                                            <span class="text-muted">No invoice</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button"
                                                class="btn btn-danger btn-sm delete-investment-btn delete-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteModal"
                                                data-url="{{ route('admin.investorInvestmentDestroy', $investment->id) }}"
                                                title="Delete">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No investments found</td>
                                </tr>
                                @endforelse

                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-right">Total:</th>
                                    <th>৳ {{ number_format($investments->sum('amount'), 2) }}</th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                @include('components.delete')
            </div>
        </div>
    </section>
</div>

<!-- Add Investment Modal -->
<div class="modal fade" id="addInvestmentModal" tabindex="-1" role="dialog" aria-labelledby="addInvestmentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.investorInvestmentStore', $investor->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addInvestmentModalLabel">Add Investment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
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

                    <div class="form-group">
                        <label for="investment_date">Investment Date <span class="text-danger">*</span></label>
                        <input type="date"
                               class="form-control @error('investment_date') is-invalid @enderror"
                               id="investment_date"
                               name="investment_date"
                               value="{{ old('investment_date', date('Y-m-d')) }}"
                               required>
                        @error('investment_date')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="invoice">Invoice/Document (Optional)</label>
                        <div class="custom-file">
                            <input type="file"
                                   class="custom-file-input @error('invoice') is-invalid @enderror"
                                   id="invoice"
                                   name="invoice"
                                   accept=".jpg,.jpeg,.png,.pdf">
                            <label class="custom-file-label" for="invoice">Choose file</label>
                        </div>
                        <small class="form-text text-muted">Accepted formats: JPG, PNG, PDF (Max: 5MB)</small>
                        @error('invoice')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="mdi mdi-cancel"></i> Close
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save"></i> Save Investment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#investmentTable').DataTable({
        "responsive": true,
        "autoWidth": false,
        "order": [[1, "desc"]] // Sort by date descending
    });

    // Custom file input label
    $('.custom-file-input').on('change', function() {
        const fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });

    // Show modal if there are validation errors
    @if($errors->any())
        var addModal = new bootstrap.Modal(document.getElementById('addInvestmentModal'));
        addModal.show();
    @endif

    // Delete Investment Button - Set form action dynamically

});
</script>
@endpush
