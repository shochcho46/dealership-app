@extends('layouts.app')

@push('custome-css')
<style>
    .cm-card { border: 1px solid #e3e8ef; border-radius: 10px; padding: 10px 14px; background: #f8fafc; height: 100%; }
    .cm-card .v { font-size: 1.3rem; font-weight: 700; line-height: 1.3; }
    .cm-card.plus { background: #f0fdf4; border-color: #bbf7d0; } .cm-card.plus .v { color: #15803d; }
    .cm-card.minus { background: #fef2f2; border-color: #fecaca; } .cm-card.minus .v { color: #dc2626; }
    .cm-card.net { background: #0B1F3A; border-color: #0B1F3A; color: #fff; } .cm-card.net small { color: rgba(255,255,255,.8); }
    .cm-plus { color: #15803d; font-weight: 700; }
    .cm-minus { color: #dc2626; font-weight: 700; }
    .cm-table td, .cm-table th { white-space: nowrap; }
    .cm-table .vendor { white-space: normal; min-width: 180px; }
</style>
@endpush

@section('content')
@php
    $money = fn ($v) => number_format((float) $v, 2);
    $signed = fn ($v) => ((float) $v > 0 ? '+' : ((float) $v < 0 ? '−' : '')) . number_format(abs((float) $v), 2);
    $cls = fn ($v) => (float) $v > 0 ? 'cm-plus' : ((float) $v < 0 ? 'cm-minus' : 'text-muted');
@endphp
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3 class="mb-0">Commission</h3></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Commission</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-primary card-outline mb-4">
            <div class="card-header">
                <form method="GET" action="{{ route('commission.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Placed by</label>
                        <select name="place_by" class="form-select">
                            <option value="">All</option>
                            @foreach($placeBys as $p)
                                <option value="{{ $p->id }}" @selected(request('place_by') == $p->id)>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Vendor</label>
                        <select name="vendor_id" class="form-select">
                            <option value="">All vendors</option>
                            @foreach($vendors as $v)
                                <option value="{{ $v->id }}" @selected(request('vendor_id') == $v->id)>{{ $v->shop_name }}{{ $v->mobile ? ' — ' . $v->mobile : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All except cancelled</option>
                            @foreach($statuses as $s)
                                <option value="{{ $s->id }}" @selected((string) request('status') === (string) $s->id)>{{ ucfirst($s->name) }}</option>
                            @endforeach
                            <option value="all" @selected(request('status') === 'all')>All (incl. cancelled)</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small mb-1">Result</label>
                        <select name="result" class="form-select">
                            <option value="">All</option>
                            <option value="plus" @selected(request('result') === 'plus')>Plus</option>
                            <option value="minus" @selected(request('result') === 'minus')>Minus</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-12 d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary"><span class="mdi mdi-filter"></span> Filter</button>
                        <a href="{{ route('commission.index') }}" class="btn btn-outline-secondary">Reset</a>
                        <a href="{{ route('commission.export', request()->query()) }}" class="btn btn-success ms-auto">
                            <span class="mdi mdi-file-excel"></span> Export Excel
                        </a>
                    </div>
                </form>
            </div>

            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <div class="row g-2 mb-3">
                    <div class="col-6 col-lg">
                        <div class="cm-card"><small>Orders</small><div class="v">{{ number_format($totals['orders']) }}</div>
                            <small class="text-muted">Order total ৳{{ $money($totals['total_amount']) }}</small></div>
                    </div>
                    <div class="col-6 col-lg">
                        <div class="cm-card"><small>Price difference</small><div class="v {{ $cls($totals['price_diff']) }}">৳{{ $signed($totals['price_diff']) }}</div>
                            <small class="text-muted">Discount ৳{{ $money($totals['discount']) }}</small></div>
                    </div>
                    <div class="col-6 col-lg">
                        <div class="cm-card plus"><small>Total plus (pay to SR)</small><div class="v">+৳{{ $money($totals['plus']) }}</div>
                            <small class="text-muted">{{ number_format($totals['plus_orders']) }} orders</small></div>
                    </div>
                    <div class="col-6 col-lg">
                        <div class="cm-card minus"><small>Total minus (SR pays)</small><div class="v">−৳{{ $money(abs($totals['minus'])) }}</div>
                            <small class="text-muted">{{ number_format($totals['minus_orders']) }} orders</small></div>
                    </div>
                    <div class="col-12 col-lg">
                        <div class="cm-card net"><small>Net commission</small>
                            <div class="v" style="color: {{ $totals['net'] >= 0 ? '#4ade80' : '#f87171' }}">৳{{ $signed($totals['net']) }}</div>
                            <small>{{ $totals['net'] >= 0 ? 'To pay the SR' : 'SR has to pay' }}</small></div>
                    </div>
                </div>

                <p class="small text-muted mb-2">
                    <span class="mdi mdi-information-outline"></span>
                    Commission per order = (sold price − product's <b>commission price</b>, or the sell price if no commission price is set) × sold qty − discount. Returned qty is not counted. Set the commission price on each product (Products → Edit).
                </p>

                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle cm-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Invoice</th>
                                <th>Vendor</th>
                                <th>Placed by</th>
                                <th>Status</th>
                                <th class="text-end">Total amount</th>
                                <th class="text-end" title="(sold price − commission price, or sell price if not set) × sold qty">Price diff</th>
                                <th class="text-end">Discount</th>
                                <th class="text-end">Commission</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td>{{ $orders->firstItem() + $loop->index }}</td>
                                    <td>{{ $order->created_at->format('d M Y') }}<br><small class="text-muted">{{ $order->created_at->format('h:i A') }}</small></td>
                                    <td><a href="{{ route('orders.show', $order->id) }}" target="_blank">{{ $order->invoice_id }}</a></td>
                                    <td class="vendor">{{ $order->vendor->shop_name ?? 'N/A' }}</td>
                                    <td>{{ $order->placeBy->name ?? 'N/A' }}</td>
                                    <td><span class="badge {{ $order->status_badge_class }}">{{ ucfirst($order->orderStatus->name ?? 'N/A') }}</span></td>
                                    <td class="text-end">৳{{ $money($order->total_amount) }}</td>
                                    <td class="text-end {{ $cls($order->price_diff) }}">{{ $signed($order->price_diff) }}</td>
                                    <td class="text-end text-muted">{{ (float) $order->discount > 0 ? '−' . $money($order->discount) : '0.00' }}</td>
                                    <td class="text-end {{ $cls($order->commission) }}">৳{{ $signed($order->commission) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-center text-muted py-4">No orders found for these filters.</td></tr>
                            @endforelse
                        </tbody>
                        @if($orders->count())
                            <tfoot class="table-light">
                                <tr class="fw-semibold">
                                    <td colspan="6" class="text-end">This page</td>
                                    <td class="text-end">৳{{ $money($orders->sum('total_amount')) }}</td>
                                    <td class="text-end {{ $cls($orders->sum('price_diff')) }}">{{ $signed($orders->sum('price_diff')) }}</td>
                                    <td class="text-end">−{{ $money($orders->sum('discount')) }}</td>
                                    <td class="text-end {{ $cls($orders->sum('commission')) }}">৳{{ $signed($orders->sum('commission')) }}</td>
                                </tr>
                                <tr class="fw-bold">
                                    <td colspan="6" class="text-end">All filtered orders ({{ number_format($totals['orders']) }})</td>
                                    <td class="text-end">৳{{ $money($totals['total_amount']) }}</td>
                                    <td class="text-end {{ $cls($totals['price_diff']) }}">{{ $signed($totals['price_diff']) }}</td>
                                    <td class="text-end">−{{ $money($totals['discount']) }}</td>
                                    <td class="text-end {{ $cls($totals['net']) }}">৳{{ $signed($totals['net']) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="9" class="text-end">Total plus</td>
                                    <td class="text-end cm-plus">+৳{{ $money($totals['plus']) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="9" class="text-end">Total minus</td>
                                    <td class="text-end cm-minus">−৳{{ $money(abs($totals['minus'])) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
            @if($orders->hasPages())
                <div class="card-footer d-flex justify-content-end">{{ $orders->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
