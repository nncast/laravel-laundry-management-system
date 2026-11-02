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
    $methods = ['cash' => ['Cash', 'fa-money-bill-wave'], 'card' => ['Card', 'fa-credit-card'], 'transfer' => ['Transfer', 'fa-building-columns'], 'other' => ['Other', 'fa-ellipsis']];
@endphp

@section('page-header')
<header class="page-header detail-header">
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
            <span class="muted">{{ $order->order_date?->format('D, M j, Y') }}</span>
        </div>
    </div>
    <div class="page-actions">
        @if($next)
            <button type="button" class="btn {{ $next[3] }}" data-set-status="{{ $next[0] }}"><i class="fas {{ $next[2] }}" aria-hidden="true"></i> {{ $next[1] }}</button>
        @endif
        @if($canPay)
            <button type="button" class="btn {{ $next ? 'btn-secondary' : 'btn-primary' }}" data-dialog-open="paymentDialog"><i class="fas fa-peso-sign" aria-hidden="true"></i> Add payment</button>
        @endif
        @if($order->can_edit)
            <a href="{{ route('pos.edit', $order) }}" class="btn btn-secondary"><i class="fas fa-pen" aria-hidden="true"></i> Edit</a>
        @endif
        <button type="button" class="btn btn-secondary" onclick="window.print()"><i class="fas fa-print" aria-hidden="true"></i> Print</button>
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

