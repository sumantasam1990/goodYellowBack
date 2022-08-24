@component('mail::message')
# Admin

Issues Details:

Email: {{ $mailData['email'] }}
Business: {{ $mailData['business_name'] }}

Issues: {{ $mailData['issue'] }}


Thank you,<br>
Team Good Yellow
@endcomponent
