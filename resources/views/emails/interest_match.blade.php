<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LinkUp Recommended Matches</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        @media only screen and (max-width: 560px) {
            .wrapper {
                padding: 0 !important;
            }

            .card {
                border-radius: 0 !important;
                box-shadow: none !important;
            }

            .intro {
                padding: 28px 22px 22px !important;
            }

            .intro-h1 {
                font-size: 26px !important;
            }

            .matches {
                padding: 0 16px 8px !important;
            }

            .match-card-td {
                display: block !important;
                width: 100% !important;
            }

            .photo-col {
                width: 100% !important;
                display: block !important;
            }

            .photo-col img {
                min-height: 240px !important;
                max-height: 280px !important;
            }

            .info-col {
                padding: 20px 18px !important;
                display: block !important;
                width: 100% !important;
            }

            .see-all {
                padding: 0 16px 20px !important;
            }

            .sponsor {
                margin: 0 16px 20px !important;
            }

            .sponsor-btn {
                width: 100% !important;
                text-align: center !important;
                display: block !important;
            }

            .safety {
                margin: 0 16px 20px !important;
            }

            .safety-card {
                display: block !important;
                width: 100% !important;
                margin-bottom: 12px !important;
            }

            .footer {
                padding: 22px 22px 28px !important;
            }
        }
    </style>
</head>

