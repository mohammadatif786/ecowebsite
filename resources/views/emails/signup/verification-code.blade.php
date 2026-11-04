<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your LinkUp code</title>
</head>
<body style="margin:0;padding:0;background:#F6F8FC;font-family:'Plus Jakarta Sans',Arial,Helvetica,sans-serif;color:#0B1530;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#F6F8FC;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:480px;background:#ffffff;border-radius:18px;border:1px solid #DCE3F0;">
                    <tr>
                        <td style="padding:32px 32px 8px 32px;">
                            <div style="font-size:22px;font-weight:800;color:#1D4ED8;letter-spacing:-0.5px;">LinkUp</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 32px 0 32px;">
                            <h1 style="margin:0 0 8px 0;font-size:22px;line-height:1.3;font-weight:800;">Confirm your email</h1>
                            <p style="margin:0;font-size:15px;line-height:1.6;color:#475569;">
                                Hi {{ $user->first_name ?: 'there' }}, enter this code on LinkUp to finish creating your account.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:24px 32px;">
                            <div style="display:inline-block;padding:16px 28px;border-radius:14px;background:#EEF4FF;border:1px solid #BFD0F5;font-size:34px;font-weight:800;letter-spacing:10px;color:#1D4ED8;">
                                {{ $code }}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 32px 32px 32px;">
                            <p style="margin:0 0 8px 0;font-size:14px;line-height:1.6;color:#475569;">
                                This code works for {{ $ttlMinutes }} minutes. Never share it with anyone.
                            </p>
                            <p style="margin:0;font-size:13px;line-height:1.6;color:#64748B;">
                                Didn't create a LinkUp account? You can ignore this email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
