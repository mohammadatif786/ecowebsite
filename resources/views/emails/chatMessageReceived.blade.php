<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Message — LinkUp</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            background: #ede8dc;
            font-family: -apple-system, 'Segoe UI', Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            color: #101828;
        }

        .wrapper {
            max-width: 640px;
            margin: 0 auto;
            padding: 32px 16px 52px;
        }

        .card {
            background: #fffdf8;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 24px 64px rgba(56, 42, 15, .16), 0 0 0 1px #e0d5be;
        }

        /* ── Logo bar ── */
        .logo-bar {
            padding: 30px 36px 22px;
            text-align: center;
            border-bottom: 1px solid #f0e8d4;
        }

        .logo-bar img {
            height: 36px;
            display: block;
            margin: auto;
        }

        .logo-tagline {
            margin: 8px 0 0;
            font-size: 13px;
            color: #667085;
        }

        .logo-tagline strong {
            font-weight: 700;
        }

        /* ── Intro ── */
        .intro {
            padding: 40px 40px 28px;
        }

        .intro-eyebrow {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: #126fc9;
            margin-bottom: 12px;
        }

        .intro h1 {
            font-size: 34px;
            font-weight: 900;
            color: #101828;
            margin: 0;
            line-height: 1.15;
            letter-spacing: -.5px;
        }

        .gold-rule {
            width: 48px;
            height: 4px;
            background: linear-gradient(90deg, #d4af37, #f5d060);
            border-radius: 4px;
            margin: 16px 0 18px;
        }

        .intro p {
            font-size: 17px;
            line-height: 1.7;
            color: #344054;
            margin: 0;
        }

        /* ── Message block ── */
        .message-wrap {
            padding: 0 32px 28px;
        }

        /* Sender card */
        .sender-card {
            background: #f9f8f5;
            border: 1px solid #ede8dc;
            border-radius: 20px;
            padding: 18px 20px;
            margin-bottom: 16px;
            display: block;
        }

        .sender-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e0d5be;
        }

        .sender-avatar-placeholder {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #126fc9, #1e90e8);
            text-align: center;
            line-height: 60px;
            font-size: 24px;
            font-weight: 900;
            color: #fff;
            border: 2px solid #e0d5be;
        }

        .sender-info {
            min-width: 0;
        }

        .sender-name {
            font-size: 18px;
            font-weight: 900;
            color: #101828;
            margin-bottom: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sender-meta {
            font-size: 13px;
            color: #667085;
            font-weight: 600;
        }

        .online-dot {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }

        /* Message bubble */
        .message-bubble {
            background: #fff;
            border: 1px solid #ede8dc;
            border-radius: 18px;
            border-top-left-radius: 4px;
            padding: 20px 22px;
            margin-bottom: 24px;
            box-shadow: 0 4px 16px rgba(16, 24, 40, .06);
            position: relative;
        }

        .bubble-label {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #9ca3af;
            margin-bottom: 10px;
        }

        .bubble-text {
            font-size: 17px;
            color: #344054;
            line-height: 1.65;
            margin: 0;
        }

        .bubble-time {
            margin-top: 12px;
            font-size: 12px;
            color: #9ca3af;
            font-weight: 600;
        }

        /* CTA button */
        .cta-wrap {
            text-align: center;
            margin-bottom: 10px;
        }

        .open-chat-btn {
            display: inline-block;
            background: linear-gradient(135deg, #126fc9, #1e90e8);
            color: white !important;
            text-decoration: none;
            padding: 16px 48px;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 800;
            box-shadow: 0 8px 22px rgba(18, 111, 201, .30);
            letter-spacing: .2px;
        }

        .reply-note {
            text-align: center;
            font-size: 13px;
            color: #9ca3af;
            margin-top: 14px;
            margin-bottom: 0;
            font-style: italic;
        }

        /* ── Safety tip ── */
        .safety-tip {
            margin: 0 32px 24px;
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 14px;
            padding: 14px 18px;
            display: block;
        }

        .safety-tip-icon {
            font-size: 20px;
            line-height: 1;
        }

        .safety-tip-text {
            font-size: 13px;
            color: #0369a1;
            line-height: 1.55;
            font-weight: 500;
        }

        .safety-tip-text strong {
            font-weight: 800;
        }

        /* ── Safety row ── */
        .safety {
            margin: 0 24px 24px;
            display: block;
        }

        .safety-card {
            background: #f9fafb;
            border: 1px solid #f0f0f0;
            border-radius: 16px;
            padding: 18px 14px;
            text-align: center;
        }

        .safety-icon {
            font-size: 26px;
            margin-bottom: 8px;
            display: block;
        }

        .safety-title {
            font-size: 13px;
            font-weight: 800;
            color: #101828;
            margin-bottom: 5px;
            display: block;
        }

        .safety-desc {
            font-size: 12px;
            color: #667085;
            line-height: 1.55;
        }

        .safety-desc a {
            color: #126fc9;
            font-weight: 700;
            text-decoration: none;
        }

        /* ── Footer ── */
        .footer {
            border-top: 1px solid #ede8d4;
            padding: 26px 36px 34px;
            text-align: center;
        }

        .footer img {
            height: 26px;
            display: block;
            margin: 0 auto 14px;
        }

        .footer-tagline {
            font-size: 13px;
            color: #667085;
            line-height: 1.6;
            margin-bottom: 18px;
        }

        .footer-links {
            text-align: center;
            margin-bottom: 18px;
        }

        .footer-links a {
            font-size: 12px;
            font-weight: 600;
            color: #126fc9;
            text-decoration: none;
            display: inline-block;
            margin: 0 7px 10px;
        }

        .footer-social {
            text-align: center;
            margin-bottom: 18px;
        }

        .soc {
            width: 34px;
            height: 34px !important;
            border-radius: 9px;
            display: inline-block;
            text-align: center;
            line-height: 34px;
            font-size: 13px;
            font-weight: 900;
            color: white !important;
            text-decoration: none;
            margin: 0 4px;
        }

        .footer-copy {
            font-size: 12px;
            color: #9ca3af;
            line-height: 1.7;
        }

        /* ── Responsive ── */
        @media (max-width: 560px) {
            .wrapper {
                padding: 0 0 32px;
            }

            .card {
                border-radius: 0;
                box-shadow: none;
            }

            .intro {
                padding: 28px 22px 22px;
            }

            .intro h1 {
                font-size: 26px;
            }

            .message-wrap {
                padding: 0 18px 24px;
            }

            .sender-card {
                padding: 14px 16px;
            }

            .sender-avatar,
            .sender-avatar-placeholder {
                width: 48px;
                height: 48px;
                font-size: 20px;
                line-height: 48px;
            }

            .sender-name {
                font-size: 16px;
            }

            .message-bubble {
                padding: 16px 18px;
            }

            .bubble-text {
                font-size: 15px;
            }

            .open-chat-btn {
                padding: 14px 32px;
                font-size: 15px;
                display: block;
                text-align: center;
            }

            .safety-tip {
                margin: 0 18px 20px;
            }

            .safety {
                margin: 0 16px 20px;
            }

            .safety td {
                display: block;
                width: 100%;
                padding: 0 0 12px !important;
            }

            .footer {
                padding: 22px 22px 28px;
            }
        }
    </style>
</head>

<body style="background-color: #ede8dc; margin: 0; padding: 0;">

    <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation"
        style="background-color: #ede8dc;">
        <tr>
            <td align="center">
                <div class="wrapper" style="text-align: left;">
                    <div class="card">

                        <!-- LOGO -->
                        <div class="logo-bar">
                            <img src="{{ $logoURL ?? asset('LinkUp Logo_1.png') }}" alt="LinkUp">
                            <p class="logo-tagline">
                                Linking <strong style="color:#126fc9;">Caribbean</strong> &amp;
                                Latin American People <strong style="color:#b07e10;">Everywhere.</strong>
                            </p>
                            <div
                                style="margin-top:6px; font-size:12px; font-weight:700; color:#667085; letter-spacing:.2px;">
                                {{ $country_flag ?? '' }} {{ $country_name ?? '' }}
                            </div>
                        </div>

                        <!-- INTRO -->
                        <div class="intro">
                            <div class="intro-eyebrow">💬 New Message</div>
                            <h1>Hi {{ $recipient_name ?? 'User' }}! 👋</h1>
                            <div class="gold-rule"></div>
                            <p>You have a new message waiting for you. Don't keep them waiting — tap below to reply.</p>
                        </div>

                        <!-- MESSAGE BLOCK -->
                        <div class="message-wrap">

                            <!-- Sender card -->
                            <div class="sender-card">
                                <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                    <tr>
                                        <td width="60" valign="middle" style="padding-right: 16px;">
                                            <div class="sender-avatar-placeholder">
                                                <img class="sender-avatar" src="{{ $sender_avatar }}"
                                                    alt="Sender Avatar">
                                            </div>
                                        </td>
                                        <td valign="middle" class="sender-info">
                                            <div class="sender-name">{{ $sender_name ?? 'User' }}</div>
                                            <div class="sender-meta">
                                                <span class="online-dot"></span>
                                                Active recently &nbsp;·&nbsp; {{ $sender_country_flag ?? '' }}
                                                {{ $sender_country ?? '' }}
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Message bubble -->
                            <div class="message-bubble">
                                <div class="bubble-label">Their message</div>
                                @if (isset($message_type) && $message_type === 'image')
                                    <p class="bubble-text">📷 Sent an image</p>
                                    @php $meta = is_string($message_meta) ? json_decode($message_meta, true) : $message_meta; @endphp
                                    @if (isset($meta['url']))
                                        <img src="{{ $meta['url'] }}" alt="Image"
                                            style="max-width:100%; border-radius:8px; margin-top:10px;">
                                    @endif
                                @elseif(isset($message_type) && $message_type === 'pdf')
                                    <p class="bubble-text">📄 Sent a PDF file</p>
                                    @php $meta = is_string($message_meta) ? json_decode($message_meta, true) : $message_meta; @endphp
                                    @if (isset($meta['url']))
                                        <a href="{{ $meta['url'] }}"
                                            style="display:inline-block; margin-top:10px; color:#126fc9; font-weight:bold; text-decoration:none;">View
                                            PDF</a>
                                    @endif
                                @elseif(isset($message_type) && $message_type === 'gif')
                                    <p class="bubble-text">🎞️ Sent a GIF</p>
                                    @php $meta = is_string($message_meta) ? json_decode($message_meta, true) : $message_meta; @endphp
                                    @if (isset($meta['url']))
                                        <img src="{{ $meta['url'] }}" alt="GIF"
                                            style="max-width:100%; border-radius:8px; margin-top:10px;">
                                    @endif
                                @elseif(isset($message_type) && $message_type === 'ticket')
                                    <p class="bubble-text">🎟️ Sent a ticket</p>
                                    @php $meta = is_string($message_meta) ? json_decode($message_meta, true) : $message_meta; @endphp
                                    @if (isset($meta['ticket_name']))
                                        <div
                                            style="margin-top:10px; padding:10px; border:1px solid #e0d5be; border-radius:8px; background:#f9f8f5;">
                                            <strong>{{ $meta['ticket_name'] }}</strong>
                                        </div>
                                    @endif
                                @else
                                    <p class="bubble-text">{{ $message_content ?? '-' }}</p>
                                @endif
                                <div class="bubble-time">{{ $message_time ?? 'Just now' }}</div>
                            </div>

                            <!-- CTA -->
                            <div class="cta-wrap">
                                <a href="{{ $chat_url ?? '#' }}" class="open-chat-btn">Open Chat →</a>
                            </div>
                            <p class="reply-note">If you don't recognize this activity, you can safely ignore this
                                email.</p>

                        </div>

                        <!-- SAFETY TIP -->
                        <div class="safety-tip">
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td width="24" valign="top" style="padding-right: 12px;">
                                        <div class="safety-tip-icon">🛡️</div>
                                    </td>
                                    <td valign="top">
                                        <div class="safety-tip-text">
                                            <strong>Stay safe on LinkUp.</strong> Never share personal financial
                                            details, passwords,
                                            or your home address in chat. If something feels off, use the in-app report
                                            button.
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- SPONSOR -->
                        @if (!empty($ad))
                            @include('emails.partials.sponsor', ['ad' => $ad])
                        @endif

                        <!-- SAFETY -->
                        <div class="safety">
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td width="33%" valign="top" style="padding-right: 6px;">
                                        <div class="safety-card">
                                            <span class="safety-icon">🔒</span>
                                            <span class="safety-title">End-to-end secure</span>
                                            <div class="safety-desc">Your conversations are encrypted and private.</div>
                                        </div>
                                    </td>
                                    <td width="33%" valign="top" style="padding: 0 6px;">
                                        <div class="safety-card">
                                            <span class="safety-icon">🎧</span>
                                            <span class="safety-title">Need help?</span>
                                            <div class="safety-desc">
                                                We're always here.<br>
                                                <a href="mailto:{{ $support_email ?? 'support@linkup.com' }}">Contact
                                                    Support</a>
                                            </div>
                                        </div>
                                    </td>
                                    <td width="33%" valign="top" style="padding-left: 6px;">
                                        <div class="safety-card">
                                            <span class="safety-icon">🚩</span>
                                            <span class="safety-title">Report & block</span>
                                            <div class="safety-desc">Feel unsafe? Report directly inside the app.</div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- FOOTER -->
                        <div class="footer">

                            <img src="{{ $logoURL ?? asset('LinkUp Logo_1.png') }}" alt="LinkUp">

                            <div class="footer-tagline">
                                © 2026 LinkUp {{ $country_flag ?? '' }} {{ $country_name ?? '' }}<br>
                                Linking <strong style="color:#126fc9;">Caribbean</strong> &amp;
                                Latin American People <strong style="color:#b07e10;">Everywhere.</strong>
                            </div>

                            <div class="footer-links">
                                <a href="#">Manage Preferences</a>
                                <a href="mailto:{{ $support_email ?? 'support@linkup.com' }}">Contact Support</a>
                                <a href="#">Privacy Policy</a>
                                <a href="#">Terms of Service</a>
                                <a href="#">Unsubscribe</a>
                            </div>

                            <div class="footer-social">
                                <a href="#" class="soc" style="background:#e1306c;"
                                    title="Instagram">IG</a>
                                <a href="#" class="soc" style="background:#1877f2;" title="Facebook">f</a>
                                <a href="#" class="soc" style="background:#000;" title="TikTok">♪</a>
                            </div>

                            <div class="footer-copy">Follow us @linkupcaribbean</div>

                        </div>

                    </div>
                </div>
            </td>
        </tr>
    </table>

</body>

</html>
