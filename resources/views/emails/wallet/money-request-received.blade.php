<!DOCTYPE html>

<html lang="en">

<head>

  <meta charset="UTF-8" />

  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>LinkUp Money Request Received</title>

  <style>

    @keyframes alertPulse {

      0%, 100% { transform: scale(1); box-shadow:0 0 0 rgba(217,143,0,0); }

      50% { transform: scale(1.07); box-shadow:0 0 22px rgba(217,143,0,0.32); }

    }



    @keyframes goldGlow {

      0%, 100% { text-shadow:0 4px 16px rgba(217,143,0,0.22); }

      50% { text-shadow:0 8px 26px rgba(217,143,0,0.46); }

    }



    .alert-pulse {

      animation: alertPulse 1.8s ease-in-out infinite;

      transform-origin:center;

    }



    .gold-glow {

      animation: goldGlow 2s ease-in-out infinite;

    }



    @media (prefers-reduced-motion: reduce) {

      .alert-pulse, .gold-glow { animation:none !important; }

    }



    @media only screen and (max-width: 640px) {

      .email-shell { width:100% !important; border-radius:0 !important; }

      .section-padding { padding-left:22px !important; padding-right:22px !important; }

      .stack-column { display:block !important; width:100% !important; border-right:none !important; border-left:none !important; }

      .center-mobile { text-align:center !important; }

      .amount-text { font-size:58px !important; }

      .wallet-art { width:240px !important; margin-top:22px !important; }

      .button-stack { display:block !important; width:100% !important; margin:0 0 12px !important; }

    }

  </style>

</head>



