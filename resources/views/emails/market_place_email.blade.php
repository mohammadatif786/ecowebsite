<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LinkUp Marketplace</title>
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

            .pad {
                padding-left: 16px !important;
                padding-right: 16px !important;
            }

            .hero-pad {
                padding: 26px 18px !important;
            }

            .hero-h1 {
                font-size: 26px !important;
            }

            .col {
                display: block !important;
                width: 100% !important;
            }

            .spacer {
                height: 12px !important;
            }

            .btn {
                display: block !important;
                width: 100% !important;
                text-align: center !important;
            }
        }
    </style>
</head>

<body
    style="margin:0;padding:0;background-color:#ede8dc;font-family:-apple-system,'Segoe UI',Arial,sans-serif;-webkit-font-smoothing:antialiased;color:#101828;">

    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%"
        style="background-color:#ede8dc;">
        <tr>
            <td align="center" class="wrapper" style="padding:32px 16px 52px;">

                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="640"
                    style="width:100%;max-width:640px;background-color:#fffdf8;border-radius:28px;overflow:hidden;box-shadow:0 24px 64px rgba(56,42,15,.16);"
                    class="card">

                    <!-- HEADER -->
                    <tr>
                        <td style="padding:26px 32px 18px;border-bottom:1px solid #f0e8d4;" class="pad">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td style="vertical-align:middle;">
                                        <img src="{{ $logoUrl ?? asset('images/logo.png') }}" alt="LinkUp"
                                            style="height:32px;display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;">
                                    </td>
                                    <td align="right" style="vertical-align:middle;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                            style="background:#f5f3ff;border:1px solid #ddd6fe;border-radius:999px;">
                                            <tr>
                                                <td
                                                    style="padding:6px 12px;font-size:12px;font-weight:900;color:#5b21b6;white-space:nowrap;">
                                                    🛍️ Marketplace
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%"
                                style="margin-top:14px;">
                                <tr>
                                    <td style="background:#eef4ff;border-radius:10px;padding:10px 12px;" class="col">
                                        <span style="font-size:12px;font-weight:900;color:#101828;">Safe &amp;
                                            Trusted</span>
                                        <span style="font-size:12px;color:#667085;">Verified sellers</span>
                                    </td>
                                    <td width="10" style="width:10px;font-size:0;line-height:0;" class="col">
                                        &nbsp;</td>
                                    <td style="background:#eefbe8;border-radius:10px;padding:10px 12px;" class="col">
                                        <span style="font-size:12px;font-weight:900;color:#101828;">Fast Delivery</span>
                                        <span style="font-size:12px;color:#667085;">Across the region</span>
                                    </td>
                                    <td width="10" style="width:10px;font-size:0;line-height:0;" class="col">
                                        &nbsp;</td>
                                    <td style="background:#fff3e0;border-radius:10px;padding:10px 12px;" class="col">
                                        <span style="font-size:12px;font-weight:900;color:#101828;">Secure
                                            Payments</span>
                                        <span style="font-size:12px;color:#667085;">Pay with Wallet</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- HERO -->
                    <tr>
                        <td style="padding:34px 32px;background:linear-gradient(135deg,#1e3a8a 0%,#1d4ed8 55%,#0ea5e9 100%);"
                            class="hero-pad pad">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <!-- Left -->
                                    <td style="vertical-align:top;" class="col">
                                        <div
                                            style="font-size:10px;font-weight:900;letter-spacing:2.5px;text-transform:uppercase;color:rgba(147,197,253,.9);padding-bottom:10px;">
                                            LinkUp Marketplace</div>
                                        <div style="font-size:32px;font-weight:900;color:#ffffff;line-height:1.1;letter-spacing:-.5px;padding-bottom:8px;"
                                            class="hero-h1">Shop Local.<br>Support Community.</div>
                                        <div
                                            style="font-size:16px;font-style:italic;font-family:Georgia,serif;color:#fde68a;padding-bottom:14px;">
                                            Find Everything You Need</div>
                                        <div
                                            style="font-size:14px;color:rgba(255,255,255,.78);line-height:1.65;padding-bottom:18px;">
                                            Browse thousands of products from trusted sellers across the Caribbean &amp;
                                            Latin America.</div>

                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td
                                                    style="background-color:#16a34a;border-radius:10px;box-shadow:0 4px 16px rgba(22,163,74,.32);">
                                                    <a href="{{ $appUrl ?? '/' }}" class="btn"
                                                        style="display:inline-block;color:#ffffff;text-decoration:none;padding:12px 20px;border-radius:10px;font-size:14px;font-weight:900;">Shop
                                                        Marketplace →</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>

                                    <td width="16" style="width:16px;font-size:0;line-height:0;" class="col">
                                        &nbsp;</td>

                                    <!-- Right tiles -->
                                    <td width="170" style="width:170px;vertical-align:top;" class="col">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                            width="100%"
                                            style="background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.22);border-radius:16px;">
                                            <tr>
                                                <td style="padding:12px;">
                                                    <table role="presentation" cellpadding="0" cellspacing="0"
                                                        border="0" width="100%">
                                                        <tr>
                                                            <td
                                                                style="background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.20);border-radius:12px;padding:12px 10px;text-align:center;">
                                                                <div style="font-size:20px;">👕</div>
                                                                <div
                                                                    style="font-size:10px;font-weight:800;color:rgba(255,255,255,.85);">
                                                                    Fashion</div>
                                                            </td>
                                                            <td width="8"
                                                                style="width:8px;font-size:0;line-height:0;">&nbsp;
                                                            </td>
                                                            <td
                                                                style="background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.20);border-radius:12px;padding:12px 10px;text-align:center;">
                                                                <div style="font-size:20px;">🧴</div>
                                                                <div
                                                                    style="font-size:10px;font-weight:800;color:rgba(255,255,255,.85);">
                                                                    Beauty</div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td height="8"
                                                                style="height:8px;font-size:0;line-height:0;"
                                                                colspan="3">&nbsp;</td>
                                                        </tr>
                                                        <tr>
                                                            <td
                                                                style="background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.20);border-radius:12px;padding:12px 10px;text-align:center;">
                                                                <div style="font-size:20px;">🎧</div>
                                                                <div
                                                                    style="font-size:10px;font-weight:800;color:rgba(255,255,255,.85);">
                                                                    Tech</div>
                                                            </td>
                                                            <td width="8"
                                                                style="width:8px;font-size:0;line-height:0;">&nbsp;
                                                            </td>
                                                            <td
                                                                style="background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.20);border-radius:12px;padding:12px 10px;text-align:center;">
                                                                <div style="font-size:20px;">🛋️</div>
                                                                <div
                                                                    style="font-size:10px;font-weight:800;color:rgba(255,255,255,.85);">
                                                                    Home</div>
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

                    <!-- FEATURED COLLECTIONS -->
                    <tr>
                        <td style="padding:20px 24px 8px;" class="pad">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                width="100%" style="border-bottom:1px solid #f0e8d4;">
                                <tr>
                                    <td style="padding:0 0 12px;font-size:15px;font-weight:900;color:#101828;">⭐
                                        Featured Collections</td>
                                    <td align="right" style="padding:0 0 12px;">
                                        <a href="{{ $appUrl ?? '/' }}"
                                            style="font-size:13px;font-weight:800;color:#6d28d9;text-decoration:none;">View
                                            all →</a>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                width="100%" style="margin-top:14px;">
                                <tr>
                                    <td style="background:#ffffff;border:1px solid #ede8dc;border-radius:16px;overflow:hidden;"
                                        class="col">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                            width="100%">
                                            <tr>
                                                <td
                                                    style="height:78px;background:linear-gradient(135deg,#0ea5e9,#f59e0b);padding:12px;vertical-align:bottom;">
                                                    <span
                                                        style="font-size:14px;font-weight:900;color:#ffffff;">Caribbean
                                                        Style</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:12px;">
                                                    <div
                                                        style="font-size:13px;color:#667085;line-height:1.5;padding-bottom:10px;">
                                                        Vibrant looks for every season.</div>
                                                    <a href="{{ $appUrl ?? '/' }}"
                                                        style="font-size:13px;font-weight:800;color:#6d28d9;text-decoration:none;">Shop
                                                        Now →</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>

                                    <td width="12" style="width:12px;font-size:0;line-height:0;" class="col">
                                        &nbsp;</td>

                                    <td style="background:#ffffff;border:1px solid #ede8dc;border-radius:16px;overflow:hidden;"
                                        class="col">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                            width="100%">
                                            <tr>
                                                <td
                                                    style="height:78px;background:linear-gradient(135deg,#d4af37,#92400e);padding:12px;vertical-align:bottom;">
                                                    <span style="font-size:14px;font-weight:900;color:#ffffff;">Island
                                                        Essentials</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:12px;">
                                                    <div
                                                        style="font-size:13px;color:#667085;line-height:1.5;padding-bottom:10px;">
                                                        Everything you need for daily living.</div>
                                                    <a href="{{ $appUrl ?? '/' }}"
                                                        style="font-size:13px;font-weight:800;color:#6d28d9;text-decoration:none;">Shop
                                                        Now →</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                width="100%" style="margin-top:12px;">
                                <tr>
                                    <td style="background:#ffffff;border:1px solid #ede8dc;border-radius:16px;overflow:hidden;"
                                        class="col">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                            width="100%">
                                            <tr>
                                                <td
                                                    style="height:78px;background:linear-gradient(135deg,#111827,#374151);padding:12px;vertical-align:bottom;">
                                                    <span style="font-size:14px;font-weight:900;color:#ffffff;">Tech
                                                        Picks</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:12px;">
                                                    <div
                                                        style="font-size:13px;color:#667085;line-height:1.5;padding-bottom:10px;">
                                                        The latest gadgets at great prices.</div>
                                                    <a href="{{ $appUrl ?? '/' }}"
                                                        style="font-size:13px;font-weight:800;color:#6d28d9;text-decoration:none;">Shop
                                                        Now →</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>

                                    <td width="12" style="width:12px;font-size:0;line-height:0;" class="col">
                                        &nbsp;</td>

                                    <td style="background:#ffffff;border:1px solid #ede8dc;border-radius:16px;overflow:hidden;"
                                        class="col">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                            width="100%">
                                            <tr>
                                                <td
                                                    style="height:78px;background:linear-gradient(135deg,#ec4899,#fde68a);padding:12px;vertical-align:bottom;">
                                                    <span
                                                        style="font-size:14px;font-weight:900;color:#ffffff;">Wellness
                                                        &amp; Self Care</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:12px;">
                                                    <div
                                                        style="font-size:13px;color:#667085;line-height:1.5;padding-bottom:10px;">
                                                        For a healthier, happier you.</div>
                                                    <a href="{{ $appUrl ?? '/' }}"
                                                        style="font-size:13px;font-weight:800;color:#6d28d9;text-decoration:none;">Shop
                                                        Now →</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- TOP PICKS -->
                    <tr>
                        <td style="padding:16px 24px 8px;" class="pad">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                width="100%" style="border-bottom:1px solid #f0e8d4;">
                                <tr>
                                    <td style="padding:0 0 12px;font-size:15px;font-weight:900;color:#101828;">🔥 Top
                                        Picks For You</td>
                                    <td align="right" style="padding:0 0 12px;">
                                        <a href="{{ $appUrl ?? '/' }}"
                                            style="font-size:13px;font-weight:800;color:#6d28d9;text-decoration:none;">View
                                            all →</a>
                                    </td>
                                </tr>
                            </table>

                            <!-- 2x2 product grid using tables -->
                            @if ($topPicks && $topPicks->isNotEmpty())
                                @php $chunks = $topPicks->chunk(2); @endphp
                                @foreach ($chunks as $index => $chunk)
                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                        width="100%" style="margin-top:{{ $index == 0 ? '14px' : '12px' }};">
                                        <tr>
                                            @foreach ($chunk as $product)
                                                <td style="background:#ffffff;border:1px solid #ede8dc;border-radius:16px;overflow:hidden;"
                                                    class="col">
                                                    <table role="presentation" cellpadding="0" cellspacing="0"
                                                        border="0" width="100%">
                                                        <tr>
                                                            <td
                                                                style="height:110px;{{ $product->cover_image ? 'background-image:url(' . $product->cover_image . ');background-size:cover;background-position:center;' : 'background:#f3f4f6;' }}text-align:center;vertical-align:middle;font-size:46px;">
                                                                @if (!$product->cover_image)
                                                                    🛍️
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding:12px;">
                                                                <div
                                                                    style="font-size:13px;font-weight:900;color:#101828;line-height:1.35;padding-bottom:8px;">
                                                                    {{ Str::limit($product->name, 30) }}</div>
                                                                <table role="presentation" cellpadding="0"
                                                                    cellspacing="0" border="0" width="100%"
                                                                    style="padding-bottom:10px;">
                                                                    <tr>
                                                                        <td
                                                                            style="font-size:15px;font-weight:900;color:#16a34a;">
                                                                            ${{ number_format($product->price, 2) }}
                                                                        </td>
                                                                        <td align="right"
                                                                            style="font-size:12px;color:#667085;">⭐
                                                                            {{ $product->rating ?? '4.5' }}</td>
                                                                    </tr>
                                                                </table>
                                                                <table role="presentation" cellpadding="0"
                                                                    cellspacing="0" border="0" width="100%">
                                                                    <tr>
                                                                        <td
                                                                            style="background:#6d28d9;border-radius:9px;text-align:center;">
                                                                            <a href="{{ $appUrl ?? '/' }}/products/{{ $product->id }}"
                                                                                style="display:block;color:#ffffff;text-decoration:none;padding:9px;border-radius:9px;font-size:12px;font-weight:900;">Add
                                                                                to Cart</a>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                @if (!$loop->last)
                                                    <td width="12" style="width:12px;font-size:0;line-height:0;"
                                                        class="col">&nbsp;</td>
                                                @endif
                                            @endforeach
                                        </tr>
                                    </table>
                                @endforeach
                            @else
                                <!-- Fallback: Show default products when no data -->
                                <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                    width="100%" style="margin-top:14px;">
                                    <tr>
                                        <td style="background:#ffffff;border:1px solid #ede8dc;border-radius:16px;overflow:hidden;"
                                            class="col">
                                            <table role="presentation" cellpadding="0" cellspacing="0"
                                                border="0" width="100%">
                                                <tr>
                                                    <td
                                                        style="height:110px;background:#fef9c3;text-align:center;vertical-align:middle;font-size:46px;">
                                                        �</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:12px;">
                                                        <div
                                                            style="font-size:13px;font-weight:900;color:#101828;line-height:1.35;padding-bottom:8px;">
                                                            Island Vibes Graphic Tee</div>
                                                        <table role="presentation" cellpadding="0" cellspacing="0"
                                                            border="0" width="100%"
                                                            style="padding-bottom:10px;">
                                                            <tr>
                                                                <td
                                                                    style="font-size:15px;font-weight:900;color:#16a34a;">
                                                                    $24.99</td>
                                                                <td align="right"
                                                                    style="font-size:12px;color:#667085;">⭐ 4.8</td>
                                                            </tr>
                                                        </table>
                                                        <table role="presentation" cellpadding="0" cellspacing="0"
                                                            border="0" width="100%">
                                                            <tr>
                                                                <td
                                                                    style="background:#6d28d9;border-radius:9px;text-align:center;">
                                                                    <a href="{{ $appUrl ?? '/' }}"
                                                                        style="display:block;color:#ffffff;text-decoration:none;padding:9px;border-radius:9px;font-size:12px;font-weight:900;">Add
                                                                        to Cart</a>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td width="12" style="width:12px;font-size:0;line-height:0;"
                                            class="col">&nbsp;</td>
                                        <td style="background:#ffffff;border:1px solid #ede8dc;border-radius:16px;overflow:hidden;"
                                            class="col">
                                            <table role="presentation" cellpadding="0" cellspacing="0"
                                                border="0" width="100%">
                                                <tr>
                                                    <td
                                                        style="height:110px;background:#eff6ff;text-align:center;vertical-align:middle;font-size:46px;">
                                                        🧴</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:12px;">
                                                        <div
                                                            style="font-size:13px;font-weight:900;color:#101828;line-height:1.35;padding-bottom:8px;">
                                                            Olivelé Radiance Face Serum</div>
                                                        <table role="presentation" cellpadding="0" cellspacing="0"
                                                            border="0" width="100%"
                                                            style="padding-bottom:10px;">
                                                            <tr>
                                                                <td
                                                                    style="font-size:15px;font-weight:900;color:#16a34a;">
                                                                    $32.00</td>
                                                                <td align="right"
                                                                    style="font-size:12px;color:#667085;">⭐ 4.9</td>
                                                            </tr>
                                                        </table>
                                                        <table role="presentation" cellpadding="0" cellspacing="0"
                                                            border="0" width="100%">
                                                            <tr>
                                                                <td
                                                                    style="background:#6d28d9;border-radius:9px;text-align:center;">
                                                                    <a href="{{ $appUrl ?? '/' }}"
                                                                        style="display:block;color:#ffffff;text-decoration:none;padding:9px;border-radius:9px;font-size:12px;font-weight:900;">Add
                                                                        to Cart</a>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            @endif
                        </td>
                    </tr>

                    <!-- POPULAR SELLERS -->
                    <tr>
                        <td style="padding:16px 24px 8px;" class="pad">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                width="100%" style="border-bottom:1px solid #f0e8d4;">
                                <tr>
                                    <td style="padding:0 0 12px;font-size:15px;font-weight:900;color:#101828;">🏪
                                        Popular Sellers</td>
                                    <td align="right" style="padding:0 0 12px;">
                                        <a href="{{ $appUrl ?? '/' }}"
                                            style="font-size:13px;font-weight:800;color:#6d28d9;text-decoration:none;">View
                                            all →</a>
                                    </td>
                                </tr>
                            </table>

                            @if ($popularSellers && $popularSellers->isNotEmpty())
                                @php $chunks = $popularSellers->chunk(2); @endphp
                                @foreach ($chunks as $index => $chunk)
                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                        width="100%" style="margin-top:{{ $index == 0 ? '14px' : '12px' }};">
                                        <tr>
                                            @foreach ($chunk as $seller)
                                                <td style="background:#ffffff;border:1px solid #ede8dc;border-radius:14px;padding:14px;"
                                                    class="col">
                                                    <table role="presentation" cellpadding="0" cellspacing="0"
                                                        border="0" width="100%">
                                                        <tr>
                                                            <td width="44" style="width:44px;vertical-align:top;">
                                                                @if ($seller->avatar)
                                                                    <img src="{{ $seller->avatar }}"
                                                                        alt="{{ $seller->name }}"
                                                                        style="width:42px;height:42px;border-radius:10px;object-fit:cover;">
                                                                @else
                                                                    <div
                                                                        style="width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,#6d28d9,#4c1d95);color:#ffffff;font-size:12px;font-weight:900;line-height:42px;text-align:center;">
                                                                        {{ strtoupper(substr($seller->name, 0, 2)) }}
                                                                    </div>
                                                                @endif
                                                            </td>
                                                            <td style="padding-left:12px;vertical-align:top;">
                                                                <div
                                                                    style="font-size:13px;font-weight:900;color:#101828;padding-bottom:2px;">
                                                                    {{ $seller->name }}</div>
                                                                <div
                                                                    style="font-size:11px;color:#667085;padding-bottom:3px;">
                                                                    {{ $seller->main_category }}</div>
                                                                <div
                                                                    style="font-size:11px;color:#f59e0b;font-weight:800;">
                                                                    ⭐ {{ $seller->rating ?? '4.8' }} ·
                                                                    {{ number_format($seller->follower_count) }}
                                                                    followers</div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                @if (!$loop->last)
                                                    <td width="12" style="width:12px;font-size:0;line-height:0;"
                                                        class="col">&nbsp;</td>
                                                @endif
                                            @endforeach
                                        </tr>
                                    </table>
                                @endforeach
                            @else
                                <!-- Fallback: Show default sellers when no data -->
                                <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                    width="100%" style="margin-top:14px;">
                                    <tr>
                                        <td style="background:#ffffff;border:1px solid #ede8dc;border-radius:14px;padding:14px;"
                                            class="col">
                                            <table role="presentation" cellpadding="0" cellspacing="0"
                                                border="0" width="100%">
                                                <tr>
                                                    <td width="44" style="width:44px;vertical-align:top;">
                                                        <div
                                                            style="width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,#6d28d9,#4c1d95);color:#ffffff;font-size:12px;font-weight:900;line-height:42px;text-align:center;">
                                                            IT</div>
                                                    </td>
                                                    <td style="padding-left:12px;vertical-align:top;">
                                                        <div
                                                            style="font-size:13px;font-weight:900;color:#101828;padding-bottom:2px;">
                                                            Island Threads</div>
                                                        <div style="font-size:11px;color:#667085;padding-bottom:3px;">
                                                            Fashion &amp; Apparel</div>
                                                        <div style="font-size:11px;color:#f59e0b;font-weight:800;">⭐
                                                            4.9 · 1.2K followers</div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td width="12" style="width:12px;font-size:0;line-height:0;"
                                            class="col">&nbsp;</td>
                                        <td style="background:#ffffff;border:1px solid #ede8dc;border-radius:14px;padding:14px;"
                                            class="col">
                                            <table role="presentation" cellpadding="0" cellspacing="0"
                                                border="0" width="100%">
                                                <tr>
                                                    <td width="44" style="width:44px;vertical-align:top;">
                                                        <div
                                                            style="width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,#6d28d9,#4c1d95);color:#ffffff;font-size:12px;font-weight:900;line-height:42px;text-align:center;">
                                                            OC</div>
                                                    </td>
                                                    <td style="padding-left:12px;vertical-align:top;">
                                                        <div
                                                            style="font-size:13px;font-weight:900;color:#101828;padding-bottom:2px;">
                                                            Olivelé Care</div>
                                                        <div style="font-size:11px;color:#667085;padding-bottom:3px;">
                                                            Beauty &amp; Wellness</div>
                                                        <div style="font-size:11px;color:#f59e0b;font-weight:800;">⭐
                                                            4.9 · 980 followers</div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            @endif
                        </td>
                    </tr>

                    <!-- SPONSOR -->
                    @if (!empty($ad))
                        @include('emails.partials.sponsor', ['ad' => $ad])
                    @endif

                    <!-- SHOP WITH CONFIDENCE -->
                    <tr>
                        <td style="padding:16px 24px 8px;" class="pad">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                width="100%" style="border-bottom:1px solid #f0e8d4;">
                                <tr>
                                    <td style="padding:0 0 12px;font-size:15px;font-weight:900;color:#101828;">🛡️ Shop
                                        with Confidence</td>
                                </tr>
                            </table>

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                width="100%" style="margin-top:14px;">
                                <tr>
                                    <td style="background:#f9f8f5;border:1px solid #ede8dc;border-radius:14px;padding:16px;"
                                        class="col">
                                        <div style="font-size:22px;padding-bottom:7px;">💖</div>
                                        <div style="font-size:13px;font-weight:900;color:#101828;padding-bottom:4px;">
                                            Verified Sellers</div>
                                        <div style="font-size:12px;color:#667085;line-height:1.55;">Every seller is
                                            checked before listing.</div>
                                    </td>
                                    <td width="12" style="width:12px;font-size:0;line-height:0;" class="col">
                                        &nbsp;</td>
                                    <td style="background:#f9f8f5;border:1px solid #ede8dc;border-radius:14px;padding:16px;"
                                        class="col">
                                        <div style="font-size:22px;padding-bottom:7px;">💳</div>
                                        <div style="font-size:13px;font-weight:900;color:#101828;padding-bottom:4px;">
                                            Secure Payments</div>
                                        <div style="font-size:12px;color:#667085;line-height:1.55;">Protected by LinkUp
                                            Wallet encryption.</div>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                width="100%" style="margin-top:12px;">
                                <tr>
                                    <td style="background:#f9f8f5;border:1px solid #ede8dc;border-radius:14px;padding:16px;"
                                        class="col">
                                        <div style="font-size:22px;padding-bottom:7px;">🛡️</div>
                                        <div style="font-size:13px;font-weight:900;color:#101828;padding-bottom:4px;">
                                            Buyer Protection</div>
                                        <div style="font-size:12px;color:#667085;line-height:1.55;">Something wrong?
                                            We've got your back.</div>
                                    </td>
                                    <td width="12" style="width:12px;font-size:0;line-height:0;" class="col">
                                        &nbsp;</td>
                                    <td style="background:#f9f8f5;border:1px solid #ede8dc;border-radius:14px;padding:16px;"
                                        class="col">
                                        <div style="font-size:22px;padding-bottom:7px;">↩️</div>
                                        <div style="font-size:13px;font-weight:900;color:#101828;padding-bottom:4px;">
                                            Easy Returns</div>
                                        <div style="font-size:12px;color:#667085;line-height:1.55;">Hassle-free returns
                                            on eligible items.</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- APP PROMO -->
                    <tr>
                        <td style="padding:12px 24px 18px;" class="pad">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                width="100%"
                                style="background:linear-gradient(135deg,#eef4ff,#dbeafe);border:1px solid #bfdbfe;border-radius:20px;overflow:hidden;">
                                <tr>
                                    <td style="padding:20px;">
                                        <div
                                            style="font-size:17px;font-weight:900;color:#101828;line-height:1.3;padding-bottom:8px;">
                                            Everything you need.<br>
                                            <span style="color:#1d4ed8;">All in the LinkUp App.</span>
                                        </div>
                                        <div style="font-size:13px;color:#374151;line-height:1.8;padding-bottom:14px;">
                                            ✓ Marketplace &nbsp; ✓ Wallet<br>
                                            ✓ Events &nbsp; ✓ Matches
                                        </div>

                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                            width="100%">
                                            <tr>
                                                <td style="background:#101828;border-radius:8px;text-align:center;">
                                                    <a href="{{ $appUrl ?? '/' }}"
                                                        style="display:block;color:#ffffff;text-decoration:none;padding:10px 12px;border-radius:8px;font-size:12px;font-weight:900;">↓
                                                        &nbsp; Download on the App Store</a>
                                                </td>
                                            </tr>
                                        </table>

                                        <div style="height:8px;font-size:0;line-height:0;">&nbsp;</div>

                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                            width="100%">
                                            <tr>
                                                <td style="background:#101828;border-radius:8px;text-align:center;">
                                                    <a href="{{ $appUrl ?? '/' }}"
                                                        style="display:block;color:#ffffff;text-decoration:none;padding:10px 12px;border-radius:8px;font-size:12px;font-weight:900;">↓
                                                        &nbsp; Get it on Google Play</a>
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
                            class="pad">
                            <img src="{{ $logoUrl ?? asset('images/logo.png') }}" alt="LinkUp"
                                style="height:26px;display:block;margin:0 auto 14px;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;">
                            <p style="margin:0 0 18px;font-size:13px;color:#667085;line-height:1.6;">
                                © 2026 LinkUp — Nassau, Bahamas<br>
                                Linking <strong style="color:#126fc9;">Caribbean</strong> &amp; Latin American People
                                <strong style="color:#b07e10;">Everywhere.</strong>
                            </p>

                            <p style="margin:0 0 14px;font-size:12px;line-height:1.7;">
                                <a href="{{ $appUrl ?? '#' }}"
                                    style="color:#126fc9;text-decoration:none;font-weight:700;">Manage Preferences</a>
                                &nbsp;&nbsp;
                                <a href="{{ $appUrl ?? '#' }}"
                                    style="color:#126fc9;text-decoration:none;font-weight:700;">Help Center</a>
                                &nbsp;&nbsp;
                                <a href="{{ $appUrl ?? '#' }}"
                                    style="color:#126fc9;text-decoration:none;font-weight:700;">Privacy Policy</a>
                                &nbsp;&nbsp;
                                <a href="{{ $appUrl ?? '#' }}"
                                    style="color:#126fc9;text-decoration:none;font-weight:700;">Terms of Service</a>
                                &nbsp;&nbsp;
                                <a href="{{ $appUrl ?? '#' }}"
                                    style="color:#126fc9;text-decoration:none;font-weight:700;">Unsubscribe</a>
                            </p>

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                align="center" style="margin:0 auto 14px;">
                                <tr>
                                    <td style="background:#e1306c;border-radius:9px;">
                                        <a href="{{ $appUrl ?? '#' }}"
                                            style="display:inline-block;width:34px;height:34px;line-height:34px;text-align:center;color:#ffffff;text-decoration:none;font-size:13px;font-weight:900;border-radius:9px;">IG</a>
                                    </td>
                                    <td width="8" style="width:8px;font-size:0;line-height:0;">&nbsp;</td>
                                    <td style="background:#1877f2;border-radius:9px;">
                                        <a href="{{ $appUrl ?? '#' }}"
                                            style="display:inline-block;width:34px;height:34px;line-height:34px;text-align:center;color:#ffffff;text-decoration:none;font-size:13px;font-weight:900;border-radius:9px;">f</a>
                                    </td>
                                    <td width="8" style="width:8px;font-size:0;line-height:0;">&nbsp;</td>
                                    <td style="background:#000000;border-radius:9px;">
                                        <a href="{{ $appUrl ?? '#' }}"
                                            style="display:inline-block;width:34px;height:34px;line-height:34px;text-align:center;color:#ffffff;text-decoration:none;font-size:13px;font-weight:900;border-radius:9px;">♪</a>
                                    </td>
                                    <td width="8" style="width:8px;font-size:0;line-height:0;">&nbsp;</td>
                                    <td style="background:#ff0000;border-radius:9px;">
                                        <a href="{{ $appUrl ?? '#' }}"
                                            style="display:inline-block;width:34px;height:34px;line-height:34px;text-align:center;color:#ffffff;text-decoration:none;font-size:13px;font-weight:900;border-radius:9px;">▶</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0;font-size:12px;color:#9ca3af;line-height:1.7;">You're receiving this
                                because you're a LinkUp member.</p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>
