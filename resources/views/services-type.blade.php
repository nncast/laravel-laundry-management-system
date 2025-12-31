@extends('layouts.app')

@section('title', 'Service Type')
@section('page-title', 'Service Type')
@section('active-services-type', 'active')

@section('content')
<!-- Include the reusable modal CSS -->
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">

<style>
/* --- Service Type Page Styles --- */
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

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    text-align: left;
    padding: 12px 15px;
    font-size: 14px;
}

th {
    background: #f8f9fa;
    font-weight: 600;
    color: var(--text-dark);
}

td {
    border-bottom: 1px solid #f1f1f1;
    color: var(--text-dark);
}

tbody tr:hover {
    background: #f9fbfd;
}

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
            <input type="text" id="searchServiceInput" placeholder="Search Here">
        </div>
        <button class="add-btn" id="addServiceBtn"><i class="fas fa-plus"></i> Add New Service Type</button>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Service Type</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="serviceTableBody">
            @forelse($serviceTypes as $index => $service)
                <tr class="service-row" data-id="{{ $service->id }}">
                    <td>{{ $index + 1 }}</td>
                    <td class="service-name">{{ $service->name }}</td>
                    <td>
                        @if($service->is_active)
                            <span class="status-active">Active</span>
                        @else
                            <span class="status-inactive">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-btns">
                            <i class="fas fa-pen edit"></i>
                            <i class="fas fa-trash delete"></i>
                        </div>
                    </td>
                </tr>
            @empty
                <tr id="noServiceFound">
                    <td colspan="4" style="text-align:center; color:#999; padding:20px;">
                        <i class="fas fa-box-open" style="font-size:24px; margin-bottom:10px; display:block;"></i>
                        No records found.
                    </td>
                </tr>
            @endforelse
        </tbody>

    </table>
</div>

<!-- Add Service Type Modal -->
<div id="addServiceModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Service Type</h3>
            <button type="button" class="close-btn" id="closeAddModal">&times;</button>
        </div>
        
        <form method="POST" action="{{ route('services.type.store') }}" id="addServiceForm">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="name">
                        Service Type Name <span class="required-star">*</span>
                    </label>
                    <input type="text" name="name" id="name" placeholder="Enter service type name" required>
                    <div class="error-message" id="name_error"></div>
                </div>
                
                <div class="form-group">
                    <label for="is_active">
                        Status <span class="required-star">*</span>
                    </label>
                    <select name="is_active" id="is_active" required>
                        <option value="">-- Select Status --</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    <div class="error-message" id="is_active_error"></div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="cancelAdd">Cancel</button>
                <button type="submit" class="btn-primary">Add Service Type</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Service Type Modal -->
<div id="editServiceModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Service Type</h3>
            <button type="button" class="close-btn" id="closeEditModal">&times;</button>
        </div>
        
        <form method="POST" id="editServiceForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="edit_service_id">
            
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_service_name">
                        Service Type Name <span class="required-star">*</span>
                    </label>
                    <input type="text" name="name" id="edit_service_name" placeholder="Enter service type name" required>
                    <div class="error-message" id="edit_name_error"></div>
                </div>
                
                <div class="form-group">
                    <label for="edit_service_status">
                        Status <span class="required-star">*</span>
                    </label>
                    <select name="is_active" id="edit_service_status" required>
                        <option value="">-- Select Status --</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    <div class="error-message" id="edit_is_active_error"></div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="cancelEdit">Cancel</button>
                <button type="submit" class="btn-primary">Update Service Type</button>
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

function validateServiceForm(form, isEdit = false) {
    let valid = true;
    clearAllErrors();

    const nameField = form.querySelector('[name="name"]');
    const statusField = form.querySelector('[name="is_active"]');

    if (!nameField || !nameField.value.trim()) {
        showError(isEdit ? 'edit_name_error' : 'name_error', 'Service type name is required');
        valid = false;
    }

    if (!statusField || !statusField.value) {
        showError(isEdit ? 'edit_is_active_error' : 'is_active_error', 'Status is required');
        valid = false;
    }

    return valid;
}

