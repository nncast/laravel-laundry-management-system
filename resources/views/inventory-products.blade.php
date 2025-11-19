@extends('layouts.app')

@section('title', 'Inventory - Products')
@section('page-title', 'Products')
@section('active-inventory-products', 'active')

@section('content')
<style>
/* --- Inventory Products Page Specific Styles --- */
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
</style>

<div class="table-container">
    <div class="header-actions">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search Here">
        </div>
        <button class="add-btn" id="addBtn"><i class="fas fa-plus"></i> Add New Product</button>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Unit</th>
                <th>Purchase Price</th>
                <th>Available Stock</th>
                <th>Minimum Stock Level</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="productTable">
            @forelse($products as $index => $product)
                <tr data-id="{{ $product->id }}"
                    data-category-id="{{ $product->category_id }}"
                    data-unit-id="{{ $product->unit_id }}">
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category->name ?? '-' }}</td>
                    <td>{{ $product->unit->name ?? '-' }}</td>
                    <td>${{ number_format($product->purchase_price, 2) }}</td>
                    <td>{{ $product->available_stock }}</td>
                    <td>{{ $product->minimum_stock_level }}</td>
                    <td>
                        <span class="status-{{ $product->status === 'active' ? 'active' : 'inactive' }}">
                            {{ ucfirst($product->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="action-btns">
                            <i class="fas fa-pen edit" data-id="{{ $product->id }}"></i>
                            <i class="fas fa-trash delete" data-id="{{ $product->id }}"></i>
                        </div>
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="10" style="text-align:center; color:#888;">No products found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Add Product Modal -->
<div id="addProductModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
    <div style="background:#fff; padding:20px; border-radius:10px; width:500px; position:relative;">
        <h3 style="margin-bottom:15px;">Add New Product</h3>
        <form method="POST" action="{{ route('products.store') }}">
            @csrf
            <input type="text" name="name" placeholder="Product Name" required style="width:100%; padding:10px; margin-bottom:10px; border-radius:5px; border:1px solid #ccc;">

            <select name="category_id" required style="width:100%; padding:10px; margin-bottom:10px; border-radius:5px; border:1px solid #ccc;">
                <option value="">Select Category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>

            <select name="unit_id" required style="width:100%; padding:10px; margin-bottom:10px; border-radius:5px; border:1px solid #ccc;">
                <option value="">Select Unit</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                @endforeach
            </select>

            <input type="number" step="0.01" name="purchase_price" placeholder="Purchase Price" required style="width:100%; padding:10px; margin-bottom:10px; border-radius:5px; border:1px solid #ccc;">
            <input type="number" name="available_stock" placeholder="Available Stock" required style="width:100%; padding:10px; margin-bottom:10px; border-radius:5px; border:1px solid #ccc;">
            <input type="number" name="minimum_stock_level" placeholder="Minimum Stock Level" required style="width:100%; padding:10px; margin-bottom:10px; border-radius:5px; border:1px solid #ccc;">

            <select name="status" required style="width:100%; padding:10px; margin-bottom:10px; border-radius:5px; border:1px solid #ccc;">
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <div style="text-align:right;">
                <button type="button" id="cancelAdd" style="margin-right:10px; padding:8px 12px; border:none; background:#ccc; border-radius:5px;">Cancel</button>
                <button type="submit" style="padding:8px 12px; border:none; background:var(--blue); color:#fff; border-radius:5px;">Add</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Product Modal -->
<div id="editProductModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
    <div style="background:#fff; padding:20px; border-radius:10px; width:500px; position:relative;">
        <h3 style="margin-bottom:15px;">Edit Product</h3>
        <form method="POST" action="{{ route('products.update') }}" id="editForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="product_id" id="edit_product_id">
            <input type="text" name="name" id="edit_name" placeholder="Product Name" required style="width:100%; padding:10px; margin-bottom:10px; border-radius:5px; border:1px solid #ccc;">

            <select name="category_id" id="edit_category_id" required style="width:100%; padding:10px; margin-bottom:10px; border-radius:5px; border:1px solid #ccc;">
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>

            <select name="unit_id" id="edit_unit_id" required style="width:100%; padding:10px; margin-bottom:10px; border-radius:5px; border:1px solid #ccc;">
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                @endforeach
            </select>

            <input type="number" step="0.01" name="purchase_price" id="edit_purchase_price" placeholder="Purchase Price" required style="width:100%; padding:10px; margin-bottom:10px; border-radius:5px; border:1px solid #ccc;">
            <input type="number" name="available_stock" id="edit_available_stock" placeholder="Available Stock" required style="width:100%; padding:10px; margin-bottom:10px; border-radius:5px; border:1px solid #ccc;">
            <input type="number" name="minimum_stock_level" id="edit_minimum_stock_level" placeholder="Minimum Stock Level" required style="width:100%; padding:10px; margin-bottom:10px; border-radius:5px; border:1px solid #ccc;">

            <select name="status" id="edit_status" required style="width:100%; padding:10px; margin-bottom:10px; border-radius:5px; border:1px solid #ccc;">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
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

    const modalAdd = document.getElementById('addProductModal');
    const modalEdit = document.getElementById('editProductModal');

    const addBtn = document.getElementById('addBtn');
    const cancelAdd = document.getElementById('cancelAdd');
    const cancelEdit = document.getElementById('cancelEdit');

    const searchInput = document.getElementById('searchInput');

    // OPEN ADD MODAL
    addBtn.addEventListener('click', () => modalAdd.style.display = 'flex');
    cancelAdd.addEventListener('click', () => modalAdd.style.display = 'none');

    // OPEN EDIT MODAL
    document.querySelectorAll('.edit').forEach(btn => {
        btn.addEventListener('click', e => {
            const row = e.target.closest('tr');

            document.getElementById('edit_product_id').value = row.dataset.id;
            document.getElementById('edit_name').value = row.cells[1].textContent.trim();
            document.getElementById('edit_category_id').value = row.dataset.categoryId;
            document.getElementById('edit_unit_id').value = row.dataset.unitId;
            document.getElementById('edit_purchase_price').value = parseFloat(row.cells[4].textContent.replace(/[^\d.-]/g, ''));
            document.getElementById('edit_available_stock').value = row.cells[5].textContent.trim();
            document.getElementById('edit_minimum_stock_level').value = row.cells[6].textContent.trim();
            document.getElementById('edit_status').value = row.cells[7].textContent.trim().toLowerCase();


            modalEdit.style.display = 'flex';
        });
    });

    cancelEdit.addEventListener('click', () => modalEdit.style.display = 'none');

    // SEARCH FILTER
    searchInput.addEventListener('input', function () {
        const query = this.value.toLowerCase();
        document.querySelectorAll('#productTable tr').forEach(row => {
            const name = row.cells[1].textContent.toLowerCase();
            row.style.display = name.includes(query) ? '' : 'none';
        });
    });

    // RESET NUMBERING
    function resetTableNumbers() {
        document.querySelectorAll('#productTable tr').forEach((row, i) => {
            row.cells[0].textContent = i + 1;
        });
    }

    // DELETE PRODUCT (AJAX)
    document.querySelectorAll('.delete').forEach(btn => {
        btn.addEventListener('click', e => {
            const productId = e.target.dataset.id;
            if (!confirm("Are you sure you want to delete this product?")) return;

            fetch(`{{ route('products.destroy') }}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ product_id: productId })
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
                alert("Error deleting product.");
            });
        });
    });

});
</script>

@endsection
