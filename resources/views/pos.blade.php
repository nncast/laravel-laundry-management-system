@php
use Illuminate\Support\Str;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'POS')</title>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
:root {
    --blue: #007bff;
    --text-dark: #2c3e50;
    --text-light: #6c757d;
    --hover-bg: #007bff;
    --hover-text: #fff;
    --transition: all 0.3s ease;
}

* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: 'Poppins', sans-serif; background:#f2f5f7; color:var(--text-dark); }

/* --- Topbar --- */
.topbar {
    position: fixed;
    top:0; left:0; width:100%;
    height: 60px;
    background:#fff;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding: 0 15px;
    border-bottom:1px solid #e0e0e0;
    z-index:1000;
}
.topbar h2 { font-size:18px; font-weight:600; }
.topbar button { background: var(--blue); border:none; color:#fff; padding:8px 12px; border-radius:6px; font-size:14px; cursor:pointer; }

/* --- POS Container --- */
.pos-container {
    display:flex;
    flex-wrap:wrap;
    gap:15px;
    padding: 70px 10px 10px;
}

/* --- Products Section --- */
.products-section {
    flex:1 1 100%;
    background:#fff;
    border-radius:10px;
    padding:15px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.products-section input {
    width:100%; padding:10px 15px; border:1px solid #ccc; border-radius:6px; margin-bottom:10px;
}

.product-grid {
    display:grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap:10px;
    max-height:50vh;
    overflow-y:auto;
}
.product-item {
    text-align:center;
    border:1px solid #eee;
    border-radius:8px;
    padding:10px;
    cursor:pointer;
    transition: transform 0.3s ease, border-color 0.3s ease;
}
.product-item:hover {
    transform:scale(1.05);
    border-color: var(--blue);
}
.product-item img {
    width:60px; height:60px; border-radius:6px; object-fit:cover;
}
.product-item p { margin-top:5px; font-size:12px; }

/* --- Order Section --- */
.order-section {
    flex:1 1 100%;
    background:#fff;
    border-radius:10px;
    padding:15px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}
.order-section table {
    width:100%;
    border-collapse: collapse;
    margin-top:10px;
}
.order-section th, .order-section td {
    border-bottom:1px solid #eee;
    padding:8px;
    font-size:12px;
    text-align:center;
}
.order-section th { background:#f8f9fa; }

button.qty-btn {
    padding: 2px 6px;
    border: none;
    border-radius: 4px;
    background: var(--blue);
    color: #fff;
    font-weight: 600;
    cursor: pointer;
    margin: 0 2px;
}
button.qty-btn:hover {
    background: #0056b3;
}

/* --- Payment Buttons --- */
.payment-buttons {
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    margin-top:15px;
}
.payment-buttons button {
    flex:1 1 100%;
    padding:12px 0;
    border:none;
    border-radius:8px;
    font-weight:600;
    font-size:13px;
    color:#fff;
    cursor:pointer;
    transition: all 0.3s ease;
}
.btn-payment { background:#6f42c1; }
.btn-save { background:#28a745; }
.btn-cancel { background:#dc3545; }
.payment-buttons button:hover { opacity:0.9; transform:translateY(-1px); }

/* --- Modal --- */
#addServiceModal {
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.4);
    display:none;
    align-items:center;
    justify-content:center;
    z-index:2000;
}
#addServiceModal .modal-content {
    background:#fff;
    padding:20px;
    width:90%;
    max-width:350px;
    max-height:80vh;
    overflow-y:auto;
    border-radius:10px;
}

/* --- Responsive --- */
@media(min-width:769px){
    .pos-container { flex-wrap:nowrap; padding:80px 20px 20px; }
    .products-section { flex:1 1 400px; max-height:none; }
    .order-section { flex:1.2 1 300px; }
    .payment-buttons button { flex:1; }
}
</style>
</head>
<body>

<div class="topbar">
    <h2>POS</h2>
    <button onclick="window.history.back()"><i class="fas fa-arrow-left"></i> Back</button>
</div>

<div class="pos-container">

    <!-- Products Section -->
    <div class="products-section">
        <input type="text" id="searchInput" placeholder="Search products...">
        <div class="product-grid">
            @foreach($services as $service)
            <div class="product-item" data-name="{{ strtolower($service->name) }}" onclick="addToOrder({{ $service->id }})">
                @if($service->icon_url)
                    <img src="{{ $service->icon_url }}" alt="{{ $service->name }}">
                @else
                    <i class="fas fa-box" style="font-size:40px; color:#333;"></i>
                @endif
                <p title="{{ $service->name }}">{{ Str::limit($service->name, 10, '...') }}</p>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Order Section -->
    <div class="order-section">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:10px; font-size:12px;">
            <div>
                <p>Order: <strong>#ORD-1</strong></p>
                <p>Date: <strong>{{ date('Y-m-d') }}</strong></p>
                <div style="display:flex; gap:10px; align-items:center;">
                    <span>Order Type:</span>
                    <label><input type="radio" name="orderType" value="pickup" checked> Pickup</label>
                    <label><input type="radio" name="orderType" value="delivery"> Delivery</label>
                </div>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <select id="selectCustomer" style="padding:6px 8px; border:1px solid #ccc; border-radius:6px; font-size:12px;">
                    <option value="">Select Customer</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
                <button onclick="openAddCustomerModal()" style="padding:6px 10px;"><i class="fas fa-plus"></i></button>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Price</th>
                    <th>QTY</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5" style="text-align:center; padding:20px; color:#999;">No items added yet.</td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top:10px; font-size:12px;">
            <div style="display:flex; justify-content:space-between;"><span>Add-on:</span><strong id="addonTotal">0.00 USD</strong></div>
            <div style="display:flex; justify-content:space-between;"><span>Sub Total:</span><strong id="subTotal">0.00 USD</strong></div>
            <div style="display:flex; justify-content:space-between; align-items:center;">
    <span>Discount:</span>
    <input type="number" id="discountInput" value="0" min="0" style="width:80px; padding:4px 6px; border:1px solid #ccc; border-radius:4px; font-size:12px;">
</div>

            <div style="display:flex; justify-content:space-between; margin-top:5px;"><span><strong>Gross Total:</strong></span><strong id="grossTotal">0.00 USD</strong></div>
        </div>

        <div style="margin-top:10px;">
            <label for="notes" style="font-size:12px;">Notes:</label>
            <textarea id="notes" placeholder="Enter notes here..." style="width:100%; height:50px; border-radius:6px; border:1px solid #ccc; padding:5px; font-size:12px;"></textarea>
        </div>

        <div class="payment-buttons">
            <button class="btn-payment">Payment</button>
            <button class="btn-save">Save</button>
            <button class="btn-cancel" onclick="cancelOrder()">Cancel</button>

        </div>
    </div>

</div>

<!-- Add Service Modal -->
<div id="addServiceModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); align-items:center; justify-content:center; z-index:2000;">

    <div style="background:#fff; padding:20px; width:90%; max-width:350px; max-height:80vh; overflow-y:auto; border-radius:10px;">
        <h3 style="margin-bottom:15px;">Add Service</h3>
        <input type="hidden" id="modal_service_id">

        <div style="margin-bottom:10px;">
            <label style="font-weight:500;">Name:</label>
            <p id="modal_service_name" style="padding:8px 10px; background:#f8f9fa; border-radius:6px; margin-top:5px;"></p>
        </div>

        <div style="margin-bottom:15px;">
            <label style="font-weight:500;">Qty:</label>
            <input type="number" id="modal_service_qty" value="1" min="1" style="width:100%; padding:8px 10px; border:1px solid #ddd; border-radius:6px; margin-top:5px;">
        </div>

        <div style="text-align:right;">
            <button onclick="closeAddModal()" style="margin-right:10px; padding:6px 12px; border-radius:6px; border:none; background:#dc3545; color:#fff;">Cancel</button>
            <button onclick="confirmAddService()" style="background:#28a745; color:#fff; padding:6px 12px; border-radius:6px; border:none;">Add</button>
        </div>
    </div>
</div>


<script>
let servicesData = @json($services);
let orderItems = [];

document.getElementById("searchInput").addEventListener("input", function(){
    const query = this.value.toLowerCase();
    document.querySelectorAll(".product-item").forEach(item=>{
        item.style.display = item.dataset.name.includes(query) ? "block" : "none";
    });
});

function addToOrder(id){
    const service = servicesData.find(s=>s.id===id);
    if(!service) return;
    document.getElementById("modal_service_id").value = service.id;
    document.getElementById("modal_service_name").textContent = service.name;

    document.getElementById("modal_service_qty").value = 1;
    document.getElementById("addServiceModal").style.display = "flex";
}

function closeAddModal(){ document.getElementById("addServiceModal").style.display = "none"; }

function confirmAddService(){
    const id = parseInt(document.getElementById("modal_service_id").value);
    const service = servicesData.find(s => s.id === id);
    if (!service) return;

    const name = service.name;
    const qty = parseInt(document.getElementById("modal_service_qty").value);
    const price = parseFloat(service.price) || 0;
    const total = price * qty;

    const existing = orderItems.find(i => i.id === id);
    if (existing) {
        existing.qty += qty;
        existing.total = existing.price * existing.qty;
    } else {
        orderItems.push({id, name, price, qty, total});
    }

    updateOrderTable();
    closeAddModal();
}

function updateOrderTable(){
    const tbody = document.querySelector("table tbody");
    tbody.innerHTML = "";

    if(orderItems.length === 0){
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:20px; color:#999;">No items added yet.</td></tr>';
    } else {
        orderItems.forEach(i => {
            tbody.innerHTML += `<tr>
                <td>${i.name}</td>
                <td>${i.price.toFixed(2)}</td>
                <td>
                    <button class="qty-btn" onclick="changeQty(${i.id}, -1)">-</button>
                    <span id="qty-${i.id}">${i.qty}</span>
                    <button class="qty-btn" onclick="changeQty(${i.id}, 1)">+</button>
                </td>
                <td>${i.total.toFixed(2)}</td>
            </tr>`;
        });
    }

    updateTotals();
}

function changeQty(id, delta) {
    const item = orderItems.find(i => i.id === id);
    if (!item) return;

    if (delta === -1 && item.qty === 1) {
        if (confirm(`Quantity is 1. Do you want to remove "${item.name}" from the order?`)) {
            orderItems = orderItems.filter(i => i.id !== id);
        }
    } else {
        item.qty += delta;
        if (item.qty < 1) item.qty = 1;
        item.total = item.price * item.qty;
    }

    updateOrderTable();
}

function updateTotals() {
    let subtotal = orderItems.reduce((sum, i) => sum + i.total, 0);
    let discount = parseFloat(document.getElementById("discountInput").value) || 0;
    let grossTotal = subtotal - discount;
    if (grossTotal < 0) grossTotal = 0; // Prevent negative totals

    document.getElementById("subTotal").textContent = subtotal.toFixed(2) + " USD";
    document.getElementById("discountTotal").textContent = discount.toFixed(2) + " USD";
    document.getElementById("grossTotal").textContent = grossTotal.toFixed(2) + " USD";
}


// Recalculate totals whenever discount changes
document.getElementById("discountInput").addEventListener("input", updateTotals);

function cancelOrder() {
    if(orderItems.length === 0){
        alert("Cart is already empty.");
        return;
    }

    if(confirm("Are you sure you want to cancel the order? This will clear the cart.")){
        orderItems = [];
        updateOrderTable();
    }
}

</script>

</body>
</html>
