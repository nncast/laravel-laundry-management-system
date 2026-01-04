@extends('layouts.app')

@section('title', 'Master Settings')
@section('page-title', 'Master Settings')
@section('active-settings-mastersettings', 'active')

@section('content')
<!-- Include the reusable modal CSS -->
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">

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
                    <label for="logo">Logo (Favicon)</label>
                    <input type="file" name="logo" id="logo" accept=".ico" onchange="previewIcoFile(this)">
                    <div class="error-message" id="logo_error"></div>
                    
                    <div class="helper-text info">
                        <i class="fas fa-info-circle"></i>
                        Only .ico files are allowed (recommended size: 16x16, 32x32, or 48x48 pixels)
                    </div>
                    
                    @if(!empty($settings->logo))
                        <div class="logo-preview">
                            <img src="{{ asset('storage/' . $settings->logo) }}" alt="Current Logo" 
                                 onerror="this.style.display='none'">
                            <span class="helper-text">
                                Current logo: <a href="{{ asset('storage/' . $settings->logo) }}" target="_blank">View</a>
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('businessProfileModal')">Cancel</button>
                <button type="submit" class="btn-primary">Save Settings</button>
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
                <form method="GET" action="/backup/download" id="backupForm">
                    <button type="submit" class="btn-primary" style="width: 100%;" id="createBackupBtn">
                        <i class="fas fa-download"></i> Download Backup File
                    </button>
                </form>
                
                <div class="helper-text" id="lastBackupText">
                    @if(!empty($settings->last_backup) && $settings->last_backup != 'Never')
                        <div class="status-indicator">
                            <span class="dot"></span>
                            Last backup: {{ \Carbon\Carbon::parse($settings->last_backup)->format('M d, Y H:i') }}
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
                    The backup file contains all your business data and settings.
                    Store it in a safe location.
                </p>
            </div>
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

// Function to preview ICO file name
function previewIcoFile(input) {
    const logoError = document.getElementById('logo_error');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const fileName = file.name;
        const fileExt = fileName.split('.').pop().toLowerCase();
        
        // Clear previous error
        logoError.style.display = 'none';
        logoError.textContent = '';
        
        // Check if it's an ICO file
        if (fileExt !== 'ico') {
            showError('logo_error', 'Only .ico files are allowed. Please select an ICO file.');
            input.value = ''; // Clear the file input
            return false;
        }
        
        // Check file size (max 100KB for ICO files)
        const maxSize = 100 * 1024; // 100KB
        if (file.size > maxSize) {
            showError('logo_error', 'ICO file size must be less than 100KB');
            input.value = '';
            return false;
        }
        
        // You could add more validation here if needed
        return true;
    }
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
            const logo = document.getElementById('logo');
            if (logo && logo.files.length > 0) {
                const file = logo.files[0];
                const validTypes = ['image/vnd.microsoft.icon', 'image/x-icon'];
                const maxSize = 100 * 1024; // 100KB
                
                // Check file extension
                const fileName = file.name;
                const fileExt = fileName.split('.').pop().toLowerCase();
                
                if (fileExt !== 'ico') {
                    showError('logo_error', 'Only .ico files are allowed');
                    valid = false;
                }
                
                // Check MIME type (some browsers might not detect ICO correctly)
                if (!validTypes.includes(file.type) && file.type !== '') {
                    // Some browsers might not recognize .ico MIME type
                    if (fileExt !== 'ico') {
                        showError('logo_error', 'Please upload a valid .ico file');
                        valid = false;
                    }
                }
                
                if (file.size > maxSize) {
                    showError('logo_error', 'ICO file size must be less than 100KB');
                    valid = false;
                }
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
    
    // Simple backup button handler
    const backupForm = document.getElementById('backupForm');
    if (backupForm) {
        backupForm.addEventListener('submit', function(e) {
            const btn = this.querySelector('button');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Backup...';
            btn.disabled = true;
            
            // Show processing message
            const lastBackupText = document.getElementById('lastBackupText');
            const originalBackupText = lastBackupText.innerHTML;
            lastBackupText.innerHTML = '<div class="status-indicator"><span class="dot"></span>Creating backup, please wait...</div>';
            
            // Update last backup time display after a short delay
            setTimeout(() => {
                const now = new Date();
                const formattedDate = now.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                lastBackupText.innerHTML = `<div class="status-indicator"><span class="dot"></span>Last backup: ${formattedDate}</div>`;
                
                // Reset button after download starts
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    // Don't close modal automatically - let user see the updated time
                }, 2000);
            }, 1500);
        });
    }
    
    // Real-time ICO file validation on change
    document.getElementById('logo')?.addEventListener('change', function() {
        previewIcoFile(this);
    });
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