@extends('layouts.app')

@section('title', 'Inventory - Products')
@section('page-title', 'Products')
@section('active-inventory-products', 'active')

@section('content')
<!-- Include the reusable modal CSS -->
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">

<style>
/* --- Inventory Products Page Specific Styles --- */
.table-container {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    padding: 25px;
    overflow-x: auto;
}

.header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.search-box input {
    padding: 10px 15px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-family: 'Poppins', sans-serif;
    width: 250px;
    min-width: 200px;
}

.add-btn {
    background: var(--blue);
    color: white;
    border: none;
    border-radius: 6px;
    padding: 10px 18px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: 0.2s ease;
    white-space: nowrap;
}

.add-btn i {
    margin-right: 6px;
}

.add-btn:hover {
    opacity: 0.85;
    background: #0056b3;
}

/* Table */
table {
    width: 100%;
    border-collapse: collapse;
    min-width: 800px;
}

th, td {
    text-align: left;
    padding: 12px 15px;
    font-size: 14px;
}

th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
}

td {
    border-bottom: 1px solid #f1f1f1;
    color: #2c3e50;
}

tbody tr:hover {
    background: #f9fbfd;
}

/* Status badge */
.status-active {
    background: #d4edda;
    color: #155724;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
}

.status-inactive {
    background: #f8d7da;
    color: #721c24;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
}

/* Action buttons */
.action-btns {
    display: flex;
    gap: 10px;
}

.action-btns i {
    font-size: 14px;
    padding: 8px;
    border-radius: 50%;
    cursor: pointer;
}

.edit {
    background: rgba(0, 123, 255, 0.1);
    color: var(--blue);
}

.delete {
    background: rgba(255, 0, 0, 0.1);
    color: red;
}

.edit:hover, .delete:hover {
    opacity: 0.8;
}

/* Money formatting */
.price-cell {
    font-weight: 500;
    color: #2c3e50;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .table-container {
        padding: 15px;
        margin: 0 -15px;
        border-radius: 0;
    }
    
    .header-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-box input {
        width: 100%;
        margin-bottom: 10px;
    }
    
    .add-btn {
        width: 100%;
        justify-content: center;
    }
    
    table {
        font-size: 13px;
        min-width: 600px;
    }
    
    th, td {
        padding: 10px;
    }
}
</style>

