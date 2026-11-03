{{-- Goes with the laundry through the shop: what to do, by when, ticked off step by step --}}
<h1>{{ __('Job order') }}</h1>
<p class="order-no">{{ $order->order_number }}</p>
@if($barcode)<div class="codes"><img src="{{ $barcode }}" alt=""></div>@endif
<div class="rule"></div>
<table class="meta">
    <tr><th>{{ __('Customer') }}</th><td class="strong">{{ $order->customer->name ?? __('Walk-in customer') }}@if($order->customer?->contact)<br>{{ $order->customer->contact }}@endif</td></tr>
    <tr><th>{{ __('Received') }}</th><td>{{ fmt_datetime($order->created_at) }}</td></tr>
    <tr><th>{{ __('Ready by') }}</th><td class="strong">{{ $order->ready_by ? fmt_date($order->ready_by) : '—' }}</td></tr>
    <tr><th>{{ __('Taken by') }}</th><td>{{ $order->staff->name ?? '—' }}</td></tr>
    @if($order->weight)<tr><th>{{ __('Weight') }}</th><td>{{ fmt_weight($order->weight) }}</td></tr>@endif
    <tr><th>{{ __('Payment') }}</th><td>{{ $order->balance > 0 ? __('Collect :amount', ['amount' => money($order->balance, $order->currency)]) : __('Paid') }}</td></tr>
</table>

<p class="heading">{{ __('Services') }}</p>
<table>
    @foreach($order->items as $item)
        <tr><td><span class="tick"></span>{{ $item->service->name ?? __('Deleted service') }}</td><td class="num strong">× {{ $item->qty }}</td></tr>
    @endforeach
    @foreach($order->addons as $addon)
        <tr><td><span class="tick"></span>{{ $addon->name }}</td><td class="num sub">{{ __('Add-on') }}</td></tr>
    @endforeach
</table>

<p class="heading">{{ __('Instructions and notes') }}</p>
<div class="box">{!! $order->notes ? nl2br(e($order->notes)) : e(__('None')) !!}</div>

<p class="heading">{{ __('Stains or damage found') }}</p>
<div class="box tall"></div>

<p class="heading">{{ __('Checklist') }}</p>
<table>
    @foreach([__('Counted, pockets checked'), __('Sorted'), __('Stains treated'), __('Washed'), __('Dried'), __('Folded or pressed'), __('Packed and tagged'), __('Quality checked')] as $step)
        <tr><td><span class="tick"></span>{{ $step }}</td><td class="num sub">{{ __('By') }} ________</td></tr>
    @endforeach
</table>
@if($qr)<div class="codes"><img class="qr" src="{{ $qr }}" alt=""></div>@endif
