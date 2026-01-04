<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Invoice {{ $order->invoice_id }}</title>

    <style>
        @font-face {
            font-family: 'kalpurush';
            src: url("{{ storage_path('fonts/kalpurush.ttf') }}") format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        /* RESET */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            color: #000 !important;
        }

        body {
            font-family: 'DejaVu Sans', 'kalpurush', Arial, sans-serif;
            direction: ltr;
            background: #fff;
            font-size: 10pt;
            line-height: 1.25;
        }

        .bangla-text {
            font-family: 'kalpurush', 'DejaVu Sans', sans-serif;
        }

        /* PAGE */
        @page {
            size: A4;
            margin: 3mm;
        }

        .page-wrap {
            max-width: 190mm;
            margin: auto;
            background: #fff;
        }

        /* COPY */
        .copy {
            border: 1px solid #000;
            padding: 2mm;
            margin-bottom: 1mm;
        }

        /* HEADER */
        .invoice-header {
            display: table;
            width: 100%;
            border-bottom: 2px solid #000;
            margin-bottom: 2px;
            padding-bottom: 2px;
        }

        .company-info,
        .invoice-info {
            display: table-cell;
            vertical-align: top;
        }

        .company-info {
            width: 60%;
        }

        .invoice-info {
            width: 35%;
            text-align: right;
        }

        .company-logo {
            max-width: 65px;
            margin-bottom: 2px;
        }

        .company-name {
            font-size: 14pt;
            font-weight: bold;
        }

        .company-details {
            font-size: 9pt;
            line-height: 1.15;
        }

        .invoice-title {
            font-size: 16pt;
            font-weight: bold;
        }

        .invoice-meta {
            border: 1px solid #000;
            padding: 3px;
            font-size: 9pt;
            display: inline-block;
            text-align: left;
        }

        /* BILL / ACCOUNT SUMMARY */
        .vendor-section {
            display: table;
            width: 100%;
            margin: 2px 0;
            border-spacing: 4px;
        }

        .bill-to,
        .ship-to {
            display: table-cell;
            width: 50%;
            border: 1px solid #000;
            padding: 4px;
            font-size: 9pt;
            vertical-align: top;
        }

        .section-title {
            font-weight: bold;
            border-bottom: 1px solid #000;
            margin-bottom: 2px;
            padding-bottom: 1px;
        }

        .vendor-name {
            font-weight: bold;
            font-size: 10pt;
        }

        /* ITEMS TABLE */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3px;
            font-size: 9pt;
            table-layout: fixed;
            line-height: 1.1;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 1.5px;
            vertical-align: top;
        }

        .items-table th {
            font-weight: bold;
            text-align: center;
        }

        .items-table td {
            font-size: 8.5pt;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* TOTALS */
        .totals-section {
            display: table;
            width: 100%;
            margin-top: 2px;
        }

        .totals-left,
        .totals-right {
            display: table-cell;
            vertical-align: top;
        }

        .totals-left {
            width: 60%;
            padding-right: 4px;
        }

        .totals-right {
            width: 40%;
        }

        .payment-info {
            border: 1px solid #000;
            padding: 4px;
            font-size: 9pt;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }

        .totals-table td {
            border: 1px solid #000;
            padding: 3px;
        }

        .grand-total td {
            font-size: 10pt;
        }

        /* STATUS */
        .status-badge {
            border: 1px solid #000;
            padding: 1px 4px;
            font-weight: bold;
        }

        /* DUPLICATE */
        .duplicate-header {
            text-align: center;
            font-weight: bold;
            border: 1px solid #000;
            padding: 2px;
            margin: 2px 0;
        }

        .separator-line {
            border-top: 1px solid #000;
            margin: 2mm 0;
        }

        /* FOOTER */
        .footer {
            margin-top: 2px;
            font-size: 8pt;
            border-top: 1px solid #000;
            padding-top: 2px;
            text-align: center;
        }

        /* SCREEN ONLY */
        @media screen {
            body {
                background: #f5f5f5;
            }
        }

        /* PRINT — SPACING FIX (SAFE) */
        @media print {
            .ship-to div {
                display: block !important;
                height: auto !important;
                line-height: 1 !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            body {
                font-size: 9.5pt;
                line-height: 1.15;
            }

            .invoice-header {
                margin-bottom: 2px;
                padding-bottom: 2px;
            }

            .vendor-section {
                margin: 2px 0;
                border-spacing: 2px;
            }

            .totals-section {
                margin-top: 1px;
            }

            .items-table th,
            .items-table td {
                padding: 1px;
            }

            .bill-to,
            .ship-to,
            .payment-info,
            .invoice-meta {
                padding: 3px;
            }

            .footer {
                margin-top: 2px;
                padding-top: 1px;
            }

            section.copy {
                page-break-inside: auto;
            }
        }
    </style>








</head>

<body>
    <div class="page-wrap">
        <!-- FIRST COPY: COMPANY COPY -->
        <section class="copy allow-break">
            {{-- @if (isset($preview) && $preview)
                <div class="preview-notice">
                    INVOICE PREVIEW - NOT FOR OFFICIAL USE
                </div>
            @endif --}}

            <!-- Header -->
            <div class="invoice-header">
                <div class="company-info">
                    @if (!empty($companyInfo['logo']) && file_exists($companyInfo['logo']))
                        <img src="{{ $companyInfo['logo'] }}" alt="Logo" class="company-logo" />
                    @endif
                    <div class="company-name">{{ $companyInfo['name'] }}</div>
                    <div class="company-details">
                        {!! nl2br(e($companyInfo['address'])) !!}<br>
                        <b>Phone1:</b> {{ $companyInfo['phone'] }}<br>
                        <b>Phone2:</b> {{ $companyInfo['phone_two'] }}<br>
                        <b>Website:</b> {{ $companyInfo['website'] }}
                    </div>
                </div>

                <div class="invoice-info">
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-meta">
                        <strong>Invoice #:</strong> {{ $order->invoice_id }}<br>
                        <strong>Order Date:</strong> {{ $order->created_at->format('d M Y') }}<br>
                        <strong>Delivery Date: </strong><br>
                        <strong>Status:</strong>
                        <span
                            class="status-badge status-{{ $order->payment_status == 2 ? 'paid' : ($order->payment_status == 1 ? 'partial' : 'unpaid') }}">
                            {{ $order->payment_status_text }}
                        </span><br>
                        <strong>Order Status:</strong> {{ $order->orderStatus->name ?? 'N/A' }}
                    </div>
                </div>
            </div>

            <!-- Vendor / Account Summary -->
            <div class="vendor-section">
                <div class="bill-to">
                    <div class="section-title">Bill To:</div>
                    <div class="vendor-name">{{ $vendor->shop_name }}</div>
                    <div>{{ $vendor->mobile }}</div>
                    <div>{{ $vendor->address }}</div>
                    @if ($vendor->email)
                        <div>{{ $vendor->email }}</div>
                    @endif
                    <div>Sales By : {{ $order?->placeBy?->name }}</div>
                </div>

                <div class="ship-to">
                    <div class="section-title">Account Summary:</div>
                    <div> <b>All Time Due:</b> ৳ <b>{{ number_format($vendorTotalDue, 2) }}</b>
                    </div>
                    {{-- <div>Paid Amount: ৳ {{ number_format($vendorPaidAmount, 2) }}</div> --}}
                    <div>Current Invoice: ৳ {{ number_format($order->total_amount, 2) }}</div>
                    <div>Current Invoice Payment: ৳ {{ number_format($currentInvoicePayment, 2) }}
                    </div>
                    <div>Current Invoice Due: ৳
                        {{ number_format($order->total_amount - $currentInvoicePayment, 2) }}</div>
                    <div style="margin-top:4px;padding-top:3px;border-top:1px solid #ddd;">
                        {{-- <strong> New Balance Due: </strong>৳ --}}
                        {{-- <strong class="price">{{ number_format($vendorTotalDue + $order->total_amount - $vendorPaidAmount, 2) }}</strong> --}}
                        {{-- <strong class="price">{{ number_format($vendorTotalDue, 2) }}</strong> --}}
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <table class="items-table company-copy" role="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Damage</th>
                        <th class="text-center">Lost</th>
                        <th class="text-center">Return</th>
                        <th class="text-right">Unit Price</th>
                        <th class="text-right">Discount</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orderItems as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <h5>{{ $item->product->name ?? 'N/A' }}</h5>
                                @if ($item->product && $item->product->description)
                                    <br><small>{{ \Illuminate\Support\Str::limit($item->product->description, 60) }}</small>
                                @endif
                            </td>
                            <td class="text-center"> <span class="price">{{ number_format($item->quantity) }}</span>
                            </td>
                            <td class="text-center"> <span
                                    class="price">{{ number_format($item->damage_quantity ?? 0) }}</span></td>
                            <td class="text-center"> <span
                                    class="price">{{ number_format($item->lost_quantity ?? 0) }}</span></td>
                            <td class="text-center"> <span
                                    class="price">{{ number_format($item->return_quantity ?? 0) }}</span></td>
                            <td class="text-right"> ৳ <span
                                    class="price">{{ number_format($item->sell_price, 2) }}</span></td>
                            <td class="text-right">৳ <span
                                    class="price">{{ number_format($item->discount_price, 2) }}</span></td>
                            <td class="text-right">৳ <span
                                    class="price">{{ number_format($item->sell_price * ($item->quantity - ($item->damage_quantity + $item->lost_quantity + $item->return_quantity)) - $item->discount_price, 2) }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Totals & Payment Info -->
            <div class="totals-section">
                <div class="totals-left">
                    <div class="payment-info">
                        <div class="section-title">Payment Information</div>
                        <div><strong>Payment Terms:</strong> {{ $vendor->payment_terms ?? 'Net 1 days' }}</div>
                        {{-- <div><strong>Due Date:</strong> {{ $order->created_at->addDays(5)->format('d M Y') }}</div> --}}
                        @if ($order->payment_status > 0)
                            <div style="margin-top:4px;">
                                <strong>Payment Status:</strong>
                                <span
                                    class="status-badge status-{{ $order->payment_status == 2 ? 'paid' : 'partial' }}">
                                    {{ $order->payment_status_text }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="totals-right">
                    <table class="totals-table" role="table">
                        <tr>
                            <td class="total-label">Subtotal:</td>
                            <td class="text-right">
                                ৳ <span
                                    class="price">{{ number_format($orderItems->sum(function ($i) {return $i->sell_price * ($i->quantity - ($i->damage_quantity + $i->lost_quantity + $i->return_quantity)) - $i->discount_price;}),2) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="total-label">Total Discount:</td>
                            <td class="text-right">৳ <span
                                    class="price">{{ number_format($order->total_discount_amount, 2) }}</span></td>
                        </tr>
                        @if (($order->tax_amount ?? 0) > 0)
                            <tr>
                                <td class="total-label">Tax:</td>
                                <td class="text-right">৳ <span
                                        class="price">{{ number_format($order->tax_amount, 2) }}</span></td>
                            </tr>
                        @endif
                        <tr class="grand-total">
                            <td><strong>TOTAL AMOUNT:</strong></td>
                            <td class="text-right"> ৳ <strong>{{ number_format($order->total_amount, 2) }}</strong>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- <div class="footer">
                <div><strong>Thank you for your business!</strong></div>
                <div>Hotline: {{ $companyInfo['phone'] }} | {{ $companyInfo['phone_two'] }}</div>
                <div>Generated on {{ now()->format('d M Y H:i:s') }} | Invoice {{ $order->invoice_id }}</div>
            </div> --}}
        </section>
        <div class="separator-line"></div>
        <!-- SECOND COPY: CUSTOMER COPY -->
        <section class="copy allow-break">
            <div class="duplicate-header">CUSTOMER COPY</div>

            <!-- Header -->
            <div class="invoice-header">
                <div class="company-info">
                    @if (!empty($companyInfo['logo']) && file_exists($companyInfo['logo']))
                        <img src="{{ $companyInfo['logo'] }}" alt="Logo" class="company-logo" />
                    @endif
                    <div class="company-name">{{ $companyInfo['name'] }}</div>
                    <div class="company-details">
                        {!! nl2br(e($companyInfo['address'])) !!}<br>
                        <b>Phone1:</b> {{ $companyInfo['phone'] }}<br>
                        <b>Phone2:</b> {{ $companyInfo['phone_two'] }}<br>
                        {{-- <b>Website:</b> {{ $companyInfo['website'] }} --}}
                    </div>
                </div>

                <div class="invoice-info">
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-meta">
                        <strong>Invoice #:</strong> {{ $order->invoice_id }}<br>
                        <strong>Order Date:</strong> {{ $order->created_at->format('d M Y') }}<br>
                        <strong>Delivery Date:</strong> <br>
                        <strong>Status:</strong>
                        <span
                            class="status-badge status-{{ $order->payment_status == 2 ? 'paid' : ($order->payment_status == 1 ? 'partial' : 'unpaid') }}">
                            {{ $order->payment_status_text }}
                        </span><br>
                        <strong>Order Status:</strong> {{ $order->orderStatus->name ?? 'N/A' }}
                    </div>
                </div>
            </div>

            <!-- Vendor / Account Summary -->
            <div class="vendor-section">
                <div class="bill-to">
                    <div class="section-title">Bill To:</div>
                    <div class="vendor-name">{{ $vendor->shop_name }}</div>
                    <div>{{ $vendor->mobile }}</div>
                    <div>{{ $vendor->address }}</div>
                    @if ($vendor->email)
                        <div>{{ $vendor->email }}</div>
                    @endif
                    <div>Sales By : {{ $order?->placeBy?->name }}</div>
                </div>

                <div class="ship-to">
                    <div class="section-title">Account Summary:</div>
                    <div><b>All Time Due:</b> ৳ <b>{{ number_format($vendorTotalDue, 2) }}</b></div>
                    {{-- <div>Paid Amount: ৳ {{ number_format($vendorPaidAmount, 2) }}</div> --}}
                    <div>Current Invoice: ৳ {{ number_format($order->total_amount, 2) }}</div>
                    <div>Current Invoice Payment: ৳ {{ number_format($currentInvoicePayment, 2) }}</div>
                    <div>Current Invoice Due: ৳ {{ number_format($order->total_amount - $currentInvoicePayment, 2) }}
                    </div>
                    {{-- <div style="margin-top:4px;padding-top:3px;border-top:1px solid #ddd;">
                        <strong> New Balance Due: </strong> ৳
                        <strong>{{ number_format($vendorTotalDue, 2) }}</strong>
                    </div> --}}
                </div>
            </div>

            <!-- Items Table -->
            <table class="items-table customer-copy" role="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Damage</th>
                        <th class="text-center">Lost</th>
                        <th class="text-center">Return</th>
                        <th class="text-right">Unit Price</th>
                        <th class="text-right">Discount</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orderItems as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <h5>{{ $item->product->name ?? 'N/A' }}</h5>
                                @if ($item->product && $item->product->description)
                                    <br><small>{{ \Illuminate\Support\Str::limit($item->product->description, 60) }}</small>
                                @endif
                            </td>
                            <td class="text-center"> <span class="price">{{ number_format($item->quantity) }}</span>
                            </td>
                            <td class="text-center"> <span
                                    class="price">{{ number_format($item->damage_quantity ?? 0) }}</span></td>
                            <td class="text-center"> <span
                                    class="price">{{ number_format($item->lost_quantity ?? 0) }}</span></td>
                            <td class="text-center"> <span
                                    class="price">{{ number_format($item->return_quantity ?? 0) }}</span></td>
                            <td class="text-right"> ৳ <span
                                    class="price">{{ number_format($item->sell_price, 2) }}</span></td>
                            <td class="text-right">৳ <span
                                    class="price">{{ number_format($item->discount_price, 2) }}</span></td>
                            <td class="text-right">৳ <span
                                    class="price">{{ number_format($item->sell_price * ($item->quantity - ($item->damage_quantity + $item->lost_quantity + $item->return_quantity)) - $item->discount_price, 2) }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Totals & Payment Info -->
            <div class="totals-section">
                <div class="totals-left">
                    <div class="payment-info">
                        <div class="section-title">Payment Information</div>
                        <div><strong>Payment Terms:</strong> {{ $vendor->payment_terms ?? 'Net 1 days' }}</div>
                        {{-- <div><strong>Due Date:</strong> {{ $order->created_at->addDays(30)->format('d M Y') }}</div> --}}
                        @if ($order->payment_status > 0)
                            <div style="margin-top:4px;">
                                <strong>Payment Status:</strong>
                                <span
                                    class="status-badge status-{{ $order->payment_status == 2 ? 'paid' : 'partial' }}">
                                    {{ $order->payment_status_text }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="totals-right">
                    <table class="totals-table" role="table">
                        <tr>
                            <td class="total-label">Subtotal:</td>
                            <td class="text-right">
                                ৳ <span
                                    class="price">{{ number_format($orderItems->sum(function ($i) {return $i->sell_price * ($i->quantity - ($i->damage_quantity + $i->lost_quantity + $i->return_quantity)) - $i->discount_price;}),2) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="total-label">Total Discount:</td>
                            <td class="text-right">৳ <span
                                    class="price">{{ number_format($order->total_discount_amount, 2) }}</span></td>
                        </tr>
                        @if (($order->tax_amount ?? 0) > 0)
                            <tr>
                                <td class="total-label">Tax:</td>
                                <td class="text-right">৳{{ number_format($order->tax_amount, 2) }}</td>
                            </tr>
                        @endif
                        <tr class="grand-total">
                            <td><strong>TOTAL AMOUNT:</strong></td>
                            <td class="text-right">৳ <strong>{{ number_format($order->total_amount, 2) }}</strong>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="footer">
                <div><strong>Customer Copy - Please keep for your records</strong></div>
                <div>Hotline: {{ $companyInfo['phone'] }} | {{ $companyInfo['phone_two'] }}</div>
                <div>Generated on {{ now()->format('d M Y H:i:s') }} | Invoice {{ $order->invoice_id }}</div>
            </div>
        </section>
    </div>
</body>

</html>
