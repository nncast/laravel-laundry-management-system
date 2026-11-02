@extends('layouts.app')

@section('title', 'Order ' . $order->order_number)

@php
    $isManager = in_array(session('staff.role'), ['manager', 'admin'], true);
    $flow = ['pending' => 'Pending', 'processing' => 'Processing', 'ready' => 'Ready for pickup', 'completed' => 'Completed'];
    $position = array_search($order->status, array_keys($flow), true);
    // The one obvious next step: [status, button text, icon, button style]
    $next = [
        'pending' => ['processing', 'Start processing', 'fa-play', 'btn-primary'],
        'processing' => ['ready', 'Mark ready for pickup', 'fa-bell', 'btn-primary'],
        'ready' => ['completed', 'Mark picked up', 'fa-check', 'btn-success'],
    ][$order->status] ?? null;
    $balance = $order->balance;
    $canPay = $order->status !== 'cancelled' && $balance > 0;
    $addonsTotal = $order->addons->sum('pivot.price');
    // This order's own currency (it may predate a currency change), for the payment dialog
    $orderFmt = ['symbol' => currency_symbol($order->currency), 'decimals' => app(\App\Support\Format::class)->decimals($order->currency)];
    $methods = ['cash' => ['Cash', 'fa-money-bill-wave'], 'card' => ['Card', 'fa-credit-card'], 'transfer' => ['Transfer', 'fa-building-columns'], 'other' => ['Other', 'fa-ellipsis']];
@endphp

@section('page-header')
<header class="page-header detail-header" id="orderHeader" data-refresh>
    <div class="page-heading">
        <p class="eyebrow"><a href="{{ route('orders.index') }}" class="back-link"><i class="fas fa-arrow-left" aria-hidden="true"></i> Orders</a></p>
        <h1 class="page-title">{{ $order->order_number }}</h1>
        <div class="cluster header-badges">
            <span class="badge badge-{{ $order->status }}">{{ $order->status_label }}</span>
            @if($order->status !== 'cancelled')
                @if($balance <= 0)
                    <span class="badge badge-paid">Paid</span>
                @else
                    <span class="badge {{ $order->paid_amount > 0 ? 'badge-partial' : 'badge-unpaid' }}">{{ $order->paid_amount > 0 ? 'Partly paid' : 'Unpaid' }}</span>
                @endif
            @endif
            <span class="muted">{{ fmt_date($order->order_date) }}</span>
            @if($order->ready_by && in_array($order->status, \App\Models\Order::OPEN_STATUSES, true))
                <span class="muted">· Ready by <strong>{{ fmt_date($order->ready_by) }}</strong></span>
            @endif
        </div>
    </div>
    <div class="page-actions">
        @if($next)
            <button type="button" class="btn {{ $next[3] }}" data-set-status="{{ $next[0] }}"><i class="fas {{ $next[2] }}" aria-hidden="true"></i> {{ $next[1] }}</button>
        @endif
        @if($canPay)
            <button type="button" class="btn {{ $next ? 'btn-secondary' : 'btn-primary' }}" data-dialog-open="paymentDialog"><i class="fas fa-wallet" aria-hidden="true"></i> Add payment</button>
        @endif
        @if($order->can_edit)
            <a href="{{ route('pos.edit', $order) }}" class="btn btn-secondary"><i class="fas fa-pen" aria-hidden="true"></i> Edit</a>
        @endif
        {{-- Receipt once paid, claim ticket before; the print page switches documents and paper --}}
        <a href="{{ route('orders.document', [$order, $order->payments->isNotEmpty() ? 'receipt' : 'ticket']) }}" class="btn btn-secondary"><i class="fas fa-print" aria-hidden="true"></i> Print</a>
    </div>
</header>
@endsection

@push('styles')
<style>
.detail-header .page-title { font-size: 26px; }
.back-link { display: inline-flex; align-items: center; gap: 6px; min-height: 32px; color: var(--text-muted); text-decoration: none; }
.header-badges { margin-top: var(--s-2); }

.progress { display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--s-2); list-style: none; padding: var(--s-4) var(--s-5); }
.progress li { display: flex; flex-direction: column; gap: 6px; font-size: var(--fs-sm); font-weight: 700; color: var(--text-muted); }
.progress li::before { content: ""; height: 6px; border-radius: 3px; background: var(--surface-sunken); }
.progress li.done::before { background: var(--accent); }
.progress li.current { color: var(--accent); }
.progress li.current::before { background: var(--accent); box-shadow: 0 0 0 3px var(--accent-soft-strong); }

