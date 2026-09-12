<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Products in the Marketplace — LinkUp</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; background: #ede8dc; font-family: -apple-system, 'Segoe UI', Arial, sans-serif; -webkit-font-smoothing: antialiased; color: #101828; }
        a { text-decoration: none; }
        .wrapper { max-width: 640px; margin: 0 auto; padding: 32px 16px 52px; }
        .card { background: #fffdf8; border-radius: 28px; overflow: hidden; box-shadow: 0 24px 64px rgba(56, 42, 15, .16), 0 0 0 1px #e0d5be; }
        .logo-bar { padding: 30px 36px 22px; text-align: center; border-bottom: 1px solid #f0e8d4; }
        .logo-bar img { height: 36px; display: block; margin: auto; }
        .logo-tagline { margin: 8px 0 0; font-size: 13px; color: #667085; }
        .logo-tagline strong { font-weight: 700; }
        .intro { padding: 36px 40px 24px; }
        .intro-eyebrow { font-size: 11px; font-weight: 800; letter-spacing: 2.5px; text-transform: uppercase; color: #2563eb; margin-bottom: 12px; }
        .intro h1 { font-size: 32px; font-weight: 900; color: #101828; margin: 0; line-height: 1.2; letter-spacing: -.5px; }
        .gold-rule { width: 48px; height: 4px; background: linear-gradient(90deg, #d4af37, #f5d060); border-radius: 4px; margin: 16px 0 18px; }
        .intro p { font-size: 17px; line-height: 1.7; color: #344054; margin: 0; }
        .products-wrap { padding: 0 24px 8px; }
        .product-card { background: #fff; border: 1px solid #ede8dc; border-radius: 20px; overflow: hidden; margin-bottom: 18px; box-shadow: 0 4px 20px rgba(16, 24, 40, .07); }
        .product-img-wrap { position: relative; height: 220px; overflow: hidden; background: #f3f4f6; }
        .product-img-wrap img { width: 100%; height: 220px; object-fit: cover; display: block; }
        .product-body { padding: 18px 20px 20px; }
        .product-name { font-size: 20px; font-weight: 900; color: #101828; margin-bottom: 6px; letter-spacing: -.2px; line-height: 1.3; }
        .product-desc { font-size: 14px; color: #667085; line-height: 1.6; margin-bottom: 16px; }
        .btn-buy { display: block; background: #1d4ed8; color: white !important; text-decoration: none; padding: 13px 20px; border-radius: 12px; font-size: 14px; font-weight: 800; text-align: center; }
        .btn-view { display: block; background: #fff; color: #344054; text-decoration: none; padding: 13px 18px; border-radius: 12px; font-size: 14px; font-weight: 800; text-align: center; border: 1.5px solid #e0d5be; }
        .more-label { text-align: center; padding: 4px 0 18px; }
        .more-label a { font-size: 14px; font-weight: 700; color: #2563eb; text-decoration: none; }
        .footer { border-top: 1px solid #ede8d4; padding: 26px 36px 34px; text-align: center; }
        .footer img { height: 26px; display: block; margin: 0 auto 14px; }
        .footer-tagline { font-size: 13px; color: #667085; line-height: 1.6; margin-bottom: 18px; }
        .footer-links a { font-size: 12px; font-weight: 600; color: #126fc9; text-decoration: none; margin: 0 7px; }
        .footer-copy { font-size: 12px; color: #9ca3af; line-height: 1.7; margin-top:18px;}
        @media (max-width: 560px) {
            .wrapper { padding: 0 0 32px; }
            .card { border-radius: 0; box-shadow: none; }
            .intro { padding: 28px 22px 20px; }
            .intro h1 { font-size: 26px; }
            .products-wrap { padding: 0 16px 8px; }
            .product-img-wrap { height: 180px; }
            .sponsor-stack { display: block !important; width: 100% !important; text-align: center; margin-bottom: 10px; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <!-- LOGO -->
            <div class="logo-bar">
                <img src="{{ rtrim($appUrl ?? '', '/') }}/LinkUp Logo_1.png" alt="LinkUp">
                <p class="logo-tagline">
                    Linking <strong style="color:#126fc9;">Caribbean</strong> &amp;
                    Latin American People <strong style="color:#b07e10;">Everywhere.</strong>
                </p>
                <div style="margin-top:6px; font-size:12px; font-weight:700; color:#667085; letter-spacing:.2px;">
                    <!-- COUNTRY_FLAG --> <!-- COUNTRY_NAME -->
                </div>
            </div>

            <!-- INTRO -->
            <div class="intro">
                <div class="intro-eyebrow">🛍️ New in the Marketplace</div>
                <h1>Fresh picks just dropped</h1>
                <div class="gold-rule"></div>
                <p>Hi there! New products have just been listed by your favorite sellers. Check them out before they're gone.</p>
            </div>

            <!-- PRODUCTS -->
            <div class="products-wrap">
                @if(isset($products) && $products->count() > 0)
                    @foreach($products as $product)
                        @php
                            $seller = $product->user;
                            $sellerName = $seller ? $seller->name : 'Unknown Seller';
                            $sellerInitial = strtoupper(substr($sellerName, 0, 1));
                            $sellerCountry = $seller ? $seller->country : 'Unknown Location';
                            $sellerAvatar = $seller && $seller->avatar ? asset('storage/' . $seller->avatar) : null;
                            $imageUrl = $product->cover_image ? asset('storage/' . $product->cover_image) : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=800&auto=format&fit=crop';
                        @endphp
                        <!-- ── Product ── -->
                        <div class="product-card">
                            <div class="product-img-wrap">
                                <!-- using table to put badge on top -->
                                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="position: absolute; top:0; left:0; width:100%;">
                                    <tr>
                                        <td align="left" valign="top" style="padding:14px;">
                                            <span style="background: #2563eb; color: #fff; font-size: 10px; font-weight: 900; letter-spacing: 1.5px; text-transform: uppercase; padding: 5px 12px; border-radius: 7px;">New</span>
                                        </td>
                                        <td align="right" valign="top" style="padding:14px;">
                                            <span style="background: rgba(255, 255, 255, .96); border-radius: 100px; padding: 6px 14px; font-size: 15px; font-weight: 900; color: #101828;"><span style="font-size: 11px; color: #667085;"><!-- COUNTRY_CURRENCY --> </span>${{ number_format($product->price, 2) }}</span>
                                        </td>
                                    </tr>
                                </table>
                                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" style="width: 100%; height: 220px; object-fit: cover;">
                            </div>
                            <div class="product-body">
                                <div class="product-name">{{ $product->name }}</div>
                                <div class="product-desc">{{ Str::limit(strip_tags($product->description), 100) }}</div>

                                <!-- SELLER ROW TABLE -->
                                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #f9f8f5; border: 1px solid #ede8dc; border-radius: 12px; margin-bottom: 16px;">
                                    <tr>
                                        <td width="36" valign="middle" style="padding: 10px 0 10px 14px;">
                                            @if($sellerAvatar)
                                                <img src="{{ $sellerAvatar }}" alt="{{ $sellerName }}" style="width:36px; height:36px; border-radius:50%; display:block; border: 1.5px solid #e0d5be;">
                                            @else
                                                <div style="width:36px; height:36px; border-radius:50%; background:#126fc9; color:#fff; text-align:center; line-height:36px; font-size:14px; font-weight:bold; border: 1.5px solid #e0d5be;">{{ $sellerInitial }}</div>
                                            @endif
                                        </td>
                                        <td valign="middle" style="padding: 10px 14px;">
                                            <div style="font-size: 10px; font-weight: 800; letter-spacing: 1.5px; text-transform: uppercase; color: #9ca3af; margin-bottom: 2px;">Sold by</div>
                                            <div style="font-size: 14px; font-weight: 800; color: #101828;">{{ $sellerName }}</div>
                                            <div style="font-size: 12px; color: #667085; font-weight: 600; margin-top: 1px;">📍 {{ $sellerCountry }}</div>
                                        </td>
                                        <td width="50" valign="middle" align="right" style="padding: 10px 14px 10px 0;">
                                            <div style="font-size: 12px; font-weight: 700; color: #b07e10;">⭐ 5.0</div>
                                        </td>
                                    </tr>
                                </table>

                                <!-- PRODUCT META -->
                                <div style="margin-bottom: 16px;">
                                    @if($product->category)
                                    <span style="display:inline-block; background: #f0f4ff; border: 1px solid #c7d7fd; color: #1d4ed8; font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 100px; margin-right: 4px; margin-bottom: 4px;">{{ $product->category->name }}</span>
                                    @endif
                                    <span style="display:inline-block; background: #f0f4ff; border: 1px solid #c7d7fd; color: #1d4ed8; font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 100px; margin-right: 4px; margin-bottom: 4px;">✅ In Stock</span>
                                </div>

                                <!-- BUY ROW TABLE -->
                                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td width="50%" style="padding-right: 5px;">
                                            <a href="{{ rtrim($appUrl ?? '', '/') }}/products/{{ $product->id }}" class="btn-buy">🛒 Buy Now</a>
                                        </td>
                                        <td width="50%" style="padding-left: 5px;">
                                            <a href="{{ rtrim($appUrl ?? '', '/') }}/products/{{ $product->id }}" class="btn-view">View Details</a>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p style="text-align:center; color:#667085; padding: 20px 0;">No new products at the moment.</p>
                @endif

                <div class="more-label">
                    <a href="{{ rtrim($appUrl ?? '', '/') }}/marketplace">Browse all new listings in the Marketplace →</a>
                </div>
            </div>

            <!-- SPONSOR SLOT TABLE -->
            @if (!empty($ad))
                @include('emails.partials.sponsor', ['ad' => $ad])
            @endif

            <!-- SAFETY TABLE -->
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 0 auto 24px; max-width: 592px;">
                <tr>
                    <td style="padding: 0 24px;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td width="33%" valign="top" style="padding: 0 6px;" class="sponsor-stack">
                                    <div style="background: #f9fafb; border: 1px solid #f0f0f0; border-radius: 16px; padding: 18px 14px; text-align: center;">
                                        <span style="font-size: 26px; margin-bottom: 8px; display: block;">🛡️</span>
                                        <span style="font-size: 13px; font-weight: 800; color: #101828; margin-bottom: 5px; display: block;">Buyer protected</span>
                                        <div style="font-size: 12px; color: #667085; line-height: 1.55;">All purchases are covered by LinkUp Buyer Protection.</div>
                                    </div>
                                </td>
                                <td width="33%" valign="top" style="padding: 0 6px;" class="sponsor-stack">
                                    <div style="background: #f9fafb; border: 1px solid #f0f0f0; border-radius: 16px; padding: 18px 14px; text-align: center;">
                                        <span style="font-size: 26px; margin-bottom: 8px; display: block;">🎧</span>
                                        <span style="font-size: 13px; font-weight: 800; color: #101828; margin-bottom: 5px; display: block;">Need help?</span>
                                        <div style="font-size: 12px; color: #667085; line-height: 1.55;">We're always here.<br><a href="mailto:<!-- COUNTRY_SUPPORT -->" style="color: #126fc9; font-weight: 700; text-decoration: none;">Contact Support</a></div>
                                    </div>
                                </td>
                                <td width="33%" valign="top" style="padding: 0 6px;" class="sponsor-stack">
                                    <div style="background: #f9fafb; border: 1px solid #f0f0f0; border-radius: 16px; padding: 18px 14px; text-align: center;">
                                        <span style="font-size: 26px; margin-bottom: 8px; display: block;">🚩</span>
                                        <span style="font-size: 13px; font-weight: 800; color: #101828; margin-bottom: 5px; display: block;">Report a listing</span>
                                        <div style="font-size: 12px; color: #667085; line-height: 1.55;">Spot something suspicious? Report it in the app.</div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- FOOTER -->
            <div class="footer">
                <img src="{{ rtrim($appUrl ?? '', '/') }}/LinkUp Logo_1.png" alt="LinkUp">
                <div class="footer-tagline">
                    © 2026 LinkUp <!-- COUNTRY_FLAG --> <!-- COUNTRY_NAME --><br>
                    Linking <strong style="color:#126fc9;">Caribbean</strong> &amp;
                    Latin American People <strong style="color:#b07e10;">Everywhere.</strong>
                </div>
                <div class="footer-links">
                    <a href="#">Manage Preferences</a>
                    <a href="mailto:<!-- COUNTRY_SUPPORT -->">Contact Support</a>
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
                <div style="margin-top: 18px;">
                    <a href="#" style="display:inline-block; background:#e1306c; color:#fff; width:34px; height:34px; line-height:34px; border-radius:9px; font-weight:bold; font-size:13px; margin:0 4px; text-align:center;">IG</a>
                    <a href="#" style="display:inline-block; background:#1877f2; color:#fff; width:34px; height:34px; line-height:34px; border-radius:9px; font-weight:bold; font-size:13px; margin:0 4px; text-align:center;">f</a>
                    <a href="#" style="display:inline-block; background:#000; color:#fff; width:34px; height:34px; line-height:34px; border-radius:9px; font-weight:bold; font-size:13px; margin:0 4px; text-align:center;">♪</a>
                </div>
                <div class="footer-copy">Follow us @linkupcaribbean</div>
            </div>

        </div>
    </div>
</body>
</html>
