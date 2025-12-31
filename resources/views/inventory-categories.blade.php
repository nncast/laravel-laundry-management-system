@extends('layouts.app')

@section('title', 'Inventory - Categories')
@section('page-title', 'Categories')
@section('active-inventory-categories', 'active')

@section('content')
<!-- Include the reusable modal CSS -->
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">

<style>
/* ================================
   Categories Page – FIXED
   ================================ */

.table-container {
    width: 100%;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    padding: 25px;
}

/* Header */
.header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
}

.search-box input {
    width: 240px;
    padding: 10px 15px;
    border-radius: 8px;
    border: 1px solid #ddd;
    font-size: 14px;
    transition: 0.2s ease;
}

.search-box input:focus {
    outline: none;
    border-color: var(--blue);
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.add-btn {
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
}

.add-btn:hover {
    background: #0056b3;
}

/* ================================
   TABLE
   ================================ */

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background: #f8f9fa;
}

th {
    font-size: 14px;
    font-weight: 600;
    padding: 14px 16px;
    color: var(--text-dark);
    text-align: left;
    border-bottom: 1px solid #eaeaea;
}

td {
    padding: 14px 16px;
    font-size: 14px;
    color: var(--text-dark);
    border-bottom: 1px solid #f1f1f1;
}

tbody tr:hover {
    background: #f9fbfd;
}

/* ================================
   STATUS
   ================================ */

