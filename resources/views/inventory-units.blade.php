@extends('layouts.app')

@section('title', 'Inventory - Units')
@section('page-title', 'Units')
@section('active-inventory-units', 'active')

@section('content')
<!-- Include the reusable modal CSS -->
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">

<style>
/* --- Units Page Specific Styles --- */
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
    width: 220px;
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
    min-width: 600px;
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

/* Status badges */
.status-active {
    background: #d4edda;
    color: #155724;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
}

.status-inactive {
    background: #fff3cd;
    color: #856404;
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
    transition: opacity 0.2s;
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
    }
    
    th, td {
        padding: 10px;
    }
}
</style>

<div class="table-container">
    <div class="header-actions">
        <div class="search-box">
            <input type="text" placeholder="Search Here" id="searchInput">
        </div>
        <button class="add-btn"><i class="fas fa-plus"></i> Add New Unit</button>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Short Form</th>
                <th>Description</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        @foreach($units as $index => $unit)
        <tr class="unit-row" data-unit='@json($unit)'>
            <td>{{ $index + 1 }}</td>
            <td class="unit-name">{{ $unit->name }}</td>
            <td class="unit-short-form">{{ $unit->short_form }}</td>
            <td class="unit-description">{{ $unit->description ?? '-' }}</td>
            <td>
                <span class="status-{{ $unit->status }}">
                    {{ ucfirst($unit->status) }}
                </span>
            </td>
            <td>
                <div class="action-btns">
                    <i class="fas fa-pen edit" data-id="{{ $unit->id }}" title="Edit"></i>
                    <i class="fas fa-trash delete" data-id="{{ $unit->id }}" title="Delete"></i>
                </div>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>

<!-- Add Unit Modal -->
<div id="addUnitModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Unit</h3>
            <button type="button" class="close-btn" id="closeAddModal">&times;</button>
        </div>
        <form id="addUnitForm" method="POST" action="{{ route('units.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="unitName">
                        Name <span class="required-star">*</span>
                    </label>
                    <input type="text" id="unitName" name="name" required>
                    <div class="error-message" id="name_error"></div>
                </div>
                <div class="form-group">
                    <label for="unitShortForm">
                        Short Form
                    </label>
                    <input type="text" id="unitShortForm" name="short_form">
                    <div class="error-message" id="short_form_error"></div>
                </div>
                <div class="form-group">
                    <label for="unitDescription">
                        Description
                    </label>
                    <textarea id="unitDescription" name="description" rows="3"></textarea>
                    <div class="error-message" id="description_error"></div>
                </div>
                <div class="form-group">
                    <label for="unitStatus">
                        Status <span class="required-star">*</span>
                    </label>
                    <select id="unitStatus" name="status" required>
                        <option value="">-- Select Status --</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <div class="error-message" id="status_error"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="cancelAddBtn">Cancel</button>
                <button type="submit" class="btn-primary">Add Unit</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Unit Modal -->
