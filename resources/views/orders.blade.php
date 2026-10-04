@extends('layouts.app')

@section('title', 'Orders')
@section('page-title', 'Orders')
@section('active-orders', 'active')

@section('content')
<style>
/* --- Orders Page Specific Styling --- */
.orders-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
}

.orders-header h1 {
    font-size: 24px;
    font-weight: 600;
    color: var(--text-dark);
    margin: 0;
}

.search-add-container {
    display: flex;
    gap: 15px;
    align-items: center;
    flex-wrap: wrap;
}

.search-box {
    position: relative;
    display: flex;
    align-items: center;
}

.search-box form {
    display: flex;
    align-items: center;
}

.search-box input {
    padding: 10px 15px 10px 40px;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 14px;
    width: 250px;
    transition: all 0.3s;
}

.search-box input:focus {
    outline: none;
    border-color: var(--blue);
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.25);
}

.search-box i {
    position: absolute;
    left: 15px;
    color: var(--text-light);
}

.add-order-btn {
    background: var(--blue);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 10px 20px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: background 0.3s;
    text-decoration: none;
}

.add-order-btn:hover {
    background: var(--accent-hover);
    color: white;
}

.orders-table {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.table-header {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr 1.5fr 1fr 1.2fr;
    background: var(--surface-muted);
    padding: 15px 20px;
    font-weight: 600;
    color: var(--text-dark);
    border-bottom: 1px solid var(--border);
    gap: 10px;
    text-align: center;
    font-size: 14px;
}

.table-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr 1.5fr 1fr 1.2fr;
    padding: 15px 20px;
    border-bottom: 1px solid var(--surface-sunken);
    align-items: center;
    gap: 10px;
    text-align: center;
    font-size: 14px;
}

.table-row:last-child {
    border-bottom: none;
}

.table-row:hover {
    background: var(--surface-muted);
}

.order-info {
    display: flex;
    flex-direction: column;
    gap: 5px;
    text-align: left;
}

.order-id {
    font-weight: 600;
    color: var(--text-dark);
}

