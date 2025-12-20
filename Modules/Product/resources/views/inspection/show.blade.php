@extends('layouts.app')

@push('custome-css')
<style>
    .product-image {
        width: 50px;
        height: 50px;
        border-radius: 5px;
        object-fit: cover;
    }

    .inspection-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .summary-card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .print-btn {
        background: white;
        color: #667eea;
    }
</style>
@endpush

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Inspection Details</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.inspectionIndex') }}">Inspections</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Details</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <!-- Inspection Header -->
        <div class="inspection-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-1">{{ $inspection->inspection_number }}</h2>
                    <p class="mb-0">
                        <i class="mdi mdi-calendar"></i> {{ $inspection->inspection_date->format('d M, Y') }}
                        <span class="mx-2">|</span>
                        <i class="mdi mdi-account"></i> Inspected by: {{ $inspection->inspectedBy->name ?? 'N/A' }}
                    </p>
                    @if($inspection->notes)
                        <p class="mb-0 mt-2"><i class="mdi mdi-note-text"></i> {{ $inspection->notes }}</p>
                    @endif
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('admin.inspectionIndex') }}" class="btn print-btn">
                        <i class="mdi mdi-arrow-left"></i> Back to List
                    </a>

                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card summary-card bg-danger text-white">
                    <div class="card-body">
                        <h6>Total Damage Qty</h6>
                        <h3>{{ $inspection->total_damage_qty }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card bg-warning text-white">
                    <div class="card-body">
                        <h6>Total Lost Qty</h6>
                        <h3>{{ $inspection->total_lost_qty }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card bg-info text-white">
                    <div class="card-body">
                        <h6>Total Damage Amount</h6>
                        <h3>৳{{ number_format($inspection->total_damage_amount, 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card bg-dark text-white">
                    <div class="card-body">
                        <h6>Total Lost Amount</h6>
                        <h3>৳{{ number_format($inspection->total_lost_amount, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inspection Items -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="mdi mdi-package-variant"></i> Inspection Items ({{ $inspection->items->count() }} items with issues)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="">
                            <tr>
                                <th>#</th>
                                <th>Image</th>
                                <th>Product Name</th>
                                <th>System Qty</th>
                                <th>Physical Qty</th>
                                <th>Variance</th>
                                <th>Damage Qty</th>
                                <th>Damage Amount</th>
                                <th>Lost Qty</th>
                                <th>Lost Amount</th>
                                <th>Avg. Price</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inspection->items as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <img src="{{ $item->product->product_image_thumb_url }}"
                                             alt="{{ $item->product->name }}"
                                             class="product-image">
                                    </td>
                                    <td><strong>{{ $item->product->name }}</strong></td>
                                    <td>{{ $item->system_qty }}</td>
                                    <td>{{ $item->physical_qty }}</td>
                                    <td>
                                        <span class="badge {{ $item->variance_qty < 0 ? 'bg-danger' : ($item->variance_qty > 0 ? 'bg-success' : 'bg-secondary') }}">
                                            {{ $item->variance_qty > 0 ? '+' : '' }}{{ $item->variance_qty }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($item->damage_qty > 0)
                                            <span class="badge bg-danger">{{ $item->damage_qty }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->damage_amount > 0)
                                            <span class="text-danger fw-bold">৳{{ number_format($item->damage_amount, 2) }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->lost_qty > 0)
                                            <span class="badge bg-warning">{{ $item->lost_qty }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->lost_amount > 0)
                                            <span class="text-warning fw-bold">৳{{ number_format($item->lost_amount, 2) }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>৳{{ number_format($item->avg_purchase_price, 2) }}</td>
                                    <td>{{ $item->remarks ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center">No items found</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="6" class="text-end">Totals:</th>
                                <th>{{ $inspection->total_damage_qty }}</th>
                                <th>৳{{ number_format($inspection->total_damage_amount, 2) }}</th>
                                <th>{{ $inspection->total_lost_qty }}</th>
                                <th>৳{{ number_format($inspection->total_lost_amount, 2) }}</th>
                                <th colspan="2">
                                    <strong class="text-danger">
                                        Total Loss: ৳{{ number_format($inspection->total_damage_amount + $inspection->total_lost_amount, 2) }}
                                    </strong>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('custome-js')

@endpush
