<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $invoice['order_number'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #333;
            margin: 0;
            font-size: 28px;
        }
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 20px;
        }
        .invoice-details .section {
            flex: 1;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        .section h3 {
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f5f5f5;
            font-weight: bold;
        }
        .total {
            font-size: 20px;
            font-weight: bold;
            text-align: right;
            margin-top: 20px;
            color: #333;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .summary-row.final {
            border-top: 2px solid #333;
            padding-top: 15px;
            margin-top: 15px;
        }
        @media print {
            body { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>INVOICE</h1>
        <p style="font-size: 16px; color: #666;">{{ $invoice['order_number'] }}</p>
        <p style="font-size: 14px; color: #666;">Date: {{ \Carbon\Carbon::parse($invoice['order_date'])->format('F j, Y') }}</p>
    </div>

    <div class="invoice-details">
        <div class="section">
            <h3>Seller</h3>
            <p style="font-weight: bold; margin: 5px 0;">{{ $invoice['seller_name'] }}</p>
            <p style="margin: 5px 0; color: #666;">{{ $invoice['seller_email'] }}</p>
        </div>
        <div class="section">
            <h3>Buyer</h3>
            <p style="font-weight: bold; margin: 5px 0;">{{ $invoice['buyer_name'] }}</p>
            <p style="margin: 5px 0; color: #666;">{{ $invoice['buyer_email'] }}</p>
            @if($invoice['buyer_phone'])
                <p style="margin: 5px 0; color: #666;">{{ $invoice['buyer_phone'] }}</p>
            @endif
        </div>
    </div>

    <div class="section">
        <h3>Order Details</h3>
        <p><strong>Status:</strong> {{ ucfirst($invoice['order_status']) }}</p>
        <p><strong>Shipping Method:</strong> {{ $invoice['shipping_method'] ?? 'N/A' }}</p>
        <p><strong>Delivery Address:</strong> {{ $invoice['delivery_address'] ?? 'N/A' }}</p>
    </div>

    <div class="section">
        <h3>Items</h3>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th style="text-align: right;">Quantity</th>
                    <th style="text-align: right;">Unit Price</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice['items'] as $item)
                    <tr>
                        <td>{{ $item['product_name'] }}</td>
                        <td style="text-align: right;">{{ $item['quantity'] }}</td>
                        <td style="text-align: right;">${{ number_format($item['unit_price'], 2) }}</td>
                        <td style="text-align: right;">${{ number_format($item['sub_total'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Summary</h3>
        <div class="summary-row">
            <span>Subtotal:</span>
            <span>${{ number_format($invoice['subtotal_amount'], 2) }}</span>
        </div>
        <div class="summary-row">
            <span>Tax:</span>
            <span>${{ number_format($invoice['tax_amount'], 2) }}</span>
        </div>
        <div class="summary-row">
            <span>Shipping:</span>
            <span>${{ number_format($invoice['shipping_amount'], 2) }}</span>
        </div>
        <div class="summary-row">
            <span>Discount:</span>
            <span>${{ number_format($invoice['discount_amount'], 2) }}</span>
        </div>
        <div class="summary-row">
            <span>{{ $invoice['fee_label'] }}:</span>
            <span>${{ number_format($invoice['fee_amount'], 2) }}</span>
        </div>
        <div class="summary-row final">
            <span style="font-size: 18px; font-weight: bold;">Total:</span>
            <span style="font-size: 18px; font-weight: bold;">${{ number_format($invoice['net_total'], 2) }}</span>
        </div>
    </div>

    <div style="margin-top: 40px; text-align: center; color: #999; font-size: 12px;">
        <p>This invoice was generated automatically by LinkUp Marketplace.</p>
        <p>For any questions, please contact support.</p>
    </div>
</body>
</html>
