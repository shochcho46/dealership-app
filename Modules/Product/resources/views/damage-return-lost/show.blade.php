@extends('layouts.app')

@section('title', 'Damage/Return/Lost Details')

@push('custome-css')
<style>
    .detail-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 20px;
    }

    .info-card {
        background: #ffffff;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }

    .status-badge {
        font-size: 1.1rem;
        padding: 8px 16px;
        border-radius: 20px;
    }

    .evidence-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }

    .evidence-item {
        position: relative;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }

    .evidence-item:hover {
        transform: translateY(-5px);
    }

    .evidence-item img {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }

    .evidence-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(transparent, rgba(0,0,0,0.7));
        color: white;
        padding: 10px;
        text-align: center;
    }



    .timeline-item {
        border-left: 3px solid #007bff;
        padding-left: 20px;
        margin-bottom: 20px;
        position: relative;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -8px;
        top: 5px;
        width: 13px;
        height: 13px;
        border-radius: 50%;
        background: #007bff;
    }

    @media (max-width: 768px) {
        .evidence-gallery {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        }

        .evidence-item img {
            height: 150px;
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
                <h4 class="mb-sm-0">Damage/Return/Lost Details</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('damage-return-lost.index') }}">Damage/Return/Lost</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Record Details Header -->
    <div class="detail-card">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3><i class="mdi mdi-alert-circle-outline"></i> {{ ucfirst($damageReturnLost->status_text) }} Report #{{ $damageReturnLost->id }}</h3>
                <p class="mb-1"><i class="mdi mdi-receipt"></i> Order: {{ $damageReturnLost->order->invoice_id }}</p>
                <p class="mb-1"><i class="mdi mdi-store"></i> Vendor: {{ $damageReturnLost->order->vendor->shop_name }}</p>
                <p class="mb-0"><i class="mdi mdi-calendar"></i> Reported: {{ $damageReturnLost->created_at->format('M d, Y H:i A') }}</p>
            </div>
            <div class="col-md-4 text-end">

                <div >
                   <span class="status-badge {{ $damageReturnLost->status_badge_class }}">
                    {{ ucfirst($damageReturnLost->status_text) }}
                    </span>
                </div>
                <div class="mt-2">
                    <strong class="h4">৳{{ number_format($damageReturnLost->total_price, 2) }}</strong>
                    <br><small>Total Impact</small>
                </div>

            </div>
        </div>
    </div>

    <div class="row">
        <!-- Product Information -->
        <div class="col-lg-6">
            <div class="info-card">
                <div class="row">
                    <h5 class="card-title mb-3">
                        <i class="mdi mdi-package-variant"></i>
                        Product Information
                    </h5>
                </div>


                <div class="row mb-3">
                    <div class="col-sm-6">
                        <strong>Product Name:</strong>
                    </div>
                    <div class="col-sm-6">
                        {{ $damageReturnLost->orderItem->product->name ?? 'N/A' }}
                    </div>
                </div>

                @if($damageReturnLost->orderItem->product && $damageReturnLost->orderItem->product->description)
                <div class="row mb-3">
                    <div class="col-sm-6">
                        <strong>Description:</strong>
                    </div>
                    <div class="col-sm-6">
                        {{ Str::limit($damageReturnLost->orderItem->product->description, 100) }}
                    </div>
                </div>
                @endif

                <div class="row mb-3">
                    <div class="col-sm-6">
                        <strong>Affected Quantity:</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="badge bg-danger">{{ number_format($damageReturnLost->quantity) }} units</span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-6">
                        <strong>Unit Price:</strong>
                    </div>
                    <div class="col-sm-6">
                        ৳{{ number_format($damageReturnLost->total_price / $damageReturnLost->quantity, 2) }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-6">
                        <strong>Purchase Price:</strong>
                    </div>
                    <div class="col-sm-6">
                        ৳{{ number_format($damageReturnLost->purchase_price, 2) }}
                    </div>
                </div>

                @if($damageReturnLost->reason)
                <div class="row mb-3">
                    <div class="col-sm-6">
                        <strong>Reason:</strong>
                    </div>
                    <div class="col-sm-6">
                        {{ $damageReturnLost->reason }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Order Information -->
        <div class="col-lg-6">
            <div class="info-card">
                <div class="row">

                    <h5 class="card-title mb-3"><i class="mdi mdi-receipt"></i> Order Information</h5>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-6">
                        <strong>Invoice ID:</strong>
                    </div>
                    <div class="col-sm-6">
                        <a href="{{ route('orders.show', $damageReturnLost->order->id) }}" class="text-primary">
                            {{ $damageReturnLost->order->invoice_id }}
                        </a>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-6">
                        <strong>Order Date:</strong>
                    </div>
                    <div class="col-sm-6">
                        {{ $damageReturnLost->order->created_at->format('M d, Y') }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-6">
                        <strong>Order Total:</strong>
                    </div>
                    <div class="col-sm-6">
                        ৳{{ number_format($damageReturnLost->order->total_amount, 2) }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-6">
                        <strong>Order Status:</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="badge bg-primary">{{ $damageReturnLost->order->orderStatus->name ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vendor Information -->
    <div class="row">
        <div class="col-lg-6">
            <div class="info-card">
                <div class="row">

                    <h5 class="card-title mb-3"><i class="mdi mdi-store"></i> Vendor Information</h5>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-6">
                        <strong>Shop Name:</strong>
                    </div>
                    <div class="col-sm-6">
                        {{ $damageReturnLost->order->vendor->shop_name }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-6">
                        <strong>Contact:</strong>
                    </div>
                    <div class="col-sm-6">
                        {{ $damageReturnLost->order->vendor->mobile }}
                    </div>
                </div>

                @if($damageReturnLost->order->vendor->email)
                <div class="row mb-3">
                    <div class="col-sm-6">
                        <strong>Email:</strong>
                    </div>
                    <div class="col-sm-6">
                        {{ $damageReturnLost->order->vendor->email }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Financial Impact -->
        <div class="col-lg-6">
            <div class="info-card">
            <div class="">
                <h5 class="mb-3"><i class="mdi mdi-currency-bdt"></i> Financial Impact</h5>

                <div class="row mb-2">
                    <div class="col-8">
                        <strong>Direct Loss:</strong>
                    </div>
                    <div class="col-4 text-end">
                        <strong class="text-danger">৳{{ number_format($damageReturnLost->total_price, 2) }}</strong>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-8">
                        Cost per Unit:
                    </div>
                    <div class="col-4 text-end">
                        ৳{{ number_format($damageReturnLost->purchase_price, 2) }}
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-8">
                        Quantity Affected:
                    </div>
                    <div class="col-4 text-end">
                        {{ number_format($damageReturnLost->quantity) }} units
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-8">
                        <strong>Total Cost Impact:</strong>
                    </div>
                    <div class="col-4 text-end">
                        <strong class="text-danger">৳{{ number_format($damageReturnLost->quantity * $damageReturnLost->purchase_price, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        </div>
    </div>

    <!-- Evidence Images -->
    @if($damageReturnLost->hasMedia('evidence_pic'))
    <div class="row">
        <div class="col-12">
            <div class="info-card">
                <h5 class="card-title mb-3"><i class="mdi mdi-camera"></i> Evidence Photos</h5>

                <div class="evidence-gallery">
                    @foreach($damageReturnLost->getMedia('evidence_pic') as $media)
                        <div class="evidence-item">
                            <img src="{{ $media->getUrl() }}" alt="Evidence Photo"
                                 onclick="showImageModal('{{ $media->getUrl() }}', '{{ $media->name }}')"
                                 style="cursor: pointer;">
                            <div class="evidence-overlay">
                                <small>{{ $media->name }}</small>
                                <br><small>{{ $media->human_readable_size }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif


    <!-- Action Buttons -->
    <div class="row">
        <div class="col-12">
            <div class="text-center">
                <a href="{{ route('damage-return-lost.index') }}" class="btn btn-secondary me-2">
                    <i class="mdi mdi-arrow-left"></i> Back to List
                </a>

                <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                    <i class="mdi mdi-delete"></i> Delete Record
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalTitle">Evidence Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="Evidence" class="img-fluid" style="max-height: 70vh;">
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="mdi mdi-alert-circle text-warning" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Are you sure?</h5>
                    <p class="text-muted">This will permanently delete this {{ $damageReturnLost->status_text }} record and reverse all related stock and financial adjustments.</p>
                    <p class="text-danger"><strong>This action cannot be undone!</strong></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('damage-return-lost.destroy', $damageReturnLost) }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Yes, Delete Record</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('custome-js')
<script>
function showImageModal(imageUrl, imageName) {
    document.getElementById('modalImage').src = imageUrl;
    document.getElementById('imageModalTitle').textContent = imageName || 'Evidence Photo';
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}

function confirmDelete() {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
