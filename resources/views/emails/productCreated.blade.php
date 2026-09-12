<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Product</title>
</head>

<body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f7f7f7; color:#333;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f7f7f7; padding:20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08);">

                    <tr>
                        <td style="background:#111827; padding:18px 20px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="left" style="color:#fff; font-size:18px; font-weight:bold;">
                                        LinkUp Shop
                                    </td>
                                    <td align="right" style="color:#9ca3af; font-size:12px;">
                                        New Product
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px;">
                            @php
                                $name = $product->name ?? 'Product';
                                $price = $product->price ?? null;
                                $desc = $product->description ?? null;
                                $image = $product->cover_image ?? ($product->image_url ?? null);
                                $productUrl = route('frontend.products.show', $product->id);
                            @endphp

                            <h2 style="margin:0 0 10px; font-size:20px; color:#111827;">New product added</h2>
                            <p style="margin:0 0 16px; font-size:14px; line-height:20px; color:#374151;">
                                Hi {{ $recipient->name ?? 'there' }}, a new product is now available in LinkUp Shop.
                            </p>

                            <div style="border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; background:#fafafa;">
                                @if($image)
                                    <img src="{{ $image }}" alt="Product image" style="width:100%; height:auto; display:block;" />
                                @endif
                                <div style="padding:14px;">
                                    <div style="font-size:16px; font-weight:bold; color:#111827; margin-bottom:6px;">{{ $name }}</div>
                                    @if($price !== null)
                                        <div style="font-size:13px; color:#6b7280; margin-bottom:8px;">Price: ${{ number_format((float) $price, 2) }}</div>
                                    @endif
                                    @if($desc)
                                        <div style="font-size:13px; line-height:19px; color:#374151;">
                                            {{ \Illuminate\Support\Str::limit(strip_tags((string) $desc), 180) }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div style="text-align:center; margin-top:18px;">
                                <a href="{{ $productUrl }}" style="display:inline-block; padding:12px 20px; background:#f97316; color:#fff; text-decoration:none; border-radius:6px; font-size:14px; font-weight:bold;">
                                    View Product
                                </a>
                            </div>

                            <p style="margin:18px 0 0; font-size:12px; color:#9ca3af; text-align:center;">
                                If you don’t recognize this activity, you can ignore this email.
                            </p>

                            <div style="margin-top: 20px;">
                                @include('emails.partials.sponsor')
                            </div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

    @if(!empty($trackingToken))
        <img src="{{ url('/email/open/' . $trackingToken . '.png') }}" width="1" height="1" alt="" style="display:none;" />
    @endif
</body>

</html>
