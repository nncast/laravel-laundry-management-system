{{-- Sales report totals; loaded after the page for long periods (SalesReportController, part=summary) --}}
<x-other-currencies :rows="$otherCurrencies" />

<section class="kpi-grid" aria-label="{{ __('Totals for :range', ['range' => $range]) }}" style="margin-bottom: var(--s-5)">
    <div class="kpi"><span class="kpi-label">{{ __('Sales') }}</span><span class="kpi-value">{{ money($summary['total_sales']) }}</span><span class="kpi-meta">{{ $summary['total_orders'] }} {{ \Illuminate\Support\Str::plural('order', $summary['total_orders']) }}</span></div>
    <div class="kpi"><span class="kpi-label">{{ __('Collected') }}</span><span class="kpi-value">{{ money($summary['total_paid']) }}</span><span class="kpi-meta">{{ __('Paid on these orders') }}</span></div>
    <div class="kpi {{ $summary['total_outstanding'] > 0 ? 'is-alert' : '' }}"><span class="kpi-label">{{ __('Outstanding') }}</span><span class="kpi-value">{{ money($summary['total_outstanding']) }}</span><span class="kpi-meta">{{ __('Still to collect') }}</span></div>
    <div class="kpi"><span class="kpi-label">{{ __('Average order') }}</span><span class="kpi-value">{{ money($summary['average_order_value']) }}</span><span class="kpi-meta">{{ __('Sales ÷ orders') }}</span></div>
    <div class="kpi"><span class="kpi-label">{{ __('Discounts given') }}</span><span class="kpi-value">{{ money($summary['total_discount']) }}</span><span class="kpi-meta">{{ __('Fixed amounts and percentages') }}</span></div>
    <div class="kpi"><span class="kpi-label">{{ __('Completed') }}</span><span class="kpi-value">{{ number_format($summary['completion_rate'], 0) }}%</span><span class="kpi-meta">{{ $summary['completed_orders'] }} of {{ $summary['total_orders'] }} orders · {{ $summary['total_items_sold'] }} items, {{ $summary['total_addons_sold'] }} add-ons</span></div>
</section>
