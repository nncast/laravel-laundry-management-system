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
                <th>Service Types</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($services as $index => $service)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    <img src="{{ $service->icon ?? 'https://via.placeholder.com/32' }}" 
                         width="32" style="vertical-align:middle;margin-right:10px;">
                    {{ $service->name }}
                </td>
                <td>
                    @foreach($service->serviceType as $type)
                        <span class="type-tag">{{ $type->name }}</span>
                    @endforeach
                </td>
                <td>
                    <span class="{{ $service->is_active ? 'status-active' : 'status-inactive' }}">
                        {{ $service->is_active ? 'Active' : 'Inactive' }}
                    </span>
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

<!-- Modal templates -->
<div id="serviceModal" style="display:none;">
    <form id="serviceForm">
        @csrf
        <input type="hidden" name="id" id="serviceId">
        <div>
            <label>Name:</label>
            <input type="text" name="name" id="serviceName" required>
        </div>
        <div>
            <label>Icon URL:</label>
            <input type="text" name="icon" id="serviceIcon">
        </div>
        <div>
            <label>Service Type:</label>
            <select name="service_type_id" id="serviceTypeId" required>
                @foreach($serviceTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Status:</label>
            <input type="checkbox" name="is_active" id="serviceActive" checked> Active
        </div>
        <button type="submit">Save</button>
    </form>
</div>

<script>
function openAddModal() {
    document.getElementById('serviceForm').reset();
    document.getElementById('serviceId').value = '';
    document.getElementById('serviceModal').style.display = 'block';
}

function openEditModal(id) {
    let service = @json($services->keyBy('id'));
    let s = service[id];

    document.getElementById('serviceId').value = s.id;
    document.getElementById('serviceName').value = s.name;
    document.getElementById('serviceIcon').value = s.icon;
    document.getElementById('serviceTypeId').value = s.service_type_id;
    document.getElementById('serviceActive').checked = s.is_active;

    document.getElementById('serviceModal').style.display = 'block';
}

document.getElementById('serviceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let formData = new FormData(this);
    let id = formData.get('id');
    let url = id ? `/services/list` : `/services/list`; // POST or PUT handled by controller
    let method = id ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: formData
    }).then(res => res.json()).then(data => {
        location.reload();
    });
});

function deleteService(id) {
    if(!confirm('Are you sure you want to delete this service?')) return;

    fetch(`/services/list`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id })
    }).then(res => res.json()).then(data => location.reload());
}

// Simple search filter
document.getElementById('searchInput').addEventListener('input', function() {
    let filter = this.value.toLowerCase();
    document.querySelectorAll('#servicesTable tbody tr').forEach(tr => {
        tr.style.display = tr.innerText.toLowerCase().includes(filter) ? '' : 'none';
    });
});
</script>
@endsection