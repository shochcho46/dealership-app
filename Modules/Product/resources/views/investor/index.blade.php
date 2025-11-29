@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Investors</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Investors</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Investor List</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.investorCreate') }}" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-plus"></i> Add Investor
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
                        <table class="table table-bordered table-striped" id="investorTable">
                            <thead>
                                <tr>
                                    <th width="5%">SL</th>
                                    <th>Name</th>
                                    <th>Company</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Total Investment</th>
                                    <th>Total Disbursed</th>
                                    <th width="8%">Status</th>
                                    <th width="15%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($investors as $key => $investor)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $investor->name }}</td>
                                    <td>{{ $investor->company ?? 'N/A' }}</td>
                                    <td>{{ $investor->start_date ? date('d M Y', strtotime($investor->start_date)) : 'N/A' }}</td>
                                    <td>{{ $investor->end_date ? date('d M Y', strtotime($investor->end_date)) : 'N/A' }}</td>
                                    <td>৳ {{ number_format($investor->total_investment, 2) }}</td>
                                    <td>৳ {{ number_format($investor->total_disbursed_profit, 2) }}</td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input status-toggle"
                                                type="checkbox"
                                                id="status{{ $investor->id }}"
                                                data-id="{{ $investor->id }}"
                                                {{ $investor->status ? 'checked' : '' }}>
                                            <label class="form-check-label" for="status{{ $investor->id }}"></label>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.investorInvestments', $investor->id) }}"
                                           class="btn btn-info btn-sm" title="Add Investment">
                                            <i class="mdi mdi-cash-plus"></i>
                                        </a>
                                        <a href="{{ route('admin.investorEdit', $investor->id) }}"
                                           class="btn btn-warning btn-sm" title="Edit">
                                            <i class="mdi mdi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm delete-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteModal"
                                                data-url="{{ route('admin.investorDestroy', $investor->id) }}"
                                                title="Delete">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">No investors found</td>
                                </tr>
                                @endforelse
                                @include('components.delete')
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('custome-js')
<script>
$(document).on('change', '.status-toggle', function () {

    const investorId = $(this).data('id');
    const status = $(this).is(':checked') ? 1 : 0;

    console.log('Sending AJAX for Investor:', investorId, 'Status:', status); // DEBUG

    $.ajax({
        url: '/admin/investor/' + investorId + '/status-update',
        type: 'POST', // ✅ MUST be POST
        data: {
            _token: '{{ csrf_token() }}',
            _method: 'PUT', // ✅ Laravel method spoofing
            status: status
        },
        success: function (response) {
            toastr.success('Status updated successfully');
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            toastr.error('Failed to update status');
        }
    });

});
</script>
@endpush
