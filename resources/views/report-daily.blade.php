@extends('layouts.app')

@section('title', 'Daily Report')
@section('page-title', 'Daily Report')
@section('active-reports-daily', 'active')

@section('content')
<style>
/* ================================
   Daily Report Page - FIXED
   ================================ */
.report-header {
    margin-bottom: 25px;
    width: 100%;
}

.report-header h3 {
    font-weight: 600;
    color: var(--text);
    margin-bottom: 5px;
    font-size: 1.5rem;
}

.report-container {
    width: 100%;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    padding: 25px;
}

.date-picker {
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.date-picker label {
    font-weight: 500;
    color: var(--text);
    font-size: 14px;
    white-space: nowrap;
}

.date-picker input {
    padding: 10px 15px;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-family: var(--font);
    font-size: 14px;
    min-width: 200px;
    transition: 0.3s;
}

.date-picker input:focus {
    outline: none;
    border-color: var(--blue);
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.25);
}

.report-table {
    width: 100%;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    margin-top: 15px;
}

.table-header {
    display: grid;
    grid-template-columns: 1fr 150px;
    width: 100%;
    padding: 15px 20px;
    background: var(--surface-muted);
    font-weight: 600;
    border-bottom: 1px solid var(--border);
    font-size: 14px;
    text-align: left;
}

.table-row {
    display: grid;
    grid-template-columns: 1fr 150px;
    width: 100%;
    padding: 15px 20px;
    border-bottom: 1px solid var(--surface-sunken);
    align-items: center;
    font-size: 14px;
}

.table-row:last-child {
    border-bottom: none;
}

.value {
    text-align: right;
    font-weight: 600;
}

.value.orange { color: var(--warning); }
.value.green { color: var(--success); }
.value.blue { color: var(--info); }
.value.red { color: var(--danger); }

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
    font-family: var(--font);
}

.btn-download {
    background: var(--warning-soft);
    color: var(--warning-text);
    border: 1px solid var(--warning-soft);
}

.btn-print {
    background: var(--success-soft);
    color: var(--success-text);
    border: 1px solid var(--success-soft);
}

.btn-download:hover {
    background: var(--warning-soft);
    transform: translateY(-1px);
}

.btn-print:hover {
    background: var(--success-soft);
    transform: translateY(-1px);
}

.summary-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 25px;
}

.summary-card {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    border: 1px solid var(--border);
    text-align: center;
}

.summary-card h4 {
    font-size: 14px;
    color: var(--text-muted);
    margin-bottom: 10px;
    font-weight: 500;
}

.summary-value {
    font-size: 24px;
    font-weight: 600;
}

.summary-value.orders { color: var(--warning); }
.summary-value.delivered { color: var(--success); }
.summary-value.sales { color: var(--success); }
.summary-value.payment { color: var(--info); }
.summary-value.outstanding { color: var(--danger); }

/* Status breakdown */
.status-breakdown {
    margin-top: 25px;
    background: var(--surface-muted);
    padding: 20px;
    border-radius: 8px;
}

.status-breakdown h4 {
    font-size: 16px;
    color: var(--text);
    margin-bottom: 15px;
    font-weight: 600;
}

.status-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.status-tag {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
}

.status-pending { background: var(--warning-soft); color: var(--warning-text); }
.status-processing { background: var(--accent-soft); color: var(--accent-hover); }
.status-completed { background: var(--success-soft); color: var(--success-text); }
.status-cancelled { background: var(--danger-soft); color: var(--danger-text); }

/* Top services */
.top-services {
    margin-top: 25px;
}

.top-services h4 {
    font-size: 16px;
    color: var(--text);
    margin-bottom: 15px;
    font-weight: 600;
}

.service-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid var(--surface-sunken);
}

.service-item:last-child {
    border-bottom: none;
}

.service-name {
    color: var(--text-secondary);
}

.service-qty {
    color: var(--text-muted);
    font-size: 13px;
}

