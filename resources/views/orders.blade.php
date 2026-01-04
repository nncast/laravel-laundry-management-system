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
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    width: 250px;
    transition: all 0.3s;
}

.search-box input:focus {
    outline: none;
    border-color: var(--blue);
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
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
    background: #0056b3;
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
    background: #f8f9fa;
    padding: 15px 20px;
    font-weight: 600;
    color: var(--text-dark);
    border-bottom: 1px solid #eaeaea;
    gap: 10px;
    text-align: center;
    font-size: 14px;
}

.table-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr 1.5fr 1fr 1.2fr;
    padding: 15px 20px;
    border-bottom: 1px solid #f0f0f0;
    align-items: center;
    gap: 10px;
    text-align: center;
    font-size: 14px;
}

.table-row:last-child {
    border-bottom: none;
}

.table-row:hover {
    background: #fafafa;
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
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.status-processing {
    background: #cce5ff;
    color: #004085;
    border: 1px solid #b8daff;
}

.status-completed {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
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
    color: #28a745;
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
    background: #e7f4e4;
    color: #2e7d32;
    border: 1px solid #c8e6c9;
}

.payment-partial {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.payment-unpaid {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
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
    color: #0056b3;
    background: #f0f8ff;
}

.btn-edit {
    color: #28a745;
}

.btn-edit:hover {
    color: #218838;
    background: #e7f4e4;
}

.btn-delete {
    color: #dc3545;
}

.btn-delete:hover {
    color: #c82333;
    background: #f8d7da;
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
    border: 1px solid #ddd;
    background: white;
}

.pagination a:hover {
    background: #f8f9fa;
    border-color: var(--blue);
}

.pagination .active span {
    background: var(--blue);
    color: white;
    border: 1px solid var(--blue);
}

.pagination .disabled span {
    color: #6c757d;
    border: 1px solid #ddd;
    background: #f8f9fa;
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 15px;
    color: #dee2e6;
}

.empty-state h3 {
    font-size: 18px;
    margin-bottom: 10px;
    color: #495057;
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
        border: 1px solid #eaeaea;
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
                    if ($order->paid_amount >= $order->total) {
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
                <a href="{{ route('pos.edit', $order) }}" class="action-btn btn-edit" title="Edit">
                    <i class="fas fa-edit"></i>
                </a>
                <form action="{{ route('orders.destroy', $order) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this order?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="action-btn btn-delete" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
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
    {{ $orders->withQueryString()->links() }}
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