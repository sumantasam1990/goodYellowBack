@component('mail::message')
# Hi {{ $mailData['name'] }}

Thank you for signing up for Good Yellow as a Member.

Please click below to confirm your email address and Sign in.

@component('mail::button', ['url' => $mailData['url']])
Verify Now
@endcomponent

@component('mail::panel')
Please email us at heyhey@goodyellowco.com with any questions.
@endcomponent

Thank you,<br>
Team Good Yellow
@endcomponent
