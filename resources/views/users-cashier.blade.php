@extends('layouts.app')

@section('title', 'Cashier Dashboard')
@section('page-title', 'Cashier Dashboard')
@section('active-users-cashier', 'active')

@section('content')
<style>
/* --- Main Content Styles (kept from original) --- */
.table-container {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    padding: 25px;
}

.header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.search-box input {
    padding: 10px 15px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-family: 'Poppins', sans-serif;
    width: 250px;
}

.add-btn {
    background: #007bff;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 10px 18px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: 0.2s ease;
}
.add-btn i { margin-right: 6px; }
.add-btn:hover { opacity: 0.85; background: #0056b3; }

table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    text-align: left;
    padding: 12px 15px;
    font-size: 14px;
}
th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
}
td {
    border-bottom: 1px solid #f1f1f1;
    color: #2c3e50;
}

.status-active {
    background: #d4edda;
    color: #155724;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
}

.action-btns {
    display: flex;
    gap: 10px;
}
.action-btns i {
    font-size: 14px;
    padding: 8px;
    border-radius: 50%;
    cursor: pointer;
}
.edit { background: rgba(0,123,255,0.1); color: #007bff; }
.delete { background: rgba(0,0,0,0.05); color: #333; }
.edit:hover, .delete:hover { opacity: 0.8; }

.summary-cards {
    display: flex;
    gap: 20px;
    margin-top: 30px;
    flex-wrap: wrap;
}
.summary-card {
    flex: 1;
    min-width: 220px;
    background: #ffffff;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}
.summary-card h4 {
    color: #6c757d;
    font-size: 14px;
}
.summary-card p {
    font-size: 22px;
    font-weight: 600;
}
</style>

<div class="table-container">
    <div class="header-actions">
        <div class="search-box">
            <input type="text" placeholder="Search by Customer or Receipt No.">
        </div>
        <button class="add-btn"><i class="fas fa-cash-register"></i> New Transaction</button>
    </div>

    <h3 style="margin-bottom: 15px;">Recent Transactions</h3>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Receipt No.</th>
                <th>Customer Name</th>
                <th>Service</th>
                <th>Total Amount</th>
                <th>Payment Status</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>RCPT-00125</td>
                <td>Maria Dela Cruz</td>
                <td>Wash & Fold</td>
                <td>₱180.00</td>
                <td><span class="status-active">Paid</span></td>
                <td>Nov 1, 2025</td>
                <td>
                    <div class="action-btns">
                        <i class="fas fa-eye edit" title="View"></i>
                        <i class="fas fa-print delete" title="Print"></i>
                    </div>
                </td>
            </tr>
            <tr>
                <td>2</td>
                <td>RCPT-00126</td>
                <td>John Santos</td>
                <td>Dry Cleaning</td>
                <td>₱320.00</td>
                <td><span class="status-pending">Pending</span></td>
                <td>Nov 1, 2025</td>
                <td>
                    <div class="action-btns">
                        <i class="fas fa-eye edit" title="View"></i>
                        <i class="fas fa-credit-card delete" title="Pay Now"></i>
                    </div>
                </td>
            </tr>
            <tr>
                <td>3</td>
                <td>RCPT-00127</td>
                <td>Ana Lopez</td>
                <td>Iron Only</td>
                <td>₱90.00</td>
                <td><span class="status-active">Paid</span></td>
                <td>Oct 31, 2025</td>
                <td>
                    <div class="action-btns">
                        <i class="fas fa-eye edit" title="View"></i>
                        <i class="fas fa-print delete" title="Print"></i>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div class="summary-cards">
    <div class="summary-card">
        <h4>Total Sales Today</h4>
        <p style="color:#28a745;">₱2,490.00</p>
    </div>
    <div class="summary-card">
        <h4>Pending Payments</h4>
        <p style="color:#ffc107;">3 Orders</p>
    </div>
    <div class="summary-card">
        <h4>Completed Orders</h4>
        <p style="color:#007bff;">12 Orders</p>
    </div>
</div>
@endsection
