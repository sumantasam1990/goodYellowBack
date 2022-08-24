@component('mail::message')
# Hi {{ $mailData['buyer'] }}

Your order from {{ $mailData['vendor'] }} has been canceled. Please click below to open the order details.

@component('mail::panel')
Please email {{ $mailData['vendor'] }} with any questions that you may have about the order.
@endcomponent

Please email us at <a href="mailto:heyhey@goodyellowco.com">heyhey@goodyellowco.com</a> with any questions.


@component('mail::button', ['url' => $mailData['url'], 'color' => 'success'])
View Orders
@endcomponent

Thank you,<br>
Team Good Yellow
@endcomponent
