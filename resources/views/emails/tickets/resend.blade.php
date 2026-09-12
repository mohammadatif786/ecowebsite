<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Resend Confirmation</title>
    <style>
        body { margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f7f7f7; color:#333; }
    </style>
</head>

<body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f7f7f7; color:#333;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f7f7f7; padding:20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" border="0"
                       style="background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.1);">

                    <!-- Header -->
                    <tr>
                        <td align="center" style="background:#4CAF50; padding:20px;">
                            <h1 style="color:#fff; margin:0;">🎟 Ticket Resent</h1>
                        </td>
                    </tr>

                    <!-- Event Details -->
                    <tr>
                        <td style="padding:20px;">
                            <h2 style="margin:0; color:#222;">{{ $event->title }}</h2>
                            <p style="margin:5px 0; font-size:14px; color:#555; line-height:1.5;">
                                <strong>📍 Venue:</strong> {{ $event->venue }} <br>
                                <strong>🌍 Location:</strong> {{ $event->city }}, {{ $event->state }}, {{ $event->country }} <br>
                                <strong>📅 Date:</strong>
                                {{ \Carbon\Carbon::parse($event->start_time)->format('d M Y, h:i A') }} –
                                {{ \Carbon\Carbon::parse($event->end_time)->format('d M Y, h:i A') }}
                            </p>
                        </td>
                    </tr>

                    <!-- Single Ticket Block -->
                    <tr>
                        <td style="padding:20px;">
                            <h3 style="margin:0; color:#222;">Your Ticket</h3>
                            @php
                                $bd = is_array($ticket->fee_breakdown ?? null)
                                        ? $ticket->fee_breakdown
                                        : (is_object($ticket->fee_breakdown ?? null) ? (array) $ticket->fee_breakdown : []);
                                $serviceFeeSnap = ($bd['service_fee_pct_amount'] ?? 0) + ($bd['service_fee_fixed'] ?? 0);
                                $processingFeeSnap = ($bd['processing_fee_pct_amount'] ?? 0) + ($bd['processing_fee_fixed'] ?? 0);
                                $feeDisplay = ($ticket->fee ?? 0) > 0 ? ($ticket->fee ?? 0) : $serviceFeeSnap;
                                $taxDisplay = ($ticket->tax ?? 0) > 0 ? ($ticket->tax ?? 0) : $processingFeeSnap;
                                $discountDisplay = $ticket->coupan_amount ?? $ticket->discount ?? 0;
                                $subtotalDisplay = $ticket->sub_total ?? 0;
                                $stripePaid = $ticket->stripe_price ?? null;
                                $totalPaidDisplay = $stripePaid ?? ($ticket->total ?? ($subtotalDisplay + $feeDisplay + $taxDisplay - $discountDisplay));
                            @endphp
                            <table width="100%" cellpadding="10" cellspacing="0"
                                   style="border:1px solid #ddd; margin-top:15px; border-radius:8px; font-size:14px;">
                                <tr>
                                    <td><strong>Ticket ID:</strong></td>
                                    <td>#{{ $ticket->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Ticket Name:</strong></td>
                                    <td>{{ $ticket->ticket_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Ticket Type:</strong></td>
                                    <td>{{ ucfirst($ticket->ticket_type) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>No. of Tickets:</strong></td>
                                    <td>{{ $ticket->no_of_tickets }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td style="color:green; font-weight:bold;">{{ ucfirst($ticket->ticket_status) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Payment Method:</strong></td>
                                    <td>{{ ucfirst($ticket->payment_method) }} {{ $ticket->pay_type ? '(' . $ticket->pay_type . ')' : '' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Fee:</strong></td>
                                    <td>${{ number_format($feeDisplay, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Discount:</strong></td>
                                    <td>${{ number_format($discountDisplay, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tax:</strong></td>
                                    <td>${{ number_format($taxDisplay, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Subtotal:</strong></td>
                                    <td>${{ number_format($subtotalDisplay, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Paid:</strong></td>
                                    <td style="font-weight:bold;">${{ number_format($totalPaidDisplay, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2" align="center" style="padding-top:15px;">
                                        <p style="margin-bottom:10px;">Present this QR code at the entrance:</p>
                                        <img src="{{ $ticket->web_qrcode
                                                ? $app_url . '/storage/' . $ticket->web_qrcode
                                                : $app_url . '/assets/images/bgimageBuyticket.png' }}"
                                             alt="QR Code"
                                             style="width:150px; height:150px; object-fit:contain; display:block; margin:0 auto;" />
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Sponsored Section -->
                    <tr>
                        <td style="padding: 10px 25px;">
                            @include('emails.partials.sponsor')
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="background:#f1f1f1; padding:15px; font-size:12px; color:#555;">
                            <p style="margin:0;">This is a resend of your ticket email.</p>
                            <p style="margin:0;">&copy; {{ date('Y') }} Link Up Events. All rights reserved.</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>
