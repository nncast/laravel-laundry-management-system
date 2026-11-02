{{-- The customer keeps this until pickup; staff scan it to open the order --}}
<h1>{{ __('Claim ticket') }}</h1>
<p class="order-no">{{ $order->order_number }}</p>
@if($barcode)<div class="codes"><img src="{{ $barcode }}" alt=""></div>@endif
<div class="rule"></div>
<table class="meta">
    <tr><th>{{ __('Customer') }}</th><td class="strong">{{ $order->customer->name ?? __('Walk-in customer') }}@if($order->customer?->contact)<br>{{ $order->customer->contact }}@endif</td></tr>
    <tr><th>{{ __('Received') }}</th><td>{{ fmt_datetime($order->created_at) }}</td></tr>
    <tr><th>{{ __('Ready by') }}</th><td class="strong">{{ $order->ready_by ? fmt_date($order->ready_by) : '—' }}</td></tr>
    <tr><th>{{ __('Status') }}</th><td>{{ __($order->status_label) }}</td></tr>
</table>
<div class="rule"></div>
<table>
    @foreach($order->items as $item)
        <tr><td>{{ $item->service->name ?? __('Deleted service') }}</td><td class="num">× {{ $item->qty }}</td></tr>
    @endforeach
    @foreach($order->addons as $addon)
        <tr><td>{{ $addon->name }} <span class="sub">({{ __('Add-on') }})</span></td><td class="num"></td></tr>
    @endforeach
    <tr class="strong"><td>{{ __('Total quantity') }}</td><td class="num">{{ $order->items->sum('qty') }}</td></tr>
    @if($order->weight)<tr class="strong"><td>{{ __('Weight') }}</td><td class="num">{{ fmt_weight($order->weight) }}</td></tr>@endif
</table>
<div class="rule"></div>
<table>
    <tr><td>{{ __('Total') }}</td><td class="num">{{ money($order->total, $order->currency) }}</td></tr>
    <tr><td>{{ __('Paid') }}</td><td class="num">{{ money($order->paid_amount, $order->currency) }}</td></tr>
    @if($order->balance > 0)
        <tr class="total"><td>{{ __('To pay at pickup') }}</td><td class="num">{{ money($order->balance, $order->currency) }}</td></tr>
    @else
        <tr class="total"><td colspan="2">{{ __('Paid in full') }}</td></tr>
    @endif
</table>
@if($qr)<div class="codes"><img class="qr" src="{{ $qr }}" alt=""></div>@endif

<div class="rule"></div>
<p class="center strong">{{ __('Bring this ticket when you pick up your laundry.') }}</p>
@if($settings->receipt_pickup_instructions)<p class="small">{!! nl2br(e($settings->receipt_pickup_instructions)) !!}</p>@endif
@if($settings->receipt_terms)<p class="heading">{{ __('Terms') }}</p><p class="small">{!! nl2br(e($settings->receipt_terms)) !!}</p>@endif
@if($settings->receipt_thank_you)<p class="heading center">{{ $settings->receipt_thank_you }}</p>@endif
