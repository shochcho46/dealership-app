@extends('layouts.app')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Collection Details</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('dsr-collections.index') }}">Vendor Collection</a></li>
                    <li class="breadcrumb-item active">Details</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">

                <div class="mb-3">
                    <a href="{{ route('dsr-collections.index') }}" class="btn btn-outline-secondary">
                        <i class="mdi mdi-arrow-left"></i> Back to List
                    </a>
                    <a href="{{ route('dsr-collections.create') }}" class="btn btn-success ms-2">
                        <i class="mdi mdi-plus"></i> New Collection
                    </a>
                </div>

                <!-- Collection Info -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="mdi mdi-cash-check me-1"></i> Collection Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <th style="width:40%">Collection Date</th>
                                        <td>{{ $dsrCollection->collection_date ? $dsrCollection->collection_date->format('d M Y') : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Amount</th>
                                        <td>
                                            <span class="badge bg-success fs-6">
                                                ৳{{ number_format($dsrCollection->amount, 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Payment Method</th>
                                        <td>{{ $dsrCollection->paymentMethod->account_name ?? ($dsrCollection->paymentMethod->name ?? 'N/A') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Note</th>
                                        <td>{{ $dsrCollection->note ?: '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <th style="width:40%">Deposited By</th>
                                        <td>
                                            @if($dsrCollection->depositeBy)
                                                <strong>{{ $dsrCollection->depositeBy->name }}</strong><br>
                                                <small class="text-muted">{{ $dsrCollection->depositeBy->email }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Created By</th>
                                        <td>
                                            @if($dsrCollection->createdBy)
                                                <strong>{{ $dsrCollection->createdBy->name }}</strong><br>
                                                <small class="text-muted">{{ $dsrCollection->createdBy->email }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Recorded At</th>
                                        <td>{{ $dsrCollection->created_at->format('d M Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vendor Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="mdi mdi-account-tie me-1"></i> Vendor Details</h5>
                    </div>
                    <div class="card-body">
                        @if($dsrCollection->vendor)
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless table-sm">
                                        <tr>
                                            <th style="width:40%">Shop Name</th>
                                            <td><strong>{{ $dsrCollection->vendor->shop_name }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Contact Person</th>
                                            <td>{{ $dsrCollection->vendor->contact_person ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Mobile</th>
                                            <td>{{ $dsrCollection->vendor->mobile ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Address</th>
                                            <td>{{ $dsrCollection->vendor->full_address ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Total Due</th>
                                            <td class="text-danger"> ৳{{ $dsrCollection->vendor->due_balance ?? '-' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        @else
                            <p class="text-muted">Vendor information not available.</p>
                        @endif
                    </div>
                </div>

                <!-- Delete -->
                <div class="card border-danger">
                    <div class="card-body">
                        <button type="button" class="btn btn-outline-danger delete-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteModal"
                            data-url="{{ route('dsr-collections.destroy', $dsrCollection) }}">
                            <i class="mdi mdi-delete"></i> Delete This Record
                        </button>
                    </div>
                </div>

            </div>
        </div>

        @include('components.delete')
    </div>
</div>
@endsection
