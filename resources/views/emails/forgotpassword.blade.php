@component('mail::message')
# Hi {{ $mailData['name'] }}

Please click below button to change your password.

@component('mail::button', ['url' => $mailData['url']])
Change Password
@endcomponent

Thank you,<br>
Team Good Yellow
@endcomponent