.items-table td:first-child { font-weight: 700; }
.totals { padding: var(--s-4) var(--s-5) var(--s-5); border-top: 1px solid var(--border); }
.totals .summary { max-width: 380px; margin-left: auto; }

.balance-card { text-align: center; }
.balance-card .card-body { display: grid; gap: var(--s-2); }
.balance-amount { font-size: 32px; font-weight: 800; letter-spacing: -0.02em; }
.balance-card.is-due .balance-amount { color: var(--danger-text); }
.balance-card.is-paid .balance-amount { color: var(--success-text); }

.contact-row { display: flex; align-items: center; gap: var(--s-3); min-height: var(--tap); color: var(--text); text-decoration: none; }
.contact-row > i { width: 20px; text-align: center; color: var(--text-muted); }
.payment-row { display: flex; align-items: center; gap: var(--s-3); padding: var(--s-3) var(--s-5); border-bottom: 1px solid var(--border); }
.payment-row:last-child { border-bottom: 0; }
.notes-form .textarea { min-height: 110px; }

.pay-due { text-align: center; padding: var(--s-4); border-radius: var(--radius); background: var(--accent-soft); }
.pay-due strong { display: block; font-size: 30px; letter-spacing: -0.02em; color: var(--accent); }
.quick-cash { display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--s-2); }
.quick-cash .btn { padding: 0 var(--s-2); }
.pay-result { min-height: 24px; font-weight: 700; text-align: center; }

@media (max-width: 767px) {
    .progress { padding: var(--s-3) var(--s-4); }
    .progress li span { font-size: 11px; }
}
</style>
@endpush

