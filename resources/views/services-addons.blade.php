@extends('layouts.app')

@section('title', 'Addons')
@section('page-title', 'Addons')
@section('active-services-addons', 'active')

@section('content')
<!-- Include reusable modal CSS -->
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">

<style>
:root {
    --blue: #007bff;
    --text-dark: #2c3e50;
    --text-light: #6c757d;
    --success-bg: #d4edda;
    --success-text: #155724;
    --danger-bg: #f8d7da;
    --danger-text: #721c24;
    --table-border: #f1f1f1;
    --shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    --transition: all 0.3s ease;
}

/* --- Addons Page Styles --- */
.addons-section {
    background: #fff;
    border-radius: 10px;
    box-shadow: var(--shadow);
    padding: 25px;
    transition: var(--transition);
    width: 100%;
    overflow: visible;
}

.addons-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.addons-header .search-box {
    position: relative;
    display: flex;
    align-items: center;
}

.addons-header .search-box input {
    padding: 10px 15px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-family: 'Poppins', sans-serif;
    width: 220px;
    transition: var(--transition);
}

.addons-header .search-box input:focus {
    outline: none;
    border-color: var(--blue);
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.addons-header .add-btn {
    background: var(--blue);
    color: white;
    border: none;
    border-radius: 6px;
    padding: 10px 18px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}

.addons-header .add-btn:hover {
    opacity: 0.85;
    background: #0056b3;
    transform: translateY(-1px);
}

.addons-header .add-btn i {
    font-size: 12px;
}

/* Table Styles with Responsive Fix */
.table-wrapper {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border-radius: 8px;
}

.addons-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
    min-width: 600px; /* Minimum width for small screens */
}

.addons-table th,
.addons-table td {
    text-align: left;
    padding: 12px 15px;
    white-space: nowrap;
}

.addons-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: var(--text-dark);
    border-bottom: 1px solid #dee2e6;
    position: sticky;
    top: 0;
}

.addons-table td {
    border-bottom: 1px solid #f1f1f1;
    color: var(--text-dark);
}

.status {
    font-weight: 500;
    padding: 5px 12px;
    border-radius: 6px;
    font-size: 13px;
    text-align: center;
    display: inline-block;
}

.status-active {
    background: var(--success-bg);
    color: var(--success-text);
}

.status-inactive {
    background: var(--danger-bg);
    color: var(--danger-text);
}

.action-btn {
    display: flex;
    gap: 10px;
}

.edit-btn, .delete-btn {
    font-size: 14px;
    padding: 8px;
    border-radius: 50%;
    cursor: pointer;
    transition: var(--transition);
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
}

.edit-btn {
    background: rgba(0, 123, 255, 0.1);
    color: var(--blue);
}

.delete-btn {
    background: rgba(255, 0, 0, 0.1);
    color: #dc3545;
}

.edit-btn:hover, .delete-btn:hover {
    opacity: 0.8;
    transform: translateY(-1px);
}

/* Responsive Design */
@media (max-width: 1200px) {
    .addons-header .search-box input {
        width: 200px;
    }
}

@media (max-width: 992px) {
    .addons-section {
        padding: 20px;
    }
}

/* MOBILE STYLES (768px and below) */
@media (max-width: 768px) {
    .addons-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .addons-header .search-box {
        width: 100%;
    }
    
    .addons-header .search-box input {
        width: 100%;
    }
    
    .addons-header .add-btn {
        width: 100%;
        justify-content: center;
    }
    
    .addons-section {
        padding: 20px;
        overflow: visible;
    }
    
    .table-wrapper {
        border: 1px solid #f0f0f0;
        border-radius: 8px;
        background: white;
    }
    
    .addons-table {
        min-width: 600px;
        margin: 0;
    }
}

@media (max-width: 576px) {
    .addons-section {
        padding: 15px;
    }
    
    .addons-table {
        min-width: 550px;
        font-size: 13px;
    }
    
    .addons-table th, 
    .addons-table td {
        padding: 10px 12px;
    }
    
    .action-btn {
        gap: 8px;
    }
    
    .edit-btn, .delete-btn {
        padding: 6px;
        font-size: 13px;
        width: 32px;
        height: 32px;
    }
}

@media (max-width: 400px) {
    .addons-section {
        padding: 10px;
    }
    
    .addons-table {
        min-width: 500px;
    }
    
    .addons-header .add-btn {
        padding: 8px 15px;
        font-size: 13px;
    }
}

/* Modal Responsive Styles */
@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        margin: 10px;
        max-height: 90vh;
        overflow-y: auto;
    }
    
    .modal-body .form-group {
        margin-bottom: 15px;
    }
    
    .modal-footer {
        flex-direction: column;
        gap: 10px;
    }
    
    .modal-footer button {
        width: 100%;
    }
}
</style>