document.addEventListener("DOMContentLoaded", () => {
    const addModal = document.getElementById("addServiceModal");
    const editModal = document.getElementById("editServiceModal");
    const addBtn = document.getElementById("addServiceBtn");
    const cancelAddBtn = document.getElementById("cancelAdd");
    const cancelEditBtn = document.getElementById("cancelEdit");
    const closeAddModal = document.getElementById("closeAddModal");
    const closeEditModal = document.getElementById("closeEditModal");
    const editForm = document.getElementById("editServiceForm");
    const searchInput = document.getElementById("searchServiceInput");
    const addForm = document.getElementById("addServiceForm");

    // Open Add Modal
    addBtn.addEventListener('click', () => { 
        openModal(addModal);
        // Reset form and set defaults
        addForm.reset();
        document.getElementById('is_active').value = '1';
    });
    
    cancelAddBtn.addEventListener('click', () => closeModal(addModal));
    closeAddModal.addEventListener('click', () => closeModal(addModal));

    cancelEditBtn.addEventListener('click', () => closeModal(editModal));
    closeEditModal.addEventListener('click', () => closeModal(editModal));

    // Open Edit Modal
    document.querySelectorAll('.edit').forEach(btn => {
        btn.addEventListener('click', e => {
            const row = e.target.closest('.service-row');
            const id = row.dataset.id;
            const name = row.querySelector('.service-name').innerText;
            const isActive = row.querySelector('td:nth-child(3) span').classList.contains('status-active') ? '1' : '0';

            // Set form action
            editForm.action = "{{ route('services.type.update') }}";
            document.getElementById('edit_service_id').value = id;
            document.getElementById('edit_service_name').value = name;
            document.getElementById('edit_service_status').value = isActive;
            
            openModal(editModal);
        });
    });

    // Close modals on outside click and escape key
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

    // Delete Service Type
    document.querySelectorAll('.delete').forEach(btn => {
        btn.addEventListener('click', e => {
            const row = e.target.closest('.service-row');
            const id = row.dataset.id;
            const serviceName = row.querySelector('.service-name').innerText.trim();
            
            if(!confirm(`Are you sure you want to delete the service type "${serviceName}"?`)){
                return;
            }
            
            // Create a form to submit the DELETE request
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('services.type.destroy') }}";
            form.style.display = 'none';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            
            const idField = document.createElement('input');
            idField.type = 'hidden';
            idField.name = 'id';
            idField.value = id;
            
            form.appendChild(csrfToken);
            form.appendChild(methodField);
            form.appendChild(idField);
            document.body.appendChild(form);
            form.submit();
        });
    });

    // Search
    searchInput.addEventListener('input', () => {
        const query = searchInput.value.toLowerCase();
        const rows = document.querySelectorAll('.service-row');
        let hasVisibleRows = false;

        rows.forEach(row => {
            const name = row.querySelector('.service-name').innerText.toLowerCase();
            if (name.includes(query)) {
                row.style.display = '';
                hasVisibleRows = true;
            } else {
                row.style.display = 'none';
            }
        });

        // Show/hide the "no results" message
        const noResultsRow = document.getElementById('noServiceFound');
        if (noResultsRow) {
            noResultsRow.style.display = hasVisibleRows ? 'none' : '';
        }
    });

    // Form validation
    addForm.addEventListener('submit', function(e) {
        if (!validateServiceForm(this, false)) {
            e.preventDefault();
        }
    });

    editForm.addEventListener('submit', function(e) {
        if (!validateServiceForm(this, true)) {
            e.preventDefault();
        }
    });

    // Clear errors on input
    addForm.querySelectorAll('input, select').forEach(field => {
        field.addEventListener('input', function() {
            this.style.borderColor = '#ddd';
            const errorElement = document.getElementById(this.name + '_error');
            if (errorElement) errorElement.style.display = 'none';
        });
    });

    editForm.querySelectorAll('input, select').forEach(field => {
        field.addEventListener('input', function() {
            this.style.borderColor = '#ddd';
            const errorElement = document.getElementById('edit_' + this.name + '_error');
            if (errorElement) errorElement.style.display = 'none';
        });
    });

    // Reset table numbers (if needed after AJAX operations)
    function resetTableNumbers() {
        document.querySelectorAll('.service-row').forEach((row, i) => {
            if (row.cells.length > 0) {
                row.cells[0].textContent = i + 1;
            }
        });
    }

    // Auto-focus on modal open
    addBtn.addEventListener('click', () => {
        setTimeout(() => {
            document.getElementById('name')?.focus();
        }, 300);
    });
});
</script>
@endsection