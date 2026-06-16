<p>Hello {{ $userName }},</p>
<p>Your verification code is: <strong>{{ $code }}</strong></p>
<p>This code expires in {{ config('services.login_otp.ttl_minutes', 10) }} minutes.</p>
<p>If you did not try to sign in, you can ignore this email.</p>
