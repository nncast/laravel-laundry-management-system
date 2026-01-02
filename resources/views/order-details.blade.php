@extends('layouts.app')

@section('title', 'Order Details - ' . $order->order_number)
@section('page-title', 'Order Details')

@section('content')
<style>
<style>
/* Reset & Base - Mobile First */
* { 
    margin: 0; 
    padding: 0; 
    box-sizing: border-box; 
}
body { 
    font-family: 'Poppins', sans-serif; 
    background: #f2f5f7; 
    color: #2c3e50; 
    font-size: 14px;
    line-height: 1.5;
}

/* Order Container */
.order-container {
    width: 100%;
    margin: 0 auto;
    padding: 10px;
}

/* Order Header - Mobile */
.order-header {
    background: white;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.order-number {
    font-size: 18px;
    font-weight: 700;
    color: #007bff;
    background: #eef5ff;
    padding: 10px 15px;
    border-radius: 8px;
    text-align: center;
    width: 100%;
}

.order-meta {
    display: flex;
    flex-direction: column;
    gap: 10px;
    width: 100%;
}

.meta-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px dashed #eee;
}

.meta-label {
    color: #6c757d;
    font-size: 12px;
    font-weight: 500;
    min-width: 100px;
}

.meta-value {
    color: #2c3e50;
    font-weight: 600;
    font-size: 12px;
    text-align: right;
}

/* Status Badge & Selector */
.status-selector {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    min-width: 80px;
    text-align: center;
    text-transform: uppercase;
    display: inline-block;
}

