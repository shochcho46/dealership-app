@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Assets</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Assets</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Asset List</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.assetCreate') }}" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-plus"></i> Add Asset
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
                        <table class="table table-bordered table-striped" id="assetTable">
                            <thead>
                                <tr>
                                    <th width="5%">SL</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th width="15%">Type</th>
                                    <th width="12%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assets as $key => $asset)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $asset->name }}</td>
                                    <td>৳ {{ number_format($asset->price, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $asset->type_badge_class }}">
                                            {{ $asset->type_text }}
                                        </span>
                                    </td>

                                    <td>
                                        <a href="{{ route('admin.assetEdit', $asset->id) }}"
                                           class="btn btn-warning btn-sm" title="Edit">
                                            <i class="mdi mdi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm delete-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteModal"
                                                data-url="{{ route('admin.assetDestroy', $asset->id) }}"
                                                title="Delete">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No assets found</td>
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
$(document).ready(function() {

    // Delete Button - Set form action dynamically
    $('.delete-btn').on('click', function() {
        const deleteUrl = $(this).data('url');
        $('#deleteForm').attr('action', deleteUrl);
    });
});
</script>
@endpush
