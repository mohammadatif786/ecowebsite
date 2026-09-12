<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $hostName }} is live now 🔴</title>
    <style>
        * {
            box-sizing: border-box !important;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
            background: #ede8dc !important;
            font-family: -apple-system, 'Segoe UI', Arial, sans-serif !important;
            -webkit-font-smoothing: antialiased !important;
            color: #101828 !important;
        }

        .wrapper {
            max-width: 640px !important;
            margin: 0 auto !important;
            padding: 32px 16px 52px !important;
        }

        .card {
            background: #fffdf8 !important;
            border-radius: 28px !important;
            overflow: hidden !important;
            box-shadow: 0 24px 64px rgba(56, 42, 15, .16), 0 0 0 1px #e0d5be !important;
        }

        /* ── Logo bar ── */
        .logo-bar {
            padding: 30px 36px 22px !important;
            text-align: center !important;
            border-bottom: 1px solid #f0e8d4 !important;
        }

        .logo-bar img {
            height: 36px !important;
            display: block !important;
            margin: auto !important;
        }

        .logo-tagline {
            margin: 8px 0 0 !important;
            font-size: 13px !important;
            color: #667085 !important;
        }

        .logo-tagline strong {
            font-weight: 700 !important;
        }

        /* ── Intro ── */
        .intro {
            padding: 36px 40px 24px !important;
        }

        .intro-eyebrow {
            display: inline-block !important;
            font-size: 11px !important;
            font-weight: 800 !important;
            letter-spacing: 2.5px !important;
            text-transform: uppercase !important;
            color: #fff !important;
            background: #e02424 !important;
            padding: 5px 13px !important;
            border-radius: 100px !important;
            margin-bottom: 18px !important;
        }

        .intro h1 {
            font-size: 32px !important;
            font-weight: 900 !important;
            color: #101828 !important;
            margin: 0 !important;
            line-height: 1.2 !important;
            letter-spacing: -.5px !important;
        }

        .gold-rule {
            width: 48px !important;
            height: 4px !important;
            background: linear-gradient(90deg, #d4af37, #f5d060) !important;
            border-radius: 4px !important;
            margin: 16px 0 18px !important;
        }

        .intro p {
            font-size: 17px !important;
            line-height: 1.7 !important;
            color: #344054 !important;
            margin: 0 !important;
        }

        /* ── Stream wrap ── */
        .stream-wrap {
            padding: 0 32px 28px !important;
        }

        /* ── Detail chips ── */
        .detail-chip-label {
            font-size: 10px !important;
            font-weight: 800 !important;
            letter-spacing: 1.5px !important;
            text-transform: uppercase !important;
            color: #9ca3af !important;
            margin-bottom: 5px !important;
            display: block !important;
        }

        .detail-chip-value {
            font-size: 15px !important;
            font-weight: 900 !important;
            color: #101828 !important;
            display: block !important;
        }

        /* ── CTA ── */
        .cta-wrap {
            text-align: center !important;
            margin-bottom: 10px !important;
        }

        .join-btn {
            display: inline-block !important;
            background: #e02424 !important;
            color: white !important;
            text-decoration: none !important;
            padding: 16px 52px !important;
            border-radius: 14px !important;
            font-size: 16px !important;
            font-weight: 800 !important;
            letter-spacing: .2px !important;
        }

        .ignore-note {
            text-align: center !important;
            font-size: 13px !important;
            color: #9ca3af !important;
            margin-top: 14px !important;
            font-style: italic !important;
        }

        /* ── Safety ── */
        .safety-card {
            background: #f9fafb !important;
            border: 1px solid #f0f0f0 !important;
            border-radius: 16px !important;
            padding: 18px 14px !important;
            text-align: center !important;
        }

        .safety-icon {
            font-size: 26px !important;
            margin-bottom: 8px !important;
            display: block !important;
        }

        .safety-title {
            font-size: 13px !important;
            font-weight: 800 !important;
            color: #101828 !important;
            margin-bottom: 5px !important;
            display: block !important;
        }

        .safety-desc {
            font-size: 12px !important;
            color: #667085 !important;
            line-height: 1.55 !important;
        }

        .safety-desc a {
            color: #126fc9 !important;
            font-weight: 700 !important;
            text-decoration: none !important;
        }

        /* ── Sponsor ── */
        .sponsor-badge {
            font-size: 10px !important;
            font-weight: 800 !important;
            letter-spacing: 2px !important;
            text-transform: uppercase !important;
            color: #92670c !important;
            margin-bottom: 6px !important;
            display: block !important;
        }

        .sponsor-brand {
            font-size: 26px !important;
            font-weight: 900 !important;
            color: #5c3d00 !important;
            line-height: 1 !important;
            margin-bottom: 3px !important;
            display: block !important;
        }

        .sponsor-slogan {
            font-size: 12px !important;
            color: #92670c !important;
            font-weight: 600 !important;
            display: block !important;
        }

        .sponsor-offer-text {
            font-size: 15px !important;
            font-weight: 800 !important;
            color: #101828 !important;
            line-height: 1.4 !important;
            margin-bottom: 12px !important;
            display: block !important;
        }

        .sponsor-btn {
            display: inline-block !important;
            background: #0d9488 !important;
            color: #fff !important;
            text-decoration: none !important;
            padding: 9px 20px !important;
            border-radius: 9px !important;
            font-size: 13px !important;
            font-weight: 800 !important;
        }

        /* ── Footer ── */
        .footer {
            border-top: 1px solid #ede8d4 !important;
            padding: 26px 36px 34px !important;
            text-align: center !important;
        }

        .footer img {
            height: 26px !important;
            display: block !important;
            margin: 0 auto 14px !important;
        }

        .footer-tagline {
            font-size: 13px !important;
            color: #667085 !important;
            line-height: 1.6 !important;
            margin-bottom: 18px !important;
        }

        .footer-links a {
            font-size: 12px !important;
            font-weight: 600 !important;
            color: #126fc9 !important;
            text-decoration: none !important;
            display: inline-block !important;
            margin: 0 7px 10px !important;
        }

        .soc {
            width: 34px !important;
            height: 34px !important;
            border-radius: 9px !important;
            display: inline-block !important;
            text-align: center !important;
            line-height: 34px !important;
            font-size: 13px !important;
            font-weight: 900 !important;
            color: white !important;
            text-decoration: none !important;
            margin: 0 4px !important;
        }

        .footer-copy {
            font-size: 12px !important;
            color: #9ca3af !important;
            line-height: 1.7 !important;
        }

        /* ── Responsive ── */
        @media (max-width: 560px) {
            .wrapper {
                padding: 0 0 32px !important;
            }

            .intro {
                padding: 28px 22px 20px !important;
            }

            .intro h1 {
                font-size: 26px !important;
            }

            .stream-wrap {
                padding: 0 18px 24px !important;
            }

            .join-btn {
                padding: 14px 32px !important;
                font-size: 15px !important;
                display: block !important;
                text-align: center !important;
            }

            .footer {
                padding: 22px 22px 28px !important;
            }
        }
    </style>
</head>

<body style="background-color:#ede8dc; margin:0; padding:0;">

    <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation"
        style="background-color:#ede8dc !important;">
        <tr>
            <td align="center">
                <div class="wrapper">
                    <div class="card">

                        {{-- ── LOGO ── --}}
                        <div class="logo-bar">
                            <img src="{{ $logoUrl }}" alt="LinkUp">
                            <p class="logo-tagline">
                                Linking <strong style="color:#126fc9;">Caribbean</strong> &amp;
                                Latin American People <strong style="color:#b07e10;">Everywhere.</strong>
                            </p>
                        </div>

                        {{-- ── INTRO ── --}}
                        <div class="intro">
                            <div class="intro-eyebrow">&#9679; Live Now</div>
                            <h1>{{ $hostName }} just went live! 👋</h1>
                            <div class="gold-rule"></div>
                            <p>Hi {{ $recipientFirstName }}, someone you follow just started a live stream. Jump in
                                before you miss it.</p>
                        </div>

                        {{-- ── STREAM BLOCK ── --}}
                        <div class="stream-wrap">

                            {{-- Stream card rebuilt as email-safe table --}}
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
                                style="border-radius:22px !important; overflow:hidden !important; margin-bottom:20px !important; border-collapse:collapse !important;">
                                <tr>
                                    <td style="padding:0 !important; margin:0 !important;">

                                        {{-- TOP ROW: LIVE badge + Viewer count --}}
                                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
                                            style="background-color:#111111 !important;">
                                            <tr>
                                                <td style="padding:14px 16px !important;">
                                                    <table width="100%" cellpadding="0" cellspacing="0"
                                                        role="presentation">
                                                        <tr>
                                                            <td align="left">
                                                                <span
                                                                    style="
                                                                    background:#e02424 !important;
                                                                    color:#fff !important;
                                                                    font-size:11px !important;
                                                                    font-weight:900 !important;
                                                                    letter-spacing:2px !important;
                                                                    text-transform:uppercase !important;
                                                                    padding:5px 13px !important;
                                                                    border-radius:7px !important;
                                                                    display:inline-block !important;
                                                                    font-family:-apple-system,'Segoe UI',Arial,sans-serif !important;
                                                                ">&#9679;
                                                                    LIVE</span>
                                                            </td>
                                                            <td align="right">
                                                                <span
                                                                    style="
                                                                    background:#333333 !important;
                                                                    color:#fff !important;
                                                                    font-size:12px !important;
                                                                    font-weight:700 !important;
                                                                    padding:5px 11px !important;
                                                                    border-radius:100px !important;
                                                                    display:inline-block !important;
                                                                    font-family:-apple-system,'Segoe UI',Arial,sans-serif !important;
                                                                ">&#128065;
                                                                    {{ $viewerLabel }} watching</span>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>

                                        {{-- COVER IMAGE --}}
                                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                            <tr>
                                                <td style="padding:0 !important; line-height:0 !important;">
                                                    <img src="{{ $coverImageUrl }}" alt="{{ $streamTitle }}"
                                                        width="100%"
                                                        style="display:block !important; width:100% !important; height:260px !important; object-fit:cover !important; max-height:260px !important;">
                                                </td>
                                            </tr>
                                        </table>

                                        {{-- BOTTOM ROW: Avatar + Stream info --}}
                                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
                                            style="background-color:#111111 !important;">
                                            <tr>
                                                <td style="padding:16px 20px !important;">
                                                    <table cellpadding="0" cellspacing="0" role="presentation">
                                                        <tr>
                                                            {{-- Avatar --}}
                                                            <td valign="middle" style="padding-right:14px !important;">
                                                                @if ($hostAvatarUrl)
                                                                    <img src="{{ $hostAvatarUrl }}"
                                                                        alt="{{ $hostName }}" width="52"
                                                                        height="52"
                                                                        style="
                                                                            width:52px !important;
                                                                            height:52px !important;
                                                                            border-radius:50% !important;
                                                                            border:3px solid #e02424 !important;
                                                                            object-fit:cover !important;
                                                                            display:block !important;
                                                                        ">
                                                                @else
                                                                    <div
                                                                        style="
                                                                        width:52px !important;
                                                                        height:52px !important;
                                                                        border-radius:50% !important;
                                                                        border:3px solid #e02424 !important;
                                                                        background:linear-gradient(135deg,#e02424,#ff6b6b) !important;
                                                                        text-align:center !important;
                                                                        line-height:52px !important;
                                                                        font-size:20px !important;
                                                                        font-weight:900 !important;
                                                                        color:#fff !important;
                                                                        font-family:-apple-system,'Segoe UI',Arial,sans-serif !important;
                                                                    ">
                                                                        {{ $hostAvatarLetter }}</div>
                                                                @endif
                                                            </td>
                                                            {{-- Stream title + host --}}
                                                            <td valign="middle">
                                                                <div
                                                                    style="
                                                                    font-size:17px !important;
                                                                    font-weight:900 !important;
                                                                    color:#fff !important;
                                                                    margin-bottom:4px !important;
                                                                    font-family:-apple-system,'Segoe UI',Arial,sans-serif !important;
                                                                ">
                                                                    {{ $streamTitle }}</div>
                                                                <div
                                                                    style="
                                                                    font-size:13px !important;
                                                                    color:rgba(255,255,255,0.75) !important;
                                                                    font-weight:600 !important;
                                                                    font-family:-apple-system,'Segoe UI',Arial,sans-serif !important;
                                                                ">
                                                                    {{ $hostName }}
                                                                    @if ($hostCountryFlag || $hostCountryName)
                                                                        &nbsp;·&nbsp; {{ $hostCountryFlag }}
                                                                        {{ $hostCountryName }}
                                                                    @endif
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>
                            {{-- END stream card --}}

                            {{-- Detail chips --}}
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
                                style="margin-bottom:24px !important;">
                                <tr>
                                    <td width="33%" style="padding-right:8px !important;">
                                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                            <tr>
                                                <td
                                                    style="
                                                    background:#f9f8f5 !important;
                                                    border:1px solid #ede8dc !important;
                                                    border-radius:14px !important;
                                                    padding:14px 16px !important;
                                                    text-align:center !important;
                                                ">
                                                    <span class="detail-chip-label">Visibility</span>
                                                    <span class="detail-chip-value">{{ $visibilityLabel }}</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td width="33%"
                                        style="padding-right:4px !important; padding-left:4px !important;">
                                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                            <tr>
                                                <td
                                                    style="
                                                    background:#f9f8f5 !important;
                                                    border:1px solid #ede8dc !important;
                                                    border-radius:14px !important;
                                                    padding:14px 16px !important;
                                                    text-align:center !important;
                                                ">
                                                    <span class="detail-chip-label">Started</span>
                                                    <span class="detail-chip-value">&#9201; {{ $startedLabel }}</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td width="33%" style="padding-left:8px !important;">
                                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                            <tr>
                                                <td
                                                    style="
                                                    background:#f9f8f5 !important;
                                                    border:1px solid #ede8dc !important;
                                                    border-radius:14px !important;
                                                    padding:14px 16px !important;
                                                    text-align:center !important;
                                                ">
                                                    <span class="detail-chip-label">Category</span>
                                                    <span class="detail-chip-value">{{ $categoryLabel }}</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            {{-- CTA --}}
                            <div class="cta-wrap">
                                <a href="{{ $streamUrl }}" class="join-btn">&#128308; Join Live Stream</a>
                            </div>
                            <p class="ignore-note">If you don't recognize this activity, you can safely ignore this
                                email.</p>

                        </div>
                        {{-- END stream-wrap --}}

                        {{-- ── SPONSOR ── --}}
                        @if (!empty($ad))
                            @include('emails.partials.sponsor', ['ad' => $ad])
                        @endif

                        {{-- ── SAFETY ── --}}
                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
                            style="padding:0 24px 24px !important;">
                            <tr>
                                <td width="33%" style="padding-right:8px !important;" valign="top">
                                    <div class="safety-card">
                                        <span class="safety-icon">&#128737;&#65039;</span>
                                        <span class="safety-title">Safe streaming</span>
                                        <div class="safety-desc">All live streams are monitored for community
                                            guidelines.</div>
                                    </div>
                                </td>
                                <td width="33%" style="padding-right:4px !important; padding-left:4px !important;"
                                    valign="top">
                                    <div class="safety-card">
                                        <span class="safety-icon">&#127911;</span>
                                        <span class="safety-title">Need help?</span>
                                        <div class="safety-desc">
                                            We're always here.<br>
                                            <a
                                                href="mailto:{{ config('mail.support_address', 'support@linkup.com') }}">Contact
                                                Support</a>
                                        </div>
                                    </div>
                                </td>
                                <td width="33%" style="padding-left:8px !important;" valign="top">
                                    <div class="safety-card">
                                        <span class="safety-icon">&#128681;</span>
                                        <span class="safety-title">Report content</span>
                                        <div class="safety-desc">Tap the flag icon inside the stream to report.</div>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        {{-- ── FOOTER ── --}}
                        <div class="footer">
                            <img src="{{ $logoUrl }}" alt="LinkUp">
                            <div class="footer-tagline">
                                &copy; {{ date('Y') }} LinkUp<br>
                                Linking <strong style="color:#126fc9;">Caribbean</strong> &amp;
                                Latin American People <strong style="color:#b07e10;">Everywhere.</strong>
                            </div>
                            <div class="footer-links" style="margin-bottom:18px !important;">
                                <a
                                    href="{{ config('app.url') }}/preferences{{ $trackingToken ? '?t=' . $trackingToken : '' }}">Manage
                                    Preferences</a>
                                <a href="mailto:{{ config('mail.support_address', 'support@linkup.com') }}">Contact
                                    Support</a>
                                <a href="{{ config('app.url') }}/privacy">Privacy Policy</a>
                                <a href="{{ config('app.url') }}/terms">Terms of Service</a>
                                <a
                                    href="{{ config('app.url') }}/unsubscribe{{ $trackingToken ? '?t=' . $trackingToken : '' }}">Unsubscribe</a>
                            </div>
                            <div style="text-align:center !important; margin-bottom:18px !important;">
                                <a href="https://instagram.com/linkupcaribbean" class="soc"
                                    style="background:#e1306c !important;">IG</a>
                                <a href="https://facebook.com/linkupcaribbean" class="soc"
                                    style="background:#1877f2 !important;">f</a>
                                <a href="https://tiktok.com/@linkupcaribbean" class="soc"
                                    style="background:#000 !important;">&#9834;</a>
                            </div>
                            <div class="footer-copy">Follow us @linkupcaribbean</div>
                        </div>

                    </div>{{-- .card --}}
                </div>{{-- .wrapper --}}
            </td>
        </tr>
    </table>

</body>

</html>
