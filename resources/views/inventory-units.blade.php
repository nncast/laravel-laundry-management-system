@extends('layouts.app')

@section('title', 'Inventory - Units')
@section('page-title', 'Units')
@section('active-inventory-units', 'active')

@section('content')
<style>
/* --- Units Page Specific Styles --- */
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
            <input type="text" placeholder="Search Here">
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
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $unit->name }}</td>
            <td>{{ $unit->short_form }}</td>
            <td>{{ $unit->description ?? '-' }}</td>
            <td>
                <span class="status-{{ $unit->status }}">
                    {{ ucfirst($unit->status) }}
                </span>
            </td>
            <td>
                <div class="action-btns">
                    <i class="fas fa-pen edit" data-id="{{ $unit->id }}"></i>
                    <i class="fas fa-trash delete" data-id="{{ $unit->id }}"></i>
                </div>
            </td>
        </tr>
        @endforeach
        </tbody>

    </table>
</div>

<!-- Add Unit Modal -->
<div id="addUnitModal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; overflow:auto; background:rgba(0,0,0,0.5);">
    <div style="background:#fff; margin:10% auto; padding:25px; border-radius:10px; width:400px; position:relative;">
        <span id="closeModal" style="position:absolute; top:12px; right:15px; font-size:20px; cursor:pointer;">&times;</span>
        <h3 style="margin-bottom:20px;">Add New Unit</h3>
        <form id="addUnitForm">
            @csrf
            <div style="margin-bottom:15px;">
                <label for="unitName" style="display:block; margin-bottom:5px; font-weight:500;">Name</label>
                <input type="text" id="unitName" name="name" required style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #ccc;">
            </div>
            <div style="margin-bottom:15px;">
                <label for="unitShortForm" style="display:block; margin-bottom:5px; font-weight:500;">Short Form</label>
                <input type="text" id="unitShortForm" name="short_form" style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #ccc;">
            </div>
            <div style="margin-bottom:15px;">
                <label for="unitDescription" style="display:block; margin-bottom:5px; font-weight:500;">Description</label>
                <textarea id="unitDescription" name="description" style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #ccc;"></textarea>
            </div>
            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:500;">Status</label>
                <select id="unitStatus" name="status" required style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #ccc;">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <button type="submit" style="background:#007bff; color:#fff; border:none; border-radius:6px; padding:10px 18px; font-size:14px; font-weight:500; cursor:pointer;">Add Unit</button>
        </form>
    </div>
