@extends('layouts.app')

@push('custome-css')
<style>
    .info-box {
        background: #ffffff;
        border: 1px solid #e3e6f0;
        border-left: 4px solid var(--bs-primary);
        border-radius: 0.5rem;
        padding: 1rem 1.25rem;
        margin-bottom: 1rem;
        transition: 0.2s;
        display: flex;
    flex-direction: column;
    align-items: flex-start;
    }

    .info-box:hover {
        background: #f9fbfd;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }

    .info-box h6 {
        font-size: 0.9rem;
        text-transform: uppercase;
        color: #6c757d;
        margin-bottom: 0.4rem;
        letter-spacing: 0.3px;
    }

    .info-box p {
        font-size: 1rem;
        color: #212529;
        margin-bottom: 0;
    }

    .document-preview {
        text-align: center;
        background: #f8f9fa;
        border: 1px dashed #d1d3e2;
        border-radius: 0.5rem;
        padding: 1rem;
        transition: 0.3s;
    }

    .document-preview:hover {
        background: #ffffff;
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    }

    .document-preview img {
        max-width: 100%;
        border-radius: 6px;
        border: 1px solid #dee2e6;
        margin-bottom: 10px;
    }

    .pdf-icon {
        font-size: 4rem;
        color: #dc3545;
    }

    .btn {
        border-radius: 6px;
    }

    .card {
        border-radius: 0.75rem;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }

    .card-header {
        border-bottom: 1px solid #e3e6f0;
        background: #f8f9fa;
    }

    @media (max-width: 768px) {
        h1, h3, h5 {
            font-size: 1.2rem !important;
        }
        .btn {
            font-size: 0.85rem;
        }
        .info-box {
            padding: 0.8rem;
        }
    }

    
</style>
@endpush

@section('content')
<div class="app-content-header mb-3">
    <div class="container-fluid">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
            <h3 class="mb-2 mb-md-0 fw-semibold">
                <i class="mdi mdi-cash-multiple text-primary me-2"></i>
                Payment Collection Details
            </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('payment-collections.index') }}">Payment Collections</a></li>
                    <li class="breadcrumb-item active">View Details</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row g-4">
            <!-- Left side -->
            <div class="col-12 col-lg-8">
                <!-- Page header -->
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                    <h4 class="fw-bold mb-2">Collection #{{ $paymentCollection->id }}</h4>
                    <div>
                        <a href="{{ route('payment-collections.index') }}" class="btn btn-outline-secondary btn-sm me-2">
                            <i class="mdi mdi-arrow-left"></i> Back
                        </a>
                        <button class="btn btn-outline-danger btn-sm delete-btn" 
                                data-bs-toggle="modal" 
                                data-bs-target="#deleteModal" 
                                data-url="{{ route('payment-collections.destroy', $paymentCollection) }}">
                            <i class="mdi mdi-delete"></i> Delete
                        </button>
                    </div>
                </div>

                <!-- Payment Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0 fw-semibold"><i class="mdi mdi-information-outline text-primary me-1"></i> Payment Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="info-box">
                                    <h6 class="text-center">Collection Date</h6>
                                    
                                    <p><i class="mdi mdi-calendar"></i> {{ optional($paymentCollection->collection_date)->format('F d, Y') ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <h6>Amount :</h6>
                                    <p><span class="badge {{ $paymentCollection->type_badge_class }} fs-6">৳{{ number_format($paymentCollection->amount, 2) }}</span></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <h6>Transaction Type</h6>
                                    <p><span class="badge {{ $paymentCollection->type_badge_class }}">{{ $paymentCollection->type_text }}</span></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <h6>Payment Method</h6>
                                    <p><i class="mdi mdi-credit-card"></i> <strong>{{ $paymentCollection->paymentMethod->account_name ?? 'N/A' }}</strong>
                                        <small class="text-muted">
                                            {{ $paymentCollection->paymentMethod->account_number ?? '' }} - {{ $paymentCollection->paymentMethod->institute_name ?? '' }}
                                        </small>
                                    </p>
                                </div>
                            </div>
                            @if($paymentCollection->note)
                            <div class="col-12">
                                <div class="info-box">
                                    <h6>Note</h6>
                                    <p>{{ $paymentCollection->note }}</p>
                                </div>
                            </div>
                            @endif
                            <div class="col-md-6">
                                <div class="info-box">
                                    <h6>Created By</h6>
                                    <p>
                                        @if($paymentCollection->createdBy)
                                            <i class="mdi mdi-account"></i> <strong>{{ $paymentCollection->createdBy->name }}</strong><br>
                                            <small class="text-muted">{{ $paymentCollection->createdBy->email }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <h6>Deposited By</h6>
                                    <p>
                                        @if($paymentCollection->depositeBy)
                                            <i class="mdi mdi-account-cash"></i> <strong>{{ $paymentCollection->depositeBy->name }}</strong><br>
                                            <small class="text-muted">{{ $paymentCollection->depositeBy->email }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <h6>Created At</h6>
                                    <p><i class="mdi mdi-clock-outline"></i> {{ $paymentCollection->created_at->format('F d, Y h:i A') }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <h6>Last Updated</h6>
                                    <p><i class="mdi mdi-update"></i> {{ $paymentCollection->updated_at->format('F d, Y h:i A') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Documents -->
                @if($paymentCollection->getMedia('payment_document')->isNotEmpty())
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="fw-semibold mb-0"><i class="mdi mdi-file-document text-info me-1"></i> Payment Document</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            @foreach($paymentCollection->getMedia('payment_document') as $media)
                                <div class="col-12 col-md-6">
                                    <div class="document-preview">
                                        @if(Str::contains($media->mime_type, 'image'))
                                            <img src="{{ $media->getUrl() }}" alt="Document" class="img-fluid">
                                        @elseif($media->mime_type === 'application/pdf')
                                            <i class="mdi mdi-file-pdf pdf-icon"></i>
                                            <p class="mt-2 fw-semibold">{{ $media->file_name }}</p>
                                        @endif
                                        <div class="d-flex justify-content-center gap-2 mt-2">
                                            <a href="{{ $media->getUrl() }}" target="_blank" class="btn btn-sm btn-primary">
                                                <i class="mdi mdi-eye"></i> View
                                            </a>
                                            <a href="{{ $media->getUrl() }}" download class="btn btn-sm btn-success">
                                                <i class="mdi mdi-download"></i> Download
                                            </a>
                                        </div>
                                        <div class="mt-2 text-muted small text-start">
                                            <strong>File:</strong> {{ $media->file_name }}<br>
                                            <strong>Size:</strong> {{ $media->human_readable_size }}<br>
                                            <strong>Uploaded:</strong> {{ $media->created_at->format('F d, Y h:i A') }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Right side -->
            <div class="col-12 col-lg-4">
                <!-- Vendor -->
                <div class="card mb-4">
                    <div class="card-header bg-success-subtle">
                        <h6 class="mb-0 fw-semibold text-success"><i class="mdi mdi-store me-1"></i> Vendor Details</h6>
                    </div>
                    <div class="card-body">
                        @if($paymentCollection->vendor)
                            <h5>{{ $paymentCollection->vendor->shop_name }}</h5>
                            <hr>
                            <p><i class="mdi mdi-account"></i> <strong>Contact:</strong> {{ $paymentCollection->vendor->contact_person ?? 'N/A' }}</p>
                            <p><i class="mdi mdi-phone"></i> {{ $paymentCollection->vendor->mobile ?? 'N/A' }}</p>
                            <p><i class="mdi mdi-email"></i> {{ $paymentCollection->vendor->email ?? 'N/A' }}</p>
                            <p><i class="mdi mdi-map-marker"></i> {{ $paymentCollection->vendor->full_address ?? 'N/A' }}</p>
                            <a href="{{ route('admin.vendorEdit', $paymentCollection->vendor) }}" class="btn btn-outline-success btn-sm w-100 mt-2">
                                <i class="mdi mdi-pencil"></i> View / Edit Vendor
                            </a>
                        @else
                            <p class="text-muted text-center mb-0">No vendor information available</p>
                        @endif
                    </div>
                </div>

                <!-- Order -->
                <div class="card mb-4">
                    <div class="card-header bg-warning-subtle">
                        <h6 class="mb-0 fw-semibold text-warning"><i class="mdi mdi-cart-outline me-1"></i> Order Details</h6>
                    </div>
                    <div class="card-body">
                        @if($paymentCollection->order)
                            <p><strong>Invoice:</strong> <span class="badge bg-info">{{ $paymentCollection->order->invoice_id }}</span></p>
                            <p><strong>Total:</strong> ৳{{ number_format($paymentCollection->order->total_amount, 2) }}</p>
                            <p><strong>Status:</strong>
                                @if($paymentCollection->order->payment_status == 0)
                                    <span class="badge bg-danger">Unpaid</span>
                                @elseif($paymentCollection->order->payment_status == 1)
                                    <span class="badge bg-warning text-dark">Partially Paid</span>
                                @else
                                    <span class="badge bg-success">Fully Paid</span>
                                @endif
                            </p>
                            <p><strong>Order Date:</strong> {{ $paymentCollection->order->created_at->format('F d, Y') }}</p>
                            <a href="{{ route('orders.show', $paymentCollection->order) }}" class="btn btn-outline-warning btn-sm w-100 mt-2">
                                <i class="mdi mdi-eye"></i> View Order
                            </a>
                        @else
                            <p class="text-muted text-center">
                                <i class="mdi mdi-information-outline fs-3 d-block mb-2"></i>
                                This collection is not linked to any order.
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @include('components.delete')
    </div>
</div>
@endsection
