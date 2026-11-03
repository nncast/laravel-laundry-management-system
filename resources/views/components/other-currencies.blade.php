{{--
    A note under money totals that leave out orders taken in another currency
    (before the business currency was changed). $rows: [currency, orders, total].
--}}
@props(['rows'])
@if($rows && count($rows))
<div class="alert alert-info" role="note" style="margin-bottom: var(--s-5)">
    <i class="fas fa-coins" aria-hidden="true"></i>
    <span>
        {{ __('Totals are in :currency. Not added, from before the currency changed:', ['currency' => app(\App\Support\Format::class)->currency]) }}
        @foreach($rows as $row)
            {{ __($row->orders === 1 ? '1 order in :currency (:amount)' : ':count orders in :currency (:amount)', ['count' => $row->orders, 'currency' => $row->currency, 'amount' => money($row->total, $row->currency)]) }}{{ $loop->last ? '' : ',' }}
        @endforeach
    </span>
</div>
@endif
