{{--
    Date-range filter for the sales and order reports: one-tap presets first, then a custom range.
    <x-report-range route="reports.sales" :start="$filters['start_date']" :end="$filters['end_date']"> extra fields </x-report-range>
--}}
@props(['route', 'start', 'end', 'extra' => []])
@php
    $today = today();
    $presets = [
        __('Today') => [$today, $today],
        __('Yesterday') => [$today->copy()->subDay(), $today->copy()->subDay()],
        __('Last 7 days') => [$today->copy()->subDays(6), $today],
        __('This month') => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
        __('Last month') => [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()],
    ];
@endphp
<div class="card no-print" style="margin-bottom: var(--s-5)">
    <nav class="chips scroll" aria-label="{{ __('Date range') }}" style="padding: var(--s-3) var(--s-5) 0">
        @foreach($presets as $label => [$from, $to])
            @php $on = $from->toDateString() === $start && $to->toDateString() === $end; @endphp
            <a class="chip" href="{{ route($route, array_filter(['start_date' => $from->toDateString(), 'end_date' => $to->toDateString()] + $extra)) }}" @if($on) aria-current="true" @endif>{{ $label }}</a>
        @endforeach
    </nav>
    <form class="filter-bar" method="GET" action="{{ route($route) }}">
        <div class="field">
            <label class="label" for="start_date">{{ __('From') }}</label>
            <input class="input" type="date" id="start_date" name="start_date" value="{{ $start }}">
        </div>
        <div class="field">
            <label class="label" for="end_date">{{ __('To') }}</label>
            <input class="input" type="date" id="end_date" name="end_date" value="{{ $end }}">
        </div>
        {{ $slot }}
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter" aria-hidden="true"></i> {{ __('Show') }}</button>
        </div>
    </form>
</div>
