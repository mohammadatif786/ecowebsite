<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>LinkUp Cookout Ticket Confirmation</title>
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
        .hero { background: linear-gradient(135deg, #1f1f1f 0%, #3a241a 45%, #c94b20 100%); }
        .badge { display: inline-block; background-color: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.18); color: #ffffff; font-size: 12px; line-height: 12px; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase; padding: 8px 12px; border-radius: 999px; }
        .eyebrow { font-size: 12px; line-height: 16px; letter-spacing: 1.2px; text-transform: uppercase; font-weight: 700; color: #ffd9b8; }
        .hero-title { font-size: 32px; line-height: 38px; font-weight: 700; color: #ffffff; margin: 10px 0 10px; }
        .hero-copy { font-size: 15px; line-height: 23px; color: #fff1e6; margin: 0; }
        .section-title { font-size: 18px; line-height: 24px; font-weight: 700; color: #1f1f1f; margin: 0 0 14px; }
        .subtle { font-size: 13px; line-height: 20px; color: #67615b; }
        .accent-line { width: 56px; height: 4px; background: linear-gradient(90deg, #ff6a00 0%, #f4b400 100%); border-radius: 999px; }
        .mini-card { background-color: #fffaf5; border: 1px solid #f0dfd0; border-radius: 14px; }
        .detail-label, .ticket-meta-label { font-size: 12px; line-height: 16px; color: #8a7868; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 700; }
        .detail-value { font-size: 17px; line-height: 23px; color: #1f1f1f; font-weight: 700; }
        .detail-value-light, .ticket-meta-value { font-size: 15px; line-height: 21px; color: #1f1f1f; }
        .amount { font-size: 24px; line-height: 28px; font-weight: 700; color: #1f1f1f; }
        .status-pill { display: inline-block; background-color: #eff8ee; color: #237a33; font-size: 12px; line-height: 12px; font-weight: 700; padding: 8px 12px; border-radius: 999px; }
        .ticket-shell { background: linear-gradient(180deg, #fffaf5 0%, #fff3e8 100%); border: 1px solid #efd8c2; border-radius: 18px; overflow: hidden; }
        .ticket-top { background: linear-gradient(135deg, rgba(255,106,0,0.10) 0%, rgba(244,180,0,0.14) 100%); }
        .ticket-chip { display: inline-block; font-size: 11px; line-height: 11px; font-weight: 700; color: #7f3518; background-color: #ffe3cf; letter-spacing: 0.8px; text-transform: uppercase; padding: 8px 12px; border-radius: 999px; }
        .perforation { border-top: 2px dashed #e9cdb3; height: 2px; line-height: 2px; font-size: 0; }
        .button { display: inline-block; background: linear-gradient(135deg, #c94b20 0%, #ff6a00 100%); color: #ffffff !important; font-size: 14px; line-height: 14px; font-weight: 700; padding: 12px 18px; border-radius: 999px; }
        .qr-wrap { background: linear-gradient(180deg, #fffaf5 0%, #fff1e6 100%); border: 1px solid #efd8c2; border-radius: 18px; box-shadow: inset 0 0 0 1px rgba(255,106,0,0.06); }
        .footer { font-size: 13px; line-height: 21px; color: #7b736b; text-align: center; }
        .stack-col { vertical-align: top; }
        .icon-pill { display: inline-block; background-color: rgba(31,31,31,0.05); border: 1px solid rgba(31,31,31,0.08); color: #1f1f1f; font-size: 13px; line-height: 13px; font-weight: 700; padding: 10px 12px; border-radius: 999px; margin: 0 8px 8px 0; }
        @media screen and (max-width: 640px) {
            .px { padding-left: 16px !important; padding-right: 16px !important; }
            .hero-title { font-size: 25px !important; line-height: 31px !important; }
            .mobile-block, .mobile-block tbody, .mobile-block tr, .mobile-block td { display: block !important; width: 100% !important; }
            .mobile-spacer { display: block !important; width: 100% !important; height: 10px !important; }
        }
    </style>
</head>
<body>
    <center class="wrapper">
        <table class="main" role="presentation" cellpadding="0" cellspacing="0" border="0">
            <!-- Hero -->
            <tr>
                <td class="px" style="padding-bottom: 14px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="card">
                        <tr>
                            <td class="hero px section-pad" style="padding-top: 26px; padding-bottom: 26px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td align="left" style="padding-bottom: 12px;">
                                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                                <tr>
                                                    <td style="padding-right: 10px;"><img src="https://linkupvibes.com/wp-content/uploads/2025/08/linkup-logo.png" width="34" height="34" alt="LinkUp logo" style="width:34px;height:34px;display:block;" /></td>
                                                    <td style="font-size: 26px; line-height: 28px; font-weight: 700; color: #ffffff;">LinkUp Cookout</td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr><td style="padding-bottom: 12px;"><span class="badge">Cookout Access</span></td></tr>
                                    <tr><td class="eyebrow">Ticket Confirmation</td></tr>
                                    <tr><td class="hero-title">Your cookout pass is locked in.</td></tr>
                                    <tr><td class="hero-copy">You’re officially on the list for <strong>{{ $event->title }}</strong>. Good food, good vibes, and good people are waiting. Pull up with your QR code ready at the gate.</td></tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <!-- Snapshot -->
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
                                                        <div class="detail-label">Pass Type</div>
                                                        <div class="detail-value">{{ $ticket_sale->first()->ticket_name ?? 'Cookout Pass' }}</div>
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
                                                        @php
                                                            $totalPaid = $ticket_sale->sum(function ($row) {
                                                                $stripePrice = $row->stripe_price;
                                                                if ($stripePrice !== null && $stripePrice !== '') {
                                                                    return floatval($stripePrice);
                                                                }
                                                                return floatval($row->total ?? 0);
                                                            });

                                                            $feeBreakdown = $ticket_sale->first()->fee_breakdown ?? [];
                                                            $cookoutFeePctRate = floatval(data_get($feeBreakdown, 'cookout_fee_pct_rate', 0));
                                                            $cookoutFeeFixedRate = floatval(data_get($feeBreakdown, 'cookout_fee_fixed_rate', 0));
                                                            $cookoutFeeAmount = floatval(data_get($feeBreakdown, 'cookout_fee_amount', 0));
                                                        @endphp
                                                        <div class="amount">${{ number_format($totalPaid, 2) }}</div>

                                                        @if($cookoutFeeAmount > 0)
                                                            <div class="subtle" style="margin-top: 10px;">
                                                                <div>
                                                                    <strong style="color:#1f1f1f;">Cookout Fee:</strong>
                                                                    @if($cookoutFeePctRate > 0)
                                                                        {{ number_format($cookoutFeePctRate, 2) }}%
                                                                    @endif
                                                                    @if($cookoutFeeFixedRate > 0)
                                                                        @if($cookoutFeePctRate > 0) + @endif
                                                                        ${{ number_format($cookoutFeeFixedRate, 2) }}
                                                                    @endif
                                                                    = ${{ number_format($cookoutFeeAmount, 2) }}
                                                                </div>
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
                                        <td class="mobile-spacer" style="width: 10px; font-size: 0; line-height: 0;">&nbsp;</td>
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

            <!-- Order Summary -->
            <tr>
                <td class="px" style="padding-bottom: 14px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="card">
                        <tr>
                            <td class="px section-pad">
                                <div class="accent-line"></div>
                                <h2 class="section-title" style="margin-top: 12px; margin-bottom: 14px;">Order Summary</h2>
                                @php
                                    $subtotal = $ticket_sale->sum(function ($row) {
                                        return floatval($row->total ?? 0);
                                    });

                                    $addonCategoryLabels = [
                                        'cookout_protein' => 'Protein',
                                        'cookout_drink' => 'Drink',
                                        'cookout_manual' => 'Addon',
                                    ];
                                @endphp

                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse;">
                                    @foreach($ticket_sale as $ticket)
                                        <tr>
                                            <td style="padding: 8px 0; font-size: 14px; line-height: 20px; color: #1f1f1f;">
                                                {{ $ticket->no_of_tickets }} × {{ $ticket->ticket_name }}
                                            </td>
                                            <td align="right" style="padding: 8px 0; font-size: 14px; line-height: 20px; color: #1f1f1f; font-weight: 700;">
                                                ${{ number_format(floatval($ticket->sub_total ?? 0), 2) }}
                                            </td>
                                        </tr>

                                        @php
                                            $includedProtein = trim((string)($ticket->cookout_included_protein ?? ''));
                                            $includedSides = $ticket->cookout_included_sides;
                                            $cookoutAddons = $ticket->cookout_addons;
                                        @endphp

                                        @if($includedProtein !== '')
                                            <tr>
                                                <td style="padding: 4px 0; font-size: 13px; line-height: 18px; color: #67615b;">
                                                    {{ $includedProtein }} (Included Protein) x1
                                                </td>
                                                <td align="right" style="padding: 4px 0; font-size: 13px; line-height: 18px; color: #67615b;">$0.00</td>
                                            </tr>
                                        @endif

                                        @if(is_array($includedSides) && count($includedSides) > 0)
                                            @foreach($includedSides as $side)
                                                @php $sideName = trim((string)$side); @endphp
                                                @if($sideName !== '')
                                                    <tr>
                                                        <td style="padding: 4px 0; font-size: 13px; line-height: 18px; color: #67615b;">
                                                            {{ $sideName }} (Included Side) x1
                                                        </td>
                                                        <td align="right" style="padding: 4px 0; font-size: 13px; line-height: 18px; color: #67615b;">$0.00</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @endif

                                        @if(is_array($cookoutAddons) && count($cookoutAddons) > 0)
                                            @foreach($cookoutAddons as $addon)
                                                @php
                                                    $addonName = (string) data_get($addon, 'name', '');
                                                    $addonCategory = (string) data_get($addon, 'category', '');
                                                    $addonQty = intval(data_get($addon, 'quantity', 0));
                                                    $addonTotal = floatval(data_get($addon, 'total_price', 0));
                                                    $label = $addonCategoryLabels[$addonCategory] ?? $addonCategory;
                                                @endphp
                                                @if(trim($addonName) !== '' && $addonQty > 0)
                                                    <tr>
                                                        <td style="padding: 4px 0; font-size: 13px; line-height: 18px; color: #67615b;">
                                                            {{ $addonName }} ({{ $label }}) x{{ $addonQty }}
                                                        </td>
                                                        <td align="right" style="padding: 4px 0; font-size: 13px; line-height: 18px; color: #67615b;">
                                                            ${{ number_format($addonTotal, 2) }}
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @endif
                                    @endforeach

                                    <tr>
                                        <td colspan="2" style="padding-top: 10px; border-top: 1px solid #f0dfd0;"></td>
                                    </tr>

                                    <tr>
                                        <td style="padding: 8px 0; font-size: 14px; line-height: 20px; color: #1f1f1f; font-weight: 700;">Subtotal:</td>
                                        <td align="right" style="padding: 8px 0; font-size: 14px; line-height: 20px; color: #1f1f1f; font-weight: 700;">${{ number_format($subtotal, 2) }}</td>
                                    </tr>

                                    @if($cookoutFeeAmount > 0)
                                        <tr>
                                            <td style="padding: 6px 0; font-size: 14px; line-height: 20px; color: #1f1f1f;">Cookout Fees:</td>
                                            <td align="right" style="padding: 6px 0; font-size: 14px; line-height: 20px; color: #1f1f1f;">${{ number_format($cookoutFeeAmount, 2) }}</td>
                                        </tr>
                                    @endif

                                    <tr>
                                        <td colspan="2" style="padding-top: 10px; border-top: 1px solid #f0dfd0;"></td>
                                    </tr>

                                    <tr>
                                        <td style="padding: 8px 0; font-size: 15px; line-height: 22px; color: #1f1f1f; font-weight: 700;">Total Paid:</td>
                                        <td align="right" style="padding: 8px 0; font-size: 15px; line-height: 22px; color: #1f1f1f; font-weight: 700;">${{ number_format($totalPaid, 2) }}</td>
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
                                    <tr><td align="left" style="padding-bottom: 12px;"><span class="ticket-chip">Entry Pass</span></td></tr>
                                    <tr><td class="ticket-name" style="font-size: 28px; line-height: 34px; color: #1f1f1f; font-weight: 700; padding-bottom: 6px;">{{ $ticket->ticket_name }}</td></tr>
                                    <tr><td class="subtle" style="font-size: 15px; line-height: 22px;">Hosted by <strong style="color:#1f1f1f;">{{ $event->venue }}</strong></td></tr>
                                </table>
                            </td>
                        </tr>
                        <tr><td class="perforation">&nbsp;</td></tr>
                        
                        <tr>
                            <td style="padding: 20px 24px 24px 24px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="mobile-block">
                                    <tr>
                                        <td width="50%" class="stack-col" style="padding-right: 12px; padding-bottom: 18px;">
                                            <div class="ticket-meta-label">Ticket ID</div>
                                            <div class="ticket-meta-value">#{{ $ticket->id }}</div>
                                        </td>
                                        <td width="50%" class="stack-col" style="padding-bottom: 18px;">
                                            <div class="ticket-meta-label">Event Style</div>
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

            <!-- Experience Details -->
            <tr>
                <td class="px" style="padding-bottom: 14px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="card">
                        <tr>
                            <td class="px section-pad">
                                <h2 class="section-title" style="margin-bottom: 14px;">What’s Included</h2>
                                <div class="icon-pill">🍗 Grilled food</div>
                                <div class="icon-pill">🎶 Music</div>
                                <div class="icon-pill">🥤 Drinks</div>
                                <div class="icon-pill">🔥 Backyard vibes</div>
                                <p class="subtle" style="margin-top: 14px;"><strong>Arrival Tip:</strong> Pull up early. Food goes fast and the best vibes start first.</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <!-- Location & Contact -->
            <tr>
                <td class="px" style="padding-bottom: 14px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="card">
                        <tr>
                            <td class="px section-pad">
                                <h2 class="section-title">Location & Contact</h2>
                                <p class="subtle" style="margin-bottom: 10px;"><strong style="color:#1f1f1f;">Where:</strong> {{ $event->venue }}, {{ $event->city }}, {{ $event->state }}</p>
                                <p class="subtle" style="margin-bottom: 10px;"><strong style="color:#1f1f1f;">Email:</strong> <a href="mailto:{{ $event->email }}">{{ $event->email }}</a></p>
                                <p class="subtle" style="margin-bottom: 10px;"><strong style="color:#1f1f1f;">Phone:</strong> <a href="tel:{{ $event->phone }}">{{ $event->phone }}</a></p>
                                @php
                                    $disclaimerText = trim((string)($event->disclaimer ?? ''));
                                    if ($disclaimerText !== '') {
                                        $disclaimerText = html_entity_decode(strip_tags($disclaimerText));
                                        $disclaimerText = preg_replace('/\s+/', ' ', $disclaimerText);
                                        $disclaimerText = trim((string)$disclaimerText);
                                    }
                                @endphp
                                @if($disclaimerText !== '')
                                    <p class="subtle"><strong style="color:#1f1f1f;">Note:</strong> {{ $disclaimerText }}</p>
                                @endif
                                <div style="padding-top: 18px;">
                                    <a href="{{ $app_url }}/user/orders" class="button">Manage Booking</a>
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