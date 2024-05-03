@component('mail::message')
Dear User,

Forgot your password?
We received a request to reset password for your account.

To reset your password, click on the button below.

@component('mail::button', ['url' => $url])
Reset Password
@endcomponent

Or copy and paste the URL into your browser:
{{ $url }}

Best regards,<br>
{{ env('APP_NAME')}}
@endcomponent