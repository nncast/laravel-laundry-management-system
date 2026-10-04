@extends('layouts.app')

@section('title', 'Master Settings')
@section('page-title', 'Master Settings')
@section('active-settings-mastersettings', 'active')

@section('content')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/modal.css') }}?v={{ @filemtime(public_path('css/modal.css')) }}">
@endpush

<style>
/* ================================
   Master Settings Page - FIXED
   ================================ */

/* Header - Match customers page style */
.settings-header {
    margin-bottom: 25px;
    width: 100%;
}

.settings-header h3 {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 5px;
    font-size: 1.5rem;
}

.settings-header p {
    color: #6c757d;
    font-size: 14px;
    margin: 0;
}

/* Tool Cards Container - Match customers table style */
.settings-container {
    width: 100%;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    padding: 25px 20px; /* Reduced right padding */
}

/* Tool Cards Grid - Center aligned with better spacing */
.tools-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-top: 20px;
    max-width: 900px; /* Limit max width for better centering */
    margin-left: auto;
    margin-right: auto;
}

.tool-card {
    background: #fff;
    border-radius: 8px;
    padding: 25px 20px;
    text-align: center;
    cursor: pointer;
    border: 1px solid #eaeaea;
    transition: all 0.25s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 150px;
}

.tool-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    border-color: var(--blue);
}

.tool-card i {
    font-size: 32px;
    color: var(--blue);
    margin-bottom: 15px;
}

.tool-card h4 {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 8px;
    color: #2c3e50;
}

.tool-card p {
    font-size: 13px;
    color: #6c757d;
    margin: 0;
    line-height: 1.4;
}

/* Logo preview styling */
.logo-preview {
    margin-top: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.logo-preview img {
    width: 40px;
    height: 40px;
    object-fit: contain;
    border-radius: 4px;
    border: 1px solid #ddd;
    background: #f8f9fa;
    padding: 3px;
}

.logo-preview .helper-text {
    font-size: 12px;
    color: #6c757d;
}

/* Warning Box for Backup */
.warning-box {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 8px;
    padding: 15px;
    margin-top: 20px;
    margin-bottom: 15px;
}

.warning-box h4 {
    color: #856404;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.warning-box p {
    color: #856404;
    font-size: 13px;
    margin: 0;
    line-height: 1.5;
}

/* Form styles - Keep consistent with modal.css */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #2c3e50;
    font-size: 14px;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
    transition: 0.3s;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--blue);
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.helper-text {
    font-size: 12px;
    color: #6c757d;
    margin-top: 5px;
}

.helper-text.info {
    color: #17a2b8;
    background: #e7f7f9;
    padding: 8px 12px;
    border-radius: 6px;
    border-left: 3px solid #17a2b8;
    margin-top: 10px;
}

.helper-text.info i {
    margin-right: 5px;
}

/* Required star */
.required-star {
    color: #dc3545;
}

/* Error messages */
.error-message {
    color: #dc3545;
    font-size: 12px;
    margin-top: 5px;
    display: none;
}

/* Status indicator for backup */
.status-indicator {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    background: #e7f7f9;
    border-radius: 12px;
    font-size: 12px;
    color: #17a2b8;
    margin-top: 8px;
}

.status-indicator .dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #17a2b8;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 0.6; }
    50% { opacity: 1; }
    100% { opacity: 0.6; }
}

