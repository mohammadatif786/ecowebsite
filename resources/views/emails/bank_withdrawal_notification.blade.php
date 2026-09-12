<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
</head>
<body>

    <h1>Bank Withdrawal Notification</h1>
    <p>Dear {{ $user_name }},</p>
    <p>{{ $note }}</p>
    <ul>
        <li><strong>Total Amount:</strong> ${{ number_format($total_amount, 2) }}</li>
        <li><strong>Fee Amount:</strong> ${{ number_format($fee_amount, 2) }}</li>
        <li><strong>Payout Amount:</strong> ${{ number_format($payout_amount, 2) }}</li>
        <li><strong>Bank Name:</strong> {{ $bank_name }}</li>
        <li><strong>Account Number:</strong> {{ $account_number }}</li>
    </ul>
    <p>If you have any questions or need further assistance, please do not hesitate to contact our support team.</p>
    <p>Thank you for using our services!</p>
    @include('emails.partials.sponsor')
</body>
</html>
