<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>It's a Match — LinkUp</title>
    <style>
        /* Reset */
        body,
        table,
        td,
        a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table,
        td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
            background-color: #ede8dc;
        }

        a {
            color: #126fc9;
        }

        /* Pill styles fallback */
        .pill {
            background: #f5f3ff;
            border: 1px solid #ddd6fe;
            color: #5b21b6;
            font-size: 12px;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 100px;
            display: inline-block;
            white-space: nowrap;
            font-family: -apple-system, 'Segoe UI', Arial, sans-serif;
        }
    </style>
</head>

<body style="margin:0;padding:0;background-color:#ede8dc;">

    <!-- Outer wrapper -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%"
        style="background-color:#ede8dc;">
        <tr>
            <td align="center" style="padding:32px 16px 52px;">

                <!-- Card -->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="600"
                    style="max-width:600px;background-color:#fffdf8;border-radius:28px;overflow:hidden;box-shadow:0 24px 64px rgba(56,42,15,.16),0 0 0 1px #e0d5be;">

                    <!-- LOGO BAR -->
                    <tr>
                        <td align="center" style="padding:30px 36px 22px;border-bottom:1px solid #f0e8d4;">
                            <img src="LinkUp Logo_1.png" alt="LinkUp" height="36"
                                style="display:block;margin:0 auto;">
                            <p
                                style="margin:8px 0 0;font-size:13px;color:#667085;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">
                                Linking <strong style="color:#126fc9;">Caribbean</strong> &amp; Latin American People
                                <strong style="color:#b07e10;">Everywhere.</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- HERO -->
                    <tr>
                        <td
                            style="background:linear-gradient(160deg,#6b21a8 0%,#7c3aed 50%,#a855f7 100%);padding:44px 40px 36px;text-align:center;">

                            <!-- Eyebrow -->
                            <p
                                style="margin:0 0 14px;font-size:11px;font-weight:800;letter-spacing:2.5px;text-transform:uppercase;color:rgba(255,255,255,.65);font-family:-apple-system,'Segoe UI',Arial,sans-serif;">
                                💜 You both liked each other</p>

                            <!-- Headline -->
                            <h1
                                style="margin:0 0 8px;font-size:48px;font-weight:900;color:#fff;line-height:1.05;letter-spacing:-1.5px;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">
                                It's a Match!</h1>

                            <!-- Sub -->
                            <p
                                style="margin:0 0 28px;font-size:16px;color:rgba(255,255,255,.78);line-height:1.5;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">
                                You and {{ $matched_user_name->name }} are now connected on LinkUp.</p>

                            <!-- Avatar pair using table -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" align="center">
                                <tr>
                                    <!-- You avatar -->
                                    <td align="center" style="vertical-align:top;">
                                        <div
                                            style="margin-align:center;width:96px;height:96px;border-radius:50%;border:4px solid rgba(255,255,255,.35);background:linear-gradient(135deg,#7c3aed,#a78bfa);display:inline-flex;align-items:center;justify-content:center;font-size:36px;font-weight:900;color:#fff;box-shadow:0 8px 28px rgba(0,0,0,.3);font-family:-apple-system,'Segoe UI',Arial,sans-serif;line-height:96px;text-align:center;width:96px;height:96px;">
                                            {{ strtoupper(substr($current_user_name->name, 0, 1)) }}
                                        </div>
                                        <br>
                                        <span
                                            style="font-size:13px;font-weight:800;color:rgba(255,255,255,.9);font-family:-apple-system,'Segoe UI',Arial,sans-serif;">You</span>
                                    </td>

                                    <!-- Heart -->
                                    <td align="center" valign="middle"
                                        style="padding:0 4px;font-size:32px;padding-bottom:28px;">💜</td>

                                    <!-- Larysa avatar -->
                                    <td align="center" style="vertical-align:top;">
                                        <div
                                            style="margin-align:center; width:96px;height:96px;border-radius:50%;border:4px solid rgba(255,255,255,.35);background:linear-gradient(135deg,#b45309,#f59e0b);display:inline-flex;align-items:center;justify-content:center;font-size:36px;font-weight:900;color:#fff;box-shadow:0 8px 28px rgba(0,0,0,.3);font-family:-apple-system,'Segoe UI',Arial,sans-serif;line-height:96px;text-align:center;width:96px;height:96px;">
                                            {{ strtoupper(substr($matched_user_name->name, 0, 1)) }}
                                        </div>
                                        <br>
                                        <span
                                            style="font-size:13px;font-weight:800;color:rgba(255,255,255,.9);font-family:-apple-system,'Segoe UI',Arial,sans-serif;">{{ $matched_user_name->name }}</span>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- INTRO -->
                    <tr>
                        <td style="padding:36px 40px 20px;">
                            <div
                                style="width:48px;height:4px;background:linear-gradient(90deg,#d4af37,#f5d060);border-radius:4px;margin-bottom:18px;">
                            </div>
                            <p
                                style="margin:0;font-size:17px;line-height:1.7;color:#344054;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">
                                Hi {{ $current_user_name->name }}! Great news — you and {{ $matched_user_name->name }}
                                both liked each other. Don't wait too
                                long — say hi and start the conversation now.</p>
                        </td>
                    </tr>

                    <!-- PROFILE CARD -->
                    <tr>
                        <td style="padding:0 32px 20px;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%"
                                style="background:#fff;border:1px solid #ede8dc;border-radius:22px;overflow:hidden;box-shadow:0 8px 28px rgba(16,24,40,.09);">
                                <tr>
                                    <!-- Photo col -->
                                    <td width="180" style="vertical-align:top;position:relative;">
                                        <img src="{{ $matched_user_name->avatar }}" alt="{{ $matched_user_name->name }}"
                                            width="180"
                                            style="display:block;width:180px;min-height:280px;object-fit:cover;border-radius:22px 0 0 22px;">
                                        <!-- Badge -->
                                    </td>
                                    <!-- Info col -->
                                    <td style="padding:22px 20px;vertical-align:top;">
                                        <p
                                            style="margin:0 0 4px;font-size:24px;font-weight:900;color:#101828;letter-spacing:-.3px;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">
                                            {{ $matched_user_name->name }}, {{ $matched_user_name->age }}</p>
                                        <p
                                            style="margin:0 0 18px;font-size:13px;color:#667085;font-weight:600;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">
                                            {{ $country_flag }} {{ $matched_user_name->country }}</p>

                                        <p
                                            style="margin:0 0 9px;font-size:10px;font-weight:800;letter-spacing:2px;text-transform:uppercase;color:#7c3aed;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">
                                            ♡ Mutual Interests</p>

                                        <!-- Pills using inline-block spans -->
                                        @if (!empty($matched_user_name->interests))
                                            <table cellpadding="0" cellspacing="0" role="presentation" width="100%"
                                                style="background:#f8fff8; border:1px solid #dbeadb; border-radius:14px; margin:0 auto 28px; border-collapse: collapse;">

                                                @foreach (collect($matched_user_name->interests)->chunk(5) as $chunk)
                                                    <tr>
                                                        @foreach ($chunk as $interest)
                                                            <td
                                                                style="padding:14px 18px; font-size:15px; color:#101828; border:1px solid #dbeadb; text-align: center;">
                                                                {{ $interest }}
                                                            </td>
                                                        @endforeach

                                                        @if ($chunk->count() < 5)
                                                            @for ($i = 0; $i < 5 - $chunk->count(); $i++)
                                                                <td style="border:1px solid #dbeadb;">&nbsp;</td>
                                                            @endfor
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            </table>
                                        @endif

                                        <a href="{{ route('frontend.user.start.chat', ['userId' => $matched_user_name->id]) }}"
                                            style="display:inline-block;background:linear-gradient(135deg,#6b21a8,#7c3aed);color:#fff;text-decoration:none;padding:12px 24px;border-radius:11px;font-size:14px;font-weight:800;box-shadow:0 6px 18px rgba(124,58,237,.30);font-family:-apple-system,'Segoe UI',Arial,sans-serif;">👋
                                            Say Hi →</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- WHAT TO DO NEXT -->
                    <tr>
                        <td style="padding:0 32px 28px;">
                            <p
                                style="margin:0 0 12px;font-size:10px;font-weight:800;letter-spacing:2.5px;text-transform:uppercase;color:#9ca3af;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">
                                What to do next</p>
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <!-- Tip 1 -->
                                    <td width="33%" style="padding-right:5px;">
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0"
                                            width="100%"
                                            style="background:#f9f8f5;border:1px solid #ede8dc;border-radius:16px;">
                                            <tr>
                                                <td align="center" style="padding:16px 14px;">
                                                    <span
                                                        style="font-size:24px;display:block;margin-bottom:7px;">💬</span>
                                                    <span
                                                        style="font-size:12px;font-weight:800;color:#101828;display:block;margin-bottom:4px;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">Start
                                                        a chat</span>
                                                    <span
                                                        style="font-size:11px;color:#667085;line-height:1.5;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">Send
                                                        the first message and break the ice.</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <!-- Tip 2 -->
                                    <td width="33%" style="padding:0 3px;">
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0"
                                            width="100%"
                                            style="background:#f9f8f5;border:1px solid #ede8dc;border-radius:16px;">
                                            <tr>
                                                <td align="center" style="padding:16px 14px;">
                                                    <span
                                                        style="font-size:24px;display:block;margin-bottom:7px;">👤</span>
                                                    <span
                                                        style="font-size:12px;font-weight:800;color:#101828;display:block;margin-bottom:4px;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">View
                                                        profile</span>
                                                    <span
                                                        style="font-size:11px;color:#667085;line-height:1.5;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">Learn
                                                        more about your match before you chat.</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <!-- Tip 3 -->
                                    <td width="33%" style="padding-left:5px;">
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0"
                                            width="100%"
                                            style="background:#f9f8f5;border:1px solid #ede8dc;border-radius:16px;">
                                            <tr>
                                                <td align="center" style="padding:16px 14px;">
                                                    <span
                                                        style="font-size:24px;display:block;margin-bottom:7px;">🎟️</span>
                                                    <span
                                                        style="font-size:12px;font-weight:800;color:#101828;display:block;margin-bottom:4px;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">Plan
                                                        something</span>
                                                    <span
                                                        style="font-size:11px;color:#667085;line-height:1.5;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">Browse
                                                        local events to attend together.</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- SPONSOR -->
                    <tr>
                        <td style="padding:0 24px 24px;">
                            @include('emails.partials.sponsor', ['ad' => $ad])

                        </td>
                    </tr>

                    <!-- SAFETY -->
                    <tr>
                        <td style="padding:0 24px 24px;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0"
                                width="100%">
                                <tr>
                                    <!-- Safety 1 -->
                                    <td width="33%" style="padding-right:6px;">
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0"
                                            width="100%"
                                            style="background:#f9fafb;border:1px solid #f0f0f0;border-radius:16px;">
                                            <tr>
                                                <td align="center" style="padding:18px 14px;">
                                                    <span
                                                        style="font-size:26px;display:block;margin-bottom:8px;">🛡️</span>
                                                    <span
                                                        style="font-size:13px;font-weight:800;color:#101828;display:block;margin-bottom:5px;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">Your
                                                        safety first</span>
                                                    <span
                                                        style="font-size:12px;color:#667085;line-height:1.55;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">Bank-level
                                                        encryption keeps your info safe.</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <!-- Safety 2 -->
                                    <td width="33%" style="padding:0 3px;">
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0"
                                            width="100%"
                                            style="background:#f9fafb;border:1px solid #f0f0f0;border-radius:16px;">
                                            <tr>
                                                <td align="center" style="padding:18px 14px;">
                                                    <span
                                                        style="font-size:26px;display:block;margin-bottom:8px;">🎧</span>
                                                    <span
                                                        style="font-size:13px;font-weight:800;color:#101828;display:block;margin-bottom:5px;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">Need
                                                        help?</span>
                                                    <span
                                                        style="font-size:12px;color:#667085;line-height:1.55;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">We're
                                                        always here.<br><a href="mailto:support@linkup.com"
                                                            style="color:#126fc9;font-weight:700;text-decoration:none;">Contact
                                                            Support</a></span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <!-- Safety 3 -->
                                    <td width="33%" style="padding-left:6px;">
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0"
                                            width="100%"
                                            style="background:#f9fafb;border:1px solid #f0f0f0;border-radius:16px;">
                                            <tr>
                                                <td align="center" style="padding:18px 14px;">
                                                    <span
                                                        style="font-size:26px;display:block;margin-bottom:8px;">🚩</span>
                                                    <span
                                                        style="font-size:13px;font-weight:800;color:#101828;display:block;margin-bottom:5px;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">Report
                                                        &amp; block</span>
                                                    <span
                                                        style="font-size:12px;color:#667085;line-height:1.55;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">Feel
                                                        unsafe? Report directly inside the app.</span>
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
                        <td style="border-top:1px solid #ede8d4;padding:26px 36px 34px;text-align:center;">
                            <img src="LinkUp Logo_1.png" alt="LinkUp" height="26"
                                style="display:block;margin:0 auto 14px;">

                            <p
                                style="margin:0 0 18px;font-size:13px;color:#667085;line-height:1.6;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">
                                © 2026 LinkUp 🇯🇲<br>
                                Linking <strong style="color:#126fc9;">Caribbean</strong> &amp; Latin American People
                                <strong style="color:#b07e10;">Everywhere.</strong>
                            </p>

                            <!-- Footer links -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0"
                                align="center" style="margin-bottom:18px;">
                                <tr>
                                    <td style="padding:0 7px;"><a href="#"
                                            style="font-size:12px;font-weight:600;color:#126fc9;text-decoration:none;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">Manage
                                            Preferences</a></td>
                                    <td style="padding:0 7px;"><a href="mailto:support@linkup.com"
                                            style="font-size:12px;font-weight:600;color:#126fc9;text-decoration:none;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">Contact
                                            Support</a></td>
                                    <td style="padding:0 7px;"><a href="#"
                                            style="font-size:12px;font-weight:600;color:#126fc9;text-decoration:none;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">Privacy
                                            Policy</a></td>
                                    <td style="padding:0 7px;"><a href="#"
                                            style="font-size:12px;font-weight:600;color:#126fc9;text-decoration:none;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">Terms
                                            of Service</a></td>
                                    <td style="padding:0 7px;"><a href="#"
                                            style="font-size:12px;font-weight:600;color:#126fc9;text-decoration:none;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">Unsubscribe</a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Social icons -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0"
                                align="center" style="margin-bottom:18px;">
                                <tr>
                                    <td style="padding:0 4px;">
                                        <a href="#"
                                            style="display:inline-block;width:34px;height:34px;background:#e1306c;border-radius:9px;text-align:center;line-height:34px;font-size:13px;font-weight:900;color:#fff;text-decoration:none;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">IG</a>
                                    </td>
                                    <td style="padding:0 4px;">
                                        <a href="#"
                                            style="display:inline-block;width:34px;height:34px;background:#1877f2;border-radius:9px;text-align:center;line-height:34px;font-size:13px;font-weight:900;color:#fff;text-decoration:none;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">f</a>
                                    </td>
                                    <td style="padding:0 4px;">
                                        <a href="#"
                                            style="display:inline-block;width:34px;height:34px;background:#000;border-radius:9px;text-align:center;line-height:34px;font-size:13px;font-weight:900;color:#fff;text-decoration:none;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">♪</a>
                                    </td>
                                </tr>
                            </table>

                            <p
                                style="margin:0;font-size:12px;color:#9ca3af;line-height:1.7;font-family:-apple-system,'Segoe UI',Arial,sans-serif;">
                                Follow us @linkupcaribbean</p>
                        </td>
                    </tr>

                </table>
                <!-- /Card -->

            </td>
        </tr>
    </table>
    <!-- /Outer wrapper -->

</body>

</html>
