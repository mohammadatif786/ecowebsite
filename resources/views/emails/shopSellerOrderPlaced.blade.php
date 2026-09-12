<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Order</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f6f8fb; margin: 0; padding: 0;">

    <div style="max-width: 720px; margin: 0 auto; padding: 24px;">
        <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden;">
            <div style="padding: 18px 22px; background: #111827; color: #ffffff;">
                <div style="font-size: 16px; font-weight: bold;">New purchase received</div>
                <div style="font-size: 13px; opacity: 0.9; margin-top: 4px;">Order: <strong>{{ $order->number ?? ('#' . $order->id) }}</strong></div>
            </div>

            <div style="padding: 22px;">
                <p style="margin: 0 0 14px 0; color: #111827;">
                    Hi {{ $seller->name ?? 'Seller' }},
                </p>

                <p style="margin: 0 0 18px 0; color: #374151; line-height: 1.5;">
                    You have a new order. Please review the items below and begin processing as soon as possible.
                </p>

                <h3 style="margin: 18px 0 10px 0; color: #111827; font-size: 15px;">Item(s) purchased</h3>

                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th align="left" style="padding: 10px 8px; border-bottom: 1px solid #e5e7eb; color: #6b7280; font-size: 12px; text-transform: uppercase;">Item</th>
                            <th align="center" style="padding: 10px 8px; border-bottom: 1px solid #e5e7eb; color: #6b7280; font-size: 12px; text-transform: uppercase;">Qty</th>
                            <th align="right" style="padding: 10px 8px; border-bottom: 1px solid #e5e7eb; color: #6b7280; font-size: 12px; text-transform: uppercase;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sellerItems as $it)
                            <tr>
                                <td style="padding: 10px 8px; border-bottom: 1px solid #f3f4f6; color: #111827;">
                                    <div style="font-weight: 600;">{{ $it['product_name'] ?? 'Product' }}</div>
                                    @if (!empty($it['listing_type']))
                                        <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">Type: {{ $it['listing_type'] }}</div>
                                    @endif
                                </td>
                                <td align="center" style="padding: 10px 8px; border-bottom: 1px solid #f3f4f6; color: #111827;">
                                    {{ (int) ($it['quantity'] ?? 0) }}
                                </td>
                                <td align="right" style="padding: 10px 8px; border-bottom: 1px solid #f3f4f6; color: #111827;">
                                    ${{ number_format((float) ($it['sub_total'] ?? 0), 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <h3 style="margin: 18px 0 10px 0; color: #111827; font-size: 15px;">Buyer information</h3>
                <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 14px; color: #111827;">
                    <div><strong>Name:</strong> {{ $buyer->name ?? 'N/A' }}</div>
                    <div style="margin-top: 4px;"><strong>Email:</strong> {{ $buyer->email ?? 'N/A' }}</div>
                </div>

                <h3 style="margin: 18px 0 10px 0; color: #111827; font-size: 15px;">Delivery / Pickup details</h3>
                <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 14px; color: #111827;">
                    <div><strong>Shipping method:</strong> {{ $deliveryDetails['shipping_method'] ?? 'N/A' }}</div>

                    @if (!empty($deliveryDetails['has_pickup']))
                        <div style="margin-top: 8px;"><strong>Pickup:</strong> Yes</div>
                        @if (!empty($deliveryDetails['pickup_locations']) && is_array($deliveryDetails['pickup_locations']))
                            <div style="margin-top: 6px;">
                                <strong>Pickup locations:</strong>
                                <ul style="margin: 6px 0 0 18px; padding: 0; color: #374151;">
                                    @foreach ($deliveryDetails['pickup_locations'] as $loc)
                                        <li style="margin: 2px 0;">{{ $loc }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @else
                        <div style="margin-top: 8px;"><strong>Delivery address:</strong></div>
                        <div style="margin-top: 6px; color: #374151; line-height: 1.5;">
                            {{ $deliveryDetails['street_address'] ?? '' }}<br>
                            {{ $deliveryDetails['city'] ?? '' }}{{ !empty($deliveryDetails['city']) && !empty($deliveryDetails['state']) ? ', ' : '' }}{{ $deliveryDetails['state'] ?? '' }} {{ $deliveryDetails['zipcode'] ?? '' }}<br>
                            {{ $deliveryDetails['country'] ?? '' }}
                        </div>
                    @endif
                </div>

                <div style="margin-top: 18px; font-size: 12px; color: #6b7280;">
                    Order placed at: {{ optional($order->created_at)->toDateTimeString() ?? 'N/A' }}
                </div>

                <div style="margin-top: 24px;">
                    @include('emails.partials.sponsor')
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 14px; font-size: 12px; color: #9ca3af;">
            This email was sent to notify you of a new purchase on LinkUp Shop.
        </div>
    </div>

</body>
</html>
