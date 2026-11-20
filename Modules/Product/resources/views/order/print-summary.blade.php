{{-- Loading Sheet: pending + confirm orders, one page per day, grouped by SR (place_by). Standalone print page. --}}
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Loading Sheet {{ $from->format('d M Y') }}{{ $from->ne($to) ? ' – ' . $to->format('d M Y') : '' }}</title>
    <style>
        @page { size: A4 portrait; margin: 8mm; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #e9ecef; color: #111; font: 11px/1.35 "Segoe UI", "Nirmala UI", "Noto Sans Bengali", Arial, sans-serif; }
        .toolbar { position: sticky; top: 0; z-index: 5; display: flex; gap: 8px; align-items: center; justify-content: center;
                   padding: 10px; background: #212529; color: #fff; }
        .toolbar button { border: 0; border-radius: 6px; padding: 7px 16px; font-size: 13px; cursor: pointer; }
        .toolbar .print { background: #0d6efd; color: #fff; }
        .toolbar .muted { color: #adb5bd; font-size: 12px; margin-left: 8px; }

        .day { width: 210mm; min-height: 0; margin: 14px auto; padding: 9mm 8mm; background: #fff;
               box-shadow: 0 2px 10px rgba(0,0,0,.12); page-break-after: always; break-after: page; }
        .day:last-of-type { page-break-after: auto; break-after: auto; }

        .head { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #111; padding-bottom: 5px; margin-bottom: 7px; }
        .head h1 { margin: 0; font-size: 17px; letter-spacing: .3px; }
        .head .biz { font-size: 11px; color: #444; }
        .head .date { text-align: right; font-size: 15px; font-weight: 700; }
        .head .date small { display: block; font-size: 10px; font-weight: 400; color: #555; }

        .stats { display: flex; gap: 6px; margin-bottom: 8px; }
        .stats div { flex: 1; border: 1px solid #ccc; border-radius: 4px; padding: 3px 6px; }
        .stats b { display: block; font-size: 13px; }

        .sr { display: grid; grid-template-columns: 1fr 38mm; gap: 6px; border: 1px solid #555; border-radius: 4px;
              padding: 5px; margin-bottom: 7px; page-break-inside: avoid; break-inside: avoid; }
        .sr-title { grid-column: 1 / -1; display: flex; justify-content: space-between; align-items: center;
                    background: #f1f3f5; margin: -5px -5px 0; padding: 4px 7px; border-bottom: 1px solid #555; font-size: 12px; }
        .sr-title b { font-size: 13px; }

        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #bbb; padding: 2px 4px; vertical-align: top; }
        th { background: #f8f9fa; font-size: 10px; text-align: left; }
        .num { text-align: right; white-space: nowrap; }
        .qty { font-size: 12px; font-weight: 700; }
        .tick { width: 18px; text-align: center; }
        .tick span { display: inline-block; width: 11px; height: 11px; border: 1px solid #333; }
        .vend { font-size: 9.5px; color: #444; }
        .co { font-size: 9px; color: #666; }
        .cap { font-weight: 700; font-size: 10px; margin: 5px 0 2px; text-transform: uppercase; color: #333; }
        .pt .vendors { font-size: 10.5px; line-height: 1.5; }
        .pt .vq { white-space: nowrap; }
        .pt .vq b { font-size: 11px; }
        .pt .pname { font-weight: 600; }
        tfoot td { font-weight: 700; background: #f8f9fa; }

        .sign { display: flex; flex-direction: column; gap: 5px; }
        .sign .box { border: 1px solid #999; border-radius: 3px; padding: 3px 4px; display: flex; flex-direction: column; justify-content: flex-end; min-height: 17mm; }
        .sign .box .line { border-top: 1px solid #333; padding-top: 1px; font-size: 9.5px; text-align: center; }
        .sign .note { min-height: 22mm; justify-content: flex-start; font-size: 9.5px; color: #555; }

        .day-foot { display: flex; justify-content: space-between; gap: 10mm; margin-top: 12mm; }
        .day-foot div { flex: 1; border-top: 1px solid #333; text-align: center; padding-top: 2px; font-size: 10px; }
        .empty { text-align: center; padding: 40mm 0; color: #666; font-size: 14px; }

        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .day { margin: 0; padding: 0; width: auto; min-height: 0; box-shadow: none; zoom: var(--fit, 1); }
        }
    </style>
</head>
<body>
<div class="toolbar">
    <button class="print" onclick="window.print()">🖨 Print</button>
    <button onclick="window.close()">Close</button>
    <span class="muted">{{ $days->count() }} page(s) · one page per day · Pending + Confirm orders only</span>
</div>

@forelse($days as $day)
    <section class="day">
        <div class="head">
            <div>
                <h1>Loading Sheet</h1>
                <div class="biz">{{ optional($business)->company_name ?? config('app.name') }}@if(optional($business)->mobile_one) · {{ $business->mobile_one }}@endif</div>
            </div>
            <div class="date">
                {{ $day['date']->format('d M Y') }} <span style="font-weight:400">({{ $day['date']->format('l') }})</span>
                <small>Status: Pending + Confirm · Printed {{ now()->format('d M Y h:i A') }}</small>
            </div>
        </div>

        <div class="stats">
            <div>SR<b>{{ $day['groups']->count() }}</b></div>
            <div>Orders<b>{{ $day['totals']['orders'] }}</b></div>
            <div>Vendors<b>{{ $day['totals']['vendors'] }}</b></div>
            <div>Total Qty<b>{{ number_format($day['totals']['qty']) }}</b></div>
            <div>Amount<b>৳{{ number_format($day['totals']['amount'], 2) }}</b></div>
        </div>

        @foreach($day['groups'] as $g)
            <div class="sr">
                <div class="sr-title">
                    <span>SR: <b>{{ $g['sr'] }}</b></span>
                    <span>{{ $g['totals']['orders'] }} orders · {{ $g['totals']['vendors'] }} vendors · Qty {{ number_format($g['totals']['qty']) }} · ৳{{ number_format($g['totals']['amount'], 2) }}</span>
                </div>

                <div>
                    <table class="pt">
                        <thead>
                        <tr>
                            <th style="width:20px">#</th>
                            <th>Vendors (qty)</th>
                            <th style="width:30%">Product</th>
                            <th class="num" style="width:48px">Total Qty</th>
                            <th class="tick">✓</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($g['products'] as $i => $p)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td class="vendors">
                                    @foreach($p['vendors'] as $v)<span class="vq">{{ $v['name'] }} <b>{{ $v['qty'] }}</b></span>@if(!$loop->last), @endif @endforeach
                                </td>
                                <td class="pname">{{ $p['name'] }}</td>
                                <td class="num"><span class="qty">{{ number_format($p['qty']) }}</span>@if($p['pack'])<div class="co">{{ $p['pack'] }}</div>@endif</td>
                                <td class="tick"><span></span></td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr><td colspan="3">Total ({{ $g['totals']['vendors'] }} vendors · {{ $g['totals']['orders'] }} orders)</td><td class="num">{{ number_format($g['totals']['qty']) }}</td><td></td></tr>
                        </tfoot>
                    </table>
                </div>

                <div class="sign">
                    <div class="box"><div class="line">SR signature</div></div>
                    <div class="box"><div class="line">DSR (received)</div></div>
                    <div class="box"><div class="line">Store / Manager</div></div>
                    <div class="box note">Notes / short qty:</div>
                </div>
            </div>
        @endforeach

        <div class="day-foot">
            <div>Prepared by</div>
            <div>DSR</div>
            <div>Authorized by</div>
        </div>
    </section>
@empty
    <section class="day">
        <div class="empty">
            No pending or confirmed orders from {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}.
        </div>
    </section>
@endforelse

<div id="mm" style="position:absolute;top:0;left:0;visibility:hidden;height:100mm;width:1px"></div>
<script>
// Shrink a busy day so it always prints on ONE page. 250mm fits A4 (281mm) and US Letter (263mm) with 8mm margins.
// Measured after fonts load (Bangla names change the height) and again just before printing.
(function () {
    var probe = document.getElementById('mm');
    var mm = probe.offsetHeight / 100;
    probe.remove(); // must not add an empty page when printing
    function fitDays() {
        document.querySelectorAll('.day').forEach(function (day) {
            var cs = getComputedStyle(day);
            var content = day.offsetHeight - parseFloat(cs.paddingTop) - parseFloat(cs.paddingBottom);
            var fit = Math.min(1, (250 * mm) / content);
            day.style.setProperty('--fit', fit.toFixed(3));
        });
    }
    fitDays();
    window.addEventListener('load', fitDays);
    window.addEventListener('beforeprint', fitDays);
    if (document.fonts && document.fonts.ready) { document.fonts.ready.then(fitDays); }
})();
</script>
@if($days->isNotEmpty())
<script>window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 300); });</script>
@endif
</body>
</html>
