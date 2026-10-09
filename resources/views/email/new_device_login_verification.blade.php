<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ get_settings('system_title') }}</title>
</head>
<body dir="rtl" style="direction: rtl; text-align: right; font-family: Arial, Tahoma, sans-serif; line-height: 1.8; color: #1f2937;">
    <p>{{ get_phrase('You need to confirm that you want to login from this device') }}.</p>
    <p>{{ get_phrase('Please note that if you login from this device, your previously logged in device will be logged out.') }}</p>
    <p>
        <a href="{{ $verification_link }}" style="display: inline-block; padding: 10px 18px; background: #754FFE; color: #ffffff; text-decoration: none; border-radius: 6px;">
            {{ get_phrase('Got it, login now') }}
        </a>
    </p>
</body>
</html>
