{{--
    A note under money totals that leave out orders taken in another currency
    (before the business currency was changed). $rows: [currency, orders, total].
--}}
@props(['rows'])
@if($rows && count($rows))
<div class="alert alert-info" role="note" style="margin-bottom: var(--s-5)">
    <i class="fas fa-coins" aria-hidden="true"></i>
    <span>
        Totals are in {{ app(\App\Support\Format::class)->currency }}. Not added in:
        @foreach($rows as $row)
            {{ $row->orders }} {{ \Illuminate\Support\Str::plural('order', $row->orders) }} in {{ $row->currency }} ({{ money($row->total, $row->currency) }}){{ $loop->last ? '' : ',' }}
        @endforeach
        from before the currency changed.
    </span>
</div>
@endif
