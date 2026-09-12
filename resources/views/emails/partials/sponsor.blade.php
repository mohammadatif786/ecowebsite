<!-- [LINKUP SPONSOR BLOCK] -->
@if (isset($ad) && $ad)
    <div style="margin: 0 auto; max-width: 600px; padding: 0 16px 24px 16px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f9f8f5; border: 1px solid #ede8dc; border-radius: 16px; border-collapse: separate; overflow: hidden; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
            <tr>
                <td style="padding: 20px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <!-- Image/Brand Cell -->
                            @if ($ad->image)
                                <td valign="middle" style="width: 100px; padding-right: 18px;" class="sponsor-img-cell">
                                    <a href="{{ route('sponsor.click', $ad) }}" target="_blank" style="text-decoration: none; display: block;">
                                        <img src="{{ $ad->image_url }}" width="100" alt="{{ $ad->company_name }}" style="display: block; width: 100px; height: auto; max-height: 80px; border-radius: 8px; border: 0; outline: none; text-decoration: none;" />
                                    </a>
                                </td>
                            @else
                                <td valign="middle" style="width: 100px; padding-right: 18px;" class="sponsor-img-cell">
                                    <div style="font-size: 9px; font-weight: 800; letter-spacing: 1.2px; text-transform: uppercase; color: #9ca3af; margin-bottom: 4px; line-height: 1;">
                                        Sponsored By
                                    </div>
                                    <div style="font-size: 16px; font-weight: 900; color: #101828; line-height: 1.2;">
                                        {{ $ad->company_name }}
                                    </div>
                                </td>
                            @endif

                            <!-- Divider line -->
                            <td width="1" style="background-color: #ede8dc; width: 1px; padding: 0;" valign="stretch"></td>

                            <!-- Message Content Cell -->
                            <td valign="middle" style="padding-left: 18px;">
                                <div style="font-size: 14px; font-weight: 800; color: #101828; margin-bottom: 4px; line-height: 1.3;">
                                    {{ $ad->headline }}
                                </div>
                                <div style="font-size: 13px; color: #4b5563; line-height: 1.45; margin-bottom: 12px;">
                                    {{ $ad->message }}
                                </div>
                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse;">
                                    <tr>
                                        <td style="background-color: #6d28d9; border-radius: 8px; text-align: center;">
                                            <a href="{{ route('sponsor.click', $ad) }}" target="_blank" style="display: inline-block; background-color: #6d28d9; color: #ffffff; text-decoration: none; padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 700; line-height: 1.2; border: 1px solid #6d28d9;">
                                                {{ $ad->cta_text }} &nbsp;&rarr;
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
@endif
<!-- [/LINKUP SPONSOR BLOCK] -->
