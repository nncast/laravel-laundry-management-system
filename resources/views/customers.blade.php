@extends('layouts.app')

@section('title', 'Customers')
@section('page-title', 'Customers')

@section('content')
<style>
/* --- Customers Page Specific Styling --- */
.customers-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.search-add-container {
    display: flex;
    gap: 15px;
    align-items: center;
}

.search-box {
    position: relative;
    display: flex;
    align-items: center;
}

.search-box input {
    padding: 10px 15px 10px 40px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    width: 250px;
    transition: all 0.3s;
}

.search-box input:focus {
    outline: none;
    border-color: var(--blue);
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.search-box i {
    position: absolute;
    left: 15px;
    color: var(--text-light);
}

.add-customer-btn {
    background: var(--blue);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 10px 20px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: background 0.3s;
}

.add-customer-btn:hover { background: #0056b3; }

.customers-table {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.table-header {
    display: grid;
    grid-template-columns: 0.5fr 2fr 1.5fr 2fr 1fr;
    background: #f8f9fa;
    padding: 15px 20px;
    font-weight: 600;
    color: var(--text-dark);
    border-bottom: 1px solid #eaeaea;
    gap: 10px;
    text-align: left;
    font-size: 14px;
}

.table-row {
    display: grid;
    grid-template-columns: 0.5fr 2fr 1.5fr 2fr 1fr;
    padding: 15px 20px;
    border-bottom: 1px solid #f0f0f0;
    align-items: center;
    gap: 10px;
    text-align: left;
    font-size: 14px;
}

.table-row:last-child { border-bottom: none; }

.customer-name { font-weight: 600; color: var(--text-dark); }
.customer-contact { color: var(--text-dark); font-weight: 500; }
.customer-address { color: var(--text-light); font-size: 13px; }

.action-buttons { display: flex; gap: 6px; justify-content: flex-start; }
.action-btn {
    background: none;
    border: none;
    color: var(--blue);
    cursor: pointer;
    transition: all 0.2s;
    width: 30px; height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}
.action-btn:hover { color: #0056b3; background: #f0f8ff; }
.action-btn.delete:hover { color: #dc3545; background: #ffe6e6; }

@media (max-width: 768px) {
    .table-header { display: none; }
    .table-row {
        grid-template-columns: 1fr;
        border: 1px solid #eaeaea;
        border-radius: 8px;
        margin-bottom: 10px;
        gap: 10px;
        padding: 10px;
    }
    .action-buttons { justify-content: center; }
}

/* Modal common */
.modal {
    display:none;
    position: fixed;
    top:0; left:0;
    width:100%; height:100%;
    background: rgba(0,0,0,0.4);
    justify-content:center; align-items:center;
    z-index:1000;
}
.modal-content {
    background:#fff;
    padding:30px;
    border-radius:10px;
    box-shadow:0 4px 12px rgba(0,0,0,0.15);
    max-width:500px;
    width:100%;
}
</style>

<div class="customers-header">
    <div class="search-add-container">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search customers...">
        </div>
        <button class="add-customer-btn" id="addCustomerBtn"><i class="fas fa-plus"></i> Add New Customer</button>
    </div>
</div>

<div class="customers-table">
<div class="table-header">
    <div>#</div><div>Customer Name</div><div>Contact</div><div>Address</div><div>Action</div>
</div>

@forelse($customers as $index => $customer)
<div class="table-row" data-id="{{ $customer->id }}">
    <div>{{ $index + 1 }}</div>
    <div class="customer-name">{{ $customer->name }}</div>
    <div class="customer-contact">{{ $customer->contact }}</div>
    <div class="customer-address">{{ $customer->address }}</div>
    <div class="action-buttons">
        <button class="action-btn edit-btn" title="Edit"><i class="fas fa-edit"></i></button>
        <button class="action-btn" title="View"><i class="fas fa-eye"></i></button>
        <button class="action-btn" title="Print"><i class="fas fa-print"></i></button>
        <button class="action-btn delete" title="Delete"><i class="fas fa-trash"></i></button>
    </div>
</div>
@empty
<div class="table-row">
    <div colspan="5" style="text-align:center; color:#999;">No customers found.</div>
</div>
@endforelse
</div>

<!-- Add Customer Modal -->
<div id="addCustomerModal" class="modal">
    <div class="modal-content">
        <h3 style="margin-bottom:20px; font-size:20px; font-weight:600; color:#2c3e50;">Add Customer</h3>
        <form method="POST" action="{{ route('customers.store') }}" id="addCustomerForm">
            @csrf
            <div style="display:flex; flex-direction:column; gap:15px;">
                <div class="form-group">
                    <label>Customer Name <span style="color:red;">*</span></label>
                    <input type="text" name="name" required
                           style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;">
                    <div class="error-message" style="color:#dc3545; font-size:12px; display:none;"></div>
                </div>
                <div class="form-group">
                    <label>Contact</label>
                    <input type="text" name="contact" placeholder="Optional - 10 to 15 digits"
                           style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;">
                    <div class="error-message" style="color:#dc3545; font-size:12px; display:none;"></div>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" rows="3" placeholder="Optional"
                              style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;"></textarea>
                    <div class="error-message" style="color:#dc3545; font-size:12px; display:none;"></div>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" class="close-add-modal"
                            style="padding:10px 18px; border:none; border-radius:6px; background:#f1f1f1; cursor:pointer;">Cancel</button>
                    <button type="submit"
                            style="padding:10px 18px; border:none; border-radius:6px; background:#007bff; color:#fff; cursor:pointer;">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Customer Modal -->
<div id="editCustomerModal" class="modal">
    <div class="modal-content">
        <h3 style="margin-bottom:20px; font-size:20px; font-weight:600; color:#2c3e50;">Edit Customer</h3>
        <form method="POST" id="editCustomerForm">
            @csrf
            @method('PUT')
            <div style="display:flex; flex-direction:column; gap:15px;">
                <div class="form-group">
                    <label>Customer Name <span style="color:red;">*</span></label>
                    <input type="text" name="name" id="edit_name" required
                           style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;">
                    <div class="error-message" style="color:#dc3545; font-size:12px; display:none;"></div>
                </div>
                <div class="form-group">
                    <label>Contact</label>
                    <input type="text" name="contact" id="edit_contact" placeholder="Optional - 10 to 15 digits"
                           style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;">
                    <div class="error-message" style="color:#dc3545; font-size:12px; display:none;"></div>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" id="edit_address" rows="3" placeholder="Optional"
                              style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;"></textarea>
                    <div class="error-message" style="color:#dc3545; font-size:12px; display:none;"></div>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" class="close-edit-modal"
                            style="padding:10px 18px; border:none; border-radius:6px; background:#f1f1f1; cursor:pointer;">Cancel</button>
                    <button type="submit"
                            style="padding:10px 18px; border:none; border-radius:6px; background:#007bff; color:#fff; cursor:pointer;">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const addModal = document.getElementById("addCustomerModal");
    const editModal = document.getElementById("editCustomerModal");
    const addBtn = document.getElementById("addCustomerBtn");
    const closeAddBtns = document.querySelectorAll(".close-add-modal");
    const closeEditBtns = document.querySelectorAll(".close-edit-modal");
    const addForm = document.getElementById("addCustomerForm");
    const editForm = document.getElementById("editCustomerForm");
    const searchInput = document.getElementById("searchInput");
    const tableRows = document.querySelectorAll(".table-row:not(.no-results)");

    // ----------------------------
    // Open Add Modal
    // ----------------------------
    addBtn.addEventListener('click', () => { 
        addModal.style.display = 'flex'; 
        addForm.reset(); 
    });

    // ----------------------------
    // Close Modals
    // ----------------------------
    closeAddBtns.forEach(btn => btn.addEventListener('click', () => addModal.style.display='none'));
    closeEditBtns.forEach(btn => btn.addEventListener('click', () => editModal.style.display='none'));

    // ----------------------------
    // Edit buttons
    // ----------------------------
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const row = e.target.closest('.table-row');
            editForm.action = `/customers/${row.dataset.id}`;
            document.getElementById('edit_name').value = row.querySelector('.customer-name').innerText;
            document.getElementById('edit_contact').value = row.querySelector('.customer-contact').innerText;
            document.getElementById('edit_address').value = row.querySelector('.customer-address').innerText;
            editModal.style.display = 'flex';
        });
    });

    // ----------------------------
    // Close modals on outside click
    // ----------------------------
    window.addEventListener('click', e => {
        if(e.target === addModal) addModal.style.display='none';
        if(e.target === editModal) editModal.style.display='none';
    });

    // ----------------------------
    // Live search filter
    // ----------------------------
    searchInput.addEventListener('input', () => {
        const query = searchInput.value.toLowerCase().trim();
        let anyVisible = false;

        tableRows.forEach(row => {
            const name = row.querySelector('.customer-name').innerText.toLowerCase();
            const contact = row.querySelector('.customer-contact').innerText.toLowerCase();
            const address = row.querySelector('.customer-address').innerText.toLowerCase();

            if(name.includes(query) || contact.includes(query) || address.includes(query)){
                row.style.display = 'grid';
                anyVisible = true;
            } else {
                row.style.display = 'none';
            }
        });

        // Handle "No customers found" message
        let noResultsRow = document.querySelector('.no-results');
        if(!noResultsRow){
            noResultsRow = document.createElement('div');
            noResultsRow.classList.add('table-row', 'no-results');
            noResultsRow.innerHTML = `<div colspan="5" style="text-align:center; color:#999;">No customers found.</div>`;
            document.querySelector('.customers-table').appendChild(noResultsRow);
        }
        noResultsRow.style.display = anyVisible ? 'none' : 'grid';
    });
});

</script>
@endsection
