<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>LinkUp Ticket Confirmation</title>
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; display: block; }
        body { margin: 0; padding: 0; width: 100% !important; height: 100% !important; background-color: #f7f3ee; font-family: Arial, Helvetica, sans-serif; color: #1f1f1f; }
        a { text-decoration: none; }
        .wrapper { width: 100%; table-layout: fixed; background: linear-gradient(180deg, #f8f4ee 0%, #f2ece4 100%); padding: 22px 0; }
        .main { margin: 0 auto; width: 100%; max-width: 680px; border-spacing: 0; }
        .card { background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 28px rgba(31, 31, 31, 0.08); }
        .px { padding-left: 24px; padding-right: 24px; }
        .section-pad { padding-top: 22px; padding-bottom: 22px; }
        .hero { background: linear-gradient(135deg, #232b47 0%, #3b4a78 45%, #6a8973 100%); }
        .badge { display: inline-block; background-color: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.18); color: #ffffff; font-size: 12px; line-height: 12px; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase; padding: 8px 12px; border-radius: 999px; }
        .eyebrow { font-size: 12px; line-height: 16px; letter-spacing: 1.2px; text-transform: uppercase; font-weight: 700; color: #dfe8ff; }
        .hero-title { font-size: 32px; line-height: 38px; font-weight: 700; color: #ffffff; margin: 10px 0 10px; }
        .hero-copy { font-size: 15px; line-height: 23px; color: #f4f6ff; margin: 0; }
        .section-title { font-size: 18px; line-height: 24px; font-weight: 700; color: #1f1f1f; margin: 0 0 14px; }
        .subtle { font-size: 13px; line-height: 20px; color: #67615b; }
        .accent-line { width: 56px; height: 4px; background: linear-gradient(90deg, #6a8973 0%, #c8a96b 100%); border-radius: 999px; }
        .mini-card { background-color: #fffaf5; border: 1px solid #f0dfd0; border-radius: 14px; }
        .detail-label, .ticket-meta-label { font-size: 12px; line-height: 16px; color: #8a7868; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 700; }
        .detail-value { font-size: 17px; line-height: 23px; color: #1f1f1f; font-weight: 700; }
        .detail-value-light, .ticket-meta-value { font-size: 15px; line-height: 21px; color: #1f1f1f; }
        .amount { font-size: 24px; line-height: 28px; font-weight: 700; color: #1f1f1f; }
        .status-pill { display: inline-block; background-color: #eff8ee; color: #237a33; font-size: 12px; line-height: 12px; font-weight: 700; padding: 8px 12px; border-radius: 999px; }
        .ticket-shell { background: linear-gradient(180deg, #fffaf5 0%, #fff3e8 100%); border: 1px solid #efd8c2; border-radius: 18px; overflow: hidden; }
        .ticket-top { background: linear-gradient(135deg, rgba(106,137,115,0.12) 0%, rgba(200,169,107,0.16) 100%); }
        .ticket-chip { display: inline-block; font-size: 11px; line-height: 11px; font-weight: 700; color: #274235; background-color: #e8f2ec; letter-spacing: 0.8px; text-transform: uppercase; padding: 8px 12px; border-radius: 999px; }
        .ticket-name { font-size: 26px; line-height: 32px; font-weight: 700; color: #1f1f1f; }
        .perforation { border-top: 2px dashed #e9cdb3; height: 2px; line-height: 2px; font-size: 0; }
        .button { display: inline-block; background: linear-gradient(135deg, #6a8973 0%, #5b7764 100%); color: #ffffff !important; font-size: 14px; line-height: 14px; font-weight: 700; padding: 12px 18px; border-radius: 999px; }
        .qr-wrap { background: linear-gradient(180deg, #fffaf5 0%, #fff1e6 100%); border: 1px solid #efd8c2; border-radius: 18px; box-shadow: inset 0 0 0 1px rgba(106,137,115,0.08); }
        .footer { font-size: 13px; line-height: 21px; color: #7b736b; text-align: center; }
        .stack-col { vertical-align: top; }
        .icon-pill { display: inline-block; background-color: rgba(31,31,31,0.05); border: 1px solid rgba(31,31,31,0.08); color: #fafafa; font-size: 13px; line-height: 13px; font-weight: 700; padding: 10px 12px; border-radius: 999px; margin: 0 8px 8px 0; }
        .dark-card { background: linear-gradient(135deg, #232b47 0%, #3b4a78 100%); border-radius: 14px; }
        .dark-card-title { font-size: 16px; line-height: 20px; font-weight: 700; color: #ffffff; margin: 0 0 10px; }
        .dark-card-copy { font-size: 13px; line-height: 20px; color: #dfe8ff; margin: 0; }
        .contact-box { background-color: #fffaf5; border: 1px solid #f0dfd0; border-radius: 14px; }
        .gate-label { display: inline-block; font-size: 12px; line-height: 12px; font-weight: 800; letter-spacing: 0.9px; text-transform: uppercase; color: #6a8973; background-color: #e8f2ec; border: 1px solid rgba(106,137,115,0.22); padding: 8px 12px; border-radius: 999px; }
        .sponsor-cell { background-color: #fffaf5; border: 1px solid #f0dfd0; border-radius: 14px; text-align: center; }
        .sponsor-grid-label { font-size: 12px; line-height: 16px; color: #8a7868; text-transform: uppercase; letter-spacing: 0.9px; font-weight: 700; padding-bottom: 10px; }
        @media screen and (max-width: 640px) {
            .px { padding-left: 16px !important; padding-right: 16px !important; }
            .hero-title { font-size: 25px !important; line-height: 31px !important; }
            .mobile-block, .mobile-block tbody, .mobile-block tr, .mobile-block td { display: block !important; width: 100% !important; }
            .mobile-spacer { display: block !important; width: 100% !important; height: 10px !important; }
        }
    </style>
</head>
<body>
    @php
        $tickets = $ticket_sale ?? $ticketsale ?? collect();
        $firstTicket = is_iterable($tickets) ? ($tickets->first() ?? null) : null;

        $totalPaid = is_iterable($tickets) ? $tickets->sum(function ($row) {
            $stripePrice = $row->stripe_price;
            if ($stripePrice !== null && $stripePrice !== '') {
                return floatval($stripePrice);
            }
            return floatval($row->total ?? 0);
        }) : 0;

        $subtotalTickets = is_iterable($tickets) ? $tickets->sum(function ($row) {
            return floatval($row->sub_total ?? 0);
        }) : 0;

        $drinksTotal = is_iterable($tickets) ? $tickets->sum(function ($row) {
            return floatval($row->drinks_total ?? 0);
        }) : 0;

        $tablesTotal = is_iterable($tickets) ? $tickets->sum(function ($row) {
            return floatval($row->tables_total ?? 0);
        }) : 0;

        $discountTotal = is_iterable($tickets) ? $tickets->sum(function ($row) {
            $coupon = floatval($row->coupan_amount ?? 0);
            $discount = floatval($row->discount ?? 0);
            return $coupon > 0 ? $coupon : $discount;
        }) : 0;

        $feeTotal = is_iterable($tickets) ? $tickets->sum(function ($row) {
            return floatval($row->fee ?? 0);
        }) : 0;

        $taxTotal = is_iterable($tickets) ? $tickets->sum(function ($row) {
            return floatval($row->tax ?? 0);
        }) : 0;

        $bookingDate = $event?->start_time ? \Carbon\Carbon::parse($event->start_time)->format('M d, Y') : '';
        $bookingTime = $event?->start_time ? \Carbon\Carbon::parse($event->start_time)->format('h:i A') : '';

        $descriptionText = trim((string)($event->description ?? ''));
        if ($descriptionText !== '') {
            $descriptionText = html_entity_decode(strip_tags($descriptionText));
            $descriptionText = preg_replace('/\s+/', ' ', $descriptionText);
            $descriptionText = trim((string)$descriptionText);
        }

        $disclaimerText = trim((string)($event->disclaimer ?? ''));
        if ($disclaimerText !== '') {
            $disclaimerText = html_entity_decode(strip_tags($disclaimerText));
            $disclaimerText = preg_replace('/\s+/', ' ', $disclaimerText);
            $disclaimerText = trim((string)$disclaimerText);
        }
    @endphp

    <center class="wrapper theme-default">
        <table class="main" role="presentation" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td class="px" style="padding-bottom: 14px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="card">
                        <tr>
                            <td class="hero px section-pad" style="padding-top: 26px; padding-bottom: 26px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td align="left" style="padding-bottom: 12px;">
                                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" class="logo-row">
                                                <tr>
                                                    <td style="padding-right: 10px;"><img src="https://linkupvibes.com/wp-content/uploads/2025/08/linkup-logo.png" width="34" height="34" alt="LinkUp logo" style="width:34px;height:34px;display:block;" /></td>
                                                    <td style="font-size: 26px; line-height: 28px; font-weight: 700; color: #ffffff;">LinkUp Events</td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr><td style="padding-bottom: 12px;"><span class="badge">Event Access</span></td></tr>
                                    <tr><td class="eyebrow">Ticket Confirmation</td></tr>
                                    <tr><td class="hero-title">Your event ticket is confirmed.</td></tr>
                                    <tr><td class="hero-copy">You’re all set for <strong>{{ $event->title }}</strong>. Please review your ticket details below and present your QR code at check-in.</td></tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td class="px" style="padding-bottom: 14px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="card">
                        <tr>
                            <td class="px section-pad" style="padding-bottom: 18px;">
                                <div class="accent-line"></div>
                                <h2 class="section-title" style="margin-top: 12px; margin-bottom: 14px;">Event Snapshot</h2>
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="mobile-block">
                                    <tr>
                                        <td width="50%" class="stack-col" style="padding-right: 10px;">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="mini-card">
                                                <tr>
                                                    <td style="padding: 14px;">
                                                        <div class="detail-label">Ticket Type</div>
                                                        <div class="detail-value">{{ $firstTicket->ticket_name ?? 'Event Ticket' }}</div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td class="mobile-spacer" style="width: 10px; font-size: 0; line-height: 0;">&nbsp;</td>
                                        <td width="50%" class="stack-col">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="mini-card">
                                                <tr>
                                                    <td style="padding: 14px;">
                                                        <div class="detail-label">Total Paid</div>
                                                        <div class="amount">${{ number_format($totalPaid, 2) }}</div>
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
                                                        <div class="detail-value-light">{{ $bookingDate }}</div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td class="mobile-spacer" style="width: 10px; font-size: 0; line-height: 0;">&nbsp;</td>
                                        <td width="33.33%" class="stack-col" style="padding-right: 10px;">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="mini-card">
                                                <tr>
                                                    <td style="padding: 14px;">
                                                        <div class="detail-label">Time</div>
                                                        <div class="detail-value-light">{{ $bookingTime }}</div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td class="mobile-spacer" style="width: 10px; font-size: 0; line-height: 0;">&nbsp;</td>
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

            <tr>
                <td class="px" style="padding-bottom: 14px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="card">
                        <tr>
                            <td class="px section-pad">
                                <h2 class="section-title" style="margin-bottom: 14px;">Your Digital Ticket</h2>

                                @foreach($tickets as $t)
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="ticket-shell" style="margin-bottom: 18px;">
                                        <tr>
                                            <td class="ticket-top" style="padding: 18px 18px 14px 18px;">
                                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                    <tr>
                                                        <td align="left" style="padding-bottom: 10px;">
                                                            <span class="ticket-chip">{{ ucfirst($t->ticket_type ?? 'General') }}</span>
                                                        </td>
                                                    </tr>
                                                    <tr><td class="ticket-name">{{ $event->title }}</td></tr>
                                                    <tr><td class="subtle" style="font-size: 15px; line-height: 22px;">Hosted by <strong style="color:#1f1f1f;">{{ $event->venue }}</strong></td></tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr><td class="perforation">&nbsp;</td></tr>

                                        @if(isset($sponsors) && $sponsors && method_exists($sponsors, 'isNotEmpty') && $sponsors->isNotEmpty() && $sponsors->first()?->image_url)
                                            <tr>
                                                <td style="padding: 12px 18px 0 18px;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="contact-box">
                                                        <tr>
                                                            <td style="padding: 10px 14px;" align="center">
                                                                <div class="sponsor-grid-label" style="padding-bottom: 8px;">Featured Sponsor</div>
                                                                <img src="{{ $sponsors->first()->image_url }}" alt="Featured sponsor" style="max-width: 120px; height: auto; margin: 0 auto;" />
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        @endif

                                        <tr>
                                            <td style="padding: 16px 18px 18px 18px;">
                                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="mobile-block">
                                                    <tr>
                                                        <td width="50%" class="stack-col" style="padding-right: 10px; padding-bottom: 14px;">
                                                            <div class="ticket-meta-label">Ticket ID</div>
                                                            <div class="ticket-meta-value">#{{ $t->id }}</div>
                                                        </td>
                                                        <td width="50%" class="stack-col" style="padding-bottom: 14px;">
                                                            <div class="ticket-meta-label">Event Style</div>
                                                            <div class="ticket-meta-value">{{ ucfirst($t->ticket_type ?? '') }}</div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td width="50%" class="stack-col" style="padding-right: 10px; padding-bottom: 14px;">
                                                            <div class="ticket-meta-label">Entries</div>
                                                            <div class="ticket-meta-value">{{ $t->no_of_tickets }}</div>
                                                        </td>
                                                        <td width="50%" class="stack-col" style="padding-bottom: 14px;">
                                                            <div class="ticket-meta-label">Paid Via</div>
                                                            <div class="ticket-meta-value">{{ ucfirst($t->payment_method) }} ({{ $t->pay_type }})</div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td width="50%" class="stack-col" style="padding-right: 10px;">
                                                            <div class="ticket-meta-label">Date</div>
                                                            <div class="ticket-meta-value">{{ $bookingDate }}</div>
                                                        </td>
                                                        <td width="50%" class="stack-col">
                                                            <div class="ticket-meta-label">Time</div>
                                                            <div class="ticket-meta-value">{{ $bookingTime }}</div>
                                                        </td>
                                                    </tr>
                                                </table>

                                                @php
                                                    $ticketStripePrice = $t->stripe_price;
                                                    $ticketPaid = ($ticketStripePrice !== null && $ticketStripePrice !== '') ? floatval($ticketStripePrice) : floatval($t->total ?? 0);
                                                    $ticketDiscount = floatval(($t->coupan_amount ?? 0) > 0 ? ($t->coupan_amount ?? 0) : ($t->discount ?? 0));
                                                @endphp

                                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top: 14px; border-top: 1px solid #f0dfd0;">
                                                    <tr>
                                                        <td colspan="2" style="padding-top: 12px; font-size: 12px; line-height: 16px; color: #8a7868; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 700;">
                                                            Payment Breakdown
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding: 6px 0; font-size: 13px; line-height: 18px; color: #67615b;">Subtotal</td>
                                                        <td align="right" style="padding: 6px 0; font-size: 13px; line-height: 18px; color: #67615b;">${{ number_format(floatval($t->sub_total ?? 0), 2) }}</td>
                                                    </tr>
                                                    @if(floatval($t->drinks_total ?? 0) > 0)
                                                        <tr>
                                                            <td style="padding: 6px 0; font-size: 13px; line-height: 18px; color: #67615b;">Drinks Total</td>
                                                            <td align="right" style="padding: 6px 0; font-size: 13px; line-height: 18px; color: #67615b;">${{ number_format(floatval($t->drinks_total ?? 0), 2) }}</td>
                                                        </tr>
                                                    @endif
                                                    @if(floatval($t->tables_total ?? 0) > 0)
                                                        <tr>
                                                            <td style="padding: 6px 0; font-size: 13px; line-height: 18px; color: #67615b;">Tables Total</td>
                                                            <td align="right" style="padding: 6px 0; font-size: 13px; line-height: 18px; color: #67615b;">${{ number_format(floatval($t->tables_total ?? 0), 2) }}</td>
                                                        </tr>
                                                    @endif
                                                    @if($ticketDiscount > 0)
                                                        <tr>
                                                            <td style="padding: 6px 0; font-size: 13px; line-height: 18px; color: #237a33;">Discount</td>
                                                            <td align="right" style="padding: 6px 0; font-size: 13px; line-height: 18px; color: #237a33;">-${{ number_format($ticketDiscount, 2) }}</td>
                                                        </tr>
                                                    @endif
                                                    @if(floatval($t->fee ?? 0) > 0)
                                                        <tr>
                                                            <td style="padding: 6px 0; font-size: 13px; line-height: 18px; color: #67615b;">Fee</td>
                                                            <td align="right" style="padding: 6px 0; font-size: 13px; line-height: 18px; color: #67615b;">${{ number_format(floatval($t->fee ?? 0), 2) }}</td>
                                                        </tr>
                                                    @endif
                                                    @if(floatval($t->tax ?? 0) > 0)
                                                        <tr>
                                                            <td style="padding: 6px 0; font-size: 13px; line-height: 18px; color: #67615b;">Tax</td>
                                                            <td align="right" style="padding: 6px 0; font-size: 13px; line-height: 18px; color: #67615b;">${{ number_format(floatval($t->tax ?? 0), 2) }}</td>
                                                        </tr>
                                                    @endif
                                                    <tr>
                                                        <td style="padding: 8px 0 0; font-size: 14px; line-height: 20px; color: #1f1f1f; font-weight: 700;">Total Paid</td>
                                                        <td align="right" style="padding: 8px 0 0; font-size: 14px; line-height: 20px; color: #1f1f1f; font-weight: 700;">${{ number_format($ticketPaid, 2) }}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                @endforeach
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td class="px" style="padding-bottom: 14px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="card">
                        <tr>
                            <td class="px section-pad">
                                <div class="accent-line"></div>
                                <h2 class="section-title" style="margin-top: 12px; margin-bottom: 14px;">Order Summary</h2>
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse;">
                                    <tr>
                                        <td style="padding: 8px 0; font-size: 14px; line-height: 20px; color: #1f1f1f;">Subtotal (Tickets)</td>
                                        <td align="right" style="padding: 8px 0; font-size: 14px; line-height: 20px; color: #1f1f1f; font-weight: 700;">${{ number_format($subtotalTickets, 2) }}</td>
                                    </tr>
                                    @if($drinksTotal > 0)
                                        <tr>
                                            <td style="padding: 6px 0; font-size: 14px; line-height: 20px; color: #1f1f1f;">Drinks Total</td>
                                            <td align="right" style="padding: 6px 0; font-size: 14px; line-height: 20px; color: #1f1f1f;">${{ number_format($drinksTotal, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if($tablesTotal > 0)
                                        <tr>
                                            <td style="padding: 6px 0; font-size: 14px; line-height: 20px; color: #1f1f1f;">Tables Total</td>
                                            <td align="right" style="padding: 6px 0; font-size: 14px; line-height: 20px; color: #1f1f1f;">${{ number_format($tablesTotal, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if($discountTotal > 0)
                                        <tr>
                                            <td style="padding: 6px 0; font-size: 14px; line-height: 20px; color: #237a33;">Discount</td>
                                            <td align="right" style="padding: 6px 0; font-size: 14px; line-height: 20px; color: #237a33;">-${{ number_format($discountTotal, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if($feeTotal > 0)
                                        <tr>
                                            <td style="padding: 6px 0; font-size: 14px; line-height: 20px; color: #1f1f1f;">Fees</td>
                                            <td align="right" style="padding: 6px 0; font-size: 14px; line-height: 20px; color: #1f1f1f;">${{ number_format($feeTotal, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if($taxTotal > 0)
                                        <tr>
                                            <td style="padding: 6px 0; font-size: 14px; line-height: 20px; color: #1f1f1f;">Tax</td>
                                            <td align="right" style="padding: 6px 0; font-size: 14px; line-height: 20px; color: #1f1f1f;">${{ number_format($taxTotal, 2) }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td colspan="2" style="padding-top: 10px; border-top: 1px solid #f0dfd0;"></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; font-size: 15px; line-height: 22px; color: #1f1f1f; font-weight: 700;">Total Paid</td>
                                        <td align="right" style="padding: 8px 0; font-size: 15px; line-height: 22px; color: #1f1f1f; font-weight: 700;">${{ number_format($totalPaid, 2) }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td class="px" style="padding-bottom: 14px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="card">
                        <tr>
                            <td class="px section-pad">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="mobile-block">
                                    <tr>
                                        <td width="50%" class="stack-col" style="padding-right: 10px;">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="dark-card">
                                                <tr>
                                                    <td style="padding: 18px;">
                                                        <h2 class="dark-card-title">Included</h2>
                                                        <div class="icon-pill">🎟️ Event entry</div>
                                                        <div class="icon-pill">📍 Access details</div>
                                                        <div class="icon-pill">🕒 Scheduled time</div>
                                                        <div class="icon-pill">🔳 QR check-in</div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td class="mobile-spacer" style="width: 10px; font-size: 0; line-height: 0;">&nbsp;</td>
                                        <td width="50%" class="stack-col">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="dark-card">
                                                <tr>
                                                    <td style="padding: 18px;">
                                                        <h2 class="dark-card-title">Event Notes</h2>
                                                        <p class="dark-card-copy">Access Type: {{ $firstTicket->ticket_name ?? 'Event Access' }}</p>
                                                        <p class="dark-card-copy" style="padding-top: 8px;">Arrival Tip: Please arrive 15 minutes early for smooth check-in.</p>
                                                        @if($disclaimerText !== '')
                                                            <p class="dark-card-copy" style="padding-top: 8px;">Note: {{ $disclaimerText }}</p>
                                                        @endif
                                                        <p class="dark-card-copy" style="padding-top: 8px;">Reminder: Please keep this confirmation email available for staff review if needed.</p>
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

            <tr>
                <td class="px" style="padding-bottom: 14px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="card">
                        <tr>
                            <td class="px section-pad">
                                <h2 class="section-title">Location &amp; Contact</h2>
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="mobile-block">
                                    <tr>
                                        <td width="50%" class="stack-col" style="padding-right: 10px;">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="contact-box">
                                                <tr>
                                                    <td style="padding: 14px;">
                                                        <div class="detail-label">Where</div>
                                                        <div class="detail-value-light">{{ $event->city }}, {{ $event->state }}, {{ $event->country }}</div>
                                                        <div class="detail-label" style="padding-top: 12px;">Venue</div>
                                                        <div class="detail-value-light">{{ $event->venue }}</div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td class="mobile-spacer" style="width: 10px; font-size: 0; line-height: 0;">&nbsp;</td>
                                        <td width="50%" class="stack-col">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="contact-box">
                                                <tr>
                                                    <td style="padding: 14px;">
                                                        <div class="detail-label">Organizer Email</div>
                                                        <div class="detail-value-light"><a href="mailto:{{ $event->email }}">{{ $event->email }}</a></div>
                                                        <div class="detail-label" style="padding-top: 12px;">Phone</div>
                                                        <div class="detail-value-light"><a href="tel:{{ $event->phone }}">{{ $event->phone }}</a></div>
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

            <tr>
                <td class="px" style="padding-bottom: 14px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="card">
                        <tr>
                            <td class="px section-pad" align="center">
                                <span class="gate-label">Scan to Enter</span>
                                <h2 class="section-title" style="margin-bottom: 8px;">Show this at entry</h2>
                                <p class="subtle" style="margin: 0 0 16px; max-width: 430px;">Present this QR code when you arrive. Keep this email handy for quick check-in.</p>
                                @if($firstTicket)
                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" class="qr-wrap">
                                        <tr>
                                            <td style="padding: 14px;" align="center">
                                                <img src="{{ $firstTicket->web_qrcode ? $app_url . '/storage/' . $firstTicket->web_qrcode : $app_url . '/assets/images/bgimageBuyticket.png' }}" width="190" height="190" alt="QR Code for booking #{{ $firstTicket->id }}" style="width: 190px; max-width: 100%; height: auto;" />
                                            </td>
                                        </tr>
                                    </table>
                                    <p style="margin: 12px 0 6px; font-size: 15px; line-height: 22px; color: #1f1f1f; font-weight: 700;">Ticket ID: #{{ $firstTicket->id }}</p>
                                @endif
                                <p style="margin: 0; font-size: 14px; line-height: 20px; color: #67615b;">Save this email for easy entry on arrival.</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td class="px" style="padding-bottom: 14px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="card">
                        <tr>
                            <td class="px section-pad">
                                <h2 class="section-title">Before You Arrive</h2>
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                    <tr><td class="subtle" style="padding-bottom: 8px;">• Bring this email or have your QR code ready for check-in.</td></tr>
                                    <tr><td class="subtle" style="padding-bottom: 8px;">• Arrive on time so your access experience is smooth.</td></tr>
                                    <tr><td class="subtle" style="padding-bottom: 8px;">• Contact the organizer directly if you need to update your booking.</td></tr>
                                    <tr><td class="subtle" style="padding-bottom: 14px;">• Refund and cancellation terms are subject to the organizer’s event policy.</td></tr>
                                    <tr><td><a href="{{ $app_url }}/user/orders" class="button">Manage Booking</a></td></tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            @if(isset($sponsors) && $sponsors && method_exists($sponsors, 'isNotEmpty') && $sponsors->isNotEmpty())
                <tr>
                    <td class="px" style="padding-bottom: 14px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="card">
                            <tr>
                                <td class="px section-pad" align="center">
                                    <h2 class="section-title" style="margin-bottom: 12px;">Sponsored By</h2>
                                    <p class="subtle" style="margin: 0 0 14px;">Partner brands supporting this event experience.</p>
                                    <div class="sponsor-grid-label" style="padding-bottom: 10px;">Event Sponsors</div>
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="mobile-block">
                                        <tr>
                                            @foreach($sponsors->take(3) as $sp)
                                                <td width="33.33%" class="stack-col" style="padding-right: 10px;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="sponsor-cell">
                                                        <tr>
                                                            <td style="padding: 14px;" align="center">
                                                                @if($sp?->image_url)
                                                                    <img src="{{ $sp->image_url }}" alt="{{ $sp->name ?? 'Sponsor' }}" style="max-width: 96px; height: auto; margin: 0 auto;" />
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            @endforeach
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            @endif

            <tr>
                <td class="px" style="padding-bottom: 14px;">
                    @include('emails.partials.sponsor')
                </td>
            </tr>

            <tr>
                <td class="px" style="padding-top: 4px; padding-bottom: 14px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td class="footer">
                                Thanks for booking with LinkUp ✨<br />
                                © {{ date('Y') }} Link Up Events. All rights reserved.
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>
