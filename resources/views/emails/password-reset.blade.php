@component('mail::message')
# Password Reset Request

Hello {{ $userName }},

We received a request to reset your password for your Calaia PH account.

@component('mail::button', ['url' => $resetUrl, 'color' => 'blue'])
Reset Password
@endcomponent

This password reset link will expire in 24 hours. If you did not request a password reset, please ignore this email or contact us if you have concerns.

Thank you,
Calaia PH Team
@endcomponent
