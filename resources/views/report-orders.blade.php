@extends('layouts.app')

@section('title', 'Order Report')
@section('page-title', 'Order Report')
@section('active-reports-orders', 'active')

@section('content')
<style>
    .main-content {
    margin-left: 130px; /* match sidebar width */
    width: calc(100% - 100px);
    padding: 80px 20px 20px; /* top + horizontal padding */
    box-sizing: border-box;
}
/* --- Main Content Styles --- */
.report-section {
    background: #fff;
    border-radius: 10px;
    padding: 25px;
    margin: 30px 0;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.filters {
    display: flex;
    align-items: flex-end;
    gap: 20px;
    margin-bottom: 25px;
}

.filter-group label {
    display: block;
    font-weight: 500;
    margin-bottom: 6px;
    color: #2c3e50;
}

.filter-group input,
.filter-group select {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-family: 'Poppins', sans-serif;
    width: 220px;
}

.generate-btn {
    background: #fff3cd;
    color: #856404;
    padding: 10px 18px;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    transition: 0.2s ease;
}
.generate-btn:hover { opacity: 0.85; }

/* Table */
.report-table {
    width: 100%;
    border-collapse: collapse;
}

.report-table th,
.report-table td {
    text-align: left;
    padding: 12px 15px;
}

.report-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
    border-bottom: 1px solid #dee2e6;
}

.report-table td {
    border-bottom: 1px solid #f1f1f1;
    color: #2c3e50;
}

.report-footer {
    display: flex;
    justify-content: space-between;
    margin-top: 15px;
    font-size: 14px;
    color: #2c3e50;
    padding: 10px 5px;
    border-top: 1px solid #eee;
}

.buttons {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
    gap: 10px;
}

button {
    border: none;
    border-radius: 6px;
    padding: 10px 18px;
    font-weight: 500;
    cursor: pointer;
    transition: 0.2s ease;
    font-family: 'Poppins', sans-serif;
}

.download {
    background: #fff3cd;
    color: #856404;
}

.print {
    background: #d4edda;
    color: #155724;
}

button:hover { opacity: 0.85; }
</style>

<div class="main-content">
    <div class="report-section">
        <div class="filters">
            <div class="filter-group">
                <label>Start Date</label>
                <input type="date" value="2025-10-01">
            </div>
            <div class="filter-group">
                <label>End Date</label>
                <input type="date" value="2025-10-31">
            </div>
            <div class="filter-group">
                <label>Status</label>
                <select>
                    <option>All</option>
                    <option>Pending</option>
                    <option>Processing</option>
                    <option>Completed</option>
                    <option>Cancelled</option>
                </select>
            </div>
            <button class="generate-btn"><i class="fas fa-chart-line"></i> Generate Report</button>
        </div>

        <table class="report-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Service</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6" style="text-align:center; color:#888; padding:30px;">No records found</td>
                </tr>
            </tbody>
        </table>

        <div class="report-footer">
            <div>Total Orders: 0</div>
            <div>Total Amount: <b>0.00 USD</b></div>
        </div>

        <div class="buttons">
            <button class="download"><i class="fas fa-download"></i> Download Report</button>
            <button class="print"><i class="fas fa-print"></i> Print Report</button>
        </div>
    </div>
</div>
@endsection
