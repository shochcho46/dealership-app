{{--
    Customer invoice for a confirmed website order.
    Public: /i/{code} (link in the confirmation SMS).  Admin: website-inquiries/{id}/invoice.
    Expects: $inquiry (WebsiteInquiry, priced), $products (Product models keyed by id), $business, $isAdmin.
--}}
@php
    use App\Support\Frontend;
    $companyName = optional($business)->company_name ?: config('app.name');
    $logoUrl     = $business && $business->hasMedia('logo') ? $business->getFirstMediaUrl('logo') : null;
    $phone       = optional($business)->mobile_one;
    $items       = $inquiry->items ?: [];
    $saved       = collect($items)->sum(fn ($i) => !empty($i['mrp_unit']) && $i['mrp_unit'] > $i['unit_price'] ? ($i['mrp_unit'] - $i['unit_price']) * $i['cases'] : 0);
    $date        = $inquiry->contacted_at ?: $inquiry->created_at;
@endphp
<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('Invoice') }} {{ $inquiry->invoice_number }} — {{ $companyName }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --ink: #0B1F3A; --muted: #64748b; --line: #e2e8f0; --teal: #0FB5A6; --soft: #f8fafc; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #eef2f6; color: var(--ink); font: 15px/1.5 "Hind Siliguri", "Segoe UI", system-ui, sans-serif; }
        .bar { max-width: 760px; margin: 16px auto 0; padding: 0 16px; display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
        .bar a, .bar button { font: inherit; font-weight: 600; font-size: 14px; border: 0; border-radius: 10px; padding: 9px 14px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .b-print { background: var(--ink); color: #fff; }
        .b-wa { background: #25D366; color: #fff; }
        .b-back { background: #fff; color: var(--ink); border: 1px solid var(--line) !important; margin-right: auto; }
        .inv { max-width: 760px; margin: 12px auto 32px; background: #fff; border-radius: 18px; box-shadow: 0 10px 30px rgba(11,31,58,.08); overflow: hidden; }
        .head { display: flex; justify-content: space-between; gap: 16px; flex-wrap: wrap; padding: 24px; border-bottom: 1px solid var(--line); }
        .brand { display: flex; gap: 12px; align-items: center; }
        .brand img { width: 52px; height: 52px; object-fit: contain; border-radius: 10px; }
        .brand h1 { margin: 0; font-size: 20px; }
        .brand p { margin: 0; color: var(--muted); font-size: 13px; }
        .meta { text-align: right; }
        .meta .tag { display: inline-block; background: #dcfce7; color: #166534; font-weight: 700; font-size: 12px; padding: 3px 10px; border-radius: 999px; }
        .meta h2 { margin: 6px 0 0; font-size: 18px; letter-spacing: .5px; }
        .meta p { margin: 0; color: var(--muted); font-size: 13px; }
        .bill { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; padding: 18px 24px; background: var(--soft); border-bottom: 1px solid var(--line); }
        .bill h3 { margin: 0 0 4px; font-size: 12px; text-transform: uppercase; letter-spacing: .08em; color: var(--muted); }
        .bill p { margin: 0; }
        .bill .name { font-weight: 700; font-size: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); padding: 12px 8px; border-bottom: 1px solid var(--line); }
        td { padding: 12px 8px; border-bottom: 1px solid var(--line); vertical-align: middle; }
        th:first-child, td:first-child { padding-left: 24px; }
        th:last-child, td:last-child { padding-right: 24px; text-align: right; }
        .num { text-align: right; white-space: nowrap; }
        .prod { display: flex; gap: 10px; align-items: center; }
        .prod img { width: 44px; height: 44px; object-fit: contain; border: 1px solid var(--line); border-radius: 8px; background: #fff; flex: none; }
        .prod b { display: block; font-weight: 600; }
        .prod small, .mrp { color: var(--muted); font-size: 12px; }
        .totals { padding: 16px 24px 24px; display: flex; justify-content: flex-end; }
        .totals dl { margin: 0; min-width: 260px; }
        .totals div { display: flex; justify-content: space-between; gap: 24px; padding: 5px 0; }
        .totals dt { color: var(--muted); }
        .totals dd { margin: 0; font-weight: 600; }
        .totals .grand { border-top: 2px solid var(--ink); margin-top: 6px; padding-top: 10px; }
        .totals .grand dt { color: var(--ink); font-weight: 700; font-size: 17px; }
        .totals .grand dd { font-size: 22px; font-weight: 800; }
        .totals .save dd { color: #15803d; }
        .note { margin: 0 24px 20px; padding: 12px 14px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; font-size: 14px; }
        .foot { text-align: center; color: var(--muted); font-size: 13px; padding: 16px 24px 24px; border-top: 1px dashed var(--line); }
        @media (max-width: 560px) {
            .head, .bill { padding: 16px; } .meta { text-align: left; }
            .bill { grid-template-columns: 1fr; }
            th:first-child, td:first-child { padding-left: 16px; } th:last-child, td:last-child { padding-right: 16px; }
            .col-price { display: none; }
            .prod img { width: 36px; height: 36px; }
            .totals { padding: 12px 16px 20px; } .totals dl { min-width: 0; width: 100%; }
        }
        /* Print: half A4 (A5 portrait, 148 × 210 mm), compact so a normal order fits one sheet. */
        @page { size: A5 portrait; margin: 7mm; }
        @media print {
            html, body { background: #fff; font-size: 10.5px; line-height: 1.35; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .bar { display: none !important; }
            .inv { box-shadow: none; margin: 0; max-width: none; border-radius: 0; }
            .head { padding: 0 0 8px; gap: 8px; flex-wrap: nowrap; }
            .brand { gap: 8px; }
            .brand img { width: 34px; height: 34px; border-radius: 6px; }
            .brand h1 { font-size: 14px; }
            .brand p, .meta p { font-size: 9.5px; }
            .meta { text-align: right; }
            .meta .tag { font-size: 9px; padding: 1px 7px; }
            .meta h2 { font-size: 12.5px; margin-top: 3px; }
            .bill { grid-template-columns: 1fr 1fr; gap: 8px; padding: 7px 8px; }
            .bill h3 { font-size: 8.5px; margin-bottom: 1px; }
            .bill .name { font-size: 11.5px; }
            th { font-size: 8.5px; padding: 5px 4px; }
            td { padding: 5px 4px; }
            th:first-child, td:first-child { padding-left: 0; }
            th:last-child, td:last-child { padding-right: 0; }
            tr { break-inside: avoid; }
            .col-price { display: table-cell !important; }
            .prod { gap: 6px; }
            .prod img { width: 24px; height: 24px; border-radius: 4px; }
            .prod small, .mrp { font-size: 8.5px; }
            .totals { padding: 6px 0 8px; }
            .totals { justify-content: flex-end; }
            .totals dl { min-width: 55%; width: auto; }
            .totals div { padding: 2px 0; gap: 12px; }
            .totals .grand { margin-top: 3px; padding-top: 5px; }
            .totals .grand dt { font-size: 12px; }
            .totals .grand dd { font-size: 15px; }
            .note { margin: 0 0 8px; padding: 5px 8px; font-size: 9.5px; border-radius: 6px; }
            .foot { padding: 6px 0 0; font-size: 9px; }
        }
    </style>
</head>
<body>
<div class="bar">
    @if($isAdmin)
        <a href="{{ route('website-inquiries.show', $inquiry) }}" class="b-back">← Back to order</a>
        {{-- Send the invoice to the customer on WhatsApp --}}
        @php
            $waText = "Dear {$inquiry->name}, your order is confirmed.\n"
                . __('Invoice') . " {$inquiry->invoice_number}\n"
                . __('Total') . ': ' . Frontend::money($inquiry->total) . "\n"
                . $inquiry->invoice_url . "\n- {$companyName}";
        @endphp
        <a class="b-wa" target="_blank" rel="noopener" href="https://wa.me/{{ $inquiry->whatsapp_number }}?text={{ rawurlencode($waText) }}"
           title="Send this invoice to {{ $inquiry->mobile }} on WhatsApp">WhatsApp customer</a>
    @endif
    @if(!$isAdmin && $phone)
        <a class="b-wa" target="_blank" rel="noopener" href="{{ Frontend::waLink($phone, __('Invoice') . ' ' . $inquiry->invoice_number) }}">WhatsApp</a>
    @endif
    <button type="button" class="b-print" onclick="window.print()">🖨 {{ __('Print / Save PDF') }}</button>
</div>

<main class="inv">
    <header class="head">
        <div class="brand">
            @if($logoUrl)<img src="{{ $logoUrl }}" alt="{{ $companyName }}">@endif
            <div>
                <h1>{{ $companyName }}</h1>
                @if(optional($business)->address)<p>{{ $business->address }}</p>@endif
                <p>{{ collect([$phone, optional($business)->mobile_two])->filter()->implode(' · ') }}</p>
            </div>
        </div>
        <div class="meta">
            <span class="tag">✓ {{ __('Order confirmed') }}</span>
            <h2>{{ __('Invoice') }} {{ $inquiry->invoice_number }}</h2>
            <p>{{ $date->format('d M Y, h:i A') }}</p>
        </div>
    </header>

    <section class="bill">
        <div>
            <h3>{{ __('Bill to') }}</h3>
            <p class="name">{{ $inquiry->name }}</p>
            <p>{{ $inquiry->mobile }}</p>
        </div>
        <div>
            <h3>{{ __('Deliver to') }}@if($inquiry->delivery_type) · {{ $inquiry->delivery_type === 'home' ? __('deliver_home') : __('deliver_shop') }}@endif</h3>
            <p>{{ $inquiry->area ?: '—' }}</p>
        </div>
    </section>

    <table>
        <thead>
        <tr>
            <th>{{ __('Products') }}</th>
            <th class="num">{{ __('Qty') }}</th>
            <th class="num col-price">{{ __('Price') }}</th>
            <th>{{ __('Amount') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($items as $item)
            @php
                $model = $products->get($item['product_id'] ?? 0);
                $unit  = ($item['pack_unit'] ?? 'piece') === 'piece' ? __('piece') : __($item['pack_unit']);
            @endphp
            <tr>
                <td>
                    <div class="prod">
                        <img src="{{ $model ? $model->website_image_url : asset('frontend/v2/img/product-placeholder.svg') }}" alt="" loading="lazy">
                        <div>
                            <b>{{ $item['name'] ?? 'Product' }}</b>
                            <small>{{ Frontend::money($item['unit_price'] ?? 0) }} / {{ $unit }}@if(!empty($item['per_pack'])) ({{ Frontend::num($item['per_pack']) }} {{ __('pcs') }})@endif</small>
                        </div>
                    </div>
                </td>
                <td class="num">{{ Frontend::num($item['cases'] ?? 1) }} {{ $unit }}</td>
                <td class="num col-price">
                    {{ Frontend::money($item['unit_price'] ?? 0) }}
                    @if(!empty($item['mrp_unit']) && $item['mrp_unit'] > ($item['unit_price'] ?? 0))
                        <div class="mrp">{{ __('MRP') }} <del>{{ Frontend::money($item['mrp_unit']) }}</del></div>
                    @endif
                </td>
                <td>{{ Frontend::money($item['line_total'] ?? 0) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <section class="totals">
        <dl>
            <div><dt>{{ __('Subtotal') }}</dt><dd>{{ Frontend::money($inquiry->subtotal) }}</dd></div>
            <div><dt>{{ __('Delivery fee') }}</dt><dd>{{ Frontend::money($inquiry->delivery_fee) }}</dd></div>
            @if($saved > 0)
                <div class="save"><dt>{{ __('You save') }}</dt><dd>{{ Frontend::money($saved) }}</dd></div>
            @endif
            <div class="grand"><dt>{{ __('Total') }}</dt><dd>{{ Frontend::money($inquiry->total) }}</dd></div>
        </dl>
    </section>

    @if($inquiry->message)
        <p class="note"><b>{{ __('Note') }}:</b> {{ $inquiry->message }}</p>
    @endif

    <footer class="foot">
        {{ __('Thank you for your order!') }}
        @if($phone) · {{ __('Questions? Call or WhatsApp us') }}: {{ $phone }}@endif
    </footer>
</main>
</body>
</html>
