<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8" />

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>LinkUp New Like Email</title>

    <style>
        @keyframes heartbeat {

            0%,
            100% {
                transform: scale(1);
            }

            14% {
                transform: scale(1.18);
            }

            28% {
                transform: scale(1);
            }

            42% {
                transform: scale(1.14);
            }

            70% {
                transform: scale(1);
            }

        }



        @keyframes flagWave {

            0%,
            100% {
                transform: rotate(0deg) skewX(0deg);
            }

            25% {
                transform: rotate(2deg) skewX(-6deg);
            }

            50% {
                transform: rotate(0deg) skewX(4deg);
            }

            75% {
                transform: rotate(-2deg) skewX(-4deg);
            }

        }



        .heart-beat {

            display: inline-block;

            animation: heartbeat 1.4s ease-in-out infinite;

            transform-origin: center;

        }



        .flag-wave {

            display: inline-block;

            animation: flagWave 1.6s ease-in-out infinite;

            transform-origin: left center;

        }



        @media (prefers-reduced-motion: reduce) {

            .heart-beat,
            .flag-wave {
                animation: none !important;
            }

        }
    </style>

</head>

<body style="margin:0; padding:0; background:#f4f7f4; font-family:Arial, Helvetica, sans-serif; color:#101828;">

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#f4f7f4; padding:30px 12px;">

        <tr>

            <td align="center">

                <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
                    style="max-width:760px; background:#ffffff; border-radius:22px; overflow:hidden; box-shadow:0 10px 35px rgba(0,0,0,0.12);">



                    <!-- Header -->

                    <tr>

                        <td align="center"
                            style="background:linear-gradient(135deg,#038c36,#0fa846); padding:44px 24px 58px; color:#ffffff;">

                            <div class="heart-beat" style="font-size:54px; line-height:1; margin-bottom:12px;">❤️</div>

                            <h1 style="margin:0; font-size:38px; line-height:1.15; font-weight:800;">You’ve Got a New
                                Like!</h1>

                            <p style="margin:12px 0 0; font-size:22px; color:#eaffef;">Someone on Linup likes you</p>

                        </td>

                    </tr>



                    <!-- Body -->

                    <tr>

                        <td align="center" style="padding:34px 28px 20px;">

                            <p style="margin:0 0 20px; font-size:22px;">Hi <strong>{{ $likedUser->name }}</strong>,</p>

                            <!-- Notification Card -->

                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
                                style="max-width:520px; background:#fff8fa; border:1px solid #ffd3dc; border-radius:16px; margin:0 auto 28px;">

                                <tr>

                                    <td style="padding:20px;">

                                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">

                                            <tr>

                                                <td width="58" valign="middle">

                                                    <div
                                                        style="width:50px; height:50px; border-radius:50%; background:#ff4d6d; color:#ffffff; text-align:center; line-height:50px; font-size:30px; box-shadow:0 4px 14px rgba(255,77,109,0.35);">
                                                        ❤</div>

                                                </td>

                                                <td valign="middle"
                                                    style="font-size:24px; line-height:1.35; color:#101828;">

                                                    <strong style="color:#ff4d6d;">{{ $liker->name }}</strong>
                                                    <strong>from the
                                                        {{ $liker->country }} <span
                                                            class="flag-wave">🇧🇸</span></strong><br />

                                                    Linked your profile on <strong
                                                        style="color:#ff4d6d;">Linup</strong>.

                                                </td>

                                            </tr>

                                        </table>

                                    </td>

                                </tr>

                            </table>



                            <!-- Profile Image -->

                            <div
                                style="width:190px; height:190px; border-radius:50%; border:8px solid #159b39; overflow:hidden; margin:0 auto; background:#ddd;">

                                <img src="{{ $liker->avatar }}" width="190" height="190" alt="Manuel profile photo"
                                    style="display:block; width:190px; height:190px; object-fit:cover;" />

                            </div>



                            <div
                                style="display:inline-block; margin-top:-18px; margin-left:120px; background:#ffffff; border-radius:24px; padding:8px 16px; box-shadow:0 4px 15px rgba(0,0,0,0.14); font-size:15px; color:#098f35; font-weight:700;">

                                ● Active now

                            </div>



                            <h2 style="margin:18px 0 6px; font-size:30px; line-height:1.2;">{{ $liker->name }} <span
                                    style="color:#098f35; font-size:20px;">✔</span></h2>

                            <p style="margin:0 0 22px; font-size:18px; color:#344054;">👤 {{ $liker->age }} years old
                                &nbsp; | &nbsp;
                                <span class="flag-wave">{{ $liker->country_code }}</span> {{ $liker->city }},
                                {{ $liker->country }}
                            </p>



                            <!-- Interest Tags -->

                            @if (!empty($liker->interests))
                                <table cellpadding="0" cellspacing="0" role="presentation" width="100%"
                                    style="background:#f8fff8; border:1px solid #dbeadb; border-radius:14px; margin:0 auto 28px; border-collapse: collapse;">

                                    @foreach (collect($liker->interests)->chunk(5) as $chunk)
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

                            <p
                                style="max-width:560px; margin:0 auto 28px; font-size:20px; line-height:1.45; font-style:italic; color:#101828;">

                                “I am here to be a part of this wonderful ecosystem.<br />

                                I am here because this is a wonderful platform to meet people from the Caribbean.”

                            </p>



                            <a href="{{ route('frontend.linkup.user.details', ['user' => $liker->id]) }}"
                                style="display:inline-block; background:linear-gradient(135deg,#ff4d6d,#e11d48); color:#ffffff; text-decoration:none; padding:18px 48px; border-radius:14px; font-size:22px; font-weight:800; box-shadow:0 8px 20px rgba(225,29,72,0.32);">

                                View Profile →

                            </a>

                        </td>

                    </tr>



                    <!-- Trust Row -->

                    <tr>

                        <td style="padding:22px 34px 18px;">

                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
                                style="border:1px solid #dcebdc; border-radius:16px; background:#fbfffb;">

                                <tr>

                                    <td width="33.33%" style="padding:18px; border-right:1px solid #dcebdc;">

                                        <strong style="display:block; font-size:16px;">🛡️ Safe & Secure</strong>

                                        <span style="font-size:14px; color:#344054;">Your safety is our priority.</span>

                                    </td>

                                    <td width="33.33%" style="padding:18px; border-right:1px solid #dcebdc;">

                                        <strong style="display:block; font-size:16px;">👥 Real Connections</strong>

                                        <span style="font-size:14px; color:#344054;">Meet genuine people from the
                                            Caribbean.</span>

                                    </td>

                                    <td width="33.33%" style="padding:18px;">

                                        <strong style="display:block; font-size:16px;">♡ Made for Us</strong>

                                        <span style="font-size:14px; color:#344054;">Built by Caribbean people, for
                                            Caribbean people.</span>

                                    </td>

                                </tr>

                            </table>

                        </td>

                    </tr>



                    <!-- Sponsored Section -->

                    <tr>

                        <td style="padding:0 34px 24px;">

                            @include('emails.partials.sponsor', ['ad' => $ad])

                        </td>

                    </tr>



                    <!-- Footer -->

                    <tr>

                        <td style="background:#f8fbf6; padding:28px 34px 18px;">

                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">

                                <tr>

                                    <td width="33.33%" align="center"
                                        style="border-right:1px solid #3bc43b; padding:0 15px;">

                                        <img src="{{ $logoUrl }}" alt="LinkUp Logo"
                                            style="max-width:170px; width:100%; height:auto; display:block; margin:0 auto 8px;" />

                                    </td>

                                    <td width="33.33%" align="center"
                                        style="border-right:1px solid #dcebdc; padding:0 15px;">

                                        <div style="font-size:16px; margin-bottom:10px;">Download the <strong
                                                style="color:#159b39;">Linup</strong> app</div>

                                        <a href="#"
                                            style="display:inline-block; background:#111; color:#fff; text-decoration:none; padding:8px 12px; border-radius:6px; font-size:12px; margin:3px;">App
                                            Store</a>

                                        <a href="#"
                                            style="display:inline-block; background:#111; color:#fff; text-decoration:none; padding:8px 12px; border-radius:6px; font-size:12px; margin:3px;">Google
                                            Play</a>

                                    </td>

                                    <td width="33.33%" align="center" style="padding:0 15px;">

                                        <div
                                            style="font-size:16px; margin-bottom:14px; color:#5aa0c8; font-weight:700;">
                                            Follow us</div>



                                        <a href="#"
                                            style="display:inline-block; width:42px; height:42px; border-radius:50%; background:linear-gradient(135deg,#5aa0c8,#c8e000); color:#ffffff; text-decoration:none; line-height:42px; margin:0 6px; font-weight:900; font-size:14px; box-shadow:0 4px 10px rgba(90,160,200,0.28);">IG</a>



                                        <a href="#"
                                            style="display:inline-block; width:42px; height:42px; border-radius:50%; background:linear-gradient(135deg,#5aa0c8,#c8e000); color:#ffffff; text-decoration:none; line-height:42px; margin:0 6px; font-weight:900; font-size:16px; box-shadow:0 4px 10px rgba(90,160,200,0.28);">f</a>



                                        <a href="#"
                                            style="display:inline-block; width:42px; height:42px; border-radius:50%; background:linear-gradient(135deg,#5aa0c8,#c8e000); color:#ffffff; text-decoration:none; line-height:42px; margin:0 6px; font-weight:900; font-size:16px; box-shadow:0 4px 10px rgba(90,160,200,0.28);">♪</a>

                                    </td>

                                </tr>

                            </table>



                            <p style="margin:24px 0 8px; text-align:center; font-size:13px; color:#667085;">

                                You are receiving this email because you are part of Linup 💚

                            </p>



                            <p
                                style="margin:0 0 10px; text-align:center; font-size:13px; color:#667085; line-height:1.6;">

                                Need help? Contact our support team at

                                <a href="mailto:support@linup.com"
                                    style="color:#5aa0c8; text-decoration:none; font-weight:700;">support@linup.com</a>

                                &nbsp; | &nbsp;

                                <a href="#" style="color:#c8e000; text-decoration:none; font-weight:700;">Help
                                    Center</a>

                            </p>



                            <p
                                style="margin:0 0 10px; text-align:center; font-size:12px; color:#98a2b3; line-height:1.6; max-width:620px; margin-left:auto; margin-right:auto;">

                                Never share financial or personal banking information with other users. Report
                                suspicious activity immediately through the Linup app.

                            </p>



                            <p
                                style="margin:0 0 10px; text-align:center; font-size:12px; color:#98a2b3; line-height:1.6;">

                                Sponsored partnerships help us continue connecting Caribbean & Latin American people
                                worldwide.

                            </p>



                            <p style="margin:0 0 12px; text-align:center; font-size:12px; color:#667085;">

                                <a href="#" style="color:#5aa0c8; text-decoration:none;">Manage
                                    Notifications</a>

                                &nbsp; | &nbsp;

                                <a href="#" style="color:#5aa0c8; text-decoration:none;">Unsubscribe</a>

                                &nbsp; | &nbsp;

                                <a href="#" style="color:#5aa0c8; text-decoration:none;">Privacy Policy</a>

                            </p>



                            <p style="margin:0; text-align:center; font-size:13px; color:#667085;">

                                © 2026 Linup. All rights reserved. Nassau, Bahamas.

                            </p>

                        </td>

                    </tr>



                </table>

            </td>

        </tr>

    </table>

</body>

</html>