.print-only { display: none; }
@media (max-width: 767px) {
    .progress { padding: var(--s-3) var(--s-4); }
    .progress li span { font-size: 11px; }
}
@media print {
    .print-only { display: block; }
    .detail-header, .balance-card, .no-print, .progress { display: none !important; }
    .grid-sidebar { grid-template-columns: 1fr; }
    .print-head { display: flex; justify-content: space-between; gap: 24px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 2px solid #0b1b34; }
    .print-head h2 { font-size: 20px; }
}
</style>
@endpush

@section('content')
<div class="print-only print-head">
    <div>
        <h2>{{ $settings->business_name ?? 'Soap Opera' }}</h2>
        @if($settings->address)<div>{{ $settings->address }}</div>@endif
        @if($settings->contact)<div>{{ $settings->contact }}</div>@endif
    </div>
    <div class="text-right">
        <strong>Receipt</strong>
        <div>{{ $order->order_number }}</div>
        <div>{{ $order->order_date?->format('M j, Y') }}</div>
    </div>
</div>

<div class="grid-sidebar">
    <div class="stack">
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

        <section class="card" aria-labelledby="items-title">
            <header class="card-header"><h2 class="card-title" id="items-title"><i class="fas fa-shirt" aria-hidden="true"></i> Items</h2></header>
            <div class="table-wrap static">
                <table class="table items-table">
                    <thead><tr><th>Service</th><th class="num">Price</th><th class="num">Qty</th><th class="num">Total</th></tr></thead>
                    <tbody>
                        @forelse($order->items as $item)
                        <tr>
                            <td>{{ $item->service->name ?? 'Deleted service' }}</td>
                            <td class="num">₱{{ number_format($item->price, 2) }}</td>
                            <td class="num">{{ $item->qty }}</td>
                            <td class="num">₱{{ number_format($item->total, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="muted">No services on this order.</td></tr>
                        @endforelse
                        @foreach($order->addons as $addon)
                        <tr>
                            <td>{{ $addon->name }} <span class="badge">Add-on</span></td>
                            <td class="num">₱{{ number_format($addon->pivot->price, 2) }}</td>
                            <td class="num">1</td>
                            <td class="num">₱{{ number_format($addon->pivot->price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="totals">
                <div class="summary">
                    <div class="summary-row"><span>Services</span><span>₱{{ number_format($order->subtotal, 2) }}</span></div>
                    @if($addonsTotal > 0)<div class="summary-row"><span>Add-ons</span><span>₱{{ number_format($addonsTotal, 2) }}</span></div>@endif
                    @if($order->discount > 0)<div class="summary-row"><span>Discount{{ $order->discount_percent !== null ? ' (' . (float) $order->discount_percent . '%)' : '' }}</span><span>−₱{{ number_format($order->discount, 2) }}</span></div>@endif
                    <div class="summary-row summary-total"><span>Total</span><span>₱{{ number_format($order->total, 2) }}</span></div>
                    <div class="summary-row"><span>Paid</span><span>₱{{ number_format($order->paid_amount, 2) }}</span></div>
                    <div class="summary-row"><span>Balance</span><span class="{{ $balance > 0 ? 'text-danger' : 'text-success' }}">₱{{ number_format(max($balance, 0), 2) }}</span></div>
                </div>
            </div>
        </section>

        <section class="card" aria-labelledby="payments-title">
            <header class="card-header">
                <h2 class="card-title" id="payments-title"><i class="fas fa-receipt" aria-hidden="true"></i> Payments</h2>
                @if($canPay)<button type="button" class="btn btn-sm btn-soft no-print" data-dialog-open="paymentDialog"><i class="fas fa-plus" aria-hidden="true"></i> Add payment</button>@endif
            </header>
            @forelse($order->payments->sortByDesc('created_at') as $payment)
                <div class="payment-row">
                    <span class="bubble-tile tone-green" aria-hidden="true"><i class="fas {{ $methods[$payment->payment_method][1] ?? 'fa-peso-sign' }}"></i></span>
                    <span class="list-main">
                        <span class="list-title">{{ $methods[$payment->payment_method][0] ?? ucfirst($payment->payment_method) }}</span>
                        <span class="list-sub">{{ $payment->created_at->format('M j, Y · g:i A') }}</span>
                    </span>
                    <strong class="money">₱{{ number_format($payment->amount, 2) }}</strong>
                </div>
            @empty
                <p class="empty">No payments yet.</p>
            @endforelse
        </section>

        <section class="card" aria-labelledby="notes-title">
            <header class="card-header"><h2 class="card-title" id="notes-title"><i class="fas fa-note-sticky" aria-hidden="true"></i> Notes</h2></header>
            <div class="card-body">
                <p class="print-only">{{ $order->notes ?: 'No notes.' }}</p>
                <form class="notes-form stack-sm no-print" id="notesForm">
                    <label class="sr-only" for="notes">Notes for this order</label>
                    <textarea class="textarea" id="notes" maxlength="1000" placeholder="Stains, special care, pickup instructions…">{{ $order->notes }}</textarea>
                    <div><button type="submit" class="btn btn-secondary"><i class="fas fa-floppy-disk" aria-hidden="true"></i> Save notes</button></div>
                </form>
            </div>
        </section>
    </div>

    <aside class="stack">
        <section class="card balance-card {{ $order->status === 'cancelled' ? '' : ($balance > 0 ? 'is-due' : 'is-paid') }}" aria-label="Balance">
            <div class="card-body">
                <span class="eyebrow">{{ $balance > 0 && $order->status !== 'cancelled' ? 'Balance due' : 'Balance' }}</span>
                <span class="balance-amount money">₱{{ number_format(max($balance, 0), 2) }}</span>
                <span class="muted">of ₱{{ number_format($order->total, 2) }} · ₱{{ number_format($order->paid_amount, 2) }} paid</span>
                @if($canPay)
                    <button type="button" class="btn btn-primary btn-lg btn-block no-print" data-dialog-open="paymentDialog"><i class="fas fa-peso-sign" aria-hidden="true"></i> Collect payment</button>
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

        <section class="card no-print" aria-labelledby="details-title">
            <header class="card-header"><h2 class="card-title" id="details-title"><i class="fas fa-circle-info" aria-hidden="true"></i> Details</h2></header>
            <div class="card-body stack-sm">
                <dl class="dl">
                    <dt>Taken by</dt><dd>{{ $order->staff->name ?? '—' }}</dd>
                    <dt>Created</dt><dd>{{ $order->created_at?->format('M j, Y g:i A') }}</dd>
                    <dt>Updated</dt><dd>{{ $order->updated_at?->format('M j, Y g:i A') }}</dd>
                </dl>
                <div class="field">
                    <label class="label" for="statusSelect">Change status</label>
                    <select class="select" id="statusSelect">
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

<dialog id="paymentDialog" class="dialog dialog-sm" aria-labelledby="paymentTitle">
    <form id="paymentForm" novalidate>
        <div class="dialog-header">
            <h2 class="dialog-title" id="paymentTitle">Add payment</h2>
            <button type="button" class="icon-btn" data-dialog-close aria-label="Close"><i class="fas fa-xmark" aria-hidden="true"></i></button>
        </div>
        <div class="dialog-body">
            <div class="pay-due"><span class="muted">Balance due</span><strong class="money">₱{{ number_format(max($balance, 0), 2) }}</strong></div>
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
                    <span class="affix" aria-hidden="true">₱</span>
                    <input class="input" type="number" id="amount" name="amount" inputmode="decimal" min="0.01" step="0.01" value="{{ number_format(max($balance, 0), 2, '.', '') }}" aria-describedby="payResult">
                </div>
                <div class="field-error" id="amountError"></div>
            </div>
            <div class="quick-cash" aria-label="Quick amounts">
                <button type="button" class="btn btn-secondary" data-amount="{{ max($balance, 0) }}">Exact</button>
                <button type="button" class="btn btn-secondary" data-amount="100">₱100</button>
                <button type="button" class="btn btn-secondary" data-amount="500">₱500</button>
                <button type="button" class="btn btn-secondary" data-amount="1000">₱1,000</button>
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
    const balance = {{ max($balance, 0) }};
    const labels = { pending: 'Pending', processing: 'Processing', ready: 'Ready for pickup', completed: 'Completed', cancelled: 'Cancelled' };

    // ---- Status ----
    async function setStatus(status, control) {
        if (status === 'cancelled') {
            const ok = await confirmAction({ title: 'Cancel this order?', message: 'It will no longer count in sales. You can change the status back later.', confirmText: 'Cancel order', cancelText: 'Keep order' });
            if (!ok) { document.getElementById('statusSelect').value = @json($order->status); return; }
        }
        control?.classList.add('is-loading');
        try {
            await sendJson(appUrl(`orders/${orderId}/update-status`), { status });
            flashToast(`Order marked ${labels[status].toLowerCase()}.`);
            location.reload();
        } catch (e) {
            control?.classList.remove('is-loading');
            showToast(e.message, 'error');
        }
    }
    document.querySelectorAll('[data-set-status]').forEach(b => b.addEventListener('click', () => setStatus(b.dataset.setStatus, b)));
    document.getElementById('statusSelect').addEventListener('change', e => setStatus(e.target.value));

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

    // ---- Payment ----
    const dialog = document.getElementById('paymentDialog');
    const amount = document.getElementById('amount');
    const result = document.getElementById('payResult');
    const payButton = document.getElementById('payButton');
    const describe = () => {
        const value = parseFloat(amount.value) || 0;
        if (value <= 0) { result.textContent = ''; payButton.textContent = 'Record payment'; return; }
        if (value > balance) { result.innerHTML = `Change to give back: <span class="text-success">${peso(value - balance)}</span>`; payButton.textContent = 'Record ' + peso(balance); }
        else if (value < balance) { result.innerHTML = `Partial payment · <span class="text-danger">${peso(balance - value)}</span> still due`; payButton.textContent = 'Record ' + peso(value); }
        else { result.textContent = 'Pays the order in full.'; payButton.textContent = 'Record ' + peso(value); }
    };
    amount.addEventListener('input', describe);
    document.querySelectorAll('[data-amount]').forEach(b => b.addEventListener('click', () => { amount.value = Number(b.dataset.amount).toFixed(2); describe(); }));
    dialog.addEventListener('close', () => { document.getElementById('amountError').textContent = ''; amount.removeAttribute('aria-invalid'); });
    describe();

    document.getElementById('paymentForm').addEventListener('submit', async e => {
        e.preventDefault();
        const value = parseFloat(amount.value) || 0;
        const error = document.getElementById('amountError');
        if (value <= 0) { error.textContent = 'Enter the amount received.'; amount.setAttribute('aria-invalid', 'true'); amount.focus(); return; }
        payButton.classList.add('is-loading');
        try {
            const data = await sendJson(appUrl(`orders/${orderId}/add-payment`), {
                amount: value,
                payment_method: dialog.querySelector('input[name="payment_method"]:checked').value,
            });
            flashToast(data.change > 0 ? `Payment recorded. Give back ${peso(data.change)} change.` : 'Payment recorded.');
            location.replace(appUrl(`orders/${orderId}/details`));
        } catch (err) {
            payButton.classList.remove('is-loading');
            error.textContent = err.message;
        }
    });

    @if($canPay && request()->boolean('pay'))
    openDialog(dialog);
    @endif
    @if(request()->boolean('print'))
    window.addEventListener('load', () => setTimeout(() => window.print(), 300));
    @endif
})();
</script>
@endpush
