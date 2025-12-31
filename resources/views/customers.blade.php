@php
use Illuminate\Support\Str;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'POS')</title>

<!-- Include the reusable modal CSS -->
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Flatpickr CSS for Date Picker -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

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

/* --- Customer Select Container --- */
.customer-select-container {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-bottom: 10px;
    flex-wrap: wrap;
}

.customer-select-container select {
    flex: 1;
    min-width: 150px;
    padding: 6px 8px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 12px;
    background-color: white;
}

.customer-select-container button {
    padding: 6px 10px;
    border: none;
    border-radius: 6px;
    font-size: 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 4px;
}

.btn-add-customer {
    background: #28a745;
    color: white;
}

.btn-add-customer:hover {
    background: #218838;
}

/* --- Date Selector --- */
.date-selector {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 5px;
}

.date-display {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 4px 8px;
    background: #f8f9fa;
    border-radius: 4px;
    border: 1px solid #e9ecef;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 12px;
}

.date-display:hover {
    background: #e9ecef;
    border-color: #dee2e6;
}

.date-display i {
    color: #6c757d;
    font-size: 11px;
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

/* --- Payment Modal Specific Styles --- */
.payment-amount-display {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin: 15px 0;
    text-align: center;
}

.amount-label {
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 5px;
}

.amount-value {
    font-size: 24px;
    font-weight: 600;
    color: #28a745;
}

/* Payment Real-time Display Styles */
.payment-change-display {
    margin-top: 10px;
    padding: 10px;
    background: #d4edda;
    border-radius: 6px;
    border: 1px solid #c3e6cb;
}

.change-info {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #155724;
    font-size: 14px;
}

.change-info i {
    color: #28a745;
}

.payment-shortfall-display {
    margin-top: 10px;
    padding: 10px;
    background: #f8d7da;
    border-radius: 6px;
    border: 1px solid #f5c6cb;
}

.shortfall-info {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #721c24;
    font-size: 14px;
}

.shortfall-info i {
    color: #dc3545;
}

/* Highlight input based on amount */
.payment-valid {
    border-color: #28a745 !important;
    background-color: #f8fff9;
}

.payment-invalid {
    border-color: #dc3545 !important;
    background-color: #fff8f8;
}

/* --- Calendar Modal Center --- */
.calendar-center {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 300px;
    padding: 20px;
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
                <div class="date-selector">
                    <p style="margin:0;">Date:</p>
                    <div class="date-display" id="dateDisplay">
                        <i class="fas fa-calendar-alt"></i>
                        <span id="selectedDate">{{ date('Y-m-d') }}</span>
                    </div>
                    <input type="hidden" id="orderDate" value="{{ date('Y-m-d') }}">
                </div>
            </div>
            <div class="customer-select-container">
                <select id="selectCustomer">
                    <option value="">Select Customer</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
                <button class="btn-add-customer" onclick="openAddCustomerModal()">
                    <i class="fas fa-plus"></i> Add
                </button>
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
            <div style="display:flex; justify-content:space-between; margin-top:5px;">
                <span><strong>Gross Total:</strong></span>
                <strong id="grossTotal">0.00 USD</strong>
            </div>
        </div>

        <div style="margin-top:10px;">
            <label for="notes" style="font-size:12px;">Notes:</label>
            <textarea id="notes" placeholder="Enter notes here..." style="width:100%; height:50px; border-radius:6px; border:1px solid #ccc; padding:5px; font-size:12px;"></textarea>
        </div>

        <div class="payment-buttons">
            <button class="btn-payment" onclick="openPaymentModal()">Payment</button>
            <button class="btn-save" onclick="saveOrder()">Save</button>
            <button class="btn-cancel" onclick="cancelOrder()">Cancel</button>
        </div>
    </div>

</div>

<!-- Add Service Modal -->
<div id="addServiceModal" class="modal modal-sm">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Service</h3>
            <button type="button" class="close-btn" onclick="closeAddModal()">&times;</button>
        </div>
        
        <div class="modal-body">
            <div class="form-group">
                <label>Name:</label>
                <p id="modal_service_name" style="padding:8px 10px; background:#f8f9fa; border-radius:6px; margin-top:5px;"></p>
                <input type="hidden" id="modal_service_id">
            </div>
            
            <div class="form-group">
                <label>Qty: <span class="required-star">*</span></label>
                <input type="number" id="modal_service_qty" value="1" min="1" style="width:100%; padding:8px 10px; border:1px solid #ddd; border-radius:6px; margin-top:5px;">
            </div>
        </div>
        
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeAddModal()">Cancel</button>
            <button type="button" class="btn-primary" onclick="confirmAddService()">Add</button>
        </div>
    </div>
</div>

<!-- Add Customer Modal for POS -->
<div id="addCustomerModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Customer</h3>
            <button type="button" class="close-btn" onclick="closeCustomerModal()">&times;</button>
        </div>
        
        <form id="addCustomerForm" onsubmit="handleAddCustomer(event)">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="customer_name">
                        Customer Name <span class="required-star">*</span>
                    </label>
                    <input type="text" name="name" id="customer_name" placeholder="Enter customer name" required>
                    <div class="error-message" id="customer_name_error"></div>
                </div>
                
                <div class="form-group">
                    <label for="customer_contact">
                        Contact Number
                    </label>
                    <input type="text" name="contact" id="customer_contact" placeholder="e.g., 09123456789">
                    <div class="error-message" id="customer_contact_error"></div>
                    <div class="helper-text">Optional - 10 to 15 digits only</div>
                </div>
                
                <div class="form-group">
                    <label for="customer_address">
                        Address
                    </label>
                    <textarea name="address" id="customer_address" rows="3" placeholder="Enter customer address"></textarea>
                    <div class="error-message" id="customer_address_error"></div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeCustomerModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Customer</button>
            </div>
        </form>
    </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="modal modal-sm">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Process Payment</h3>
            <button type="button" class="close-btn" onclick="closePaymentModal()">&times;</button>
        </div>
        
        <div class="modal-body">
            <!-- Amount Display -->
            <div class="payment-amount-display">
                <div class="amount-label">Total Amount Due</div>
                <div class="amount-value" id="paymentTotalAmount">0.00 USD</div>
            </div>
            
            <!-- Amount Input -->
            <div class="form-group">
                <label for="paymentAmount">
                    Payment Amount <span class="required-star">*</span>
                </label>
                <input type="number" id="paymentAmount" 
                       placeholder="Enter payment amount" 
                       min="0" 
                       step="0.01"
                       class="form-control">
                <div class="error-message" id="payment_amount_error"></div>
                <div class="helper-text">Enter the amount being paid</div>
                
                <!-- Real-time change display -->
                <div class="payment-change-display" id="paymentChangeDisplay" style="display: none;">
                    <div class="change-info">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Change: <strong id="paymentChangeAmount">0.00</strong> USD</span>
                    </div>
                </div>
                
                <!-- Real-time shortfall display -->
                <div class="payment-shortfall-display" id="paymentShortfallDisplay" style="display: none;">
                    <div class="shortfall-info">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Shortfall: <strong id="paymentShortfallAmount">0.00</strong> USD</span>
                    </div>
                </div>
            </div>
            
        </div>
        
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closePaymentModal()">Cancel</button>
            <button type="button" class="btn-primary" onclick="processPayment()">Process Payment</button>
        </div>
    </div>
</div>

<!-- Date Picker Modal -->
<div id="datePickerModal" class="modal modal-sm">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Select Date</h3>
            <button type="button" class="close-btn" onclick="closeDatePicker()">&times;</button>
        </div>
        
        <div class="modal-body">
            <div class="calendar-center" id="calendarContainer"></div>
        </div>
        
        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="cancelDateBtn" onclick="closeDatePicker()">Cancel</button>
            <button type="button" class="btn-primary" id="selectDateBtn" onclick="confirmDateSelection()">Select Date</button>
        </div>
    </div>
</div>

<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
// ============================================
// GLOBAL VARIABLES
// ============================================
let servicesData = @json($services);
let customersData = @json($customers);
let orderItems = [];
let orderData = {
    paymentAmount: null,
    totalAmount: 0,
    customerId: null,
    orderDate: "{{ date('Y-m-d') }}"
};

// Calendar instance
let calendarInstance = null;
let selectedCalendarDate = null;

// ============================================
// DATE PICKER FUNCTIONS
// ============================================
function openDatePicker() {
    // Initialize Flatpickr calendar
    const calendarContainer = document.getElementById('calendarContainer');
    calendarContainer.innerHTML = '';
    
    // Create calendar input
    const calendarInput = document.createElement('input');
    calendarInput.type = 'text';
    calendarInput.id = 'calendarInput';
    calendarInput.style.width = '100%';
    calendarInput.style.padding = '10px';
    calendarInput.style.border = 'none';
    calendarInput.style.textAlign = 'center';
    calendarInput.style.fontSize = '16px';
    calendarContainer.appendChild(calendarInput);
    
    // Initialize Flatpickr
    calendarInstance = flatpickr(calendarInput, {
        inline: true,
        dateFormat: "Y-m-d",
        defaultDate: orderData.orderDate,
        onChange: function(selectedDates, dateStr, instance) {
            selectedCalendarDate = dateStr;
            document.getElementById('calendarInput').value = dateStr;
        },
        onReady: function(selectedDates, dateStr, instance) {
            // Center the calendar
            const calendarElement = instance.calendarContainer;
            calendarElement.style.margin = '0 auto';
            calendarElement.style.width = '100%';
        }
    });
    
    // Set initial value
    calendarInput.value = orderData.orderDate;
    selectedCalendarDate = orderData.orderDate;
    
    openModal(document.getElementById("datePickerModal"));
}

function closeDatePicker() {
    closeModal(document.getElementById("datePickerModal"));
}

function confirmDateSelection() {
    if (selectedCalendarDate) {
        orderData.orderDate = selectedCalendarDate;
        document.getElementById('selectedDate').textContent = selectedCalendarDate;
        document.getElementById('orderDate').value = selectedCalendarDate;
        closeDatePicker();
    }
}

// ============================================
// MODAL UTILITY FUNCTIONS
// ============================================
function openModal(modal) {
    modal.classList.add('active');
    document.body.classList.add('modal-open');
}

function closeModal(modal) {
    modal.classList.remove('active');
    document.body.classList.remove('modal-open');
    if (modal.id === 'addCustomerModal') {
        clearCustomerErrors();
    } else if (modal.id === 'paymentModal') {
        resetPaymentModalErrors();
    }
}

// ============================================
// CUSTOMER MODAL FUNCTIONS
// ============================================
function clearCustomerErrors() {
    document.querySelectorAll('#addCustomerForm .error-message').forEach(error => {
        error.style.display = 'none';
        error.textContent = '';
    });
    document.querySelectorAll('#addCustomerForm input, #addCustomerForm textarea').forEach(field => {
        field.style.borderColor = '#ddd';
    });
}

function showCustomerError(fieldId, message) {
    const errorElement = document.getElementById(fieldId);
    const inputElement = document.querySelector(`#addCustomerForm [name="${fieldId.replace('customer_', '').replace('_error', '')}"]`);
    if (errorElement && inputElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
        inputElement.style.borderColor = '#dc3545';
    }
}

function validatePhone(phone) {
    if (!phone) return true;
    return /^[0-9]{10,15}$/.test(phone.replace(/\s/g, ''));
}

function validateCustomerForm() {
    let valid = true;
    clearCustomerErrors();
    
    const nameField = document.getElementById('customer_name');
    const contactField = document.getElementById('customer_contact');
    
    if (!nameField || !nameField.value.trim()) {
        showCustomerError('customer_name_error', 'Customer name is required');
        valid = false;
    }
    
    if (contactField && contactField.value && !validatePhone(contactField.value)) {
        showCustomerError('customer_contact_error', 'Contact number must be 10-15 digits');
        valid = false;
    }
    
    return valid;
}

function openAddCustomerModal() {
    document.getElementById('addCustomerForm').reset();
    clearCustomerErrors();
    openModal(document.getElementById("addCustomerModal"));
    
    setTimeout(() => {
        document.getElementById('customer_name')?.focus();
    }, 300);
}

function closeCustomerModal() {
    closeModal(document.getElementById("addCustomerModal"));
}

async function handleAddCustomer(event) {
    event.preventDefault();
    
    if (!validateCustomerForm()) return;
    
    const formData = new FormData(event.target);
    
    try {
        const response = await fetch('{{ route("customers.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (response.ok) {
            const select = document.getElementById('selectCustomer');
            const option = document.createElement('option');
            option.value = data.customer.id;
            option.textContent = data.customer.name;
            select.appendChild(option);
            select.value = data.customer.id;
            customersData.push(data.customer);
            closeCustomerModal();
            alert('Customer added successfully!');
        } else {
            if (data.errors) {
                if (data.errors.name) showCustomerError('customer_name_error', data.errors.name[0]);
                if (data.errors.contact) showCustomerError('customer_contact_error', data.errors.contact[0]);
                if (data.errors.address) showCustomerError('customer_address_error', data.errors.address[0]);
            } else {
                alert('Error adding customer: ' + (data.message || 'Unknown error'));
            }
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error adding customer. Please try again.');
    }
}

// ============================================
// PAYMENT MODAL FUNCTIONS - REAL-TIME UPDATE
// ============================================
function openPaymentModal() {
    if (orderItems.length === 0) {
        alert('Please add items to the order first.');
        return;
    }
    
    const total = calculateTotal();
    orderData.totalAmount = total;
    document.getElementById('paymentTotalAmount').textContent = total.toFixed(2) + ' USD';
    
    // If payment amount already exists, show it
    if (orderData.paymentAmount !== null) {
        document.getElementById('paymentAmount').value = orderData.paymentAmount.toFixed(2);
    } else {
        document.getElementById('paymentAmount').value = total.toFixed(2);
    }
    
    resetPaymentModalErrors();
    openModal(document.getElementById("paymentModal"));
    
    // Calculate initial change/shortfall
    calculatePaymentStatus();
}

function closePaymentModal() {
    closeModal(document.getElementById("paymentModal"));
}

function resetPaymentModalErrors() {
    document.getElementById('payment_amount_error').style.display = 'none';
    document.getElementById('payment_amount_error').textContent = '';
    
    // Reset real-time displays
    document.getElementById('paymentChangeDisplay').style.display = 'none';
    document.getElementById('paymentShortfallDisplay').style.display = 'none';
    
    // Reset input styling
    const paymentInput = document.getElementById('paymentAmount');
    paymentInput.classList.remove('payment-valid', 'payment-invalid');
}

// Real-time calculation of change/shortfall
function calculatePaymentStatus() {
    const paymentInput = document.getElementById('paymentAmount');
    const paymentAmount = parseFloat(paymentInput.value) || 0;
    const total = orderData.totalAmount;
    
    // Reset styles and displays
    paymentInput.classList.remove('payment-valid', 'payment-invalid');
    document.getElementById('paymentChangeDisplay').style.display = 'none';
    document.getElementById('paymentShortfallDisplay').style.display = 'none';
    
    // Clear error message if amount is valid
    if (paymentAmount >= total) {
        document.getElementById('payment_amount_error').style.display = 'none';
    }
    
    if (paymentAmount === 0) {
        // No amount entered
        return;
    }
    
    if (paymentAmount > total) {
        // Payment exceeds total - show change
        const change = paymentAmount - total;
        document.getElementById('paymentChangeAmount').textContent = change.toFixed(2);
        document.getElementById('paymentChangeDisplay').style.display = 'block';
        paymentInput.classList.add('payment-valid');
    } else if (paymentAmount < total && paymentAmount > 0) {
        // Payment less than total - show shortfall
        const shortfall = total - paymentAmount;
        document.getElementById('paymentShortfallAmount').textContent = shortfall.toFixed(2);
        document.getElementById('paymentShortfallDisplay').style.display = 'block';
        paymentInput.classList.add('payment-invalid');
    } else if (paymentAmount === total) {
        // Exact payment
        paymentInput.classList.add('payment-valid');
    }
}

function validatePaymentForm() {
    let valid = true;
    
    resetPaymentModalErrors();
    
    const paymentAmount = parseFloat(document.getElementById('paymentAmount').value) || 0;
    const netTotal = calculateTotal();  // This already includes discount
    
    if (paymentAmount <= 0) {
        document.getElementById('payment_amount_error').textContent = 'Please enter payment amount';
        document.getElementById('payment_amount_error').style.display = 'block';
        valid = false;
    } else if (paymentAmount < netTotal) {
        document.getElementById('payment_amount_error').textContent = 'Amount is less than total after discount. Please enter full amount.';
        document.getElementById('payment_amount_error').style.display = 'block';
        valid = false;
    }
    
    return valid;
}

function processPayment() {
    if (!validatePaymentForm()) return;
    
    const paymentAmount = parseFloat(document.getElementById('paymentAmount').value);
    const netTotal = calculateTotal();
    
    // FIXED: Store the ACTUAL payment amount
    orderData.paymentAmount = paymentAmount;  // Store $100 (actual payment)
    
    const change = paymentAmount - netTotal;
    
    if (change > 0) {
        alert(`Payment recorded!\nNet Amount Due: ${netTotal.toFixed(2)} USD\nAmount Paid: ${paymentAmount.toFixed(2)} USD\nChange: ${change.toFixed(2)} USD`);
    } else {
        alert(`Payment recorded!\nNet Amount Due: ${netTotal.toFixed(2)} USD\nAmount Paid: ${paymentAmount.toFixed(2)} USD`);
    }
    
    console.log('Payment Data:', {
        net_amount_due: netTotal,      // $80 (what customer owes after discount)
        amount_paid: paymentAmount,    // $100 (what gets stored in database)
        change_given: change           // $20 (cash handling)
    });
    
    closePaymentModal();
}

// ============================================
// EVENT LISTENERS FOR REAL-TIME CALCULATION
// ============================================
document.addEventListener("DOMContentLoaded", () => {
    // Date display click event
    document.getElementById('dateDisplay').addEventListener('click', openDatePicker);
    
    // Real-time payment calculation
    document.getElementById('paymentAmount')?.addEventListener('input', function() {
        calculatePaymentStatus();
    });
    
    // Real-time payment calculation on keyup (for backspace, delete, etc.)
    document.getElementById('paymentAmount')?.addEventListener('keyup', function() {
        calculatePaymentStatus();
    });
    
    // Real-time payment calculation on change (for arrow buttons)
    document.getElementById('paymentAmount')?.addEventListener('change', function() {
        calculatePaymentStatus();
    });
    
    // Search functionality
    document.getElementById("searchInput").addEventListener("input", function() {
        const query = this.value.toLowerCase();
        document.querySelectorAll(".product-item").forEach(item => {
            item.style.display = item.dataset.name.includes(query) ? "block" : "none";
        });
    });
    
    // Discount input - update payment modal in real-time
    document.getElementById("discountInput").addEventListener("input", function() {
        updateTotals();
        
        // Update payment modal if open
        if (document.getElementById('paymentModal').classList.contains('active')) {
            const total = calculateTotal();
            orderData.totalAmount = total;
            document.getElementById('paymentTotalAmount').textContent = total.toFixed(2) + ' USD';
            
            // Recalculate payment status with new total
            calculatePaymentStatus();
            
            // If payment amount is less than new total, clear it
            const paymentAmount = parseFloat(document.getElementById('paymentAmount').value) || 0;
            if (orderData.paymentAmount !== null && orderData.paymentAmount < total) {
                document.getElementById('paymentAmount').value = '';
                orderData.paymentAmount = null;
                resetPaymentModalErrors();
            }
        }
    });
    
    // Customer form input validation
    document.querySelectorAll('#addCustomerForm input, #addCustomerForm textarea').forEach(field => {
        field.addEventListener('input', function() {
            this.style.borderColor = '#ddd';
            const errorElement = document.getElementById('customer_' + this.name + '_error');
            if (errorElement) errorElement.style.display = 'none';
        });
    });
    
    // Phone validation
    document.getElementById('customer_contact')?.addEventListener('blur', function() {
        if (this.value && !validatePhone(this.value)) {
            showCustomerError('customer_contact_error', 'Contact number must be 10-15 digits');
        }
    });
});

// ============================================
// SERVICE MODAL FUNCTIONS
// ============================================
function addToOrder(id) {
    const service = servicesData.find(s => s.id === id);
    if (!service) return;
    
    document.getElementById("modal_service_id").value = service.id;
    document.getElementById("modal_service_name").textContent = service.name;
    document.getElementById("modal_service_qty").value = 1;
    
    openModal(document.getElementById("addServiceModal"));
}

function closeAddModal() {
    closeModal(document.getElementById("addServiceModal"));
}

function confirmAddService() {
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

// ============================================
// ORDER MANAGEMENT FUNCTIONS
// ============================================
function updateOrderTable() {
    const tbody = document.querySelector("table tbody");
    tbody.innerHTML = "";

    if (orderItems.length === 0) {
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

function calculateTotal() {
    let subtotal = orderItems.reduce((sum, i) => sum + i.total, 0);
    let discount = parseFloat(document.getElementById("discountInput").value) || 0;
    let grossTotal = subtotal - discount;
    return grossTotal < 0 ? 0 : grossTotal;
}

function updateTotals() {
    const total = calculateTotal();
    orderData.totalAmount = total;
    
    document.getElementById("subTotal").textContent = orderItems.reduce((sum, i) => sum + i.total, 0).toFixed(2) + " USD";
    document.getElementById("grossTotal").textContent = total.toFixed(2) + " USD";
    
    // Update payment modal if open
    if (document.getElementById('paymentModal').classList.contains('active')) {
        document.getElementById('paymentTotalAmount').textContent = total.toFixed(2) + ' USD';
        
        // If payment amount is less than new total, clear it
        if (orderData.paymentAmount !== null && orderData.paymentAmount < total) {
            document.getElementById('paymentAmount').value = '';
            orderData.paymentAmount = null;
        }
    }
}

function cancelOrder() {
    if (orderItems.length === 0) {
        alert("Cart is already empty.");
        return;
    }

    if (confirm("Are you sure you want to cancel the order? This will clear the cart and payment information.")) {
        orderItems = [];
        orderData = {
            paymentAmount: null,
            totalAmount: 0,
            customerId: null,
            orderDate: "{{ date('Y-m-d') }}"
        };
        document.getElementById('selectedDate').textContent = "{{ date('Y-m-d') }}";
        document.getElementById('orderDate').value = "{{ date('Y-m-d') }}";
        updateOrderTable();
    }
}

function saveOrder() {
    if (orderItems.length === 0) {
        alert('No items in the order to save.');
        return;
    }
    
    if (orderData.paymentAmount === null) {
        if (!confirm('Payment has not been recorded. Do you want to save as draft instead?')) {
            return;
        }
    }
    
    const netTotal = calculateTotal();
    const discount = parseFloat(document.getElementById('discountInput').value) || 0;
    
    const orderToSave = {
        items: orderItems,
        subtotal: orderItems.reduce((sum, i) => sum + i.total, 0),  // Before discount
        discount: discount,
        net_total: netTotal,  // After discount
        customerId: document.getElementById('selectCustomer').value || null,
        orderDate: orderData.orderDate,
        notes: document.getElementById('notes').value,
        payment: orderData.paymentAmount !== null ? {
            amount: orderData.paymentAmount,  // This is the ACTUAL payment amount ($100)
            net_amount_due: netTotal,         // What customer owed ($80)
            change: orderData.paymentAmount > netTotal ? orderData.paymentAmount - netTotal : 0
        } : null
    };
    
    console.log('Saving order:', orderToSave);
    
    if (orderToSave.payment) {
        alert(`Order saved!\nDate: ${orderToSave.orderDate}\nSubtotal: ${orderToSave.subtotal.toFixed(2)} USD\nDiscount: ${orderToSave.discount.toFixed(2)} USD\nNet Total: ${orderToSave.net_total.toFixed(2)} USD\nPayment Received: ${orderToSave.payment.amount.toFixed(2)} USD`);
    } else {
        alert(`Order saved as draft (no payment recorded).\nDate: ${orderToSave.orderDate}`);
    }
    
    // Reset for next order
    orderItems = [];
    orderData = {
        paymentAmount: null,
        totalAmount: 0,
        customerId: null,
        orderDate: "{{ date('Y-m-d') }}"
    };
    document.getElementById('selectedDate').textContent = "{{ date('Y-m-d') }}";
    document.getElementById('orderDate').value = "{{ date('Y-m-d') }}";
    updateOrderTable();
    document.getElementById('notes').value = '';
    document.getElementById('discountInput').value = 0;
    document.getElementById('selectCustomer').value = '';
}

// ============================================
// INITIALIZATION & EVENT LISTENERS
// ============================================
document.addEventListener("DOMContentLoaded", () => {
    // Search functionality
    document.getElementById("searchInput").addEventListener("input", function() {
        const query = this.value.toLowerCase();
        document.querySelectorAll(".product-item").forEach(item => {
            item.style.display = item.dataset.name.includes(query) ? "block" : "none";
        });
    });
    
    // Discount input
    document.getElementById("discountInput").addEventListener("input", updateTotals);
    
    // Customer form input validation
    document.querySelectorAll('#addCustomerForm input, #addCustomerForm textarea').forEach(field => {
        field.addEventListener('input', function() {
            this.style.borderColor = '#ddd';
            const errorElement = document.getElementById('customer_' + this.name + '_error');
            if (errorElement) errorElement.style.display = 'none';
        });
    });
    
    // Phone validation
    document.getElementById('customer_contact')?.addEventListener('blur', function() {
        if (this.value && !validatePhone(this.value)) {
            showCustomerError('customer_contact_error', 'Contact number must be 10-15 digits');
        }
    });
});

// Close modals when clicking outside
window.addEventListener('click', function(e) {
    const serviceModal = document.getElementById('addServiceModal');
    const customerModal = document.getElementById('addCustomerModal');
    const paymentModal = document.getElementById('paymentModal');
    const datePickerModal = document.getElementById('datePickerModal');
    
    if (e.target === serviceModal) closeAddModal();
    if (e.target === customerModal) closeCustomerModal();
    if (e.target === paymentModal) closePaymentModal();
    if (e.target === datePickerModal) closeDatePicker();
});

// Close modals on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddModal();
        closeCustomerModal();
        closePaymentModal();
        closeDatePicker();
    }
});
</script>

</body>
</html>