<div class="table-container">
    <div class="header-actions">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search Here">
        </div>
        <button class="add-btn" id="addBtn"><i class="fas fa-plus"></i> Add New Product</button>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Unit</th>
                <th>Purchase Price</th>
                <th>Available Stock</th>
                <th>Minimum Stock Level</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="productTable">
            @forelse($products as $index => $product)
                <tr data-id="{{ $product->id }}"
                    data-category-id="{{ $product->category_id }}"
                    data-unit-id="{{ $product->unit_id }}">
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category->name ?? '-' }}</td>
                    <td>{{ $product->unit->name ?? '-' }}</td>
                    <!-- CHANGED: from $product->price to $product->purchase_price -->
                    <td class="price-cell">₱{{ number_format($product->purchase_price, 2) }}</td>
                    <td>{{ $product->available_stock }}</td>
                    <td>{{ $product->minimum_stock_level }}</td>
                    <td>
                        <span class="status-{{ $product->status === 'active' ? 'active' : 'inactive' }}">
                            {{ ucfirst($product->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="action-btns">
                            <i class="fas fa-pen edit" data-id="{{ $product->id }}"></i>
                            <i class="fas fa-trash delete" data-id="{{ $product->id }}"></i>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align:center; color:#888;">No products found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Add Product Modal -->
<div id="addProductModal" class="modal modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Product</h3>
            <button type="button" class="close-btn" id="closeAddModal">&times;</button>
        </div>
        
        <form method="POST" action="{{ route('products.store') }}" id="addProductForm">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="name">
                        Product Name <span class="required-star">*</span>
                    </label>
                    <input type="text" name="name" id="name" placeholder="Enter product name" required>
                    <div class="error-message" id="name_error"></div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="category_id">
                            Category <span class="required-star">*</span>
                        </label>
                        <select name="category_id" id="category_id" required>
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <div class="error-message" id="category_id_error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="unit_id">
                            Unit <span class="required-star">*</span>
                        </label>
                        <select name="unit_id" id="unit_id" required>
                            <option value="">-- Select Unit --</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                        <div class="error-message" id="unit_id_error"></div>
                    </div>
                </div>
                
                <!-- CHANGED: Use purchase_price -->
                <div class="form-group">
                    <label for="purchase_price">
                        Purchase Price <span class="required-star">*</span>
                    </label>
                    <input type="number" step="0.01" min="0" name="purchase_price" id="purchase_price" placeholder="0.00" required>
                    <div class="error-message" id="purchase_price_error"></div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="available_stock">
                            Available Stock <span class="required-star">*</span>
                        </label>
                        <input type="number" min="0" name="available_stock" id="available_stock" placeholder="0" required>
                        <div class="error-message" id="available_stock_error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="minimum_stock_level">
                            Minimum Stock Level <span class="required-star">*</span>
                        </label>
                        <input type="number" min="0" name="minimum_stock_level" id="minimum_stock_level" placeholder="0" required>
                        <div class="error-message" id="minimum_stock_level_error"></div>
                        <div class="helper-text">Alert when stock goes below this level</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="status">
                        Status <span class="required-star">*</span>
                    </label>
                    <select name="status" id="status" required>
                        <option value="">-- Select Status --</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <div class="error-message" id="status_error"></div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="cancelAdd">Cancel</button>
                <button type="submit" class="btn-primary">Add Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Product Modal -->
<div id="editProductModal" class="modal modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Product</h3>
            <button type="button" class="close-btn" id="closeEditModal">&times;</button>
        </div>
        
        <form method="POST" action="{{ route('products.update') }}" id="editForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="product_id" id="edit_product_id">
            
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_name">
                        Product Name <span class="required-star">*</span>
                    </label>
                    <input type="text" name="name" id="edit_name" placeholder="Enter product name" required>
                    <div class="error-message" id="edit_name_error"></div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_category_id">
                            Category <span class="required-star">*</span>
                        </label>
                        <select name="category_id" id="edit_category_id" required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <div class="error-message" id="edit_category_id_error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_unit_id">
                            Unit <span class="required-star">*</span>
                        </label>
                        <select name="unit_id" id="edit_unit_id" required>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                        <div class="error-message" id="edit_unit_id_error"></div>
                    </div>
                </div>
                
                <!-- CHANGED: Use purchase_price -->
                <div class="form-group">
                    <label for="edit_purchase_price">
                        Purchase Price <span class="required-star">*</span>
                    </label>
                    <input type="number" step="0.01" min="0" name="purchase_price" id="edit_purchase_price" placeholder="0.00" required>
                    <div class="error-message" id="edit_purchase_price_error"></div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_available_stock">
                            Available Stock <span class="required-star">*</span>
                        </label>
                        <input type="number" min="0" name="available_stock" id="edit_available_stock" placeholder="0" required>
                        <div class="error-message" id="edit_available_stock_error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_minimum_stock_level">
                            Minimum Stock Level <span class="required-star">*</span>
                        </label>
                        <input type="number" min="0" name="minimum_stock_level" id="edit_minimum_stock_level" placeholder="0" required>
                        <div class="error-message" id="edit_minimum_stock_level_error"></div>
                        <div class="helper-text">Alert when stock goes below this level</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_status">
                        Status <span class="required-star">*</span>
                    </label>
                    <select name="status" id="edit_status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <div class="error-message" id="edit_status_error"></div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="cancelEdit">Cancel</button>
                <button type="submit" class="btn-primary">Update Product</button>
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
    document.querySelectorAll('input, select').forEach(field => {
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

function validateProductForm(form) {
    let valid = true;
    clearAllErrors();

    // CHANGED: Use 'purchase_price' instead of 'price'
    const requiredFields = ['name', 'category_id', 'unit_id', 'purchase_price', 'available_stock', 'minimum_stock_level', 'status'];
    
    requiredFields.forEach(field => {
        const input = form.querySelector(`[name="${field}"]`);
        const isEdit = form.id === 'editForm';
        const errorId = isEdit ? `edit_${field}_error` : `${field}_error`;
        
        if (input && !input.value.trim()) {
            showError(errorId, 'This field is required');
            valid = false;
        }
        
        // Validate numeric fields
        if (field === 'purchase_price' || field === 'available_stock' || field === 'minimum_stock_level') {
            if (input && (parseFloat(input.value) < 0)) {
                showError(errorId, 'Value must be zero or greater');
                valid = false;
            }
        }
    });

    return valid;
}

document.addEventListener('DOMContentLoaded', function () {

    const modalAdd = document.getElementById('addProductModal');
    const modalEdit = document.getElementById('editProductModal');

    const addBtn = document.getElementById('addBtn');
    const cancelAdd = document.getElementById('cancelAdd');
    const cancelEdit = document.getElementById('cancelEdit');
    const closeAddModal = document.getElementById('closeAddModal');
    const closeEditModal = document.getElementById('closeEditModal');

    const searchInput = document.getElementById('searchInput');

    // OPEN ADD MODAL
    addBtn.addEventListener('click', () => {
        openModal(modalAdd);
        // Reset form and set defaults
        document.getElementById('addProductForm').reset();
        document.getElementById('status').value = 'active';
        document.getElementById('purchase_price').value = ''; // CHANGED
        document.getElementById('available_stock').value = '';
        document.getElementById('minimum_stock_level').value = '';
    });

    cancelAdd.addEventListener('click', () => closeModal(modalAdd));
    closeAddModal.addEventListener('click', () => closeModal(modalAdd));

    // OPEN EDIT MODAL
    document.querySelectorAll('.edit').forEach(btn => {
        btn.addEventListener('click', e => {
            const row = e.target.closest('tr');

            document.getElementById('edit_product_id').value = row.dataset.id;
            document.getElementById('edit_name').value = row.cells[1].textContent.trim();
            document.getElementById('edit_category_id').value = row.dataset.categoryId;
            document.getElementById('edit_unit_id').value = row.dataset.unitId;
            
            // CHANGED: Extract purchase price value
            const priceText = row.cells[4].textContent.trim();
            const priceValue = priceText.replace(/[^\d.-]/g, '');
            document.getElementById('edit_purchase_price').value = priceValue; // CHANGED
            
            document.getElementById('edit_available_stock').value = row.cells[5].textContent.trim();
            document.getElementById('edit_minimum_stock_level').value = row.cells[6].textContent.trim();
            
            const statusText = row.cells[7].textContent.trim().toLowerCase();
            document.getElementById('edit_status').value = statusText;

            openModal(modalEdit);
        });
    });

    cancelEdit.addEventListener('click', () => closeModal(modalEdit));
    closeEditModal.addEventListener('click', () => closeModal(modalEdit));

    // CLOSE MODALS (CLICK OUTSIDE & ESCAPE)
    window.addEventListener('click', e => {
        if (e.target === modalAdd) closeModal(modalAdd);
        if (e.target === modalEdit) closeModal(modalEdit);
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeModal(modalAdd);
            closeModal(modalEdit);
        }
    });

    // SEARCH FILTER
    searchInput.addEventListener('input', function () {
        const query = this.value.toLowerCase();
        const rows = document.querySelectorAll('#productTable tr');
        let hasVisibleRows = false;

        rows.forEach(row => {
            // Skip the "no products" row
            if (row.cells.length < 2) return;
            
            const name = row.cells[1].textContent.toLowerCase();
            if (name.includes(query)) {
                row.style.display = '';
                hasVisibleRows = true;
            } else {
                row.style.display = 'none';
            }
        });

        // Show/hide the "no results" message
        const noResultsRow = document.querySelector('#productTable tr[colspan]');
        if (noResultsRow) {
            noResultsRow.style.display = hasVisibleRows ? 'none' : '';
        }
    });

    // RESET NUMBERING
    function resetTableNumbers() {
        document.querySelectorAll('#productTable tr:not([colspan])').forEach((row, i) => {
            if (row.cells.length > 0) {
                row.cells[0].textContent = i + 1;
            }
        });
    }

    // FORM VALIDATION
    document.getElementById('addProductForm')?.addEventListener('submit', function(e) {
        if (!validateProductForm(this)) {
            e.preventDefault();
        }
    });

    document.getElementById('editForm')?.addEventListener('submit', function(e) {
        if (!validateProductForm(this)) {
            e.preventDefault();
        }
    });

    // Clear errors on input
    document.querySelectorAll('#addProductForm input, #addProductForm select').forEach(field => {
        field.addEventListener('input', function() {
            this.style.borderColor = '#ddd';
            const errorElement = document.getElementById(this.name + '_error');
            if (errorElement) errorElement.style.display = 'none';
        });
    });

    document.querySelectorAll('#editForm input, #editForm select').forEach(field => {
        field.addEventListener('input', function() {
            this.style.borderColor = '#ddd';
            const errorElement = document.getElementById('edit_' + this.name + '_error');
            if (errorElement) errorElement.style.display = 'none';
        });
    });

    // DELETE PRODUCT (AJAX)
    document.querySelectorAll('.delete').forEach(btn => {
        btn.addEventListener('click', e => {
            const productId = e.target.dataset.id;
            const productName = e.target.closest('tr').cells[1].textContent.trim();

            if (!confirm(`Are you sure you want to delete the product "${productName}"?`)) {
                return;
            }

            fetch(`{{ route('products.destroy') }}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ product_id: productId })
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    // Remove the row from the table
                    const row = e.target.closest('tr');
                    row.remove();
                    
                    // Reset row numbers
                    resetTableNumbers();
                    
                    // If no rows left, show empty message
                    const remainingRows = document.querySelectorAll('#productTable tr:not([colspan])');
                    const noResultsRow = document.querySelector('#productTable tr[colspan]');
                    
                    if (remainingRows.length === 0 && !noResultsRow) {
                        const tbody = document.getElementById('productTable');
                        tbody.innerHTML = '<tr><td colspan="10" style="text-align:center; color:#888;">No products found.</td></tr>';
                    }
                } else {
                    alert("Delete failed: " + (data.message || "Unknown error"));
                }
            })
            .catch(err => {
                console.error('Delete error:', err);
                alert("Error deleting product. Please try again.");
            });
        });
    });

    // Auto-focus on modal open
    addBtn.addEventListener('click', () => {
        setTimeout(() => {
            document.getElementById('name')?.focus();
        }, 300);
    });

});
</script>

@endsection