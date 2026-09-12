<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payout Receipt</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            line-height: 1.6;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #0066FF;
            margin-bottom: 10px;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }
        .details-box {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        .label {
            display: table-cell;
            font-weight: bold;
            width: 150px;
            color: #555;
        }
        .value {
            display: table-cell;
            color: #333;
        }
        .amount-box {
            text-align: right;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }
        .net-amount {
            font-size: 24px;
            font-weight: bold;
            color: #0066FF;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #d1fae5;
            color: #065f46;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">LinkUp</div>
            <div class="title">Payout Receipt</div>
        </div>

        <div class="details-box">
            <div class="row">
                <div class="label">Reference ID:</div>
                <div class="value">{{ $payout->reference }}</div>
            </div>
            <div class="row">
                <div class="label">Date:</div>
                <div class="value">{{ $payout->created_at->format('F d, Y H:i A') }}</div>
            </div>
            <div class="row">
                <div class="label">Status:</div>
                <div class="value">
                    <span class="status-badge">{{ $payout->status }}</span>
                </div>
            </div>
        </div>

        <div class="details-box">
            <div class="row">
                <div class="label">Event:</div>
                <div class="value">{{ $payout->event->title ?? 'N/A' }}</div>
            </div>
            <div class="row">
                <div class="label">Organizer:</div>
                <div class="value">{{ $payout->organizer->organizer_name ?? 'N/A' }}</div>
            </div>
        </div>

        <div class="details-box">
            <div class="row">
                <div class="label">Gross Amount:</div>
                <div class="value">${{ number_format($payout->amount, 2) }}</div>
            </div>
            <div class="row">
                <div class="label">Fees:</div>
                <div class="value">-${{ number_format($payout->fee_amount, 2) }}</div>
            </div>
             @if($payout->add_on > 0)
            <div class="row">
                <div class="label">Add-ons:</div>
                <div class="value">+${{ number_format($payout->add_on, 2) }}</div>
            </div>
            @endif
            
            <div class="amount-box">
                <div class="label" style="display: inline-block;">Net Payout:</div>
                <div class="net-amount" style="display: inline-block;">${{ number_format($payout->net_amount, 2) }}</div>
            </div>
        </div>

        <div class="footer">
            <p>This is an electronically generated receipt.</p>
            <p>&copy; {{ date('Y') }} LinkUp. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
