{{-- <!-- resources/views/Email/passwordReset.blade.php -->

@component('mail::message')
# Introduction

The body of your message.

@component('mail::button', ['url' => 'http://localhost:4200/response-password-reset?token='.$token])
Button Text
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent --}}
<!-- resources/views/Email/passwordReset.blade.php -->

@component('mail::message')
# Password Reset Request

We received a request to reset your password. Your password reset token is:

**{{ $token }}**

Click the button below to reset your password:

@component('mail::button', ['url' => 'http://localhost:4200/response-password-reset?token='.$token])
Reset Password
@endcomponent

If you did not request a password reset, no further action is required.

Thanks,<br>
{{ config('app.name') }}
@endcomponent

