<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8" />

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>LinkUp Money Received Email</title>

    <style>
        @keyframes successPulse {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 rgba(212, 175, 55, 0);
            }

            50% {
                transform: scale(1.08);
                box-shadow: 0 0 22px rgba(212, 175, 55, 0.45);
            }

        }



        @keyframes moneyGlow {

            0%,
            100% {
                text-shadow: 0 4px 18px rgba(63, 180, 40, 0.22);
            }

            50% {
                text-shadow: 0 8px 28px rgba(212, 175, 55, 0.5);
            }

        }



        .success-pulse {

            animation: successPulse 1.7s ease-in-out infinite;

            transform-origin: center;

        }



        .money-glow {

            animation: moneyGlow 2s ease-in-out infinite;

        }



        @media (prefers-reduced-motion: reduce) {

            .success-pulse,
            .money-glow {
                animation: none !important;
            }

        }



        @media only screen and (max-width: 640px) {

            .email-shell {
                width: 100% !important;
                border-radius: 0 !important;
            }

            .section-padding {
                padding-left: 22px !important;
                padding-right: 22px !important;
            }

            .stack-column {
                display: block !important;
                width: 100% !important;
                border-right: none !important;
                border-left: none !important;
            }

            .center-mobile {
                text-align: center !important;
            }

            .amount-text {
                font-size: 58px !important;
            }

            .wallet-img {
                width: 210px !important;
                margin-top: 22px !important;
            }

            .action-card {
                display: block !important;
                width: 100% !important;
                margin-bottom: 12px !important;
            }

        }
    </style>

</head>



