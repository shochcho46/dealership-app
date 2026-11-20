@extends('layouts.app')

@push('custome-css')
<style>
    .inq-thumb { width: 52px; height: 52px; object-fit: contain; border: 1px solid #e9ecef; border-radius: 8px; background: #fff; }
    .inq-table input[type=number] { max-width: 120px; }
    .inq-totals dt { font-weight: 500; color: #6c757d; }
    .inq-totals dd { font-weight: 700; text-align: right; margin-bottom: .4rem; }
    .inq-totals .grand { font-size: 1.35rem; color: #0B1F3A; }
    .sms-preview { white-space: pre-line; background: #e7f5ec; border-radius: 12px 12px 12px 4px; padding: 10px 12px; font-size: .9rem; }
    tr.is-removed { opacity: .4; text-decoration: line-through; }
</style>
@endpush

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Inquiry {{ $inquiry->invoice_number }}
                    <span class="badge {{ $inquiry->status === 0 ? 'text-bg-warning' : ($inquiry->status === 1 ? 'text-bg-info' : 'text-bg-secondary') }} fs-6 align-middle">{{ $inquiry->status_label }}</span>
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('website-inquiries.index') }}">Website Inquiries</a></li>
                    <li class="breadcrumb-item active">{{ $inquiry->invoice_number }}</li>
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
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        @php $locked = $inquiry->isPriceLocked(); @endphp
        @if($locked)
            <div class="alert alert-info d-flex align-items-center gap-2">
                <span class="mdi mdi-lock fs-5"></span>
                <div>
                    <strong>Order {{ $inquiry->contacted_at ? 'confirmed on ' . $inquiry->contacted_at->format('d M Y, h:i A') : 'closed' }}.</strong>
                    Prices, quantities and the delivery fee are locked and can't be changed.
                </div>
            </div>
        @endif

        <div class="row">
            {{-- Order items + prices --}}
            <div class="col-lg-8">
                <form method="POST" action="{{ route('website-inquiries.pricing', $inquiry) }}">
                    @csrf
                    @method('PUT')
                    <div class="card card-primary card-outline mb-4">
                        <div class="card-header d-flex align-items-center">
                            <h3 class="card-title mb-0"><span class="mdi mdi-cart-outline"></span> Ordered products</h3>
                            <small class="text-muted ms-auto">@if($locked)<span class="mdi mdi-lock"></span> Locked @else Prices were saved when the customer ordered. Change them here before confirming. @endif</small>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0 inq-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:64px"></th>
                                            <th>Product</th>
                                            <th>Qty</th>
                                            <th>Unit price (৳)</th>
                                            <th class="text-end">Amount</th>
                                            @unless($locked)<th class="text-center" title="Remove line"><span class="mdi mdi-delete-outline"></span></th>@endunless
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($inquiry->items ?: [] as $i => $item)
                                            @php $pm = $products->get($item['product_id'] ?? 0); @endphp
                                            <tr data-line>
                                                <td><img class="inq-thumb" src="{{ $pm ? $pm->website_image_url : asset('frontend/v2/img/product-placeholder.svg') }}" alt=""></td>
                                                <td>
                                                    <strong>{{ $item['name'] ?? 'Product' }}</strong>
                                                    <br><small class="text-muted">
                                                        per {{ strtolower($item['pack_unit'] ?? 'piece') }}@if(!empty($item['per_pack'])) ({{ $item['per_pack'] }} pcs)@endif
                                                        @if(!empty($item['mrp_unit'])) · MRP ৳{{ number_format($item['mrp_unit'], 2) }}@endif
                                                    </small>
                                                </td>
                                                <td><input type="number" min="1" step="1" class="form-control form-control-sm" data-qty @disabled($locked)
                                                           name="items[{{ $i }}][cases]" value="{{ old("items.$i.cases", $item['cases'] ?? 1) }}"></td>
                                                <td><input type="number" min="0" step="0.01" class="form-control form-control-sm" data-price @disabled($locked)
                                                           name="items[{{ $i }}][unit_price]" value="{{ old("items.$i.unit_price", $item['unit_price'] ?? 0) }}"></td>
                                                <td class="text-end fw-semibold text-nowrap" data-amount>৳{{ number_format($item['line_total'] ?? 0, 2) }}</td>
                                                @unless($locked)<td class="text-center"><input type="checkbox" class="form-check-input" name="remove[{{ $i }}]" value="1" data-remove title="Remove this line"></td>@endunless
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="text-center text-muted py-4">General inquiry — no products.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row align-items-end">
                                <div class="col-md-5 mb-3 mb-md-0">
                                    <label for="delivery_fee" class="form-label fw-semibold"><span class="mdi mdi-truck-delivery-outline"></span> Delivery fee</label>
                                    <div class="input-group">
                                        <span class="input-group-text">৳</span>
                                        <input type="number" min="0" step="0.01" class="form-control" id="delivery_fee" name="delivery_fee"
                                               value="{{ old('delivery_fee', $inquiry->delivery_fee + 0) }}" data-fee @disabled($locked)>
                                    </div>
                                    <small class="text-muted">@if($locked)<span class="mdi mdi-lock"></span> Locked after confirmation. @else Shown on the SMS and invoice. 0 = free delivery. Locked once marked Contacted. @endif</small>
                                </div>
                                <div class="col-md-7">
                                    <dl class="row inq-totals mb-2">
                                        <dt class="col-7">Subtotal</dt><dd class="col-5" data-subtotal>৳{{ number_format($inquiry->subtotal, 2) }}</dd>
                                        <dt class="col-7">Delivery fee</dt><dd class="col-5" data-fee-out>৳{{ number_format($inquiry->delivery_fee, 2) }}</dd>
                                        <dt class="col-7 grand">Total</dt><dd class="col-5 grand" data-total>৳{{ number_format($inquiry->total, 2) }}</dd>
                                    </dl>
                                    @unless($locked)
                                        <button type="submit" class="btn btn-primary w-100"><span class="mdi mdi-content-save"></span> Save prices &amp; delivery fee</button>
                                    @endunless
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Customer, status, SMS, invoice --}}
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header"><h3 class="card-title"><span class="mdi mdi-account"></span> Customer</h3></div>
                    <div class="card-body">
                        <h5 class="mb-1">{{ $inquiry->name }}</h5>
                        <p class="mb-2"><a href="tel:{{ $inquiry->mobile }}">{{ $inquiry->mobile }}</a></p>
                        <div class="d-flex gap-2 mb-3">
                            <a href="tel:{{ $inquiry->mobile }}" class="btn btn-sm btn-outline-primary"><span class="mdi mdi-phone"></span> Call</a>
                            <a href="https://wa.me/{{ $inquiry->whatsapp_number }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success"><span class="mdi mdi-whatsapp"></span> WhatsApp</a>
                        </div>
                        <dl class="mb-0 small">
                            <dt>Delivery</dt>
                            <dd>{{ $inquiry->delivery_type ? (\Modules\Product\Models\WebsiteInquiry::DELIVERY_TYPES[$inquiry->delivery_type] ?? $inquiry->delivery_type) . ' delivery' : '—' }}</dd>
                            <dt>Place / address</dt><dd>{{ $inquiry->area ?: '—' }}</dd>
                            <dt>Note</dt><dd style="white-space: pre-line">{{ $inquiry->message ?: '—' }}</dd>
                            <dt>Received</dt><dd class="mb-0">{{ $inquiry->created_at->format('d M Y, h:i A') }} <span class="text-muted">· IP {{ $inquiry->ip }}</span></dd>
                        </dl>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header"><h3 class="card-title"><span class="mdi mdi-progress-check"></span> Status &amp; SMS</h3></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('website-inquiries.update', $inquiry) }}" class="mb-3">
                            @csrf
                            @method('PUT')
                            <label class="form-label small mb-1">Status</label>
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                @foreach(\Modules\Product\Models\WebsiteInquiry::STATUSES as $value => $label)
                                    <option value="{{ $value }}" @selected($inquiry->status === $value)
                                            @disabled($value === \Modules\Product\Models\WebsiteInquiry::STATUS_NEW && !$inquiry->canReopen())>{{ $label }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">
                                @if($smsOn) Marking <b>Contacted</b> confirms the order: prices are locked and the SMS is sent once. @else Marking <b>Contacted</b> confirms and locks prices. Inquiry SMS is <b>off</b> (Website Settings). @endif
                            </small>
                            @if($inquiry->handledBy)<div class="small text-muted mt-1">Last changed by {{ $inquiry->handledBy->name }}</div>@endif
                        </form>

                        <div class="small mb-2">
                            SMS:
                            @if($inquiry->sms_status === 'sent')
                                <span class="badge text-bg-success">Sent {{ optional($inquiry->sms_sent_at)->format('d M, h:i A') }}</span>
                            @elseif($inquiry->sms_status === 'failed')
                                <span class="badge text-bg-danger" title="{{ $inquiry->sms_response }}">Failed</span>
                                <div class="text-danger mt-1">{{ $inquiry->sms_response }}</div>
                            @elseif($inquiry->sms_status === 'disabled')
                                <span class="badge text-bg-light border">Not sent (SMS off)</span>
                            @else
                                <span class="badge text-bg-light border">Not sent yet</span>
                            @endif
                        </div>
                        <div class="sms-preview mb-2">{{ $inquiry->confirmationSms() }}</div>

                        @if($inquiry->has_invoice)
                            <form method="POST" action="{{ route('website-inquiries.sendSms', $inquiry) }}" class="d-grid mb-2"
                                  onsubmit="return confirm('Send the confirmation SMS to {{ $inquiry->mobile }}?')">
                                @csrf
                                <button type="submit" class="btn btn-outline-success"><span class="mdi mdi-send"></span> {{ $inquiry->sms_sent_at ? 'Resend SMS' : 'Send SMS now' }}</button>
                            </form>
                            <div class="d-grid gap-2">
                                <a href="{{ route('website-inquiries.invoice', $inquiry) }}" target="_blank" class="btn btn-dark"><span class="mdi mdi-file-document-outline"></span> Invoice</a>
                                <button type="button" class="btn btn-outline-secondary" data-copy="{{ $inquiry->invoice_url }}"><span class="mdi mdi-link-variant"></span> Copy customer invoice link</button>
                            </div>
                        @else
                            <div class="alert alert-light border small mb-0"><span class="mdi mdi-information-outline"></span> The invoice and SMS are available after the inquiry is marked <b>Contacted</b>.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('custome-js')
<script>
(function () {
    function money(v) { return '৳' + v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function recalc() {
        var sub = 0;
        document.querySelectorAll('tr[data-line]').forEach(function (tr) {
            var q = parseFloat(tr.querySelector('[data-qty]').value) || 0;
            var p = parseFloat(tr.querySelector('[data-price]').value) || 0;
            var rm = tr.querySelector('[data-remove]');
            var removed = rm ? rm.checked : false;
            tr.classList.toggle('is-removed', removed);
            tr.querySelector('[data-amount]').textContent = money(q * p);
            if (!removed) sub += q * p;
        });
        var fee = parseFloat((document.querySelector('[data-fee]') || {}).value) || 0;
        document.querySelector('[data-subtotal]').textContent = money(sub);
        document.querySelector('[data-fee-out]').textContent = money(fee);
        document.querySelector('[data-total]').textContent = money(sub + fee);
    }
    document.querySelectorAll('[data-qty],[data-price],[data-fee],[data-remove]').forEach(function (el) {
        el.addEventListener('input', recalc); el.addEventListener('change', recalc);
    });
    document.querySelectorAll('[data-copy]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var url = btn.getAttribute('data-copy');
            (navigator.clipboard ? navigator.clipboard.writeText(url) : Promise.reject()).then(function () {
                btn.innerHTML = '<span class="mdi mdi-check"></span> Link copied';
            }, function () { window.prompt('Invoice link:', url); });
        });
    });
})();
</script>
@endpush