.order-dates {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.order-date-item {
    color: var(--text-light);
    font-size: 13px;
}

.customer-info {
    font-weight: 500;
    color: var(--text-dark);
}

.order-amount {
    font-weight: 600;
    color: var(--text-dark);
    font-size: 15px;
}

.status-container {
    display: flex;
    justify-content: center;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 500;
    text-align: center;
    width: fit-content;
    display: flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
    min-width: 100px;
    justify-content: center;
}

.status-pending {
    background: var(--warning-soft);
    color: var(--warning-text);
    border: 1px solid var(--warning-soft);
}

.status-processing {
    background: var(--accent-soft);
    color: var(--accent-hover);
    border: 1px solid var(--accent-soft);
}

.status-completed {
    background: var(--success-soft);
    color: var(--success-text);
    border: 1px solid var(--success-soft);
}

.status-cancelled {
    background: var(--danger-soft);
    color: var(--danger-text);
    border: 1px solid var(--danger-soft);
}

.status-badge i {
    font-size: 10px;
}

.payment-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.payment-total {
    font-weight: 500;
    color: var(--text-dark);
}

.payment-paid {
    color: var(--success);
    font-weight: 500;
}

.payment-status {
    padding: 4px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 500;
    width: fit-content;
    margin: 0 auto;
}

.payment-fully-paid {
    background: var(--success-soft);
    color: var(--success-text);
    border: 1px solid var(--success-soft);
}

.payment-partial {
    background: var(--warning-soft);
    color: var(--warning-text);
    border: 1px solid var(--warning-soft);
}

.payment-unpaid {
    background: var(--danger-soft);
    color: var(--danger-text);
    border: 1px solid var(--danger-soft);
}

.created-by {
    font-weight: 500;
    color: var(--text-dark);
}

.action-buttons {
    display: flex;
    gap: 6px;
    justify-content: center;
}

.action-btn {
    background: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

.btn-view {
    color: var(--blue);
}

.btn-view:hover {
    color: var(--accent-hover);
    background: var(--accent-soft);
}

.btn-edit {
    color: var(--success);
}

.btn-edit:hover {
    color: var(--success-hover);
    background: var(--success-soft);
}

.btn-delete {
    color: var(--danger);
}

.btn-delete:hover {
    color: var(--danger-hover);
    background: var(--danger-soft);
}

/* Pagination */
.pagination-container {
    display: flex;
    justify-content: center;
    margin-top: 20px;
    padding: 20px 0;
}

.pagination {
    display: flex;
    list-style: none;
    gap: 5px;
    padding: 0;
    margin: 0;
}

.pagination li {
    display: inline;
}

.pagination a,
.pagination span {
    padding: 8px 12px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
}

.pagination a {
    color: var(--blue);
    border: 1px solid var(--border);
    background: white;
}

.pagination a:hover {
    background: var(--surface-muted);
    border-color: var(--blue);
}

.pagination .active span {
    background: var(--blue);
    color: white;
    border: 1px solid var(--blue);
}

.pagination .disabled span {
    color: var(--text-muted);
    border: 1px solid var(--border);
    background: var(--surface-muted);
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: var(--text-muted);
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 15px;
    color: var(--border);
}

.empty-state h3 {
    font-size: 18px;
    margin-bottom: 10px;
    color: var(--text-secondary);
}

.empty-state p {
    font-size: 14px;
    margin-bottom: 20px;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .table-header { display: none; }
    .table-row {
        grid-template-columns: 1fr;
        border: 1px solid var(--border);
        border-radius: 8px;
        margin-bottom: 10px;
        gap: 10px;
        padding: 10px;
        text-align: left;
    }
    .order-info, .customer-info, .order-amount, .status-container, .payment-info, .created-by, .action-buttons {
        text-align: left;
    }
    .status-container { justify-content: flex-start; }
    .action-buttons { justify-content: flex-start; }
    .search-box input { width: 200px; }

    .status-badge {
        min-width: auto;
        justify-content: flex-start;
    }
}

/* ---- Refreshed list layout ---- */
.orders-table { border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow); }
.order-id { font-weight: 600; letter-spacing: 0.01em; }
.order-date-item { font-size: 12.5px; color: var(--text-muted); }
.order-amount { font-weight: 600; font-variant-numeric: tabular-nums; }
.payment-total { display: none; } /* same figure as the Amount column */
.payment-info { flex-direction: row; flex-wrap: wrap; align-items: center; gap: 6px 8px; }
.payment-paid { color: var(--text-muted); font-weight: 500; font-size: 13px; }
.payment-status { margin: 0; padding: 2px 8px; border-radius: 999px; font-size: 11.5px; font-weight: 600; border: 0; }
.created-by { font-weight: 500; color: var(--text-secondary); }
@media (min-width: 769px) {
    .table-header, .table-row { text-align: left; padding: 12px 20px; }
    .table-header { font-size: 12px; color: var(--text-muted); }
    .table-row .status-container, .table-row .action-buttons { justify-content: flex-start; }
    .table-header > :last-child, .table-row > :last-child { justify-self: end; }
}
</style>

<div class="orders-header">
    <div class="search-add-container">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <form method="GET" action="{{ route('orders.index') }}">
                <input type="text" name="search" placeholder="Search orders..." value="{{ request('search') }}">
            </form>
        </div>
        <a href="{{ route('pos.index') }}" class="add-order-btn">
            <i class="fas fa-plus"></i> Add New Order
        </a>
    </div>
</div>

<div class="orders-table">
    @if($orders->count() > 0)
        <div class="table-header">
            <div>Order Info</div>
            <div>Customer</div>
            <div>Amount</div>
            <div>Status</div>
            <div>Payment</div>
            <div>Created By</div>
            <div>Action</div>
        </div>

        @foreach($orders as $order)
        <div class="table-row">
            <div class="order-info">
                <div class="order-id">{{ $order->order_number }}</div>
                <div class="order-dates">
                    <div class="order-date-item">Order Date: {{ \Carbon\Carbon::parse($order->order_date)->format('d/m/Y') }}</div>
                </div>
            </div>
            <div class="customer-info">
                {{ $order->customer ? $order->customer->name : 'Walk In Customer' }}
            </div>
            <div class="order-amount">{{ number_format($order->total, 2) }} PHP</div>
            <div class="status-container">
                @php
                    $statusClasses = [
                        'pending' => 'status-pending',
                        'processing' => 'status-processing',
                        'completed' => 'status-completed',
                        'cancelled' => 'status-cancelled'
                    ];
                    $statusIcons = [
                        'pending' => 'fa-clock',
                        'processing' => 'fa-cog',
                        'completed' => 'fa-check-circle',
                        'cancelled' => 'fa-times-circle'
                    ];
                @endphp
                <span class="status-badge {{ $statusClasses[$order->status] ?? 'status-pending' }}">
                    <i class="fas {{ $statusIcons[$order->status] ?? 'fa-clock' }}"></i>
                    {{ ucfirst($order->status) }}
                </span>
            </div>
            <div class="payment-info">
                <div class="payment-total">Total: {{ number_format($order->total, 2) }} PHP</div>
                <div class="payment-paid">Paid: {{ number_format($order->paid_amount, 2) }} PHP</div>
                @php
                    $paymentStatus = 'unpaid';
                    $paymentClass = 'payment-unpaid';
                    if ($order->total > 0 && $order->paid_amount >= $order->total) {
                        $paymentStatus = 'fully paid';
                        $paymentClass = 'payment-fully-paid';
                    } elseif ($order->paid_amount > 0) {
                        $paymentStatus = 'partial';
                        $paymentClass = 'payment-partial';
                    }
                @endphp
                <div class="payment-status {{ $paymentClass }}">{{ ucfirst($paymentStatus) }}</div>
            </div>
            <div class="created-by">{{ $order->staff->name ?? 'N/A' }}</div>
            <div class="action-buttons">
                <a href="{{ route('orders.details', $order) }}" class="action-btn btn-view" title="View">
                    <i class="fas fa-eye"></i>
                </a>
                @if($order->can_edit)
                <a href="{{ route('pos.edit', $order) }}" class="action-btn btn-edit" title="Edit">
                    <i class="fas fa-edit"></i>
                </a>
                @endif
                @if(in_array(session('staff.role'), ['manager', 'admin']))
                <form action="{{ route('orders.destroy', $order) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this order?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="action-btn btn-delete" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    @else
        <div class="empty-state">
            <i class="fas fa-clipboard-list"></i>
            <h3>No orders found</h3>
            <p>{{ request('search') ? 'No orders match your search criteria.' : 'Start by creating your first order.' }}</p>
            <a href="{{ route('pos.index') }}" class="add-order-btn">
                <i class="fas fa-plus"></i> Create Order
            </a>
        </div>
    @endif
</div>

@if($orders->count() > 0)
<div class="pagination-container">
    {{ $orders->links() }}
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto submit search on typing with debounce
    let searchTimer;
    const searchInput = document.querySelector('input[name="search"]');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                this.form.submit();
            }, 500);
        });

        // Clear search button
        if (searchInput.value) {
            const clearBtn = document.createElement('button');
            clearBtn.type = 'button';
            clearBtn.innerHTML = '&times;';
            clearBtn.style.cssText = `
                position: absolute;
                right: 10px;
                background: none;
                border: none;
                color: #999;
                font-size: 16px;
                cursor: pointer;
                padding: 0;
                width: 20px;
                height: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
            `;
            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                searchInput.form.submit();
            });
            searchInput.parentNode.appendChild(clearBtn);
        }
    }
});
</script>
@endsection