<div id="editUnitModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Unit</h3>
            <button type="button" class="close-btn" id="closeEditModal">&times;</button>
        </div>
        <form id="editUnitForm" method="POST" action="{{ route('units.update') }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="editUnitId">
            <div class="modal-body">
                <div class="form-group">
                    <label for="editUnitName">
                        Name <span class="required-star">*</span>
                    </label>
                    <input type="text" id="editUnitName" name="name" required>
                    <div class="error-message" id="edit_name_error"></div>
                </div>
                <div class="form-group">
                    <label for="editUnitShortForm">
                        Short Form
                    </label>
                    <input type="text" id="editUnitShortForm" name="short_form">
                    <div class="error-message" id="edit_short_form_error"></div>
                </div>
                <div class="form-group">
                    <label for="editUnitDescription">
                        Description
                    </label>
                    <textarea id="editUnitDescription" name="description" rows="3"></textarea>
                    <div class="error-message" id="edit_description_error"></div>
                </div>
                <div class="form-group">
                    <label for="editUnitStatus">
                        Status <span class="required-star">*</span>
                    </label>
                    <select id="editUnitStatus" name="status" required>
                        <option value="">-- Select Status --</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <div class="error-message" id="edit_status_error"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="cancelEditBtn">Cancel</button>
                <button type="submit" class="btn-primary">Update Unit</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="modal">
    <div class="modal-content modal-sm">
        <div class="modal-header">
            <h3>Confirm Delete</h3>
            <button type="button" class="close-btn" id="closeDeleteModal">&times;</button>
        </div>
        <div class="modal-body text-center">
            <p>Are you sure you want to delete this unit?</p>
            <p class="mb-20"><strong id="deleteUnitName"></strong></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="cancelDeleteBtn">Cancel</button>
            <button type="button" class="btn-danger" id="confirmDeleteBtn">Delete</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Elements
    const tableBody = document.querySelector('table tbody');
    const searchInput = document.getElementById('searchInput');
    const addBtn = document.querySelector('.add-btn');
    const addModal = document.getElementById('addUnitModal');
    const editModal = document.getElementById('editUnitModal');
    const deleteModal = document.getElementById('deleteConfirmModal');
    
    // Close buttons
    const closeAddModal = document.getElementById('closeAddModal');
    const closeEditModal = document.getElementById('closeEditModal');
    const closeDeleteModal = document.getElementById('closeDeleteModal');
    
    // Cancel buttons
    const cancelAddBtn = document.getElementById('cancelAddBtn');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    
    // Forms
    const addForm = document.getElementById('addUnitForm');
    const editForm = document.getElementById('editUnitForm');
    
    // Delete modal elements
    const deleteUnitName = document.getElementById('deleteUnitName');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    
    // Variables
    let unitsData = {!! $units->toJson() !!};
    let unitToDelete = null;

    // Modal utility functions
    function openModal(modal) {
        modal.classList.add('active');
        document.body.classList.add('modal-open');
    }

    function closeModal(modal) {
        modal.classList.remove('active');
        document.body.classList.remove('modal-open');
        clearAllErrors();
    }

    // Open Add Modal
    addBtn.addEventListener('click', () => {
        openModal(addModal);
        document.getElementById('unitName').focus();
    });

    // Close Add Modal
    cancelAddBtn.addEventListener('click', () => closeModal(addModal));
    closeAddModal.addEventListener('click', () => closeModal(addModal));

    // Close Edit Modal
    cancelEditBtn.addEventListener('click', () => closeModal(editModal));
    closeEditModal.addEventListener('click', () => closeModal(editModal));

    // Close Delete Modal
    cancelDeleteBtn.addEventListener('click', () => closeModal(deleteModal));
    closeDeleteModal.addEventListener('click', () => closeModal(deleteModal));

    // Close modals when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === addModal) closeModal(addModal);
        if (e.target === editModal) closeModal(editModal);
        if (e.target === deleteModal) closeModal(deleteModal);
    });

    // Close modals with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal(addModal);
            closeModal(editModal);
            closeModal(deleteModal);
        }
    });

    // Handle Edit and Delete button clicks
    tableBody.addEventListener('click', (e) => {
        if (e.target.classList.contains('edit')) {
            const id = e.target.dataset.id;
            openEditModal(id);
        }
        
        if (e.target.classList.contains('delete')) {
            const row = e.target.closest('.unit-row');
            const id = e.target.dataset.id;
            const unit = unitsData.find(u => u.id == id);
            
            if (unit) {
                unitToDelete = { id: id, name: unit.name };
                deleteUnitName.textContent = unit.name;
                openModal(deleteModal);
            }
        }
    });

    // Search functionality
    searchInput.addEventListener('input', () => {
        const filter = searchInput.value.toLowerCase().trim();
        const rows = tableBody.querySelectorAll('.unit-row');

        rows.forEach(row => {
            const name = row.querySelector('.unit-name').textContent.toLowerCase();
            const shortForm = row.querySelector('.unit-short-form').textContent.toLowerCase();
            const description = row.querySelector('.unit-description').textContent.toLowerCase();
            const status = row.querySelector('.status-active, .status-inactive').textContent.toLowerCase();

            const combined = `${name} ${shortForm} ${description} ${status}`;
            row.style.display = combined.includes(filter) ? '' : 'none';
        });
    });

    // Add Unit Form Submission
    addForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (!validateForm(addForm, false)) {
            return;
        }

        const formData = new FormData(addForm);
        const csrfToken = document.querySelector('input[name="_token"]').value;

        try {
            const response = await fetch("{{ route('units.store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok) {
                // Add new row to table
                addNewUnitToTable(data.unit || data);
                closeModal(addModal);
                addForm.reset();
                
                // Show success message (you could add a toast notification here)
                alert('Unit added successfully!');
            } else {
                // Handle validation errors from server
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        showError(`${field}_error`, data.errors[field][0]);
                    });
                } else {
                    alert(data.message || 'Failed to add unit');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error adding unit');
        }
    });

    // Edit Unit Form Submission
    editForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!validateForm(editForm, true)) {
            return;
        }

        const id = document.getElementById('editUnitId').value;
        const formData = new FormData(editForm);
        formData.append('id', id);
        
        const csrfToken = document.querySelector('input[name="_token"]').value;

        try {
            const response = await fetch("{{ route('units.update') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok) {
                updateTableRow(data.unit || data);
                closeModal(editModal);
                alert('Unit updated successfully!');
            } else {
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        showError(`edit_${field}_error`, data.errors[field][0]);
                    });
                } else {
                    alert(data.message || 'Failed to update unit');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error updating unit');
        }
    });

    // Confirm Delete
    confirmDeleteBtn.addEventListener('click', async () => {
        if (!unitToDelete) return;

        const csrfToken = document.querySelector('input[name="_token"]').value;
        const formData = new FormData();
        formData.append('id', unitToDelete.id);
        formData.append('_method', 'DELETE');

        try {
            const response = await fetch("{{ route('units.destroy') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Remove row from table
                const deleteBtn = tableBody.querySelector(`.delete[data-id="${unitToDelete.id}"]`);
                if (deleteBtn) {
                    const row = deleteBtn.closest('tr');
                    row.remove();
                    
                    // Remove from unitsData
                    unitsData = unitsData.filter(u => u.id != unitToDelete.id);
                    
                    // Renumber rows
                    renumberRows();
                    
                    closeModal(deleteModal);
                    unitToDelete = null;
                    
                    alert('Unit deleted successfully!');
                }
            } else {
                alert(data.message || 'Failed to delete unit');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error deleting unit');
        }
    });

    // Function to open edit modal and populate data
    function openEditModal(id) {
        const unit = unitsData.find(u => u.id == id);
        
        if (!unit) {
            alert('Unit not found');
            return;
        }

        document.getElementById('editUnitId').value = unit.id;
        document.getElementById('editUnitName').value = unit.name;
        document.getElementById('editUnitShortForm').value = unit.short_form;
        document.getElementById('editUnitDescription').value = unit.description || '';
        document.getElementById('editUnitStatus').value = unit.status;

        openModal(editModal);
        document.getElementById('editUnitName').focus();
    }

    // Function to add new unit to table
    function addNewUnitToTable(unit) {
        const newRow = document.createElement('tr');
        newRow.className = 'unit-row';
        newRow.dataset.unit = JSON.stringify(unit);
        newRow.innerHTML = `
            <td>${tableBody.querySelectorAll('tr').length + 1}</td>
            <td class="unit-name">${unit.name}</td>
            <td class="unit-short-form">${unit.short_form}</td>
            <td class="unit-description">${unit.description || '-'}</td>
            <td>
                <span class="status-${unit.status}">
                    ${unit.status.charAt(0).toUpperCase() + unit.status.slice(1)}
                </span>
            </td>
            <td>
                <div class="action-btns">
                    <i class="fas fa-pen edit" data-id="${unit.id}" title="Edit"></i>
                    <i class="fas fa-trash delete" data-id="${unit.id}" title="Delete"></i>
                </div>
            </td>
        `;
        tableBody.appendChild(newRow);
        
        unitsData.push(unit);
    }

    // Function to update table row after edit
    function updateTableRow(unit) {
        const editBtn = tableBody.querySelector(`.edit[data-id="${unit.id}"]`);
        if (editBtn) {
            const row = editBtn.closest('tr');
            row.querySelector('.unit-name').textContent = unit.name;
            row.querySelector('.unit-short-form').textContent = unit.short_form;
            row.querySelector('.unit-description').textContent = unit.description || '-';
            
            // Update status
            const statusSpan = row.querySelector('span');
            statusSpan.textContent = unit.status.charAt(0).toUpperCase() + unit.status.slice(1);
            statusSpan.className = `status-${unit.status}`;
            
            // Update row data
            row.dataset.unit = JSON.stringify(unit);
            
            // Update unitsData
            const index = unitsData.findIndex(u => u.id == unit.id);
            if (index !== -1) {
                unitsData[index] = unit;
            }
        }
    }

    // Function to renumber table rows after deletion
    function renumberRows() {
        const rows = tableBody.querySelectorAll('tr');
        rows.forEach((row, index) => {
            row.querySelector('td:first-child').textContent = index + 1;
        });
    }

    // Validation Functions
    function validateForm(form, isEdit) {
        let valid = true;
        clearAllErrors();

        // Required fields
        const requiredFields = ['name', 'status'];
        requiredFields.forEach(field => {
            const input = form.querySelector(`[name="${field}"]`);
            if (input && !input.value.trim()) {
                showError(`${isEdit ? 'edit_' : ''}${field}_error`, 'This field is required');
                valid = false;
            }
        });

        return valid;
    }

    function clearAllErrors() {
        document.querySelectorAll('.error-message').forEach(error => {
            error.style.display = 'none';
            error.textContent = '';
        });
        document.querySelectorAll('input, select, textarea').forEach(field => {
            field.style.borderColor = '#ddd';
        });
    }

    function showError(fieldId, message) {
        const errorElement = document.getElementById(fieldId);
        const inputElement = document.querySelector(`[name="${fieldId.replace('_error', '')}"]`) || 
                             document.querySelector(`[name="${fieldId.replace('edit_', '').replace('_error', '')}"]`);
        
        if (errorElement && inputElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
            inputElement.style.borderColor = '#dc3545';
        }
    }

    // Clear errors on input
    document.querySelectorAll('input, select, textarea').forEach(field => {
        field.addEventListener('input', function() {
            this.style.borderColor = '#ddd';
            const formType = this.closest('form').id.includes('edit') ? 'edit_' : '';
            const fieldName = this.name;
            const errorElement = document.getElementById(formType + fieldName + '_error');
            if (errorElement) errorElement.style.display = 'none';
        });
    });
});
</script>

@endsection