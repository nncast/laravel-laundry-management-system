@extends('layouts.app')

@section('title', 'Sales Report')
@section('page-title', 'Sales Report')
@section('active-reports-sales', 'active')

@section('content')
<style>
/* ================================
   Sales Report Page - FIXED
   ================================ */

/* Header - Match other pages style */
.report-header {
    margin-bottom: 25px;
    width: 100%;
}

.report-header h3 {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 5px;
    font-size: 1.5rem;
}

.report-header p {
    color: #6c757d;
    font-size: 14px;
    margin: 0;
}

/* Report container - match other pages */
.report-container {
    width: 100%;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    padding: 25px;
}

/* Date range picker */
.date-range {
    display: flex;
    gap: 20px;
    margin-bottom: 25px;
    flex-wrap: wrap;
}

.date-range-item {
    flex: 1;
    min-width: 200px;
}

.date-range label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #2c3e50;
    font-size: 14px;
}

.date-range input {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    transition: 0.3s;
}

.date-range input:focus {
    outline: none;
    border-color: var(--blue);
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

/* Apply button */
.apply-btn {
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
    height: fit-content;
    align-self: flex-end;
    transition: all 0.2s ease;
}

.apply-btn:hover {
    background: #0056b3;
    transform: translateY(-1px);
}

/* Table styling - match customers page */
.sales-table {
    width: 100%;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    margin-top: 15px;
}

.table-header {
    display: grid;
    grid-template-columns: 1fr 1fr 1.5fr 1fr 1fr 1fr 1fr 1fr;
    width: 100%;
    padding: 15px 20px;
    background: #f8f9fa;
    font-weight: 600;
    border-bottom: 1px solid #eaeaea;
    font-size: 14px;
    text-align: left;
}

.table-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1.5fr 1fr 1fr 1fr 1fr 1fr;
    width: 100%;
    padding: 15px 20px;
    border-bottom: 1px solid #f0f0f0;
    align-items: center;
    font-size: 14px;
}

.table-row:last-child {
    border-bottom: none;
}

.table-row.empty {
    grid-template-columns: 1fr;
    text-align: center;
    color: #999;
    font-style: italic;
    padding: 30px 20px;
}

/* Column alignments */
.table-row .text-right {
    text-align: right;
}

.table-row .text-center {
    text-align: center;
}

/* Report summary footer */
.report-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #eaeaea;
}

.summary-item {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
}

.summary-label {
    font-size: 13px;
    color: #6c757d;
    margin-bottom: 8px;
    font-weight: 500;
}

.summary-value {
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
}

