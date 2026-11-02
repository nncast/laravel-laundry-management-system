@extends('layouts.app')

@section('title', 'Daily summary')
@section('page-title', 'Daily summary')
@php
    $day = \Carbon\Carbon::parse($selectedDate);
    $labels = ['pending' => 'Pending', 'processing' => 'Processing', 'ready' => 'Ready for pickup', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
@endphp
@section('page-subtitle', $day->format('l, F j, Y') . ($day->isToday() ? ' · today' : ''))

@section('page-actions')
    <button type="button" class="btn btn-secondary" id="downloadCsv"><i class="fas fa-file-csv" aria-hidden="true"></i> Download CSV</button>
    <button type="button" class="btn btn-secondary" onclick="window.print()"><i class="fas fa-print" aria-hidden="true"></i> Print</button>
@endsection

@push('styles')
<style>
.day-picker { display: flex; flex-wrap: wrap; align-items: center; gap: var(--s-2); margin-bottom: var(--s-5); }
.day-picker .input { width: auto; }
.rank-list { list-style: none; padding: var(--s-4) var(--s-5); display: grid; gap: var(--s-3); }
.rank-row { display: flex; justify-content: space-between; gap: var(--s-3); font-weight: 700; }
.rank-row span:last-child { color: var(--text-muted); font-weight: 600; white-space: nowrap; }
.rank-track { height: 8px; margin-top: 6px; border-radius: 4px; background: var(--accent-soft); overflow: hidden; }
.rank-bar { display: block; height: 100%; border-radius: 4px; background: var(--accent); }
</style>
@endpush

@section('content')
@if(!empty($error))
    <div class="alert alert-danger" style="margin-bottom: var(--s-4)"><i class="fas fa-circle-exclamation" aria-hidden="true"></i><span>{{ $error }}</span></div>
@endif

<form class="day-picker no-print" method="GET" action="{{ route('reports.daily') }}" id="dayForm">
    <a class="icon-btn outlined" href="{{ route('reports.daily', ['date' => $day->copy()->subDay()->toDateString()]) }}" aria-label="Previous day"><i class="fas fa-chevron-left" aria-hidden="true"></i></a>
    <label class="sr-only" for="date">Day</label>
    <input class="input" type="date" id="date" name="date" value="{{ $selectedDate }}" max="{{ today()->toDateString() }}">
    <a class="icon-btn outlined" href="{{ route('reports.daily', ['date' => $day->copy()->addDay()->toDateString()]) }}" aria-label="Next day" @if($day->isToday()) aria-disabled="true" style="pointer-events:none;opacity:.45" @endif><i class="fas fa-chevron-right" aria-hidden="true"></i></a>
    @unless($day->isToday())<a class="btn btn-ghost" href="{{ route('reports.daily') }}">Today</a>@endunless
</form>

<x-other-currencies :rows="$stats['other_currencies']" />
<section class="kpi-grid" aria-label="Totals" style="margin-bottom: var(--s-5)">
    <div class="kpi"><span class="kpi-label">Orders taken</span><span class="kpi-value">{{ $stats['total_orders'] }}</span><span class="kpi-meta">{{ $stats['delivered_orders'] }} completed</span></div>
    <div class="kpi"><span class="kpi-label">Sales</span><span class="kpi-value">{{ money($stats['total_sales']) }}</span><span class="kpi-meta">Orders dated this day</span></div>
    <div class="kpi"><span class="kpi-label">Collected</span><span class="kpi-value">{{ money($stats['total_payments']) }}</span><span class="kpi-meta">Payments received this day, for any order</span></div>
    <div class="kpi {{ $stats['outstanding'] > 0 ? 'is-alert' : '' }}"><span class="kpi-label">Outstanding</span><span class="kpi-value">{{ money($stats['outstanding']) }}</span><span class="kpi-meta">Still owed on this day's orders</span></div>
</section>

<div class="grid-sidebar">
    <section class="card card-flush" aria-labelledby="day-orders">
        <header class="card-header"><h2 class="card-title" id="day-orders"><i class="fas fa-receipt" aria-hidden="true"></i> Orders</h2>
            <div class="cluster">
                @foreach($stats['status_breakdown'] as $status => $count)
                    <span class="badge badge-{{ $status }}">{{ $labels[$status] ?? ucfirst($status) }} · {{ $count }}</span>
                @endforeach
            </div>
        </header>
        @if($stats['orders']->isEmpty())
            <div class="empty"><span class="empty-icon" aria-hidden="true"><i class="fas fa-mug-hot"></i></span><p class="empty-title">No orders on this day</p></div>
        @else
        <div class="table-wrap static">
            <table class="table table-stack">
                <thead><tr><th>Order</th><th>Customer</th><th class="num">Total</th><th class="num">Paid</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($stats['orders'] as $order)
                    <tr data-href="{{ route('orders.details', $order) }}">
                        <td class="cell-primary"><a class="cell-title nowrap" href="{{ route('orders.details', $order) }}">{{ $order->order_number }}</a></td>
                        <td data-label="Customer">{{ $order->customer->name ?? 'Walk-in customer' }}</td>
                        <td data-label="Total" class="num"><strong>{{ money($order->total, $order->currency) }}</strong></td>
                        <td data-label="Paid" class="num {{ $order->balance > 0 && $order->status !== 'cancelled' ? 'text-danger' : '' }}">{{ money($order->paid_amount, $order->currency) }}</td>
                        <td data-label="Status"><span class="badge badge-{{ $order->status }}">{{ $order->status_label }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </section>

    <section class="card" aria-labelledby="day-services">
        <header class="card-header"><h2 class="card-title" id="day-services"><i class="fas fa-star" aria-hidden="true"></i> Top services</h2></header>
        @if($stats['top_services']->isEmpty())
            <p class="empty">Nothing sold yet.</p>
        @else
            @php $most = max(1, $stats['top_services']->max('total_amount')); @endphp
            <ol class="rank-list">
                @foreach($stats['top_services'] as $service)
                <li>
                    <div class="rank-row"><span>{{ $service->name }}</span><span>×{{ $service->total_qty }} · {{ money($service->total_amount) }}</span></div>
                    <div class="rank-track" aria-hidden="true"><span class="rank-bar" style="width: {{ round($service->total_amount / $most * 100, 1) }}%"></span></div>
                </li>
                @endforeach
            </ol>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script>
(function () {
    document.getElementById('date').addEventListener('change', e => e.target.form.submit());

    // CSV of the day's figures, built from the same data the server reports
    document.getElementById('downloadCsv').addEventListener('click', async e => {
        const button = e.currentTarget;
        const date = @json($selectedDate);
        button.classList.add('is-loading');
        try {
            const { data } = await sendJson(appUrl('reports/daily/download?date=' + date), {}, 'GET');
            const rows = [
                ['Daily report', date], [],
                ['Particulars', 'Value (' + window.FMT.currency + ')'],
                ['Total orders', data.total_orders],
                ['Completed orders', data.delivered_orders],
                ['Total sales', data.total_sales],
                ['Payments received', data.total_payments],
                ['Outstanding', data.outstanding], [],
                ['Status', 'Orders'],
                ...Object.entries(data.status_breakdown),
            ];
            const csv = '﻿' + rows.map(r => r.map(v => `"${String(v ?? '').replace(/"/g, '""')}"`).join(',')).join('\r\n');
            const link = document.createElement('a');
            link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
            link.download = `daily_report_${date}.csv`;
            link.click();
            URL.revokeObjectURL(link.href);
        } catch (err) {
            showToast(err.message, 'error');
        } finally {
            button.classList.remove('is-loading');
        }
    });
})();
</script>
@endpush
