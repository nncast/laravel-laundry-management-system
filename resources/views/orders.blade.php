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
}

.add-order-btn:hover {
    background: #0056b3;
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
}

.status-ready {
    background: #e7f4e4;
    color: #2e7d32;
    border: 1px solid #c8e6c9;
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
    background: #e7f4e4;
    color: #2e7d32;
    border: 1px solid #c8e6c9;
    width: fit-content;
    margin: 0 auto;
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
    color: var(--blue);
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

.action-btn:hover {
    color: #0056b3;
    background: #f0f8ff;
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
}
</style>

<div class="orders-header">
    <div class="search-add-container">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search orders...">
        </div>
        <button class="add-order-btn">
            <i class="fas fa-plus"></i> Add New Order
        </button>
    </div>
</div>

<div class="orders-table">
    <div class="table-header">
        <div>Order Info</div>
        <div>Customer</div>
        <div>Amount</div>
        <div>Status</div>
        <div>Payment</div>
        <div>Created By</div>
        <div>Action</div>
    </div>

    <!-- Example Static Order -->
    <div class="table-row">
        <div class="order-info">
            <div class="order-id">ORD-3738</div>
            <div class="order-dates">
                <div class="order-date-item">Order Date: 28/10/25</div>
                <div class="order-date-item">Delivery Date: 28/10/25</div>
            </div>
        </div>
        <div class="customer-info">Walk In Customer</div>
        <div class="order-amount">1,600.00 USD</div>
        <div class="status-container">
            <span class="status-badge status-ready">
                <i class="fas fa-check-circle"></i>
                Ready To Deliver
            </span>
        </div>
        <div class="payment-info">
            <div class="payment-total">Total: 1,600.00 USD</div>
            <div class="payment-paid">Paid: 1,600.00 USD</div>
            <div class="payment-status">Fully Paid</div>
        </div>
        <div class="created-by">Admin</div>
        <div class="action-buttons">
            <button class="action-btn" title="Edit"><i class="fas fa-edit"></i></button>
            <button class="action-btn" title="View"><i class="fas fa-eye"></i></button>
            <button class="action-btn" title="Print"><i class="fas fa-print"></i></button>
            <button class="action-btn" title="Delete"><i class="fas fa-trash"></i></button>
        </div>
    </div>

    <!-- No orders found placeholder -->
    <div class="table-row no-results" style="display:none;">
        <div style="grid-column: 1 / -1; text-align:center; color:#999;">No orders found.</div>
    </div>
</div>
@endsection
