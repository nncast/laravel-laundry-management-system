@extends('layouts.app')

@section('title', __('Orders'))
@section('page-title', __('Orders'))

@php
    $isManager = in_array(session('staff.role'), ['manager', 'admin'], true);
    $tabs = ['' => __('All'), 'open' => __('Open'), 'overdue' => __('Overdue'), 'pending' => __('Pending'), 'processing' => __('Processing'),
             'ready' => __('Ready for pickup'), 'completed' => __('Completed'), 'cancelled' => __('Cancelled')];
    $filtersOn = $search !== '' || $payment !== '' || $from !== '' || $to !== '';
    // The one tap that moves an open order along: [next status, button text, icon]
    $nextStep = [
        'pending' => ['processing', __('Start'), 'fa-play'],
        'processing' => ['ready', __('Ready'), 'fa-bell'],
        'ready' => ['completed', __('Picked up'), 'fa-check'],
    ];
    $statusLabels = collect(\App\Models\Order::STATUSES)->mapWithKeys(fn ($s) => [$s => \App\Models\Order::labelFor($s)])->all();
@endphp

@section('page-subtitle')
    {{ __($orders->total() === 1 ? '1 order' : ':count orders', ['count' => fmt_number($orders->total())]) }}{{ $status !== '' ? ' · ' . $tabs[$status] : '' }}{{ $filtersOn ? ' · ' . __('filtered') : '' }}
@endsection

@push('styles')
<style>
.order-tabs { margin-bottom: var(--s-3); }
.filters { display: flex; flex-wrap: wrap; align-items: flex-end; gap: var(--s-2); margin-bottom: var(--s-3); }
.filters .search { flex: 1 1 240px; }
.filters .field { flex: 0 1 170px; }
.filter-fields { display: contents; }
.filter-toggle { display: none; }
.icon-btn.slot { visibility: hidden; }
.filters .label { font-size: var(--fs-xs); }
.saved-views { display: flex; flex-wrap: wrap; align-items: center; gap: var(--s-2); margin-bottom: var(--s-4); }
.saved-views:empty { display: none; }
.saved-view { display: inline-flex; align-items: center; border: 1px solid var(--border-strong); border-radius: var(--radius-pill); background: var(--surface); }
.saved-view a { display: inline-flex; align-items: center; gap: 6px; min-height: 40px; padding: 0 4px 0 14px; color: var(--text-secondary); font-weight: 700; text-decoration: none; }
.saved-view button { width: 40px; height: 40px; border: 0; background: none; color: var(--text-muted); border-radius: 50%; }
.order-no { font-weight: 800; white-space: nowrap; }
.pay-cell { display: flex; flex-direction: column; align-items: flex-start; gap: 2px; }
.step-btn { min-width: 112px; }
@media (max-width: 767px) {
    .filter-toggle { display: inline-flex; }
    .filter-fields { display: none; flex-wrap: wrap; align-items: flex-end; gap: var(--s-2); width: 100%; }
    .filters.is-open .filter-fields { display: flex; }
    .filters .field { flex: 1 1 calc(50% - 4px); }
    .filter-fields > .btn { flex: 1; }
    .pay-cell { align-items: flex-end; }
    .step-btn { flex: 1; }
}
</style>
@endpush

@section('content')
<nav class="chips scroll order-tabs" aria-label="{{ __('Order status') }}">
    @foreach($tabs as $key => $label)
        <a href="{{ route('orders.index', array_filter(['status' => $key, 'search' => $search, 'payment' => $payment, 'from' => $from, 'to' => $to])) }}"
           class="chip" @if($status === $key) aria-current="page" @endif>
            {{ $label }} <span class="chip-count">{{ $counts[$key] }}</span>
        </a>
    @endforeach
</nav>

