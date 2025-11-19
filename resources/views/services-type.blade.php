@extends('layouts.app')

@section('title', 'Service Type')
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
            <tr id="noServiceFound"><td colspan="4" style="text-align:center; color:#999;">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Add Service Type Modal -->
<div id="addServiceModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:1000;">
    <div style="background:#fff; padding:25px; border-radius:10px; width:400px; display:flex; flex-direction:column; gap:15px;">
        <h3 style="margin-bottom:15px;">Add New Service Type</h3>
        <form method="POST" action="{{ route('services.type.store') }}" id="addServiceForm" style="display:flex; flex-direction:column; gap:15px;">
            @csrf
            <input type="text" name="name" placeholder="Service Type Name" required style="padding:10px; border-radius:6px; border:1px solid #ccc; width:100%;">
            <select name="is_active" style="padding:10px; border-radius:6px; border:1px solid #ccc;">
                <option value="1" selected>Active</option>
                <option value="0">Inactive</option>
            </select>
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="close-add-modal" style="padding:10px 18px; border:none; border-radius:6px; background:#f1f1f1; cursor:pointer;">Cancel</button>
                <button type="submit" style="padding:10px 18px; border:none; border-radius:6px; background:#007bff; color:#fff; cursor:pointer;">Add</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Service Type Modal -->
<div id="editServiceModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:1000;">
    <div style="background:#fff; padding:25px; border-radius:10px; width:400px; display:flex; flex-direction:column; gap:15px;">
        <h3 style="margin-bottom:15px;">Edit Service Type</h3>
        <form method="POST" id="editServiceForm" style="display:flex; flex-direction:column; gap:15px;">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="edit_service_id">
            <input type="text" name="name" id="edit_service_name" placeholder="Service Type Name" required style="padding:10px; border-radius:6px; border:1px solid #ccc; width:100%;">
            <select name="is_active" id="edit_service_status" style="padding:10px; border-radius:6px; border:1px solid #ccc;">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="close-edit-modal" style="padding:10px 18px; border:none; border-radius:6px; background:#f1f1f1; cursor:pointer;">Cancel</button>
                <button type="submit" style="padding:10px 18px; border:none; border-radius:6px; background:#007bff; color:#fff; cursor:pointer;">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const addModal = document.getElementById("addServiceModal");
    const editModal = document.getElementById("editServiceModal");
    const addBtn = document.getElementById("addServiceBtn");
    const closeAddBtn = document.querySelector(".close-add-modal");
    const closeEditBtn = document.querySelector(".close-edit-modal");
    const editForm = document.getElementById("editServiceForm");
    const searchInput = document.getElementById("searchServiceInput");

    // Open Add Modal
    addBtn.addEventListener('click', () => { 
        addModal.style.display = 'flex'; 
    });
    
    closeAddBtn.addEventListener('click', () => addModal.style.display = 'none');
    closeEditBtn.addEventListener('click', () => editModal.style.display = 'none');

    // Open Edit Modal
    document.querySelectorAll('.edit').forEach(btn => {
        btn.addEventListener('click', e => {
            const row = e.target.closest('.service-row');
            const id = row.dataset.id;
            const name = row.querySelector('.service-name').innerText;
            const status = row.querySelector('td:nth-child(3) span').classList.contains('status-active') ? 1 : 0;

            // Set form action and method
            editForm.action = "{{ route('services.type.update') }}";
            document.getElementById('edit_service_id').value = id;
            document.getElementById('edit_service_name').value = name;
            document.getElementById('edit_service_status').value = status;
            editModal.style.display = 'flex';
        });
    });

    // Delete Service Type
    document.querySelectorAll('.delete').forEach(btn => {
        btn.addEventListener('click', e => {
            const row = e.target.closest('.service-row');
            const id = row.dataset.id;
            
            if(confirm('Are you sure you want to delete this service type?')){
                // Create a form to submit the DELETE request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('services.type.destroy') }}";
                
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
            }
        });
    });

    // Search
    searchInput.addEventListener('input', () => {
        const query = searchInput.value.toLowerCase();
        document.querySelectorAll('.service-row').forEach(row => {
            const name = row.querySelector('.service-name').innerText.toLowerCase();
            row.style.display = name.includes(query) ? 'table-row' : 'none';
        });
    });

    // Close modals on outside click
    window.addEventListener('click', e => {
        if(e.target === addModal) addModal.style.display = 'none';
        if(e.target === editModal) editModal.style.display = 'none';
    });

    // Handle form submissions
    document.getElementById('addServiceForm').addEventListener('submit', function(e) {
        e.preventDefault();
        this.submit();
    });

    document.getElementById('editServiceForm').addEventListener('submit', function(e) {
        e.preventDefault();
        this.submit();
    });
});
</script>
@endsection