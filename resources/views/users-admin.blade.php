@extends('layouts.app')

@section('title', 'Staff Dashboard')
@section('page-title', 'Staff Dashboard')
@section('active-users-admin', 'active')

@section('content')
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">

<style>
/* --- Administrator Page Styles --- */
.table-container {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    padding: 25px;
    overflow-x: auto;
}

.header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.search-box input {
    padding: 10px 15px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-family: 'Poppins', sans-serif;
    width: 250px;
    min-width: 200px;
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
    white-space: nowrap;
}
.add-btn i { margin-right: 6px; }
.add-btn:hover { opacity: 0.85; background: #0056b3; }

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 650px;
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

/* ==========================================================
   MODAL STYLES - RESPONSIVE
   ========================================================== */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 10000;
    padding: 15px;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s ease;
}

.modal.active {
    opacity: 1;
    visibility: visible;
}

.modal-content {
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.2);
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    transform: translateY(20px);
    transition: transform 0.3s ease;
}

.modal.active .modal-content {
    transform: translateY(0);
}

.modal-header {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.modal-header h3 {
    font-size: 20px;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.close-btn {
    position: absolute;
    top: 20px;
    right: 20px;
    background: none;
    border: none;
    font-size: 20px;
    color: #999;
    cursor: pointer;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background 0.2s;
}

.close-btn:hover {
    background: #f5f5f5;
    color: #333;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    font-weight: 500;
    color: #2c3e50;
    display: block;
    margin-bottom: 6px;
    font-size: 14px;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 12px 15px;
    border-radius: 8px;
    border: 1px solid #ddd;
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
    transition: border 0.2s;
    background: #fff;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
}

.error-message {
    color: #dc3545;
    font-size: 12px;
    margin-top: 5px;
    display: none;
}

.helper-text {
    color: #6c757d;
    font-size: 11px;
    margin-top: 4px;
}

.required-star {
    color: red;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #eee;
    flex-wrap: wrap;
}

.modal-footer button {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 14px;
    min-width: 80px;
}

.cancel-btn {
    background: #f8f9fa;
    color: #495057;
}

.cancel-btn:hover {
    background: #e9ecef;
}

.submit-btn {
    background: #007bff;
    color: white;
}

.submit-btn:hover {
    background: #0056b3;
}

/* ==========================================================
   RESPONSIVE BREAKPOINTS
   ========================================================== */
@media (max-width: 768px) {
    .table-container {
        padding: 15px;
        margin: 0 -15px;
        border-radius: 0;
    }
    
    .header-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-box input {
        width: 100%;
        margin-bottom: 10px;
    }
    
    .add-btn {
        width: 100%;
        justify-content: center;
    }
    
    .modal-content {
        padding: 20px;
        border-radius: 10px;
        margin: 10px;
        max-height: 85vh;
    }
    
    .modal-header h3 {
        font-size: 18px;
        padding-right: 30px;
    }
    
    .close-btn {
        top: 15px;
        right: 15px;
    }
    
    .form-group input,
    .form-group select {
        padding: 11px 12px;
        font-size: 13px;
    }
    
    .modal-footer {
        flex-direction: column-reverse;
        gap: 8px;
    }
    
    .modal-footer button {
        width: 100%;
        padding: 12px;
    }
    
    table {
        font-size: 13px;
    }
    
    th, td {
        padding: 10px;
    }
}

@media (max-width: 576px) {
    .modal-content {
        padding: 18px;
        margin: 5px;
    }
    
    .modal-header {
        margin-bottom: 15px;
        padding-bottom: 10px;
    }
    
    .form-group {
        margin-bottom: 12px;
    }
    
    .summary-cards {
        flex-direction: column;
        gap: 15px;
    }
    
    .summary-card {
        min-width: 100%;
    }
}

@media (max-width: 400px) {
    .modal-content {
        padding: 15px;
    }
    
    .modal-header h3 {
        font-size: 16px;
    }
    
    .close-btn {
        top: 10px;
        right: 10px;
        font-size: 18px;
    }
    
    .form-group label {
        font-size: 13px;
    }
}

/* Prevent body scroll when modal is open */
body.modal-open {
    overflow: hidden;
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
                @forelse($users as $index => $user)
                <tr class="staff-row"
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

<!-- Add User Modal -->
<div id="addUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New User</h3>
            <button type="button" class="close-btn" id="closeAddModal">&times;</button>
        </div>
        
        <form id="addUserForm" method="POST" action="{{ route('staff.store') }}">
            @csrf
            <div class="form-group">
                <label for="name">
                    Full Name <span class="required-star">*</span>
                </label>
                <input type="text" name="name" id="name" required>
                <div class="error-message" id="name_error"></div>
            </div>
            
            <div class="form-group">
                <label for="phone">
                    Phone Number
                </label>
                <input type="tel" name="phone" id="phone" placeholder="e.g., 09123456789">
                <div class="error-message" id="phone_error"></div>
                <div class="helper-text">Optional - 10 to 15 digits only</div>
            </div>
            
            <div class="form-group">
                <label for="username">
                    Username <span class="required-star">*</span>
                </label>
                <input type="text" name="username" id="username" required>
                <div class="error-message" id="username_error"></div>
            </div>
            
            <div class="form-group">
                <label for="password">
                    Password <span class="required-star">*</span>
                </label>
                <input type="password" name="password" id="password" required>
                <div class="error-message" id="password_error"></div>
                <div class="helper-text">Minimum 6 characters</div>
            </div>
            
            <div class="form-group">
                <label for="role">
                    Role <span class="required-star">*</span>
                </label>
                <select name="role" id="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="admin">Administrator</option>
                    <option value="manager">Manager</option>
                    <option value="cashier">Cashier</option>
                </select>
                <div class="error-message" id="role_error"></div>
            </div>
            
            <div class="form-group">
                <label for="is_active">
                    Status <span class="required-star">*</span>
                </label>
                <select name="is_active" id="is_active" required>
                    <option value="">-- Select Status --</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                <div class="error-message" id="is_active_error"></div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="cancel-btn" id="cancelBtn">Cancel</button>
                <button type="submit" class="submit-btn">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit User</h3>
            <button type="button" class="close-btn" id="closeEditModal">&times;</button>
        </div>
        
        <form id="editUserForm" method="POST" action="">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="edit_user_id">
            
            <div class="form-group">
                <label for="edit_name">
                    Full Name <span class="required-star">*</span>
                </label>
                <input type="text" name="name" id="edit_name" required>
                <div class="error-message" id="edit_name_error"></div>
            </div>
            
            <div class="form-group">
                <label for="edit_phone">
                    Phone Number
                </label>
                <input type="tel" name="phone" id="edit_phone" placeholder="e.g., 09123456789">
                <div class="error-message" id="edit_phone_error"></div>
                <div class="helper-text">Optional - 10 to 15 digits only</div>
            </div>
            
            <div class="form-group">
                <label for="edit_username">
                    Username <span class="required-star">*</span>
                </label>
                <input type="text" name="username" id="edit_username" required>
                <div class="error-message" id="edit_username_error"></div>
            </div>
            
            <div class="form-group">
                <label for="edit_password">
                    Password
                </label>
                <input type="password" name="password" id="edit_password" placeholder="Leave blank to keep current password">
                <div class="error-message" id="edit_password_error"></div>
                <div class="helper-text">Leave blank to keep current password</div>
            </div>
            
            <div class="form-group">
                <label for="edit_role">
                    Role <span class="required-star">*</span>
                </label>
                <select name="role" id="edit_role" required>
                    <option value="">-- Select Role --</option>
                    <option value="admin">Administrator</option>
                    <option value="manager">Manager</option>
                    <option value="cashier">Cashier</option>
                </select>
                <div class="error-message" id="edit_role_error"></div>
            </div>
            
            <div class="form-group">
                <label for="edit_is_active">
                    Status <span class="required-star">*</span>
                </label>
                <select name="is_active" id="edit_is_active" required>
                    <option value="">-- Select Status --</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                <div class="error-message" id="edit_is_active_error"></div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="cancel-btn" id="editCancelBtn">Cancel</button>
                <button type="submit" class="submit-btn">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // ELEMENTS
    const addModal = document.getElementById("addUserModal");
    const editModal = document.getElementById("editUserModal");
    const addBtn = document.querySelector(".add-btn");
    const cancelBtn = document.getElementById("cancelBtn");
    const editCancelBtn = document.getElementById("editCancelBtn");
    const closeAddModal = document.getElementById("closeAddModal");
    const closeEditModal = document.getElementById("closeEditModal");
    const searchInput = document.getElementById("searchInput");
    const staffRows = document.querySelectorAll(".staff-row");
    const noStaffFound = document.getElementById("noStaffFound");

    // MODAL UTILITY FUNCTIONS
    function openModal(modal) {
        modal.classList.add('active');
        document.body.classList.add('modal-open');
    }

    function closeModal(modal) {
        modal.classList.remove('active');
        document.body.classList.remove('modal-open');
        clearAllErrors();
    }

    // ADD USER MODAL
    addBtn.addEventListener("click", () => openModal(addModal));
    cancelBtn.addEventListener("click", () => closeModal(addModal));
    closeAddModal.addEventListener("click", () => closeModal(addModal));

    // EDIT USER MODAL
    editCancelBtn.addEventListener("click", () => closeModal(editModal));
    closeEditModal.addEventListener("click", () => closeModal(editModal));

    // CLOSE MODALS
    window.addEventListener("click", e => {
        if (e.target === addModal) closeModal(addModal);
        if (e.target === editModal) closeModal(editModal);
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal(addModal);
            closeModal(editModal);
        }
    });

    // SEARCH FUNCTIONALITY
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

        noStaffFound.style.display = found ? "none" : "";
    });

    // EDIT BUTTONS
    function attachEditEventListeners() {
        document.querySelectorAll(".action-btns .edit").forEach(button => {
            button.addEventListener("click", function() {
                const row = this.closest("tr");
                const userId = row.dataset.id;
                const name = row.querySelector(".staff-name").innerText.trim();
                const username = row.querySelector(".staff-username").innerText.trim();
                const phone = row.dataset.phone || "";
                const role = row.dataset.role;
                const isActive = row.dataset.status;

                document.getElementById("edit_user_id").value = userId;
                document.getElementById("edit_name").value = name;
                document.getElementById("edit_username").value = username;
                document.getElementById("edit_phone").value = phone;
                document.getElementById("edit_role").value = role;
                document.getElementById("edit_is_active").value = isActive;
                document.getElementById("editUserForm").action = `/staff/${userId}`;

                openModal(editModal);
            });
        });
    }

    attachEditEventListeners();

    // VALIDATION FUNCTIONS
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

    // FORM VALIDATION
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

    // FORM SUBMISSION
    document.getElementById('addUserForm')?.addEventListener('submit', function(e) {
        if (!validateForm(this, false)) e.preventDefault();
    });

    document.getElementById('editUserForm')?.addEventListener('submit', function(e) {
        if (!validateForm(this, true)) e.preventDefault();
    });

    // REAL-TIME VALIDATION
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

    // CLEAR ERRORS ON INPUT
    document.querySelectorAll('input, select').forEach(field => {
        field.addEventListener('input', function() {
            this.style.borderColor = '#ddd';
            const formType = this.closest('form').id.includes('edit') ? 'edit_' : '';
            const errorElement = document.getElementById(formType + this.name + '_error');
            if (errorElement) errorElement.style.display = 'none';
        });
    });

    // AUTO FOCUS
    addBtn.addEventListener('click', () => {
        setTimeout(() => {
            document.getElementById('name')?.focus();
        }, 300);
    });
});
</script>
@endsection