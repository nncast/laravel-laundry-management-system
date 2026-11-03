@extends('layouts.app')

@section('title', __('Order report'))
@section('page-title', __('Order report'))
@php
    $start = \Carbon\Carbon::parse($filters['start_date']);
    $end = \Carbon\Carbon::parse($filters['end_date']);
    $range = $start->equalTo($end) ? fmt_date($start) : fmt_date($start) . ' – ' . fmt_date($end);
    $statuses = ['' => __('All statuses')] + collect(\App\Models\Order::STATUSES)->mapWithKeys(fn ($s) => [$s => \App\Models\Order::labelFor($s)])->all();
@endphp
@section('page-subtitle', $range . ($filters['status'] ? ' · ' . $statuses[$filters['status']] : ''))

@section('page-actions')
    <a href="{{ route('reports.orders.download', array_filter($filters)) }}" class="btn btn-secondary"><i class="fas fa-file-csv" aria-hidden="true"></i> {{ __('Download CSV') }}</a>
    <button type="button" class="btn btn-secondary" onclick="window.print()"><i class="fas fa-print" aria-hidden="true"></i> {{ __('Print') }}</button>
@endsection

@section('content')
<x-report-range route="reports.orders" :start="$filters['start_date']" :end="$filters['end_date']" :extra="['status' => $filters['status']]">
    <div class="field">
        <label class="label" for="status">{{ __('Status') }}</label>
        <select class="select" id="status" name="status">
            @foreach($statuses as $value => $label)
                <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</x-report-range>
<x-other-currencies :rows="$otherCurrencies" />

<section class="kpi-grid" aria-label="{{ __('Totals for :range', ['range' => $range]) }}" style="margin-bottom: var(--s-5)">
    <div class="kpi"><span class="kpi-label">{{ __('Orders') }}</span><span class="kpi-value">{{ $summary['total_orders'] }}</span><span class="kpi-meta">{{ money($summary['total_amount']) }} in total</span></div>
    <div class="kpi"><span class="kpi-label"><span class="badge badge-pending">{{ __('Pending') }}</span></span><span class="kpi-value">{{ $summary['pending_count'] }}</span></div>
    <div class="kpi"><span class="kpi-label"><span class="badge badge-processing">{{ __('Processing') }}</span></span><span class="kpi-value">{{ $summary['processing_count'] }}</span></div>
    <div class="kpi"><span class="kpi-label"><span class="badge badge-ready">{{ __('Ready for pickup') }}</span></span><span class="kpi-value">{{ $summary['ready_count'] }}</span></div>
    <div class="kpi"><span class="kpi-label"><span class="badge badge-completed">{{ __('Completed') }}</span></span><span class="kpi-value">{{ $summary['completed_count'] }}</span></div>
    <div class="kpi"><span class="kpi-label"><span class="badge badge-cancelled">{{ __('Cancelled') }}</span></span><span class="kpi-value">{{ $summary['cancelled_count'] }}</span></div>
</section>

<div class="card card-flush">
    @if($orders->isEmpty())
        <div class="empty">
            <span class="empty-icon" aria-hidden="true"><i class="fas fa-clipboard-list"></i></span>
            <p class="empty-title">{{ __('No orders match') }}</p>
            <p>{{ __('Pick another range or status above.') }}</p>
        </div>
    @else
    <div class="table-wrap static">
        <table class="table table-stack">
            <thead>
                <tr><th>{{ __('Order') }}</th><th>{{ __('Date') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Services') }}</th><th class="num">{{ __('Amount') }}</th><th class="num">{{ __('Balance') }}</th><th>{{ __('Status') }}</th></tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr data-href="{{ route('orders.details', $order) }}">
                    <td class="cell-primary"><a class="cell-title nowrap" href="{{ route('orders.details', $order) }}">{{ $order->order_number }}</a></td>
                    <td data-label="{{ __('Date') }}" class="nowrap">{{ fmt_date($order->order_date) }}</td>
                    <td data-label="{{ __('Customer') }}">{{ $order->customer->name ?? __('Walk-in customer') }}</td>
                    <td data-label="{{ __('Services') }}" class="cell-sub">
                        {{ $order->items->map(fn ($item) => ($item->service->name ?? __('Deleted service')) . ($item->qty > 1 ? " ×{$item->qty}" : ''))->implode(', ') ?: '—' }}
                    </td>
                    <td data-label="{{ __('Amount') }}" class="num"><strong>{{ money($order->total, $order->currency) }}</strong></td>
                    <td data-label="{{ __('Balance') }}" class="num {{ $order->balance > 0 && $order->status !== 'cancelled' ? 'text-danger' : 'muted' }}">{{ $order->status !== 'cancelled' && $order->balance > 0 ? money($order->balance, $order->currency) : '—' }}</td>
                    <td data-label="{{ __('Status') }}"><span class="badge badge-{{ $order->status }}">{{ $order->status_label }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $orders->links() }}
    @endif
</div>
@endsection
