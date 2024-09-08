<x-mail::message>
# Order Placed SUCCESSFULLY

THANK YOU FOR THE ORDER. YOUR ORDER NUMBER IS: {{ $order->id }}.

<x-mail::button :url="$url">
View Order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
