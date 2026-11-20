@extends('layouts.app')

@push('custome-css')
<style>
    .sms-preview { white-space: pre-line; font-family: inherit; background: #e7f5ec; border-radius: 14px 14px 14px 4px; padding: 12px 14px; max-width: 360px; font-size: .95rem; }
    .setting-card .form-switch .form-check-input { width: 2.6em; height: 1.4em; }
</style>
@endpush

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3 class="mb-0">Website Settings</h3></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Website Settings</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <form method="POST" action="{{ route('website-settings.update') }}">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-lg-6">
                    <div class="card card-primary card-outline mb-4 setting-card">
                        <div class="card-header"><h3 class="card-title"><span class="mdi mdi-truck-delivery-outline"></span> Delivery fee</h3></div>
                        <div class="card-body">
                            <div class="form-check form-switch mb-3">
                                <input type="hidden" name="delivery_fee_enabled" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" id="delivery_fee_enabled" name="delivery_fee_enabled" value="1"
                                       @checked(old('delivery_fee_enabled', $business->delivery_fee_enabled))>
                                <label class="form-check-label ms-2 fw-semibold" for="delivery_fee_enabled">Add a delivery fee to every website order</label>
                            </div>
                            <label for="delivery_fee" class="form-label">Delivery fee amount</label>
                            <div class="input-group" style="max-width: 240px">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="0" class="form-control @error('delivery_fee') is-invalid @enderror"
                                       id="delivery_fee" name="delivery_fee" value="{{ old('delivery_fee', $business->delivery_fee + 0) }}">
                                @error('delivery_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <small class="text-muted d-block mt-2">
                                When on, this amount is added to each new website order and shown on the website order list, SMS and invoice.
                                When off, the delivery fee is <b>৳0</b>. You can still change the fee for any single order on its details page.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card card-success card-outline mb-4 setting-card">
                        <div class="card-header"><h3 class="card-title"><span class="mdi mdi-message-text-outline"></span> Inquiry SMS</h3></div>
                        <div class="card-body">
                            <div class="form-check form-switch mb-3">
                                <input type="hidden" name="inquiry_sms_enabled" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" id="inquiry_sms_enabled" name="inquiry_sms_enabled" value="1"
                                       @checked(old('inquiry_sms_enabled', $business->inquiry_sms_enabled))>
                                <label class="form-check-label ms-2 fw-semibold" for="inquiry_sms_enabled">Send SMS when an inquiry is marked <span class="badge text-bg-info">Contacted</span></label>
                            </div>
                            <div class="small text-muted mb-1">Example message ({{ count(preg_split('/\s+/', trim($smsPreview))) }} words, {{ mb_strlen($smsPreview) }} characters):</div>
                            <div class="sms-preview mb-2">{{ $smsPreview }}</div>
                            <small class="text-muted">The link opens the customer's invoice. Sent once per order; you can resend from the order details page.</small>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mb-4"><span class="mdi mdi-content-save"></span> Save settings</button>
        </form>

        <div class="card mb-4" style="max-width: 560px">
            <div class="card-header"><h3 class="card-title"><span class="mdi mdi-cellphone-message"></span> Send a test SMS</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('website-settings.testSms') }}" class="d-flex gap-2">
                    @csrf
                    <input type="tel" name="test_mobile" class="form-control" placeholder="01XXXXXXXXX" required maxlength="20">
                    <button type="submit" class="btn btn-outline-success text-nowrap"><span class="mdi mdi-send"></span> Send test</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