<body style="margin:0; padding:0; background:#f7f4ed; font-family:Arial, Helvetica, sans-serif; color:#101828;">

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#f7f4ed; padding:28px 12px;">

        <tr>

            <td align="center">

                <table class="email-shell" width="100%" cellpadding="0" cellspacing="0" role="presentation"
                    style="max-width:820px; background:#fffdf8; border-radius:24px; overflow:hidden; box-shadow:0 14px 38px rgba(56,42,15,0.12); border:1px solid #eee3c7;">



                    <!-- Top Brand Area -->

                    <tr>

                        <td align="center" class="section-padding"
                            style="padding:34px 34px 10px; background:linear-gradient(180deg,#fffdf8 0%,#fffaf0 100%); position:relative;">

                            <img src="{{ $logoUrl }}" alt="LinkUp Logo"
                                style="max-width:235px; width:100%; height:auto; display:block; margin:0 auto 10px;" />

                            <div class="success-pulse"
                                style="width:78px; height:78px; border-radius:50%; border:4px solid #d4af37; background:#ffffff; color:#2ea52d; font-size:42px; line-height:78px; text-align:center; margin:14px auto 14px; box-shadow:0 8px 22px rgba(212,175,55,0.22);">
                                ✓</div>

                            <h1 style="margin:0; font-size:44px; line-height:1.1; font-weight:900; color:#101828;">

                                Money <span style="color:#2ea52d;">Received!</span>

                            </h1>

                            <p style="margin:12px 0 0; font-size:18px; line-height:1.5; color:#344054;">

                                Great news, <strong style="color:#b88900;">{{ $recipient->first_name }}</strong>! You’ve
                                received money on
                                <strong style="color:#126fc9;">LinkUp</strong>.

                            </p>

                        </td>

                    </tr>



                    <!-- Hero Section -->

                    <tr>

                        <td class="section-padding" style="padding:22px 40px 18px; background:#fffdf8;">

                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">

                                <tr>

                                    <td class="stack-column center-mobile" width="55%" valign="middle"
                                        style="padding-right:20px;">

                                        <p style="margin:0 0 8px; font-size:22px; font-weight:800; color:#101828;">Hi
                                            {{ $recipient->first_name }} {{ $recipient->last_name }},</p>

                                        <p style="margin:0 0 32px; font-size:20px; color:#475467;">You’ve received
                                            money!</p>



                                        <div
                                            style="font-size:16px; letter-spacing:3px; font-weight:900; color:#2ea52d; margin-bottom:6px;">
                                            AMOUNT RECEIVED</div>

                                        <div class="money-glow amount-text"
                                            style="font-size:82px; line-height:1; font-weight:900; color:#3eb62c; margin:0;">

                                            {{ number_format($amount, 2) }}
                                        </div>

                                    </td>

                                </tr>

                            </table>

                        </td>

                    </tr>



                    <!-- Transaction Details Card -->

                    <tr>

                        <td class="section-padding" style="padding:18px 40px 16px;">

                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
                                style="background:#ffffff; border-radius:22px; border:1px solid #eadfbd; box-shadow:0 10px 25px rgba(56,42,15,0.08); overflow:hidden;">

                                <tr>

                                    <td class="stack-column" width="58%" valign="top"
                                        style="padding:26px 24px; border-right:1px solid #eee3c7;">

                                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">

                                            <tr>

                                                <td width="84" valign="top">

                                                    <div
                                                        style="width:70px; height:70px; border-radius:50%; background:linear-gradient(135deg,#d4af37,#ffffff); padding:3px;">

                                                        <img src="{{ $sender->avatar }}" width="70" height="70"
                                                            alt="Sender profile photo"
                                                            style="display:block; width:70px; height:70px; border-radius:50%; object-fit:cover;" />

                                                    </div>

                                                </td>

                                                <td valign="top">

                                                    <h2 style="margin:0 0 16px; font-size:22px; color:#101828;">
                                                        Transaction Details</h2>

                                                    <p style="margin:0 0 12px; font-size:16px; color:#101828;"><span
                                                            style="color:#b88900; font-weight:800;">👤 From:</span>
                                                        &nbsp; {{ $sender->name }} {{ $sender->linkup_id ?? '-' }}</p>

                                                    <p style="margin:0 0 12px; font-size:16px; color:#101828;"><span
                                                            style="color:#b88900; font-weight:800;">💬 Note:</span>
                                                        &nbsp; {{ $note }}</p>

                                                    <p style="margin:0 0 12px; font-size:16px; color:#101828;"><span
                                                            style="color:#b88900; font-weight:800;">📅 Date:</span>
                                                        &nbsp; {{ $date }} at {{ $time }}</p>

                                                    <p style="margin:0; font-size:16px; color:#101828;"><span
                                                            style="color:#b88900; font-weight:800;">🏷️ Transaction
                                                            ID:</span> &nbsp; LU-{{ $transaction_id ?? '-' }}</p>

                                                </td>

                                            </tr>

                                        </table>

                                    </td>



                                    <td class="stack-column" width="42%" valign="top" style="padding:26px 24px;">

                                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">

                                            <tr>

                                                <td width="52" valign="top">

                                                    <div
                                                        style="width:42px; height:42px; border-radius:50%; background:#fff7df; border:1px solid #d4af37; color:#d4af37; line-height:42px; text-align:center; font-size:24px;">
                                                        🛡</div>

                                                </td>

                                                <td valign="top">

                                                    <strong
                                                        style="display:block; color:#b88900; font-size:18px; margin-bottom:6px;">Secure
                                                        & Verified</strong>

                                                    <span style="font-size:15px; color:#475467; line-height:1.45;">This
                                                        transaction has been secured and verified.</span>

                                                </td>

                                            </tr>

                                        </table>



                                        <div style="height:1px; background:#eee3c7; margin:22px 0;"></div>



                                        <div style="font-size:16px; font-weight:900; color:#2ea52d; margin-bottom:8px;">
                                            NEW WALLET BALANCE</div>

                                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">

                                            <tr>

                                                <td valign="middle"
                                                    style="font-size:34px; font-weight:900; color:#101828;">
                                                    ${{ number_format($walletBalanceAfter, 2) }}
                                                </td>

                                                <td align="right" valign="middle">

                                                    <div
                                                        style="width:54px; height:54px; border-radius:50%; background:#f6fbf3; border:1px solid #d8ead0; color:#2ea52d; line-height:54px; text-align:center; font-size:26px;">
                                                        💳</div>

                                                </td>

                                            </tr>

                                        </table>

                                    </td>

                                </tr>

                            </table>

                        </td>

                    </tr>



                    <!-- Message + Actions -->

                    <tr>

                        <td class="section-padding" style="padding:18px 40px 18px;">

                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">

                                <tr>

                                    <td class="stack-column" width="50%" valign="top"
                                        style="padding:8px 22px 8px 0; border-right:1px solid #eee3c7;">

                                        <p style="margin:0 0 16px; font-size:17px; line-height:1.55; color:#101828;">
                                            <span style="color:#2ea52d; font-weight:900;">✓</span> &nbsp; The funds
                                            have been added to your wallet and are now available for use.
                                        </p>

                                        <p style="margin:0 0 22px; font-size:16px; line-height:1.55; color:#667085;">
                                            You can view your transaction history in your wallet dashboard.</p>

                                        <a href="{{ route('frontend.user.wallet') }}"
                                            style="display:inline-block; background:linear-gradient(135deg,#d4af37,#f1d771); color:#ffffff; text-decoration:none; padding:16px 54px; border-radius:12px; font-size:20px; font-weight:900; box-shadow:0 8px 20px rgba(212,175,55,0.28);">View
                                            Wallet →</a>

                                    </td>



                                    <td class="stack-column center-mobile" width="50%" valign="top"
                                        align="center" style="padding:8px 0 8px 22px;">

                                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">

                                            <tr>

                                                <td class="action-card" width="33.33%" align="center"
                                                    style="padding:0 7px;">

                                                    <a href="#"
                                                        style="display:block; text-decoration:none; color:#101828; background:#ffffff; border:1px solid #dcefd7; border-radius:16px; padding:18px 8px; box-shadow:0 8px 18px rgba(56,42,15,0.06);">

                                                        <div style="font-size:32px; margin-bottom:8px; color:#2ea52d;">
                                                            💳</div>

                                                        <strong style="font-size:16px;">View<br />Wallet</strong>

                                                    </a>

                                                </td>

                                                <td class="action-card" width="33.33%" align="center"
                                                    style="padding:0 7px;">

                                                    <a href="#"
                                                        style="display:block; text-decoration:none; color:#101828; background:#ffffff; border:1px solid #dbeaf8; border-radius:16px; padding:18px 8px; box-shadow:0 8px 18px rgba(56,42,15,0.06);">

                                                        <div style="font-size:32px; margin-bottom:8px; color:#126fc9;">
                                                            ✈</div>

                                                        <strong style="font-size:16px;">Send<br />Money</strong>

                                                    </a>

                                                </td>

                                                <td class="action-card" width="33.33%" align="center"
                                                    style="padding:0 7px;">

                                                    <a href="#"
                                                        style="display:block; text-decoration:none; color:#101828; background:#ffffff; border:1px solid #ead69c; border-radius:16px; padding:18px 8px; box-shadow:0 8px 18px rgba(56,42,15,0.06);">

                                                        <div style="font-size:32px; margin-bottom:8px; color:#d4af37;">
                                                            🏦</div>

                                                        <strong style="font-size:16px;">Cash<br />Out</strong>

                                                    </a>

                                                </td>

                                            </tr>

                                        </table>

                                    </td>

                                </tr>

                            </table>

                        </td>

                    </tr>



                    <!-- Sponsor Section -->
                    <tr>
                        <td class="section-padding" style="padding:12px 40px 22px;">
                            @include('emails.partials.sponsor', ['ad' => $ad])
                        </td>
                    </tr>



                    <!-- Safety / Support Row -->

                    <tr>

                        <td class="section-padding" style="padding:4px 40px 24px;">

                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">

                                <tr>

                                    <td class="stack-column" width="33.33%"
                                        style="padding:16px 18px; border-right:1px solid #eee3c7;">

                                        <div
                                            style="width:50px; height:50px; border-radius:50%; background:#f6fbf3; border:1px solid #d8ead0; color:#2ea52d; line-height:50px; text-align:center; font-size:26px; margin-bottom:10px;">
                                            🔒</div>

                                        <strong
                                            style="display:block; font-size:16px; color:#101828; margin-bottom:4px;">Your
                                            security is our priority.</strong>

                                        <span style="font-size:14px; color:#667085; line-height:1.45;">We use
                                            bank-level encryption to protect your money.</span>

                                    </td>

                                    <td class="stack-column" width="33.33%"
                                        style="padding:16px 18px; border-right:1px solid #eee3c7;">

                                        <div
                                            style="width:50px; height:50px; border-radius:50%; background:#f0f8ff; border:1px solid #dbeaf8; color:#126fc9; line-height:50px; text-align:center; font-size:26px; margin-bottom:10px;">
                                            🎧</div>

                                        <strong
                                            style="display:block; font-size:16px; color:#101828; margin-bottom:4px;">Need
                                            help?</strong>

                                        <span style="font-size:14px; color:#667085; line-height:1.45;">Our support team
                                            is here for you.<br /><a href="mailto:support@linkup.com"
                                                style="color:#126fc9; text-decoration:none; font-weight:700;">support@linkup.com</a></span>

                                    </td>

                                    <td class="stack-column" width="33.33%" style="padding:16px 18px;">

                                        <div
                                            style="width:50px; height:50px; border-radius:50%; background:#fff8e6; border:1px solid #ead69c; color:#d4af37; line-height:50px; text-align:center; font-size:26px; margin-bottom:10px;">
                                            🛡</div>

                                        <strong
                                            style="display:block; font-size:16px; color:#101828; margin-bottom:4px;">Don’t
                                            recognize this transaction?</strong>

                                        <span style="font-size:14px; color:#667085; line-height:1.45;">Secure your
                                            account and contact us immediately.</span>

                                    </td>

                                </tr>

                            </table>

                        </td>

                    </tr>



                    <!-- Footer -->

                    <tr>

                        <td class="section-padding"
                            style="padding:24px 40px 28px; background:#fffaf0; border-top:1px solid #eee3c7;">

                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">

                                <tr>

                                    <td class="stack-column center-mobile" width="30%" valign="middle"
                                        style="padding-bottom:12px;">

                                        <img src="{{ $logoUrl }}" alt="LinkUp Logo"
                                            style="max-width:150px; width:100%; height:auto; display:block;" />

                                    </td>

                                    <td class="stack-column center-mobile" width="45%" valign="middle"
                                        style="font-size:13px; color:#667085; line-height:1.6; padding-bottom:12px;">

                                        © 2026 LinkUp. All rights reserved.<br />

                                        Nassau, Bahamas<br />

                                        Linking Caribbean & Latin American People <span
                                            style="color:#2ea52d; font-weight:800;">Everywhere.</span>

                                    </td>

                                    <td class="stack-column center-mobile" width="25%" valign="middle"
                                        align="right" style="padding-bottom:12px;">

                                        <div style="font-size:13px; color:#667085; margin-bottom:8px;">Follow us
                                            @linkupcaribbean</div>

                                        <a href="#"
                                            style="display:inline-block; width:34px; height:34px; border-radius:50%; background:#126fc9; color:#fff; text-decoration:none; line-height:34px; text-align:center; font-weight:900; margin-left:5px;">IG</a>

                                        <a href="#"
                                            style="display:inline-block; width:34px; height:34px; border-radius:50%; background:#126fc9; color:#fff; text-decoration:none; line-height:34px; text-align:center; font-weight:900; margin-left:5px;">f</a>

                                        <a href="#"
                                            style="display:inline-block; width:34px; height:34px; border-radius:50%; background:#9ad223; color:#101828; text-decoration:none; line-height:34px; text-align:center; font-weight:900; margin-left:5px;">♪</a>

                                    </td>

                                </tr>

                            </table>



                            <div style="height:1px; background:#eadfbd; margin:12px 0 18px;"></div>



                            <p
                                style="margin:0 0 10px; text-align:center; font-size:13px; color:#667085; line-height:1.6;">

                                This is an automated message from LinkUp. Please do not reply to this email.

                            </p>

                            <p
                                style="margin:0 0 10px; text-align:center; font-size:13px; color:#667085; line-height:1.6;">

                                Need help? Contact our support team at <a href="mailto:support@linkup.com"
                                    style="color:#126fc9; text-decoration:none; font-weight:700;">support@linkup.com</a>.

                            </p>

                            <p
                                style="margin:0 0 14px; text-align:center; font-size:12px; color:#98a2b3; line-height:1.6; max-width:650px; margin-left:auto; margin-right:auto;">

                                If you did not expect this transaction, secure your account immediately and contact
                                LinkUp support. Sponsored partnerships help us continue connecting Caribbean and Latin
                                American people worldwide.

                            </p>

                            <p style="margin:0; text-align:center; font-size:12px; color:#667085; line-height:1.6;">

                                <a href="#" style="color:#126fc9; text-decoration:none;">Help Center</a>

                                &nbsp; | &nbsp;

                                <a href="#" style="color:#126fc9; text-decoration:none;">Manage
                                    Notifications</a>

                                &nbsp; | &nbsp;

                                <a href="#" style="color:#126fc9; text-decoration:none;">Unsubscribe</a>

                                &nbsp; | &nbsp;

                                <a href="#" style="color:#126fc9; text-decoration:none;">Privacy Policy</a>

                                &nbsp; | &nbsp;

                                <a href="#" style="color:#126fc9; text-decoration:none;">Terms of Service</a>

                            </p>

                        </td>

                    </tr>



                </table>

            </td>

        </tr>

    </table>

</body>

</html>