<form class="filters" method="GET" action="{{ route('orders.index') }}" id="filterForm" role="search">
    @if($status !== '')<input type="hidden" name="status" value="{{ $status }}">@endif
    <div class="search">
        <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
        <input class="input" type="search" name="search" value="{{ $search }}" placeholder="{{ __('Order number, customer or phone') }}" aria-label="{{ __('Search orders') }}" autocomplete="off">
    </div>
    <button type="button" class="btn btn-secondary filter-toggle" aria-expanded="false" aria-controls="filterFields">
        <i class="fas fa-sliders" aria-hidden="true"></i> {{ __('Filters') }} @if($payment !== '' || $from !== '' || $to !== '')<span class="chip-count">{{ ($payment !== '') + ($from !== '') + ($to !== '') }}</span>@endif
    </button>
    <div class="filter-fields" id="filterFields">
    <div class="field">
        <label class="label" for="payment">{{ __('Payment') }}</label>
        <select class="select" id="payment" name="payment">
            <option value="">{{ __('Any') }}</option>
            <option value="unpaid" @selected($payment === 'unpaid')>{{ __('Has a balance') }}</option>
            <option value="partial" @selected($payment === 'partial')>{{ __('Partly paid') }}</option>
            <option value="paid" @selected($payment === 'paid')>{{ __('Fully paid') }}</option>
        </select>
    </div>
    <div class="field">
        <label class="label" for="from">{{ __('From') }}</label>
        <input class="input" type="date" id="from" name="from" value="{{ $from }}">
    </div>
    <div class="field">
        <label class="label" for="to">{{ __('To') }}</label>
        <input class="input" type="date" id="to" name="to" value="{{ $to }}">
    </div>
    <button type="submit" class="btn btn-secondary"><i class="fas fa-filter" aria-hidden="true"></i> {{ __('Apply') }}</button>
    </div>
    @if($filtersOn || $status !== '')
        <a href="{{ route('orders.index') }}" class="btn btn-ghost">{{ __('Clear') }}</a>
        <button type="button" class="btn btn-ghost" id="saveViewBtn"><i class="fas fa-bookmark" aria-hidden="true"></i> {{ __('Save view') }}</button>
    @endif
</form>

<div class="saved-views" id="savedViews" aria-label="{{ __('Saved views') }}"></div>

@if($orders->isEmpty())
    <div class="card">
        <div class="empty">
            @if($filtersOn || $status !== '')
                <span class="empty-icon" aria-hidden="true"><i class="fas fa-filter-circle-xmark"></i></span>
                <p class="empty-title">{{ __('No orders match') }}</p>
                <p>{{ __('Try another tab, or clear the filters.') }}</p>
                <a href="{{ route('orders.index') }}" class="btn btn-secondary">{{ __('Clear filters') }}</a>
            @else
                <x-mascot mood="curious" class="empty-art mascot-ink" />
                <p class="empty-title">{{ __('No orders yet') }}</p>
                <p>{{ __('New orders from the point of sale show up here.') }}</p>
                <a href="{{ route('pos.index') }}" class="btn btn-primary"><i class="fas fa-plus" aria-hidden="true"></i> {{ __('New order') }}</a>
            @endif
        </div>
    </div>
