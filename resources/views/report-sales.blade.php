@extends('layouts.app')

@section('title', 'Sales report')
@section('page-title', 'Sales report')
@php
    $start = \Carbon\Carbon::parse($filters['start_date']);
    $end = \Carbon\Carbon::parse($filters['end_date']);
    $range = $start->equalTo($end) ? $start->format('M j, Y') : $start->format('M j') . ' – ' . $end->format('M j, Y');
    $pesos = fn ($v) => '₱' . number_format((float) $v, 2);
@endphp
@section('page-subtitle', $range . ' · cancelled orders not counted')

@section('page-actions')
    <a href="{{ route('reports.sales.download', $filters) }}" class="btn btn-secondary"><i class="fas fa-file-csv" aria-hidden="true"></i> Download CSV</a>
    <button type="button" class="btn btn-secondary" onclick="window.print()"><i class="fas fa-print" aria-hidden="true"></i> Print</button>
@endsection

@section('content')
<x-report-range route="reports.sales" :start="$filters['start_date']" :end="$filters['end_date']" />

<section class="kpi-grid" aria-label="Totals for {{ $range }}" style="margin-bottom: var(--s-5)">
    <div class="kpi"><span class="kpi-label">Sales</span><span class="kpi-value">{{ $pesos($summary['total_sales']) }}</span><span class="kpi-meta">{{ $summary['total_orders'] }} {{ \Illuminate\Support\Str::plural('order', $summary['total_orders']) }}</span></div>
    <div class="kpi"><span class="kpi-label">Collected</span><span class="kpi-value">{{ $pesos($summary['total_paid']) }}</span><span class="kpi-meta">Paid on these orders</span></div>
    <div class="kpi {{ $summary['total_outstanding'] > 0 ? 'is-alert' : '' }}"><span class="kpi-label">Outstanding</span><span class="kpi-value">{{ $pesos($summary['total_outstanding']) }}</span><span class="kpi-meta">Still to collect</span></div>
    <div class="kpi"><span class="kpi-label">Average order</span><span class="kpi-value">{{ $pesos($summary['average_order_value']) }}</span><span class="kpi-meta">Sales ÷ orders</span></div>
    <div class="kpi"><span class="kpi-label">Discounts given</span><span class="kpi-value">{{ $pesos($summary['total_discount']) }}</span><span class="kpi-meta">Peso and percentage</span></div>
    <div class="kpi"><span class="kpi-label">Completed</span><span class="kpi-value">{{ number_format($summary['completion_rate'], 0) }}%</span><span class="kpi-meta">{{ $summary['completed_orders'] }} of {{ $summary['total_orders'] }} orders · {{ $summary['total_items_sold'] }} items, {{ $summary['total_addons_sold'] }} add-ons</span></div>
</section>

<div class="card card-flush">
    <header class="card-header"><h2 class="card-title"><i class="fas fa-list" aria-hidden="true"></i> Orders in this period</h2><span class="card-subtitle">{{ count($salesData) }} {{ \Illuminate\Support\Str::plural('order', count($salesData)) }}</span></header>
    @if(count($salesData) === 0)
        <div class="empty">
            <span class="empty-icon" aria-hidden="true"><i class="fas fa-chart-line"></i></span>
            <p class="empty-title">No sales in this period</p>
            <p>Pick another range above.</p>
        </div>
    @else
    <div class="table-wrap static">
        <table class="table table-stack">
            <thead>
                <tr><th>Date</th><th>Order</th><th>Customer</th><th class="num">Services</th><th class="num">Add-ons</th><th class="num">Discount</th><th class="num">Total</th><th class="num">Paid</th><th>Status</th></tr>
            </thead>
            <tbody>
                @foreach($salesData as $sale)
                <tr data-href="{{ route('orders.details', $sale['order']) }}">
                    <td data-label="Date" class="nowrap">{{ \Carbon\Carbon::parse($sale['date'])->format('M j') }}</td>
                    <td class="cell-primary"><a class="cell-title nowrap" href="{{ route('orders.details', $sale['order']) }}">{{ $sale['order_number'] }}</a></td>
                    <td data-label="Customer">{{ $sale['customer_name'] }}</td>
                    <td data-label="Services" class="num">{{ $pesos($sale['items_total']) }}</td>
                    <td data-label="Add-ons" class="num">{{ $pesos($sale['addon_total']) }}</td>
                    <td data-label="Discount" class="num">{{ $sale['discount'] > 0 ? '−' . $pesos($sale['discount']) : '—' }}</td>
                    <td data-label="Total" class="num"><strong>{{ $pesos($sale['total']) }}</strong></td>
                    <td data-label="Paid" class="num {{ $sale['outstanding'] > 0 ? 'text-danger' : '' }}">{{ $pesos($sale['paid_amount']) }}</td>
                    <td data-label="Status"><span class="badge badge-{{ $sale['status'] }}">{{ $sale['order']->status_label }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
