@php
    // For one payment ($payment, with what was paid up to it); an order with no payment yet prints as an invoice
    $currency = $order->currency;
    $balance = round((float) $order->total - $paidToDate, 2);
    $open = in_array($order->status, \App\Models\Order::OPEN_STATUSES, true);
@endphp
<h1>{{ $payment ? __('Receipt') : __('Invoice') }}</h1>
<table class="meta">
    @if($payment)<tr><th>{{ __('Receipt no.') }}</th><td>{{ $payment->receipt_number }}</td></tr>@endif
    <tr><th>{{ __('Order no.') }}</th><td>{{ $order->order_number }}</td></tr>
    <tr><th>{{ __('Date') }}</th><td>{{ fmt_datetime($payment->created_at ?? $order->created_at) }}</td></tr>
    <tr><th>{{ __('Customer') }}</th><td>{{ $order->customer->name ?? __('Walk-in customer') }}@if($order->customer?->contact)<br>{{ $order->customer->contact }}@endif</td></tr>
    <tr><th>{{ __('Cashier') }}</th><td>{{ $payment?->receiver->name ?? $order->staff->name ?? '—' }}</td></tr>
    @if($open && $order->ready_by)<tr><th>{{ __('Ready by') }}</th><td class="strong">{{ fmt_date($order->ready_by) }}</td></tr>@endif
    @if($order->status === 'cancelled')<tr><th>{{ __('Status') }}</th><td class="strong">{{ __('Cancelled') }}</td></tr>@endif
</table>
<div class="rule"></div>
<table>
    @foreach($order->items as $item)
        <tr>
            <td>{{ $item->service->name ?? __('Deleted service') }}<div class="sub">{{ $item->qty }} × {{ money($item->price, $currency) }}</div></td>
            <td class="num">{{ money($item->total, $currency) }}</td>
        </tr>
    @endforeach
    @foreach($order->addons as $addon)
        <tr>
            <td>{{ $addon->name }}<div class="sub">{{ __('Add-on') }}</div></td>
            <td class="num">{{ money($addon->pivot->price, $currency) }}</td>
        </tr>
    @endforeach
</table>
@if($order->weight)<p class="sub">{{ __('Weight') }}: {{ fmt_weight($order->weight) }}</p>@endif
<div class="rule"></div>
<table>
    <tr><td>{{ __('Subtotal') }}</td><td class="num">{{ money($order->subtotal + $order->addons->sum('pivot.price'), $currency) }}</td></tr>
    @if($order->discount > 0)
        <tr><td>{{ __('Discount') }}{{ $order->discount_percent !== null ? ' (' . (float) $order->discount_percent . '%)' : '' }}</td><td class="num">−{{ money($order->discount, $currency) }}</td></tr>
    @endif
    <tr class="total"><td>{{ __('Total') }}</td><td class="num">{{ money($order->total, $currency) }}</td></tr>
    @foreach($payment ? $order->payments->where('id', '<=', $payment->id)->sortBy('id') : [] as $paid)
        <tr><td>{{ __('Paid') }} · {{ __(ucfirst($paid->payment_method)) }}<div class="sub">{{ fmt_datetime($paid->created_at) }}</div></td><td class="num">{{ money($paid->amount, $currency) }}</td></tr>
    @endforeach
    <tr class="strong"><td>{{ $balance > 0 && $order->status !== 'cancelled' ? __('Balance due') : __('Balance') }}</td><td class="num">{{ money(max($balance, 0), $currency) }}</td></tr>
</table>

@if($barcode || $qr)
    <div class="rule"></div>
    <div class="codes">
        @if($barcode)<img src="{{ $barcode }}" alt=""><p class="small">{{ $order->order_number }}</p>@endif
        @if($qr)<img class="qr" src="{{ $qr }}" alt="">@endif
    </div>
@endif

@if($settings->receipt_thank_you || $settings->receipt_footer || ($open && $settings->receipt_pickup_instructions) || $settings->receipt_terms)
    <div class="rule"></div>
    @if($settings->receipt_thank_you)<p class="center strong">{{ $settings->receipt_thank_you }}</p>@endif
    @if($settings->receipt_footer)<p class="center small">{!! nl2br(e($settings->receipt_footer)) !!}</p>@endif
    @if($open && $settings->receipt_pickup_instructions)<p class="heading">{{ __('Pickup') }}</p><p class="small">{!! nl2br(e($settings->receipt_pickup_instructions)) !!}</p>@endif
    @if($settings->receipt_terms)<p class="heading">{{ __('Terms') }}</p><p class="small">{!! nl2br(e($settings->receipt_terms)) !!}</p>@endif
@endif