.summary-value.orders { color: #ff9f43; }
.summary-value.sales { color: #28a745; }
.summary-value.tax { color: #17a2b8; }

/* Action buttons */
.action-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 25px;
}

.btn-download, .btn-print {
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    font-weight: 500;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    font-family: 'Poppins', sans-serif;
}

.btn-download {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.btn-print {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.btn-download:hover {
    background: #ffeaa7;
    transform: translateY(-1px);
}

.btn-print:hover {
    background: #c3e6cb;
    transform: translateY(-1px);
}

/* Filter controls */
.filter-controls {
    display: flex;
    align-items: flex-end;
    gap: 15px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

/* ================================
   MOBILE RESPONSIVENESS
   ================================ */
@media (max-width: 1200px) {
    .table-header {
        display: none;
    }
    
    .table-row {
        grid-template-columns: 1fr;
        border: 1px solid #eaeaea;
        border-radius: 8px;
        margin-bottom: 10px;
        padding: 15px;
        gap: 10px;
    }
    
    .table-row > div::before {
        content: attr(data-label);
        font-weight: 600;
        color: #2c3e50;
        display: block;
        margin-bottom: 5px;
        font-size: 12px;
    }
    
    .table-row .text-right {
        text-align: left;
    }
}

@media (max-width: 768px) {
    .report-container {
        padding: 15px;
    }
    
    .date-range {
        flex-direction: column;
        gap: 15px;
    }
    
    .date-range-item {
        min-width: unset;
        width: 100%;
    }
    
    .apply-btn {
        width: 100%;
        justify-content: center;
    }
    
    .filter-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-download, .btn-print {
        width: 100%;
        justify-content: center;
    }
    
    .report-summary {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 576px) {
    .table-row {
        font-size: 13px;
        padding: 12px;
    }
    
    .summary-item {
        padding: 12px;
    }
    
    .summary-value {
        font-size: 16px;
    }
}

/* Scrollable table for small screens */
@media (max-width: 1200px) {
    .sales-table {
        overflow-x: auto;
    }
    
    .table-header, .table-row {
        min-width: 1000px; /* Minimum width for horizontal scroll */
    }
}
</style>

<div class="report-header">
    <h3>Sales Report</h3>
    <p class="text-muted">Detailed sales analysis with filtering options</p>
</div>

<div class="report-container">
    <!-- Filter controls -->
    <div class="filter-controls">
        <div class="date-range">
            <div class="date-range-item">
                <label for="start-date">Start Date</label>
                <input type="date" id="start-date" value="{{ date('Y-m-01') }}">
            </div>
            <div class="date-range-item">
                <label for="end-date">End Date</label>
                <input type="date" id="end-date" value="{{ date('Y-m-d') }}">
            </div>
            <button class="apply-btn" id="applyFilter">
                <i class="fas fa-filter"></i> Apply Filter
            </button>
        </div>
    </div>
    
    <!-- Sales table -->
    <div class="sales-table">
        <div class="table-header">
            <div>Date</div>
            <div>Order#</div>
            <div>Customer</div>
            <div>Subtotal</div>
            <div>Addon Total</div>
            <div>Discount</div>
            <div>Tax Amount</div>
            <div>Gross Total</div>
        </div>
        
        <!-- Empty state -->
        <div class="table-row empty">
            <div>No sales data found for the selected date range</div>
        </div>
        
        <!-- Sample data row (commented out for now) -->
        <!--
        <div class="table-row">
            <div data-label="Date">2025-10-28</div>
            <div data-label="Order#">ORD-001</div>
            <div data-label="Customer">John Doe</div>
            <div data-label="Subtotal" class="text-right">$100.00</div>
            <div data-label="Addon Total" class="text-right">$20.00</div>
            <div data-label="Discount" class="text-right">-$10.00</div>
            <div data-label="Tax Amount" class="text-right">$5.50</div>
            <div data-label="Gross Total" class="text-right">$115.50</div>
        </div>
        -->
    </div>
    
    <!-- Report summary -->
    <div class="report-summary">
        <div class="summary-item">
            <div class="summary-label">Total Orders</div>
            <div class="summary-value orders">0</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Sales</div>
            <div class="summary-value sales">0.00 USD</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Tax Amount</div>
            <div class="summary-value tax">0.00 USD</div>
        </div>
    </div>
    
    <!-- Action buttons -->
    <div class="action-buttons">
        <button class="btn-download" id="downloadReport">
            <i class="fas fa-download"></i> Download Report
        </button>
        <button class="btn-print" id="printReport">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');
    const applyFilterBtn = document.getElementById('applyFilter');
    const downloadBtn = document.getElementById('downloadReport');
    const printBtn = document.getElementById('printReport');
    
    // Set default date range (current month)
    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    
    if (!startDateInput.value) {
        startDateInput.value = firstDayOfMonth.toISOString().split('T')[0];
    }
    
    if (!endDateInput.value) {
        endDateInput.value = today.toISOString().split('T')[0];
    }
    
    // Validate date range
    function validateDateRange() {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);
        
        if (startDate > endDate) {
            alert('Start date cannot be later than end date.');
            endDateInput.value = startDateInput.value;
            return false;
        }
        
        // Limit to maximum 1 year range
        const diffTime = Math.abs(endDate - startDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays > 365) {
            alert('Date range cannot exceed 1 year. Please select a smaller range.');
            return false;
        }
        
        return true;
    }
    
    // Apply filter handler
    applyFilterBtn.addEventListener('click', function() {
        if (!validateDateRange()) {
            return;
        }
        
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        
        // Show loading state
        const originalText = applyFilterBtn.innerHTML;
        applyFilterBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        applyFilterBtn.disabled = true;
        
        console.log('Fetching sales data for range:', startDate, 'to', endDate);
        
        // Simulate API call
        setTimeout(() => {
            // In real app, update table data here
            alert(`Sales report filtered for ${startDate} to ${endDate}`);
            
            // Reset button
            applyFilterBtn.innerHTML = originalText;
            applyFilterBtn.disabled = false;
        }, 1000);
    });
    
    // Download report handler
    downloadBtn.addEventListener('click', function() {
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        
        const formattedStart = new Date(startDate).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        const formattedEnd = new Date(endDate).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        // Show loading state
        const originalText = downloadBtn.innerHTML;
        downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Downloading...';
        downloadBtn.disabled = true;
        
        // Simulate download process
        setTimeout(() => {
            // In a real application, this would trigger a file download
            alert(`Sales report from ${formattedStart} to ${formattedEnd} is being downloaded.`);
            
            // Reset button
            downloadBtn.innerHTML = originalText;
            downloadBtn.disabled = false;
        }, 1500);
    });
    
    // Print report handler
    printBtn.addEventListener('click', function() {
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        
        const formattedStart = new Date(startDate).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        const formattedEnd = new Date(endDate).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        // Create print-friendly version
        const printContent = `
            <div style="font-family: 'Poppins', sans-serif; padding: 20px;">
                <h2 style="color: #2c3e50; margin-bottom: 5px;">Sales Report</h2>
                <p style="color: #6c757d; margin-bottom: 10px;">${formattedStart} to ${formattedEnd}</p>
                
                <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 12px 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Date</th>
                            <th style="padding: 12px 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Order#</th>
                            <th style="padding: 12px 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Customer</th>
                            <th style="padding: 12px 15px; text-align: right; border-bottom: 2px solid #dee2e6;">Subtotal</th>
                            <th style="padding: 12px 15px; text-align: right; border-bottom: 2px solid #dee2e6;">Addon Total</th>
                            <th style="padding: 12px 15px; text-align: right; border-bottom: 2px solid #dee2e6;">Discount</th>
                            <th style="padding: 12px 15px; text-align: right; border-bottom: 2px solid #dee2e6;">Tax Amount</th>
                            <th style="padding: 12px 15px; text-align: right; border-bottom: 2px solid #dee2e6;">Gross Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="8" style="padding: 30px 15px; text-align: center; color: #999; font-style: italic;">
                                No sales data found for the selected date range
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; text-align: center;">
                            <div style="font-size: 13px; color: #6c757d;">Total Orders</div>
                            <div style="font-size: 18px; font-weight: 600; color: #ff9f43;">0</div>
                        </div>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; text-align: center;">
                            <div style="font-size: 13px; color: #6c757d;">Total Sales</div>
                            <div style="font-size: 18px; font-weight: 600; color: #28a745;">0.00 USD</div>
                        </div>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; text-align: center;">
                            <div style="font-size: 13px; color: #6c757d;">Total Tax Amount</div>
                            <div style="font-size: 18px; font-weight: 600; color: #17a2b8;">0.00 USD</div>
                        </div>
                    </div>
                </div>
                
                <p style="margin-top: 30px; font-size: 12px; color: #999;">Generated on ${new Date().toLocaleString()}</p>
            </div>
        `;
        
        // Open print window
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Sales Report - ${formattedStart} to ${formattedEnd}</title>
                    <style>
                        body { font-family: 'Poppins', sans-serif; margin: 20px; }
                        @media print {
                            body { margin: 0; }
                            @page { margin: 20mm; }
                        }
                    </style>
                </head>
                <body>${printContent}</body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        
        // Wait for content to load then print
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 250);
    });
    
    // Auto-validate on date change
    startDateInput.addEventListener('change', validateDateRange);
    endDateInput.addEventListener('change', validateDateRange);
});
</script>
@endsection