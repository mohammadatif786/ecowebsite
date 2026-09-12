<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Money Request Rejected - Link Up</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8fafc;
        }
        .container {
            background: white;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .header {
            text-align: center;
            margin-bottom: 32px;
        }
        .logo {
            background: linear-gradient(135deg, #0ea5e9, #38bdf8);
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 18px;
            display: inline-block;
        }
        .content {
            margin-bottom: 24px;
        }
        .amount {
            font-size: 28px;
            font-weight: 700;
            color: #ef4444;
            margin: 20px 0;
        }
        .info-box {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 16px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        .footer {
            text-align: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
            font-size: 14px;
            color: #64748b;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #0ea5e9, #38bdf8);
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            margin-top: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Link Up</div>
            <h1 style="margin: 16px 0 8px 0; color: #1e293b;">Money Request Rejected</h1>
        </div>

        <div class="content">
            <p>Hi {{ $requester->name }},</p>
            
            <p>Your money request has been rejected.</p>

            <div class="amount">{{ $formattedAmount }}</div>

            <div class="info-box">
                <strong>Request Details:</strong><br>
                <strong>Rejected by:</strong> {{ $recipient->name }} ({{ $recipient->linkup_id }})<br>
                <strong>Date:</strong> {{ now()->format('F j, Y \a\t g:i A') }}
            </div>

            <p>Your money request to {{ $recipient->name }} has been rejected. The funds have not been transferred.</p>

            <p>You can send a new request or contact the recipient directly if needed.</p>

            <a href="{{ route('frontend.user.wallet') }}" class="button">View Wallet</a>
        </div>

        @include('emails.partials.sponsor')

        <div class="footer">
            <p>This is an automated message from Link Up. Please do not reply to this email.</p>
            <p>If you have any questions, please contact our support team.</p>
        </div>
    </div>
</body>
</html>
