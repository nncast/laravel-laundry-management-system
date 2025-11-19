@extends('layouts.app')

@section('title', 'Customers')
@section('page-title', 'Customers')
@section('active-customers', 'active')

@section('content')
<style>
/* --- Customers POS Page Specific Styles --- */
.pos-container {
    display: flex;
    gap: 25px;
    padding: 20px;
}

/* Product Section */
.products-section .product-grid {
    max-height: 65vh;
    overflow-y: auto;
    scrollbar-width: thin;
}

.product-item {
    transition: 0.3s ease;
}
.product-item:hover {
    transform: scale(1.05);
    border-color: var(--blue);
}

/* Order Section */
.order-section button {
    transition: 0.3s ease;
}
.order-section button:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}

.order-section div:last-child button {
    flex: 1;
    margin: 0 6px;
    min-width: 110px;
}

.order-section table th,
.order-section table td {
    border-bottom: 1px solid #eee;
    font-size: 14px;
}

.order-section div[style*="margin-top: 20px;"] strong {
    color: var(--blue);
}

/* Payment Buttons */
.payment-buttons {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    margin-top: 25px;
}

.payment-buttons button {
    flex: 1;
    padding: 14px 0;
    border: none;
    border-radius: 8px;
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 14px;
    color: #fff;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-payment { background: #6f42c1; }
.btn-cash { background: #0056b3; }
.btn-save { background: #28a745; }
.btn-cancel { background: #dc3545; }

.payment-buttons button:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 1100px) {
    .pos-container { flex-direction: column; }
    .order-section { order: 2; }
}

@media (max-width: 768px) {
    .payment-buttons { flex-direction: column; }
    .payment-buttons button { width: 100%; }
}
</style>

<div class="pos-container">

    <!-- Products Section -->
    <div class="products-section" style="flex: 1; background: #fff; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); padding: 20px;">
        <div style="display: flex; align-items: center; margin-bottom: 15px;">
            <input type="text" placeholder="Search Here" style="flex: 1; padding: 10px 15px; border: 1px solid #ccc; border-radius: 6px; font-family: 'Poppins', sans-serif;">
        </div>

        <div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 15px;">
            <div class="product-item" style="text-align: center; border: 1px solid #eee; border-radius: 10px; padding: 15px;">
                <img src="https://via.placeholder.com/80" alt="Dry cl" style="width: 80px; height: 80px; border-radius: 6px; object-fit: cover;">
                <p style="margin-top: 10px; font-size: 14px;">Dry Clean</p>
            </div>
            <div class="product-item" style="text-align: center; border: 1px solid #eee; border-radius: 10px; padding: 15px;">
                <i class="fas fa-basket-shopping" style="font-size: 40px; color: #333;"></i>
                <p style="margin-top: 10px; font-size: 14px;">Valet</p>
            </div>
            <div class="product-item" style="text-align: center; border: 1px solid #eee; border-radius: 10px; padding: 15px;">
                <i class="fas fa-tshirt" style="font-size: 40px; color: #333;"></i>
                <p style="margin-top: 10px; font-size: 14px;">Shirt</p>
            </div>
            <div class="product-item" style="text-align: center; border: 1px solid #eee; border-radius: 10px; padding: 15px;">
                <i class="fas fa-user-tie" style="font-size: 40px; color: #333;"></i>
                <p style="margin-top: 10px; font-size: 14px;">Coat</p>
            </div>
        </div>
    </div>

    <!-- Order Section -->
    <div class="order-section" style="flex: 1.2; background: #fff; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); padding: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
            <div>
                <p style="font-size: 13px;">Order: <strong>#ORD-3739</strong></p>
                <p style="font-size: 13px;">Date: <strong>2025-10-28</strong></p>
                <p style="font-size: 13px;">Delivery Date: <strong>2025-10-28</strong></p>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <input type="text" placeholder="Select A Customer" style="padding: 8px 12px; border: 1px solid #ccc; border-radius: 6px; width: 180px;">
                <button style="background: var(--blue); border: none; color: white; padding: 8px 10px; border-radius: 6px; cursor: pointer;">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
            <thead>
                <tr style="background: #f8f9fa;">
                    <th style="padding: 10px; text-align: left;">Service</th>
                    <th style="padding: 10px;">Color</th>
                    <th style="padding: 10px;">Price</th>
                    <th style="padding: 10px;">Rate</th>
                    <th style="padding: 10px;">QTY</th>
                    <th style="padding: 10px;">Tax (16%)</th>
                    <th style="padding: 10px;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 30px; color: #999;">No items added yet.</td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top: 20px; font-size: 14px;">
            <div style="display: flex; justify-content: space-between;"><span>Add-on:</span><strong>0.00 USD</strong></div>
            <div style="display: flex; justify-content: space-between;"><span>Sub Total:</span><strong>0.00 USD</strong></div>
            <div style="display: flex; justify-content: space-between;"><span>Tax (16%):</span><strong>0.00 USD</strong></div>
            <div style="display: flex; justify-content: space-between;"><span>Discount:</span><strong>0.00 USD</strong></div>
            <div style="display: flex; justify-content: space-between; margin-top: 5px;"><span><strong>Gross Total:</strong></span><strong>0.00 USD</strong></div>
        </div>

        <div style="margin-top: 15px;">
            <label for="notes" style="font-size: 14px;">Notes:</label>
            <textarea id="notes" placeholder="Enter notes here..." style="width: 100%; height: 60px; border-radius: 6px; border: 1px solid #ccc; padding: 8px; font-family: 'Poppins', sans-serif; font-size: 13px;"></textarea>
        </div>

        <div class="payment-buttons">
            <button class="btn-payment">Payment</button>
            <button class="btn-cash">Cash</button>
            <button class="btn-save">Save & Print</button>
            <button class="btn-cancel">Cancel</button>
        </div>
    </div>
</div>
@endsection