.status-badge.pending { background: #fff3cd; color: #856404; }
.status-badge.processing { background: #cce5ff; color: #004085; }
.status-badge.completed { background: #d4edda; color: #155724; }
.status-badge.cancelled { background: #f8d7da; color: #721c24; }

.status-dropdown {
    padding: 5px 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    background: white;
    font-size: 12px;
    color: #333;
    cursor: pointer;
    min-width: 100px;
    flex: 1;
}

/* Two Column Layout - Mobile: Stack */
.two-columns {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-bottom: 15px;
}

.left-main, .right-sidebar {
    width: 100%;
}

/* Main Content Box */
.content-box {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    overflow: hidden;
    margin-bottom: 15px;
}

.box-section {
    padding: 15px;
    border-bottom: 1px solid #eee;
}

.box-section:last-child { 
    border-bottom: none;
    margin-bottom: 0;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
    padding-bottom: 10px;
    border-bottom: 2px solid #eef5ff;
    flex-wrap: wrap;
    gap: 10px;
}

.section-title {
    font-size: 15px;
    font-weight: 600;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 8px;
}

.section-title i { 
    color: #007bff;
    font-size: 14px;
}

/* Order Items Table - Mobile Responsive */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin: 0 -15px;
    padding: 0 15px;
}

.order-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 500px; /* Minimum width for table on mobile */
}

.order-table th {
    background: #f8f9fa;
    padding: 10px 8px;
    text-align: left;
    font-weight: 600;
    color: #495057;
    font-size: 12px;
    border-bottom: 2px solid #eaeaea;
    white-space: nowrap;
}

.order-table td {
    padding: 10px 8px;
    border-bottom: 1px solid #eee;
    font-size: 12px;
    color: #666;
    vertical-align: top;
}

.order-table tr:hover { 
    background: #f9f9f9; 
}

.service-name { 
    font-weight: 500; 
    color: #2c3e50;
    font-size: 13px;
    margin-bottom: 2px;
}

.service-variant { 
    font-size: 11px; 
    color: #999;
    line-height: 1.3;
}

/* Customer & Payment Grid */
.info-grid {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 6px 0;
    border-bottom: 1px dashed #eee;
    flex-wrap: wrap;
}

.info-label { 
    color: #6c757d; 
    font-size: 12px;
    min-width: 80px;
}

.info-value { 
    color: #2c3e50; 
    font-weight: 500; 
    font-size: 12px;
    text-align: right;
    flex: 1;
}

.payment-details {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.payment-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.payment-row.total {
    border-top: 2px solid #333;
    border-bottom: none;
    margin-top: 10px;
    padding-top: 12px;
    font-size: 14px;
    font-weight: 700;
}

.payment-label { 
    color: #6c757d; 
    font-size: 13px;
}

.payment-value { 
    color: #2c3e50; 
    font-weight: 500; 
    font-size: 13px;
}

.payment-row.total .payment-value {
    font-weight: 700;
    color: #2c3e50;
}

.balance-positive { color: #28a745 !important; }
.balance-negative { color: #dc3545 !important; }

/* Notes Section */
.notes-display {
    padding: 12px;
    background: #f8f9fa;
    border-radius: 6px;
    color: #555;
    font-size: 12px;
    line-height: 1.5;
    min-height: 50px;
    margin-bottom: 10px;
}

.notes-textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 12px;
    resize: vertical;
    font-family: inherit;
    margin-bottom: 10px;
    min-height: 80px;
}

.notes-textarea:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.1);
}

/* Store Info Box */
.store-info-box {
    background: white;
    border-radius: 10px;
    padding: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    margin-bottom: 15px;
}

.store-header {
    text-align: center;
    margin-bottom: 15px;
    padding-bottom: 12px;
    border-bottom: 2px solid #eef5ff;
}

.store-name {
    font-size: 18px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 8px;
    word-break: break-word;
}

.store-details {
    display: flex;
    flex-direction: column;
    gap: 6px;
    font-size: 12px;
    color: #666;
}

.store-details span {
    display: flex;
    align-items: center;
    gap: 8px;
    word-break: break-word;
}

.store-details i {
    color: #007bff;
    width: 16px;
    text-align: center;
    flex-shrink: 0;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.btn-action {
    padding: 10px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none;
    transition: all 0.3s;
    border: none;
    width: 100%;
    text-align: center;
}

.btn-action i {
    font-size: 13px;
}

.btn-edit { background: #007bff; color: white; }
.btn-edit:hover { background: #0056b3; color: white; }

.btn-complete { background: #28a745; color: white; }
.btn-complete:hover { background: #218838; color: white; }

.btn-cancel { background: #dc3545; color: white; }
.btn-cancel:hover { background: #c82333; color: white; }

.btn-save {
    background: #007bff;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 8px 15px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.3s;
    width: 100%;
    margin-top: 5px;
}

.btn-save:hover { background: #0056b3; }

/* Footer */
.order-footer {
    text-align: center;
    padding: 15px;
    margin-top: 15px;
    color: #666;
    font-size: 12px;
    border-top: 1px solid #eee;
}

.order-footer strong {
    color: #007bff;
    font-weight: 600;
}

/* Tablet Styles (min-width: 600px) */
@media (min-width: 600px) {
    .order-container {
        padding: 15px;
        max-width: 100%;
    }
    
    .order-header {
        flex-direction: row;
        align-items: center;
        padding: 20px;
    }
    
    .order-number {
        width: auto;
        min-width: 200px;
        font-size: 20px;
    }
    
    .order-meta {
        flex: 1;
    }
    
    .meta-label {
        font-size: 13px;
    }
    
    .meta-value {
        font-size: 13px;
    }
    
    .status-badge {
        font-size: 12px;
        min-width: 100px;
    }
    
    .status-dropdown {
        min-width: 120px;
    }
    
    .section-title {
        font-size: 16px;
    }
    
    .order-table th,
    .order-table td {
        padding: 12px 10px;
        font-size: 13px;
    }
    
    .service-name {
        font-size: 14px;
    }
    
    .info-label,
    .info-value {
        font-size: 13px;
    }
    
    .payment-label,
    .payment-value {
        font-size: 14px;
    }
    
    .notes-textarea {
        font-size: 13px;
    }
    
    .store-name {
        font-size: 20px;
    }
    
    .store-details {
        font-size: 13px;
    }
    
    .btn-action {
        font-size: 13px;
        padding: 12px 15px;
    }
    
    .order-footer {
        font-size: 13px;
    }
}

/* Small Desktop (min-width: 768px) */
@media (min-width: 768px) {
    .order-container {
        max-width: 90%;
    }
    
    .two-columns {
        flex-direction: row;
        gap: 20px;
    }
    
    .left-main {
        flex: 1;
    }
    
    .right-sidebar {
        width: 350px;
        flex-shrink: 0;
    }
    
    .content-box {
        margin-bottom: 0;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 12px;
    }
    
    .info-row {
        flex-direction: column;
        border-bottom: none;
        padding: 0;
        gap: 4px;
    }
    
    .info-label,
    .info-value {
        width: 100%;
        text-align: left;
    }
    
    .btn-save {
        width: auto;
        align-self: flex-start;
    }
}

/* Large Desktop (min-width: 992px) */
@media (min-width: 992px) {
    .order-container {
        max-width: 1200px;
    }
    
    .order-header {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 20px;
    }
    
    .order-number {
        font-size: 24px;
    }
    
    .order-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }
    
    .meta-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
        border-bottom: none;
        padding: 0;
    }
    
    .meta-label {
        font-size: 13px;
    }
    
    .meta-value {
        font-size: 13px;
        text-align: left;
    }
    
    .status-selector {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .status-dropdown {
        width: 100%;
    }
    
    .order-table {
        min-width: 100%;
    }
}

/* Extra Large Screens (min-width: 1200px) */
@media (min-width: 1200px) {
    .order-container {
        padding: 20px 15px;
    }
    
    .box-section {
        padding: 20px;
    }
    
    .section-title {
        font-size: 17px;
    }
    
    .store-name {
        font-size: 22px;
    }
}

/* Touch Device Optimizations */
@media (hover: none) and (pointer: coarse) {
    .btn-action,
    .btn-save,
    .status-dropdown {
        min-height: 44px; /* Minimum touch target size */
    }
    
    .notes-textarea {
        font-size: 16px; /* Prevent iOS zoom on focus */
    }
    
    select.status-dropdown {
        font-size: 16px; /* Prevent iOS zoom on focus */
    }
}

/* Print Styles */
@media print {
    body {
        background: white !important;
        font-size: 12pt !important;
    }
    
    .order-container {
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .btn-action,
    .btn-save,
    .status-dropdown,
    .notes-textarea,
    .order-footer {
        display: none !important;
    }
    
    .content-box,
    .store-info-box {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
        page-break-inside: avoid;
    }
}


/* Large Desktop (min-width: 992px) */
@media (min-width: 992px) {
    .order-container {
        max-width: 1200px;
    }
    
    .order-header {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 30px;
        padding: 20px;
    }
    
    .order-number {
        font-size: 24px;
        min-width: 220px;
        text-align: center;
        padding: 12px 20px;
        margin: 0;
    }
    
    .order-meta {
        flex: 1;
        display: flex;
        flex-direction: row;
        gap: 30px;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-start;
    }
    
    .meta-item {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 8px;
        border-bottom: none;
        padding: 0;
        min-height: auto;
    }
    
    .meta-label {
        font-size: 13px;
        min-width: auto;
        white-space: nowrap;
        color: #6c757d;
    }
    
    .meta-value {
        font-size: 13px;
        text-align: left;
        font-weight: 600;
        color: #2c3e50;
        white-space: nowrap;
    }
    
    /* Status selector adjustments */
    .meta-item:nth-child(3) {
        min-width: 200px;
    }
    
    .status-selector {
        flex-direction: row;
        align-items: center;
        gap: 8px;
        width: auto;
    }
    
    .status-badge {
        min-width: 90px;
        font-size: 11px;
    }
    
    .status-dropdown {
        width: 120px;
        min-width: 120px;
        font-size: 12px;
        padding: 5px 8px;
    }
    
    .order-table {
        min-width: 100%;
    }
}

/* Extra Large Screens (min-width: 1200px) */
@media (min-width: 1200px) {
    .order-container {
        padding: 20px 15px;
    }
    
    .order-header {
        gap: 40px;
    }
    
    .order-meta {
        gap: 40px;
        justify-content: space-between;
    }
    
    .meta-item {
        gap: 10px;
    }
    
    .meta-label,
    .meta-value {
        font-size: 14px;
    }
    
    .box-section {
        padding: 20px;
    }
    
    .section-title {
        font-size: 17px;
    }
    
    .store-name {
        font-size: 22px;
    }
}
</style>

<div class="order-container">
    <!-- Order Header -->
    <div class="order-header">
        <div class="order-number">
            {{ $order->order_number }}
        </div>
        <div class="order-meta">
            <div class="meta-item">
                <span class="meta-label">Order Date:</span>
                <span class="meta-value">{{ \Carbon\Carbon::parse($order->order_date)->format('d/m/Y') }}</span>
            </div>
            @if($order->delivery_date)
            <div class="meta-item">
                <span class="meta-label">Delivery Date:</span>
                <span class="meta-value">{{ \Carbon\Carbon::parse($order->delivery_date)->format('d/m/Y') }}</span>
            </div>
            @endif
            <div class="meta-item">
                <span class="meta-label">Status:</span>
                <div class="status-selector">
                    <span class="status-badge {{ $order->status }}">{{ ucfirst($order->status) }}</span>
                    <select class="status-dropdown" data-order-id="{{ $order->id }}">
                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="two-columns">
        <!-- Left Main Content -->
        <div class="left-main">
            <!-- Single Content Box -->
            <div class="content-box">
                <!-- Order Items Section -->
                <div class="box-section">
                    <div class="section-header">
                        <h3 class="section-title"><i class="fas fa-list-alt"></i> Order Items</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="order-table">
                            <thead>
                                <tr>
                                    <th>Service Name</th>
                                    <th>Price</th>
                                    <th>QTY</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($order->items as $item)
                                <tr>
                                    <td>
                                        <div>
                                            <div class="service-name">{{ $item->service->name ?? 'N/A' }}</div>
                                            @if($item->variant)
                                            <div class="service-variant">[{{ $item->variant }}]</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ number_format($item->price, 2) }} USD</td>
                                    <td>{{ $item->qty }}</td>
                                    <td>{{ number_format($item->total, 2) }} USD</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 20px; color: #999;">
                                        No items in this order
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Customer Information Section -->
                <div class="box-section">
                    <div class="section-header">
                        <h3 class="section-title"><i class="fas fa-user"></i> Invoice To</h3>
                    </div>
                    <div class="info-grid">
                        @if($order->customer)
                        <div class="info-row">
                            <span class="info-label">Name:</span>
                            <span class="info-value">{{ $order->customer->name }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Phone:</span>
                            <span class="info-value">{{ $order->customer->contact ?? '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Email:</span>
                            <span class="info-value">{{ $order->customer->email ?? '-' }}</span>
                        </div>
                        @else
                        <div class="info-row">
                            <span class="info-value" style="color: #666; font-style: italic;">
                                Walk In Customer
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Payment Details Section -->
                <div class="box-section">
                    <div class="section-header">
                        <h3 class="section-title"><i class="fas fa-credit-card"></i> Payment Details</h3>
                    </div>
                    <div class="payment-details">
                        <div class="payment-row">
                            <span class="payment-label">Sub Total:</span>
                            <span class="payment-value">{{ number_format($order->subtotal, 2) }} USD</span>
                        </div>
                        @if($order->addons->count() > 0)
                        <div class="payment-row">
                            <span class="payment-label">Addons:</span>
                            <span class="payment-value">{{ number_format($order->addons->sum('price'), 2) }} USD</span>
                        </div>
                        @endif
                        <div class="payment-row">
                            <span class="payment-label">Discount:</span>
                            <span class="payment-value">{{ number_format($order->discount, 2) }} USD</span>
                        </div>
                        @if($order->tax > 0)
                        <div class="payment-row">
                            <span class="payment-label">Tax:</span>
                            <span class="payment-value">{{ number_format($order->tax, 2) }} USD</span>
                        </div>
                        @endif
                        <div class="payment-row total">
                            <span class="payment-label">Gross Total:</span>
                            <strong class="payment-value">{{ number_format($order->total, 2) }} USD</strong>
                        </div>
                        <div class="payment-row">
                            <span class="payment-label">Paid Amount:</span>
                            <span class="payment-value">{{ number_format($order->paid_amount, 2) }} USD</span>
                        </div>
                        <div class="payment-row total">
                            <span class="payment-label">Balance:</span>
                            <strong class="payment-value {{ ($order->total - $order->paid_amount) > 0 ? 'balance-negative' : 'balance-positive' }}">
                                {{ number_format($order->total - $order->paid_amount, 2) }} USD
                            </strong>
                        </div>
                    </div>
                </div>

                <!-- Notes Section -->
                <div class="box-section">
                    <div class="section-header">
                        <h3 class="section-title"><i class="fas fa-sticky-note"></i> Notes</h3>
                    </div>
                    <div class="notes-display">
                        {{ $order->notes ?? 'No notes available' }}
                    </div>
                    <textarea class="notes-textarea" placeholder="Add new notes..." rows="3" data-order-id="{{ $order->id }}"></textarea>
                    <button class="btn-save" data-order-id="{{ $order->id }}">Edit Notes</button>
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="right-sidebar">
            <!-- Store Info Box -->
            <div class="store-info-box">
                <div class="store-header">
                    <h2 class="store-name">{{ $settings->business_name ?? 'LAUNDRY' }}</h2>
                </div>
                <div class="store-details">
                    @if($settings->contact)
                    <span><i class="fas fa-phone"></i> {{ $settings->contact }}</span>
                    @endif
                    @if($settings->address)
                    <span><i class="fas fa-map-marker-alt"></i> {{ $settings->address }}</span>
                    @endif
                </div>
            </div>

            <!-- Action Buttons Box -->
            <div class="store-info-box">
                <div class="store-header">
                    <h2 class="store-name"><i class="fas fa-cogs"></i> Actions</h2>
                </div>
                <div class="action-buttons">

                    <a href="{{ route('pos.edit', $order) }}" class="btn-action btn-edit">
                        <i class="fas fa-edit"></i> Edit Order
                    </a>
                    <button class="btn-action btn-complete btn-mark-complete" data-order-id="{{ $order->id }}">
                        <i class="fas fa-check"></i> Mark Complete
                    </button>
                    <button class="btn-action btn-cancel btn-cancel-order" data-order-id="{{ $order->id }}">
                        <i class="fas fa-times"></i> Cancel Order
                    </button>
                </div>
            </div>
        </div>
    </div>


</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status dropdown change handler
    const statusDropdown = document.querySelector('.status-dropdown');
    const statusBadge = document.querySelector('.status-badge');
    
    if(statusDropdown) {
        statusDropdown.addEventListener('change', function() {
            const status = this.value;
            const orderId = this.dataset.orderId;
            
            // Store the original value in case we need to revert
            const originalStatus = this.value;
            
            // Update badge immediately
            statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
            statusBadge.className = 'status-badge ' + status;
            
            // Show loading state
            statusDropdown.disabled = true;
            
            // Send AJAX request to update status - USE POST ONLY
            fetch(`/orders/${orderId}/update-status`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ 
                    status: status
                    // REMOVED _method: 'PUT' - route only accepts POST
                })
            })
            .then(async response => {
                const text = await response.text();
                console.log('Raw response:', text);
                
                try {
                    const data = JSON.parse(text);
                    return { response: response, data: data };
                } catch (e) {
                    console.error('JSON parse error:', e);
                    throw new Error('Server returned invalid JSON');
                }
            })
            .then(({ response, data }) => {
                statusDropdown.disabled = false;
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${data.message || 'Server error'}`);
                }
                
                if (data.success) {
                    // Success - update UI with server response
                    if (data.order && data.order.status) {
                        const newStatus = data.order.status;
                        statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                        statusBadge.className = 'status-badge ' + newStatus;
                        statusDropdown.value = newStatus;
                    }
                    
                    // Show success message
                    showNotification('Status updated successfully!', 'success');
                    
                } else {
                    // Server returned error
                    showNotification(data.message || 'Failed to update status', 'error');
                    
                    // Revert to original status
                    revertStatus(originalStatus);
                }
            })
            .catch(error => {
                console.error('Fetch error details:', error);
                statusDropdown.disabled = false;
                
                // Show error message
                showNotification('Error: ' + error.message, 'error');
                
                // Revert to original status
                revertStatus(originalStatus);
                
                // Check if update actually succeeded by reloading after 2 seconds
                setTimeout(() => {
                    if (confirm('There was an error. Reload the page to see current status?')) {
                        location.reload();
                    }
                }, 2000);
            });
            
            // Helper function to revert status
            function revertStatus(originalStatus) {
                if (statusDropdown && statusBadge) {
                    statusDropdown.value = originalStatus;
                    statusBadge.textContent = originalStatus.charAt(0).toUpperCase() + originalStatus.slice(1);
                    statusBadge.className = 'status-badge ' + originalStatus;
                }
            }
        });
    }
    
    // Save notes button
    const saveNotesBtn = document.querySelector('.btn-save');
    if(saveNotesBtn) {
        saveNotesBtn.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            const notes = document.querySelector('.notes-textarea').value;
            
            if (!notes.trim()) {
                alert('Please enter notes to save.');
                return;
            }
            
            // Disable button and show loading
            const originalText = this.textContent;
            this.textContent = 'Saving...';
            this.disabled = true;
            
            // Send AJAX request to save notes
            fetch(`/orders/${orderId}/add-notes`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ 
                    notes: notes 
                })
            })
            .then(response => response.json())
            .then(data => {
                this.textContent = originalText;
                this.disabled = false;
                
                if (data.success) {
                    alert('Notes saved successfully!');
                    location.reload(); // Reload to show updated notes
                } else {
                    alert('Failed to save notes: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.textContent = originalText;
                this.disabled = false;
                alert('Network error. Please try again.');
            });
        });
    }
    
    // Mark as complete button
    const markCompleteBtn = document.querySelector('.btn-mark-complete');
    if(markCompleteBtn) {
        markCompleteBtn.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            
            if (confirm('Are you sure you want to mark this order as complete?')) {
                // Update the status dropdown and badge first
                if (statusDropdown) {
                    statusDropdown.value = 'completed';
                    statusBadge.textContent = 'Completed';
                    statusBadge.className = 'status-badge completed';
                }
                
                // Disable button and show loading
                const originalText = this.textContent;
                this.textContent = 'Processing...';
                this.disabled = true;
                
                // Send AJAX request to update status to completed
                fetch(`/orders/${orderId}/update-status`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ 
                        status: 'completed'
                        // REMOVED _method: 'PUT'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    this.textContent = originalText;
                    this.disabled = false;
                    
                    if (data.success) {
                        alert('Order marked as complete!');
                        location.reload();
                    } else {
                        alert('Failed to update order: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.textContent = originalText;
                    this.disabled = false;
                    alert('Network error. Please try again.');
                });
            }
        });
    }
    
    // Cancel order button
    const cancelOrderBtn = document.querySelector('.btn-cancel-order');
    if(cancelOrderBtn) {
        cancelOrderBtn.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            
            if (confirm('Are you sure you want to cancel this order?')) {
                // Update the status dropdown and badge first
                if (statusDropdown) {
                    statusDropdown.value = 'cancelled';
                    statusBadge.textContent = 'Cancelled';
                    statusBadge.className = 'status-badge cancelled';
                }
                
                // Disable button and show loading
                const originalText = this.textContent;
                this.textContent = 'Cancelling...';
                this.disabled = true;
                
                // Send AJAX request to change status to cancelled
                fetch(`/orders/${orderId}/update-status`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ 
                        status: 'cancelled'
                        // REMOVED _method: 'PUT'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    this.textContent = originalText;
                    this.disabled = false;
                    
                    if (data.success) {
                        alert('Order cancelled successfully!');
                        location.reload(); // Reload to show updated status
                    } else {
                        alert('Failed to cancel order: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.textContent = originalText;
                    this.disabled = false;
                    alert('Network error. Please try again.');
                });
            }
        });
    }
    
    // Helper function to show notifications
    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                ${message}
            </div>
            <button class="notification-close">&times;</button>
        `;
        
        // Add styles
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 8px;
            color: white;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-width: 300px;
            max-width: 400px;
            z-index: 9999;
            animation: slideIn 0.3s ease-out;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;
        
        // Set background color based on type
        if (type === 'success') {
            notification.style.backgroundColor = '#28a745';
        } else if (type === 'error') {
            notification.style.backgroundColor = '#dc3545';
        } else if (type === 'warning') {
            notification.style.backgroundColor = '#ffc107';
            notification.style.color = '#212529';
        } else {
            notification.style.backgroundColor = '#007bff';
        }
        
        // Add close button styles
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.style.cssText = `
            background: none;
            border: none;
            color: inherit;
            font-size: 20px;
            cursor: pointer;
            margin-left: 10px;
            padding: 0;
            line-height: 1;
        `;
        
        // Add close functionality
        closeBtn.addEventListener('click', () => {
            notification.style.animation = 'slideOut 0.3s ease-in forwards';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        });
        
        // Add CSS animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
        
        // Add to page
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.animation = 'slideOut 0.3s ease-in forwards';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }
        }, 5000);
    }
});
</script>
@endsection