@section('content')
<div class="grid-sidebar">
    <div class="stack">
        <div id="orderProgress" data-refresh>
        @if($order->status === 'cancelled')
            <div class="alert alert-danger no-print"><i class="fas fa-ban" aria-hidden="true"></i><span>This order was cancelled. It no longer counts in sales.</span></div>
        @else
            <div class="card no-print">
                <ol class="progress" aria-label="Order progress">
                    @foreach($flow as $key => $label)
                        <li class="{{ $loop->index < $position ? 'done' : ($loop->index === $position ? 'current' : '') }}" @if($loop->index === $position) aria-current="step" @endif><span>{{ $label }}</span></li>
                    @endforeach
                </ol>
            </div>
        @endif
        </div>

        <section class="card" id="orderItems" data-refresh aria-labelledby="items-title">
            <header class="card-header"><h2 class="card-title" id="items-title"><i class="fas fa-shirt" aria-hidden="true"></i> Items</h2></header>
            <div class="table-wrap static">
                <table class="table items-table">
                    <thead><tr><th>Service</th><th class="num">Price</th><th class="num">Qty</th><th class="num">Total</th></tr></thead>
                    <tbody>
                        @forelse($order->items as $item)
                        <tr>
                            <td>{{ $item->service->name ?? 'Deleted service' }}</td>
                            <td class="num">{{ money($item->price, $order->currency) }}</td>
                            <td class="num">{{ $item->qty }}</td>
                            <td class="num">{{ money($item->total, $order->currency) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="muted">No services on this order.</td></tr>
                        @endforelse
                        @foreach($order->addons as $addon)
                        <tr>
                            <td>{{ $addon->name }} <span class="badge">Add-on</span></td>
                            <td class="num">{{ money($addon->pivot->price, $order->currency) }}</td>
                            <td class="num">1</td>
                            <td class="num">{{ money($addon->pivot->price, $order->currency) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="totals">
                <div class="summary">
                    <div class="summary-row"><span>Services</span><span>{{ money($order->subtotal, $order->currency) }}</span></div>
                    @if($addonsTotal > 0)<div class="summary-row"><span>Add-ons</span><span>{{ money($addonsTotal, $order->currency) }}</span></div>@endif
                    @if($order->discount > 0)<div class="summary-row"><span>Discount{{ $order->discount_percent !== null ? ' (' . (float) $order->discount_percent . '%)' : '' }}</span><span>−{{ money($order->discount, $order->currency) }}</span></div>@endif
                    <div class="summary-row summary-total"><span>Total</span><span>{{ money($order->total, $order->currency) }}</span></div>
                    <div class="summary-row"><span>Paid</span><span>{{ money($order->paid_amount, $order->currency) }}</span></div>
                    <div class="summary-row"><span>Balance</span><span class="{{ $balance > 0 ? 'text-danger' : 'text-success' }}">{{ money(max($balance, 0), $order->currency) }}</span></div>
                </div>
            </div>
        </section>

        <section class="card" id="orderPayments" data-refresh aria-labelledby="payments-title">
            <header class="card-header">
                <h2 class="card-title" id="payments-title"><i class="fas fa-receipt" aria-hidden="true"></i> Payments</h2>
                @if($canPay)<button type="button" class="btn btn-sm btn-soft no-print" data-dialog-open="paymentDialog"><i class="fas fa-plus" aria-hidden="true"></i> Add payment</button>@endif
            </header>
            @forelse($order->payments->sortByDesc('created_at') as $payment)
                <div class="payment-row">
                    <span class="bubble-tile tone-green" aria-hidden="true"><i class="fas {{ $methods[$payment->payment_method][1] ?? 'fa-wallet' }}"></i></span>
                    <span class="list-main">
                        <span class="list-title">{{ $methods[$payment->payment_method][0] ?? ucfirst($payment->payment_method) }}</span>
                        <span class="list-sub">{{ $payment->receipt_number }} · {{ fmt_date($payment->created_at) }} · {{ fmt_time($payment->created_at) }}@if($payment->receiver) · {{ $payment->receiver->name }}@endif</span>
                    </span>
                    <strong class="money">{{ money($payment->amount, $order->currency) }}</strong>
                    <a class="icon-btn" href="{{ route('orders.document', [$order, 'receipt', 'payment' => $payment->id]) }}" data-print aria-label="Print receipt {{ $payment->receipt_number }}"><i class="fas fa-print" aria-hidden="true"></i></a>
                </div>
            @empty
                <p class="empty">No payments yet.</p>
            @endforelse
        </section>

        <section class="card" aria-labelledby="notes-title">
            <header class="card-header"><h2 class="card-title" id="notes-title"><i class="fas fa-note-sticky" aria-hidden="true"></i> Notes</h2></header>
            <div class="card-body">
                <form class="notes-form stack-sm no-print" id="notesForm">
                    <label class="sr-only" for="notes">Notes for this order</label>
                    <textarea class="textarea" id="notes" maxlength="1000" placeholder="Stains, special care, pickup instructions…">{{ $order->notes }}</textarea>
                    <div><button type="submit" class="btn btn-secondary"><i class="fas fa-floppy-disk" aria-hidden="true"></i> Save notes</button></div>
                </form>
            </div>
        </section>
    </div>

    <aside class="stack">
        <section id="orderBalance" data-refresh class="card balance-card {{ $order->status === 'cancelled' ? '' : ($balance > 0 ? 'is-due' : 'is-paid') }}" aria-label="Balance">
            <div class="card-body">
                <span class="eyebrow">{{ $balance > 0 && $order->status !== 'cancelled' ? 'Balance due' : 'Balance' }}</span>
                <span class="balance-amount money">{{ money(max($balance, 0), $order->currency) }}</span>
                <span class="muted">of {{ money($order->total, $order->currency) }} · {{ money($order->paid_amount, $order->currency) }} paid</span>
                @if($canPay)
                    <button type="button" class="btn btn-primary btn-lg btn-block no-print" data-dialog-open="paymentDialog"><i class="fas fa-wallet" aria-hidden="true"></i> Collect payment</button>
                @endif
            </div>
        </section>

        <section class="card" aria-labelledby="customer-title">
            <header class="card-header"><h2 class="card-title" id="customer-title"><i class="fas fa-user" aria-hidden="true"></i> Customer</h2></header>
            <div class="card-body stack-sm">
                @if($order->customer)
                    <strong>{{ $order->customer->name }}</strong>
                    @if($order->customer->contact)
                        <a class="contact-row" href="tel:{{ $order->customer->contact }}"><i class="fas fa-phone" aria-hidden="true"></i> {{ $order->customer->contact }}</a>
                    @endif
                    @if($order->customer->address)
                        <span class="contact-row"><i class="fas fa-location-dot" aria-hidden="true"></i> {{ $order->customer->address }}</span>
                    @endif
                    <a href="{{ route('orders.index', ['search' => $order->customer->name]) }}" class="btn btn-ghost btn-sm no-print" style="align-self:flex-start"><i class="fas fa-clock-rotate-left" aria-hidden="true"></i> Their other orders</a>
                @else
                    <span class="muted">Walk-in customer</span>
                @endif
            </div>
        </section>

        <section class="card no-print" id="orderDetailsCard" data-refresh aria-labelledby="details-title">
            <header class="card-header"><h2 class="card-title" id="details-title"><i class="fas fa-circle-info" aria-hidden="true"></i> Details</h2></header>
            <div class="card-body stack-sm">
                <dl class="dl">
                    <dt>Taken by</dt><dd>{{ $order->staff->name ?? '—' }}</dd>
                    <dt>Ready by</dt><dd>{{ $order->ready_by ? fmt_date($order->ready_by) : '—' }}</dd>
                    @if($order->weight)<dt>Weight</dt><dd>{{ fmt_weight($order->weight) }}</dd>@endif
                    <dt>Created</dt><dd>{{ fmt_datetime($order->created_at) }}</dd>
                    <dt>Updated</dt><dd>{{ fmt_datetime($order->updated_at) }}</dd>
                </dl>
                <div class="field">
                    <label class="label" for="statusSelect">Change status</label>
                    <select class="select" id="statusSelect" data-current="{{ $order->status }}">
                        @foreach(\App\Models\Order::STATUSES as $value)
                            <option value="{{ $value }}" @selected($order->status === $value)>{{ ['pending' => 'Pending', 'processing' => 'Processing', 'ready' => 'Ready for pickup', 'completed' => 'Completed', 'cancelled' => 'Cancelled'][$value] }}</option>
                        @endforeach
                    </select>
                </div>
                @if(!in_array($order->status, ['completed', 'cancelled'], true))
                    <button type="button" class="btn btn-danger-soft" data-set-status="cancelled"><i class="fas fa-ban" aria-hidden="true"></i> Cancel order</button>
                @endif
            </div>
        </section>
    </aside>
</div>

<dialog id="paymentDialog" class="dialog dialog-sm" aria-labelledby="paymentTitle" data-refresh data-balance="{{ max($balance, 0) }}">
    <form id="paymentForm" novalidate>
        <div class="dialog-header">
            <h2 class="dialog-title" id="paymentTitle">Add payment</h2>
            <button type="button" class="icon-btn" data-dialog-close aria-label="Close"><i class="fas fa-xmark" aria-hidden="true"></i></button>
        </div>
        <div class="dialog-body">
            <div class="pay-due"><span class="muted">Balance due</span><strong class="money">{{ money(max($balance, 0), $order->currency) }}</strong></div>
            <div class="field">
                <span class="label" id="methodLabel">Method</span>
                <div class="segmented block" role="radiogroup" aria-labelledby="methodLabel">
                    @foreach($methods as $value => [$label, $icon])
                        <label><input type="radio" name="payment_method" value="{{ $value }}" @checked($value === 'cash')><span><i class="fas {{ $icon }}" aria-hidden="true"></i> {{ $label }}</span></label>
                    @endforeach
                </div>
            </div>
            <div class="field">
                <label class="label" for="amount">Amount received</label>
                <div class="input-affix">
                    <span class="affix" aria-hidden="true">{{ currency_symbol($order->currency) }}</span>
                    <input class="input" type="number" id="amount" name="amount" inputmode="decimal" min="{{ money_step($order->currency) }}" step="{{ money_step($order->currency) }}" value="{{ money_input(max($balance, 0), $order->currency) }}" aria-describedby="payResult">
                </div>
                <div class="field-error" id="amountError"></div>
            </div>
            <div class="quick-cash" aria-label="Quick amounts">
                <button type="button" class="btn btn-secondary" data-amount="{{ max($balance, 0) }}">Exact</button>
                @foreach(array_slice(app(\App\Support\Format::class)->currencyInfo($order->currency)['bills'], -3) as $bill)
                    <button type="button" class="btn btn-secondary" data-amount="{{ $bill }}">{{ money($bill, $order->currency, whole: true) }}</button>
                @endforeach
            </div>
            <p class="pay-result" id="payResult" aria-live="polite"></p>
        </div>
        <div class="dialog-footer">
            <button type="button" class="btn btn-secondary" data-dialog-close>Cancel</button>
            <button type="submit" class="btn btn-primary" id="payButton">Record payment</button>
        </div>
    </form>
</dialog>
@endsection

@push('scripts')
<script>
(function () {
    const orderId = {{ $order->id }};
    const orderFmt = @json($orderFmt);
    const money = value => window.money(value, orderFmt);
    const labels = { pending: 'Pending', processing: 'Processing', ready: 'Ready for pickup', completed: 'Completed', cancelled: 'Cancelled' };

    // ---- Status: the page updates in place (notes being typed stay as they are) ----
    async function setStatus(status, control) {
        const select = document.getElementById('statusSelect');
        if (status === 'cancelled') {
            const ok = await confirmAction({ title: 'Cancel this order?', message: 'It will no longer count in sales. You can change the status back later.', confirmText: 'Cancel order', cancelText: 'Keep order' });
            if (!ok) { select.value = select.dataset.current; return; }
        }
        control?.classList.add('is-loading');
        try {
            await sendJson(appUrl(`orders/${orderId}/update-status`), { status });
            await refreshRegions();
            showToast(`Order marked ${labels[status].toLowerCase()}.`, 'success');
        } catch (e) {
            select.value = select.dataset.current;
            showToast(e.message, 'error');
        } finally {
            control?.classList.remove('is-loading');
        }
    }
    document.addEventListener('click', e => {
        const button = e.target.closest('[data-set-status]');
        if (button) setStatus(button.dataset.setStatus, button);
    });
    document.addEventListener('change', e => {
        if (e.target.id === 'statusSelect') setStatus(e.target.value, null);
    });

    // ---- Notes ----
    document.getElementById('notesForm').addEventListener('submit', async e => {
        e.preventDefault();
        const button = e.target.querySelector('button[type="submit"]');
        button.classList.add('is-loading');
        try {
            await sendJson(appUrl(`orders/${orderId}/add-notes`), { notes: document.getElementById('notes').value });
            showToast('Notes saved.', 'success');
        } catch (err) {
            showToast(err.message, 'error');
        } finally {
            button.classList.remove('is-loading');
        }
    });

    // ---- Payment dialog (bound again each time the page refreshes it) ----
    function bindPayment() {
        const dialog = document.getElementById('paymentDialog');
        const form = document.getElementById('paymentForm');
        if (!dialog || !form) return;
        const balance = Number(dialog.dataset.balance) || 0;
        const amount = document.getElementById('amount');
        const result = document.getElementById('payResult');
        const payButton = document.getElementById('payButton');
        const error = document.getElementById('amountError');
        const describe = () => {
            const value = parseFloat(amount.value) || 0;
            if (value <= 0) { result.textContent = ''; payButton.textContent = 'Record payment'; return; }
            if (value > balance) { result.innerHTML = `Change to give back: <span class="text-success">${money(value - balance)}</span>`; payButton.textContent = 'Record ' + money(balance); }
            else if (value < balance) { result.innerHTML = `Partial payment · <span class="text-danger">${money(balance - value)}</span> still due`; payButton.textContent = 'Record ' + money(value); }
            else { result.textContent = 'Pays the order in full.'; payButton.textContent = 'Record ' + money(value); }
        };
        amount.addEventListener('input', describe);
        dialog.querySelectorAll('[data-amount]').forEach(b => b.addEventListener('click', () => { amount.value = Number(b.dataset.amount).toFixed(orderFmt.decimals); describe(); }));
        dialog.addEventListener('close', () => { error.textContent = ''; amount.removeAttribute('aria-invalid'); });
        describe();

        form.addEventListener('submit', async e => {
            e.preventDefault();
            const value = parseFloat(amount.value) || 0;
            if (value <= 0) { error.textContent = 'Enter the amount received.'; amount.setAttribute('aria-invalid', 'true'); amount.focus(); return; }
            payButton.classList.add('is-loading');
            try {
                const data = await sendJson(appUrl(`orders/${orderId}/add-payment`), {
                    amount: value,
                    payment_method: dialog.querySelector('input[name="payment_method"]:checked').value,
                });
                closeDialog(dialog);
                await refreshRegions();
                showToast(data.change > 0 ? `Payment recorded. Give back ${money(data.change)} change.` : 'Payment recorded.', 'success', 8000,
                    { label: 'Print receipt', onClick: () => printDocument(appUrl(`orders/${orderId}/print/receipt`)) });
            } catch (err) {
                payButton.classList.remove('is-loading');
                error.textContent = err.message;
            }
        });
    }
    bindPayment();
    document.addEventListener('regions:refreshed', bindPayment);

    @if($canPay && request()->boolean('pay'))
    openDialog('paymentDialog');
    @endif
})();
</script>
@endpush
