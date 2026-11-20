@extends('layouts.app')

@push('custome-css')
<style>
    .inq-items { margin: 0; padding-left: 1rem; font-size: .9rem; }
    .inq-msg { max-width: 280px; white-space: pre-line; font-size: .9rem; }
    .status-form select { min-width: 120px; }
    .inq-items { list-style: none; padding-left: 0; }
    .inq-items li { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; }
    .inq-items img { width: 36px; height: 36px; object-fit: contain; border: 1px solid #e9ecef; border-radius: 6px; background: #fff; flex: none; }
    .inq-items .line-total { margin-left: auto; padding-left: 8px; white-space: nowrap; font-weight: 600; }
    .fee-form .input-group { width: 150px; margin-left: auto; }
    .fee-form input { text-align: right; }
    .sum-box { border: 1px solid #e3e8ef; border-radius: 10px; padding: 10px 14px; background: #f8fafc; height: 100%; }
    .sum-box .sum-val { font-size: 1.35rem; font-weight: 700; line-height: 1.3; }
    .sum-box.sum-total { background: #0B1F3A; border-color: #0B1F3A; color: #fff; }
    .sum-box.sum-total small { color: rgba(255,255,255,.8); }
</style>
@endpush

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Website Inquiries</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Website Inquiries</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <div class="row mb-3">
            @foreach(\Modules\Product\Models\WebsiteInquiry::STATUSES as $value => $label)
                <div class="col-md-4 mb-2">
                    <a href="{{ route('website-inquiries.index', ['status' => $value]) }}" class="text-decoration-none">
                        <div class="card {{ $value === 0 ? 'text-bg-warning' : ($value === 1 ? 'text-bg-info' : 'text-bg-secondary') }}">
                            <div class="card-body py-3">
                                <h4 class="mb-0">{{ $counts[$value] ?? 0 }}</h4>
                                <span>{{ $label }}</span>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="card card-primary card-outline mb-4">
            <div class="card-header">
                <form method="GET" action="{{ route('website-inquiries.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Name, mobile, place" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            @foreach(\Modules\Product\Models\WebsiteInquiry::STATUSES as $value => $label)
                                <option value="{{ $value }}" {{ request('status') !== null && request('status') !== '' && (int) request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">From</label>
                        <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">To</label>
                        <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><span class="mdi mdi-filter"></span> Filter</button>
                        <a href="{{ route('website-inquiries.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
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

                {{-- Sums for all inquiries matching the filters (date range / status / search) --}}
                @php
                    $hasFilter = request()->hasAny(['from_date', 'to_date', 'status', 'search']) && request()->collect()->only(['from_date', 'to_date', 'status', 'search'])->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
                    $period = request('from_date') || request('to_date')
                        ? (request('from_date') ? \Carbon\Carbon::parse(request('from_date'))->format('d M Y') : 'Start') . ' – ' . (request('to_date') ? \Carbon\Carbon::parse(request('to_date'))->format('d M Y') : 'Today')
                        : 'All dates';
                    $a = $summary['all']; $c = $summary['confirmed'];
                @endphp
                <div class="inq-summary mb-3">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <strong><span class="mdi mdi-sigma"></span> Summary</strong>
                        <span class="badge text-bg-light border"><span class="mdi mdi-calendar-range"></span> {{ $period }}</span>
                        @if($hasFilter)<small class="text-muted">(filtered)</small>@endif
                    </div>
                    <div class="row g-2">
                        <div class="col-6 col-lg-3">
                            <div class="sum-box">
                                <small>Orders</small>
                                <div class="sum-val">{{ number_format($a->orders) }}</div>
                                <small class="text-muted">Confirmed: {{ number_format($c->orders) }}</small>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="sum-box">
                                <small>Products amount</small>
                                <div class="sum-val">৳{{ number_format($a->subtotal, 2) }}</div>
                                <small class="text-muted">Confirmed: ৳{{ number_format($c->subtotal, 2) }}</small>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="sum-box">
                                <small><span class="mdi mdi-truck-delivery-outline"></span> Total delivery fee</small>
                                <div class="sum-val">৳{{ number_format($a->delivery_fee, 2) }}</div>
                                <small class="text-muted">Confirmed: ৳{{ number_format($c->delivery_fee, 2) }}</small>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="sum-box sum-total">
                                <small>Total amount</small>
                                <div class="sum-val">৳{{ number_format($a->total, 2) }}</div>
                                <small>Confirmed: ৳{{ number_format($c->total, 2) }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Requested Products</th>
                                <th>Message</th>
                                <th class="text-end">Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inquiries as $inquiry)
                                <tr>
                                    <td>{{ $inquiries->firstItem() + $loop->index }}</td>
                                    <td class="text-nowrap">
                                        {{ $inquiry->created_at->format('d M Y') }}<br>
                                        <small class="text-muted">{{ $inquiry->created_at->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $inquiry->name }}</strong>
                                        @if($inquiry->shop_name)<br><span class="text-muted">{{ $inquiry->shop_name }}</span>@endif
                                        <br><a href="tel:{{ $inquiry->mobile }}">{{ $inquiry->mobile }}</a>
                                        @if($inquiry->delivery_type)
                                            <br><span class="badge {{ $inquiry->delivery_type === 'home' ? 'text-bg-info' : 'text-bg-secondary' }} mt-1">
                                                <span class="mdi {{ $inquiry->delivery_type === 'home' ? 'mdi-home-outline' : 'mdi-storefront-outline' }}"></span>
                                                {{ $inquiry->delivery_type === 'home' ? 'Home delivery' : 'Shop delivery' }}
                                            </span>
                                        @endif
                                        @if($inquiry->area)<br><small class="text-muted" title="Place / delivery address"><span class="mdi mdi-map-marker"></span> {{ $inquiry->area }}</small>@endif
                                    </td>
                                    <td>
                                        @if($inquiry->items)
                                            <ul class="inq-items">
                                                @foreach($inquiry->items as $item)
                                                    @php $pm = $products->get($item['product_id'] ?? 0); @endphp
                                                    <li>
                                                        <img src="{{ $pm ? $pm->website_image_url : asset('frontend/v2/img/product-placeholder.svg') }}" alt="" loading="lazy">
                                                        <span>{{ $item['name'] ?? 'Product' }}<br>
                                                            <small class="text-muted"><strong>{{ $item['cases'] ?? 1 }}</strong> {{ strtolower($item['pack_unit'] ?? 'case') }} × ৳{{ number_format($item['unit_price'] ?? 0, 2) }}</small>
                                                        </span>
                                                        <span class="line-total">৳{{ number_format($item['line_total'] ?? 0, 2) }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @elseif($inquiry->product)
                                            {{ $inquiry->product->name }}
                                        @else
                                            <span class="text-muted">General inquiry</span>
                                        @endif
                                    </td>
                                    <td class="inq-msg">{{ $inquiry->message ?: '—' }}</td>
                                    <td class="text-end text-nowrap">
                                        <strong>৳{{ number_format($inquiry->total, 2) }}</strong>
                                        @if($inquiry->isPriceLocked())
                                            <br><small class="text-muted">incl. delivery ৳{{ number_format($inquiry->delivery_fee, 2) }}</small>
                                            <br><span class="badge text-bg-light border mt-1" title="Order confirmed — prices and delivery fee can't change"><span class="mdi mdi-lock"></span> Locked</span>
                                        @else
                                            <br><small class="text-muted">products ৳{{ number_format($inquiry->subtotal, 2) }}</small>
                                            <form method="POST" action="{{ route('website-inquiries.deliveryFee', $inquiry) }}" class="fee-form mt-1">
                                                @csrf
                                                @method('PUT')
                                                <div class="input-group input-group-sm" title="Delivery fee — can be changed until the order is marked Contacted">
                                                    <span class="input-group-text"><span class="mdi mdi-truck-delivery-outline"></span></span>
                                                    <input type="number" name="delivery_fee" min="0" max="1000000" step="0.01" required
                                                           class="form-control" value="{{ $inquiry->delivery_fee + 0 }}" aria-label="Delivery fee">
                                                    <button type="submit" class="btn btn-outline-primary" title="Save delivery fee"><span class="mdi mdi-check"></span></button>
                                                </div>
                                            </form>
                                        @endif
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('website-inquiries.update', $inquiry) }}" class="status-form">
                                            @csrf
                                            @method('PUT')
                                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()"
                                                    title="Marking Contacted confirms the order: prices & delivery fee get locked and the SMS is sent (if enabled)">
                                                @foreach(\Modules\Product\Models\WebsiteInquiry::STATUSES as $value => $label)
                                                    <option value="{{ $value }}" {{ $inquiry->status === $value ? 'selected' : '' }}
                                                            @disabled($value === \Modules\Product\Models\WebsiteInquiry::STATUS_NEW && !$inquiry->canReopen())>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                        @if($inquiry->handledBy)
                                            <small class="text-muted">by {{ $inquiry->handledBy->name }}</small>
                                        @endif
                                        @if($inquiry->sms_status)
                                            <br><span class="badge {{ ['sent' => 'text-bg-success', 'failed' => 'text-bg-danger'][$inquiry->sms_status] ?? 'text-bg-light border' }}"
                                                      title="{{ $inquiry->sms_response }}">
                                                <span class="mdi mdi-message-text-outline"></span> SMS {{ $inquiry->sms_status }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('website-inquiries.show', $inquiry) }}" class="btn btn-sm btn-outline-info" title="Details / edit price">
                                                <span class="mdi mdi-eye"></span>
                                            </a>
                                            @if($inquiry->has_invoice)
                                                <a href="{{ route('website-inquiries.invoice', $inquiry) }}" target="_blank" class="btn btn-sm btn-outline-dark" title="Invoice">
                                                    <span class="mdi mdi-file-document-outline"></span>
                                                </a>
                                            @endif
                                            <a href="tel:{{ $inquiry->mobile }}" class="btn btn-sm btn-outline-primary" title="Call">
                                                <span class="mdi mdi-phone"></span>
                                            </a>
                                            <a href="https://wa.me/{{ $inquiry->whatsapp_number }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success" title="WhatsApp">
                                                <span class="mdi mdi-whatsapp"></span>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger delete-btn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal"
                                                    data-url="{{ route('website-inquiries.destroy', $inquiry) }}"
                                                    title="Delete">
                                                <span class="mdi mdi-delete"></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No inquiries yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end">
                {{ $inquiries->links() }}
            </div>
        </div>

        @include('components.delete')
    </div>
</div>
@endsection

@push('custome-js')
@endpush
