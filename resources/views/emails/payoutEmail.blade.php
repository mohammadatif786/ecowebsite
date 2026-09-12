<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LinkUp Payout Confirmation</title>
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #0b2239; background-color: #f7faff; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; box-shadow: 0 6px 16px rgba(12,54,124,.06); overflow: hidden; }
        .header { background: linear-gradient(135deg, #2f7de1, #2563c9); color: white; padding: 32px 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .header p { margin: 8px 0 0; opacity: 0.9; }
        .content { padding: 32px 24px; }
        .greeting { font-size: 18px; font-weight: 600; margin-bottom: 24px; }
        .details-table { width: 100%; border-collapse: collapse; margin: 24px 0; background: #f8fbff; border-radius: 8px; overflow: hidden; }
        .details-table th, .details-table td { padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f8; }
        .details-table th { background: #f8fbff; font-weight: 600; color: #425a7b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
        .details-table td { font-weight: 500; }
        .proof-section { background: #f0f6ff; border: 1px solid #c7dcff; border-radius: 8px; padding: 16px; margin: 24px 0; }
        .proof-section h3 { margin: 0 0 12px; color: #29569b; }
        .proof-section ul { margin: 0; padding-left: 20px; }
        .proof-section li { margin-bottom: 4px; }
        .next-steps { background: #e7f7ef; border: 1px solid #cfeede; border-radius: 8px; padding: 16px; margin: 24px 0; }
        .next-steps h3 { margin: 0 0 12px; color: #085e43; }
        .next-steps ul { margin: 0; padding-left: 20px; }
        .next-steps li { margin-bottom: 4px; }
        .cta-button { display: inline-block; background: #2f7de1; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: 600; margin: 16px 0; }
        .footer { background: #f8fbff; padding: 24px; text-align: center; font-size: 14px; color: #5b6b81; border-top: 1px solid #eef2f8; }
        .disclaimer { font-size: 12px; opacity: 0.8; margin-top: 16px; }
        .verification { background: #fff6e8; border: 1px solid #f59e0b; border-radius: 8px; padding: 12px; margin: 16px 0; text-align: center; }
        .verification p { margin: 0; font-size: 13px; color: #92400e; }
        .tax-notice { background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 12px; margin: 16px 0; font-size: 13px; color: #92400e; }
        .hash { font-family: monospace; background: #edf2fc; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        @media (max-width: 600px) { .container { margin: 0; border-radius: 0; } .content { padding: 24px 16px; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>LinkUp Payout Confirmation</h1>
            <p>Your funds have been transferred successfully</p>
        </div>
        <div class="content">
            <p class="greeting">Dear {{ $organizerContact->organizer->user->name ?? 'Valued Organizer' }},</p>
            
            <p>We hope this email finds you well. We're pleased to confirm that your requested payout from LinkUp has been successfully processed and sent via wire transfer.</p>
            
            <h2 style="color: #2f7de1; margin: 32px 0 16px;">Payout Details</h2>
            <table class="details-table">
                <tr><th>Field</th><th>Details</th></tr>
                <tr><td><strong>Reference Number</strong></td><td>{{ $payout->reference }}</td></tr>
                <tr><td><strong>Event Name</strong></td><td>{{ $payout->event->title ?? 'Unknown Event' }}</td></tr>
                <tr><td><strong>Payout Date</strong></td><td>{{ \Carbon\Carbon::parse($payout->updated_at)->format('F j, Y') }}</td></tr>
                <tr><td><strong>Amount Requested</strong></td><td>${{ number_format($payout->amount, 2) }} USD</td></tr>
                <tr><td><strong>Processing Fee</strong></td><td>${{ number_format($payout->fee_amount ?? (($payout->amount * 0.03) + 0.30), 2) }}</td></tr>
                <tr><td><strong>Net Amount Transferred</strong></td><td><span style="color: #10b981; font-weight: 600;">${{ number_format($payout->net_amount ?? ($payout->amount - (($payout->amount * 0.03) + 0.30)), 2) }} USD</span></td></tr>
                <tr><td><strong>Transfer Method</strong></td><td>{{ ucfirst($payout->method) }}</td></tr>
                <tr><td><strong>Destination Account</strong></td><td>{{ ucfirst($payout->method) }} Account</td></tr>
                <tr><td><strong>Expected Arrival</strong></td><td>1-3 Business Days ({{ \Carbon\Carbon::parse($payout->updated_at)->addDays(3)->format('F j, Y') }})</td></tr>
                <!-- <tr><td><strong>Transaction ID</strong></td><td>{{ $payout->firebase_id ?? 'PENDING' }}</td></tr> -->
            </table>
            
            <p>This payout reflects the available balance from ticket sales and other revenues for your event after deducting platform fees and any applicable taxes.</p>
            
            <div class="proof-section">
                <h3>Proof of Transfer</h3>
                <p>Attached to this email is a PDF copy of the official wire transfer receipt from our payment processor (e.g., Stripe or ACH Network). <strong>Digital Signature Verified</strong> – This document includes:</p>
                <ul>
                    <li>Timestamped confirmation of the transfer initiation.</li>
                    <li>Full transaction details matching the above.</li>
                    <li>Bank routing and account verification stamps.</li>
                </ul>
                <p>For added security, this email includes a transaction hash for verification: <code class="hash">09CA6E355E1B5263</code>. Verify it on our secure dashboard.</p>
            </div>
            
            <div class="verification">
                <p><strong>Secure Verification</strong>: Click below to confirm receipt with 2FA (one-time link expires in 24 hours).</p>
                <a href="{{ route('organizer.payout.verify.transfer', $payout->reference) }}" class="cta-button">Verify & Acknowledge</a>
            </div>
            
            <div class="next-steps">
                <h3>Next Steps</h3>
                <ul>
                    <li><strong>Monitor Your Account</strong>: Funds should appear within the estimated timeframe. If not received by October 20, 2025, contact us.</li>
                    <li><strong>Questions?</strong> Reply to this email or reach support@linkup.com / +1 (555) 123-4567.</li>
                    <li><strong>View in Dashboard</strong>: <a href="{{ route('organizer.payout.request') }}" class="cta-button">Access Payout Report</a></li>
                </ul>
            </div>
            
            <div class="tax-notice">
                <strong>Tax Notice</strong>: This payout may be reportable on IRS Form 1099-K if annual totals exceed $600 USD. For details, see our <a href="https://linkup.com/tax-resources" style="color: #92400e;">Tax Resources</a>. By using LinkUp, you agree to our <a href="https://linkup.com/tos" style="color: #92400e;">Terms of Service</a> and <a href="https://linkup.com/privacy" style="color: #92400e;">Privacy Policy</a>.
            </div>
            
            <p>Thank you for partnering with LinkUp to create unforgettable experiences. We look forward to your next event!</p>
            
            <p style="text-align: center; margin: 24px 0;">
                <a href="https://linkup.com/feedback?ref=stu901vwx234" class="cta-button" style="background: #10b981;">How was your experience? Rate Us</a>
            </p>
            @include('emails.partials.sponsor')
        </div>
        <div class="footer">
            <p>The LinkUp Team<br>LinkUp Events Inc.<br>123 Event Street, Suite 100<br>New York, NY 10001</p>
            <p>Email: hello@linkup.com | Website: <a href="https://www.linkupvibes.com" style="color: #2f7de1;">www.linkupvibes.com</a></p>
            <p class="disclaimer">This is an automated confirmation. For security, never share credentials via email. If you didn't request this, contact support immediately.</p>
        </div>
    </div>
</body>
</html>