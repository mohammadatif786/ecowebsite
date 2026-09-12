<!doctype html>
<html lang="en-US">

<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
    <title>Forgot Password Notification</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style type="text/css">
        body {
            margin: 0;
            padding: 0;
            font-family: 'Open Sans', sans-serif;
        }

        .main-table {
            padding-top: 50px;
            padding-bottom: 50px;
            padding-left: 0;
            padding-right: 0;
        }

        table {
            border-spacing: 0;
        }

        img {
            border: 0;
            max-width: 100%;
            height: auto;
        }

        @media only screen and (max-width: 600px) {

            .main-table {
                background-color: #ffffff;
            }

            .wrapper {
                width: 100% !important;
                padding: 0 10px;
            }

            .logo img {
                width: 100px !important;
            }

            .content {
                padding: 20px 10px !important;
            }

            .text {
                font-size: 14px !important;
                line-height: 22px !important;
            }
        }
    </style>
</head>

<body>
    <table width="100%" cellpadding="0" cellspacing="0" class="main-table">
        <tr>
            <td align="center">
                <table class="wrapper"
                    style="max-width: 670px; width: 100%; margin: 0 auto; background-color: #fff; border-radius: 8px; box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);">
                    <tr>
                        <td style="padding: 20px 0; text-align: center;background:gray;">
                            <a href="{{ config('app.url') }}" title="logo" class="logo">
                                <img width="120" src="{{ asset('assets/images/logo.png') }}" alt="logo">
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="content" style="padding: 20px 40px; text-align: center;">
                            <p class="text" style="color: #333; font-size: 16px; line-height: 24px; margin: 0;">
                                Hello {{ $data['name'] }},
                                <br><br>
                                Your password has been changed in our records to the following:
                                <br><br>
                                <b>Temporary Password:</b> {{ $data['temp_password'] }}
                                <br><br>
                                You may keep this temporary password or go to Account Settings to create a new password.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 40px 20px;">
                            @include('emails.partials.sponsor')
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
