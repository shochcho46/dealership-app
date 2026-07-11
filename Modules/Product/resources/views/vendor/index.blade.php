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
                        <button type="button" id="printAllQrBtn" class="btn btn-outline-dark me-2">
                            <span class="mdi mdi-qrcode"></span> Print All QR
                        </button>
                        <a href="{{ route('admin.vendorExport') }}" class="btn btn-success me-2">
                            <span class="mdi mdi-file-excel"></span> Export Excel
                        </a>
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
                                                            class="btn btn-sm btn-outline-success qr-btn"
                                                            data-uuid="{{ $vendor->uuid }}"
                                                            data-name="{{ $vendor->shop_name }}"
                                                            data-mobile="{{ $vendor->mobile }}"
                                                            data-contact="{{ $vendor->contact_person ?? 'N/A' }}"
                                                            data-address="{{ $vendor->full_address ?? 'N/A' }}"
                                                            title="QR Code">
                                                        <span class="mdi mdi-qrcode"></span>
                                                    </button>
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

<div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Vendor QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <canvas id="qrCanvas"></canvas>
                <h6 class="mt-3 mb-0" id="qrVendorName"></h6>
                <small class="text-muted d-block" id="qrBusinessInfo"></small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="qrPrintBtn">
                    <span class="mdi mdi-printer"></span> Print
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@php
    $vendorsQrData = $vendors->map(function ($vendor) {
        return [
            'uuid' => $vendor->uuid,
            'name' => $vendor->shop_name,
            'mobile' => $vendor->mobile,
            'contact' => $vendor->contact_person ?? 'N/A',
            'address' => $vendor->full_address ?? 'N/A',
        ];
    });

    $businessQrData = [
        'name' => $businessDetail->company_name ?? 'N/A',
        'mobileOne' => $businessDetail->mobile_one ?? 'N/A',
        'mobileTwo' => $businessDetail->mobile_two ?? 'N/A',
    ];
@endphp

@push('custome-js')
<script src="{{ asset('vendor/qrcode/qrcode.min.js') }}"></script>
<script>
    const allVendorsQrData = @json($vendorsQrData);
    const businessInfo = @json($businessQrData);

    function buildBusinessLine() {
        return businessInfo.name + ' | Hotline: ' + businessInfo.mobileOne + ' / ' + businessInfo.mobileTwo;
    }

    function buildQrText(d) {
        return 'Vendor ID: ' + d.uuid
            + '\nName: ' + d.name
            + '\nMobile: ' + d.mobile
            + '\nContact Person: ' + d.contact
            + '\nAddress: ' + d.address
            + '\nCompany: ' + businessInfo.name
            + '\nHotline: ' + businessInfo.mobileOne + ' / ' + businessInfo.mobileTwo;
    }

    async function openQrPrintWindow(vendorList) {
        const win = window.open('', '_blank');
        win.document.write(
            '<!DOCTYPE html><html><head><title>Vendor QR Codes</title><style>'
            + '@page { size: A4; margin: 10mm; }'
            + 'body { font-family: Arial, sans-serif; margin: 0; }'
            + '.qr-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; padding: 10px; }'
            + '.qr-item { text-align: center; border: 1px solid #ccc; border-radius: 6px; padding: 8px; page-break-inside: avoid; }'
            + '.qr-item img { width: 100%; max-width: 140px; height: auto; }'
            + '.qr-item h6 { margin: 6px 0 0; font-size: 12px; word-break: break-word; }'
            + '.qr-item small { display: block; font-size: 10px; color: #666; word-break: break-word; }'
            + '</style></head><body><div class="qr-grid" id="qrGrid"></div></body></html>'
        );
        win.document.close();

        const grid = win.document.getElementById('qrGrid');

        for (const v of vendorList) {
            const dataUrl = await QRCode.toDataURL(buildQrText(v), { width: 220, margin: 1 });
            const item = win.document.createElement('div');
            item.className = 'qr-item';
            item.innerHTML = '<img src="' + dataUrl + '" alt="QR code"><h6>' + v.name + '</h6>'
                + '<small>' + buildBusinessLine() + '</small>';
            grid.appendChild(item);
        }

        setTimeout(function () {
            win.focus();
            win.print();
        }, 300);
    }

    document.querySelectorAll('.qr-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const d = this.dataset;
            document.getElementById('qrVendorName').textContent = d.name;
            document.getElementById('qrBusinessInfo').textContent = buildBusinessLine();

            const canvas = document.getElementById('qrCanvas');
            QRCode.toCanvas(canvas, buildQrText(d), { width: 220, margin: 1 }, function (error) {
                if (error) console.error(error);
            });

            document.getElementById('qrPrintBtn').onclick = function () {
                openQrPrintWindow([{
                    uuid: d.uuid,
                    name: d.name,
                    mobile: d.mobile,
                    contact: d.contact,
                    address: d.address,
                }]);
            };

            new bootstrap.Modal(document.getElementById('qrModal')).show();
        });
    });

    document.getElementById('printAllQrBtn')?.addEventListener('click', function () {
        openQrPrintWindow(allVendorsQrData);
    });
</script>
@endpush
