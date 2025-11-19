@extends('layouts.app')

@section('title', 'Sales Report')
@section('page-title', 'Sales Report')
@section('active-reports-sales', 'active')

@section('content')
<style>
/* ---------------- Main Content Styles ---------------- */
.main-content {
    margin-left: 100px; /* match sidebar width */
    width: calc(100% - 100px);
    padding: 80px 20px 20px; /* top + horizontal padding */
    box-sizing: border-box;
}

/* --------- REPORT SECTION (like uploaded image) --------- */

.report-section {
    background: #fff;
    border-radius: 10px;
    padding: 25px;
    margin: 30px 40px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.date-range {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.date-range label {
    font-weight: 500;
    display: block;
    margin-bottom: 6px;
    color: var(--text-dark);
}

.date-range input {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-family: 'Poppins', sans-serif;
    width: 220px;
}

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
    color: var(--text-dark);
    border-bottom: 1px solid #dee2e6;
}

.report-table td {
    border-bottom: 1px solid #f1f1f1;
    color: var(--text-dark);
}

.report-footer {
    display: flex;
    justify-content: space-between;
    margin-top: 15px;
    font-size: 14px;
    color: var(--text-dark);
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

button:hover {
    opacity: 0.85;
}
</style>

<div class="main-content">
    <div class="report-section">
        <div class="date-range">
            <div>
                <label>Start Date</label>
                <input type="date" value="2025-10-28">
            </div>
            <div>
                <label>End Date</label>
                <input type="date" value="2025-10-28">
            </div>
        </div>

        <table class="report-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Order#</th>
                    <th>Customer</th>
                    <th>Subtotal</th>
                    <th>Addon Total</th>
                    <th>Discount</th>
                    <th>Tax Amount</th>
                    <th>Gross Total</th>
                </tr>
            </thead>
            <tbody>
                <!-- Empty rows -->
            </tbody>
        </table>

        <div class="report-footer">
            <div>Total Orders: 0</div>
            <div>Total Sales: <b>0.00 USD</b></div>
            <div>Total Tax Amount: <b>0.00 USD</b></div>
        </div>

        <div class="buttons">
            <button class="download"><i class="fas fa-download"></i> Download Report</button>
            <button class="print"><i class="fas fa-print"></i> Print Report</button>
        </div>
    </div>
</div>
@endsection