<div class="addons-section">
    <div class="addons-header">
        <div class="search-box">
            <input type="text" id="addonSearch" placeholder="Search Here">
        </div>
        <button class="add-btn" id="addAddonBtn">
            <i class="fas fa-plus"></i> Add New Addon
        </button>
    </div>

    <div class="table-wrapper">
        <table class="addons-table" id="addonsTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Addon</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($addons as $index => $addon)
                <tr data-id="{{ $addon->id }}">
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $addon->name }}</td>
                    <td class="price-cell">₱{{ number_format($addon->price, 2) }}</td>
                    <td>
                        <span class="status {{ $addon->is_active ? 'status-active' : 'status-inactive' }}">
                            {{ $addon->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="action-btn">
                        <button class="edit-btn" 
                            data-id="{{ $addon->id }}" 
                            data-name="{{ $addon->name }}" 
                            data-price="{{ $addon->price }}" 
                            data-active="{{ $addon->is_active }}">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button class="delete-btn" 
                            data-id="{{ $addon->id }}" 
                            data-name="{{ $addon->name }}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;color:#999;padding:20px;">
                        <i class="fas fa-box-open" style="font-size:24px;margin-bottom:10px;display:block;"></i>
                        No addons found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="addonModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="addonModalTitle">Add Addon</h3>
            <button type="button" class="close-btn" id="closeAddonModal">&times;</button>
        </div>

        <form id="addonForm">
            @csrf
            <input type="hidden" name="id" id="addonId">
            <div class="modal-body">
                <div class="form-group">
                    <label for="addonName">Addon Name <span style="color:red">*</span></label>
                    <input type="text" id="addonName" name="name" placeholder="Enter addon name" required>
                    <div class="error-message" id="addonName_error"></div>
                </div>
                <div class="form-group">
                    <label for="addonPrice">Price <span style="color:red">*</span></label>
                    <input type="number" id="addonPrice" name="price" placeholder="0.00" step="0.01" min="0" required>
                    <div class="error-message" id="addonPrice_error"></div>
                </div>
                <div class="form-group">
                    <label for="addonStatus">Status <span style="color:red">*</span></label>
                    <select id="addonStatus" name="is_active" required>
                        <option value="">-- Select Status --</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    <div class="error-message" id="addonStatus_error"></div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="cancelAddon">Cancel</button>
                <button type="submit" class="btn-primary" id="saveAddonBtn">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
// Modal functions
const addonModal = document.getElementById('addonModal');
const addonForm = document.getElementById('addonForm');
const addonModalTitle = document.getElementById('addonModalTitle');
let currentAddonId = null;

function openAddonModal(edit=false, data=null){
    addonForm.reset();
    clearErrors();
    if(edit && data){
        addonModalTitle.textContent = 'Edit Addon';
        currentAddonId = data.id;
        document.getElementById('addonId').value = data.id;
        document.getElementById('addonName').value = data.name;
        document.getElementById('addonPrice').value = data.price;
        document.getElementById('addonStatus').value = data.is_active ? '1':'0';
    } else {
        addonModalTitle.textContent = 'Add Addon';
        currentAddonId = null;
    }
    addonModal.classList.add('active');
    document.body.classList.add('modal-open');
}

function closeAddonModal(){
    addonModal.classList.remove('active');
    document.body.classList.remove('modal-open');
    clearErrors();
}

function clearErrors(){
    document.querySelectorAll('.error-message').forEach(el=>{
        el.style.display='none';
        el.textContent='';
    });
}

// Event Listeners
document.getElementById('addAddonBtn').addEventListener('click',()=>openAddonModal());

document.getElementById('closeAddonModal').addEventListener('click',closeAddonModal);
document.getElementById('cancelAddon').addEventListener('click',closeAddonModal);

window.addEventListener('click', e=>{
    if(e.target === addonModal) closeAddonModal();
});

document.addEventListener('keydown', e=>{
    if(e.key==='Escape') closeAddonModal();
});

// Edit button
document.querySelectorAll('.edit-btn').forEach(btn=>{
    btn.addEventListener('click',()=>{
        const data = {
            id: btn.dataset.id,
            name: btn.dataset.name,
            price: btn.dataset.price,
            is_active: btn.dataset.active==='1'
        };
        openAddonModal(true,data);
    });
});

// Delete button
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        const name = btn.dataset.name;

        // Custom confirmation popup
        if(!confirm(`Are you sure you want to delete the addon "${name}"? This action cannot be undone.`)) 
            return;

        // Send DELETE request
        const formData = new FormData();
        formData.append('_method', 'DELETE'); // Laravel requires this
        formData.append('id', id);

        fetch(`/services/addons/${id}`, {
            method: 'POST', // Laravel reads _method
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                alert(`Addon "${name}" deleted successfully.`);
                location.reload(); // Refresh table
            } else {
                alert(data.message || 'Failed to delete addon');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Server error. Could not delete addon.');
        });
    });
});


// Add/Edit Form submit
addonForm.addEventListener('submit', function(e){
    e.preventDefault();
    clearErrors();
    const formData = new FormData(addonForm);
    let url = '/services/addons';
    let method = 'POST';
    
    if(currentAddonId){
        url += `/${currentAddonId}`;
        formData.append('_method','PUT');
    }

    fetch(url,{
        method:'POST',
        headers:{ 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
        body: formData
    })
    .then(res=>res.json())
    .then(data=>{
        if(data.success){
            closeAddonModal();
            location.reload();
        } else {
            // Display validation errors if any
            if(data.errors){
                Object.keys(data.errors).forEach(key=>{
                    const errorEl = document.getElementById(`${key}_error`);
                    if(errorEl){
                        errorEl.textContent = data.errors[key][0];
                        errorEl.style.display = 'block';
                    }
                });
            } else {
                alert(data.message || 'Failed to save addon');
            }
        }
    })
    .catch(err=>{
        console.error(err);
        alert('Server error');
    });
});

// Search functionality
document.getElementById('addonSearch').addEventListener('input', function(){
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#addonsTable tbody tr');
    
    rows.forEach(row=>{
        if(row.cells.length > 1){
            const text = row.cells[1].textContent.toLowerCase(); // Search in Addon column
            row.style.display = text.includes(filter) ? '' : 'none';
        }
    });
});

// Responsive table adjustment
function adjustTableForMobile(){
    if(window.innerWidth <= 768){
        // Add mobile-friendly styles if needed
        document.querySelectorAll('.action-btn').forEach(btn=>{
            btn.style.flexWrap = 'nowrap';
        });
    }
}

// Call on load and resize
window.addEventListener('load', adjustTableForMobile);
window.addEventListener('resize', adjustTableForMobile);
</script>
@endsection