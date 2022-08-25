@component('mail::message')
# Hi {{ $mailData['buyer'] }}

{{ $mailData['name'] }} sent you some discount codes.

@component('mail::table')
| Discount (%)       | Discount Code       |
| ------------- |:------------------------:|
@foreach ($mailData['discounts'] as $discount)
    | {{ $discount->discount }} | {{ $discount->discount_code }} |
@endforeach


@endcomponent

@component('mail::panel')
Please email the customer with any questions that you may have about the order.
@endcomponent

Please email us at <a href="mailto:heyhey@goodyellowco.com">heyhey@goodyellowco.com</a> with any questions.


{{-- @component('mail::button', ['url' => $mailData['url'], 'color' => 'success'])
View Orders
@endcomponent --}}

Thank you,<br>
Team Good Yellow
@endcomponent
