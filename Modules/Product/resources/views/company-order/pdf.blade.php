<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Order {{ $companyOrder->order_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #000;
        }
        .order-info {
            margin-bottom: 20px;
        }
        .order-info table {
            width: 100%;
        }
        .order-info td {
            padding: 5px;
        }
        .order-info .label {
            font-weight: bold;
            width: 150px;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table.items th,
        table.items td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        table.items th {
            background-color: #f0f0f0;
            font-weight: bold;
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
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
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
    <div class="header">
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

    <div class="footer">
        <p>Generated on {{ now()->format('d M Y H:i:s') }}</p>
        <p>This is a computer-generated document. No signature is required.</p>
    </div>
</body>
</html>
