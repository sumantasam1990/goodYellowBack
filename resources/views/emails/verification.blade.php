@component('mail::message')
# Hi {{ $mailData['name'] }}

Thank you for signing up for Good Yellow as a Partner.

Please click below to confirm your email address and to start the signup process.

Please email us at heyhey@goodyellowco.com with any questions.

@component('mail::button', ['url' => $mailData['url']])
Verify Now
@endcomponent

Thank you,<br>
Team Good Yellow
@endcomponent