<body
    style="margin:0;padding:0;background-color:#ede8dc;font-family:-apple-system,'Segoe UI',Arial,sans-serif;-webkit-font-smoothing:antialiased;color:#101828;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%"
        style="background-color:#ede8dc;">
        <tr>
            <td align="center" style="padding:32px 16px 52px;" class="wrapper">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="640"
                    style="width:100%;max-width:640px;background-color:#fffdf8;border-radius:28px;overflow:hidden;box-shadow:0 24px 64px rgba(56,42,15,.16);"
                    class="card">

                    <!-- LOGO -->
                    <tr>
                        <td align="center"
                            style="padding:30px 36px 22px;text-align:center;border-bottom:1px solid #f0e8d4;">
                            <img src="{{ $logoUrl ?? asset('images/logo.png') }}" alt="LinkUp"
                                style="height:36px;display:block;margin:0 auto;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;">
                            <p style="margin:8px 0 0;font-size:13px;color:#667085;line-height:1.5;">
                                Linking <strong style="color:#126fc9;">Caribbean</strong> &amp; Latin American People
                                <strong style="color:#b07e10;">Everywhere.</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- INTRO -->
                    <tr>
                        <td style="padding:40px 40px 28px;" class="intro">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td
                                        style="font-size:11px;font-weight:800;letter-spacing:2.5px;text-transform:uppercase;color:#b07e10;padding-bottom:12px;">
                                        Your Recommended Matches</td>
                                </tr>
                                <tr>
                                    <td style="font-size:34px;font-weight:900;color:#101828;line-height:1.15;letter-spacing:-.5px;padding-bottom:16px;"
                                        class="intro-h1">Hi {{ $user->name ?? 'there' }}! 👋</td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom:18px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td
                                                    style="width:48px;height:4px;background:linear-gradient(90deg,#d4af37,#f5d060);border-radius:4px;font-size:0;line-height:0;">
                                                    &nbsp;</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-size:17px;line-height:1.7;color:#344054;">We've handpicked some
                                        ideal matches for you based on your interests and preferences. Here's who we
                                        think you'll connect with.</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- MATCHES -->
                    <tr>
                        <td style="padding:0 24px 12px;" class="matches">
                            @foreach ($userMatches as $match)
                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%"
                                    style="background-color:#ffffff;border:1px solid #ede8dc;border-radius:22px;overflow:hidden;margin-bottom:16px;box-shadow:0 4px 20px rgba(16,24,40,.07);"
                                    class="match-card">
                                    <tr>
                                        <!-- Photo Column -->
                                        <td width="220" style="width:220px;vertical-align:top;position:relative;"
                                            class="photo-col match-card-td">
                                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                                width="100%">
                                                <tr>
                                                    <td style="position:relative;">
                                                        <img src="{{ $match['user']->avatar ?? 'https://via.placeholder.com/320x380.png?text=' . urlencode($match['user']->name ?? 'User') }}"
                                                            alt="{{ $match['user']->name ?? 'User' }}"
                                                            style="width:100%;height:300px;object-fit:cover;display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;"
                                                            class="match-photo">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:0;">
                                                        <table role="presentation" cellpadding="0" cellspacing="0"
                                                            border="0"
                                                            style="margin-top:-36px;margin-left:12px;position:relative;">
                                                            <tr>
                                                                <td
                                                                    style="background-color:rgba(255,255,255,.96);border-radius:100px;padding:5px 11px;box-shadow:0 2px 10px rgba(0,0,0,.12);">
                                                                    <table role="presentation" cellpadding="0"
                                                                        cellspacing="0" border="0">
                                                                        <tr>
                                                                            <td style="padding-right:5px;">
                                                                                <table role="presentation"
                                                                                    cellpadding="0" cellspacing="0"
                                                                                    border="0">
                                                                                    <tr>
                                                                                        <td
                                                                                            style="width:8px;height:8px;border-radius:50%;background-color:#10b981;font-size:0;line-height:0;">
                                                                                            &nbsp;</td>
                                                                                    </tr>
                                                                                </table>
                                                                            </td>
                                                                            <td
                                                                                style="font-size:12px;font-weight:800;color:#065f46;">
                                                                                {{ $match['percentage'] ?? 0 }}% Match
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
                                        <!-- Info Column -->
                                        <td style="padding:24px 22px;vertical-align:top;"
                                            class="info-col match-card-td">
                                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                                width="100%">
                                                <tr>
                                                    <td
                                                        style="font-size:26px;font-weight:900;color:#101828;letter-spacing:-.3px;padding-bottom:5px;">
                                                        {{ $match['user']->name ?? 'User' }},
                                                        {{ $match['user']->age ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding-bottom:20px;">
                                                        <table role="presentation" cellpadding="0" cellspacing="0"
                                                            border="0">
                                                            <tr>
                                                                <td
                                                                    style="font-size:18px;line-height:1;padding-right:6px;">
                                                                    {{ $match['user']->country ?? '🌍' }}</td>
                                                                <td
                                                                    style="font-size:13px;color:#667085;font-weight:600;">
                                                                    {{ $match['user']->city ?? 'Unknown' }}</td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td
                                                        style="font-size:10px;font-weight:800;letter-spacing:2px;text-transform:uppercase;color:#0d9488;padding-bottom:9px;">
                                                        ♡ Mutual Interests</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding-bottom:22px;">
                                                        @foreach ($match['common_interests'] ?? [] as $interest)
                                                            <span
                                                                style="display:inline-block;background-color:#f0fdf9;border:1px solid #a7f3d0;color:#065f46;font-size:12px;font-weight:700;padding:5px 12px;border-radius:100px;white-space:nowrap;margin:0 6px 8px 0;">
                                                                {{ $interest }}
                                                            </span>
                                                        @endforeach
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <table role="presentation" cellpadding="0" cellspacing="0"
                                                            border="0">
                                                            <tr>
                                                                <td
                                                                    style="background:linear-gradient(135deg,#c9960c,#e8ad18);border-radius:11px;box-shadow:0 6px 18px rgba(201,150,12,.28);">
                                                                    <a href="{{ route('frontend.linkup.user.details', ['user' => $match['user']->id]) }}"
                                                                        style="display:inline-block;color:#ffffff;text-decoration:none;padding:12px 26px;border-radius:11px;font-size:14px;font-weight:800;letter-spacing:.2px;">View
                                                                        Profile →</a>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            @endforeach
                        </td>
                    </tr>

                    <!-- SEE ALL -->
                    <tr>
                        <td align="center" style="padding:4px 24px 24px;text-align:center;" class="see-all">
                            <a href="{{ route('frontend.user.matches') }}"
                                style="font-size:14px;font-weight:700;color:#126fc9;text-decoration:none;">See all your
                                matches →</a>
                        </td>
                    </tr>

                    <!-- SPONSOR -->
                    @if (!empty($ad))
                        @include('emails.partials.sponsor', ['ad' => $ad])
                    @endif

                    <!-- SAFETY -->
                    <tr>
                        <td style="padding:0 24px 24px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                width="100%">
                                <tr>
                                    <td width="33%" style="width:33%;padding-right:6px;" class="safety-card">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                            width="100%"
                                            style="background-color:#f9fafb;border:1px solid #f0f0f0;border-radius:16px;">
                                            <tr>
                                                <td align="center" style="padding:18px 14px;text-align:center;">
                                                    <span
                                                        style="font-size:26px;display:block;padding-bottom:8px;">🛡️</span>
                                                    <span
                                                        style="font-size:13px;font-weight:800;color:#101828;display:block;padding-bottom:5px;">Your
                                                        safety first</span>
                                                    <span
                                                        style="font-size:12px;color:#667085;line-height:1.55;">Bank-level
                                                        encryption keeps your information safe.</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td width="33%" style="width:33%;padding-left:6px;padding-right:6px;"
                                        class="safety-card">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                            width="100%"
                                            style="background-color:#f9fafb;border:1px solid #f0f0f0;border-radius:16px;">
                                            <tr>
                                                <td align="center" style="padding:18px 14px;text-align:center;">
                                                    <span
                                                        style="font-size:26px;display:block;padding-bottom:8px;">🎧</span>
                                                    <span
                                                        style="font-size:13px;font-weight:800;color:#101828;display:block;padding-bottom:5px;">Need
                                                        help?</span>
                                                    <span style="font-size:12px;color:#667085;line-height:1.55;">We're
                                                        always here.<br><a href="mailto:support@linkup.com"
                                                            style="color:#126fc9;font-weight:700;text-decoration:none;">support@linkup.com</a></span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td width="33%" style="width:33%;padding-left:6px;" class="safety-card">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                            width="100%"
                                            style="background-color:#f9fafb;border:1px solid #f0f0f0;border-radius:16px;">
                                            <tr>
                                                <td align="center" style="padding:18px 14px;text-align:center;">
                                                    <span
                                                        style="font-size:26px;display:block;padding-bottom:8px;">🔒</span>
                                                    <span
                                                        style="font-size:13px;font-weight:800;color:#101828;display:block;padding-bottom:5px;">Stay
                                                        in control</span>
                                                    <span style="font-size:12px;color:#667085;line-height:1.55;">You
                                                        decide who you connect with, always.</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td align="center"
                            style="padding:26px 36px 34px;text-align:center;border-top:1px solid #ede8d4;"
                            class="footer">
                            <img src="{{ $logoUrl ?? asset('images/logo.png') }}" alt="LinkUp"
                                style="height:26px;display:block;margin:0 auto 14px;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;">
                            <p style="margin:0 0 18px;font-size:13px;color:#667085;line-height:1.6;">
                                © 2026 LinkUp — Nassau, Bahamas<br>
                                Linking <strong style="color:#126fc9;">Caribbean</strong> &amp; Latin American People
                                <strong style="color:#b07e10;">Everywhere.</strong>
                            </p>
                            <p style="margin:0 0 18px;font-size:12px;font-weight:600;line-height:1.6;">
                                <a href="#" style="color:#126fc9;text-decoration:none;">Manage Preferences</a>
                                &nbsp;·&nbsp;
                                <a href="#" style="color:#126fc9;text-decoration:none;">Help Center</a>
                                &nbsp;·&nbsp;
                                <a href="#" style="color:#126fc9;text-decoration:none;">Privacy Policy</a>
                                &nbsp;·&nbsp;
                                <a href="#" style="color:#126fc9;text-decoration:none;">Terms of Service</a>
                                &nbsp;·&nbsp;
                                <a href="#" style="color:#126fc9;text-decoration:none;">Unsubscribe</a>
                            </p>
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                style="margin:0 auto 18px;">
                                <tr>
                                    <td
                                        style="width:34px;height:34px;border-radius:9px;background-color:#e1306c;text-align:center;vertical-align:middle;">
                                        <a href="#"
                                            style="font-size:13px;font-weight:900;color:#ffffff;text-decoration:none;display:block;line-height:34px;">IG</a>
                                    </td>
                                    <td width="8" style="width:8px;font-size:0;line-height:0;">&nbsp;</td>
                                    <td
                                        style="width:34px;height:34px;border-radius:9px;background-color:#1877f2;text-align:center;vertical-align:middle;">
                                        <a href="#"
                                            style="font-size:13px;font-weight:900;color:#ffffff;text-decoration:none;display:block;line-height:34px;">f</a>
                                    </td>
                                    <td width="8" style="width:8px;font-size:0;line-height:0;">&nbsp;</td>
                                    <td
                                        style="width:34px;height:34px;border-radius:9px;background-color:#000000;text-align:center;vertical-align:middle;">
                                        <a href="#"
                                            style="font-size:13px;font-weight:900;color:#ffffff;text-decoration:none;display:block;line-height:34px;">♪</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:0;font-size:12px;color:#9ca3af;line-height:1.7;">Follow us
                                @linkupcaribbean</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>
