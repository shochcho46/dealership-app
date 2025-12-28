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

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'kalpurush', 'DejaVu Sans', Arial, sans-serif;
            direction: ltr;
            text-align: left;
            margin: 0;
            padding: 0;
            background: #fff;
            color: #333;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-size: 10pt;
            line-height: 1.3;
        }

        .bangla-text {
            font-family: 'kalpurush', 'DejaVu Sans', sans-serif;
            font-size: 10pt;
        }

        @page {
            size: A4;
            margin: 5mm;
        }

        .page-wrap {
            width: 100%;
            max-width: 190mm;
            margin: 0 auto;
            padding: 2mm;
            background: #fff;
        }

        .copy {
            width: 100%;
            padding: 3mm;
            margin-bottom: 2mm;
            background: #fff;
            border: 1px solid #e0e0e0;
            page-break-inside: avoid;
        }

        .copy.allow-break {
            page-break-inside: auto;
        }

        .invoice-header {
            display: table;
            width: 100%;
            margin-bottom: 5px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 3px;
        }

        .company-info {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }

        .company-logo {
            max-width: 70px;
            height: auto;
            margin-bottom: 3px;
            display: block;
        }

        .company-name {
            font-size: 14pt;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 3px;
        }

        .company-details {
            font-size: 9pt;
            color: #666;
            line-height: 1.2;
        }

        .invoice-info {
            display: table-cell;
            width: 35%;
            vertical-align: top;
            text-align: right;
            padding-left: 5px;
        }

        .invoice-title {
            font-size: 16pt;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 3px;
        }

        .invoice-meta {
            background: #f8f9fa;
            padding: 4px;
            border-radius: 3px;
            font-size: 9pt;
            display: inline-block;
            text-align: left;
            min-width: 50%;
        }

        .vendor-section {
            display: table;
            width: 100%;
            margin: 5px 0;
            border-spacing: 5px;
        }

        .bill-to,
        .ship-to {
            display: table-cell;
            vertical-align: top;
            padding: 5px;
            background: #f8f9fa;
            border-radius: 3px;
            font-size: 9pt;
        }

        .bill-to {
            width: 50%;
        }

        .ship-to {
            width: 50%;
        }

        .section-title {
            font-weight: bold;
            font-size: 10pt;
            color: #007bff;
            margin-bottom: 3px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 2px;
        }

        .vendor-name {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 2px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            font-size: 9pt;
            table-layout: fixed;
            word-wrap: break-word;
        }

        .items-table th {
            background: #007bff;
            color: #fff;
            padding: 2px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #007bff;
            font-size: 9pt;
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        .items-table td {
            padding: 2px;
            border: 1px solid #ddd;
            vertical-align: top;
            font-size: 8.25pt;
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        .items-table small {
            color: #666;
            font-size: 8pt;
        }

        /* Company copy table column widths (9 columns) */
        .items-table.company-copy th:nth-child(1),
        .items-table.company-copy td:nth-child(1) { width: 5%; }
    .items-table.company-copy th:nth-child(2),
    .items-table.company-copy td:nth-child(2) { width: 32%; }
        .items-table.company-copy th:nth-child(3),
        .items-table.company-copy td:nth-child(3) { width: 6%; }
        .items-table.company-copy th:nth-child(4),
        .items-table.company-copy td:nth-child(4) { width: 8%; }
        .items-table.company-copy th:nth-child(5),
        .items-table.company-copy td:nth-child(5) { width: 8%; }
        .items-table.company-copy th:nth-child(6),
        .items-table.company-copy td:nth-child(6) { width: 8%; }
        .items-table.company-copy th:nth-child(7),
        .items-table.company-copy td:nth-child(7) { width: 12%; }
    .items-table.company-copy th:nth-child(8),
    .items-table.company-copy td:nth-child(8) { width: 10%; }
    .items-table.company-copy th:nth-child(9),
    .items-table.company-copy td:nth-child(9) { width: 11%; }

        /* Alignment for company copy columns */
        /* Qty, Damage, Lost, Return (columns 3-6) center */
        .items-table.company-copy th:nth-child(3),
        .items-table.company-copy td:nth-child(3),
        .items-table.company-copy th:nth-child(4),
        .items-table.company-copy td:nth-child(4),
        .items-table.company-copy th:nth-child(5),
        .items-table.company-copy td:nth-child(5),
        .items-table.company-copy th:nth-child(6),
        .items-table.company-copy td:nth-child(6) {
            text-align: center;
        }

        /* Unit Price, Discount, Total (columns 7-9) right */
        .items-table.company-copy th:nth-child(7),
        .items-table.company-copy td:nth-child(7),
        .items-table.company-copy th:nth-child(8),
        .items-table.company-copy td:nth-child(8),
        .items-table.company-copy th:nth-child(9),
        .items-table.company-copy td:nth-child(9) {
            text-align: right;
        }

        /* Customer copy table column widths (9 columns) */
        .items-table.customer-copy th:nth-child(1),
        .items-table.customer-copy td:nth-child(1) { width: 5%; }
    .items-table.customer-copy th:nth-child(2),
    .items-table.customer-copy td:nth-child(2) { width: 32%; }
        .items-table.customer-copy th:nth-child(3),
        .items-table.customer-copy td:nth-child(3) { width: 6%; }
        .items-table.customer-copy th:nth-child(4),
        .items-table.customer-copy td:nth-child(4) { width: 8%; }
        .items-table.customer-copy th:nth-child(5),
        .items-table.customer-copy td:nth-child(5) { width: 8%; }
        .items-table.customer-copy th:nth-child(6),
        .items-table.customer-copy td:nth-child(6) { width: 8%; }
        .items-table.customer-copy th:nth-child(7),
        .items-table.customer-copy td:nth-child(7) { width: 12%; }
    .items-table.customer-copy th:nth-child(8),
    .items-table.customer-copy td:nth-child(8) { width: 10%; }
    .items-table.customer-copy th:nth-child(9),
    .items-table.customer-copy td:nth-child(9) { width: 11%; }

        /* Alignment for customer copy columns */
        .items-table.customer-copy th:nth-child(3),
        .items-table.customer-copy td:nth-child(3),
        .items-table.customer-copy th:nth-child(4),
        .items-table.customer-copy td:nth-child(4),
        .items-table.customer-copy th:nth-child(5),
        .items-table.customer-copy td:nth-child(5),
        .items-table.customer-copy th:nth-child(6),
        .items-table.customer-copy td:nth-child(6) {
            text-align: center;
        }
        .items-table.customer-copy th:nth-child(7),
        .items-table.customer-copy td:nth-child(7),
        .items-table.customer-copy th:nth-child(8),
        .items-table.customer-copy td:nth-child(8),
        .items-table.customer-copy th:nth-child(9),
        .items-table.customer-copy td:nth-child(9) {
            text-align: right;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals-section {
            display: table;
            width: 100%;
            margin-top: 5px;
        }

        .totals-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
            padding-right: 5px;
        }

        .totals-right {
            display: table-cell;
            width: 40%;
            vertical-align: top;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }

        .totals-table td {
            padding: 3px 4px;
            border: 1px solid #ddd;
        }

        .totals-table .total-label {
            /* font-weight: bold; */
            font-size: 9pt;
            background: #f8f9fa;
        }

        .totals-table .grand-total {
            /* background: #007bff; */
            color: #000000;
            /* font-weight: bold; */
            font-size: 10pt;
        }

        .payment-info {
            background: #f8f9fa;
            padding: 5px;
            border-radius: 3px;
            margin-top: 4px;
            font-size: 9pt;
        }

        .footer {
            margin-top: 5px;
            font-size: 8pt;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 4px;
            text-align: center;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 2px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-paid {
            background: #28a745;
            color: #fff;
        }

        .status-unpaid {
            background: #dc3545;
            color: #fff;
        }

        .status-partial {
            background: #ffc107;
            color: #212529;
        }

        .duplicate-header {
            text-align: center;
            font-weight: bold;
            color: #666;
            margin: 2px 0 4px;
            font-size: 10pt;
            padding: 3px;
            background: #f0f0f0;
            border-radius: 3px;
        }

        .page-break {
            page-break-after: always;
        }

        .preview-notice {
            background: #ff9800;
            color: #fff;
            padding: 4px;
            border-radius: 3px;
            margin-bottom: 4px;
            font-weight: bold;
            text-align: center;
            font-size: 9pt;
        }

        .separator-line {
            width: 100%;
            border-top: 1px dashed #000;
            margin: 2mm 0;
        }

        @media screen {
            body {
                background: #f5f5f5;
                padding: 5px;
            }

            .page-wrap {
                box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            }

            .copy {
                border: 1px solid #ccc;
            }
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                background: #fff;
            }

            .page-wrap {
                padding: 0;
                box-shadow: none;
                max-width: 190mm;
            }

            .copy {
                padding: 2mm; /* Slightly reduced from 3mm */
                margin-bottom: 1mm; /* Slightly reduced from 2mm */
            }

            .company-name {
                font-size: 14.5pt; /* Slightly increased from 11pt */
                margin-bottom: 2px; /* Slightly reduced from 3px */
            }

            .invoice-title {
                font-size: 16.5pt; /* Slightly increased from 11pt */
                margin-bottom: 2px; /* Slightly reduced from 3px */
            }

            .company-details,
            .invoice-meta,
            .items-table td,
            .items-table th {
                font-size: 8pt; /* Slightly increased from 7.5pt */
            }

            .footer {
                font-size: 8pt; /* Slightly increased from 5.5pt */
            }

            .preview-notice {
                display: none;
            }

            .invoice-header {
                margin-bottom: 2px; /* Slightly reduced from 5px */
                padding-bottom: 1px; /* Slightly reduced from 3px */
            }

            .company-logo {
                max-width: 65px; /* Slightly reduced from 70px */
                margin-bottom: 2px; /* Slightly reduced from 3px */
            }

            .company-details {
                line-height: 0.7; /* Kept as is */
            }

            .invoice-info {
                padding-left: 4px; /* Slightly reduced from 5px */
            }

            .invoice-meta {
                padding: 3px; /* Slightly reduced from 4px */
                font-size: 9.5pt; /* Slightly increased from 8pt */
                line-height: 0.7; /* Kept as is */
            }

            .vendor-section {
                margin: 4px 0; /* Slightly reduced from 5px */
                border-spacing: 4px; /* Slightly reduced from 5px */
            }

            .bill-to,
            .ship-to {
                padding: 4px; /* Slightly reduced from 5px */
                font-size: 9.5pt; /* Slightly increased from 8pt */
                line-height: 0.7; /* Kept as is */
            }

            .section-title {
                margin-bottom: 2px; /* Slightly reduced from 3px */
                padding-bottom: 1px; /* Slightly reduced from 2px */
                font-size: 10.5pt; /* Slightly increased from 7.5pt */
            }

            .vendor-name {
                margin-bottom: 1px; /* Slightly reduced from 2px */
                font-size: 10.5pt; /* Slightly increased from 7.5pt */
            }

            .items-table {
                margin-top: 4px; /* Slightly reduced from 5px */
            }

            .items-table th,
            .items-table td {
                padding: 2px; /* Slightly reduced from 3px */
            }

            .totals-section {
                margin-top: 4px; /* Slightly reduced from 5px */
            }

            .totals-left {
                padding-right: 4px; /* Slightly reduced from 5px */
            }

            .totals-table td {
                padding: 2px 3px; /* Slightly reduced from 3px 4px */
            }

            .payment-info {
                padding: 4px; /* Slightly reduced from 5px */
                margin-top: 3px; /* Slightly reduced from 4px */
            }

            .footer {
                margin-top: 4px; /* Slightly reduced from 5px */
                padding-top: 3px; /* Slightly reduced from 4px */
            }

            .duplicate-header {
                margin: 1px 0 3px; /* Slightly adjusted from 2px 0 4px */
                padding: 2px; /* Slightly reduced from 3px */
            }

            .separator-line {
                margin: 1mm 0; /* Adjusted for print view */
            }

            .price{
                font-size: 8.6pt;
               font-weight: bold;
            }
        }

        @media (max-width: 700px) {
            .company-name {
                font-size: 12pt;
            }

            .invoice-title {
                font-size: 14pt;
            }

            .items-table th,
            .items-table td {
                font-size: 8pt;
            }

            .vendor-section {
                display: block;
            }

            .bill-to,
            .ship-to {
                display: block;
                width: 100%;
                margin-bottom: 5px;
            }


        }
    </style>
</head>

<body>
    <div class="page-wrap">
        <!-- FIRST COPY: COMPANY COPY -->
        <section class="copy allow-break">
            @if (isset($preview) && $preview)
                <div class="preview-notice">
                    INVOICE PREVIEW - NOT FOR OFFICIAL USE
                </div>
            @endif

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
                        <strong>Date:</strong> {{ $order->created_at->format('d M Y') }}<br>
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
                    <div>Sales Representative : {{ $order?->placeBy?->name }}</div>
                </div>

                <div class="ship-to">
                    <div class="section-title">Account Summary:</div>
                    <div> <b>Total Due:</b> ৳ <b>{{ number_format($vendorTotalDue, 2) }}</b></div>
                    <div>Paid Amount: ৳ {{ number_format($vendorPaidAmount, 2) }}</div>
                    <div>Current Invoice: ৳ {{ number_format($order->total_amount, 2) }}</div>
                    <div>Current Invoice Payment: ৳ {{ number_format($currentInvoicePayment, 2) }}</div>
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
                        <th>Product Description</th>
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
                                <strong>{{ $item->product->name ?? 'N/A' }}</strong>
                                @if ($item->product && $item->product->description)
                                    <br><small>{{ \Illuminate\Support\Str::limit($item->product->description, 60) }}</small>
                                @endif
                            </td>
                                <td class="text-center"> <span class="price">{{ number_format($item->quantity) }}</span></td>
                                <td class="text-center"> <span class="price">{{ number_format($item->damage_quantity ?? 0) }}</span></td>
                                <td class="text-center"> <span class="price">{{ number_format($item->lost_quantity ?? 0) }}</span></td>
                                <td class="text-center"> <span class="price">{{ number_format($item->return_quantity ?? 0) }}</span></td>
                                <td class="text-right"> ৳ <span class="price">{{ number_format($item->sell_price, 2) }}</span></td>
                                <td class="text-right">৳ <span class="price">{{ number_format($item->discount_price, 2) }}</span></td>
                                <td class="text-right">৳ <span class="price">{{ number_format($item->sell_price * ($item->quantity - ($item->damage_quantity  + $item->lost_quantity  + $item->return_quantity )) - $item->discount_price, 2) }}</span></td>
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
                        <div><strong>Due Date:</strong> {{ $order->created_at->addDays(5)->format('d M Y') }}</div>
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
                                ৳ <span class="price">{{ number_format($orderItems->sum(function ($i) {return ($i->sell_price * $i->quantity)-(($i->damage_quantity + $i->lost_quantity + $i->return_quantity)*$i->sell_price);}),2) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="total-label">Total Discount:</td>
                            <td class="text-right">৳ <span class="price">{{ number_format($order->total_discount_amount, 2) }}</span></td>
                        </tr>
                        @if (($order->tax_amount ?? 0) > 0)
                            <tr>
                                <td class="total-label">Tax:</td>
                                <td class="text-right">৳ <span class="price">{{ number_format($order->tax_amount, 2) }}</span></td>
                            </tr>
                        @endif
                        <tr class="grand-total">
                            <td><strong>TOTAL AMOUNT:</strong></td>
                            <td class="text-right"> ৳ <strong>{{ number_format($order->total_amount, 2) }}</strong></td>
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
                        <strong>Date:</strong> {{ $order->created_at->format('d M Y') }}<br>
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
                    <div>Sales Representative : {{ $order?->placeBy?->name }}</div>
                </div>

                <div class="ship-to">
                    <div class="section-title">Account Summary:</div>
                    <div><b>Total Due:</b> ৳ <b>{{ number_format($vendorTotalDue, 2) }}</b></div>
                    <div>Paid Amount: ৳ {{ number_format($vendorPaidAmount, 2) }}</div>
                    <div>Current Invoice: ৳ {{ number_format($order->total_amount, 2) }}</div>
                    <div>Current Invoice Payment: ৳ {{ number_format($currentInvoicePayment, 2) }}</div>
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
                        <th>Product Description</th>
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
                                <strong>{{ $item->product->name ?? 'N/A' }}</strong>
                                @if ($item->product && $item->product->description)
                                    <br><small>{{ \Illuminate\Support\Str::limit($item->product->description, 60) }}</small>
                                @endif
                            </td>
                             <td class="text-center"> <span class="price">{{ number_format($item->quantity) }}</span></td>
                                <td class="text-center"> <span class="price">{{ number_format($item->damage_quantity ?? 0) }}</span></td>
                                <td class="text-center"> <span class="price">{{ number_format($item->lost_quantity ?? 0) }}</span></td>
                                <td class="text-center"> <span class="price">{{ number_format($item->return_quantity ?? 0) }}</span></td>
                                <td class="text-right"> ৳ <span class="price">{{ number_format($item->sell_price, 2) }}</span></td>
                                <td class="text-right">৳ <span class="price">{{ number_format($item->discount_price, 2) }}</span></td>
                                <td class="text-right">৳ <span class="price">{{ number_format($item->sell_price * ($item->quantity - ($item->damage_quantity  + $item->lost_quantity  + $item->return_quantity )) - $item->discount_price, 2) }}</span></td>
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
                        <div><strong>Due Date:</strong> {{ $order->created_at->addDays(30)->format('d M Y') }}</div>
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
                                ৳ <span class="price">{{ number_format($orderItems->sum(function ($i) {return $i->sell_price * $i->quantity;}),2) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="total-label">Total Discount:</td>
                            <td class="text-right">৳ <span class="price">{{ number_format($order->total_discount_amount, 2) }}</span></td>
                        </tr>
                        @if (($order->tax_amount ?? 0) > 0)
                            <tr>
                                <td class="total-label">Tax:</td>
                                <td class="text-right">৳{{ number_format($order->tax_amount, 2) }}</td>
                            </tr>
                        @endif
                        <tr class="grand-total">
                            <td><strong>TOTAL AMOUNT:</strong></td>
                            <td class="text-right">৳ <strong>{{ number_format($order->total_amount, 2) }}</strong></td>
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
