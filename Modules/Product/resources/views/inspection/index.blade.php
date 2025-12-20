@extends('layouts.app')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Stock Inspection</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Inspections</li>
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
                    <h1 class="mt-3">Inspection List</h1>
                    <div class="text-end">
                        <a href="{{ route('admin.inspectionCreate') }}" class="btn btn-primary">
                            <span class="mdi mdi-plus"></span> Add Inspection
                        </a>
                    </div>
                </div>

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

                <div class="card card-primary card-outline mb-4">
                    <div class="card-header">
                        <div class="card-title">All Inspections</div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Inspection Number</th>
                                        <th>Inspection Date</th>
                                        <th>Total Damage Qty</th>
                                        <th>Damage Amount</th>
                                        <th>Total Lost Qty</th>
                                        <th>Lost Amount</th>
                                        <th>Total Amount</th>
                                        <th>Inspected By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($inspections as $inspection)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <strong class="text-primary">{{ $inspection->inspection_number }}</strong>
                                            </td>
                                            <td>{{ $inspection->inspection_date->format('d M, Y') }}</td>
                                            <td>
                                                <span class="badge bg-danger">{{ $inspection->total_damage_qty }}</span>
                                            </td>
                                            <td>৳{{ number_format($inspection->total_damage_amount, 2) }}</td>
                                            <td>
                                                <span class="badge bg-warning">{{ $inspection->total_lost_qty }}</span>
                                            </td>
                                            <td>৳{{ number_format($inspection->total_lost_amount, 2) }}</td>
                                            <td>
                                                <strong class="text-danger">
                                                    ৳{{ number_format($inspection->total_damage_amount + $inspection->total_lost_amount, 2) }}
                                                </strong>
                                            </td>
                                            <td>{{ $inspection->inspectedBy->name ?? 'N/A' }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.inspectionShow', $inspection) }}"
                                                       class="btn btn-sm btn-outline-info"
                                                       title="View Details">
                                                        <span class="mdi mdi-eye"></span>
                                                    </a>
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-danger delete-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteModal"
                                                            data-url="{{ route('admin.inspectionDestroy', $inspection) }}"
                                                            title="Delete">
                                                        <span class="mdi mdi-delete"></span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center">No inspections found</td>
                                        </tr>
                                    @endforelse
                                    @include('components.delete')
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end">
                        {{ $inspections->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
