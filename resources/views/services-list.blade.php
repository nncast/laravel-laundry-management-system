@extends('layouts.app')

@section('title', 'Services - Service Type')
@section('page-title', 'Service Type')
@section('active-services-type', 'active')

@section('content')
<style>
/* --- Service Type Page Styles --- */
.table-container {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    padding: 25px;
}

.header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.search-box input {
    padding: 10px 15px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-family: 'Poppins', sans-serif;
    width: 220px;
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

/* Type tag badge */
.type-tag {
    display: inline-block;
    background: rgba(0,123,255,0.1);
    color: var(--blue);
    font-size: 12px;
    padding: 2px 8px;
    border-radius: 12px;
    margin-right: 4px;
    margin-bottom: 2px;
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

/* --- Modal common styles --- */
/* --- Modal common styles --- */
.modal {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.4);
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    max-width: 500px;
    width: 100%;
}

.modal-content h3 {
    margin-bottom: 20px;
    font-size: 20px;
    font-weight: 600;
    color: #2c3e50;
}

.modal-content input[type="text"],
.modal-content select,
.modal-content input[type="file"] {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: none; /* removed border */
    margin-bottom: 15px;
    background: #f8f9fa; /* subtle background for inputs */
    font-size: 14px;
    box-sizing: border-box;
}

.modal-content label.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px; /* space between checkbox and label text */
    font-size: 14px;
    margin-bottom: 15px;
    cursor: pointer;
}

.modal-content label.checkbox-label input[type="checkbox"] {
    margin: 0; /* remove default spacing */
}

.modal-content button {
    padding:10px 18px;
    border:none;
    border-radius:6px;
    cursor:pointer;
}
.modal-content .cancel-btn { background:#f1f1f1; color:#333; }
.modal-content .save-btn { background:#007bff; color:#fff; }

</style>

<div class="table-container">
    <div class="header-actions">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search Here">
        </div>
        <button class="add-btn" onclick="openAddModal()"><i class="fas fa-plus"></i> Add New Service</button>
    </div>

    <table id="servicesTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Service Name</th>
                <th>Service Type</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($services as $index => $service)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    <img src="{{ $service->icon_url }}" 
                         width="32" height="32" 
                         style="vertical-align:middle;margin-right:10px;border-radius:4px;">
                    {{ $service->name }}
                </td>
                <td>
                    <span class="type-tag">{{ $service->serviceType->name }}</span>
                </td>
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
            @endforeach
        </tbody>
    </table>
</div>

<!-- Add Service Modal -->
<div id="addServiceModal" class="modal">
    <form id="addServiceForm" class="modal-content" enctype="multipart/form-data">
        @csrf
        <h3>Add New Service</h3>
        <input type="text" name="name" placeholder="Service Name" required>
        <input type="file" name="icon_file" accept="image/*">
        <select name="service_type_id" required>
            @foreach($serviceTypes as $type)
                <option value="{{ $type->id }}">{{ $type->name }}</option>
            @endforeach
        </select>
        <label class="checkbox-label">
    <input type="checkbox" name="is_active" checked> Active
</label>

        <div style="display:flex; justify-content:flex-end; gap:10px;">
            <button type="button" class="cancel-btn" onclick="closeAddModal()">Cancel</button>
            <button type="submit" class="save-btn">Save</button>
        </div>
    </form>
</div>

<!-- Edit Service Modal -->
<div id="editServiceModal" class="modal">
    <form id="editServiceForm" class="modal-content" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <h3>Edit Service</h3>
        <input type="hidden" name="id" id="editServiceId">
        <input type="text" name="name" id="editServiceName" placeholder="Service Name" required>
        <input type="file" name="icon_file" id="editServiceIcon" accept="image/*">
        <select name="service_type_id" id="editServiceType" required>
            @foreach($serviceTypes as $type)
                <option value="{{ $type->id }}">{{ $type->name }}</option>
            @endforeach
        </select>
        <label class="checkbox-label">
    <input type="checkbox" name="is_active" id="editServiceActive"> Active
</label>

        <div style="display:flex; justify-content:flex-end; gap:10px;">
            <button type="button" class="cancel-btn" onclick="closeEditModal()">Cancel</button>
            <button type="submit" class="save-btn">Update</button>
        </div>
    </form>
</div>

<script>
// -------------------- Add Modal --------------------
function openAddModal() {
    document.getElementById('addServiceForm').reset();
    document.getElementById('addServiceModal').style.display = 'flex';
}
function closeAddModal() { document.getElementById('addServiceModal').style.display = 'none'; }

// -------------------- Edit Modal --------------------
function openEditModal(id) {
    let services = @json($services->keyBy('id'));
    let s = services[id];

    document.getElementById('editServiceId').value = s.id;
    document.getElementById('editServiceName').value = s.name;
    document.getElementById('editServiceType').value = s.service_type_id;
    document.getElementById('editServiceActive').checked = s.is_active;

    document.getElementById('editServiceModal').style.display = 'flex';
}
function closeEditModal() { document.getElementById('editServiceModal').style.display = 'none'; }

// -------------------- Form Submit --------------------
document.getElementById('addServiceForm').addEventListener('submit', function(e){
    e.preventDefault();
    let formData = new FormData(this);

    fetch(`{{ route('services.store') }}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: formData
    }).then(res => res.json())
      .then(data => { if(data.success) location.reload(); else alert('Error'); })
      .catch(err => console.error(err));
});

document.getElementById('editServiceForm').addEventListener('submit', function(e){
    e.preventDefault();
    let formData = new FormData(this);
    let id = formData.get('id');

    fetch(`/services/list`, { // Use your update route
        method: 'POST', // You can send POST with _method=PUT
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: formData
    }).then(res => res.json())
      .then(data => { if(data.success) location.reload(); else alert('Error'); })
      .catch(err => console.error(err));
});

// -------------------- Delete --------------------
function deleteService(id){
    if(!confirm('Are you sure you want to delete this service?')) return;
    fetch(`/services/list`, {
        method:'DELETE',
        headers:{
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type':'application/json'
        },
        body: JSON.stringify({id})
    }).then(res=>res.json())
      .then(data=>{ if(data.success) location.reload(); else alert('Failed'); });
}

// -------------------- Search --------------------
document.getElementById('searchInput').addEventListener('input', function(){
    let filter = this.value.toLowerCase();
    document.querySelectorAll('#servicesTable tbody tr').forEach(tr=>{
        tr.style.display = tr.innerText.toLowerCase().includes(filter)? '' : 'none';
    });
});

// -------------------- Close modal on outside click --------------------
window.addEventListener('click', function(e){
    if(e.target === document.getElementById('addServiceModal')) closeAddModal();
    if(e.target === document.getElementById('editServiceModal')) closeEditModal();
});
</script>
@endsection
