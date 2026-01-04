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
    color: #2c3e50;
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
    color: #2c3e50;
    font-size: 14px;
    white-space: nowrap;
}

.date-picker input {
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    min-width: 200px;
    transition: 0.3s;
}

.date-picker input:focus {
    outline: none;
    border-color: var(--blue);
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
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
    background: #f8f9fa;
    font-weight: 600;
    border-bottom: 1px solid #eaeaea;
    font-size: 14px;
    text-align: left;
}

.table-row {
    display: grid;
    grid-template-columns: 1fr 150px;
    width: 100%;
    padding: 15px 20px;
    border-bottom: 1px solid #f0f0f0;
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

.value.orange { color: #ff9f43; }
.value.green { color: #28a745; }
.value.blue { color: #17a2b8; }
.value.red { color: #dc3545; }

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
    border: 1px solid #eaeaea;
    text-align: center;
}

.summary-card h4 {
    font-size: 14px;
    color: #6c757d;
    margin-bottom: 10px;
    font-weight: 500;
}

.summary-value {
    font-size: 24px;
    font-weight: 600;
}

.summary-value.orders { color: #ff9f43; }
.summary-value.delivered { color: #28a745; }
.summary-value.sales { color: #28a745; }
.summary-value.payment { color: #17a2b8; }
.summary-value.outstanding { color: #dc3545; }

/* Status breakdown */
.status-breakdown {
    margin-top: 25px;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
}

.status-breakdown h4 {
    font-size: 16px;
    color: #2c3e50;
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

.status-pending { background: #fff3cd; color: #856404; }
.status-processing { background: #cce5ff; color: #004085; }
.status-completed { background: #d4edda; color: #155724; }
.status-cancelled { background: #f8d7da; color: #721c24; }

/* Top services */
.top-services {
    margin-top: 25px;
}

.top-services h4 {
    font-size: 16px;
    color: #2c3e50;
    margin-bottom: 15px;
    font-weight: 600;
}

.service-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.service-item:last-child {
    border-bottom: none;
}

.service-name {
    color: #495057;
}

.service-qty {
    color: #6c757d;
    font-size: 13px;
}

.service-amount {
    font-weight: 600;
    color: #28a745;
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
        border: 1px solid #eaeaea;
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
            <div style="font-family: 'Poppins', sans-serif; padding: 20px;">
                <h2 style="color: #2c3e50; margin-bottom: 5px;">Daily Report</h2>
                <p style="color: #6c757d; margin-bottom: 20px;">${formattedDate}</p>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 12px 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Particulars</th>
                            <th style="padding: 12px 15px; text-align: right; border-bottom: 2px solid #dee2e6;">Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td style="padding: 12px 15px; border-bottom: 1px solid #f1f1f1;">Total Orders</td><td style="padding: 12px 15px; text-align: right; border-bottom: 1px solid #f1f1f1; color: #ff9f43;">${stats.total_orders}</td></tr>
                        <tr><td style="padding: 12px 15px; border-bottom: 1px solid #f1f1f1;">Completed Orders</td><td style="padding: 12px 15px; text-align: right; border-bottom: 1px solid #f1f1f1; color: #28a745;">${stats.delivered_orders}</td></tr>
                        <tr><td style="padding: 12px 15px; border-bottom: 1px solid #f1f1f1;">Total Sales</td><td style="padding: 12px 15px; text-align: right; border-bottom: 1px solid #f1f1f1; color: #28a745;">${stats.total_sales}</td></tr>
                        <tr><td style="padding: 12px 15px; border-bottom: 1px solid #f1f1f1;">Total Payment</td><td style="padding: 12px 15px; text-align: right; border-bottom: 1px solid #f1f1f1; color: #17a2b8;">${stats.total_payments}</td></tr>
                        ${stats.outstanding !== '0.00 PHP' ? `<tr><td style="padding: 12px 15px; border-bottom: 1px solid #f1f1f1;">Outstanding Amount</td><td style="padding: 12px 15px; text-align: right; border-bottom: 1px solid #f1f1f1; color: #dc3545;">${stats.outstanding}</td></tr>` : ''}
                    </tbody>
                </table>
                <p style="margin-top: 30px; font-size: 12px; color: #999;">Generated on ${new Date().toLocaleString()}</p>
            </div>
        `;
        
        // Open print window
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Daily Report - ${formattedDate}</title>
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
});
</script>
@endsection