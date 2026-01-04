@extends('layouts.app')

@section('title', 'Customers')
@section('page-title', 'Customers')

@section('content')
<!-- Include the reusable modal CSS -->
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">

<style>
/* ================================
   Customers Page – FINAL FIX
   ================================ */

.customers-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    width: 100%;
}

/* Search + Add */
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
    width: 250px;
    min-width: 200px;
    padding: 10px 15px 10px 40px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
    transition: 0.3s;
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

.add-customer-btn {
    background: var(--blue);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 10px 20px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}

.add-customer-btn:hover {
    background: #0056b3;
}

/* ================================
   TABLE (FULL WIDTH FIX)
   ================================ */

.customers-table {
    width: 100%;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

/* Header */
.table-header {
    display: grid;
    grid-template-columns: 60px 2fr 1.5fr 2fr 1fr;
    width: 100%;
    padding: 15px 20px;
    background: #f8f9fa;
    font-weight: 600;
    border-bottom: 1px solid #eaeaea;
    font-size: 14px;
    text-align: left;
}

/* Rows */
.table-row {
    display: grid;
    grid-template-columns: 60px 2fr 1.5fr 2fr 1fr;
    width: 100%;
    padding: 15px 20px;
    border-bottom: 1px solid #f0f0f0;
    align-items: center;
    font-size: 14px;
}

.table-row:last-child {
    border-bottom: none;
}

.table-row.no-results {
    grid-template-columns: 1fr;
    text-align: center;
    color: #999;
    font-style: italic;
    padding: 30px 20px;
}

.customer-name {
    font-weight: 600;
    color: #2c3e50;
}

.customer-contact {
    font-weight: 500;
    color: #495057;
}

.customer-address {
    color: #6c757d;
    font-size: 13px;
    line-height: 1.4;
}

/* Actions */
.action-buttons {
    display: flex;
    gap: 6px;
}

.action-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: none;
    border: none;
    color: var(--blue);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.action-btn:hover {
    background: rgba(0, 123, 255, 0.1);
    color: #0056b3;
}

.action-btn.delete:hover {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

/* ================================
   MOBILE
   ================================ */
@media (max-width: 768px) {
    .customers-header {
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
    }
    
    .search-add-container {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-box input {
        width: 100%;
        min-width: unset;
    }
    
    .add-customer-btn {
        width: 100%;
        justify-content: center;
    }
    
    .table-header {
        display: none;
    }

    .table-row {
        grid-template-columns: 1fr;
        border: 1px solid #eaeaea;
        border-radius: 8px;
        margin-bottom: 10px;
        padding: 12px;
        gap: 10px;
    }
    
    .action-buttons {
        justify-content: flex-start;
        margin-top: 10px;
    }
}

@media (max-width: 576px) {
    .table-row {
        font-size: 13px;
    }
    
    .action-btn {
        width: 28px;
        height: 28px;
        font-size: 12px;
    }
}
</style>

<div class="customers-header">
    <div class="search-add-container">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search customers...">
        </div>
        <button class="add-customer-btn" id="addCustomerBtn"><i class="fas fa-plus"></i> Add New Customer</button>
    </div>
</div>

<div class="customers-table">
    <div class="table-header">
        <div>#</div><div>Customer Name</div><div>Contact</div><div>Address</div><div>Action</div>
    </div>

    @forelse($customers as $index => $customer)
    <div class="table-row" data-id="{{ $customer->id }}">
        <div>{{ $index + 1 }}</div>
        <div class="customer-name">{{ $customer->name }}</div>
        <div class="customer-contact">{{ $customer->contact }}</div>
        <div class="customer-address">{{ $customer->address }}</div>
        <div class="action-buttons">
            <button class="action-btn edit-btn" title="Edit"><i class="fas fa-edit"></i></button>

            <button class="action-btn delete" title="Delete"><i class="fas fa-trash"></i></button>
        </div>
    </div>
    @empty
    <div class="table-row no-results">
        <div>No customers found.</div>
    </div>
    @endforelse
</div>

<!-- Add Customer Modal -->
<div id="addCustomerModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Customer</h3>
            <button type="button" class="close-btn" id="closeAddModal">&times;</button>
        </div>
        
        <form method="POST" action="{{ route('customers.store') }}" id="addCustomerForm">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="name">
                        Customer Name <span class="required-star">*</span>
                    </label>
                    <input type="text" name="name" id="name" placeholder="Enter customer name" required>
                    <div class="error-message" id="name_error"></div>
                </div>
                
                <div class="form-group">
                    <label for="contact">
                        Contact Number
                    </label>
                    <input type="text" name="contact" id="contact" placeholder="e.g., 09123456789">
                    <div class="error-message" id="contact_error"></div>
                    <div class="helper-text">Optional - 10 to 15 digits only</div>
                </div>
                
                <div class="form-group">
                    <label for="address">
                        Address
                    </label>
                    <textarea name="address" id="address" rows="3" placeholder="Enter customer address"></textarea>
                    <div class="error-message" id="address_error"></div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="cancelAdd">Cancel</button>
                <button type="submit" class="btn-primary">Save Customer</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Customer Modal -->
<div id="editCustomerModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Customer</h3>
            <button type="button" class="close-btn" id="closeEditModal">&times;</button>
        </div>
        
        <form method="POST" id="editCustomerForm">
            @csrf
            @method('PUT')
            
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_name">
                        Customer Name <span class="required-star">*</span>
                    </label>
                    <input type="text" name="name" id="edit_name" placeholder="Enter customer name" required>
                    <div class="error-message" id="edit_name_error"></div>
                </div>
                
                <div class="form-group">
                    <label for="edit_contact">
                        Contact Number
                    </label>
                    <input type="text" name="contact" id="edit_contact" placeholder="e.g., 09123456789">
                    <div class="error-message" id="edit_contact_error"></div>
                    <div class="helper-text">Optional - 10 to 15 digits only</div>
                </div>
                
                <div class="form-group">
                    <label for="edit_address">
                        Address
                    </label>
                    <textarea name="address" id="edit_address" rows="3" placeholder="Enter customer address"></textarea>
                    <div class="error-message" id="edit_address_error"></div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="cancelEdit">Cancel</button>
                <button type="submit" class="btn-primary">Update Customer</button>
            </div>
        </form>
    </div>
</div>

<script>
// Modal Utility Functions
function openModal(modal) {
    modal.classList.add('active');
    document.body.classList.add('modal-open');
}

function closeModal(modal) {
    modal.classList.remove('active');
    document.body.classList.remove('modal-open');
    clearAllErrors();
}

function clearAllErrors() {
    document.querySelectorAll('.error-message').forEach(error => {
        error.style.display = 'none';
        error.textContent = '';
    });
    document.querySelectorAll('input, textarea').forEach(field => {
        field.style.borderColor = '#ddd';
    });
}

function showError(fieldId, message) {
    const errorElement = document.getElementById(fieldId);
    const inputElement = document.querySelector(`[name="${fieldId.replace('_error', '')}"]`);
    if (errorElement && inputElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
        inputElement.style.borderColor = '#dc3545';
    }
}

function validatePhone(phone) {
    if (!phone) return true; // Optional field
    return /^[0-9]{10,15}$/.test(phone.replace(/\s/g, ''));
}

function validateForm(form) {
    let valid = true;
    clearAllErrors();
    
    const nameField = form.querySelector('[name="name"]');
    const contactField = form.querySelector('[name="contact"]');
    const isEdit = form.id === 'editCustomerForm';
    
    if (!nameField || !nameField.value.trim()) {
        showError(isEdit ? 'edit_name_error' : 'name_error', 'Customer name is required');
        valid = false;
    }
    
    if (contactField && contactField.value && !validatePhone(contactField.value)) {
        showError(isEdit ? 'edit_contact_error' : 'contact_error', 'Contact number must be 10-15 digits');
        valid = false;
    }
    
    return valid;
}

document.addEventListener("DOMContentLoaded", () => {
    const addModal = document.getElementById("addCustomerModal");
    const editModal = document.getElementById("editCustomerModal");
    const addBtn = document.getElementById("addCustomerBtn");
    const cancelAddBtn = document.getElementById("cancelAdd");
    const cancelEditBtn = document.getElementById("cancelEdit");
    const closeAddModal = document.getElementById("closeAddModal");
    const closeEditModal = document.getElementById("closeEditModal");
    const addForm = document.getElementById("addCustomerForm");
    const editForm = document.getElementById("editCustomerForm");
    const searchInput = document.getElementById("searchInput");
    const tableRows = document.querySelectorAll(".table-row:not(.no-results)");

    // ----------------------------
    // Open Add Modal
    // ----------------------------
    addBtn.addEventListener('click', () => { 
        openModal(addModal);
        addForm.reset();
        
        // Auto-focus on name field
        setTimeout(() => {
            document.getElementById('name')?.focus();
        }, 300);
    });

    // ----------------------------
    // Close Modals
    // ----------------------------
    cancelAddBtn.addEventListener('click', () => closeModal(addModal));
    closeAddModal.addEventListener('click', () => closeModal(addModal));
    
    cancelEditBtn.addEventListener('click', () => closeModal(editModal));
    closeEditModal.addEventListener('click', () => closeModal(editModal));

    // ----------------------------
    // Edit buttons
    // ----------------------------
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const row = e.target.closest('.table-row');
            const customerId = row.dataset.id;
            
            editForm.action = `/customers/${customerId}`;
            document.getElementById('edit_name').value = row.querySelector('.customer-name').innerText;
            document.getElementById('edit_contact').value = row.querySelector('.customer-contact').innerText;
            document.getElementById('edit_address').value = row.querySelector('.customer-address').innerText;
            
            openModal(editModal);
            
            // Auto-focus on name field
            setTimeout(() => {
                document.getElementById('edit_name')?.focus();
            }, 300);
        });
    });

    // ----------------------------
    // Close modals on outside click & escape
    // ----------------------------
    window.addEventListener('click', e => {
        if(e.target === addModal) closeModal(addModal);
        if(e.target === editModal) closeModal(editModal);
    });
    
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeModal(addModal);
            closeModal(editModal);
        }
    });

    // ----------------------------
    // Form Validation
    // ----------------------------
    addForm.addEventListener('submit', function(e) {
        if (!validateForm(this)) {
            e.preventDefault();
        }
    });
    
    editForm.addEventListener('submit', function(e) {
        if (!validateForm(this)) {
            e.preventDefault();
        }
    });
    
    // Real-time phone validation
    document.getElementById('contact')?.addEventListener('blur', function() {
        if (this.value && !validatePhone(this.value)) {
            showError('contact_error', 'Contact number must be 10-15 digits');
        }
    });
    
    document.getElementById('edit_contact')?.addEventListener('blur', function() {
        if (this.value && !validatePhone(this.value)) {
            showError('edit_contact_error', 'Contact number must be 10-15 digits');
        }
    });
    
    // Clear errors on input
    document.querySelectorAll('#addCustomerForm input, #addCustomerForm textarea').forEach(field => {
        field.addEventListener('input', function() {
            this.style.borderColor = '#ddd';
            const errorElement = document.getElementById(this.name + '_error');
            if (errorElement) errorElement.style.display = 'none';
        });
    });
    
    document.querySelectorAll('#editCustomerForm input, #editCustomerForm textarea').forEach(field => {
        field.addEventListener('input', function() {
            this.style.borderColor = '#ddd';
            const errorElement = document.getElementById('edit_' + this.name + '_error');
            if (errorElement) errorElement.style.display = 'none';
        });
    });

    // ----------------------------
    // Live search filter
    // ----------------------------
    searchInput.addEventListener('input', () => {
        const query = searchInput.value.toLowerCase().trim();
        let anyVisible = false;

        tableRows.forEach(row => {
            const name = row.querySelector('.customer-name').innerText.toLowerCase();
            const contact = row.querySelector('.customer-contact').innerText.toLowerCase();
            const address = row.querySelector('.customer-address').innerText.toLowerCase();

            if(name.includes(query) || contact.includes(query) || address.includes(query)){
                row.style.display = 'grid';
                anyVisible = true;
            } else {
                row.style.display = 'none';
            }
        });

        // Handle "No customers found" message
        let noResultsRow = document.querySelector('.table-row.no-results');
        
        if (!anyVisible && !noResultsRow) {
            noResultsRow = document.createElement('div');
            noResultsRow.classList.add('table-row', 'no-results');
            noResultsRow.innerHTML = `<div>No customers found.</div>`;
            document.querySelector('.customers-table').appendChild(noResultsRow);
        }
        
        if (noResultsRow) {
            noResultsRow.style.display = anyVisible ? 'none' : 'grid';
        }
    });
    
    // ----------------------------
    // Delete functionality (if needed)
    // ----------------------------
    document.querySelectorAll('.action-btn.delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const row = e.target.closest('.table-row');
            const customerId = row.dataset.id;
            const customerName = row.querySelector('.customer-name').innerText.trim();
            
            if (!confirm(`Are you sure you want to delete "${customerName}"?`)) {
                return;
            }
            
            // Create a form to submit the DELETE request
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/customers/${customerId}`;
            form.style.display = 'none';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            
            form.appendChild(csrfToken);
            form.appendChild(methodField);
            document.body.appendChild(form);
            form.submit();
        });
    });
});
</script>
@endsection