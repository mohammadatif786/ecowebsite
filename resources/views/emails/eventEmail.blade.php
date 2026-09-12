<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LinkUp Events Newsletter</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #ede8dc;
            font-family: -apple-system, 'Segoe UI', Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            color: #101828;
        }

        table {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        img {
            border: 0;
            display: block;
            outline: none;
            text-decoration: none;
        }

        a {
            text-decoration: none;
        }

        .wrapper {
            width: 100%;
            table-layout: fixed;
            background: #ede8dc;
            padding-bottom: 50px;
        }

        .main-content {
            margin: 0 auto;
            width: 100%;
            max-width: 640px;
            background: #fffdf8;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 24px 64px rgba(56, 42, 15, .16), 0 0 0 1px #e0d5be;
        }

        @media (max-width: 560px) {
            .main-content {
                border-radius: 0;
                box-shadow: none;
            }

            .hide-mobile {
                display: none !important;
            }

            .stack-mobile {
                display: block !important;
                width: 100% !important;
                padding: 0 !important;
            }

            .pad-mobile {
                padding: 16px !important;
            }
        }
    </style>
</head>

<body>

    <table class="wrapper" cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#ede8dc">
        <tr>
            <td align="center" style="padding: 32px 16px 52px;">

                <!-- MAIN CARD -->
                <table class="main-content" cellpadding="0" cellspacing="0" border="0" width="100%"
                    style="max-width: 640px; background: #fffdf8; border-radius: 28px;">

                    <!-- LOGO BAR -->
                    <tr>
                        <td style="padding: 26px 32px 20px; border-bottom: 1px solid #f0e8d4;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td valign="middle">
                                        <img src="{{ asset('LinkUp Logo_1.png') }}" alt="LinkUp"
                                            style="height: 34px;">
                                        <div style="font-size: 12px; color: #667085; margin-top: 6px;">
                                            Linking <strong style="color:#126fc9;">Caribbean</strong> &amp;
                                            Latin American People <strong style="color:#b07e10;">Everywhere.</strong>
                                        </div>
                                    </td>
                                    <td align="right" valign="middle" class="hide-mobile">
                                        <table cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td width="42" height="42" align="center" valign="middle"
                                                    style="background: #fff7e6; border-radius: 50%; font-size: 20px;">📅
                                                </td>
                                                <td width="10">&nbsp;</td>
                                                <td align="left"
                                                    style="font-size: 13px; color: #101828; line-height: 1.5;">
                                                    <strong>Your events. Your culture.</strong><br>
                                                    Your people. <strong style="color:#b07e10;">All in one
                                                        place.</strong>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- HERO -->
                    <tr>
                        <td
                            style="background: linear-gradient(135deg, #1a0f4e 0%, #3b0d94 55%, #c2410c 100%); padding: 48px 36px; text-align: left;">
                            <div
                                style="font-size: 22px; font-style: italic; font-family: Georgia, serif; color: rgba(255,255,255,.7); margin-bottom: 2px;">
                                Your Weekend</div>
                            <div
                                style="font-size: 54px; font-weight: 900; color: #ffd166; line-height: .95; letter-spacing: -1.5px; margin-bottom: 18px;">
                                STARTS HERE</div>
                            <p
                                style="font-size: 16px; line-height: 1.65; color: rgba(255,255,255,.8); max-width: 380px; margin: 0 0 26px;">
                                Discover the best events, cookouts, wellness experiences and more happening near you.
                            </p>
                            <a href="#"
                                style="display: inline-block; background: #fff; color: #4c1d95; text-decoration: none; padding: 13px 28px; border-radius: 11px; font-size: 15px; font-weight: 800;">Explore
                                Events →</a>
                        </td>
                    </tr>

                    <!-- UPCOMING EVENTS -->
                    <tr>
                        <td style="padding: 28px 24px 16px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-bottom: 14px; border-bottom: 1px solid #f0e8d4;">
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="font-size: 15px; font-weight: 900; color: #101828;">🎟️
                                                    Upcoming Events Near You</td>
                                                <td align="right"><a href="#"
                                                        style="font-size: 13px; font-weight: 700; color: #6d28d9; text-decoration: none;">View
                                                        all →</a></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top: 14px;">
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            @if (isset($events) && $events->count() > 0)
                                                @foreach ($events->chunk(2) as $chunk)
                                                    <tr>
                                                        @foreach ($chunk as $event)
                                                            @php
                                                                $bgImage = $event->image_url
                                                                    ? 'url(' . $event->image_url . ') center/cover'
                                                                    : 'linear-gradient(135deg,#7c3aed,#111827)';
                                                                $startDate = $event->start_time
                                                                    ? \Carbon\Carbon::parse($event->start_time)
                                                                    : now();
                                                                $endDate = $event->end_time
                                                                    ? \Carbon\Carbon::parse($event->end_time)
                                                                    : null;
                                                            @endphp
                                                            <td width="50%" valign="top" style="padding: 6px;"
                                                                class="stack-mobile">
                                                                <table width="100%" cellpadding="0" cellspacing="0"
                                                                    border="0"
                                                                    style="background: #fff; border: 1px solid #ede8dc; border-radius: 16px;">
                                                                    <tr>
                                                                        <td style="height: 84px; padding: 12px; background: {{ $bgImage }}; border-radius: 16px 16px 0 0;"
                                                                            valign="top">
                                                                            @if ($event->category)
                                                                                <span
                                                                                    style="display: inline-block; background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.28); color: #fff; font-size: 10px; font-weight: 800; letter-spacing: .8px; text-transform: uppercase; padding: 4px 9px; border-radius: 100px;">{{ $event->category->name ?? 'Event' }}</span>
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="padding: 14px;">
                                                                            <table width="100%" cellpadding="0"
                                                                                cellspacing="0" border="0">
                                                                                <tr>
                                                                                    <td width="48" align="center"
                                                                                        valign="middle"
                                                                                        style="background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 10px; padding: 7px 9px;">
                                                                                        <div
                                                                                            style="font-size: 9px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; color: #6d28d9;">
                                                                                            {{ strtoupper($startDate->format('M')) }}
                                                                                        </div>
                                                                                        <div
                                                                                            style="font-size: 22px; font-weight: 900; line-height: 1; color: #3b0764;">
                                                                                            {{ $startDate->format('d') }}
                                                                                        </div>
                                                                                        <div
                                                                                            style="font-size: 9px; font-weight: 700; text-transform: uppercase; color: #6d28d9;">
                                                                                            {{ strtoupper($startDate->format('D')) }}
                                                                                        </div>
                                                                                    </td>
                                                                                    <td width="10">&nbsp;</td>
                                                                                    <td valign="top">
                                                                                        <div
                                                                                            style="font-size: 14px; font-weight: 800; color: #101828; line-height: 1.3; margin-bottom: 3px;">
                                                                                            {{ Str::limit($event->title, 35) }}
                                                                                        </div>
                                                                                        <div
                                                                                            style="font-size: 12px; color: #667085; margin-bottom: 2px;">
                                                                                            {{ Str::limit($event->venue, 20) }}
                                                                                        </div>
                                                                                        <div
                                                                                            style="font-size: 12px; font-weight: 700; color: #6d28d9;">
                                                                                            📍
                                                                                            {{ $event->city ? $event->city . ', ' . $event->state : 'TBD' }}
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                            </table>
                                                                            <div
                                                                                style="font-size: 12px; color: #667085; line-height: 1.65; margin: 10px 0 12px;">
                                                                                🕘 {{ $startDate->format('g:i A') }}
                                                                                @if ($endDate)
                                                                                    – {{ $endDate->format('g:i A') }}
                                                                                @endif
                                                                                <br>
                                                                                👥 {{ $event->likes_count ?? 0 }}
                                                                                interested
                                                                            </div>
                                                                            <a href="{{ url('/events/' . $event->id) }}"
                                                                                style="display: block; text-align: center; text-decoration: none; padding: 10px; border-radius: 9px; font-size: 13px; font-weight: 800; color: #fff; background: #6d28d9;">Get
                                                                                Tickets →</a>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        @endforeach
                                                        @if ($chunk->count() == 1)
                                                            <td width="50%" style="padding: 6px;"
                                                                class="hide-mobile">&nbsp;</td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td style="padding: 20px; text-align: center; color: #667085;">No
                                                        new events from your followed organizers at the moment.</td>
                                                </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- COOKOUTS & EATS -->
                    @if (isset($cookouts) && $cookouts->count() > 0)
                        <tr>
                            <td style="padding: 0 24px 16px;">
                                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td style="padding-bottom: 14px; border-bottom: 1px solid #f0e8d4;">
                                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                <tr>
                                                    <td style="font-size: 15px; font-weight: 900; color: #101828;">🍴
                                                        Cookouts &amp; Eats</td>
                                                    <td align="right"><a href="#"
                                                            style="font-size: 13px; font-weight: 700; color: #ea580c; text-decoration: none;">View
                                                            all →</a></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-top: 14px;">
                                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                @foreach ($cookouts->chunk(2) as $chunk)
                                                    <tr>
                                                        @foreach ($chunk as $event)
                                                            @php
                                                                $bgImage = $event->image_url
                                                                    ? 'url(' . $event->image_url . ') center/cover'
                                                                    : 'linear-gradient(135deg,#f59e0b,#7c2d12)';
                                                                $startDate = $event->start_time
                                                                    ? \Carbon\Carbon::parse($event->start_time)
                                                                    : now();
                                                                $endDate = $event->end_time
                                                                    ? \Carbon\Carbon::parse($event->end_time)
                                                                    : null;
                                                            @endphp
                                                            <td width="50%" valign="top" style="padding: 6px;"
                                                                class="stack-mobile">
                                                                <table width="100%" cellpadding="0" cellspacing="0"
                                                                    border="0"
                                                                    style="background: #fff; border: 1px solid #ede8dc; border-radius: 16px;">
                                                                    <tr>
                                                                        <td style="height: 84px; padding: 12px; background: {{ $bgImage }}; border-radius: 16px 16px 0 0;"
                                                                            valign="top">
                                                                            @if ($event->category)
                                                                                <span
                                                                                    style="display: inline-block; background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.28); color: #fff; font-size: 10px; font-weight: 800; letter-spacing: .8px; text-transform: uppercase; padding: 4px 9px; border-radius: 100px;">{{ $event->category->name ?? 'Cookout' }}</span>
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="padding: 14px;">
                                                                            <table width="100%" cellpadding="0"
                                                                                cellspacing="0" border="0">
                                                                                <tr>
                                                                                    <td width="48" align="center"
                                                                                        valign="middle"
                                                                                        style="background: #fff7ed; border: 1px solid #fed7aa; border-radius: 10px; padding: 7px 9px;">
                                                                                        <div
                                                                                            style="font-size: 9px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; color: #ea580c;">
                                                                                            {{ strtoupper($startDate->format('M')) }}
                                                                                        </div>
                                                                                        <div
                                                                                            style="font-size: 22px; font-weight: 900; line-height: 1; color: #7c2d12;">
                                                                                            {{ $startDate->format('d') }}
                                                                                        </div>
                                                                                        <div
                                                                                            style="font-size: 9px; font-weight: 700; text-transform: uppercase; color: #ea580c;">
                                                                                            {{ strtoupper($startDate->format('D')) }}
                                                                                        </div>
                                                                                    </td>
                                                                                    <td width="10">&nbsp;</td>
                                                                                    <td valign="top">
                                                                                        <div
                                                                                            style="font-size: 14px; font-weight: 800; color: #101828; line-height: 1.3; margin-bottom: 3px;">
                                                                                            {{ Str::limit($event->title, 35) }}
                                                                                        </div>
                                                                                        <div
                                                                                            style="font-size: 12px; color: #667085; margin-bottom: 2px;">
                                                                                            {{ Str::limit($event->venue, 20) }}
                                                                                        </div>
                                                                                        <div
                                                                                            style="font-size: 12px; font-weight: 700; color: #ea580c;">
                                                                                            📍
                                                                                            {{ $event->city ? $event->city . ', ' . $event->state : 'TBD' }}
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                            </table>
                                                                            <div
                                                                                style="font-size: 12px; color: #667085; line-height: 1.65; margin: 10px 0 12px;">
                                                                                🕘 {{ $startDate->format('g:i A') }}
                                                                                @if ($endDate)
                                                                                    – {{ $endDate->format('g:i A') }}
                                                                                @endif
                                                                                <br>
                                                                                👥 {{ $event->likes_count ?? 0 }}
                                                                                interested
                                                                            </div>
                                                                            <a href="{{ url('/events/' . $event->id) }}"
                                                                                style="display: block; text-align: center; text-decoration: none; padding: 10px; border-radius: 9px; font-size: 13px; font-weight: 800; color: #fff; background: #ea580c;">Get
                                                                                Tickets →</a>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        @endforeach
                                                        @if ($chunk->count() == 1)
                                                            <td width="50%" style="padding: 6px;"
                                                                class="hide-mobile">&nbsp;</td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endif

                    <!-- WELLNESS & SPA -->
                    @if (isset($wellness) && $wellness->count() > 0)
                        <tr>
                            <td style="padding: 0 24px 16px;">
                                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td style="padding-bottom: 14px; border-bottom: 1px solid #f0e8d4;">
                                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                <tr>
                                                    <td style="font-size: 15px; font-weight: 900; color: #101828;">🪷
                                                        Wellness &amp; Spa</td>
                                                    <td align="right"><a href="#"
                                                            style="font-size: 13px; font-weight: 700; color: #16a34a; text-decoration: none;">View
                                                            all →</a></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-top: 14px;">
                                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                @foreach ($wellness->chunk(2) as $chunk)
                                                    <tr>
                                                        @foreach ($chunk as $event)
                                                            @php
                                                                $bgImage = $event->image_url
                                                                    ? 'url(' . $event->image_url . ') center/cover'
                                                                    : 'linear-gradient(135deg,#38bdf8,#22c55e)';
                                                                $startDate = $event->start_time
                                                                    ? \Carbon\Carbon::parse($event->start_time)
                                                                    : now();
                                                                $endDate = $event->end_time
                                                                    ? \Carbon\Carbon::parse($event->end_time)
                                                                    : null;
                                                            @endphp
                                                            <td width="50%" valign="top" style="padding: 6px;"
                                                                class="stack-mobile">
                                                                <table width="100%" cellpadding="0" cellspacing="0"
                                                                    border="0"
                                                                    style="background: #fff; border: 1px solid #ede8dc; border-radius: 16px;">
                                                                    <tr>
                                                                        <td style="height: 84px; padding: 12px; background: {{ $bgImage }}; border-radius: 16px 16px 0 0;"
                                                                            valign="top">
                                                                            @if ($event->category)
                                                                                <span
                                                                                    style="display: inline-block; background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.28); color: #fff; font-size: 10px; font-weight: 800; letter-spacing: .8px; text-transform: uppercase; padding: 4px 9px; border-radius: 100px;">{{ $event->category->name ?? 'Wellness' }}</span>
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="padding: 14px;">
                                                                            <table width="100%" cellpadding="0"
                                                                                cellspacing="0" border="0">
                                                                                <tr>
                                                                                    <td width="48" align="center"
                                                                                        valign="middle"
                                                                                        style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 7px 9px;">
                                                                                        <div
                                                                                            style="font-size: 9px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; color: #16a34a;">
                                                                                            {{ strtoupper($startDate->format('M')) }}
                                                                                        </div>
                                                                                        <div
                                                                                            style="font-size: 22px; font-weight: 900; line-height: 1; color: #14532d;">
                                                                                            {{ $startDate->format('d') }}
                                                                                        </div>
                                                                                        <div
                                                                                            style="font-size: 9px; font-weight: 700; text-transform: uppercase; color: #16a34a;">
                                                                                            {{ strtoupper($startDate->format('D')) }}
                                                                                        </div>
                                                                                    </td>
                                                                                    <td width="10">&nbsp;</td>
                                                                                    <td valign="top">
                                                                                        <div
                                                                                            style="font-size: 14px; font-weight: 800; color: #101828; line-height: 1.3; margin-bottom: 3px;">
                                                                                            {{ Str::limit($event->title, 35) }}
                                                                                        </div>
                                                                                        <div
                                                                                            style="font-size: 12px; color: #667085; margin-bottom: 2px;">
                                                                                            {{ Str::limit($event->venue, 20) }}
                                                                                        </div>
                                                                                        <div
                                                                                            style="font-size: 12px; font-weight: 700; color: #16a34a;">
                                                                                            📍
                                                                                            {{ $event->city ? $event->city . ', ' . $event->state : 'TBD' }}
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                            </table>
                                                                            <div
                                                                                style="font-size: 12px; color: #667085; line-height: 1.65; margin: 10px 0 12px;">
                                                                                🕘 {{ $startDate->format('g:i A') }}
                                                                                @if ($endDate)
                                                                                    – {{ $endDate->format('g:i A') }}
                                                                                @endif
                                                                                <br>👥 {{ $event->likes_count ?? 0 }}
                                                                                interested
                                                                            </div>
                                                                            <a href="{{ url('/events/' . $event->id) }}"
                                                                                style="display: block; text-align: center; text-decoration: none; padding: 10px; border-radius: 9px; font-size: 13px; font-weight: 800; color: #fff; background: #16a34a;">Book
                                                                                Now →</a>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        @endforeach
                                                        @if ($chunk->count() == 1)
                                                            <td width="50%" style="padding: 6px;"
                                                                class="hide-mobile">&nbsp;</td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endif

                    <!-- SPONSOR -->
                    @if (!empty($ad))
                        <tr>
                            <td>
                                @include('emails.partials.sponsor', ['ad' => $ad])
                            </td>
                        </tr>
                    @endif

                    <!-- WHY LINKUP -->
                    <tr>
                        <td style="padding: 20px 24px 24px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td
                                        style="font-size: 15px; font-weight: 900; color: #101828; margin-bottom: 14px; padding-bottom: 14px; border-bottom: 1px solid #f0e8d4;">
                                        💚 Why LinkUp Events?
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top: 14px;">
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td width="50%" valign="top" style="padding: 6px;"
                                                    class="stack-mobile">
                                                    <table width="100%" cellpadding="0" cellspacing="0"
                                                        border="0"
                                                        style="background: #f9f8f5; border: 1px solid #ede8dc; border-radius: 14px;">
                                                        <tr>
                                                            <td style="padding: 18px 16px;">
                                                                <div style="font-size: 24px; margin-bottom: 8px;">👥
                                                                </div>
                                                                <div
                                                                    style="font-size: 13px; font-weight: 800; color: #101828; margin-bottom: 5px;">
                                                                    People &amp; Community</div>
                                                                <div
                                                                    style="font-size: 12px; color: #667085; line-height: 1.55;">
                                                                    See who's going and connect before you even arrive.
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td width="50%" valign="top" style="padding: 6px;"
                                                    class="stack-mobile">
                                                    <table width="100%" cellpadding="0" cellspacing="0"
                                                        border="0"
                                                        style="background: #f9f8f5; border: 1px solid #ede8dc; border-radius: 14px;">
                                                        <tr>
                                                            <td style="padding: 18px 16px;">
                                                                <div style="font-size: 24px; margin-bottom: 8px;">💳
                                                                </div>
                                                                <div
                                                                    style="font-size: 13px; font-weight: 800; color: #101828; margin-bottom: 5px;">
                                                                    Pay with LinkUp Wallet</div>
                                                                <div
                                                                    style="font-size: 12px; color: #667085; line-height: 1.55;">
                                                                    Secure payments with B$ Sand Dollar.</div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="50%" valign="top" style="padding: 6px;"
                                                    class="stack-mobile">
                                                    <table width="100%" cellpadding="0" cellspacing="0"
                                                        border="0"
                                                        style="background: #f9f8f5; border: 1px solid #ede8dc; border-radius: 14px;">
                                                        <tr>
                                                            <td style="padding: 18px 16px;">
                                                                <div style="font-size: 24px; margin-bottom: 8px;">📍
                                                                </div>
                                                                <div
                                                                    style="font-size: 13px; font-weight: 800; color: #101828; margin-bottom: 5px;">
                                                                    Local &amp; Relevant</div>
                                                                <div
                                                                    style="font-size: 12px; color: #667085; line-height: 1.55;">
                                                                    Events tailored to your location and interests.
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td width="50%" valign="top" style="padding: 6px;"
                                                    class="stack-mobile">
                                                    <table width="100%" cellpadding="0" cellspacing="0"
                                                        border="0"
                                                        style="background: #f9f8f5; border: 1px solid #ede8dc; border-radius: 14px;">
                                                        <tr>
                                                            <td style="padding: 18px 16px;">
                                                                <div style="font-size: 24px; margin-bottom: 8px;">🛡️
                                                                </div>
                                                                <div
                                                                    style="font-size: 13px; font-weight: 800; color: #101828; margin-bottom: 5px;">
                                                                    Safe &amp; Trusted</div>
                                                                <div
                                                                    style="font-size: 12px; color: #667085; line-height: 1.55;">
                                                                    Verified organizers. Secure experiences.</div>
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

                    <!-- APP PROMO -->
                    <tr>
                        <td style="padding: 0 24px 28px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="background: linear-gradient(135deg, #e8f8f8, #d1f5f0); border: 1px solid #b2e8e4; border-radius: 20px;">
                                <tr>
                                    <td style="padding: 22px;" align="center">
                                        <div
                                            style="font-size: 16px; font-weight: 900; color: #101828; line-height: 1.3; margin-bottom: 8px;">
                                            Everything you love.<br>
                                            <span style="color: #0d9488;">All in the LinkUp App.</span>
                                        </div>
                                        <div
                                            style="font-size: 13px; color: #374151; line-height: 1.8; margin-bottom: 14px;">
                                            ✓ Events &nbsp; ✓ Matches &nbsp; ✓ Wallet &nbsp; ✓ Marketplace
                                        </div>
                                        <div style="text-align: center;">
                                            <a href="#"
                                                style="display: inline-block; background: #101828; color: #fff; text-decoration: none; padding: 10px 16px; border-radius: 8px; font-size: 12px; font-weight: 800; margin: 4px;">↓
                                                Download on the App Store</a>
                                            <a href="#"
                                                style="display: inline-block; background: #101828; color: #fff; text-decoration: none; padding: 10px 16px; border-radius: 8px; font-size: 12px; font-weight: 800; margin: 4px;">↓
                                                Get it on Google Play</a>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td style="border-top: 1px solid #f0e8d4; padding: 26px 36px 34px; text-align: center;">
                            <img src="{{ asset('LinkUp Logo_1.png') }}" alt="LinkUp"
                                style="height: 26px; display: block; margin: 0 auto 12px;">
                            <div style="font-size: 13px; color: #667085; line-height: 1.6; margin-bottom: 16px;">
                                © 2026 LinkUp<br>
                                Linking <strong style="color:#126fc9;">Caribbean</strong> &amp;
                                Latin American People <strong style="color:#b07e10;">Everywhere.</strong>
                            </div>

                            <div style="margin-bottom: 16px;">
                                <a href="#"
                                    style="font-size: 12px; font-weight: 600; color: #126fc9; text-decoration: none; margin: 0 8px;">Manage
                                    Preferences</a>
                                <a href="#"
                                    style="font-size: 12px; font-weight: 600; color: #126fc9; text-decoration: none; margin: 0 8px;">Contact
                                    Support</a>
                                <a href="#"
                                    style="font-size: 12px; font-weight: 600; color: #126fc9; text-decoration: none; margin: 0 8px;">Privacy
                                    Policy</a>
                            </div>

                            <div style="margin-bottom: 16px;">
                                <a href="#"
                                    style="display: inline-block; width: 34px; height: 34px; line-height: 34px; background: #e1306c; color: #fff; border-radius: 9px; font-size: 13px; font-weight: 900; text-decoration: none; margin: 0 4px;">IG</a>
                                <a href="#"
                                    style="display: inline-block; width: 34px; height: 34px; line-height: 34px; background: #1877f2; color: #fff; border-radius: 9px; font-size: 13px; font-weight: 900; text-decoration: none; margin: 0 4px;">f</a>
                                <a href="#"
                                    style="display: inline-block; width: 34px; height: 34px; line-height: 34px; background: #000; color: #fff; border-radius: 9px; font-size: 13px; font-weight: 900; text-decoration: none; margin: 0 4px;">♪</a>
                            </div>

                            <div style="font-size: 12px; color: #9ca3af; line-height: 1.7;">
                                You're receiving this because you're a LinkUp member.
                            </div>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>
