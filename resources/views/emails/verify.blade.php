@component('mail::message')
# Hi {{ $firstName }},

Please verify your email by clicking the button below.

@component('mail::button', ['url' => route('verify.email', ['token' => $token])])
Verify Email
@endcomponent

Thanks,  
{{ config('app.name') }}
@endcomponent