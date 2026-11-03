@extends('layouts.app')

@section('title', __('Sales report'))
@section('page-title', __('Sales report'))
@php
    $start = \Carbon\Carbon::parse($filters['start_date']);
    $end = \Carbon\Carbon::parse($filters['end_date']);
    $range = $start->equalTo($end) ? fmt_date($start) : fmt_date($start) . ' – ' . fmt_date($end);
@endphp
@section('page-subtitle', $range . ' · ' . __('cancelled orders not counted'))

@section('page-actions')
    <a href="{{ route('reports.sales.download', $filters) }}" class="btn btn-secondary"><i class="fas fa-file-csv" aria-hidden="true"></i> {{ __('Download CSV') }}</a>
    <button type="button" class="btn btn-secondary" onclick="window.print()"><i class="fas fa-print" aria-hidden="true"></i> {{ __('Print') }}</button>
@endsection

@section('content')
<x-report-range route="reports.sales" :start="$filters['start_date']" :end="$filters['end_date']" />
@if($summary)
    @include('partials.sales-summary')
@else
    {{-- A long period: the table shows at once and the totals follow --}}
    <div id="salesSummary" data-lazy="{{ $summaryUrl }}" aria-busy="true">
        <section class="kpi-grid" aria-label="{{ __('Loading the totals') }}" style="margin-bottom: var(--s-5)">
            @for($i = 0; $i < 6; $i++)
                <div class="kpi"><span class="skeleton" style="width:45%"></span><span class="skeleton" style="height:28px;width:70%"></span><span class="skeleton" style="width:60%"></span></div>
            @endfor
        </section>
    </div>
@endif

<div class="card card-flush">
    <header class="card-header"><h2 class="card-title"><i class="fas fa-list" aria-hidden="true"></i> {{ __('Orders in this period') }}</h2><span class="card-subtitle">{{ number_format($salesData->total()) }} {{ \Illuminate\Support\Str::plural('order', $salesData->total()) }}</span></header>
    @if($salesData->total() === 0)
        <div class="empty">
            <span class="empty-icon" aria-hidden="true"><i class="fas fa-chart-line"></i></span>
            <p class="empty-title">{{ __('No sales in this period') }}</p>
            <p>{{ __('Pick another range above.') }}</p>
        </div>
    @else
    <div class="table-wrap static">
        <table class="table table-stack">
            <thead>
                <tr><th>{{ __('Date') }}</th><th>{{ __('Order') }}</th><th>{{ __('Customer') }}</th><th class="num">{{ __('Services') }}</th><th class="num">{{ __('Add-ons') }}</th><th class="num">{{ __('Discount') }}</th><th class="num">{{ __('Total') }}</th><th class="num">{{ __('Paid') }}</th><th>{{ __('Status') }}</th></tr>
            </thead>
            <tbody>
                @foreach($salesData as $sale)
                <tr data-href="{{ route('orders.details', $sale['order']) }}">
                    <td data-label="{{ __('Date') }}" class="nowrap">{{ fmt_date($sale['date']) }}</td>
                    <td class="cell-primary"><a class="cell-title nowrap" href="{{ route('orders.details', $sale['order']) }}">{{ $sale['order_number'] }}</a></td>
                    <td data-label="{{ __('Customer') }}">{{ $sale['customer_name'] }}</td>
                    <td data-label="{{ __('Services') }}" class="num">{{ money($sale['items_total'], $sale['order']->currency) }}</td>
                    <td data-label="{{ __('Add-ons') }}" class="num">{{ money($sale['addon_total'], $sale['order']->currency) }}</td>
                    <td data-label="{{ __('Discount') }}" class="num">{{ $sale['discount'] > 0 ? '−' . money($sale['discount'], $sale['order']->currency) : '—' }}</td>
                    <td data-label="{{ __('Total') }}" class="num"><strong>{{ money($sale['total'], $sale['order']->currency) }}</strong></td>
                    <td data-label="{{ __('Paid') }}" class="num {{ $sale['outstanding'] > 0 ? 'text-danger' : '' }}">{{ money($sale['paid_amount'], $sale['order']->currency) }}</td>
                    <td data-label="{{ __('Status') }}"><span class="badge badge-{{ $sale['status'] }}">{{ $sale['order']->status_label }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $salesData->links() }}
    @endif
</div>
@endsection
