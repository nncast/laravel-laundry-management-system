@extends('layouts.app')

@section('title', 'Staff Dashboard')
@section('page-title', 'Staff Dashboard')
@section('active-users-admin', 'active')

@section('content')
<style>
/* --- Administrator Page Styles --- */
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
    width: 250px;
}

.add-btn {
    background: #007bff;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 10px 18px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: 0.2s ease;
}
.add-btn i { margin-right: 6px; }
.add-btn:hover { opacity: 0.85; background: #0056b3; }

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

.status-active {
    background: #d4edda;
    color: #155724;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
}

.status-inactive {
    background: #fff3cd;
    color: #856404;
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
.edit { background: rgba(0,123,255,0.1); color: #007bff; }
.delete { background: rgba(255,0,0,0.1); color: red; }
.edit:hover, .delete:hover { opacity: 0.8; }

.summary-cards {
    display: flex;
    gap: 20px;
    margin-top: 30px;
    flex-wrap: wrap;
}
.summary-card {
    flex: 1;
    min-width: 220px;
    background: #ffffff;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}
.summary-card h4 {
    color: #6c757d;
    font-size: 14px;
}
.summary-card p {
    font-size: 22px;
    font-weight: 600;
}

.modal {
    position: fixed;
    top:0; left:0;
    width:100%; height:100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 10000;
}

</style>

<div class="table-container">
    <div class="header-actions">
        <div class="search-box">
            <form method="GET" action="{{ route('staff.index') }}">
                <input type="text" id="searchInput" name="search" placeholder="Search by Name or Role" value="{{ request('search') }}">
            </form>
        </div>

        <button class="add-btn"><i class="fas fa-user-plus"></i> Add New Staff</button>
    </div>

    <h3 style="margin-bottom: 15px;">Staff Management</h3>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Full Name</th>
                <th>Username</th>
                <th>Role</th>
                <th>Status</th>
                <th>Date Created</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <tbody>
                @forelse($users as $index => $user)
                <<tr class="staff-row" 
                data-id="{{ $user->id }}" 
                data-phone="{{ $user->phone }}" 
                data-role="{{ $user->role }}" 
                data-status="{{ $user->is_active ? 1 : 0 }}">
                    <td>{{ $index + 1 }}</td>
                    <td class="staff-name">{{ $user->name }}</td>
                    <td class="staff-username">{{ $user->username }}</td>
                    <td class="staff-role">{{ $user->role }}</td>
                    <td>
                        @if($user->is_active)
                            <span class="status-active">Active</span>
                        @else
                            <span class="status-inactive">Inactive</span>
                        @endif
                    </td>
                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="action-btns">
                            <i class="fas fa-edit edit" title="Edit"></i>
                        </div>
                    </td>
                </tr>
                @empty
                <tr id="noStaffFound">
                    <td colspan="7" style="text-align:center; color:#999;">No records found.</td>
                </tr>
                @endforelse
            </tbody>
        </tbody>
    </table>
</div>

<div class="summary-cards">
    <div class="summary-card">
        <h4>Total Staff</h4>
        <p style="color:#007bff;">{{ $totalUsers }}</p>
    </div>
    <div class="summary-card">
        <h4>Active Accounts</h4>
        <p style="color:#28a745;">{{ $activeUsers }}</p>
    </div>
</div>
<div id="addUserModal" class="modal" style="display:none;">
    <div class="modal-content" style="background:#fff; padding:30px; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.15); max-width:500px; width:100%;">
        <h3 style="margin-bottom:20px; font-size:20px; font-weight:600; color:#2c3e50;">Add New User</h3>
        <form id="addUserForm" method="POST" action="{{ route('staff.store') }}">

            @csrf
            <div style="display:flex; flex-direction:column; gap:15px;">
                <div class="form-group">
                    <label for="name" style="font-weight:500; color:#2c3e50; display:block; margin-bottom:5px;">
                        Full Name <span style="color:red;">*</span>
                    </label>
                    <input type="text" name="name" id="name" required 
                           style="width:100%; padding:10px 15px; border-radius:8px; border:1px solid #ddd; font-size:14px; font-family:'Poppins', sans-serif;">
                    <div class="error-message" id="name_error" style="color:#dc3545; font-size:12px; margin-top:5px; display:none;"></div>
                </div>
                
                <div class="form-group">
                    <label for="phone" style="font-weight:500; color:#2c3e50; display:block; margin-bottom:5px;">
                        Phone Number
                    </label>
                    <input type="tel" name="phone" id="phone" 
                        placeholder="e.g., 09123456789" 
                        style="width:100%; padding:10px 15px; border-radius:8px; border:1px solid #ddd; font-size:14px; font-family:'Poppins', sans-serif;">
                    <div class="error-message" id="phone_error" style="color:#dc3545; font-size:12px; margin-top:5px; display:none;"></div>
                    <div class="helper-text" style="color:#6c757d; font-size:11px; margin-top:3px;">Optional - 10 to 15 digits only</div>
                </div>
                
                <div class="form-group">
                    <label for="username" style="font-weight:500; color:#2c3e50; display:block; margin-bottom:5px;">
                        Username <span style="color:red;">*</span>
                    </label>
                    <input type="text" name="username" id="username" required 
                           style="width:100%; padding:10px 15px; border-radius:8px; border:1px solid #ddd; font-size:14px; font-family:'Poppins', sans-serif;">
                    <div class="error-message" id="username_error" style="color:#dc3545; font-size:12px; margin-top:5px; display:none;"></div>
                </div>
                
                <div class="form-group">
                    <label for="password" style="font-weight:500; color:#2c3e50; display:block; margin-bottom:5px;">
                        Password <span style="color:red;">*</span>
                    </label>
                    <input type="password" name="password" id="password" required 
                           style="width:100%; padding:10px 15px; border-radius:8px; border:1px solid #ddd; font-size:14px; font-family:'Poppins', sans-serif;">
                    <div class="error-message" id="password_error" style="color:#dc3545; font-size:12px; margin-top:5px; display:none;"></div>
                    <div class="helper-text" style="color:#6c757d; font-size:11px; margin-top:3px;">Minimum 6 characters</div>
                </div>
                
                <div class="form-group">
                    <label for="role" style="font-weight:500; color:#2c3e50; display:block; margin-bottom:5px;">
                        Role <span style="color:red;">*</span>
                    </label>
                    <select name="role" id="role" required 
                            style="width:100%; padding:10px 15px; border-radius:8px; border:1px solid #ddd; font-size:14px; font-family:'Poppins', sans-serif; background:#fff;">
                        <option value="">-- Select Role --</option>
                        <option value="admin">Administrator</option>
                        <option value="manager">Manager</option>
                        <option value="cashier">Cashier</option>
                    </select>
                    <div class="error-message" id="role_error" style="color:#dc3545; font-size:12px; margin-top:5px; display:none;"></div>
                </div>
                
                <div class="form-group">
                    <label for="is_active" style="font-weight:500; color:#2c3e50; display:block; margin-bottom:5px;">
                        Status <span style="color:red;">*</span>
                    </label>
                    <select name="is_active" id="is_active" required 
                            style="width:100%; padding:10px 15px; border-radius:8px; border:1px solid #ddd; font-size:14px; font-family:'Poppins', sans-serif; background:#fff;">
                        <option value="">-- Select Status --</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    <div class="error-message" id="is_active_error" style="color:#dc3545; font-size:12px; margin-top:5px; display:none;"></div>
                </div>
                
                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:15px;">
                    <button type="button" id="cancelBtn" style="padding:10px 18px; border:none; border-radius:6px; background:#f1f1f1; font-weight:500; cursor:pointer; transition:0.2s ease;">Cancel</button>
                    <button type="submit" style="padding:10px 18px; border:none; border-radius:6px; background:#007bff; color:#fff; font-weight:500; cursor:pointer; transition:0.2s ease;">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal" style="display:none;">
    <div class="modal-content" style="background:#fff; padding:30px; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.15); max-width:500px; width:100%;">
        <h3 style="margin-bottom:20px; font-size:20px; font-weight:600; color:#2c3e50;">Edit User</h3>
        <form id="editUserForm" method="POST" action="">
            @csrf
            @method('PUT')
            <div style="display:flex; flex-direction:column; gap:15px;">
                <input type="hidden" name="id" id="edit_user_id">
                
                <div class="form-group">
                    <label for="edit_name" style="font-weight:500; color:#2c3e50; display:block; margin-bottom:5px;">
                        Full Name <span style="color:red;">*</span>
                    </label>
                    <input type="text" name="name" id="edit_name" required 
                           style="width:100%; padding:10px 15px; border-radius:8px; border:1px solid #ddd; font-size:14px; font-family:'Poppins', sans-serif;">
                    <div class="error-message" id="edit_name_error" style="color:#dc3545; font-size:12px; margin-top:5px; display:none;"></div>
                </div>
                
                <div class="form-group">
                    <label for="edit_phone" style="font-weight:500; color:#2c3e50; display:block; margin-bottom:5px;">
                        Phone Number
                    </label>
                    <input type="tel" name="phone" id="edit_phone" 
                        placeholder="e.g., 09123456789" 
                        style="width:100%; padding:10px 15px; border-radius:8px; border:1px solid #ddd; font-size:14px; font-family:'Poppins', sans-serif;">
                    <div class="error-message" id="edit_phone_error" style="color:#dc3545; font-size:12px; margin-top:5px; display:none;"></div>
                    <div class="helper-text" style="color:#6c757d; font-size:11px; margin-top:3px;">Optional - 10 to 15 digits only</div>
                </div>
                
                <div class="form-group">
                    <label for="edit_username" style="font-weight:500; color:#2c3e50; display:block; margin-bottom:5px;">
                        Username <span style="color:red;">*</span>
                    </label>
                    <input type="text" name="username" id="edit_username" required 
                           style="width:100%; padding:10px 15px; border-radius:8px; border:1px solid #ddd; font-size:14px; font-family:'Poppins', sans-serif;">
                    <div class="error-message" id="edit_username_error" style="color:#dc3545; font-size:12px; margin-top:5px; display:none;"></div>
                </div>
                
                <div class="form-group">
                    <label for="edit_password" style="font-weight:500; color:#2c3e50; display:block; margin-bottom:5px;">
                        Password
                    </label>
                    <input type="password" name="password" id="edit_password" 
                           placeholder="Leave blank to keep current password" 
                           style="width:100%; padding:10px 15px; border-radius:8px; border:1px solid #ddd; font-size:14px; font-family:'Poppins', sans-serif;">
                    <div class="error-message" id="edit_password_error" style="color:#dc3545; font-size:12px; margin-top:5px; display:none;"></div>
                    <div class="helper-text" style="color:#6c757d; font-size:11px; margin-top:3px;">Leave blank to keep current password</div>
                </div>
                
                <div class="form-group">
                    <label for="edit_role" style="font-weight:500; color:#2c3e50; display:block; margin-bottom:5px;">
                        Role <span style="color:red;">*</span>
                    </label>
                    <select name="role" id="edit_role" required 
                            style="width:100%; padding:10px 15px; border-radius:8px; border:1px solid #ddd; font-size:14px; font-family:'Poppins', sans-serif; background:#fff;">
                        <option value="">-- Select Role --</option>
                        <option value="admin">Administrator</option>
                        <option value="manager">Manager</option>
                        <option value="cashier">Cashier</option>
                    </select>
                    <div class="error-message" id="edit_role_error" style="color:#dc3545; font-size:12px; margin-top:5px; display:none;"></div>
                </div>
                
                <div class="form-group">
                    <label for="edit_is_active" style="font-weight:500; color:#2c3e50; display:block; margin-bottom:5px;">
                        Status <span style="color:red;">*</span>
                    </label>
                    <select name="is_active" id="edit_is_active" required 
                            style="width:100%; padding:10px 15px; border-radius:8px; border:1px solid #ddd; font-size:14px; font-family:'Poppins', sans-serif; background:#fff;">
                        <option value="">-- Select Status --</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    <div class="error-message" id="edit_is_active_error" style="color:#dc3545; font-size:12px; margin-top:5px; display:none;"></div>
                </div>
                
                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:15px;">
                    <button type="button" id="editCancelBtn" style="padding:10px 18px; border:none; border-radius:6px; background:#f1f1f1; font-weight:500; cursor:pointer; transition:0.2s ease;">Cancel</button>
                    <button type="submit" style="padding:10px 18px; border:none; border-radius:6px; background:#007bff; color:#fff; font-weight:500; cursor:pointer; transition:0.2s ease;">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>


<script>
document.addEventListener("DOMContentLoaded", () => {

    // ==========================================================
    // ELEMENTS
    // ==========================================================
    const addModal = document.getElementById("addUserModal");
    const editModal = document.getElementById("editUserModal");
    const addBtn = document.querySelector(".add-btn");
    const cancelBtn = document.getElementById("cancelBtn");
    const editCancelBtn = document.getElementById("editCancelBtn");
    const searchInput = document.getElementById("searchInput");

    const staffRows = document.querySelectorAll(".staff-row");
    const noStaffFound = document.getElementById("noStaffFound");



    // ==========================================================
    // ADD USER MODAL
    // ==========================================================
    addBtn.addEventListener("click", () => {
        addModal.style.display = "flex";
        clearAllErrors();
    });

    cancelBtn.addEventListener("click", () => addModal.style.display = "none");

    // ==========================================================
    // EDIT USER MODAL CLOSE
    // ==========================================================
    editCancelBtn.addEventListener("click", () => editModal.style.display = "none");

    // ==========================================================
    // CLOSE MODALS WHEN CLICKING OUTSIDE
    // ==========================================================
    window.addEventListener("click", e => {
        if (e.target === addModal) addModal.style.display = "none";
        if (e.target === editModal) editModal.style.display = "none";
    });

    // ==========================================================
    // INSTANT CLIENT-SIDE SEARCH (FAST LIKE CUSTOMERS)
    // ==========================================================
    searchInput.addEventListener("input", () => {
        const term = searchInput.value.toLowerCase().trim();
        let found = false;

        staffRows.forEach(row => {
            const name = row.querySelector(".staff-name").innerText.toLowerCase();
            const username = row.querySelector(".staff-username").innerText.toLowerCase();
            const role = row.querySelector(".staff-role").innerText.toLowerCase();

            if (name.includes(term) || username.includes(term) || role.includes(term)) {
                row.style.display = "";
                found = true;
            } else {
                row.style.display = "none";
            }
        });

        // Toggle "no results"
        noStaffFound.style.display = found ? "none" : "";
    });

    // ==========================================================
    // EDIT USER BUTTONS
    // ==========================================================
    function attachEditEventListeners() {
        document.querySelectorAll(".action-btns .edit").forEach(button => {
            button.addEventListener("click", function() {
                const row = this.closest("tr");

                const userId = row.dataset.id;
                const name = row.querySelector(".staff-name").innerText.trim();
                const username = row.querySelector(".staff-username").innerText.trim();
                const phone = row.dataset.phone || "";
                const role = row.dataset.role;       // "admin", "manager", etc.
                const isActive = row.dataset.status; // "1" or "0"

                // Fill modal fields
                document.getElementById("edit_user_id").value = userId;
                document.getElementById("edit_name").value = name;
                document.getElementById("edit_username").value = username;
                document.getElementById("edit_phone").value = phone;
                document.getElementById("edit_role").value = role;
                document.getElementById("edit_is_active").value = isActive;

                // Set form action
                document.getElementById("editUserForm").action = `/staff/${userId}`;

                // Show modal
                editModal.style.display = "flex";

                // Clear any previous errors
                clearAllErrors();
            });

        });
    }

    attachEditEventListeners();

    // ==========================================================
    // VALIDATION HELPERS
    // ==========================================================
    function validatePhone(phone) {
        if (!phone) return true;
        return /^[0-9]{10,15}$/.test(phone.replace(/\s/g, ''));
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

    // ==========================================================
    // FORM VALIDATION (ADD + EDIT)
    // ==========================================================
    function validateForm(form, isEdit) {
        let valid = true;
        clearAllErrors();

        const fields = ['name', 'username', 'role', 'is_active'];
        if (!isEdit) fields.push('password');

        fields.forEach(field => {
            const input = form.querySelector(`[name="${field}"]`);
            if (input && !input.value.trim()) {
                showError(`${isEdit ? 'edit_' : ''}${field}_error`, 'This field is required');
                valid = false;
            }
        });

        const phoneField = form.querySelector('[name="phone"]');
        if (phoneField && phoneField.value && !validatePhone(phoneField.value)) {
            showError(`${isEdit ? 'edit_' : ''}phone_error`, 'Phone number must be 10-15 digits');
            valid = false;
        }

        if (!isEdit) {
            const passField = form.querySelector('[name="password"]');
            if (passField && passField.value.length < 6) {
                showError('password_error', 'Password must be at least 6 characters');
                valid = false;
            }
        }

        if (isEdit) {
            const passField = form.querySelector('[name="password"]');
            if (passField.value && passField.value.length < 6) {
                showError('edit_password_error', 'Password must be at least 6 characters');
                valid = false;
            }
        }

        return valid;
    }

    // ==========================================================
    // ADD FORM SUBMIT
    // ==========================================================
    document.getElementById('addUserForm')?.addEventListener('submit', function(e) {
        if (!validateForm(this, false)) e.preventDefault();
    });

    // ==========================================================
    // EDIT FORM SUBMIT
    // ==========================================================
    document.getElementById('editUserForm')?.addEventListener('submit', function(e) {
        if (!validateForm(this, true)) e.preventDefault();
    });

    // ==========================================================
    // REAL-TIME PHONE VALIDATION
    // ==========================================================
    document.getElementById('phone')?.addEventListener('blur', function() {
        if (this.value && !validatePhone(this.value)) {
            showError('phone_error', 'Phone number must be 10-15 digits');
        }
    });

    document.getElementById('edit_phone')?.addEventListener('blur', function() {
        if (this.value && !validatePhone(this.value)) {
            showError('edit_phone_error', 'Phone number must be 10-15 digits');
        }
    });

    // ==========================================================
    // REMOVE ERROR STYLES WHEN USER TYPES
    // ==========================================================
    document.querySelectorAll('input, select').forEach(field => {
        field.addEventListener('input', function() {
            this.style.borderColor = '#ddd';

            const formType = this.closest('form').id.includes('edit') ? 'edit_' : '';
            const errorElement = document.getElementById(formType + this.name + '_error');

            if (errorElement) errorElement.style.display = 'none';
        });
    });

});
</script>

@endsection