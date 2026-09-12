<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Sent — LinkUp</title>
    <style>
        @media only screen and (max-width: 600px) {
            .email-shell {
                width: 100% !important;
                border-radius: 0 !important;
                border-left: none !important;
                border-right: none !important;
            }

            .stack-column {
                display: block !important;
                width: 100% !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                margin-bottom: 12px !important;
            }

            .stack-column div {
                min-height: auto !important;
            }
        }
    </style>
</head>

<body
    style="margin: 0; padding: 0; background: #ede8dc; font-family: -apple-system, 'Segoe UI', Arial, sans-serif; -webkit-font-smoothing: antialiased; color: #101828;">

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
        style="background: #ede8dc; padding: 32px 12px 52px;">
        <tr>
            <td align="center">

                <!-- Main Card Wrapper (Email Shell) -->
                <table class="email-shell" width="100%" cellpadding="0" cellspacing="0" role="presentation"
                    style="max-width: 640px; background: #fffdf8; border-radius: 28px; overflow: hidden; box-shadow: 0 24px 64px rgba(56, 42, 15, .16); border: 1px solid #e0d5be; border-collapse: separate;">

                    <!-- LOGO BAR -->
                    <tr>
                        <td align="center" style="padding: 30px 36px 22px; border-bottom: 1px solid #f0e8d4;">
                            <img src="{{ asset('LinkUp Logo_1.png') }}" alt="LinkUp"
                                style="height: 36px; display: block; margin: auto;">
                            <p
                                style="margin: 8px 0 0; font-size: 13px; color: #667085; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                Linking <strong style="color: #126fc9;">Caribbean</strong> &amp; Latin American People
                                <strong style="color: #b07e10;">Everywhere.</strong>
                            </p>
                            <div
                                style="margin-top: 6px; font-size: 12px; font-weight: 700; color: #667085; letter-spacing: .2px; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                {{ $country_flag ?? '' }} {{ $country_name ?? '' }}
                            </div>
                        </td>
                    </tr>

                    <!-- SUCCESS HERO -->
                    <tr>
                        <td align="center"
                            style="background: #065f46; background: linear-gradient(160deg, #064e3b 0%, #065f46 60%, #047857 100%); padding: 44px 40px 40px; text-align: center; color: #ffffff;">

                            <!-- Check Circle -->
                            <div
                                style="width: 72px; height: 72px; background: rgba(255, 255, 255, .15); border: 2.5px solid rgba(255, 255, 255, .35); border-radius: 50%; display: inline-block; text-align: center; line-height: 66px; font-size: 32px; margin-bottom: 20px;">
                                ✅
                            </div>

                            <div
                                style="font-size: 11px; font-weight: 800; letter-spacing: 2.5px; text-transform: uppercase; color: rgba(255, 255, 255, .65); margin-bottom: 10px; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                Payment Confirmed
                            </div>

                            <h1
                                style="margin: 0 0 10px; font-size: 36px; font-weight: 900; color: #ffffff; line-height: 1.1; letter-spacing: -.5px; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                Payment Sent!
                            </h1>

                            <p
                                style="margin: 0; font-size: 16px; color: rgba(255, 255, 255, .75); line-height: 1.5; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                Your payment was processed successfully.
                            </p>

                            <!-- Amount Pill -->
                            <div
                                style="background: rgba(255, 255, 255, .14); border: 1.5px solid rgba(255, 255, 255, .25); border-radius: 100px; display: inline-block; padding: 10px 28px; margin-top: 22px;">
                                <span
                                    style="font-size: 42px; font-weight: 900; color: #ffffff; line-height: 1; letter-spacing: -1.5px; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">${{ number_format($amount, 0) }}</span><span
                                    style="font-size: 22px; font-weight: 800; color: rgba(255, 255, 255, .8); font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">.{{ str_pad(round(($amount - floor($amount)) * 100, 0), 2, '0', STR_PAD_LEFT) }}</span>
                            </div>

                        </td>
                    </tr>

                    <!-- BODY WRAPPER -->
                    <tr>
                        <td style="padding: 32px 32px 24px;">

                            <!-- Recipient row -->
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
                                style="background: #f9f8f5; border: 1px solid #ede8dc; border-radius: 20px; margin-bottom: 16px; border-collapse: separate;">
                                <tr>
                                    <td style="padding: 18px 20px;">
                                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                            <tr>
                                                <!-- Avatar -->
                                                <td width="56" valign="middle">
                                                    @if ($recipient_avatar)
                                                        <img src="{{ $recipient_avatar }}"
                                                            alt="{{ $recipient->name ?? 'Recipient' }}"
                                                            style="width: 56px; height: 56px; border-radius: 50%; object-fit: cover; border: 2px solid #e0d5be; display: block;">
                                                    @else
                                                        <div
                                                            style="width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, #b45309, #f59e0b); display: block; text-align: center; line-height: 56px; font-size: 22px; font-weight: 900; color: #ffffff; border: 2px solid #e0d5be;">
                                                            {{ substr($recipient->name ?? 'R', 0, 1) }}
                                                        </div>
                                                    @endif
                                                </td>

                                                <!-- Recipient info -->
                                                <td valign="middle" style="padding-left: 16px;">
                                                    <div
                                                        style="font-size: 10px; font-weight: 800; letter-spacing: 1.8px; text-transform: uppercase; color: #9ca3af; margin-bottom: 3px; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                                        Paid To</div>
                                                    <div
                                                        style="font-size: 18px; font-weight: 900; color: #101828; margin-bottom: 2px; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                                        {{ $recipient->name ?? '' }}</div>
                                                    <div
                                                        style="font-size: 13px; color: #667085; font-weight: 600; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                                        {{ $recipient->country ?? ($country_name ?? '') }} · LinkUp
                                                        Wallet</div>
                                                </td>

                                                <!-- Paid Badge -->
                                                <td width="90" align="right" valign="middle">
                                                    <div
                                                        style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #047857; font-size: 12px; font-weight: 800; padding: 6px 14px; border-radius: 100px; display: inline-block; white-space: nowrap; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                                        ✓ Paid
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Transaction breakdown -->
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
                                style="background: #ffffff; border: 1px solid #ede8dc; border-radius: 18px; overflow: hidden; margin-bottom: 20px; box-shadow: 0 4px 14px rgba(16, 24, 40, .05); border-collapse: separate;">
                                <tr>
                                    <td
                                        style="padding: 14px 20px; background: #f9f8f5; border-bottom: 1px solid #ede8dc; font-size: 10px; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; color: #9ca3af; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                        Transaction Details
                                    </td>
                                </tr>

                                <!-- Reference -->
                                <tr>
                                    <td style="padding: 14px 20px; border-bottom: 1px solid #f5f1ea;">
                                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                            <tr>
                                                <td
                                                    style="font-size: 14px; color: #667085; font-weight: 600; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                                    Reference</td>
                                                <td align="right"
                                                    style="font-size: 14px; color: #101828; font-weight: 800; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                                    #{{ $transaction_id ?? 'TXN-' . strtoupper(substr(md5(uniqid()), 0, 6)) }}
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Date & Time -->
                                <tr>
                                    <td style="padding: 14px 20px; border-bottom: 1px solid #f5f1ea;">
                                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                            <tr>
                                                <td
                                                    style="font-size: 14px; color: #667085; font-weight: 600; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                                    Date &amp; Time</td>
                                                <td align="right"
                                                    style="font-size: 14px; color: #101828; font-weight: 800; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                                    {{ $transaction_date ?? now()->format('F j, Y · g:i A') }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Payment method -->
                                <tr>
                                    <td style="padding: 14px 20px; border-bottom: 1px solid #f5f1ea;">
                                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                            <tr>
                                                <td
                                                    style="font-size: 14px; color: #667085; font-weight: 600; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                                    Payment method</td>
                                                <td align="right"
                                                    style="font-size: 14px; color: #101828; font-weight: 800; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                                    {{ $payment_method ?? 'LinkUp Wallet' }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Currency -->
                                <tr>
                                    <td style="padding: 14px 20px; border-bottom: 1px solid #f5f1ea;">
                                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                            <tr>
                                                <td
                                                    style="font-size: 14px; color: #667085; font-weight: 600; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                                    Currency</td>
                                                <td align="right"
                                                    style="font-size: 14px; color: #101828; font-weight: 800; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                                    {{ $currency ?? 'USD' }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Request reason -->
                                <tr>
                                    <td style="padding: 14px 20px; border-bottom: 1px solid #f5f1ea;">
                                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                            <tr>
                                                <td
                                                    style="font-size: 14px; color: #667085; font-weight: 600; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                                    Request reason</td>
                                                <td align="right"
                                                    style="font-size: 14px; color: #101828; font-weight: 800; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                                    {{ $reason ?? 'Payment via LinkUp' }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Fee -->
                                <tr>
                                    <td style="padding: 14px 20px; border-bottom: 1px solid #f5f1ea;">
                                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                            <tr>
                                                <td
                                                    style="font-size: 14px; color: #667085; font-weight: 600; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                                    Fee</td>
                                                <td align="right"
                                                    style="font-size: 14px; color: #047857; font-weight: 800; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                                    {{ ($fee ?? 0) > 0 ? '$' . number_format($fee, 2) : 'Free' }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Total Paid -->
                                <tr>
                                    <td
                                        style="padding: 16px 20px; background: #f0fdf4; border-top: 1.5px solid #a7f3d0;">
                                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                            <tr>
                                                <td
                                                    style="font-size: 15px; font-weight: 900; color: #065f46; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                                    Total Paid</td>
                                                <td align="right"
                                                    style="font-size: 20px; font-weight: 900; color: #047857; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                                    {{ $formattedAmount }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Note -->
                            @if ($note)
                                <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
                                    style="background: #ffffff; border: 1px solid #ede8dc; border-radius: 16px; border-top-left-radius: 4px; margin-bottom: 24px; box-shadow: 0 4px 14px rgba(16, 24, 40, .05); border-collapse: separate;">
                                    <tr>
                                        <td style="padding: 16px 20px;">
                                            <div
                                                style="font-size: 10px; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; color: #9ca3af; margin-bottom: 7px; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                                Your Note</div>
                                            <p
                                                style="font-size: 15px; color: #344054; line-height: 1.6; margin: 0; font-style: italic; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                                "{{ $note }}"</p>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            <!-- CTA -->
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td align="center" style="padding-bottom: 10px;">
                                        <a href="{{ $wallet_url ?? '#' }}"
                                            style="display: inline-block; background: #047857; background: linear-gradient(135deg, #047857, #059669); color: #ffffff; text-decoration: none; padding: 15px 44px; border-radius: 14px; font-size: 15px; font-weight: 800; box-shadow: 0 8px 22px rgba(4, 120, 87, .28); letter-spacing: .2px; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                            View Wallet History →
                                        </a>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- SECURITY STRIP -->
                    <tr>
                        <td style="padding: 0 32px 24px;">
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
                                style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 14px; border-collapse: separate;">
                                <tr>
                                    <td style="padding: 14px 18px;">
                                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                            <tr>
                                                <td width="32" valign="top"
                                                    style="font-size: 22px; line-height: 1;">
                                                    🔒
                                                </td>
                                                <td valign="top"
                                                    style="padding-left: 12px; font-size: 13px; color: #065f46; font-weight: 600; line-height: 1.5; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                                    <strong>Transaction secured.</strong> This payment was processed
                                                    using bank-level encryption. If you did not authorise this, contact
                                                    support immediately.
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- SPONSOR SLOT -->
                    <tr>
                        <td class="section-padding" style="padding:12px 40px 22px;">
                            @include('emails.partials.sponsor', ['ad' => $ad])
                        </td>
                    </tr>

                    <!-- SAFETY -->
                    <tr>
                        <td style="padding: 0 24px 24px;">
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>

                                    <!-- Card 1 -->
                                    <td class="stack-column" width="33.33%" valign="top"
                                        style="padding-right: 6px;">
                                        <div
                                            style="background: #f9fafb; border: 1px solid #f0f0f0; border-radius: 16px; padding: 18px 14px; text-align: center; min-height: 145px; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                            <span
                                                style="font-size: 26px; margin-bottom: 8px; display: block;">🛡️</span>
                                            <span
                                                style="font-size: 13px; font-weight: 800; color: #101828; margin-bottom: 5px; display: block;">Encrypted</span>
                                            <div style="font-size: 12px; color: #667085; line-height: 1.55;">Every
                                                transaction is protected end-to-end.</div>
                                        </div>
                                    </td>

                                    <!-- Card 2 -->
                                    <td class="stack-column" width="33.33%" valign="top"
                                        style="padding-left: 6px; padding-right: 6px;">
                                        <div
                                            style="background: #f9fafb; border: 1px solid #f0f0f0; border-radius: 16px; padding: 18px 14px; text-align: center; min-height: 145px; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                            <span
                                                style="font-size: 26px; margin-bottom: 8px; display: block;">🎧</span>
                                            <span
                                                style="font-size: 13px; font-weight: 800; color: #101828; margin-bottom: 5px; display: block;">Need
                                                help?</span>
                                            <div style="font-size: 12px; color: #667085; line-height: 1.55;">
                                                We're always here.<br>
                                                <a href="mailto:{{ $support_email ?? 'support@linkup.com' }}"
                                                    style="color: #126fc9; font-weight: 700; text-decoration: none;">Contact
                                                    Support</a>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Card 3 -->
                                    <td class="stack-column" width="33.33%" valign="top"
                                        style="padding-left: 6px;">
                                        <div
                                            style="background: #f9fafb; border: 1px solid #f0f0f0; border-radius: 16px; padding: 18px 14px; text-align: center; min-height: 145px; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                            <span
                                                style="font-size: 26px; margin-bottom: 8px; display: block;">🚩</span>
                                            <span
                                                style="font-size: 13px; font-weight: 800; color: #101828; margin-bottom: 5px; display: block;">Dispute
                                                a payment</span>
                                            <div style="font-size: 12px; color: #667085; line-height: 1.55;">Open the
                                                app to raise a dispute anytime.</div>
                                        </div>
                                    </td>

                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td
                            style="border-top: 1px solid #ede8d4; padding: 26px 36px 34px; text-align: center; background: #fffdf8;">

                            <img src="{{ asset('LinkUp Logo_1.png') }}" alt="LinkUp"
                                style="height: 26px; display: block; margin: 0 auto 14px;">

                            <div
                                style="font-size: 13px; color: #667085; line-height: 1.6; margin-bottom: 18px; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                © 2026 LinkUp {{ $country_flag ?? '' }} {{ $country_name ?? '' }}<br>
                                Linking <strong style="color: #126fc9;">Caribbean</strong> &amp; Latin American People
                                <strong style="color: #b07e10;">Everywhere.</strong>
                            </div>

                            <div
                                style="margin-bottom: 18px; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                <a href="#"
                                    style="font-size: 12px; font-weight: 600; color: #126fc9; text-decoration: none; margin: 0 7px;">Manage
                                    Preferences</a>
                                <a href="mailto:{{ $support_email ?? 'support@linkup.com' }}"
                                    style="font-size: 12px; font-weight: 600; color: #126fc9; text-decoration: none; margin: 0 7px;">Contact
                                    Support</a>
                                <a href="#"
                                    style="font-size: 12px; font-weight: 600; color: #126fc9; text-decoration: none; margin: 0 7px;">Privacy
                                    Policy</a>
                                <a href="#"
                                    style="font-size: 12px; font-weight: 600; color: #126fc9; text-decoration: none; margin: 0 7px;">Terms
                                    of Service</a>
                                <a href="#"
                                    style="font-size: 12px; font-weight: 600; color: #126fc9; text-decoration: none; margin: 0 7px;">Unsubscribe</a>
                            </div>

                            <div
                                style="margin-bottom: 18px; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                <a href="#"
                                    style="display: inline-block; width: 34px; height: 34px; border-radius: 9px; background: #e1306c; color: #ffffff; text-decoration: none; line-height: 34px; font-weight: 900; margin: 0 4px;"
                                    title="Instagram">IG</a>
                                <a href="#"
                                    style="display: inline-block; width: 34px; height: 34px; border-radius: 9px; background: #1877f2; color: #ffffff; text-decoration: none; line-height: 34px; font-weight: 900; margin: 0 4px;"
                                    title="Facebook">f</a>
                                <a href="#"
                                    style="display: inline-block; width: 34px; height: 34px; border-radius: 9px; background: #000000; color: #ffffff; text-decoration: none; line-height: 34px; font-weight: 900; margin: 0 4px;"
                                    title="TikTok">♪</a>
                            </div>

                            <div
                                style="font-size: 12px; color: #9ca3af; line-height: 1.7; font-family: -apple-system, 'Segoe UI', Arial, sans-serif;">
                                Follow us @linkupcaribbean
                            </div>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>