@else
<div class="card card-flush">
    <div class="table-wrap">
        <table class="table table-stack" id="ordersTable">
            <thead>
                <tr>
                    <th class="check-col"><label class="check" title="{{ __('Select all') }}"><input type="checkbox" id="selectAll"><span class="sr-only">{{ __('Select all orders on this page') }}</span></label></th>
                    <th>{{ __('Order') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Payment') }}</th>
                    <th class="num">{{ __('Total') }}</th>
                    <th class="actions"><span class="sr-only">{{ __('Actions') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                @php $step = $nextStep[$order->status] ?? null; @endphp
                <tr data-href="{{ route('orders.details', $order) }}" data-id="{{ $order->id }}" data-status="{{ $order->status }}">
                    <td class="check-col"><label class="check"><input type="checkbox" class="row-check" value="{{ $order->id }}"><span class="sr-only">Select {{ $order->order_number }}</span></label></td>
                    <td class="cell-primary">
                        <a href="{{ route('orders.details', $order) }}" class="cell-title order-no">{{ $order->order_number }}</a>
                        <div class="cell-sub">{{ fmt_date($order->order_date) }}</div>
                    </td>
                    <td data-label="{{ __('Customer') }}">
                        <div>
                            <div class="cell-title">{{ $order->customer->name ?? __('Walk-in customer') }}</div>
                            @if($order->customer?->contact)<div class="cell-sub">{{ $order->customer->contact }}</div>@endif
                        </div>
                    </td>
                    <td data-label="{{ __('Status') }}"><span class="badge badge-{{ $order->status }}" data-status-badge>{{ $order->status_label }}</span></td>
                    <td data-label="{{ __('Payment') }}">
                        <div class="pay-cell">
                            @if($order->status === 'cancelled')
                                <span class="cell-sub">—</span>
                            @elseif($order->balance <= 0)
                                <span class="badge badge-paid">{{ __('Paid') }}</span>
                            @else
                                <span class="badge {{ $order->paid_amount > 0 ? 'badge-partial' : 'badge-unpaid' }}">{{ $order->paid_amount > 0 ? __('Partly paid') : __('Unpaid') }}</span>
                                <span class="cell-sub money">{{ __(':amount due', ['amount' => money($order->balance, $order->currency)]) }}</span>
                            @endif
                        </div>
                    </td>
                    <td data-label="{{ __('Total') }}" class="num"><strong>{{ money($order->total, $order->currency) }}</strong></td>
                    <td class="actions">
                        <div class="cell-actions">
                            @if($step && $order->status === 'ready' && $order->balance > 0)
                                <a href="{{ route('orders.details', ['order' => $order, 'pay' => 1]) }}" class="btn btn-sm btn-soft step-btn"><i class="fas fa-wallet" aria-hidden="true"></i> {{ __('Collect') }}</a>
                            @elseif($step)
                                <button type="button" class="btn btn-sm btn-soft step-btn" data-next="{{ $step[0] }}"><i class="fas {{ $step[2] }}" aria-hidden="true"></i> {{ $step[1] }}</button>
                            @endif
                            @if($order->can_edit)
                                <a href="{{ route('pos.edit', $order) }}" class="icon-btn" aria-label="{{ __('Edit :number', ['number' => $order->order_number]) }}" title="{{ __('Edit') }}"><i class="fas fa-pen" aria-hidden="true"></i></a>
                            @else
                                <span class="icon-btn slot" aria-hidden="true"></span>
                            @endif
                            @if($isManager && $order->paid_amount <= 0)
                                <form action="{{ route('orders.destroy', $order) }}" method="POST" data-confirm="{{ __(':number will be removed for good.', ['number' => $order->order_number]) }}" data-confirm-title="{{ __('Delete this order?') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="icon-btn danger" aria-label="{{ __('Delete :number', ['number' => $order->order_number]) }}" title="{{ __('Delete') }}"><i class="fas fa-trash" aria-hidden="true"></i></button>
                                </form>
                            @elseif($isManager)
                                <span class="icon-btn slot" aria-hidden="true"></span>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $orders->links() }}
</div>
@endif

<div class="bulk-bar" id="bulkBar" hidden role="region" aria-label="{{ __('Bulk actions') }}">
    <span class="bulk-count" id="bulkCount">{{ __(':count selected', ['count' => 0]) }}</span>
    <button type="button" class="btn btn-sm btn-ghost" data-bulk="processing"><i class="fas fa-play" aria-hidden="true"></i> {{ __('Processing') }}</button>
    <button type="button" class="btn btn-sm btn-ghost" data-bulk="ready"><i class="fas fa-bell" aria-hidden="true"></i> {{ __('Ready') }}</button>
    <button type="button" class="btn btn-sm btn-ghost" data-bulk="completed"><i class="fas fa-check" aria-hidden="true"></i> {{ __('Completed') }}</button>
    <button type="button" class="icon-btn bulk-clear" id="bulkClear" aria-label="{{ __('Clear selection') }}"><i class="fas fa-xmark" aria-hidden="true"></i></button>
</div>

<dialog id="saveViewDialog" class="dialog dialog-sm" aria-labelledby="saveViewTitle">
    <form method="dialog" id="saveViewForm">
        <div class="dialog-header">
            <h2 class="dialog-title" id="saveViewTitle">{{ __('Save this view') }}</h2>
            <button type="button" class="icon-btn" data-dialog-close aria-label="{{ __('Close') }}"><i class="fas fa-xmark" aria-hidden="true"></i></button>
        </div>
        <div class="dialog-body">
            <div class="field">
                <label class="label" for="viewName">{{ __('Name') }}</label>
                <input class="input" id="viewName" maxlength="30" required placeholder="{{ __('e.g. Unpaid this week') }}">
                <p class="help">{{ __('Saved on this device. Tap it later to get these filters back.') }}</p>
            </div>
        </div>
        <div class="dialog-footer">
            <button type="button" class="btn btn-secondary" data-dialog-close>{{ __('Cancel') }}</button>
            <button type="submit" class="btn btn-primary" value="save">{{ __('Save view') }}</button>
        </div>
    </form>
</dialog>
@endsection

@push('scripts')
<script>
(function () {
    const statusLabels = @json($statusLabels);
    const steps = @json($nextStep);

    // Search as you type (after a short pause); dates and payment apply on change
    const form = document.getElementById('filterForm');
    let timer;
    form.search.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(() => form.submit(), 600); });
    ['payment', 'from', 'to'].forEach(name => form[name].addEventListener('change', () => form.submit()));
    form.querySelector('.filter-toggle').addEventListener('click', e => {
        const open = form.classList.toggle('is-open');
        e.currentTarget.setAttribute('aria-expanded', String(open));
    });

    // ---- Status changes (one row or many), with Undo ----
    const setStatus = (ids, status) => sendJson(appUrl('orders/bulk-status'), { ids, status });
    const paint = (row, status) => {
        row.dataset.status = status;
        const badge = row.querySelector('[data-status-badge]');
        badge.className = 'badge badge-' + status;
        badge.textContent = statusLabels[status];
        const button = row.querySelector('[data-next]');
        const step = steps[status];
        if (!button) return;
        if (step) {
            button.dataset.next = step[0];
            button.innerHTML = `<i class="fas ${step[2]}" aria-hidden="true"></i> ${step[1]}`;
            button.hidden = false;
        } else {
            button.hidden = true;
        }
    };
    const change = async (rows, status) => {
        const before = rows.map(r => [r, r.dataset.status]);
        try {
            const data = await setStatus(rows.map(r => Number(r.dataset.id)), status);
            rows.forEach(r => paint(r, status));
            showToast(rows.length === 1 ? t('Order marked as :status.', { status: statusLabels[status] }) : data.message, 'success', 6000, {
                label: t('Undo'),
                onClick: async () => {
                    try {
                        for (const [row, old] of before) { await setStatus([Number(row.dataset.id)], old); paint(row, old); }
                    } catch (e) { showToast(e.message, 'error'); }
                },
            });
        } catch (e) {
            showToast(e.message, 'error');
        }
    };
    document.querySelectorAll('[data-next]').forEach(button => {
        button.addEventListener('click', async () => {
            button.classList.add('is-loading');
            await change([button.closest('tr')], button.dataset.next);
            button.classList.remove('is-loading');
        });
    });

    // ---- Selection + bulk bar ----
    const checks = Array.from(document.querySelectorAll('.row-check'));
    const all = document.getElementById('selectAll');
    const bar = document.getElementById('bulkBar');
    const selectedRows = () => checks.filter(c => c.checked).map(c => c.closest('tr'));
    const refresh = () => {
        const n = selectedRows().length;
        checks.forEach(c => c.closest('tr').classList.toggle('is-selected', c.checked));
        bar.hidden = n === 0;
        document.getElementById('bulkCount').textContent = t(':count selected', { count: n });
        if (all) { all.checked = n > 0 && n === checks.length; all.indeterminate = n > 0 && n < checks.length; }
    };
    checks.forEach(c => c.addEventListener('change', refresh));
    all?.addEventListener('change', () => { checks.forEach(c => { c.checked = all.checked; }); refresh(); });
    document.getElementById('bulkClear').addEventListener('click', () => { checks.forEach(c => { c.checked = false; }); refresh(); });
    document.querySelectorAll('[data-bulk]').forEach(button => {
        button.addEventListener('click', async () => {
            const rows = selectedRows();
            const status = button.dataset.bulk;
            const ok = await confirmAction({
                title: t(rows.length === 1 ? 'Mark 1 order as :status?' : 'Mark :count orders as :status?', { count: rows.length, status: statusLabels[status] }),
                message: t('You can undo this right after.'),
                confirmText: t('Update'), tone: 'neutral',
            });
            if (!ok) return;
            await change(rows, status);
            checks.forEach(c => { c.checked = false; });
            refresh();
        });
    });

    // ---- Saved views (kept on this device) ----
    const KEY = 'soapOpera.orderViews';
    const load = () => { try { return JSON.parse(localStorage.getItem(KEY) || '[]'); } catch (e) { return []; } };
    const store = views => { try { localStorage.setItem(KEY, JSON.stringify(views)); } catch (e) { /* storage blocked */ } };
    const renderViews = () => {
        const box = document.getElementById('savedViews');
        box.replaceChildren();
        load().forEach((view, i) => {
            const chip = document.createElement('span');
            chip.className = 'saved-view';
            const link = document.createElement('a');
            link.href = appUrl('orders') + view.query;
            link.innerHTML = '<i class="fas fa-bookmark" aria-hidden="true"></i>';
            link.append(document.createTextNode(' ' + view.name));
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.setAttribute('aria-label', t('Remove saved view :name', { name: view.name }));
            remove.innerHTML = '<i class="fas fa-xmark" aria-hidden="true"></i>';
            remove.addEventListener('click', () => { const views = load(); views.splice(i, 1); store(views); renderViews(); });
            chip.append(link, remove);
            box.append(chip);
        });
    };
    renderViews();
    document.getElementById('saveViewBtn')?.addEventListener('click', () => {
        document.getElementById('viewName').value = '';
        openDialog('saveViewDialog');
    });
    document.getElementById('saveViewForm').addEventListener('submit', () => {
        const name = document.getElementById('viewName').value.trim();
        if (!name) return;
        store([...load().filter(v => v.name !== name), { name, query: location.search }].slice(-8));
        renderViews();
        showToast(t('Saved ":name".', { name }), 'success');
    });
})();
</script>
@endpush
