@extends('layouts.app')

@section('title', 'Damage/Return/Lost Management')

@push('custome-css')
<style>
    .summary-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .summary-card .number {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .summary-card .label {
        font-size: 0.9rem;
        opacity: 0.9;
    }

    .type-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 10px;
        font-weight: bold;
        text-transform: uppercase;
    }

    .type-damage {
        background: #dc3545;
        color: white;
    }

    .type-return {
        background: #fffb00;
        color: rgb(0, 0, 0);
    }

    .type-lost {
        background: #1b1e20;
        color: white;
    }

    .evidence-images {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }

    .evidence-thumb {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 5px;
        cursor: pointer;
    }

    @media (max-width: 768px) {
        .summary-card .number {
            font-size: 1.5rem;
        }

        .table-responsive {
            font-size: 0.85rem;
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
                <h4 class="mb-sm-0">Damage/Return/Lost Management</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Damage/Return/Lost</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-4 mb-4">
            <div class="summary-card" style="background: linear-gradient(135deg, #dc3545 0%, #ff6b7a 100%);">
                <div class="number">{{ number_format($totalDamaged) }}</div>
                <div class="label">Total Damaged Items</div>
            </div>
        </div>

        <div class="col-xl-4 col-md-4 mb-4">
            <div class="summary-card" style="background: linear-gradient(135deg, #6c757d 0%, #8e9499 100%);">
                <div class="number">{{ number_format($totalLost) }}</div>
                <div class="label">Total Lost Items</div>
            </div>
        </div>
            <div class="col-xl-4 col-md-4 mb-4">
            <div class="summary-card" style="background: linear-gradient(135deg, #ff9800 0%, #ffc107 100%);">
                <div class="number">৳{{ bd_number_format($totalValue, 2) }}</div>
                <div class="label">Total Value Impact</div>
            </div>
        </div>
    </div>


    <div class="row mb-4">
        <div class="col-xl-4 col-md-4 mb-4">
            <div class="summary-card" style="background: linear-gradient(135deg, #1164ff 0%, #ff6b7a 100%);">
                <div class="number">{{ number_format($rangeDamaged) }}</div>
                <div class="label">DateRange Damaged Items</div>
            </div>
        </div>

        <div class="col-xl-4 col-md-4 mb-4">
            <div class="summary-card" style="background: linear-gradient(135deg, #0c3457 0%, #8e9499 100%);">
                <div class="number">{{ number_format($rangeLost) }}</div>
                <div class="label">DateRange Lost Items</div>
            </div>
        </div>
            <div class="col-xl-4 col-md-4 mb-4">
            <div class="summary-card" style="background: linear-gradient(135deg, #a16202 0%, #ffc107 100%);">
                <div class="number">৳{{ bd_number_format($rangeValue, 2) }}</div>
                <div class="label">DateRange Value Impact</div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('damage-return-lost.index') }}">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Type Filter</label>
                        <select name="type_filter" class="form-select">
                            <option value="">All Types</option>
                            <option value="1" {{ request('type_filter') == 1 ? 'selected' : '' }}>Damage</option>
                            <option value="2" {{ request('type_filter') == 2 ? 'selected' : '' }}>Lost</option>
                        </select>
                    </div>

                    {{-- <div class="col-md-2">
                        <label class="form-label">Vendor Filter</label>
                        <select name="vendor_filter" class="form-select select2">
                            <option value="">All Vendors</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}"
                                    {{ request('vendor_filter') == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->shop_name }}<span class="text-muted"> ({{ $vendor->mobile }})</span>
                                </option>
                            @endforeach
                        </select>
                    </div> --}}


                    <div class="col-md-3">
                        <label class="form-label">Vendor Filter</label>
                        <select name="vendor_filter" class="select2 form-select mt-1 p-1">
                            <option value="">All Vendors</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}"
                                    {{ request('vendor_filter') == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->shop_name }}<span class="text-muted"> ({{ $vendor->mobile }})</span>
                                </option>
                            @endforeach
                        </select>
                    </div>


                    <div class="col-md-3">
                        <label class="form-label">Product Search</label>
                        <input type="text" name="product_search" class="form-control"
                               value="{{ request('product_search') }}" placeholder="Search by product name or">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="mdi mdi-magnify"></i> Filter
                        </button>
                        <a href="{{ route('damage-return-lost.index') }}" class="btn btn-secondary me-2">
                            <i class="mdi mdi-refresh"></i> Reset
                        </a>
                        {{-- <a href="{{ route('damage-return-lost.create') }}" class="btn btn-danger float-end">
                            <i class="mdi mdi-plus"></i> Report New Issue
                        </a> --}}
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Records Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Damage/Return/Lost Records</h5>
            <div class="text-end">
                <a href="{{ route('damage-return-lost.create') }}" class="btn btn-danger float-end">
                            <i class="mdi mdi-plus"></i> Report New Issue
                        </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Type</th>
                            <th>Order/Product</th>
                            <th>Vendor</th>
                            <th>Quantity</th>
                            <th>Value</th>
                            <th>Reason</th>
                            <th>Evidence</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            <tr>
                                <td>
                                    <span class="type-badge type-{{ $record->status_text }}">
                                        {{ ucfirst($record->status_text) }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $record->order->invoice_id ?? 'N/A' }}</strong>
                                    <br><small class="text-muted">{{ $record->orderItem->product->name ?? 'N/A' }}</small>

                                </td>
                                <td>
                                    <strong>{{ $record?->order?->vendor->shop_name ?? 'N/A' }}</strong>
                                    <br><small class="text-muted">{{ $record?->order?->vendor->mobile ?? 'N/A' }}</small>
                                    <br><small class="text-muted">{{ $record?->order?->vendor->full_address ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <strong class="text-danger">{{ number_format($record?->quantity) }}</strong>
                                    <br><small class="text-muted">puchase ৳{{ number_format($record?->purchase_price, 2) }}</small>

                                </td>
                                <td>
                                    <strong class="text-danger">৳{{ number_format($record?->total_price, 2) }}</strong>
                                </td>
                                <td>
                                    <div style="max-width: 200px;">
                                        {{ Str::limit($record?->reason, 50) }}
                                        @if(strlen($record?->reason) > 50)
                                            <br><a href="#" class="text-primary" data-bs-toggle="tooltip"
                                                   title="{{ $record?->reason }}">Read more</a>
                                        @endif
                                    </div>
                                </td>
                                <td>

                                    @if($record->hasMedia('evidence_pic'))
                                        <div class="evidence-images">
                                            @foreach($record->getMedia('evidence_pic')->take(3) as $media)
                                                <img src="{{ $media->getUrl() }}" alt="Evidence"
                                                     class="evidence-thumb"
                                                     onclick="showImageModal('{{ $media->getUrl() }}')">
                                            @endforeach
                                            @if($record->getMedia('evidence_pic')->count() > 3)
                                                <span class="badge bg-secondary">
                                                    +{{ $record->getMedia('evidence_pic')->count() - 3 }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">No evidence</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $record->created_at->format('M d, Y') }}
                                    <br><small class="text-muted">{{ $record->created_at->diffForHumans() }}</small>
                                    @if($record->reported_by)
                                        <br><small class="text-muted">By: {{ $record->reported_by }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('damage-return-lost.show', $record) }}"
                                           class="btn btn-outline-info" title="View Details">
                                            <i class="mdi mdi-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger" title="Delete"
                                                onclick="confirmDelete({{ $record->id }})">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="mdi mdi-shield-check" style="font-size: 3rem; color: #ccc;"></i>
                                    <br>No damage/return/lost records found
                                    <br><small class="text-muted">All orders are in good condition!</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($records->hasPages())
                <div class="mt-3">
                    {{ $records->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Evidence Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="Evidence" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this record? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('custome-js')
<script>
function showImageModal(imageUrl) {
    document.getElementById('modalImage').src = imageUrl;
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
}

function confirmDelete(recordId) {

    //  Use a route placeholder generated by Blade, then replace :id in JS
    let url = '{{ route("damage-return-lost.destroy", ":id") }}';
    url = url.replace(':id', recordId);

    document.getElementById('deleteForm').action = url;

    // Show bootstrap modal
    const modalEl = document.getElementById('deleteModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
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
