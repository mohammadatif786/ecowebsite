<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $event->title }} — LinkUp</title>
<style>
  body { margin: 0; padding: 0; background: #ede8dc; font-family: -apple-system, 'Segoe UI', Arial, sans-serif; -webkit-font-smoothing: antialiased; color: #101828; }
  table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
  img { border: 0; display: block; outline: none; text-decoration: none; }
  a { text-decoration: none; }
  .wrapper { width: 100%; table-layout: fixed; background: #ede8dc; padding-bottom: 50px; }
  .main-content { margin: 0 auto; width: 100%; max-width: 640px; background: #fffdf8; border-radius: 28px; overflow: hidden; box-shadow: 0 24px 64px rgba(56,42,15,.16), 0 0 0 1px #e0d5be; }
  
  @media (max-width: 560px) {
    .main-content { border-radius: 0; box-shadow: none; }
    .hide-mobile { display: none !important; }
    .stack-mobile { display: block !important; width: 100% !important; padding: 0 !important; }
  }
</style>
</head>
<body>

@php
    $startDate = $event->start_time ? \Carbon\Carbon::parse($event->start_time) : null;
    $endDate = $event->end_time ? \Carbon\Carbon::parse($event->end_time) : null;
    $eventLink = url('/events/' . $event->id) . ($trackingToken ?? '' ? '?t=' . $trackingToken : '');
@endphp

<table class="wrapper" cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#ede8dc">
  <tr>
    <td align="center" style="padding: 32px 16px 52px;">
      
      <!-- MAIN CARD -->
      <table class="main-content" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width: 640px; background: #fffdf8; border-radius: 28px;">
        
        <!-- LOGO BAR -->
        <tr>
          <td style="padding: 26px 36px 20px; text-align: center; border-bottom: 1px solid #f0e8d4;">
            <img src="{{ asset('LinkUp Logo_1.png') }}" alt="LinkUp" style="height: 34px; display: block; margin: 0 auto;">
            <div style="font-size: 13px; color: #667085; margin-top: 7px;">
              Linking <strong style="color:#126fc9;">Caribbean</strong> &amp; Latin American People <strong style="color:#b07e10;">Everywhere.</strong>
            </div>
          </td>
        </tr>

        <!-- FLYER HERO -->
        <tr>
          <td style="background: #101828;">
            @if($event->image_url)
              <img src="{{ $event->image_url }}" alt="{{ $event->title }}" width="100%" style="display: block; width: 100%; max-height: 400px; object-fit: cover;">
            @else
              <div style="width: 100%; height: 200px; background: linear-gradient(135deg, #7c3aed, #111827);"></div>
            @endif
          </td>
        </tr>

        <!-- EVENT INTRO -->
        <tr>
          <td style="padding: 28px 36px 20px;">
            <div style="margin-bottom: 12px;">
              <span style="display: inline-block; background: #fef08a; color: #854d0e; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase; margin-right: 8px;">🎉 Just Added</span>
              @if($event->capacity && $event->capacity < 50)
              <span style="display: inline-block; background: #fee2e2; color: #b91c1c; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase;">🔥 Limited Tickets</span>
              @endif
            </div>
            <div style="font-size: 36px; font-weight: 900; color: #101828; line-height: 1.1; letter-spacing: -1px; margin: 0 0 10px;">{{ $event->title }}</div>
            @if($event->description)
              <div style="font-size: 16px; color: #667085; line-height: 1.65; margin: 0;">{{ Str::limit(strip_tags($event->description), 120) }}</div>
            @endif
          </td>
        </tr>

        <!-- EVENT DETAILS -->
        <tr>
          <td style="padding: 0 28px 22px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #f9f8f5; border: 1px solid #ede8dc; border-radius: 20px;">
              <!-- Date -->
              <tr>
                <td style="padding: 16px 20px; border-bottom: 1px solid #ede8dc;">
                  <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                      <td width="30" valign="top" style="font-size: 22px;">📅</td>
                      <td valign="top">
                        <div style="font-size: 10px; font-weight: 800; letter-spacing: 1.8px; text-transform: uppercase; color: #9ca3af; margin-bottom: 3px;">Date &amp; Time</div>
                        <div style="font-size: 15px; font-weight: 800; color: #e02424; line-height: 1.4;">
                          {{ $startDate ? $startDate->format('l, F j, Y \· g:i A') : 'TBA' }}
                        </div>
                        @if($endDate)
                        <div style="font-size: 13px; color: #667085; margin-top: 2px; line-height: 1.5;">Until {{ $endDate->format('l, F j, Y \a\t g:i A') }}</div>
                        @endif
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <!-- Location -->
              <tr>
                <td style="padding: 16px 20px; border-bottom: 1px solid #ede8dc;">
                  <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                      <td width="30" valign="top" style="font-size: 22px;">📍</td>
                      <td valign="top">
                        <div style="font-size: 10px; font-weight: 800; letter-spacing: 1.8px; text-transform: uppercase; color: #9ca3af; margin-bottom: 3px;">Location</div>
                        <div style="font-size: 15px; font-weight: 800; color: #101828; line-height: 1.4;">{{ $event->venue ?? 'Venue TBA' }}</div>
                        <div style="font-size: 13px; color: #667085; margin-top: 2px; line-height: 1.5;">{{ $event->city ? $event->city . ', ' . $event->state : '' }}</div>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <!-- Category -->
              <tr>
                <td style="padding: 16px 20px; border-bottom: 1px solid #ede8dc;">
                  <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                      <td width="30" valign="top" style="font-size: 22px;">🎭</td>
                      <td valign="top">
                        <div style="font-size: 10px; font-weight: 800; letter-spacing: 1.8px; text-transform: uppercase; color: #9ca3af; margin-bottom: 3px;">Category</div>
                        <div style="font-size: 15px; font-weight: 800; color: #101828; line-height: 1.4;">{{ $event->category->name ?? 'Event' }}</div>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <!-- Payment -->
              <tr>
                <td style="padding: 16px 20px;">
                  <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                      <td width="30" valign="top" style="font-size: 22px;">💳</td>
                      <td valign="top">
                        <div style="font-size: 10px; font-weight: 800; letter-spacing: 1.8px; text-transform: uppercase; color: #9ca3af; margin-bottom: 3px;">Payment</div>
                        <div style="font-size: 15px; font-weight: 800; color: #101828; line-height: 1.4;">Secure checkout via LinkUp Wallet</div>
                        <div style="font-size: 13px; color: #667085; margin-top: 2px; line-height: 1.5;">Instant e-ticket delivery</div>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- TICKETS -->
        @if($event->tickets && $event->tickets->count() > 0)
        <tr>
          <td style="padding: 0 28px 22px;">
            <div style="font-size: 11px; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; color: #9ca3af; margin-bottom: 10px; padding: 0 2px;">Ticket Options</div>
            
            @foreach($event->tickets as $ticket)
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #fff; border: 1px solid #ede8dc; border-radius: 16px; margin-bottom: 10px;">
              <tr>
                <td style="padding: 16px 18px;">
                  <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                      <td valign="middle">
                        <div style="font-size: 15px; font-weight: 900; color: #101828; margin-bottom: 3px;">{{ $ticket->name }}</div>
                        <div style="font-size: 12px; color: #667085; line-height: 1.5;">{{ Str::limit($ticket->description, 50) }}</div>
                      </td>
                      <td valign="middle" align="right" style="width: 100px;">
                        <div style="font-size: 22px; font-weight: 900; color: #101828; line-height: 1; margin-bottom: 2px;">${{ number_format($ticket->price, 2) }}</div>
                        @if($ticket->quantity_available > 0 && $ticket->quantity_available < 20)
                          <div style="font-size: 11px; color: #e02424; font-weight: 700;">⚠ Only {{ $ticket->quantity_available }} left</div>
                        @else
                          <div style="font-size: 11px; color: #059669; font-weight: 700;">✓ Available</div>
                        @endif
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
            @endforeach
          </td>
        </tr>
        @endif

        <!-- ORGANIZER -->
        <tr>
          <td style="padding: 0 28px 22px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #f9f8f5; border: 1px solid #ede8dc; border-radius: 18px;">
              <tr>
                <td style="padding: 16px 18px;">
                  <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                      <td width="52" valign="middle">
                        @if($organizer->logo_url)
                          <img src="{{ $organizer->logo_url }}" width="52" height="52" style="border-radius: 14px; border: 1.5px solid #e0d5be; object-fit: cover;">
                        @else
                          <div style="width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, #101828, #344054); border: 1.5px solid #e0d5be; display: inline-block; text-align: center; line-height: 52px; font-size: 20px; font-weight: 900; color: #fff;">
                            {{ substr($organizer->organizer_name ?? 'O', 0, 1) }}
                          </div>
                        @endif
                      </td>
                      <td width="14">&nbsp;</td>
                      <td valign="middle">
                        <div style="font-size: 10px; font-weight: 800; letter-spacing: 1.8px; text-transform: uppercase; color: #9ca3af; margin-bottom: 3px;">Organised by</div>
                        <div style="font-size: 15px; font-weight: 900; color: #101828; margin-bottom: 2px;">{{ $organizer->organizer_name ?? 'LinkUp Organizer' }}</div>
                        <div style="font-size: 12px; color: #667085; font-weight: 600;">⭐ Top Rated</div>
                      </td>
                      <td valign="middle" align="right" class="hide-mobile">
                        <a href="{{ url('/organizers/' . $organizer->id) }}" style="display: inline-block; background: #fff; border: 1.5px solid #e0d5be; color: #344054; text-decoration: none; padding: 8px 18px; border-radius: 10px; font-size: 13px; font-weight: 800;">+ Follow</a>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- CTA -->
        <tr>
          <td style="padding: 4px 28px 28px; text-align: center;">
            <a href="{{ $eventLink }}" style="display: block; background: linear-gradient(135deg, #c0200f, #e02424); color: #fff; text-decoration: none; padding: 18px 32px; border-radius: 16px; font-size: 18px; font-weight: 900; letter-spacing: .2px; margin-bottom: 10px;">🎟️ Get My Tickets</a>
            <div style="font-size: 13px; color: #9ca3af; font-style: italic;">Secure checkout · Powered by LinkUp Wallet</div>
          </td>
        </tr>

        <!-- SPONSOR -->
        @if (!empty($ad))
        <tr>
          <td style="padding: 0 24px 24px;">
            @include('emails.partials.sponsor', ['ad' => $ad])
          </td>
        </tr>
        @endif

        <!-- SAFETY -->
        <tr>
          <td style="padding: 0 24px 24px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td width="33%" valign="top" style="padding: 0 6px;" class="stack-mobile">
                  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #f9fafb; border: 1px solid #f0f0f0; border-radius: 16px;">
                    <tr>
                      <td align="center" style="padding: 18px 14px;">
                        <div style="font-size: 26px; margin-bottom: 8px;">🎟️</div>
                        <div style="font-size: 13px; font-weight: 800; color: #101828; margin-bottom: 5px;">Instant e-ticket</div>
                        <div style="font-size: 12px; color: #667085; line-height: 1.55;">Your ticket is delivered instantly to the app.</div>
                      </td>
                    </tr>
                  </table>
                  <div class="stack-mobile" style="display:none; height:12px;"></div>
                </td>
                <td width="33%" valign="top" style="padding: 0 6px;" class="stack-mobile">
                  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #f9fafb; border: 1px solid #f0f0f0; border-radius: 16px;">
                    <tr>
                      <td align="center" style="padding: 18px 14px;">
                        <div style="font-size: 26px; margin-bottom: 8px;">🎧</div>
                        <div style="font-size: 13px; font-weight: 800; color: #101828; margin-bottom: 5px;">Need help?</div>
                        <div style="font-size: 12px; color: #667085; line-height: 1.55;">We're always here.<br><a href="#" style="color: #126fc9; font-weight: 700; text-decoration: none;">Contact Support</a></div>
                      </td>
                    </tr>
                  </table>
                  <div class="stack-mobile" style="display:none; height:12px;"></div>
                </td>
                <td width="33%" valign="top" style="padding: 0 6px;" class="stack-mobile">
                  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #f9fafb; border: 1px solid #f0f0f0; border-radius: 16px;">
                    <tr>
                      <td align="center" style="padding: 18px 14px;">
                        <div style="font-size: 26px; margin-bottom: 8px;">🔒</div>
                        <div style="font-size: 13px; font-weight: 800; color: #101828; margin-bottom: 5px;">Safe checkout</div>
                        <div style="font-size: 12px; color: #667085; line-height: 1.55;">All payments are encrypted and protected.</div>
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
          <td style="border-top: 1px solid #ede8d4; padding: 26px 36px 34px; text-align: center;">
            <img src="{{ asset('LinkUp Logo_1.png') }}" alt="LinkUp" style="height: 26px; display: block; margin: 0 auto 14px;">
            <div style="font-size: 13px; color: #667085; line-height: 1.6; margin-bottom: 18px;">
              © 2026 LinkUp<br>
              Linking <strong style="color:#126fc9;">Caribbean</strong> &amp; Latin American People <strong style="color:#b07e10;">Everywhere.</strong>
            </div>
            
            <div style="margin-bottom: 18px;">
              <a href="#" style="font-size: 12px; font-weight: 600; color: #126fc9; text-decoration: none; margin: 0 7px;">Manage Preferences</a>
              <a href="#" style="font-size: 12px; font-weight: 600; color: #126fc9; text-decoration: none; margin: 0 7px;">Contact Support</a>
              <a href="#" style="font-size: 12px; font-weight: 600; color: #126fc9; text-decoration: none; margin: 0 7px;">Privacy Policy</a>
            </div>

            <div style="margin-bottom: 18px;">
              <a href="#" style="display: inline-block; width: 34px; height: 34px; line-height: 34px; background: #e1306c; color: #fff; border-radius: 9px; font-size: 13px; font-weight: 900; text-decoration: none; margin: 0 4px;">IG</a>
              <a href="#" style="display: inline-block; width: 34px; height: 34px; line-height: 34px; background: #1877f2; color: #fff; border-radius: 9px; font-size: 13px; font-weight: 900; text-decoration: none; margin: 0 4px;">f</a>
              <a href="#" style="display: inline-block; width: 34px; height: 34px; line-height: 34px; background: #000; color: #fff; border-radius: 9px; font-size: 13px; font-weight: 900; text-decoration: none; margin: 0 4px;">♪</a>
            </div>
            <div style="font-size: 12px; color: #9ca3af; line-height: 1.7;">
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
