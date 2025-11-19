@extends('layouts.app')

@section('title', 'Inventory - Categories')
@section('page-title', 'Categories')
@section('active-inventory-categories', 'active')

@section('content')
<style>
/* --- Categories Page Specific Styles --- */
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
                            <i class="fas fa-pen edit" data-id="{{ $category->id }}"></i>
                            <i class="fas fa-trash delete" data-id="{{ $category->id }}"></i>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center; color:#888;">No categories found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Add Category Modal -->
<div id="addCategoryModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
    <div style="background:#fff; padding:20px; border-radius:10px; width:400px; position:relative;">
        <h3 style="margin-bottom:15px;">Add New Category</h3>
        <form method="POST" action="{{ route('categories.store') }}">
            @csrf
            <input type="text" name="name" placeholder="Category Name" required style="width:100%; padding:10px; margin-bottom:10px; border-radius:5px; border:1px solid #ccc;">

            <!-- Status Field -->
            <select name="status" required style="width:100%; padding:10px; margin-bottom:10px; border-radius:5px; border:1px solid #ccc;">
                <option value="1" selected>Active</option>
                <option value="0">Inactive</option>
            </select>

            <div style="text-align:right;">
                <button type="button" id="cancelAdd" style="margin-right:10px; padding:8px 12px; border:none; background:#ccc; border-radius:5px;">Cancel</button>
                <button type="submit" style="padding:8px 12px; border:none; background:var(--blue); color:#fff; border-radius:5px;">Add</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editCategoryModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
    <div style="background:#fff; padding:20px; border-radius:10px; width:400px; position:relative;">
        <h3 style="margin-bottom:15px;">Edit Category</h3>
        <form method="POST" action="{{ route('categories.update') }}" id="editForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="category_id" id="edit_category_id">
            <input type="text" name="name" id="edit_name" placeholder="Category Name" required style="width:100%; padding:10px; margin-bottom:10px; border-radius:5px; border:1px solid #ccc;">

            <!-- Status Field -->
            <select name="status" id="edit_status" required style="width:100%; padding:10px; margin-bottom:10px; border-radius:5px; border:1px solid #ccc;">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>

            <div style="text-align:right;">
                <button type="button" id="cancelEdit" style="margin-right:10px; padding:8px 12px; border:none; background:#ccc; border-radius:5px;">Cancel</button>
                <button type="submit" style="padding:8px 12px; border:none; background:var(--blue); color:#fff; border-radius:5px;">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const modalAdd = document.getElementById('addCategoryModal');
    const modalEdit = document.getElementById('editCategoryModal');

    const addBtn = document.getElementById('addBtn');
    const cancelAdd = document.getElementById('cancelAdd');
    const cancelEdit = document.getElementById('cancelEdit');

    const searchInput = document.getElementById('searchInput');


    /* -------------------------
        OPEN ADD MODAL
    ------------------------- */
    addBtn.addEventListener('click', () => {
        modalAdd.style.display = 'flex';
    });

    cancelAdd.addEventListener('click', () => {
        modalAdd.style.display = 'none';
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
            document.getElementById('edit_status').value = statusText === "active" ? 1 : 0;

            modalEdit.style.display = 'flex';
        });
    });

    cancelEdit.addEventListener('click', () => {
        modalEdit.style.display = 'none';
    });


    /* -------------------------
        CLOSE MODALS (CLICK OUTSIDE)
    ------------------------- */
    window.addEventListener('click', e => {
        if (e.target === modalAdd) modalAdd.style.display = 'none';
        if (e.target === modalEdit) modalEdit.style.display = 'none';
    });


    /* -------------------------
        SEARCH FILTER
    ------------------------- */
    searchInput.addEventListener('input', function () {
        const query = this.value.toLowerCase();
        document.querySelectorAll('#categoryTable tr').forEach(row => {
            const name = row.cells[1].textContent.toLowerCase();
            row.style.display = name.includes(query) ? '' : 'none';
        });
    });


    /* -------------------------
        RESET NUMBERING
    ------------------------- */
    function resetTableNumbers() {
        document.querySelectorAll('#categoryTable tr').forEach((row, i) => {
            row.cells[0].textContent = i + 1;
        });
    }


    /* -------------------------
        DELETE CATEGORY (AJAX)
    ------------------------- */
    document.querySelectorAll('.delete').forEach(btn => {
        btn.addEventListener('click', e => {

            const categoryId = e.target.dataset.id;

            if (!confirm("Are you sure you want to delete this category?"))
                return;

            fetch(`{{ route('categories.destroy') }}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ category_id: categoryId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const row = e.target.closest('tr');
                    row.remove();
                    resetTableNumbers();
                } else {
                    alert("Delete failed.");
                }
            })
            .catch(err => {
                console.error(err);
                alert("Error deleting category.");
            });
        });
    });

});


</script>

@endsection