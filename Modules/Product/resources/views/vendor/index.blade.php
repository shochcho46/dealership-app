@extends('layouts.app')

@push('custome-css')
<style>
    .vendor-image {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        border: 2px solid #dee2e6;
        object-fit: cover;
    }
</style>
@endpush

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Vendor Management</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Vendors</li>
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
                    <h1 class="mt-3">Vendor List</h1>
                    <div class="text-end">
                        <a href="{{ route('admin.vendorCreate') }}" class="btn btn-primary">
                            <span class="mdi mdi-plus"></span> Add New Vendor
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
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="card-title mb-0">All Vendors</div>
                        <form method="GET" action="{{ route("admin.vendorIndex")}}" class="d-flex" role="search">
                            <input type="text" name="search" class="form-control" placeholder="Search vendors..." value="{{ request('search') }}">
                            <button type="submit" class="btn btn-outline-primary m-1">Filter</button>
                        </form>
                    </div>
                    {{-- <div class="card-header">
                        <div class="card-title">All Vendors</div>
                    </div> --}}
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Image</th>
                                        <th>Shop Name</th>
                                        <th>Contact Person</th>
                                        <th>Mobile</th>
                                        <th>Email</th>
                                        <th>Country</th>
                                        <th>Old Due</th>
                                        <th>Due Balance</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($vendors as $vendor)
                                        <tr>
                                            <td>{{  $vendors->firstItem() + $loop->index }}</td>
                                            <td>
                                                <img src="{{ $vendor->vendor_image_thumb_url }}" alt="Vendor Image" class="vendor-image">
                                            </td>
                                            <td>{{ $vendor->shop_name }}</td>
                                            <td>{{ $vendor->contact_person ?? 'N/A' }}</td>
                                            <td>{{ $vendor->mobile }}</td>
                                            <td>{{ $vendor->email ?? 'N/A' }}</td>
                                            <td>{{ $vendor->country->name ?? 'N/A' }}</td>
                                            <td class="text-danger">{{ $vendor->old_due }}</td>
                                            <td class="text-danger"> ৳ {{ $vendor->due_balance }}</td>
                                            <td>
                                                @if($vendor->status)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('vendor.publicAccount', $vendor->uuid) }}"
                                                       class="btn btn-sm btn-outline-secondary"
                                                       title="View Account"
                                                       target="_blank">
                                                        <span class="mdi mdi-web"></span>
                                                    </a>

                                                    <a href="{{ route('admin.vendorAccount', $vendor->uuid) }}"
                                                       class="btn btn-sm btn-outline-info"
                                                       title="View Account">
                                                        <span class="mdi mdi-wallet"></span>
                                                    </a>

                                                    <a href="{{ route('admin.vendorEdit', $vendor) }}"
                                                       class="btn btn-sm btn-outline-primary"
                                                       title="Edit">
                                                        <span class="mdi mdi-pencil"></span>
                                                    </a>
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-danger delete-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteModal"
                                                            data-url="{{ route('admin.vendorDestroy', $vendor) }}"
                                                            title="Delete">
                                                        <span class="mdi mdi-delete"></span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center">No vendors found</td>
                                        </tr>
                                    @endforelse
                                    @include('components.delete')
                                </tbody>
                                <tfoot class="table-secondary">

                                    <tr>
                                        <th colspan="7" class="text-end">Current Page Total:</th>
                                        <th class="text-danger"> ৳ {{ number_format($pageOldDue, 2) }}</th>
                                        <th class="text-danger"> ৳ {{ number_format($pageDueBalance, 2) }}</th>
                                        <th colspan="2"></th>
                                    </tr>

                                    <tr>
                                        <th colspan="7" class="text-end">All Page Total:</th>
                                        <th class="text-danger"> ৳ {{ number_format($overallOldDue, 2) }}</th>
                                        <th class="text-danger"> ৳ {{ number_format($overallDueBalance, 2) }}</th>
                                        <th colspan="2"></th>
                                    </tr>

                            </tfoot>

                            </table>
                        </div>
                    </div>
                        <div class="card-footer d-flex justify-content-end">
                            {{ $vendors->links() }}
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('custome-js')

@endpush
