<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Order {{ $companyOrder->order_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #333;
        }
        .business-header {
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #333;
        }
        .business-info {
            display: table;
            width: 100%;
        }
        .logo-section {
            display: table-cell;
            width: 60px;
            vertical-align: middle;
            text-align: center;
        }
        .logo-section img {
            max-width: 50px;
            max-height: 50px;
        }
        .company-details {
            display: table-cell;
            vertical-align: middle;
            padding-left: 10px;
        }
        .company-name {
            font-size: 14px;
            font-weight: bold;
            margin: 0;
            color: #000;
        }
        .brand-name {
            font-size: 11px;
            color: #555;
            margin: 2px 0;
        }
        .contact-info {
            font-size: 9px;
            color: #666;
            margin-top: 3px;
            line-height: 1.4;
        }
        .document-title {
            text-align: center;
            margin: 8px 0;
            padding: 5px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
        }
        .document-title h1 {
            margin: 0;
            font-size: 14px;
            color: #333;
        }
        .document-title p {
            margin: 3px 0 0 0;
            font-size: 11px;
            color: #666;
        }
        .order-info {
            margin-bottom: 10px;
        }
        .order-info table {
            width: 100%;
        }
        .order-info td {
            padding: 3px;
            font-size: 9px;
        }
        .order-info .label {
            font-weight: bold;
            width: 100px;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.items th,
        table.items td {
            border: 1px solid #333;
            padding: 4px;
            text-align: left;
            font-size: 9px;
        }
        table.items th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        table.items thead {
            display: table-header-group;
        }
        table.items tfoot {
            display: table-footer-group;
        }
        table.items tfoot td {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 2px;
            font-weight: bold;
            font-size: 8px;
        }
        .status-paid {
            background-color: #28a745;
            color: white;
        }
        .status-partial {
            background-color: #ffc107;
            color: black;
        }
        .status-unpaid {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Business Header -->
    @if($business)
    <div class="business-header">
        <div class="business-info">
            @if($logoBase64)
            <div class="logo-section">
                <img src="{{ $logoBase64 }}" alt="Logo">
            </div>
            @endif
            <div class="company-details">
                @if($business->company_name)
                    <h2 class="company-name">{{ $business->company_name }}</h2>
                @endif
                @if($business->brand_name)
                    <div class="brand-name">{{ $business->brand_name }}</div>
                @endif
                <div class="contact-info">
                    @if($business->address)
                        <div><strong>Address:</strong> {{ $business->address }}</div>
                    @endif
                    @if($business->mobile_one || $business->mobile_two)
                        <div>
                            <strong>Phone:</strong>
                            {{ $business->mobile_one ?? '' }}
                            @if($business->mobile_one && $business->mobile_two) | @endif
                            {{ $business->mobile_two ?? '' }}
                        </div>
                    @endif
                    @if($business->email)
                        <div><strong>Email:</strong> {{ $business->email }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Document Title -->
    <div class="document-title">
        <h1>COMPANY ORDER</h1>
        <p>Order #{{ $companyOrder->order_number }}</p>
    </div>

    <div class="order-info">
        <table>
            <tr>
                <td class="label">Company:</td>
                <td>{{ $companyOrder->company->name ?? 'N/A' }}</td>
                <td class="label">Order Date:</td>
                <td>{{ $companyOrder->created_at->format('d M Y') }}</td>
            </tr>
            <tr>
                <td class="label">Order Status:</td>
                <td>{{ strtoupper($companyOrder->status) }}</td>
                <td class="label">Payment Status:</td>
                <td>
                    <span class="status-badge status-{{ $companyOrder->payment_status }}">
                        {{ strtoupper($companyOrder->payment_status) }}
                    </span>
                </td>
            </tr>
            <tr>
                <td class="label">Total Amount:</td>
                <td>{{ number_format($companyOrder->total_amount, 2) }}</td>
                <td class="label">Paid Amount:</td>
                <td>{{ number_format($companyOrder->paid_amount, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Due Amount:</td>
                <td>{{ number_format($companyOrder->total_amount - $companyOrder->paid_amount, 2) }}</td>
                <td class="label"></td>
                <td></td>
            </tr>
            @if($companyOrder->notes)
            <tr>
                <td class="label">Notes:</td>
                <td colspan="3">{{ $companyOrder->notes }}</td>
            </tr>
            @endif
        </table>
    </div>

    <h3>Order Items</h3>
    <table class="items">
        <thead>
            <tr>
                <th width="3%" class="text-center">#</th>
                <th >Product</th>
                <th >M. Unit</th>
                <th >P. Unit</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Dmg</th>
                <th class="text-right">Lost</th>
                <th class="text-right">Price</th>
                <th class="text-right">Dmg Price</th>
                <th class="text-right">Lost Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($companyOrder->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->product_name }}</td>
                <td>{{ $item->measurement_unit }}</td>
                <td>{{ $item->package_unit }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">{{ $item->damage_quantity }}</td>
                <td class="text-right">{{ $item->lost_quantity }}</td>
                <td class="text-right">
                  {{ number_format($item->price, 2) }}
                </td>
                <td class="text-right">{{ number_format($item->damage_price, 2) }}</td>
                <td class="text-right">{{ number_format($item->lost_price, 2) }}</td>
                <td class="text-right">{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="10" class="text-right">Grand Total:</td>
                <td class="text-right">{{ number_format($companyOrder->total_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    @php
        // Calculate damage and lost totals
        $totalDamageAmount = $companyOrder->items->sum('damage_price');
        $totalLostAmount = $companyOrder->items->sum('lost_price');
        $totalDeductions = $totalDamageAmount + $totalLostAmount;

        // Calculate final payable amount
        $finalPayableAmount = $companyOrder->total_amount - $totalDeductions;

        // Calculate due or overpaid
        $balanceAmount = $finalPayableAmount - $companyOrder->paid_amount;
        $isDue = $balanceAmount > 0;
        $isOverpaid = $balanceAmount < 0;
    @endphp

    <h3 style="margin-top: 15px;">Payment Summary</h3>
    <table class="order-info" style="margin-bottom: 0;">
        <tr>
            <td class="label">Order Total:</td>
            <td style="text-align: right; font-weight: bold;">{{ number_format($companyOrder->total_amount, 2) }}</td>
            <td class="label" style="width: 100px;"></td>
            <td style="width: 150px;"></td>
        </tr>
        @if($totalDamageAmount > 0)
        <tr>
            <td class="label" style="color: #dc3545;">Less: Damage Amount:</td>
            <td style="text-align: right; color: #dc3545;">- {{ number_format($totalDamageAmount, 2) }}</td>
            <td class="label"></td>
            <td></td>
        </tr>
        @endif
        @if($totalLostAmount > 0)
        <tr>
            <td class="label" style="color: #dc3545;">Less: Lost Amount:</td>
            <td style="text-align: right; color: #dc3545;">- {{ number_format($totalLostAmount, 2) }}</td>
            <td class="label"></td>
            <td></td>
        </tr>
        @endif
        @if($totalDeductions > 0)
        <tr>
            <td colspan="4" style="border-top: 1px solid #ddd; padding-top: 5px;"></td>
        </tr>
        @endif
        <tr>
            <td class="label" style="font-weight: bold; font-size: 10px;">Final Payable Amount:</td>
            <td style="text-align: right; font-weight: bold; font-size: 10px; background-color: #f0f0f0; padding: 5px;">{{ number_format($finalPayableAmount, 2) }}</td>
            <td class="label"></td>
            <td></td>
        </tr>
        <tr>
            <td class="label" style="color: #28a745;">Amount Paid:</td>
            <td style="text-align: right; color: #28a745; font-weight: bold;">{{ number_format($companyOrder->paid_amount, 2) }}</td>
            <td class="label"></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="4" style="border-top: 2px solid #333; padding-top: 5px;"></td>
        </tr>
        @if($isDue)
        <tr>
            <td class="label" style="font-weight: bold; color: #dc3545; font-size: 11px;">Due Amount:</td>
            <td style="text-align: right; font-weight: bold; color: #dc3545; font-size: 11px; background-color: #ffe6e6; padding: 5px;">{{ number_format(abs($balanceAmount), 2) }}</td>
            <td class="label"></td>
            <td></td>
        </tr>
        @elseif($isOverpaid)
        <tr>
            <td class="label" style="font-weight: bold; color: #ffc107; font-size: 11px;">Overpaid Amount:</td>
            <td style="text-align: right; font-weight: bold; color: #ffc107; font-size: 11px; background-color: #fff9e6; padding: 5px;">{{ number_format(abs($balanceAmount), 2) }}</td>
            <td class="label"></td>
            <td></td>
        </tr>
        @else
        <tr>
            <td class="label" style="font-weight: bold; color: #28a745; font-size: 11px;">Status:</td>
            <td style="text-align: right; font-weight: bold; color: #28a745; font-size: 11px; background-color: #e6ffe6; padding: 5px;">FULLY PAID</td>
            <td class="label"></td>
            <td></td>
        </tr>
        @endif
    </table>

    <div class="footer">
        <p>Generated on {{ now()->format('d M Y H:i:s') }}</p>
        <p>This is a computer-generated document. No signature is required.</p>
    </div>
</body>
</html>
