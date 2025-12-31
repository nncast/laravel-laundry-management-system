@extends('layouts.app')

@section('title', 'Services - Service Type')
@section('page-title', 'Service Type')
@section('active-services-type', 'active')

@section('content')
<!-- Include the reusable modal CSS -->
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">

<style>
/* --- Service Type Page Styles --- */
.table-container { 
    background:#fff; 
    border-radius:10px; 
    box-shadow:0 2px 6px rgba(0,0,0,0.05); 
    padding:25px; 
    overflow-x: auto;
}

.header-actions { 
    display:flex; 
    justify-content:space-between; 
    align-items:center; 
    margin-bottom:20px; 
    flex-wrap: wrap;
    gap: 15px;
}

.search-box input { 
    padding:10px 15px; 
    border-radius:6px; 
    border:1px solid #ccc; 
    width: 250px;
    min-width: 200px;
    font-family: 'Poppins', sans-serif;
}

.add-btn { 
    background:var(--blue); 
    color:white; 
    border:none; 
    border-radius:6px; 
    padding:10px 18px; 
    font-size:14px; 
    cursor:pointer; 
    transition:.2s; 
    white-space: nowrap;
}

.add-btn i { margin-right:6px; }
.add-btn:hover { opacity:.85; background:#0056b3; }

table { width:100%; border-collapse:collapse; }
th, td { text-align:left; padding:12px 15px; font-size:14px; }
th { background:#f8f9fa; font-weight:600; color:#2c3e50; }
td { border-bottom:1px solid #f1f1f1; color:#2c3e50; }

tbody tr:hover {
    background: #f9fbfd;
}

.service-icon {
    vertical-align: middle;
    margin-right: 10px;
    border-radius: 4px;
    object-fit: cover;
}

.type-tag { 
    display:inline-block; 
    background:rgba(0,123,255,0.1); 
    color:var(--blue); 
    font-size:12px; 
    padding:2px 8px; 
    border-radius:12px; 
    margin-right:4px; 
    margin-bottom:2px; 
}

.status-active { 
    background:#d4edda; 
    color:#155724; 
    padding:5px 12px; 
    border-radius:20px; 
    font-size:13px; 
    font-weight:500; 
}

.status-inactive { 
    background:#f8d7da; 
    color:#721c24; 
    padding:5px 12px; 
    border-radius:20px; 
    font-size:13px; 
    font-weight:500; 
}

.action-btns { display:flex; gap:10px; }
.action-btns i { 
    font-size:14px; 
    padding:8px; 
    border-radius:50%; 
    cursor:pointer; 
}

.edit { background: rgba(0,123,255,0.1); color:var(--blue); }
.delete { background: rgba(255,0,0,0.1); color:red; }
.edit:hover, .delete:hover { opacity:.8; }

/* File upload preview */
.file-upload-preview {
    margin-top: 10px;
    text-align: center;
}

.file-upload-preview img {
    max-width: 100px;
    max-height: 100px;
    border-radius: 6px;
    margin-bottom: 10px;
}

.file-upload-label {
    display: block;
    margin-bottom: 5px;
    font-size: 13px;
    color: #6c757d;
}

/* Price formatting */
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
        <button class="add-btn" id="addServiceBtn"><i class="fas fa-plus"></i> Add New Service</button>
    </div>

    <table id="servicesTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Service Name</th>
                <th>Service Type</th>
                <th>Price</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($services as $index => $service)
                <tr data-service-id="{{ $service->id }}">
                    <td>{{ $index + 1 }}</td>
                    <td>
                        @if($service->icon_url)
                            <img src="{{ $service->icon_url }}" width="32" height="32" class="service-icon">
                        @endif
                        {{ $service->name }}
                    </td>
                    <td><span class="type-tag">{{ $service->serviceType->name }}</span></td>
                    <td class="price-cell">₱{{ number_format($service->price, 2) }}</td>
                    <td>
                        @if($service->is_active)
                            <span class="status-active">Active</span>
                        @else
                            <span class="status-inactive">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-btns">
                            <i class="fas fa-pen edit" onclick="openEditModal({{ $service->id }})"></i>
                            <i class="fas fa-trash delete" onclick="deleteService({{ $service->id }})"></i>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;color:#999;padding:20px;">
                        <i class="fas fa-box-open" style="font-size:24px;margin-bottom:10px;display:block;"></i>
                        No services found.
                    </td>
                </tr>
            @endforelse
        </tbody>

    </table>
</div>

<!-- Add Modal -->
<div id="addServiceModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Service</h3>
            <button type="button" class="close-btn" id="closeAddModal">&times;</button>
        </div>
        
        <form id="addServiceForm" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="name">
                        Service Name <span class="required-star">*</span>
                    </label>
                    <input type="text" name="name" id="name" placeholder="Enter service name" required>
                    <div class="error-message" id="name_error"></div>
                </div>
                
                <div class="form-group">
                    <label for="icon_file" class="file-upload-label">
                        Service Icon
                    </label>
                    <input type="file" name="icon_file" id="icon_file" accept="image/*">
                    <div class="helper-text">Optional. JPG, PNG or GIF. Max 2MB.</div>
                    <div class="error-message" id="icon_file_error"></div>
                </div>
                
                <div class="form-group">
                    <label for="service_type_id">
                        Service Type <span class="required-star">*</span>
                    </label>
                    <select name="service_type_id" id="service_type_id" required>
                        <option value="">-- Select Service Type --</option>
                        @foreach($serviceTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                    <div class="error-message" id="service_type_id_error"></div>
                </div>
                
                <div class="form-group">
                    <label for="price">
                        Price <span class="required-star">*</span>
                    </label>
                    <input type="number" name="price" id="price" placeholder="0.00" step="0.01" min="0" required>
                    <div class="error-message" id="price_error"></div>
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
                <button type="submit" class="btn-primary">Save Service</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editServiceModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Service</h3>
            <button type="button" class="close-btn" id="closeEditModal">&times;</button>
        </div>
        
        <form id="editServiceForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="editServiceId">
            
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_name">
                        Service Name <span class="required-star">*</span>
                    </label>
                    <input type="text" name="name" id="edit_name" placeholder="Enter service name" required>
                    <div class="error-message" id="edit_name_error"></div>
                </div>
                
                <div class="form-group">
                    <label for="edit_icon_file" class="file-upload-label">
                        Service Icon
                    </label>
                    <input type="file" name="icon_file" id="edit_icon_file" accept="image/*">
                    <div class="helper-text">Leave empty to keep current icon. JPG, PNG or GIF. Max 2MB.</div>
                    <div id="currentIconPreview" class="file-upload-preview"></div>
                    <div class="error-message" id="edit_icon_file_error"></div>
                </div>
                
                <div class="form-group">
                    <label for="edit_service_type_id">
                        Service Type <span class="required-star">*</span>
                    </label>
                    <select name="service_type_id" id="edit_service_type_id" required>
                        @foreach($serviceTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                    <div class="error-message" id="edit_service_type_id_error"></div>
                </div>
                
                <div class="form-group">
                    <label for="edit_price">
                        Price <span class="required-star">*</span>
                    </label>
                    <input type="number" name="price" id="edit_price" placeholder="0.00" step="0.01" min="0" required>
                    <div class="error-message" id="edit_price_error"></div>
                </div>
                
                <div class="form-group">
                    <label for="edit_is_active">
                        Status <span class="required-star">*</span>
                    </label>
                    <select name="is_active" id="edit_is_active" required>
                        <option value="">-- Select Status --</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    <div class="error-message" id="edit_is_active_error"></div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="cancelEdit">Cancel</button>
                <button type="submit" class="btn-primary">Update Service</button>
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

// Services data from PHP
const servicesData = @json($services->keyBy('id'));

// DOM Elements
const addModal = document.getElementById('addServiceModal');
const editModal = document.getElementById('editServiceModal');
const addBtn = document.getElementById('addServiceBtn');
const cancelAddBtn = document.getElementById('cancelAdd');
const cancelEditBtn = document.getElementById('cancelEdit');
const closeAddModal = document.getElementById('closeAddModal');
const closeEditModal = document.getElementById('closeEditModal');
const searchInput = document.getElementById('searchInput');

// Initialize event listeners when DOM loads
document.addEventListener('DOMContentLoaded', function() {
    // Open Add Modal
    addBtn.addEventListener('click', function() {
        openModal(addModal);
        document.getElementById('addServiceForm').reset();
        document.getElementById('is_active').value = '1'; // Set default to Active
        
        // Auto-focus on name field
        setTimeout(() => {
            document.getElementById('name')?.focus();
        }, 300);
    });
    
    // Close Add Modal
    cancelAddBtn.addEventListener('click', () => closeModal(addModal));
    closeAddModal.addEventListener('click', () => closeModal(addModal));
    
    // Close Edit Modal
    cancelEditBtn.addEventListener('click', () => closeModal(editModal));
    closeEditModal.addEventListener('click', () => closeModal(editModal));
    
    // Close modals on outside click
    window.addEventListener('click', e => {
        if (e.target === addModal) closeModal(addModal);
        if (e.target === editModal) closeModal(editModal);
    });
    
    // Close modals on escape key
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeModal(addModal);
            closeModal(editModal);
        }
    });
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('#servicesTable tbody tr');
        let hasVisibleRows = false;
        
        rows.forEach(row => {
            const rowText = row.innerText.toLowerCase();
            if (rowText.includes(filter)) {
                row.style.display = '';
                hasVisibleRows = true;
            } else {
                row.style.display = 'none';
            }
        });
    });
    
    // Add Service Form Submission
    document.getElementById('addServiceForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Basic validation
        let valid = true;
        clearAllErrors();
        
        const name = document.getElementById('name').value.trim();
        const serviceTypeId = document.getElementById('service_type_id').value;
        const price = document.getElementById('price').value;
        const status = document.getElementById('is_active').value;
        
        if (!name) {
            showError('name_error', 'Service name is required');
            valid = false;
        }
        
        if (!serviceTypeId) {
            showError('service_type_id_error', 'Service type is required');
            valid = false;
        }
        
        if (!price || parseFloat(price) < 0) {
            showError('price_error', 'Valid price is required');
            valid = false;
        }
        
        if (!status) {
            showError('is_active_error', 'Status is required');
            valid = false;
        }
        
        if (!valid) return;
        
        // File validation (optional)
        const iconFile = document.getElementById('icon_file').files[0];
        if (iconFile) {
            const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
            const maxSize = 2 * 1024 * 1024; // 2MB
            
            if (!validTypes.includes(iconFile.type)) {
                showError('icon_file_error', 'File must be JPG, PNG or GIF');
                return;
            }
            
            if (iconFile.size > maxSize) {
                showError('icon_file_error', 'File size must be less than 2MB');
                return;
            }
        }
        
        let formData = new FormData(this);
        
        fetch('/services/list', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        })
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(data => {
            if (data.success) {
                closeModal(addModal);
                location.reload();
            } else {
                alert(data.message || 'Failed to add service');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Server error, please try again');
        });
    });
    
    // Edit Service Form Submission
    document.getElementById('editServiceForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Basic validation
        let valid = true;
        clearAllErrors();
        
        const name = document.getElementById('edit_name').value.trim();
        const serviceTypeId = document.getElementById('edit_service_type_id').value;
        const price = document.getElementById('edit_price').value;
        const status = document.getElementById('edit_is_active').value;
        
        if (!name) {
            showError('edit_name_error', 'Service name is required');
            valid = false;
        }
        
        if (!serviceTypeId) {
            showError('edit_service_type_id_error', 'Service type is required');
            valid = false;
        }
        
        if (!price || parseFloat(price) < 0) {
            showError('edit_price_error', 'Valid price is required');
            valid = false;
        }
        
        if (!status) {
            showError('edit_is_active_error', 'Status is required');
            valid = false;
        }
        
        if (!valid) return;
        
        // File validation (optional)
        const iconFile = document.getElementById('edit_icon_file').files[0];
        if (iconFile) {
            const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
            const maxSize = 2 * 1024 * 1024; // 2MB
            
            if (!validTypes.includes(iconFile.type)) {
                showError('edit_icon_file_error', 'File must be JPG, PNG or GIF');
                return;
            }
            
            if (iconFile.size > maxSize) {
                showError('edit_icon_file_error', 'File size must be less than 2MB');
                return;
            }
        }
        
        let id = document.getElementById('editServiceId').value;
        let formData = new FormData(this);
        formData.append('_method', 'PUT');
        
        fetch(`/services/list/${id}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        })
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(data => {
            if (data.success) {
                closeModal(editModal);
                location.reload();
            } else {
                alert(data.message || 'Failed to update service');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Server error, please try again');
        });
    });
    
    // Clear errors on input
    document.querySelectorAll('#addServiceForm input, #addServiceForm select').forEach(field => {
        field.addEventListener('input', function() {
            this.style.borderColor = '#ddd';
            const errorElement = document.getElementById(this.name + '_error');
            if (errorElement) errorElement.style.display = 'none';
        });
    });
    
    document.querySelectorAll('#editServiceForm input, #editServiceForm select').forEach(field => {
        field.addEventListener('input', function() {
            this.style.borderColor = '#ddd';
            const errorElement = document.getElementById('edit_' + this.name + '_error');
            if (errorElement) errorElement.style.display = 'none';
        });
    });
});

// Open Edit Modal Function
function openEditModal(id) {
    if (!servicesData[id]) {
        alert('Service data not found');
        return;
    }
    
    const service = servicesData[id];
    
    document.getElementById('editServiceId').value = service.id;
    document.getElementById('edit_name').value = service.name;
    document.getElementById('edit_service_type_id').value = service.service_type_id;
    document.getElementById('edit_price').value = service.price;
    document.getElementById('edit_is_active').value = service.is_active ? '1' : '0'; // Set dropdown value
    
    // Show current icon preview if exists
    const previewContainer = document.getElementById('currentIconPreview');
    if (service.icon_url) {
        previewContainer.innerHTML = `
            <div class="helper-text">Current Icon:</div>
            <img src="${service.icon_url}" alt="Current Icon" style="max-width: 80px; border-radius: 4px;">
        `;
    } else {
        previewContainer.innerHTML = '<div class="helper-text">No current icon</div>';
    }
    
    openModal(editModal);
    
    // Auto-focus on name field
    setTimeout(() => {
        document.getElementById('edit_name')?.focus();
    }, 300);
}

// Delete Service Function
function deleteService(id) {
    const service = servicesData[id];
    if (!service) return;
    
    if (!confirm(`Are you sure you want to delete the service "${service.name}"?`)) {
        return;
    }
    
    fetch(`/services/list/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: id })
    })
    .then(res => {
        if (!res.ok) throw new Error('Network response was not ok');
        return res.json();
    })
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to delete service');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete service');
    });
}
</script>
@endsection