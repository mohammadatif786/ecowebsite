<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>LinkUp Spa Booking Confirmation</title>
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; display: block; }
        body { margin: 0; padding: 0; width: 100% !important; height: 100% !important; background-color: #f6f1e8; font-family: Arial, Helvetica, sans-serif; color: #2f2f2f; }
        a { color: #6a8973; text-decoration: none; }
        .wrapper { width: 100%; table-layout: fixed; background: linear-gradient(180deg, #f8f3ea 0%, #f3ede3 100%); padding: 24px 0; }
        .main { background-color: transparent; margin: 0 auto; width: 100%; max-width: 680px; border-spacing: 0; }
        .card { background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 8px 24px rgba(47, 47, 47, 0.07); }
        .px { padding-left: 24px; padding-right: 24px; }
        .section-pad { padding-top: 22px; padding-bottom: 22px; }
        .hero { background: linear-gradient(135deg, #5f7f69 0%, #86a08c 52%, #d7c09a 100%); }
        .badge { display: inline-block; background-color: rgba(255,255,255,0.18); color: #ffffff; font-size: 12px; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase; padding: 8px 14px; border-radius: 999px; border: 1px solid rgba(255,255,255,0.25); }
        .eyebrow { font-size: 12px; letter-spacing: 1.2px; text-transform: uppercase; color: #eef4ef; font-weight: 700; }
        .hero-title { font-size: 32px; line-height: 38px; font-weight: 700; color: #ffffff; margin: 10px 0 12px; }
        .hero-copy { font-size: 15px; line-height: 23px; color: #f6f8f6; margin: 0; }
        .section-title { font-size: 18px; line-height: 24px; font-weight: 700; color: #2f2f2f; margin: 0 0 18px; }
        .subtle { font-size: 13px; line-height: 20px; color: #6b6b6b; }
        .detail-label { font-size: 12px; line-height: 16px; color: #7a7a7a; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 700; padding-bottom: 6px; }
        .detail-value { font-size: 17px; line-height: 23px; color: #2f2f2f; font-weight: 700; }
        .detail-value-light { font-size: 15px; line-height: 21px; color: #2f2f2f; font-weight: 400; }
        .mini-card { background-color: #fcfaf6; border: 1px solid #eee5d6; border-radius: 14px; }
        .status-pill { display: inline-block; background-color: #edf7ef; color: #2a7a39; font-size: 13px; line-height: 13px; font-weight: 700; padding: 8px 12px; border-radius: 999px; }
        .amount { font-size: 24px; line-height: 28px; font-weight: 700; color: #2f2f2f; }
        .gold-line { width: 54px; height: 3px; background-color: #c8a96b; border-radius: 99px; }
        .button { display: inline-block; background: linear-gradient(135deg, #6a8973 0%, #5b7764 100%); color: #ffffff !important; font-size: 14px; line-height: 14px; font-weight: 700; padding: 12px 18px; border-radius: 999px; }
        .ticket-shell { background: linear-gradient(180deg, #fffdfa 0%, #f8f4ec 100%); border: 1px solid #eadfcd; border-radius: 18px; overflow: hidden; }
        .ticket-top { background: linear-gradient(135deg, rgba(95,127,105,0.08) 0%, rgba(214,193,154,0.22) 100%); }
        .ticket-chip { display: inline-block; font-size: 12px; line-height: 12px; font-weight: 700; color: #6a5a3a; text-transform: uppercase; letter-spacing: 0.8px; background-color: #f3ead9; padding: 8px 12px; border-radius: 999px; }
        .ticket-meta-label { font-size: 12px; line-height: 16px; color: #7c7c7c; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 700; padding-bottom: 4px; }
        .ticket-meta-value { font-size: 15px; line-height: 20px; color: #2f2f2f; font-weight: 700; }
        .perforation { border-top: 2px dashed #e5d7bf; height: 2px; line-height: 2px; font-size: 0; }
        .qr-wrap { background: linear-gradient(180deg, #fffdf9 0%, #f8f2e7 100%); border: 1px solid #e7dac2; border-radius: 18px; box-shadow: inset 0 0 0 1px rgba(200, 169, 107, 0.12); }
        .sponsor-inline { background: rgba(255,255,255,0.55); border: 1px solid rgba(200, 169, 107, 0.28); border-radius: 18px; }
        .sponsor-inline-label { font-size: 11px; line-height: 14px; color: #7b6a49; text-transform: uppercase; letter-spacing: 0.9px; font-weight: 700; padding-bottom: 10px; }
        .sponsor-grid-label { font-size: 12px; line-height: 16px; color: #7b6a49; text-transform: uppercase; letter-spacing: 0.9px; font-weight: 700; padding-bottom: 14px; }
        .sponsor-cell { background-color: #fcfaf6; border: 1px solid #eee5d6; border-radius: 14px; text-align: center; }
        .footer { font-size: 13px; line-height: 21px; color: #7a7a7a; text-align: center; }
        .stack-col { vertical-align: top; }
        @media screen and (max-width: 640px) {
            .px { padding-left: 16px !important; padding-right: 16px !important; }
            .hero-title { font-size: 25px !important; line-height: 31px !important; }
            .mobile-block, .mobile-block tbody, .mobile-block tr, .mobile-block td { display: block !important; width: 100% !important; }
            .mobile-spacer { display: block !important; width: 100% !important; height: 14px !important; }
        }
    </style>
</head>
<body>
    <center class="wrapper">
        <table class="main" role="presentation" cellpadding="0" cellspacing="0" border="0">
            <!-- Header/Hero -->
            <tr>
                <td class="px" style="padding-bottom: 16px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="card">
                        <tr>
                            <td class="hero px section-pad" style="padding-top: 26px; padding-bottom: 26px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td align="left" style="padding-bottom: 12px;">
                                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                                <tr>
                                                    <td style="padding-right: 10px;"><img src="https://linkupvibes.com/wp-content/uploads/2025/08/linkup-logo.png" width="34" height="34" alt="LinkUp logo" style="width:34px;height:34px;display:block;" /></td>
                                                    <td style="font-size: 26px; line-height: 28px; font-weight: 700; color: #ffffff;">LinkUp Spa</td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr><td style="padding-bottom: 12px;"><span class="badge">Confirmed</span></td></tr>
                                    <tr><td class="eyebrow">Wellness Booking Confirmation</td></tr>
                                    <tr><td class="hero-title">Your spa appointment is officially booked.</td></tr>
                                    <tr><td class="hero-copy">Thank you for booking with LinkUp. Your reservation for <strong>{{ $event->title }}</strong> has been confirmed. Please review your booking details below and present your QR code when you arrive for check-in.</td></tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <!-- Appointment Snapshot -->
            <tr>
                <td class="px" style="padding-bottom: 16px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="card">
                        <tr>
                            <td class="px section-pad" style="padding-bottom: 20px;">
                                <div class="gold-line"></div>
                                <h2 class="section-title" style="margin-top: 12px; margin-bottom: 14px;">Appointment Snapshot</h2>
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="mobile-block">
                                    <tr>
                                        <td width="50%" class="stack-col" style="padding-right: 10px;">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="mini-card">
                                                <tr>
                                                    <td style="padding: 14px;">
                                                        <div class="detail-label">Service</div>
                                                        <div class="detail-value">{{ $ticket_sale->first()->ticket_name ?? 'Wellness' }}</div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td class="mobile-spacer" style="width: 12px; font-size: 0; line-height: 0;">&nbsp;</td>
                                        <td width="50%" class="stack-col">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="mini-card">
                                                <tr>
                                                    <td style="padding: 14px;">
                                                        <div class="detail-label">Total Paid</div>
                                                        @php
                                                            $totalPaid = $ticket_sale->sum(function ($row) {
                                                                $stripePrice = $row->stripe_price;
                                                                if ($stripePrice !== null && $stripePrice !== '') {
                                                                    return floatval($stripePrice);
                                                                }
                                                                return floatval($row->total ?? 0);
                                                            });
                                                        @endphp
                                                        <div class="amount">${{ number_format($totalPaid, 2) }}</div>
                                                        @php
                                                            $feeBreakdown = $ticket_sale->first()->fee_breakdown ?? [];
                                                            $spaPlatformFeePctRate = floatval(data_get($feeBreakdown, 'spa_platform_fee_pct_rate', 0));
                                                            $spaPlatformFeeFixedRate = floatval(data_get($feeBreakdown, 'spa_platform_fee_fixed_rate', 0));
                                                            $spaPlatformFeeAmount = floatval(data_get($feeBreakdown, 'spa_platform_fee_amount', 0));
                                                            $mobileFeeAmount = floatval(data_get($feeBreakdown, 'mobile_fee_amount', 0));
                                                        @endphp

                                                        @if($spaPlatformFeeAmount > 0 || $mobileFeeAmount > 0)
                                                            <div class="subtle" style="margin-top: 10px;">
                                                                @if($spaPlatformFeeAmount > 0)
                                                                    <div style="margin-bottom: 4px;">
                                                                        <strong style="color:#2f2f2f;">Spa Platform Fee:</strong>
                                                                        @if($spaPlatformFeePctRate > 0)
                                                                            {{ number_format($spaPlatformFeePctRate, 2) }}%
                                                                        @endif
                                                                        @if($spaPlatformFeeFixedRate > 0)
                                                                            @if($spaPlatformFeePctRate > 0) + @endif
                                                                            ${{ number_format($spaPlatformFeeFixedRate, 2) }}
                                                                        @endif
                                                                        = ${{ number_format($spaPlatformFeeAmount, 2) }}
                                                                    </div>
                                                                @endif
                                                                @if($mobileFeeAmount > 0)
                                                                    <div>
                                                                        <strong style="color:#2f2f2f;">Mobile Fee:</strong>
                                                                        ${{ number_format($mobileFeeAmount, 2) }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="mobile-block" style="margin-top: 10px;">
                                    <tr>
                                        <td width="33.33%" class="stack-col" style="padding-right: 10px;">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="mini-card">
                                                <tr>
                                                    <td style="padding: 14px;">
                                                        <div class="detail-label">Date</div>
                                                        <div class="detail-value-light">{{ \Carbon\Carbon::parse($event->start_time)->format('M d, Y') }}</div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td class="mobile-spacer" style="width: 12px; font-size: 0; line-height: 0;">&nbsp;</td>
                                        <td width="33.33%" class="stack-col" style="padding-right: 10px;">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="mini-card">
                                                <tr>
                                                    <td style="padding: 14px;">
                                                        <div class="detail-label">Time</div>
                                                        <div class="detail-value-light">{{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}</div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td class="mobile-spacer" style="width: 12px; font-size: 0; line-height: 0;">&nbsp;</td>
                                        <td width="33.33%" class="stack-col">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="mini-card">
                                                <tr>
                                                    <td style="padding: 14px;">
                                                        <div class="detail-label">Status</div>
                                                        <span class="status-pill">Confirmed</span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <!-- Order Summary -->
            <tr>
                <td class="px" style="padding-bottom: 16px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="card">
                        <tr>
                            <td class="px section-pad">
                                <div class="gold-line"></div>
                                <h2 class="section-title" style="margin-top: 12px; margin-bottom: 14px;">Order Summary</h2>
                                @php
                                    $subtotal = $ticket_sale->sum(function ($row) {
                                        return floatval($row->total ?? 0);
                                    });

                                    $categoryLabels = [
                                        'wellness_service' => 'Wellness Service',
                                        'wellness_manual' => 'Wellness Addon',
                                        'wellness_mode' => 'Service Mode',
                                        'wellness_contact_phone' => 'wellness_contact_phone',
                                        'wellness_included_service' => 'Included Service',
                                        'wellness_slot' => 'Selected Slot',
                                    ];
                                @endphp

                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse;">
                                    @foreach($ticket_sale as $ticket)
                                        <tr>
                                            <td style="padding: 8px 0; font-size: 14px; line-height: 20px; color: #2f2f2f;">
                                                {{ $ticket->no_of_tickets }} × {{ $ticket->ticket_name }}
                                            </td>
                                            <td align="right" style="padding: 8px 0; font-size: 14px; line-height: 20px; color: #2f2f2f; font-weight: 700;">
                                                ${{ number_format(floatval($ticket->sub_total ?? 0), 2) }}
                                            </td>
                                        </tr>

                                        @if(is_array($ticket->wellness_addons) && count($ticket->wellness_addons) > 0)
                                            @foreach($ticket->wellness_addons as $addon)
                                                @php
                                                    $addonName = (string) data_get($addon, 'name', '');
                                                    $addonCategory = (string) data_get($addon, 'category', '');
                                                    $addonQty = intval(data_get($addon, 'quantity', 0));
                                                    $addonTotal = floatval(data_get($addon, 'total_price', 0));
                                                    $label = $categoryLabels[$addonCategory] ?? $addonCategory;
                                                @endphp
                                                @if($addonName !== '' && $addonQty > 0)
                                                    <tr>
                                                        <td style="padding: 4px 0; font-size: 13px; line-height: 18px; color: #6b6b6b;">
                                                            {{ $addonName }} ({{ $label }}) x{{ $addonQty }}
                                                        </td>
                                                        <td align="right" style="padding: 4px 0; font-size: 13px; line-height: 18px; color: #6b6b6b;">
                                                            ${{ number_format($addonTotal, 2) }}
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @endif
                                    @endforeach

                                    <tr>
                                        <td colspan="2" style="padding-top: 10px; border-top: 1px solid #eee5d6;"></td>
                                    </tr>

                                    <tr>
                                        <td style="padding: 8px 0; font-size: 14px; line-height: 20px; color: #2f2f2f; font-weight: 700;">Subtotal:</td>
                                        <td align="right" style="padding: 8px 0; font-size: 14px; line-height: 20px; color: #2f2f2f; font-weight: 700;">${{ number_format($subtotal, 2) }}</td>
                                    </tr>

                                    @if($spaPlatformFeeAmount > 0)
                                        <tr>
                                            <td style="padding: 6px 0; font-size: 14px; line-height: 20px; color: #2f2f2f;">Wellness &amp; Spa Fees:</td>
                                            <td align="right" style="padding: 6px 0; font-size: 14px; line-height: 20px; color: #2f2f2f;">${{ number_format($spaPlatformFeeAmount, 2) }}</td>
                                        </tr>
                                    @endif

                                    @if($mobileFeeAmount > 0)
                                        <tr>
                                            <td style="padding: 6px 0; font-size: 14px; line-height: 20px; color: #2f2f2f;">Mobile Fee:</td>
                                            <td align="right" style="padding: 6px 0; font-size: 14px; line-height: 20px; color: #2f2f2f;">${{ number_format($mobileFeeAmount, 2) }}</td>
                                        </tr>
                                    @endif

                                    <tr>
                                        <td colspan="2" style="padding-top: 10px; border-top: 1px solid #eee5d6;"></td>
                                    </tr>

                                    <tr>
                                        <td style="padding: 8px 0; font-size: 15px; line-height: 22px; color: #2f2f2f; font-weight: 700;">Total Paid:</td>
                                        <td align="right" style="padding: 8px 0; font-size: 15px; line-height: 22px; color: #2f2f2f; font-weight: 700;">${{ number_format($totalPaid, 2) }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <!-- Digital Ticket(s) -->
            <tr>
                <td class="px" style="padding-bottom: 24px;">
                    <h2 class="section-title" style="margin-bottom: 14px;">Your Digital Ticket</h2>
                    @foreach($ticket_sale as $ticket)
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="ticket-shell" style="margin-bottom: 24px;">
                        <tr>
                            <td class="ticket-top" style="padding: 24px 24px 16px 24px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                    <tr><td align="left" style="padding-bottom: 12px;"><span class="ticket-chip">Wellness Pass</span></td></tr>
                                    <tr><td class="ticket-name" style="font-size: 28px; line-height: 34px; color: #2f2f2f; font-weight: 700; padding-bottom: 6px;">{{ $ticket->ticket_name }}</td></tr>
                                    <tr><td class="subtle" style="font-size: 15px; line-height: 22px;">Hosted by <strong style="color:#2f2f2f;">{{ $event->venue }}</strong></td></tr>
                                </table>
                            </td>
                        </tr>
                        <tr><td class="perforation">&nbsp;</td></tr>
                        
                        @if($sponsors && $sponsors->isNotEmpty())
                        <tr>
                            <td style="padding: 16px 24px 0 24px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="sponsor-inline">
                                    <tr>
                                        <td style="padding: 12px 16px;" align="center">
                                            <div class="sponsor-inline-label">Featured Sponsor</div>
                                            <img src="{{ $sponsors->first()->image_url }}" alt="Featured sponsor" style="max-width: 110px; height: auto; margin: 0 auto;" />
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        @endif

                        <tr>
                            <td style="padding: 20px 24px 24px 24px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="mobile-block">
                                    <tr>
                                        <td width="50%" class="stack-col" style="padding-right: 12px; padding-bottom: 18px;">
                                            <div class="ticket-meta-label">Booking ID</div>
                                            <div class="ticket-meta-value">#{{ $ticket->id }}</div>
                                        </td>
                                        <td width="50%" class="stack-col" style="padding-bottom: 18px;">
                                            <div class="ticket-meta-label">Ticket Type</div>
                                            <div class="ticket-meta-value">{{ ucfirst($ticket->ticket_type) }}</div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="50%" class="stack-col" style="padding-right: 12px; padding-bottom: 18px;">
                                            <div class="ticket-meta-label">Entries</div>
                                            <div class="ticket-meta-value">{{ $ticket->no_of_tickets }}</div>
                                        </td>
                                        <td width="50%" class="stack-col" style="padding-bottom: 18px;">
                                            <div class="ticket-meta-label">Paid Via</div>
                                            <div class="ticket-meta-value">{{ ucfirst($ticket->payment_method) }}</div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        
                        <tr>
                            <td style="padding: 0 24px 24px 24px;" align="center">
                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" class="qr-wrap">
                                    <tr>
                                        <td style="padding: 16px;" align="center">
                                            <img src="{{ $ticket->web_qrcode ? $app_url . '/storage/' . $ticket->web_qrcode : $app_url . '/assets/images/bgimageBuyticket.png' }}" width="200" height="200" alt="QR Code" style="width: 200px; max-width: 100%; height: auto;" />
                                        </td>
                                    </tr>
                                </table>
                                <p style="margin: 14px 0 0; font-size: 15px; font-weight: 700;">Ticket ID: #{{ $ticket->id }}</p>
                            </td>
                        </tr>
                    </table>
                    @endforeach
                </td>
            </tr>

            <!-- Location & Footer -->
            <tr>
                <td class="px" style="padding-bottom: 24px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="card">
                        <tr>
                            <td class="px section-pad">
                                <h2 class="section-title">Location & Contact</h2>
                                <p class="subtle" style="margin-bottom: 10px;"><strong style="color:#2f2f2f;">Where:</strong> {{ $event->venue }}, {{ $event->city }}, {{ $event->state }}</p>
                                <p class="subtle" style="margin-bottom: 10px;"><strong style="color:#2f2f2f;">Email:</strong> <a href="mailto:{{ $event->email }}">{{ $event->email }}</a></p>
                                <p class="subtle" style="margin-bottom: 10px;"><strong style="color:#2f2f2f;">Phone:</strong> <a href="tel:{{ $event->phone }}">{{ $event->phone }}</a></p>
                                @php
                                    $disclaimerText = trim((string)($event->disclaimer ?? ''));
                                    if ($disclaimerText !== '') {
                                        $disclaimerText = html_entity_decode(strip_tags($disclaimerText));
                                        $disclaimerText = preg_replace('/\s+/', ' ', $disclaimerText);
                                        $disclaimerText = trim((string)$disclaimerText);
                                    }
                                @endphp
                                <p class="subtle"><strong style="color:#2f2f2f;">Note:</strong> {{ $disclaimerText !== '' ? $disclaimerText : 'Please arrive 15 minutes early.' }}</p>
                                <div style="padding-top: 18px;">
                                    <a href="{{ $app_url }}/user/orders" class="button">Manage Your Booking</a>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <!-- Footer -->
            <tr>
                <td class="footer" style="padding-bottom: 24px;">
                    Thanks for booking with LinkUp ✨<br />
                    © {{ date('Y') }} Link Up Events. All rights reserved.
                </td>
            </tr>
        </table>
    </center>
    @include('emails.partials.sponsor')
</body>
</html>