.status-active {
    background: #e6f4ea;
    color: #1e7e34;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}

.status-inactive {
    background: #fdecea;
    color: #a71d2a;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}

/* ================================
   ACTION BUTTONS
   ================================ */

.action-btns {
    display: flex;
    gap: 8px;
}

.action-btns i {
    width: 32px;
    height: 32px;
    font-size: 14px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: 0.2s ease;
}

.edit {
    background: rgba(0,123,255,0.12);
    color: var(--blue);
}

.delete {
    background: rgba(220,53,69,0.12);
    color: #dc3545;
}

.edit:hover {
    background: rgba(0,123,255,0.2);
}

.delete:hover {
    background: rgba(220,53,69,0.2);
}

/* ================================
   MOBILE
   ================================ */

@media (max-width: 768px) {
    .header-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .search-box input {
        width: 100%;
    }

    table thead {
        display: none;
    }

    table tbody tr {
        display: block;
        border: 1px solid #eaeaea;
        border-radius: 8px;
        margin-bottom: 12px;
    }

    table tbody td {
        display: flex;
        justify-content: space-between;
        padding: 10px 14px;
        border-bottom: none;
    }

    table tbody td:last-child {
        justify-content: flex-start;
    }
}

/* Override modal button styles to match your design */
.modal-footer .btn-cancel {
    background: #ccc;
    color: #333;
    border: none;
}

.modal-footer .btn-cancel:hover {
    background: #bbb;
}

.modal-footer .btn-primary {
    background: var(--blue);
    color: white;
    border: none;
}

.modal-footer .btn-primary:hover {
    background: #0056b3;
}

</style>


<div class="table-container">
    <div class="header-actions">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search Here">
        </div>
        <button class="add-btn" id="addBtn"><i class="fas fa-plus"></i> Add New Category</button>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="categoryTable">
    @forelse($categories as $index => $category)
        <tr data-id="{{ $category->id }}">
            <td>{{ $index + 1 }}</td>
            <td>{{ $category->name }}</td>
            <td>
                <span class="status-{{ $category->status ? 'active' : 'inactive' }}">
                    {{ $category->status ? 'Active' : 'Inactive' }}
                </span>
            </td>
            <td>
                <div class="action-btns">
                    <i class="fas fa-pen edit" data-id="{{ $category->id }}" title="Edit"></i>
                    <i class="fas fa-trash delete" data-id="{{ $category->id }}" title="Delete"></i>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="4" style="text-align:center; color:#888; padding:20px;">
                <i class="fas fa-box-open" style="font-size:24px; margin-bottom:10px; display:block;"></i>
                No categories found.
            </td>
        </tr>
    @endforelse
</tbody>

    </table>
</div>

<!-- Add Category Modal -->
<div id="addCategoryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Category</h3>
            <button type="button" class="close-btn" id="closeAddModal">&times;</button>
        </div>
        
        <form method="POST" action="{{ route('categories.store') }}" id="addCategoryForm">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="name">
                        Category Name <span class="required-star">*</span>
                    </label>
                    <input type="text" name="name" id="name" placeholder="Enter category name" required>
                    <div class="error-message" id="name_error"></div>
                </div>
                
                <div class="form-group">
                    <label for="status">
                        Status <span class="required-star">*</span>
                    </label>
                    <select name="status" id="status" required>
                        <option value="">-- Select Status --</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    <div class="error-message" id="status_error"></div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="cancelAdd">Cancel</button>
                <button type="submit" class="btn-primary">Add Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editCategoryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Category</h3>
            <button type="button" class="close-btn" id="closeEditModal">&times;</button>
        </div>
        
        <form method="POST" action="{{ route('categories.update') }}" id="editForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="category_id" id="edit_category_id">
            
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_name">
                        Category Name <span class="required-star">*</span>
                    </label>
                    <input type="text" name="name" id="edit_name" placeholder="Enter category name" required>
                    <div class="error-message" id="edit_name_error"></div>
                </div>
                
                <div class="form-group">
                    <label for="edit_status">
                        Status <span class="required-star">*</span>
                    </label>
                    <select name="status" id="edit_status" required>
                        <option value="">-- Select Status --</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    <div class="error-message" id="edit_status_error"></div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="cancelEdit">Cancel</button>
                <button type="submit" class="btn-primary">Update Category</button>
            </div>
        </form>
    </div>
</div>

<script>
// Modal Utility Functions (can be moved to a separate file later)
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

function validateForm(form) {
    let valid = true;
    clearAllErrors();

    const nameField = form.querySelector('[name="name"]');
    const statusField = form.querySelector('[name="status"]');

    if (!nameField || !nameField.value.trim()) {
        showError(form.id.includes('edit') ? 'edit_name_error' : 'name_error', 'Category name is required');
        valid = false;
    }

    if (!statusField || !statusField.value) {
        showError(form.id.includes('edit') ? 'edit_status_error' : 'status_error', 'Status is required');
        valid = false;
    }

    return valid;
}

document.addEventListener('DOMContentLoaded', function () {

    const modalAdd = document.getElementById('addCategoryModal');
    const modalEdit = document.getElementById('editCategoryModal');

    const addBtn = document.getElementById('addBtn');
    const cancelAdd = document.getElementById('cancelAdd');
    const cancelEdit = document.getElementById('cancelEdit');
    const closeAddModal = document.getElementById('closeAddModal');
    const closeEditModal = document.getElementById('closeEditModal');

    const searchInput = document.getElementById('searchInput');

    /* -------------------------
        OPEN ADD MODAL
    ------------------------- */
    addBtn.addEventListener('click', () => {
        openModal(modalAdd);
        // Set default values
        document.getElementById('name').value = '';
        document.getElementById('status').value = '1';
    });

    cancelAdd.addEventListener('click', () => {
        closeModal(modalAdd);
    });

    closeAddModal.addEventListener('click', () => {
        closeModal(modalAdd);
    });

    /* -------------------------
        OPEN EDIT MODAL
    ------------------------- */
    document.querySelectorAll('.edit').forEach(btn => {
        btn.addEventListener('click', e => {
            const row = e.target.closest('tr');

            document.getElementById('edit_category_id').value = row.dataset.id;
            document.getElementById('edit_name').value = row.cells[1].textContent.trim();

            const statusText = row.cells[2].textContent.trim().toLowerCase();
            document.getElementById('edit_status').value = statusText === "active" ? "1" : "0";

            openModal(modalEdit);
        });
    });

    cancelEdit.addEventListener('click', () => {
        closeModal(modalEdit);
    });

    closeEditModal.addEventListener('click', () => {
        closeModal(modalEdit);
    });

    /* -------------------------
        CLOSE MODALS (CLICK OUTSIDE & ESCAPE)
    ------------------------- */
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

    /* -------------------------
        SEARCH FILTER
    ------------------------- */
    searchInput.addEventListener('input', function () {
        const query = this.value.toLowerCase();
        const rows = document.querySelectorAll('#categoryTable tr');
        let hasVisibleRows = false;

        rows.forEach(row => {
            // Skip the "no categories" row
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
        const noResultsRow = document.querySelector('#categoryTable tr[colspan]');
        if (noResultsRow) {
            noResultsRow.style.display = hasVisibleRows ? 'none' : '';
        }
    });

    /* -------------------------
        RESET NUMBERING
    ------------------------- */
    function resetTableNumbers() {
        document.querySelectorAll('#categoryTable tr:not([colspan])').forEach((row, i) => {
            if (row.cells.length > 0) {
                row.cells[0].textContent = i + 1;
            }
        });
    }

    /* -------------------------
        FORM VALIDATION
    ------------------------- */
    document.getElementById('addCategoryForm')?.addEventListener('submit', function(e) {
        if (!validateForm(this)) {
            e.preventDefault();
        }
    });

    document.getElementById('editForm')?.addEventListener('submit', function(e) {
        if (!validateForm(this)) {
            e.preventDefault();
        }
    });

    // Clear errors on input
    document.querySelectorAll('#addCategoryForm input, #addCategoryForm select').forEach(field => {
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

    /* -------------------------
        DELETE CATEGORY (AJAX)
    ------------------------- */
    document.querySelectorAll('.delete').forEach(btn => {
        btn.addEventListener('click', e => {
            const categoryId = e.target.dataset.id;
            const categoryName = e.target.closest('tr').cells[1].textContent.trim();

            if (!confirm(`Are you sure you want to delete the category "${categoryName}"?`)) {
                return;
            }

            fetch(`{{ route('categories.destroy') }}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ category_id: categoryId })
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
                    const remainingRows = document.querySelectorAll('#categoryTable tr:not([colspan])');
                    const noResultsRow = document.querySelector('#categoryTable tr[colspan]');
                    
                    if (remainingRows.length === 0 && !noResultsRow) {
                        const tbody = document.getElementById('categoryTable');
                        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:#888;">No categories found.</td></tr>';
                    }
                } else {
                    alert("Delete failed: " + (data.message || "Unknown error"));
                }
            })
            .catch(err => {
                console.error('Delete error:', err);
                alert("Error deleting category. Please try again.");
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