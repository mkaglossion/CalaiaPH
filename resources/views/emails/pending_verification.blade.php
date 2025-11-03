@component('mail::message')
# Hello {{ $name }},

Thank you for signing up at **CalaiaPH**!

Please click the button below to verify your email address:

@component('mail::button', ['url' => $verifyUrl])
Verify My Email
@endcomponent

If you didn’t sign up, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