<body style="margin:0; padding:0; background:#f7f4ed; font-family:Arial, Helvetica, sans-serif; color:#101828;">

  <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#f7f4ed; padding:28px 12px;">

    <tr>

      <td align="center">

        <table class="email-shell" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width:820px; background:#fffdf8; border-radius:24px; overflow:hidden; box-shadow:0 14px 38px rgba(56,42,15,0.12); border:1px solid #eee3c7;">



          <!-- Header -->

          <tr>

            <td align="center" class="section-padding" style="padding:34px 34px 12px; background:linear-gradient(180deg,#fffdf8 0%,#fffaf0 100%);">

              <img src="{{ $logoUrl }}" alt="LinkUp Logo" style="max-width:235px; width:100%; height:auto; display:block; margin:0 auto 12px;" />



              <div class="alert-pulse" style="width:82px; height:82px; border-radius:50%; background:#fff8e8; border:2px solid #d98f00; color:#d98f00; font-size:38px; line-height:82px; text-align:center; margin:18px auto 16px; box-shadow:0 8px 20px rgba(217,143,0,0.18);">

                📄

              </div>



              <h1 style="margin:0; font-size:42px; line-height:1.15; font-weight:900; color:#101828;">

                Money Request <span style="color:#d98f00;">Received</span>

              </h1>



              <div style="width:150px; height:2px; background:#d98f00; margin:18px auto 0;"></div>

            </td>

          </tr>



          <!-- Hero / Request Summary -->

          <tr>

            <td class="section-padding" style="padding:24px 40px 18px; background:#fffdf8;">

              <table width="100%" cellpadding="0" cellspacing="0" role="presentation">

                <tr>

                  <td class="stack-column center-mobile" width="55%" valign="middle" style="padding-right:20px;">

                    <p style="margin:0 0 16px; font-size:22px; font-weight:800; color:#101828;">Hi {{ $recipient->name }},</p>



                    <p style="margin:0 0 28px; font-size:19px; line-height:1.55; color:#101828;">

                      <strong style="color:#d98f00;">{{ $requester->name }}</strong> sent you a payment request on <strong style="color:#126fc9;">LinkUp</strong>.

                    </p>



                    <div style="font-size:16px; letter-spacing:2px; font-weight:900; color:#d98f00; margin-bottom:6px;">REQUEST AMOUNT</div>

                    <div class="gold-glow amount-text" style="font-size:76px; line-height:1; font-weight:900; color:#d98f00; margin:0;">

                      B${{ number_format($amount, 2) }}

                    </div>



                    <table cellpadding="0" cellspacing="0" role="presentation" style="margin-top:18px; background:#fffaf0; border:1px solid #ead69c; border-radius:12px;">

                      <tr>

                        <td style="padding:10px 14px; font-size:26px; color:#d4af37;">✺</td>

                        <td style="padding:10px 16px 10px 0;">

                          <strong style="display:block; font-size:15px; color:#101828;">Bahamian Sand Dollar (BSD)</strong>

                          <span style="font-size:14px; color:#008c8c; font-weight:700;">Digital Currency</span>

                        </td>

                      </tr>

                    </table>

                  </td>



                  <td class="stack-column center-mobile" width="45%" valign="middle" align="center">

                    <img src="{{ $walletRequestImageUrl }}" alt="Money Request Wallet" style="max-width:280px; width:100%; height:auto; display:block;" />




                  </td>

                </tr>

              </table>

            </td>

          </tr>



          <!-- Request Details -->

          <tr>

            <td class="section-padding" style="padding:18px 40px 16px;">

              <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff; border-radius:22px; border:1px solid #eadfbd; box-shadow:0 10px 25px rgba(56,42,15,0.08); overflow:hidden;">

                <tr>

                  <td class="stack-column" width="58%" valign="top" style="padding:26px 24px; border-right:1px solid #eee3c7;">

                    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">

                      <tr>

                        <td width="84" valign="top">

                          <div style="width:70px; height:70px; border-radius:50%; background:linear-gradient(135deg,#d4af37,#ffffff); padding:3px;">

                            <img src="{{ $requesterAvatarUrl }}" width="70" height="70" alt="{{ $requester->name }} profile photo" style="display:block; width:70px; height:70px; border-radius:50%; object-fit:cover;" />

                          </div>

                        </td>

                        <td valign="top">

                          <h2 style="margin:0 0 16px; font-size:22px; color:#101828;">Request Details</h2>

                          <p style="margin:0 0 12px; font-size:16px; color:#101828;"><span style="color:#d98f00; font-weight:800;">👥 From:</span> &nbsp; {{ $requester->name }} ({{ $requester->linkup_id ?? '@' . strtolower(str_replace(' ', '', $requester->name)) }})</p>

                          <p style="margin:0 0 12px; font-size:16px; color:#101828;"><span style="color:#d98f00; font-weight:800;">🧾 Note:</span> &nbsp; {{ $note ?? 'No note provided' }}</p>

                          <p style="margin:0; font-size:16px; color:#101828;"><span style="color:#d98f00; font-weight:800;">📅 Date:</span> &nbsp; {{ $date }} at {{ $time }}</p>

                        </td>

                      </tr>

                    </table>

                  </td>



                  <td class="stack-column" width="42%" valign="top" style="padding:26px 24px;">

                    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">

                      <tr>

                        <td width="52" valign="top">

                          <div style="width:42px; height:42px; border-radius:50%; background:#fff7df; border:1px solid #d4af37; color:#d4af37; line-height:42px; text-align:center; font-size:24px;">🛡</div>

                        </td>

                        <td valign="top">

                          <strong style="display:block; color:#d98f00; font-size:18px; margin-bottom:6px;">Secure & Verified</strong>

                          <span style="font-size:15px; color:#475467; line-height:1.45;">This request was sent securely through <strong>LinkUp</strong>.</span>

                        </td>

                      </tr>

                    </table>



                    <div style="height:1px; background:#eee3c7; margin:22px 0;"></div>



                    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">

                      <tr>

                        <td width="52" valign="top">

                          <div style="width:42px; height:42px; border-radius:50%; background:#fff8e6; border:1px solid #ead69c; color:#d4af37; line-height:42px; text-align:center; font-size:22px;">🔒</div>

                        </td>

                        <td valign="top">

                          <strong style="display:block; color:#101828; font-size:16px; margin-bottom:6px;">Your security is our priority.</strong>

                          <span style="font-size:14px; color:#475467; line-height:1.45;">Please review carefully before taking any action.</span>

                        </td>

                      </tr>

                    </table>

                  </td>

                </tr>

              </table>

            </td>

          </tr>



          <!-- Review Notice -->

          <tr>

            <td class="section-padding" style="padding:8px 40px 14px;">

              <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#fffaf0; border:1px solid #ead69c; border-radius:14px;">

                <tr>

                  <td width="70" align="center" style="padding:18px 10px; font-size:32px; color:#d98f00;">ⓘ</td>

                  <td style="padding:18px 18px 18px 0; font-size:17px; line-height:1.5; color:#101828;">

                    You can choose to accept or reject this money request from your wallet dashboard.

                  </td>

                </tr>

              </table>

            </td>

          </tr>



          <!-- Buttons -->

          <tr>

            <td align="center" class="section-padding" style="padding:0 40px 22px;">

              <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width:620px; margin:0 auto;">

                <tr>

                  <td class="button-stack" width="50%" align="center" style="padding:0 10px 12px;">

                    <a href="#" style="display:block; border:2px solid #2ea52d; color:#2ea52d; background:#ffffff; text-decoration:none; padding:15px 18px; border-radius:10px; font-size:18px; font-weight:900;">

                      ✓ Review Request →

                    </a>

                  </td>

                  <td class="button-stack" width="50%" align="center" style="padding:0 10px 12px;">

                    <a href="#" style="display:block; border:2px solid #ef4444; color:#ef4444; background:#ffffff; text-decoration:none; padding:15px 18px; border-radius:10px; font-size:18px; font-weight:900;">

                      ✕ Decline Request

                    </a>

                  </td>

                </tr>

              </table>



              <div style="font-size:14px; color:#667085; margin:2px 0 12px;">or</div>



              <a href="{{route('frontend.user.wallet')}}" style="display:inline-block; background:linear-gradient(135deg,#126fc9,#0759b8); color:#ffffff; text-decoration:none; padding:15px 48px; border-radius:10px; font-size:18px; font-weight:900; box-shadow:0 8px 18px rgba(18,111,201,0.22);">

                💳 Go to Wallet →

              </a>

            </td>

          </tr>



          <!-- Sponsor Section -->

          <tr>

            <td class="section-padding" style="padding:8px 40px 22px;">

              @include('emails.partials.sponsor', ['ad' => $ad])

            </td>

          </tr>



          <!-- Safety / Support Row -->

          <tr>

            <td class="section-padding" style="padding:4px 40px 24px;">

              <table width="100%" cellpadding="0" cellspacing="0" role="presentation">

                <tr>

                  <td class="stack-column" width="33.33%" style="padding:16px 18px; border-right:1px solid #eee3c7;">

                    <div style="width:50px; height:50px; border-radius:50%; background:#f6fbf3; border:1px solid #d8ead0; color:#2ea52d; line-height:50px; text-align:center; font-size:26px; margin-bottom:10px;">🛡</div>

                    <strong style="display:block; font-size:16px; color:#101828; margin-bottom:4px;">Your security is our priority.</strong>

                    <span style="font-size:14px; color:#667085; line-height:1.45;">We use bank-level encryption to protect your money.</span>

                  </td>

                  <td class="stack-column" width="33.33%" style="padding:16px 18px; border-right:1px solid #eee3c7;">

                    <div style="width:50px; height:50px; border-radius:50%; background:#f0f8ff; border:1px solid #dbeaf8; color:#126fc9; line-height:50px; text-align:center; font-size:26px; margin-bottom:10px;">🎧</div>

                    <strong style="display:block; font-size:16px; color:#101828; margin-bottom:4px;">Need help?</strong>

                    <span style="font-size:14px; color:#667085; line-height:1.45;">Our support team is here for you.<br /><a href="mailto:support@linkup.com" style="color:#126fc9; text-decoration:none; font-weight:700;">support@linkup.com</a></span>

                  </td>

                  <td class="stack-column" width="33.33%" style="padding:16px 18px;">

                    <div style="width:50px; height:50px; border-radius:50%; background:#fff8e6; border:1px solid #ead69c; color:#d4af37; line-height:50px; text-align:center; font-size:26px; margin-bottom:10px;">🔒</div>

                    <strong style="display:block; font-size:16px; color:#101828; margin-bottom:4px;">Don’t recognize this request?</strong>

                    <span style="font-size:14px; color:#667085; line-height:1.45;">Secure your account and contact us immediately.</span>

                  </td>

                </tr>

              </table>

            </td>

          </tr>



          <!-- Footer -->

          <tr>

            <td class="section-padding" style="padding:24px 40px 28px; background:#fffaf0; border-top:1px solid #eee3c7;">

              <table width="100%" cellpadding="0" cellspacing="0" role="presentation">

                <tr>

                  <td class="stack-column center-mobile" width="30%" valign="middle" style="padding-bottom:12px;">

                    <img src="{{ $logoUrl }}" alt="LinkUp Logo" style="max-width:150px; width:100%; height:auto; display:block;" />

                  </td>

                  <td class="stack-column center-mobile" width="45%" valign="middle" style="font-size:13px; color:#667085; line-height:1.6; padding-bottom:12px;">

                    © 2026 LinkUp. All rights reserved.<br />

                    Nassau, Bahamas<br />

                    Linking Caribbean & Latin American People <span style="color:#d98f00; font-weight:800;">Everywhere.</span>

                  </td>

                  <td class="stack-column center-mobile" width="25%" valign="middle" align="right" style="padding-bottom:12px;">

                    <div style="font-size:13px; color:#667085; margin-bottom:8px;">Follow us @linkupcaribbean</div>

                    <a href="#" style="display:inline-block; width:34px; height:34px; border-radius:50%; background:#126fc9; color:#fff; text-decoration:none; line-height:34px; text-align:center; font-weight:900; margin-left:5px;">IG</a>

                    <a href="#" style="display:inline-block; width:34px; height:34px; border-radius:50%; background:#126fc9; color:#fff; text-decoration:none; line-height:34px; text-align:center; font-weight:900; margin-left:5px;">f</a>

                    <a href="#" style="display:inline-block; width:34px; height:34px; border-radius:50%; background:#9ad223; color:#101828; text-decoration:none; line-height:34px; text-align:center; font-weight:900; margin-left:5px;">♪</a>

                  </td>

                </tr>

              </table>



              <div style="height:1px; background:#eadfbd; margin:12px 0 18px;"></div>



              <p style="margin:0 0 10px; text-align:center; font-size:13px; color:#667085; line-height:1.6;">

                This is an automated message from LinkUp. Please do not reply to this email.

              </p>

              <p style="margin:0 0 10px; text-align:center; font-size:13px; color:#667085; line-height:1.6;">

                Need help? Contact our support team at <a href="mailto:support@linkup.com" style="color:#126fc9; text-decoration:none; font-weight:700;">support@linkup.com</a>.

              </p>

              <p style="margin:0 0 14px; text-align:center; font-size:12px; color:#98a2b3; line-height:1.6; max-width:650px; margin-left:auto; margin-right:auto;">

                If you did not expect this request, secure your account immediately and contact LinkUp support. Sponsored partnerships help us continue connecting Caribbean and Latin American people worldwide.

              </p>

              <p style="margin:0; text-align:center; font-size:12px; color:#667085; line-height:1.6;">

                <a href="#" style="color:#126fc9; text-decoration:none;">Help Center</a>

                &nbsp; | &nbsp;

                <a href="#" style="color:#126fc9; text-decoration:none;">Manage Notifications</a>

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
