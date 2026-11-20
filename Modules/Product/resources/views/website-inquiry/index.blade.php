@extends('layouts.app')

@push('custome-css')
<style>
    .inq-items { margin: 0; padding-left: 1rem; font-size: .9rem; }
    .inq-msg { max-width: 280px; white-space: pre-line; font-size: .9rem; }
    .status-form select { min-width: 120px; }
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

                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Requested Products</th>
                                <th>Message</th>
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
                                                    <li>{{ $item['name'] ?? 'Product' }} — <strong>{{ $item['cases'] ?? 1 }}</strong> {{ strtolower($item['pack_unit'] ?? 'case') }}</li>
                                                @endforeach
                                            </ul>
                                        @elseif($inquiry->product)
                                            {{ $inquiry->product->name }}
                                        @else
                                            <span class="text-muted">General inquiry</span>
                                        @endif
                                    </td>
                                    <td class="inq-msg">{{ $inquiry->message ?: '—' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('website-inquiries.update', $inquiry) }}" class="status-form">
                                            @csrf
                                            @method('PUT')
                                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                @foreach(\Modules\Product\Models\WebsiteInquiry::STATUSES as $value => $label)
                                                    <option value="{{ $value }}" {{ $inquiry->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                        @if($inquiry->handledBy)
                                            <small class="text-muted">by {{ $inquiry->handledBy->name }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
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
                                    <td colspan="7" class="text-center text-muted py-4">No inquiries yet.</td>
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
