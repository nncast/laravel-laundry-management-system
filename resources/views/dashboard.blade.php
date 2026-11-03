@extends('layouts.app')

@section('title', __('Dashboard'))

@php
    $hour = now()->hour;
    $firstName = ['name' => strtok(session('staff.name') ?? __('there'), ' ')];
    $greeting = $hour < 12 ? __('Good morning, :name', $firstName) : ($hour < 18 ? __('Good afternoon, :name', $firstName) : __('Good evening, :name', $firstName));
    $role = session('staff.role');
    $isManager = in_array($role, ['manager', 'admin'], true);
    $isAdmin = $role === 'admin';
    $today = today()->toDateString();
    $whole = fn ($value) => money($value, whole: true);  // tiles show whole units; reports keep the decimals

    // [label, icon, tone, value, meta, link (null = not a link), alert]
    $kpis = [
        [__("Today's orders"), 'fa-basket-shopping', 'tone-blue', $todayOrders, __(':count not started yet', ['count' => $todayPending]), route('orders.index', ['from' => $today, 'to' => $today]), false],
        [__('Active orders'), 'fa-spinner', 'tone-sky', $openOrders, __(':pending pending · :processing processing', ['pending' => $pendingOrders, 'processing' => $processingOrders]), route('orders.index', ['status' => 'open']), false],
        [__('Ready for pickup'), 'fa-bell', 'tone-violet', $readyCount, __('Waiting for customers'), route('orders.index', ['status' => 'ready']), false],
        [__('Overdue'), 'fa-hourglass-half', 'tone-red', $overdueCount, $stuckRule, route('orders.index', ['status' => 'overdue']), $overdueCount > 0],
        [__('Revenue today'), 'fa-sack-dollar', 'tone-green', $whole($todayRevenue), __(':count completed today', ['count' => $todayCompleted]), $isAdmin ? route('reports.daily') : null, false],
        [__('Collected today'), 'fa-hand-holding-dollar', 'tone-aqua', $whole($collectedToday), __('Payments taken at the counter'), $isAdmin ? route('reports.daily') : null, false],
        [__('Revenue this month'), 'fa-chart-line', 'tone-indigo', $whole($monthRevenue), 'trend', $isAdmin ? route('reports.sales') : null, false],
        [__('Unpaid balance'), 'fa-file-invoice', 'tone-amber', $whole($unpaidBalance), __($unpaidCount === 1 ? '1 order still owes' : ':count orders still owe', ['count' => $unpaidCount]), route('orders.index', ['payment' => 'unpaid']), false],
    ];

    // Action center: [key, tab label, icon, first rows, "view all" link, how many in all]
    $groups = [
        ['overdue', __('Overdue'), 'fa-hourglass-half', $stuckOrders, route('orders.index', ['status' => 'overdue']), $stuckCount],
        ['ready', __('Ready for pickup'), 'fa-bell', $readyOrders, route('orders.index', ['status' => 'ready']), $readyListCount],
        ['unpaid', __('Picked up, unpaid'), 'fa-file-invoice', $unpaidOrders, route('orders.index', ['status' => 'completed', 'payment' => 'unpaid']), $unpaidPickupCount],
    ];
    if ($isManager) {
        $groups[] = ['stock', __('Low stock'), 'fa-box-open', $lowStock, route('products.index'), $lowStock->count()];
    }
    $attentionCount = collect($groups)->sum(fn ($g) => $g[5]);
    $firstGroup = collect($groups)->first(fn ($g) => $g[5] > 0)[0] ?? null;
@endphp

@section('page-header')
<header class="page-header dash-header">
    <x-mascot mood="wave" class="dash-mascot mascot-ink" />
    <div class="page-heading">
        <p class="eyebrow">{{ now()->isoFormat('dddd, LL') }}</p>
        <h1 class="page-title">{{ $greeting }}</h1>
        <p class="page-subtitle">
            @if($attentionCount === 1) {{ __('1 thing needs you today.') }}
            @elseif($attentionCount) {{ __(':count things need you today.', ['count' => $attentionCount]) }}
            @else {{ __('Everything is on track. Fresh loads, zero drama.') }}
            @endif
        </p>
    </div>
