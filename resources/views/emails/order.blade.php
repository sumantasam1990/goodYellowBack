@component('mail::message')
# Hi {{ $mailData['name'] }}

A new order has been placed. Please click below to open the order details.

@component('mail::panel')
Please email the customer with any questions that you may have about the order.
@endcomponent

Please email us at <a href="mailto:heyhey@goodyellowco.com">heyhey@goodyellowco.com</a> with any questions.


@component('mail::button', ['url' => $mailData['url'], 'color' => 'success'])
View Orders
@endcomponent

Thank you,<br>
Team Good Yellow
@endcomponent
