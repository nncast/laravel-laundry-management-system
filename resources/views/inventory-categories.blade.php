@extends('layouts.app')

@section('title', 'Inventory - Categories')
@section('page-title', 'Categories')
@section('active-inventory-categories', 'active')

@section('content')
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
   MODALS (CLEAN)
   ================================ */

#addCategoryModal,
#editCategoryModal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.45);
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

#addCategoryModal > div,
#editCategoryModal > div {
    width: 100%;
    max-width: 420px;
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
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