</header>
@endsection

@push('styles')
<style>
.dash-header { align-items: center; justify-content: flex-start; flex-direction: row; flex-wrap: nowrap; }
.dash-mascot { width: 76px; flex-shrink: 0; margin: -6px 0 -10px -6px; }

.quick-actions { display: grid; grid-template-columns: repeat(var(--n), minmax(0, 1fr)); gap: var(--s-3); }
.quick-action {
    display: flex; align-items: center; gap: var(--s-3);
    min-height: 60px; padding: 0 var(--s-4);
    border: 1px solid var(--border); border-radius: var(--radius);
    background: var(--surface); color: var(--text);
    font-weight: 700; text-align: start; text-decoration: none;
}
.quick-action > i { flex-shrink: 0; display: grid; place-items: center; width: 36px; height: 36px; border-radius: 10px; background: var(--accent-soft); color: var(--accent); }
.quick-action.is-primary { background: var(--accent); border-color: var(--accent); color: var(--on-accent); }
.quick-action.is-primary > i { background: rgba(255, 255, 255, 0.18); color: #fff; }
.quick-action:active { transform: scale(0.98); }

.kpi-grid.kpi-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
.kpi-value { font-variant-numeric: normal; }

.dash-grid { display: grid; gap: var(--s-5); grid-template-columns: minmax(0, 1fr) 380px; align-items: start; }

.attention-tabs { display: flex; gap: var(--s-2); padding: var(--s-3) var(--s-5); border-bottom: 1px solid var(--border); overflow-x: auto; scrollbar-width: none; }
.attention-tabs::-webkit-scrollbar { display: none; }
.attention-tabs .chip[aria-selected="true"] { background: var(--accent); border-color: var(--accent); color: var(--on-accent); }
.attention-tabs .chip[aria-selected="true"] .chip-count { background: rgba(255, 255, 255, 0.22); color: #fff; }
.attention-tabs .chip:disabled { opacity: 0.55; cursor: default; }
.attention-item { display: flex; align-items: center; gap: var(--s-3); padding: var(--s-2) var(--s-5); border-bottom: 1px solid var(--border); }
.list > li:last-child > .attention-item { border-bottom: 0; }
.attention-item > a { display: flex; align-items: center; gap: var(--s-3); flex: 1; min-width: 0; min-height: 56px; color: inherit; text-decoration: none; }
.attention-item .bubble-tile { width: 40px; height: 40px; font-size: 15px; }
.card-more { display: block; padding: var(--s-3) var(--s-5); border-top: 1px solid var(--border); font-weight: 700; text-decoration: none; }
.system-status { justify-content: flex-start; gap: var(--s-3); color: var(--text-secondary); font-size: var(--fs-sm); }
.system-status > span { flex: 1; }
.system-status .fa-circle-check { color: var(--success); }
.system-status .fa-triangle-exclamation { color: var(--warning-hover); }

/* Sales: one series, one baseline, value on tap/hover/focus */
.bar-chart { --plot-h: 220px; }
.bar-plot { position: relative; height: var(--plot-h); margin: var(--s-2) 0 0 52px; }
.bar-gridline { position: absolute; inset-inline-start: 0; inset-inline-end: 0; border-top: 1px solid var(--border); }
.bar-gridline span { position: absolute; inset-inline-end: calc(100% + 10px); top: -9px; font-size: var(--fs-xs); color: var(--text-muted); font-variant-numeric: tabular-nums; white-space: nowrap; }
.bar-cols { position: absolute; inset: 0; display: flex; }
.bar-col { position: relative; flex: 1; display: flex; align-items: flex-end; justify-content: center; border: 0; background: none; padding: 0; border-radius: 6px; }
.bar-col:focus-visible { outline-offset: -2px; }
.bar { position: relative; width: min(24px, 60%); background: var(--accent); border-radius: 4px 4px 0 0; transition: background-color var(--fast); }
.bar-col.is-active .bar { background: var(--accent-hover); }
.bar-col.is-active { background: var(--surface-muted); }
.bar-cap { position: absolute; inset-inline-start: 50%; bottom: 100%; transform: translateX(-50%); padding-bottom: 4px; font-size: var(--fs-xs); font-weight: 700; color: var(--text-secondary); white-space: nowrap; }
.bar-labels { display: flex; margin-inline-start: 52px; padding-top: var(--s-2); border-top: 1px solid var(--border-strong); }
.bar-labels span { flex: 1; text-align: center; font-size: var(--fs-xs); font-weight: 600; color: var(--text-muted); white-space: nowrap; }
.bar-tip {
    position: absolute; z-index: 2; transform: translate(-50%, calc(-100% - 8px)); pointer-events: none;
    padding: 6px 10px; border-radius: var(--radius-sm); background: var(--text); color: #fff;
    font-size: var(--fs-sm); white-space: nowrap; box-shadow: var(--shadow);
}
.bar-tip strong { display: block; font-size: var(--fs-base); }
.bar-tip span { color: #c5d1e0; }

.rank-list { list-style: none; padding: var(--s-2) var(--s-5) var(--s-4); display: grid; gap: var(--s-3); }
.rank-row { display: flex; justify-content: space-between; gap: var(--s-3); font-weight: 700; }
.rank-row span:last-child { color: var(--text-muted); font-weight: 600; font-variant-numeric: tabular-nums; white-space: nowrap; }
.rank-track { height: 8px; margin-top: 6px; border-radius: 4px; background: var(--accent-soft); overflow: hidden; }
.rank-bar { display: block; height: 100%; border-radius: 4px; background: var(--accent); }

.list-end.col { flex-direction: column; align-items: flex-end; gap: 4px; }

@media (hover: hover) {
    .quick-action:not(.is-primary):hover { border-color: var(--accent-soft-strong); background: var(--surface-muted); }
    .quick-action.is-primary:hover { background: var(--accent-hover); }
    .attention-item > a:hover .list-title { color: var(--accent); }
}
@media (max-width: 1279px) { .dash-grid { grid-template-columns: minmax(0, 1fr) 340px; } }
@media (max-width: 1099px) { .dash-grid { grid-template-columns: minmax(0, 1fr); } }
@media (max-width: 1023px) { .kpi-grid.kpi-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 767px) {
    .dash-mascot { width: 60px; }
    .quick-actions { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .quick-actions > :first-child { grid-column: 1 / -1; }
    .kpi { min-height: 104px; padding: var(--s-3) var(--s-4); }
    .kpi-value { font-size: 22px; }
    .kpi-label .bubble-tile { display: none; }
    .bar-chart { --plot-h: 180px; }
    .bar-chart[data-period="year"] .bar-labels span:nth-child(even) { visibility: hidden; }
}
</style>
@endpush

@section('content')
<div class="stack">
    <nav class="quick-actions" aria-label="{{ __('Quick actions') }}" style="--n: {{ 3 + ($isManager ? 1 : 0) + ($isAdmin ? 1 : 0) }}">
        <a href="{{ route('pos.index') }}" class="quick-action is-primary"><i class="fas fa-plus" aria-hidden="true"></i><span>{{ __('New order') }}</span></a>
        <button type="button" class="quick-action" data-focus-search><i class="fas fa-magnifying-glass" aria-hidden="true"></i><span>{{ __('Find order') }}</span></button>
        <a href="{{ route('customers.index', ['new' => 1]) }}" class="quick-action"><i class="fas fa-user-plus" aria-hidden="true"></i><span>{{ __('Add customer') }}</span></a>
        @if($isManager)
        <a href="{{ route('products.index') }}" class="quick-action"><i class="fas fa-boxes-stacked" aria-hidden="true"></i><span>{{ __('Inventory') }}</span></a>
        @endif
        @if($isAdmin)
        <a href="{{ route('reports.daily') }}" class="quick-action"><i class="fas fa-chart-column" aria-hidden="true"></i><span>{{ __('Reports') }}</span></a>
        @endif
    </nav>

    <section class="kpi-grid kpi-4" aria-label="{{ __('At a glance') }}">
        @foreach($kpis as [$label, $icon, $tone, $value, $meta, $href, $alert])
            <{{ $href ? 'a' : 'div' }} class="kpi {{ $alert ? 'is-alert' : '' }}" @if($href) href="{{ $href }}" @endif>
                <span class="kpi-label"><span class="bubble-tile {{ $tone }}" aria-hidden="true"><i class="fas {{ $icon }}"></i></span>{{ $label }}</span>
                <span class="kpi-value">{{ $value }}</span>
                <span class="kpi-meta">
                    @if($meta === 'trend')
                        @if($revenueTrend > 0)
                            <span class="trend-up"><i class="fas fa-arrow-trend-up" aria-hidden="true"></i> {{ $revenueTrend }}%</span> {{ __('vs same days last month') }}
                        @elseif($revenueTrend < 0)
                            <span class="trend-down"><i class="fas fa-arrow-trend-down" aria-hidden="true"></i> {{ abs($revenueTrend) }}%</span> {{ __('vs same days last month') }}
                        @else
                            {{ __('Same as this time last month') }}
                        @endif
                    @else
                        {{ $meta }}
                    @endif
                </span>
                @if($href)<i class="fas fa-arrow-right kpi-go" aria-hidden="true"></i>@endif
            </{{ $href ? 'a' : 'div' }}>
        @endforeach
    </section>

    <div class="dash-grid">
        <div class="stack">
            <section class="card" aria-labelledby="attention-title">
                <header class="card-header">
                    <h2 class="card-title" id="attention-title"><i class="fas fa-bolt" aria-hidden="true"></i> {{ __('Needs attention') }}</h2>
                    <span class="card-subtitle">{{ __('Checked each time this page opens') }}</span>
                </header>

                @if($attentionCount)
                <div class="attention-tabs" role="tablist" aria-label="{{ __('What needs attention') }}">
                    @foreach($groups as [$key, $label, $icon, $items, $allLink, $count])
                        <button type="button" class="chip" role="tab" id="tab-{{ $key }}" aria-controls="panel-{{ $key }}"
                                aria-selected="{{ $key === $firstGroup ? 'true' : 'false' }}" tabindex="{{ $key === $firstGroup ? 0 : -1 }}"
                                @disabled($count === 0)>
                            <i class="fas {{ $icon }}" aria-hidden="true"></i> {{ $label }} <span class="chip-count" data-count>{{ $count }}</span>
                        </button>
                    @endforeach
                </div>

                @foreach($groups as [$key, $label, $icon, $items, $allLink, $count])
                <div role="tabpanel" id="panel-{{ $key }}" aria-labelledby="tab-{{ $key }}" @if($key !== $firstGroup) hidden @endif>
                    <ul class="list">
                        @foreach($items->take(6) as $item)
                        <li>
                            <div class="attention-item">
                                @if($key === 'stock')
                                    <a href="{{ route('products.index') }}">
                                        <span class="bubble-tile tone-amber" aria-hidden="true"><i class="fas fa-box-open"></i></span>
                                        <span class="list-main">
                                            <span class="list-title">{{ $item->name }}</span>
                                            <span class="list-sub">{{ $item->available_stock }} left · minimum {{ $item->minimum_stock_level }}</span>
                                        </span>
                                    </a>
                                    <span class="badge badge-warning">{{ __('Restock') }}</span>
                                @else
                                    <a href="{{ route('orders.details', $item) }}">
                                        <span class="bubble-tile {{ $key === 'overdue' ? 'tone-red' : ($key === 'ready' ? 'tone-violet' : 'tone-amber') }}" aria-hidden="true"><i class="fas {{ $icon }}"></i></span>
                                        <span class="list-main">
                                            <span class="list-title">{{ $item->customer->name ?? __('Walk-in customer') }}</span>
                                            <span class="list-sub">{{ $item->order_number }} · {{ fmt_date($item->order_date) }}{{ $key === 'overdue' ? ' · ' . $item->status_label : '' }}</span>
                                        </span>
                                    </a>
                                    @if($key === 'ready' && $item->balance <= 0)
                                        <button type="button" class="btn btn-sm btn-success" data-pickup="{{ $item->id }}"><i class="fas fa-check" aria-hidden="true"></i> {{ __('Picked up') }}</button>
                                    @elseif($item->balance > 0)
                                        <span class="badge badge-unpaid money">{{ __(':amount due', ['amount' => money($item->balance, $item->currency)]) }}</span>
                                    @else
                                        <span class="cell-sub nowrap">{{ $item->order_date?->diffForHumans(['parts' => 1]) }}</span>
                                    @endif
                                @endif
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @if($count > 6)
                        <a class="card-more" href="{{ $allLink }}">View all {{ number_format($count) }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                    @endif
                </div>
                @endforeach
                @else
                <div class="empty">
                    <x-mascot mood="proud" class="empty-art mascot-ink" />
                    <p class="empty-title">{{ __('All caught up') }}</p>
                    <p>{{ $isManager ? __('No overdue, unpaid or waiting orders, and stock is fine.') : __('No overdue, unpaid or waiting orders.') }}</p>
                </div>
                @endif

                @if($isAdmin)
                <footer class="card-footer system-status">
                    @if($backupStale)
                        <i class="fas fa-triangle-exclamation" aria-hidden="true"></i>
                        <span>{{ $lastBackupAt ? __('Last backup :time.', ['time' => \Carbon\Carbon::createFromTimestamp($lastBackupAt)->diffForHumans()]) : __('No backup yet.') }} {{ __('A new one is being made now; if this stays, check Settings.') }}</span>
                        <a href="{{ route('settings.mastersettings') }}#backups" class="btn btn-sm btn-secondary">{{ __('Backups') }}</a>
                    @else
                        <i class="fas fa-circle-check" aria-hidden="true"></i>
                        <span>{{ __('Data backed up :time', ['time' => \Carbon\Carbon::createFromTimestamp($lastBackupAt)->diffForHumans()]) }}</span>
                    @endif
                </footer>
                @endif
            </section>

            <section class="card" aria-labelledby="sales-title">
                <header class="card-header">
                    <div>
                        <h2 class="card-title" id="sales-title"><i class="fas fa-chart-column" aria-hidden="true"></i> {{ __('Sales') }}</h2>
                        <p class="card-subtitle">{{ __('Completed orders, by day') }}</p>
                    </div>
                    <div class="segmented" role="radiogroup" aria-label="{{ __('Sales period') }}">
                        <label><input type="radio" name="salesPeriod" value="week" checked><span>{{ __('Week') }}</span></label>
                        <label><input type="radio" name="salesPeriod" value="month"><span>{{ __('Month') }}</span></label>
                        <label><input type="radio" name="salesPeriod" value="year"><span>{{ __('Year') }}</span></label>
                    </div>
                </header>
                <div class="card-body">
                    @foreach($chartData as $period => $series)
                        @php
                            $max = max($series['sales'] ?: [0]);
                            // Clean axis: 4 steps of 1, 2, 2.5 or 5 x 10^n
                            $raw = max($max, 100) / 4;
                            $magnitude = 10 ** floor(log10($raw));
                            $step = collect([1, 2, 2.5, 5, 10])->map(fn ($m) => $m * $magnitude)->first(fn ($s) => $s >= $raw);
                            $top = $step * 4;
                            $compact = $top >= 10000;
                            $tick = fn ($v) => currency_symbol() . ($compact ? rtrim(rtrim(fmt_number($v / 1000, 1), '0'), '.,') . 'K' : fmt_number($v));
                            $peak = $max > 0 ? array_search($max, $series['sales']) : null;
                            $unit = ['week' => __('Completed sales per day'), 'month' => __('Completed sales per week'), 'year' => __('Completed sales per month')][$period];
                        @endphp
                        <div class="bar-chart" data-period="{{ $period }}" @if($period !== 'week') hidden @endif>
                            <div class="bar-plot">
                                @for($i = 0; $i <= 4; $i++)
                                    <div class="bar-gridline" style="bottom: {{ $i * 25 }}%" aria-hidden="true"><span>{{ $tick($step * $i) }}</span></div>
                                @endfor
                                <div class="bar-cols" role="group" aria-label="{{ $unit }}">
                                    @foreach($series['sales'] as $i => $value)
                                        @php $orders = $series['orders'][$i]; $amount = money($value); @endphp
                                        <button type="button" class="bar-col"
                                                data-value="{{ $amount }}" data-label="{{ $series['titles'][$i] }} · {{ __($orders === 1 ? ':count order' : ':count orders', ['count' => $orders]) }}"
                                                aria-label="{{ __(':period: :amount from :count completed orders', ['period' => $series['titles'][$i], 'amount' => $amount, 'count' => $orders]) }}">
                                            @if($value > 0)
                                                <span class="bar" style="height: {{ round($value / $top * 100, 2) }}%">
                                                    @if($i === $peak)<span class="bar-cap" aria-hidden="true">{{ $tick($value) }}</span>@endif
                                                </span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            <div class="bar-labels" aria-hidden="true">
                                @foreach($series['labels'] as $label)<span>{{ $label }}</span>@endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($isAdmin)
                <div class="card-footer"><a href="{{ route('reports.sales') }}" class="btn btn-sm btn-ghost">{{ __('Open sales report') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a></div>
                @endif
            </section>
        </div>

        <div class="stack">
            <section class="card" aria-labelledby="recent-title">
                <header class="card-header">
                    <h2 class="card-title" id="recent-title"><i class="fas fa-clock-rotate-left" aria-hidden="true"></i> {{ __('Recent orders') }}</h2>
                    <a href="{{ route('orders.index') }}" class="btn btn-sm btn-ghost">{{ __('View all') }}</a>
                </header>
                @if($recentOrders->isEmpty())
                    <div class="empty">
                        <span class="empty-icon" aria-hidden="true"><i class="fas fa-receipt"></i></span>
                        <p class="empty-title">{{ __('No orders yet') }}</p>
                        <a href="{{ route('pos.index') }}" class="btn btn-primary"><i class="fas fa-plus" aria-hidden="true"></i> {{ __('New order') }}</a>
                    </div>
                @else
                <ul class="list">
                    @foreach($recentOrders as $order)
                    <li>
                        <a class="list-item" href="{{ route('orders.details', $order) }}">
                            <span class="list-main">
                                <span class="list-title">{{ $order->customer->name ?? __('Walk-in customer') }}</span>
                                <span class="list-sub">{{ $order->order_number }}</span>
                            </span>
                            <span class="list-end col">
                                <span class="money">{{ money($order->total, $order->currency) }}</span>
                                <span class="badge badge-{{ $order->status }}">{{ $order->status_label }}</span>
                            </span>
                        </a>
                    </li>
                    @endforeach
                </ul>
                @endif
            </section>

            <section class="card" aria-labelledby="top-title">
                <header class="card-header">
                    <h2 class="card-title" id="top-title"><i class="fas fa-star" aria-hidden="true"></i> {{ __('Top services') }}</h2>
                    <span class="card-subtitle">{{ __('Completed orders') }}</span>
                </header>
                @if($topServices->isEmpty())
                    <p class="empty">{{ __('Shows up once orders are completed.') }}</p>
                @else
                    @php $mostOrders = max(1, $topServices->max('order_count')); @endphp
                    <ol class="rank-list">
                        @foreach($topServices as $service)
                        <li>
                            <div class="rank-row"><span>{{ $service->name }}</span><span>{{ __($service->order_count === 1 ? ':count order' : ':count orders', ['count' => $service->order_count]) }} · {{ money($service->total_revenue, whole: true) }}</span></div>
                            <div class="rank-track" aria-hidden="true"><span class="rank-bar" style="width: {{ round($service->order_count / $mostOrders * 100, 1) }}%"></span></div>
                        </li>
                        @endforeach
                    </ol>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    // "Find order" puts the cursor in the search box at the top
    document.querySelector('[data-focus-search]')?.addEventListener('click', () => {
        const search = document.querySelector('[data-search-shortcut]');
        search?.focus();
        search?.scrollIntoView({ block: 'center' });
    });

    // Action center tabs (arrow keys move between them, like any tab list)
    const tabs = Array.from(document.querySelectorAll('.attention-tabs [role="tab"]'));
    const select = tab => {
        tabs.forEach(t => {
            const on = t === tab;
            t.setAttribute('aria-selected', String(on));
            t.tabIndex = on ? 0 : -1;
            document.getElementById(t.getAttribute('aria-controls')).hidden = !on;
        });
    };
    tabs.forEach(tab => {
        tab.addEventListener('click', () => select(tab));
        tab.addEventListener('keydown', e => {
            const usable = tabs.filter(t => !t.disabled);
            const i = usable.indexOf(tab);
            const next = e.key === 'ArrowRight' ? usable[(i + 1) % usable.length] : e.key === 'ArrowLeft' ? usable[(i - 1 + usable.length) % usable.length] : null;
            if (next) { e.preventDefault(); select(next); next.focus(); }
        });
    });

    // "Picked up": complete a paid, ready order right here (with Undo)
    const setStatus = (id, status) => sendJson(appUrl(`orders/${id}/update-status`), { status });
    document.querySelectorAll('[data-pickup]').forEach(button => {
        button.addEventListener('click', async () => {
            const id = button.dataset.pickup;
            const row = button.closest('li');
            button.classList.add('is-loading');
            try {
                await setStatus(id, 'completed');
                row.hidden = true;
                const count = document.querySelector('#tab-ready [data-count]');
                count.textContent = Math.max(0, Number(count.textContent) - 1);
                showToast(t('Order marked as picked up.'), 'success', 6000, {
                    label: t('Undo'),
                    onClick: async () => {
                        try { await setStatus(id, 'ready'); row.hidden = false; count.textContent = Number(count.textContent) + 1; }
                        catch (e) { showToast(e.message, 'error'); }
                    },
                });
            } catch (e) {
                showToast(e.message, 'error');
            } finally {
                button.classList.remove('is-loading');
            }
        });
    });

    // Sales chart: period switch + one tooltip that follows tap, hover and keyboard focus
    document.querySelectorAll('input[name="salesPeriod"]').forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('.bar-chart').forEach(chart => { chart.hidden = chart.dataset.period !== radio.value; });
            const subtitle = document.querySelector('#sales-title').closest('.card-header').querySelector('.card-subtitle');
            subtitle.textContent = ({ week: t('Completed orders, by day'), month: t('Completed orders, by week'), year: t('Completed orders, by month') })[radio.value];
        });
    });
    let tip = null;
    const showTip = col => {
        const plot = col.closest('.bar-plot');
        hideTip();
        tip = document.createElement('div');
        tip.className = 'bar-tip';
        const value = document.createElement('strong');
        value.textContent = col.dataset.value;
        const label = document.createElement('span');
        label.textContent = col.dataset.label;
        tip.append(value, label);
        plot.append(tip);
        const bar = col.querySelector('.bar');
        const left = col.offsetLeft + col.offsetWidth / 2;
        tip.style.left = Math.min(Math.max(left, tip.offsetWidth / 2), plot.offsetWidth - tip.offsetWidth / 2) + 'px';
        tip.style.top = (plot.offsetHeight - (bar ? bar.offsetHeight : 0)) + 'px';
        col.classList.add('is-active');
    };
    const hideTip = () => {
        tip?.remove();
        tip = null;
        document.querySelectorAll('.bar-col.is-active').forEach(c => c.classList.remove('is-active'));
    };
    document.querySelectorAll('.bar-col').forEach(col => {
        col.addEventListener('pointerenter', e => { if (e.pointerType === 'mouse') showTip(col); });
        col.addEventListener('pointerleave', e => { if (e.pointerType === 'mouse') hideTip(); });
        col.addEventListener('focus', () => showTip(col));
        col.addEventListener('blur', hideTip);
        col.addEventListener('click', () => showTip(col));
    });
})();
</script>
@endpush