.service-amount {
    font-weight: 600;
    color: var(--success);
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .report-container {
        padding: 15px;
    }
    
    .date-picker {
        flex-direction: column;
        align-items: stretch;
    }
    
    .date-picker input {
        min-width: unset;
        width: 100%;
    }
    
    .table-header {
        display: none;
    }
    
    .table-row {
        grid-template-columns: 1fr;
        border: 1px solid var(--border);
        border-radius: 8px;
        margin-bottom: 10px;
        padding: 12px;
        gap: 10px;
    }
    
    .value {
        text-align: left;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-download, .btn-print {
        width: 100%;
        justify-content: center;
    }
    
    .summary-section {
        grid-template-columns: 1fr;
    }
    
    .status-tags {
        flex-direction: column;
    }
}

@media (max-width: 576px) {
    .table-row {
        font-size: 13px;
    }
    
    .summary-card {
        padding: 15px;
    }
    
    .summary-value {
        font-size: 20px;
    }
}
</style>

<div class="report-header">
    <h3>Daily Report</h3>
    <p class="text-muted">View daily orders, sales, and payments summary</p>
</div>

<div class="report-container">
    <!-- Date selection -->
    <form method="GET" action="{{ route('reports.daily') }}" id="dateForm">
        <div class="date-picker">
            <label for="report-date">Select Date</label>
            <input type="date" id="report-date" name="date" value="{{ $selectedDate }}">
        </div>
    </form>
    
    <!-- Summary cards for quick overview -->
    <div class="summary-section">
        <div class="summary-card">
            <h4>Total Orders</h4>
            <div class="summary-value orders">{{ $stats['total_orders'] }}</div>
        </div>
        <div class="summary-card">
            <h4>Orders Completed</h4>
            <div class="summary-value delivered">{{ $stats['delivered_orders'] }}</div>
        </div>
        <div class="summary-card">
            <h4>Total Sales</h4>
            <div class="summary-value sales">{{ number_format($stats['total_sales'], 2) }} PHP</div>
        </div>
        <div class="summary-card">
            <h4>Total Payment</h4>
            <div class="summary-value payment">{{ number_format($stats['total_payments'], 2) }} PHP</div>
        </div>
        @if($stats['outstanding'] > 0)
        <div class="summary-card">
            <h4>Outstanding</h4>
            <div class="summary-value outstanding">{{ number_format($stats['outstanding'], 2) }} PHP</div>
        </div>
        @endif
    </div>
    
    <!-- Status breakdown -->
    @if(count($stats['status_breakdown']) > 0)
    <div class="status-breakdown">
        <h4>Order Status Breakdown</h4>
        <div class="status-tags">
            @foreach($stats['status_breakdown'] as $status => $count)
                <span class="status-tag status-{{ $status }}">{{ ucfirst($status) }}: {{ $count }}</span>
            @endforeach
        </div>
    </div>
    @endif
    
    <!-- Top services -->
    @if(count($stats['top_services']) > 0)
    <div class="top-services">
        <h4>Top Services Today</h4>
        @foreach($stats['top_services'] as $service)
        <div class="service-item">
            <div>
                <div class="service-name">{{ $service->name }}</div>
                <div class="service-qty">Qty: {{ $service->total_qty }}</div>
            </div>
            <div class="service-amount">{{ number_format($service->total_amount, 2) }} PHP</div>
        </div>
        @endforeach
    </div>
    @endif
    
    <!-- Detailed table -->
    <div class="report-table">
        <div class="table-header">
            <div>Particulars</div>
            <div>Value</div>
        </div>
        
        <div class="table-row">
            <div>Total Orders</div>
            <div class="value orange">{{ $stats['total_orders'] }}</div>
        </div>
        <div class="table-row">
            <div>Completed Orders</div>
            <div class="value green">{{ $stats['delivered_orders'] }}</div>
        </div>
        <div class="table-row">
            <div>Total Sales</div>
            <div class="value green">{{ number_format($stats['total_sales'], 2) }} PHP</div>
        </div>
        <div class="table-row">
            <div>Total Payment</div>
            <div class="value blue">{{ number_format($stats['total_payments'], 2) }} PHP</div>
        </div>
        @if($stats['outstanding'] > 0)
        <div class="table-row">
            <div>Outstanding Amount</div>
            <div class="value red">{{ number_format($stats['outstanding'], 2) }} PHP</div>
        </div>
        @endif
    </div>
    
    <!-- Action buttons -->
    <div class="action-buttons">
        <button class="btn-download" id="downloadReport" data-date="{{ $selectedDate }}">
            <i class="fas fa-download"></i> Download Report
        </button>
        <button class="btn-print" id="printReport">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('report-date');
    const downloadBtn = document.getElementById('downloadReport');
    const printBtn = document.getElementById('printReport');
    const dateForm = document.getElementById('dateForm');
    
    // Date change handler - submit form
    dateInput.addEventListener('change', function() {
        dateForm.submit();
    });
    
    // Download report handler
    downloadBtn.addEventListener('click', function() {
        const date = this.getAttribute('data-date');
        const formattedDate = new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        // Show loading state
        const originalText = downloadBtn.innerHTML;
        downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Downloading...';
        downloadBtn.disabled = true;
        
        // Make AJAX call to download endpoint
        fetch(`/reports/daily/download?date=${date}`)
            .then(response => response.json())
            .then(data => {
                // Create CSV content
                let csvContent = "Daily Report - " + formattedDate + "\n\n";
                csvContent += "Particulars,Value\n";
                csvContent += `Total Orders,${data.data.total_orders}\n`;
                csvContent += `Completed Orders,${data.data.delivered_orders}\n`;
                csvContent += `Total Sales,${data.data.total_sales}\n`;
                csvContent += `Total Payment,${data.data.total_payments}\n`;
                csvContent += `Outstanding,${data.data.outstanding}\n\n`;
                
                // Add status breakdown
                csvContent += "Status Breakdown\n";
                Object.entries(data.data.status_breakdown).forEach(([status, count]) => {
                    csvContent += `${status},${count}\n`;
                });
                
                // Create and download CSV file
                const blob = new Blob([csvContent], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `daily_report_${date}.csv`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            })
            .catch(error => {
                console.error('Error downloading report:', error);
                alert('Error downloading report. Please try again.');
            })
            .finally(() => {
                // Reset button
                downloadBtn.innerHTML = originalText;
                downloadBtn.disabled = false;
            });
    });
    
    // Print report handler
    printBtn.addEventListener('click', function() {
        const date = dateInput.value;
        const formattedDate = new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        // Get current stats from page
        const stats = {
            total_orders: document.querySelector('.summary-value.orders').textContent,
            delivered_orders: document.querySelector('.summary-value.delivered').textContent,
            total_sales: document.querySelectorAll('.value.green')[1].textContent,
            total_payments: document.querySelector('.value.blue').textContent,
            outstanding: document.querySelector('.value.red') ? document.querySelector('.value.red').textContent : '0.00 PHP'
        };
        
        // Create print-friendly version
        const printContent = `
            <div style="font-family: var(--font); padding: 20px;">
                <h2 style="color: var(--text); margin-bottom: 5px;">Daily Report</h2>
                <p style="color: var(--text-muted); margin-bottom: 20px;">${formattedDate}</p>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--surface-muted);">
                            <th style="padding: 12px 15px; text-align: left; border-bottom: 2px solid var(--border);">Particulars</th>
                            <th style="padding: 12px 15px; text-align: right; border-bottom: 2px solid var(--border);">Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td style="padding: 12px 15px; border-bottom: 1px solid var(--surface-sunken);">Total Orders</td><td style="padding: 12px 15px; text-align: right; border-bottom: 1px solid var(--surface-sunken); color: var(--warning);">${stats.total_orders}</td></tr>
                        <tr><td style="padding: 12px 15px; border-bottom: 1px solid var(--surface-sunken);">Completed Orders</td><td style="padding: 12px 15px; text-align: right; border-bottom: 1px solid var(--surface-sunken); color: var(--success);">${stats.delivered_orders}</td></tr>
                        <tr><td style="padding: 12px 15px; border-bottom: 1px solid var(--surface-sunken);">Total Sales</td><td style="padding: 12px 15px; text-align: right; border-bottom: 1px solid var(--surface-sunken); color: var(--success);">${stats.total_sales}</td></tr>
                        <tr><td style="padding: 12px 15px; border-bottom: 1px solid var(--surface-sunken);">Total Payment</td><td style="padding: 12px 15px; text-align: right; border-bottom: 1px solid var(--surface-sunken); color: var(--info);">${stats.total_payments}</td></tr>
                        ${stats.outstanding !== '0.00 PHP' ? `<tr><td style="padding: 12px 15px; border-bottom: 1px solid var(--surface-sunken);">Outstanding Amount</td><td style="padding: 12px 15px; text-align: right; border-bottom: 1px solid var(--surface-sunken); color: var(--danger);">${stats.outstanding}</td></tr>` : ''}
                    </tbody>
                </table>
                <p style="margin-top: 30px; font-size: 12px; color: var(--text-faint);">Generated on ${new Date().toLocaleString()}</p>
            </div>
        `;
        
        // Open print window
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Daily Report - ${formattedDate}</title>
                    <style>
                        body { font-family: var(--font); margin: 20px; }
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
});
</script>
@endsection