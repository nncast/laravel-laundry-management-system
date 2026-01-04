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

/* Filter controls */
.filter-controls {
    display: flex;
    align-items: flex-end;
    gap: 15px;
    margin-bottom: 25px;
    flex-wrap: wrap;
}

.filter-group {
    flex: 1;
    min-width: 180px;
}

.filter-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #2c3e50;
    font-size: 14px;
}

.filter-group input,
.filter-group select {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    transition: 0.3s;
}

.filter-group input:focus,
.filter-group select:focus {
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
    transition: all 0.2s ease;
}

.apply-btn:hover {
    background: #0056b3;
    transform: translateY(-1px);
}

/* Status badges */
.status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-processing {
    background: #cce5ff;
    color: #004085;
}

.status-completed {
    background: #d4edda;
    color: #155724;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

/* Table styling */
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
    gap: 10px;
}

.table-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1.5fr 1fr 1fr 1fr 1fr 1fr;
    width: 100%;
    padding: 15px 20px;
    border-bottom: 1px solid #f0f0f0;
    align-items: center;
    font-size: 14px;
    gap: 10px;
}

.table-row:last-child {
    border-bottom: none;
}

/* Column alignments */
.table-row .text-right {
    text-align: right;
    font-weight: 600;
}

/* Report summary */
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
.summary-value.discount { color: #17a2b8; }
.summary-value.items { color: #6f42c1; }
.summary-value.average { color: #20c997; }

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

/* Empty state */
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
    font-style: italic;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 15px;
    color: #dee2e6;
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
    
    .filter-controls {
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
    }
    
    .filter-group {
        min-width: unset;
        width: 100%;
    }
    
    .apply-btn {
        width: 100%;
        justify-content: center;
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
    
    .status-badge {
        font-size: 11px;
        padding: 3px 8px;
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
        min-width: 1000px;
    }
}

/* Loading overlay */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    border-radius: 10px;
}

.loading-spinner {
    font-size: 24px;
    color: var(--blue);
}

/* Filter chips */
.filter-chips {
    display: flex;
    gap: 10px;
    margin-top: 15px;
    flex-wrap: wrap;
}

.filter-chip {
    background: #e7f1ff;
    color: #0056b3;
    padding: 6px 12px;
    border-radius: 16px;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.filter-chip .remove {
    cursor: pointer;
    font-size: 14px;
    opacity: 0.7;
}

.filter-chip .remove:hover {
    opacity: 1;
}
</style>

<div class="report-header">
    <h3>Sales Report</h3>
    <p class="text-muted">Detailed sales analysis with filtering options</p>
</div>

<div class="report-container" id="reportContainer">
    <!-- Filter controls form -->
    <form method="GET" action="{{ route('reports.sales') }}" id="filterForm">
        <div class="filter-controls">
            <div class="filter-group">
                <label for="start_date">Start Date</label>
                <input type="date" id="start_date" name="start_date" value="{{ $filters['start_date'] }}">
            </div>
            <div class="filter-group">
                <label for="end_date">End Date</label>
                <input type="date" id="end_date" name="end_date" value="{{ $filters['end_date'] }}">
            </div>
            <button type="submit" class="apply-btn" id="applyFilter">
                <i class="fas fa-filter"></i> Apply Filter
            </button>
        </div>
    </form>
    
    <!-- Applied filters chips -->
    <div class="filter-chips" id="filterChips" style="{{ $filters['start_date'] || $filters['end_date'] ? 'display: flex;' : 'display: none;' }}">
        @if($filters['start_date'] || $filters['end_date'])
        <div class="filter-chip">
            Date: {{ date('M d, Y', strtotime($filters['start_date'])) }} to {{ date('M d, Y', strtotime($filters['end_date'])) }}
            <span class="remove" onclick="clearFilter('date')">&times;</span>
        </div>
        @endif
    </div>
    
    <!-- Sales table -->
    <div class="sales-table">
        <div class="table-header">
            <div>Date</div>
            <div>Order#</div>
            <div>Customer</div>
            <div>Items Total</div>
            <div>Addon Total</div>
            <div>Discount</div>
            <div>Total</div>
            <div>Status</div>
        </div>
        
        @forelse($salesData as $sale)
        <div class="table-row">
            <div data-label="Date">{{ $sale['date'] }}</div>
            <div data-label="Order#">{{ $sale['order_number'] }}</div>
            <div data-label="Customer">{{ $sale['customer_name'] }}</div>
            <div data-label="Items Total" class="text-right">${{ number_format($sale['items_total'], 2) }}</div>
            <div data-label="Addon Total" class="text-right">${{ number_format($sale['addon_total'], 2) }}</div>
            <div data-label="Discount" class="text-right">${{ number_format($sale['discount'], 2) }}</div>
            <div data-label="Total" class="text-right" style="color: #28a745; font-weight: 700;">${{ number_format($sale['total'], 2) }}</div>
            <div data-label="Status">
                <span class="status-badge status-{{ $sale['status'] }}">
                    {{ ucfirst($sale['status']) }}
                </span>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <i class="fas fa-chart-line"></i>
            <p>No sales data found for the selected date range</p>
        </div>
        @endforelse
    </div>
    
    <!-- Report summary -->
    <div class="report-summary">
        <div class="summary-item">
            <div class="summary-label">Total Orders</div>
            <div class="summary-value orders">{{ $summary['total_orders'] }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Sales</div>
            <div class="summary-value sales">${{ number_format($summary['total_sales'], 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Discount</div>
            <div class="summary-value discount">${{ number_format($summary['total_discount'], 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Items Sold</div>
            <div class="summary-value items">{{ $summary['total_items_sold'] }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Avg Order Value</div>
            <div class="summary-value average">${{ number_format($summary['average_order_value'], 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Completion Rate</div>
            <div class="summary-value" style="color: #20c997;">{{ number_format($summary['completion_rate'], 1) }}%</div>
        </div>
    </div>
    
    <!-- Payment summary -->
    @if($summary['total_outstanding'] > 0)
    <div class="report-summary" style="margin-top: 15px;">
        <div class="summary-item">
            <div class="summary-label">Total Paid</div>
            <div class="summary-value" style="color: #28a745;">${{ number_format($summary['total_paid'], 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Outstanding</div>
            <div class="summary-value" style="color: #dc3545;">${{ number_format($summary['total_outstanding'], 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Addons Sold</div>
            <div class="summary-value" style="color: #6f42c1;">{{ $summary['total_addons_sold'] }}</div>
        </div>
    </div>
    @endif
    
    <!-- Action buttons -->
    <div class="action-buttons">
        <button class="btn-download" id="downloadReport" 
                data-start-date="{{ $filters['start_date'] }}"
                data-end-date="{{ $filters['end_date'] }}">
            <i class="fas fa-download"></i> Download Report
        </button>
        <button class="btn-print" id="printReport">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const applyFilterBtn = document.getElementById('applyFilter');
    const downloadBtn = document.getElementById('downloadReport');
    const printBtn = document.getElementById('printReport');
    const filterForm = document.getElementById('filterForm');
    
    // Validate date range on form submit
    filterForm.addEventListener('submit', function(e) {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);
        
        if (startDate > endDate) {
            e.preventDefault();
            alert('Start date cannot be later than end date.');
            endDateInput.value = startDateInput.value;
            return false;
        }
        
        // Limit to maximum 1 year range
        const diffTime = Math.abs(endDate - startDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays > 365) {
            e.preventDefault();
            alert('Date range cannot exceed 1 year. Please select a smaller range.');
            return false;
        }
        
        // Show loading state
        applyFilterBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        applyFilterBtn.disabled = true;
    });
    
    // Download report handler
    downloadBtn.addEventListener('click', function() {
        const startDate = this.getAttribute('data-start-date');
        const endDate = this.getAttribute('data-end-date');
        
        const formattedStart = startDate ? new Date(startDate).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) : 'All Time';
        
        const formattedEnd = endDate ? new Date(endDate).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) : 'All Time';
        
        // Show loading state
        const originalText = downloadBtn.innerHTML;
        downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Downloading...';
        downloadBtn.disabled = true;
        
        // Build download URL
        let downloadUrl = "{{ route('reports.sales.download') }}";
        downloadUrl += `?start_date=${startDate}&end_date=${endDate}`;
        
        // Create temporary link for download
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.target = '_blank';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Reset button after a short delay
        setTimeout(() => {
            downloadBtn.innerHTML = originalText;
            downloadBtn.disabled = false;
        }, 1000);
    });
    
    // Print report handler
    printBtn.addEventListener('click', function() {
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        
        const formattedStart = startDate ? new Date(startDate).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) : 'All Time';
        
        const formattedEnd = endDate ? new Date(endDate).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) : 'All Time';
        
        // Get table data
        const tableRows = document.querySelectorAll('.table-row:not(.empty-state)');
        const rowsHtml = Array.from(tableRows).map(row => {
            const cells = row.querySelectorAll('div');
            return `
                <tr>
                    <td style="padding: 10px 12px; border-bottom: 1px solid #eee;">${cells[0]?.textContent || ''}</td>
                    <td style="padding: 10px 12px; border-bottom: 1px solid #eee;">${cells[1]?.textContent || ''}</td>
                    <td style="padding: 10px 12px; border-bottom: 1px solid #eee;">${cells[2]?.textContent || ''}</td>
                    <td style="padding: 10px 12px; border-bottom: 1px solid #eee; text-align: right;">${cells[3]?.textContent || ''}</td>
                    <td style="padding: 10px 12px; border-bottom: 1px solid #eee; text-align: right;">${cells[4]?.textContent || ''}</td>
                    <td style="padding: 10px 12px; border-bottom: 1px solid #eee; text-align: right;">${cells[5]?.textContent || ''}</td>
                    <td style="padding: 10px 12px; border-bottom: 1px solid #eee; text-align: right; color: #28a745; font-weight: bold;">${cells[6]?.textContent || ''}</td>
                    <td style="padding: 10px 12px; border-bottom: 1px solid #eee;">${cells[7]?.textContent || ''}</td>
                </tr>
            `;
        }).join('');
        
        // Get summary data
        const totalOrders = document.querySelector('.summary-value.orders')?.textContent || '0';
        const totalSales = document.querySelector('.summary-value.sales')?.textContent || '$0.00';
        const totalDiscount = document.querySelector('.summary-value.discount')?.textContent || '$0.00';
        const totalItems = document.querySelector('.summary-value.items')?.textContent || '0';
        const avgOrderValue = document.querySelector('.summary-value.average')?.textContent || '$0.00';
        
        // Create print-friendly version
        const printContent = `
            <div style="font-family: 'Poppins', sans-serif; padding: 20px;">
                <h2 style="color: #2c3e50; margin-bottom: 5px;">Sales Report</h2>
                <p style="color: #6c757d; margin-bottom: 15px;">Date Range: ${formattedStart} to ${formattedEnd}</p>
                
                <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 12px 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Date</th>
                            <th style="padding: 12px 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Order#</th>
                            <th style="padding: 12px 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Customer</th>
                            <th style="padding: 12px 15px; text-align: right; border-bottom: 2px solid #dee2e6;">Items Total</th>
                            <th style="padding: 12px 15px; text-align: right; border-bottom: 2px solid #dee2e6;">Addon Total</th>
                            <th style="padding: 12px 15px; text-align: right; border-bottom: 2px solid #dee2e6;">Discount</th>
                            <th style="padding: 12px 15px; text-align: right; border-bottom: 2px solid #dee2e6;">Total</th>
                            <th style="padding: 12px 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rowsHtml || '<tr><td colspan="8" style="padding: 30px 15px; text-align: center; color: #999; font-style: italic;">No sales data found</td></tr>'}
                    </tbody>
                </table>
                
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px; margin-bottom: 15px;">
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; text-align: center;">
                            <div style="font-size: 13px; color: #6c757d;">Total Orders</div>
                            <div style="font-size: 18px; font-weight: 600; color: #ff9f43;">${totalOrders}</div>
                        </div>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; text-align: center;">
                            <div style="font-size: 13px; color: #6c757d;">Total Sales</div>
                            <div style="font-size: 18px; font-weight: 600; color: #28a745;">${totalSales}</div>
                        </div>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; text-align: center;">
                            <div style="font-size: 13px; color: #6c757d;">Total Discount</div>
                            <div style="font-size: 18px; font-weight: 600; color: #17a2b8;">${totalDiscount}</div>
                        </div>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; text-align: center;">
                            <div style="font-size: 13px; color: #6c757d;">Items Sold</div>
                            <div style="font-size: 18px; font-weight: 600; color: #6f42c1;">${totalItems}</div>
                        </div>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; text-align: center;">
                            <div style="font-size: 13px; color: #6c757d;">Avg Order Value</div>
                            <div style="font-size: 18px; font-weight: 600; color: #20c997;">${avgOrderValue}</div>
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

// Function to clear filter
function clearFilter(type) {
    const form = document.getElementById('filterForm');
    
    if (type === 'date') {
        document.getElementById('start_date').value = '';
        document.getElementById('end_date').value = '';
    }
    
    form.submit();
}

// Function to validate date range
function validateDateRange() {
    const startDate = new Date(document.getElementById('start_date').value);
    const endDate = new Date(document.getElementById('end_date').value);
    
    if (startDate > endDate) {
        alert('Start date cannot be later than end date.');
        document.getElementById('end_date').value = document.getElementById('start_date').value;
        return false;
    }
    
    const diffTime = Math.abs(endDate - startDate);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays > 365) {
        alert('Date range cannot exceed 1 year. Please select a smaller range.');
        return false;
    }
    
    return true;
}
</script>
@endsection