<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $event['title'] }} Invitation</title>
</head>

<body style="margin:0; padding:0; background:#f4f4f4; font-family:'Segoe UI', Roboto, Arial, sans-serif; color:#333;">

    <!-- Outer Wrapper -->
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center" style="padding:20px;">
                <table width="600" cellpadding="0" cellspacing="0" role="presentation"
                    style="background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,0.1);">

                    <!-- Banner -->
                    {{-- <tr>
                        <td align="center" style="background:#000;">
                            <img src="{{ asset('storage/'.$event['image_object']) }}"
                                 alt="Event Banner" style="max-width:100%; display:block;">
                        </td>
                    </tr> --}}

                    <!-- Title & Subtitle -->
                    <tr>
                        <td align="center" style="padding:35px 25px 20px; background:#fafafa;">
                            <h1 style="margin:0; font-size:28px; font-weight:700; color:#111;">
                                {{ $event['title'] }}
                            </h1>
                            <p style="margin:10px 0 0; font-size:16px; color:#666;">
                                You're exclusively invited to join us!
                            </p>
                        </td>
                    </tr>

                    <!-- Event Details -->
                    <tr>
                        <td style="padding:30px 25px;">
                            <p style="font-size:15px; line-height:1.6; margin:0 0 20px;">
                                We’re thrilled to have you at this special event. Expect great conversations,
                                valuable connections, and an unforgettable experience.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td style="padding:12px; background:#f9f9f9; border-radius:6px; font-size:15px;">
                                        📅 <strong>Starts:</strong>
                                        {{ \Carbon\Carbon::parse($event['start_time'])->format('F j, Y g:i A') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        style="padding:12px; background:#f9f9f9; border-radius:6px; font-size:15px; margin-top:8px;">
                                        🕒 <strong>Ends:</strong>
                                        {{ \Carbon\Carbon::parse($event['end_time'])->format('F j, Y g:i A') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        style="padding:12px; background:#f9f9f9; border-radius:6px; font-size:15px; margin-top:8px;">
                                        📍 <strong>Venue:</strong> {{ $event['venue'] }}
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size:15px; line-height:1.6; margin:25px 0 0;">
                                Confirm your attendance below and secure your seat.
                            </p>
                        </td>
                    </tr>

                    <!-- CTA -->
                    <tr>
                        <td align="center" style="padding:20px;">
                            <a href="{{ $eventLink }}"
                                style="background:#007bff; color:#ffffff; text-decoration:none; padding:14px 32px;
                                      font-size:16px; font-weight:600; border-radius:6px; display:inline-block;">
                                Confirm Attendance
                            </a>
                        </td>
                    </tr>

                    <!-- Sponsored Section -->
                    <tr>
                        <td style="padding: 10px 25px;">
                            @include('emails.partials.sponsor')
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="background:#111; color:#aaa; padding:20px; font-size:13px;">
                            © {{ date('Y') }} All Rights Reserved<br>
                            <a href="{{ url('user.home') }}" style="color:#007bff; text-decoration:none;">
                                Visit our website
                            </a>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>

</html>
