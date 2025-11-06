@extends('layouts.app')

@section('title', 'Order Details')
@push('custome-css')
<style>
    @media print {
        .btn, .card-header .d-flex .d-flex, .breadcrumb, .page-title-box {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Order Details</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('orders.index') }}">Orders</a></li>
                        <li class="breadcrumb-item active">{{ $order->invoice_id }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Order Information -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Order Information</h5>
                        <div class="d-flex gap-2">
                            @if($order->canBeCancelled())
                                <a href="{{ route('orders.edit', $order) }}" class="btn btn-sm btn-primary">
                                    <i class="mdi mdi-pencil me-1"></i>Edit Order
                                </a>
                                <form action="{{ route('orders.cancel', $order) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to cancel this order? Stock will be restored.')">
                                        <i class="mdi mdi-cancel me-1"></i>Cancel Order
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td class="fw-bold">Invoice ID:</td>
                                    <td>{{ $order->invoice_id }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Vendor:</td>
                                    <td>{{ $order->vendor->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Created By:</td>
                                    <td>{{ $order->admin->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Status:</td>
                                    <td>
                                        <span class="badge {{ $order->status_badge_class }}">
                                            {{ $order->orderStatus->name ?? 'Unknown' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td class="fw-bold">Order Date:</td>
                                    <td>{{ $order->created_at->format('d M Y, h:i A') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Last Updated:</td>
                                    <td>{{ $order->updated_at->format('d M Y, h:i A') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Total Items:</td>
                                    <td>{{ $order->total_quantity }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Total Products:</td>
                                    <td>{{ $order->orderItems->count() }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Order Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Purchase Price</th>
                                    <th>Sell Price</th>
                                    <th>Quantity</th>
                                    <th>Discount</th>
                                    <th>Total</th>
                                    <th>Profit</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->orderItems as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($item->product->product_image_thumb_url)
                                                    <img src="{{ $item->product->product_image_thumb_url }}"
                                                         alt="{{ $item->product->name }}"
                                                         class="me-2"
                                                         style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                                @endif
                                                <div>
                                                    <strong>{{ $item->product->name }}</strong>
                                                    <br>

                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            ৳{{ number_format($item->purchase_price, 2) }}
                                           @if ($item->orderItemStocks->count() > 0)
                                           <div class="mt-2 border-top pt-1">
                                                @foreach ($item->orderItemStocks as $itemstock)
                                                    <small class="d-block text-muted">
                                                        Qty: <strong>{{ $itemstock->quantity }}</strong> |
                                                        ৳{{ number_format($itemstock->purchase_price, 2) }}
                                                    </small>

                                                @endforeach
                                                </div>
                                            @endif

                                        </td>
                                        <td>৳{{ number_format($item->sell_price, 2) }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>
                                            @if($item->discount_price > 0)
                                                <span class="text-danger">৳{{ number_format($item->discount_price, 2) }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>৳{{ number_format($item->net_price, 2) }}</strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong class="text-success">৳{{ number_format($item->total_profit, 2) }}</strong>
                                                <br>
                                                <small class="text-muted">{{ number_format($item->profit_margin, 1) }}%</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $item->status_badge_class }}">
                                                {{ $item->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Order Summary -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td>Subtotal:</td>
                                <td class="text-end">৳{{ number_format($order->total_amount + $order->total_discount_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Total Discount:</td>
                                <td class="text-end text-danger">
                                    @if($order->total_discount_amount > 0)
                                        - ৳{{ number_format($order->total_discount_amount, 2) }}
                                    @else
                                        ৳0.00
                                    @endif
                                </td>
                            </tr>
                            <tr class="border-top">
                                <td><strong>Net Total:</strong></td>
                                <td class="text-end"><strong>৳{{ number_format($order->net_amount, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td><strong>Total Profit:</strong></td>
                                <td class="text-end"><strong class="text-success">৳{{ number_format($order->total_profit, 2) }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Order Analytics -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Analytics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary">{{ $order->total_quantity }}</h4>
                                <p class="text-muted mb-0">Total Items</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">{{ $order->orderItems->count() }}</h4>
                            <p class="text-muted mb-0">Products</p>
                        </div>
                    </div>

                    <hr>

                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h5 class="text-warning">{{ $order->total_return_quantity }}</h5>
                                <p class="text-muted mb-0">Returned</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <h5 class="text-danger">{{ $order->total_damage_quantity }}</h5>
                            <p class="text-muted mb-0">Damaged</p>
                        </div>
                    </div>

                    <hr>

                    @php
                        $avgProfit = $order->orderItems->count() > 0 ? $order->total_profit / $order->orderItems->count() : 0;
                        $profitMargin = $order->total_amount > 0 ? ($order->total_profit / $order->total_amount) * 100 : 0;
                    @endphp

                    <div class="row text-center">
                        <div class="col-12 mb-3">
                            <h5 class="text-info">৳{{ number_format($avgProfit, 2) }}</h5>
                            <p class="text-muted mb-0">Avg Profit per Item</p>
                        </div>
                        <div class="col-12">
                            <h5 class="text-success">{{ number_format($profitMargin, 1) }}%</h5>
                            <p class="text-muted mb-0">Profit Margin</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('orders.index') }}" class="btn btn-outline-primary">
                            <i class="mdi mdi-arrow-left me-1"></i>Back to Orders
                        </a>
                        @if($order->canBeCancelled())
                            <a href="{{ route('orders.edit', $order) }}" class="btn btn-outline-secondary">
                                <i class="mdi mdi-pencil me-1"></i>Edit Order
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