/* Backup / restore controls */
.backup-btn,
.restore-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 11px 15px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
    text-decoration: none;
    cursor: pointer;
    color: #fff;
}
.backup-btn { background: var(--blue); }
.backup-btn:hover { background: #0056b3; }
.backup-btn.is-loading { pointer-events: none; opacity: 0.7; }
.restore-btn { background: #dc3545; }
.restore-btn:hover { background: #b02a37; }
.restore-form {
    border-top: 1px solid #eee;
    padding-top: 18px;
    margin-top: 5px;
}
.restore-confirm {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    font-size: 13px;
    color: #856404;
    margin-bottom: 15px;
    cursor: pointer;
}
.restore-confirm input { width: auto; margin-top: 4px; }

/* ================================
   MOBILE RESPONSIVENESS
   ================================ */
@media (max-width: 768px) {
    .settings-container {
        padding: 15px;
    }

    .tools-grid {
        grid-template-columns: 1fr;
        gap: 15px;
        max-width: 100%;
    }

    .tool-card {
        min-height: 130px;
        padding: 20px 15px;
    }

    .tool-card i {
        font-size: 28px;
        margin-bottom: 12px;
    }
}

@media (max-width: 576px) {
    .settings-header h3 {
        font-size: 1.3rem;
    }

    .settings-header p {
        font-size: 13px;
    }

    .settings-container {
        padding: 15px 12px; /* Reduced padding on mobile */
    }
}
</style>

<div class="settings-container">
    <div class="tools-grid">
        <div class="tool-card" onclick="openModal('businessProfileModal')">
            <i class="fas fa-store"></i>
            <h4>Business Profile</h4>
            <p>Business identity and branding</p>
        </div>

        <div class="tool-card" onclick="openModal('dataBackupModal')">
            <i class="fas fa-database"></i>
            <h4>Data Backup</h4>
            <p>Backup and restore</p>
        </div>
    </div>
</div>

<!-- Business Profile Modal -->
<div id="businessProfileModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-store"></i> Business Profile</h3>
            <button type="button" class="close-btn" onclick="closeModal('businessProfileModal')">&times;</button>
        </div>

        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" id="businessProfileForm">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="business_name">Business Name <span class="required-star">*</span></label>
                    <input type="text" name="business_name" id="business_name"
                           value="{{ old('business_name', $settings->business_name ?? '') }}"
                           placeholder="Enter business name" required>
                    <div class="error-message" id="business_name_error"></div>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea name="address" id="address" rows="3"
                              placeholder="Enter business address">{{ old('address', $settings->address ?? '') }}</textarea>
                    <div class="error-message" id="address_error"></div>
                </div>

                <div class="form-group">
                    <label for="contact">Contact Number</label>
                    <input type="text" name="contact" id="contact"
                           value="{{ old('contact', $settings->contact ?? '') }}"
                           placeholder="e.g., 09123456789">
                    <div class="error-message" id="contact_error"></div>
                    <div class="helper-text">Optional - 10 to 15 digits only</div>
                </div>

                <div class="form-group">
                    <label for="favicon">Logo (Favicon)</label>
                    <input type="file" name="favicon" id="favicon" accept=".ico,image/x-icon,image/vnd.microsoft.icon">
                    <div class="error-message" id="favicon_error"></div>

                    <div class="helper-text info">
                        <i class="fas fa-info-circle"></i>
                        Only .ico files are allowed (recommended size: 16x16, 32x32, or 48x48 pixels)
                    </div>

                    @if(!empty($settings->favicon))
                        <div class="logo-preview">
                            <img src="{{ asset($settings->favicon) }}?v={{ optional($settings->updated_at)->timestamp }}" alt="Current Logo"
                                 onerror="this.parentElement.style.display='none'">
                            <span class="helper-text">Current logo</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('businessProfileModal')">Cancel</button>
                <button type="submit" class="btn-primary" id="saveSettingsBtn">Save Settings</button>
            </div>
        </form>
    </div>
</div>

<!-- Data Backup Modal -->
<div id="dataBackupModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-database"></i> Data Backup & Restore</h3>
            <button type="button" class="close-btn" onclick="closeModal('dataBackupModal')">&times;</button>
        </div>

        <div class="modal-body">
            <div class="form-group">
                <label>Create Backup</label>
                <a href="{{ route('backup.download') }}" class="btn-primary backup-btn" id="createBackupBtn">
                    <i class="fas fa-download"></i> Download Backup File
                </a>

                <div class="helper-text" id="lastBackupText">
                    @if(!empty($lastBackup))
                        <div class="status-indicator">
                            <span class="dot"></span>
                            Last backup: {{ \Carbon\Carbon::createFromTimestamp($lastBackup)->timezone(config('app.timezone'))->format('M d, Y h:i A') }}
                        </div>
                    @else
                        <div class="helper-text" style="color: #dc3545;">
                            <i class="fas fa-exclamation-circle"></i> No backup created yet
                        </div>
                    @endif
                </div>
            </div>

            <div class="warning-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Important</h4>
                <p>
                    <strong>Backup your data regularly</strong> to prevent data loss.
                    The backup file (.sql) contains all your business data and settings.
                    Store it in a safe location (USB drive, cloud storage).
                </p>
            </div>

            <form method="POST" action="{{ route('backup.restore') }}" enctype="multipart/form-data" id="restoreForm" class="restore-form">
                @csrf
                <div class="form-group">
                    <label for="backup_file">Restore From Backup</label>
                    <input type="file" name="backup_file" id="backup_file" accept=".sql" required>
                    <div class="helper-text">Only .sql files downloaded from this page can be restored.</div>
                </div>
                <label class="restore-confirm">
                    <input type="checkbox" name="confirm" value="1" required>
                    <span>I understand this will <strong>replace all current data</strong>. A safety backup is saved automatically first.</span>
                </label>
                <button type="submit" class="btn-danger restore-btn" id="restoreBtn">
                    <i class="fas fa-upload"></i> Restore Backup
                </button>
            </form>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeModal('dataBackupModal')">Close</button>
        </div>
    </div>
</div>

<script>
// Modal Utility Functions - Match customers page pattern
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.add('active');
    document.body.classList.add('modal-open');

    // Auto-focus on first input
    setTimeout(() => {
        const firstInput = modal.querySelector('input:not([type="file"]), textarea, button');
        if (firstInput) firstInput.focus();
    }, 300);
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('active');
    document.body.classList.remove('modal-open');
    clearErrors(modalId);
}

function clearErrors(modalId) {
    const modal = document.getElementById(modalId);
    modal.querySelectorAll('.error-message').forEach(error => {
        error.style.display = 'none';
        error.textContent = '';
    });
    modal.querySelectorAll('input, textarea').forEach(field => {
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

// Validate the selected .ico file
function validateIcoFile(input) {
    if (!input.files || !input.files[0]) return true;
    const file = input.files[0];
    const ext = file.name.split('.').pop().toLowerCase();

    if (ext !== 'ico') {
        showError('favicon_error', 'Only .ico files are allowed. Please select an ICO file.');
        input.value = '';
        return false;
    }
    if (file.size > 200 * 1024) {
        showError('favicon_error', 'ICO file size must be less than 200KB');
        input.value = '';
        return false;
    }
    return true;
}

// Form validation for Business Profile
document.addEventListener('DOMContentLoaded', () => {
    // Business Profile Form Validation
    const businessProfileForm = document.getElementById('businessProfileForm');
    if (businessProfileForm) {
        businessProfileForm.addEventListener('submit', function(e) {
            clearErrors('businessProfileModal');
            let valid = true;

            // Business Name validation
            const businessName = document.getElementById('business_name');
            if (!businessName || !businessName.value.trim()) {
                showError('business_name_error', 'Business name is required');
                valid = false;
            }

            // Contact number validation (optional but must be valid if provided)
            const contact = document.getElementById('contact');
            if (contact && contact.value.trim()) {
                const phoneRegex = /^[0-9]{10,15}$/;
                if (!phoneRegex.test(contact.value.replace(/\s/g, ''))) {
                    showError('contact_error', 'Contact number must be 10-15 digits');
                    valid = false;
                }
            }

            // ICO file validation (optional)
            const favicon = document.getElementById('favicon');
            if (favicon && favicon.files.length > 0 && !validateIcoFile(favicon)) {
                valid = false;
            }

            if (!valid) {
                e.preventDefault();
            }
        });
    }

    // Close modals on outside click & escape (match customers page)
    window.addEventListener('click', e => {
        if (e.target.classList.contains('modal')) {
            e.target.classList.remove('active');
            document.body.classList.remove('modal-open');
        }
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.active').forEach(modal => {
                modal.classList.remove('active');
                document.body.classList.remove('modal-open');
            });
        }
    });

    // Backup download: show progress briefly (the browser handles the file download)
    const backupBtn = document.getElementById('createBackupBtn');
    if (backupBtn) {
        backupBtn.addEventListener('click', function () {
            const originalHtml = this.innerHTML;
            this.classList.add('is-loading');
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Preparing backup...';
            setTimeout(() => {
                this.classList.remove('is-loading');
                this.innerHTML = originalHtml;
            }, 4000);
        });
    }

    // Restore: final confirmation
    document.getElementById('restoreForm')?.addEventListener('submit', function (e) {
        const file = document.getElementById('backup_file').files[0];
        if (!file || !file.name.toLowerCase().endsWith('.sql')) {
            e.preventDefault();
            showToast('Please choose a .sql backup file.', 'error');
            return;
        }
        if (!confirm('Restore "' + file.name + '"?\n\nAll current orders, customers, services and settings will be replaced by the backup. You will be logged out afterwards.')) {
            e.preventDefault();
            return;
        }
        const btn = document.getElementById('restoreBtn');
        setTimeout(() => { btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Restoring...'; }, 0);
    });

    document.getElementById('favicon')?.addEventListener('change', function() {
        clearErrors('businessProfileModal');
        validateIcoFile(this);
    });

    // Re-open the profile modal when the server rejected the input
    @if($errors->hasAny(['business_name', 'address', 'contact', 'favicon']))
        openModal('businessProfileModal');
    @endif
    @if($errors->hasAny(['backup_file', 'confirm']))
        openModal('dataBackupModal');
    @endif
});

// Real-time validation for contact field
document.getElementById('contact')?.addEventListener('blur', function() {
    if (this.value && !/^[0-9]{10,15}$/.test(this.value.replace(/\s/g, ''))) {
        showError('contact_error', 'Contact number must be 10-15 digits');
    }
});

// Clear errors on input
document.querySelectorAll('#businessProfileModal input, #businessProfileModal textarea').forEach(field => {
    field.addEventListener('input', function() {
        this.style.borderColor = '#ddd';
        const errorElement = document.getElementById(this.name + '_error');
        if (errorElement) errorElement.style.display = 'none';
    });
});
</script>
@endsection