</div>
<!-- Edit Unit Modal -->
<div id="editUnitModal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; overflow:auto; background:rgba(0,0,0,0.5);">
    <div style="background:#fff; margin:10% auto; padding:25px; border-radius:10px; width:400px; position:relative;">
        <span id="closeEditModal" style="position:absolute; top:12px; right:15px; font-size:20px; cursor:pointer;">&times;</span>
        <h3 style="margin-bottom:20px;">Edit Unit</h3>
        <form id="editUnitForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="editUnitId">
            <div style="margin-bottom:15px;">
                <label for="editUnitName" style="display:block; margin-bottom:5px; font-weight:500;">Name</label>
                <input type="text" id="editUnitName" name="name" required style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #ccc;">
            </div>
            <div style="margin-bottom:15px;">
                <label for="editUnitShortForm" style="display:block; margin-bottom:5px; font-weight:500;">Short Form</label>
                <input type="text" id="editUnitShortForm" name="short_form" style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #ccc;">
            </div>
            <div style="margin-bottom:15px;">
                <label for="editUnitDescription" style="display:block; margin-bottom:5px; font-weight:500;">Description</label>
                <textarea id="editUnitDescription" name="description" style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #ccc;"></textarea>
            </div>
            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:500;">Status</label>
                <select id="editUnitStatus" name="status" required style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #ccc;">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <button type="submit" style="background:#28a745; color:#fff; border:none; border-radius:6px; padding:10px 18px; font-size:14px; font-weight:500; cursor:pointer;">Update Unit</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.querySelector('table tbody');
    const searchInput = document.querySelector('.search-box input');
    const addBtn = document.querySelector('.add-btn');
    const addModal = document.getElementById('addUnitModal');
    const editModal = document.getElementById('editUnitModal');
    const closeModal = document.getElementById('closeModal');
    const closeEditModal = document.getElementById('closeEditModal');
    const addForm = document.getElementById('addUnitForm');
    const editForm = document.getElementById('editUnitForm');
    const editIdInput = document.getElementById('editUnitId');

    // Store units data for easy access (populated from server)
    let unitsData = {!! $units->toJson() !!};

    // Open Add Modal
    addBtn.addEventListener('click', () => {
        addModal.style.display = 'block';
    });

    // Close Add Modal
    closeModal.addEventListener('click', () => {
        addModal.style.display = 'none';
    });

    // Close Edit Modal
    closeEditModal.addEventListener('click', () => {
        editModal.style.display = 'none';
    });

    // Close modals when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === addModal) {
            addModal.style.display = 'none';
        }
        if (e.target === editModal) {
            editModal.style.display = 'none';
        }
    });

    // Handle Edit button clicks
    tableBody.addEventListener('click', (e) => {
        if (e.target.classList.contains('edit')) {
            const id = e.target.dataset.id;
            openEditModal(id);
        }
        
        // Delete functionality
        if (e.target.classList.contains('delete')) {
            const id = e.target.dataset.id;
            if (!confirm("Are you sure you want to delete this unit?")) return;
            deleteUnit(id, e.target);
        }
    });

    // Search functionality
    searchInput.addEventListener('input', () => {
        const filter = searchInput.value.toLowerCase().trim();
        const rows = tableBody.querySelectorAll('tr');

        rows.forEach(row => {
            const name = row.children[1].textContent.toLowerCase();
            const shortForm = row.children[2].textContent.toLowerCase();
            const description = row.children[3].textContent.toLowerCase();
            const status = row.children[4].textContent.toLowerCase();

            const combined = `${name} ${shortForm} ${description} ${status}`;
            row.style.display = combined.includes(filter) ? '' : 'none';
        });
    });

    // Add Unit Form Submission
    addForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
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
                addModal.style.display = 'none';
                addForm.reset();
                alert('Unit added successfully!');
            } else {
                alert(data.message || 'Failed to add unit');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error adding unit');
        }
    });

    // Edit Unit Form Submission
    editForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const id = editIdInput.value;
        const formData = new FormData(editForm);
        formData.append('id', id); // Add ID to form data
        
        const csrfToken = document.querySelector('input[name="_token"]').value;

        try {
            const response = await fetch("{{ route('units.update') }}", {
                method: 'POST', // Use POST method
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok) {
                updateTableRow(data.unit || data);
                editModal.style.display = 'none';
                alert('Unit updated successfully!');
            } else {
                alert(data.message || 'Failed to update unit');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error updating unit');
        }
    });

    // Function to open edit modal and populate data
    function openEditModal(id) {
        // Find unit data from the stored units data
        const unit = unitsData.find(u => u.id == id);
        
        if (!unit) {
            alert('Unit not found');
            return;
        }

        // Populate the edit form
        editIdInput.value = unit.id;
        document.getElementById('editUnitName').value = unit.name;
        document.getElementById('editUnitShortForm').value = unit.short_form;
        document.getElementById('editUnitDescription').value = unit.description || '';
        document.getElementById('editUnitStatus').value = unit.status;

        // Show the modal
        editModal.style.display = 'block';
    }

    // Function to add new unit to table
    function addNewUnitToTable(unit) {
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>${document.querySelectorAll('table tbody tr').length + 1}</td>
            <td>${unit.name}</td>
            <td>${unit.short_form}</td>
            <td>${unit.description || '-'}</td>
            <td>
                <span class="status-${unit.status}">
                    ${unit.status.charAt(0).toUpperCase() + unit.status.slice(1)}
                </span>
            </td>
            <td>
                <div class="action-btns">
                    <i class="fas fa-pen edit" data-id="${unit.id}"></i>
                    <i class="fas fa-trash delete" data-id="${unit.id}"></i>
                </div>
            </td>
        `;
        tableBody.appendChild(newRow);
        
        // Add to unitsData for future edits
        unitsData.push(unit);
    }

    // Function to update table row after edit
    function updateTableRow(unit) {
        const editBtn = tableBody.querySelector(`.edit[data-id="${unit.id}"]`);
        if (editBtn) {
            const row = editBtn.closest('tr');
            row.children[1].textContent = unit.name;
            row.children[2].textContent = unit.short_form;
            row.children[3].textContent = unit.description || '-';
            
            // Update status
            const statusSpan = row.children[4].querySelector('span');
            statusSpan.textContent = unit.status.charAt(0).toUpperCase() + unit.status.slice(1);
            statusSpan.className = `status-${unit.status}`;
            
            // Update unitsData
            const index = unitsData.findIndex(u => u.id == unit.id);
            if (index !== -1) {
                unitsData[index] = unit;
            }
        }
    }

    // Function to delete unit
async function deleteUnit(id, deleteBtn) {
    const csrfToken = document.querySelector('input[name="_token"]').value;
    
    // Create form data and append the ID
    const formData = new FormData();
    formData.append('id', id);
    formData.append('_method', 'DELETE'); // Add this line

    try {
        const response = await fetch("{{ route('units.destroy') }}", {
            method: 'POST', // Use POST method for Laravel form spoofing
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await response.json();

        if (response.ok && data.success) {
            // Remove row from table
            const row = deleteBtn.closest('tr');
            row.remove();
            
            // Remove from unitsData
            unitsData = unitsData.filter(u => u.id != id);
            
            // Renumber rows
            renumberRows();
            
            alert('Unit deleted successfully!');
        } else {
            alert(data.message || 'Failed to delete unit');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error deleting unit');
    }
}

    // Function to renumber table rows after deletion
    function renumberRows() {
        const rows = tableBody.querySelectorAll('tr');
        rows.forEach((row, index) => {
            row.children[0].textContent = index + 1;
        });
    }
});
</script>

@endsection
