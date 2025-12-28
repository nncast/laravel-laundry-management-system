@extends('layouts.app')

@section('title', 'Daily Report')
@section('page-title', 'Daily Report')
@section('active-reports-daily', 'active')

@section('content')
<style>
.main-content {
    margin-left: 100px; /* match sidebar width */
    width: calc(100% - 100px);
    padding: 80px 20px 20px; /* top + horizontal padding */
    box-sizing: border-box;
}

/* Report container */
.report-container {
    background: #fff;
    border-radius: 10px;
    padding: 25px;
    margin: 0 auto;         /* center horizontally */
    max-width: 1000px;      /* limits width like in screenshot */
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}


.report-container h3 {
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 20px;
}

.date-picker {
    margin-bottom: 20px;
}

.date-picker label {
    font-weight: 500;
    margin-right: 10px;
}

.date-picker input {
    padding: 8px 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-family: 'Poppins', sans-serif;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

th, td {
    text-align: left;
    padding: 12px 15px;
}

th {
    background: #f8f9fa;
    font-weight: 600;
    color: var(--text-dark);
    border-bottom: 1px solid #dee2e6;
}

td {
    border-bottom: 1px solid #f1f1f1;
    color: var(--text-dark);
}

td:nth-child(2) {
    text-align: right;
}

td.orange { color: #ff9f43; }
td.green { color: #28a745; }
td.blue { color: #17a2b8; }
td.red { color: #dc3545; }

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
    <div class="report-container">
        <div class="date-picker">
            <label for="report-date">Date</label>
            <input type="date" id="report-date" value="2025-10-28">
        </div>

        <table>
            <thead>
                <tr>
                    <th>Particulars</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Orders</td>
                    <td class="orange">1</td>
                </tr>
                <tr>
                    <td>No. of Orders Delivered</td>
                    <td class="green">0</td>
                </tr>
                <tr>
                    <td>Total Sales</td>
                    <td class="green">0.00 USD</td>
                </tr>
                <tr>
                    <td>Total Payment</td>
                    <td class="blue">11,948.00 USD</td>
                </tr>
            </tbody>
        </table>

        <div class="buttons">
            <button class="download"><i class="fas fa-download"></i> Download Report</button>
            <button class="print"><i class="fas fa-print"></i> Print Report</button>
        </div>
    </div>
</div>
@endsection
