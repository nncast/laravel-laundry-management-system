{{-- Sales report totals; loaded after the page for long periods (SalesReportController, part=summary) --}}
<x-other-currencies :rows="$otherCurrencies" />

<section class="kpi-grid" aria-label="Totals for {{ $range }}" style="margin-bottom: var(--s-5)">
    <div class="kpi"><span class="kpi-label">Sales</span><span class="kpi-value">{{ money($summary['total_sales']) }}</span><span class="kpi-meta">{{ $summary['total_orders'] }} {{ \Illuminate\Support\Str::plural('order', $summary['total_orders']) }}</span></div>
    <div class="kpi"><span class="kpi-label">Collected</span><span class="kpi-value">{{ money($summary['total_paid']) }}</span><span class="kpi-meta">Paid on these orders</span></div>
    <div class="kpi {{ $summary['total_outstanding'] > 0 ? 'is-alert' : '' }}"><span class="kpi-label">Outstanding</span><span class="kpi-value">{{ money($summary['total_outstanding']) }}</span><span class="kpi-meta">Still to collect</span></div>
    <div class="kpi"><span class="kpi-label">Average order</span><span class="kpi-value">{{ money($summary['average_order_value']) }}</span><span class="kpi-meta">Sales ÷ orders</span></div>
    <div class="kpi"><span class="kpi-label">Discounts given</span><span class="kpi-value">{{ money($summary['total_discount']) }}</span><span class="kpi-meta">Fixed amounts and percentages</span></div>
    <div class="kpi"><span class="kpi-label">Completed</span><span class="kpi-value">{{ number_format($summary['completion_rate'], 0) }}%</span><span class="kpi-meta">{{ $summary['completed_orders'] }} of {{ $summary['total_orders'] }} orders · {{ $summary['total_items_sold'] }} items, {{ $summary['total_addons_sold'] }} add-ons</span></div>
</section>
