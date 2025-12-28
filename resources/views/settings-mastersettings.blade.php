@extends('layouts.app')

@section('title', 'Master Settings')
@section('page-title', 'Master Settings')
@section('active-settings-mastersettings', 'active')

@section('content')

<style>
/* ================= PAGE-ONLY MASTER SETTINGS STYLES ================= */
/* ================= PAGE-ONLY MASTER SETTINGS STYLES ================= */

:root {
    --blue: #007bff;
    --green: #28a745;
    --text-dark: #2c3e50;
    --text-light: #6c757d;
    --shadow: 0 2px 10px rgba(0,0,0,.05);
    --transition: all .3s ease;
}

.tools-container {
    background: #ffffff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: var(--shadow);
}

.tool-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 20px;
    margin-bottom: 35px;
}

.tool-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 25px;
    text-align: center;
    cursor: pointer;
    border: 1px solid #e0e0e0;
    transition: var(--transition);
}

.tool-card i {
    font-size: 34px;
    color: var(--blue);
    margin-bottom: 12px;
}

.tool-card h4 {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-dark);
}

.tool-card p {
    font-size: 13px;
    color: var(--text-light);
}

.tool-card:hover {
    transform: translateY(-5px);
    background: #fff;
    box-shadow: 0 8px 20px rgba(0,0,0,.12);
    border-color: var(--blue);
}

/* ================= MODALS ================= */

.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.5);
    z-index: 1000;
}

.modal-content {
    background: #fff;
    margin: 5% auto;
    width: 90%;
    max-width: 800px;
    height: 80vh;
    display: flex;
    flex-direction: column;
    border-radius: 12px;
    overflow: hidden;
}

.modal-header {
    background: linear-gradient(135deg, var(--blue), #0056b3);
    color: #fff;
    padding: 20px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-body {
    padding: 25px;
    overflow-y: auto;
    background: #fafbfc;
    flex: 1;
}

.modal-footer {
    padding: 20px 25px;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.close-modal {
    background: none;
    border: none;
    font-size: 26px;
    color: #fff;
    cursor: pointer;
}

/* Forms */
.form-group {
    margin-bottom: 15px;
}
.form-group label {
    font-weight: 500;
    display: block;
    margin-bottom: 5px;
}
.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 10px 12px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

/* Toggle */
.toggle-switch {
    position: relative;
    width: 46px;
    height: 24px;
}
.toggle-switch input {
    display: none;
}
.toggle-slider {
    position: absolute;
    inset: 0;
    background: #ccc;
    border-radius: 30px;
}
.toggle-slider::before {
    content: '';
    position: absolute;
    height: 18px;
    width: 18px;
    left: 3px;
    top: 3px;
    background: #fff;
    border-radius: 50%;
    transition: .3s;
}
.toggle-switch input:checked + .toggle-slider {
    background: var(--blue);
}
.toggle-switch input:checked + .toggle-slider::before {
    transform: translateX(22px);
}

/* Important info box */
.modal-body .important-info {
    background: #fff3cd;
    padding: 15px;
    border-radius: 6px;
    margin-top: 20px;
}
.modal-body .important-info h4 {
    margin: 0 0 10px 0;
    color: #856404;
    font-size: 14px;
}
.modal-body .important-info p {
    margin: 0;
    color: #856404;
    font-size: 13px;
}
/* ================= BUTTON STYLES ================= */

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 18px;
    font-size: 14px;
    font-weight: 500;
    border-radius: 6px;
    border: 1px solid transparent;
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
    text-align: center;
}

/* Primary button */
.btn-primary {
    background-color: var(--blue);
    color: #fff;
    border-color: var(--blue);
}
.btn-primary:hover {
    background-color: #0056b3;
    border-color: #004085;
}

/* Secondary button */
.btn-secondary {
    background-color: #f8f9fa;
    color: var(--text-dark);
    border-color: #d6d8db;
}
.btn-secondary:hover {
    background-color: #e2e6ea;
    border-color: #c6c8ca;
}

/* Icon inside button */
.btn i {
    margin-right: 6px;
    font-size: 16px;
}

</style>

<div class="main-content">
    <div class="tools-container">
        <h3>Master Settings</h3>
        <p class="text-muted mb-4">
            Configure business profile, taxes, receipts, system rules, and backups.
        </p>

        <div class="tool-options">
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
</div>
{{-- BUSINESS PROFILE MODAL --}}
<div id="businessProfileModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-store"></i> Business Profile</h3>
            <button class="close-modal" onclick="closeModal('businessProfileModal')">&times;</button>
        </div>

        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Business Name</label>
                    <input name="business_name" value="{{ old('business_name', $settings->business_name ?? '') }}">
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address">{{ old('address', $settings->address ?? '') }}</textarea>
                </div>

                <div class="form-group">
                    <label>Contact</label>
                    <input name="contact" value="{{ old('contact', $settings->contact ?? '') }}">
                </div>

                <div class="form-group">
                    <label>Logo</label>
                    <input type="file" name="logo">
                    @if(!empty($settings->favicon))
                        <small>Current logo: {{ $settings->favicon }}</small>
                    @endif
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('businessProfileModal')">Cancel</button>
                <button class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>



<!-- Data Backup Modal -->
<div id="dataBackupModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-database"></i> Data Backup & Restore</h3>
            <button class="close-modal" onclick="closeModal('dataBackupModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Create Backup</label>
                <button class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-download"></i> Download Backup File
                </button>
                <small style="color: #6c757d; font-size: 12px;">Last backup: 2025-02-18 14:30</small>
            </div>
            
            <div class="form-group">
                <label>Restore from Backup</label>
                <input type="file" accept=".sql,.backup,.json">
                <small style="color: #6c757d; font-size: 12px;">Select backup file to restore</small>
            </div>
            
            <div style="background: #fff3cd; padding: 15px; border-radius: 6px; margin-top: 20px;">
                <h4 style="margin: 0 0 10px 0; color: #856404; font-size: 14px;">
                    <i class="fas fa-exclamation-triangle"></i> Important
                </h4>
                <p style="margin: 0; color: #856404; font-size: 13px;">
                    Backup your data regularly to prevent data loss. Restoring from backup will replace all current data.
                </p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('dataBackupModal')">Cancel</button>
            <button class="btn btn-primary">Restore Backup</button>
        </div>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).style.display = 'block';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
window.onclick = e => {
    if (e.target.classList.contains('modal')) e.target.style.display = 'none';
};
</script>

@endsection
