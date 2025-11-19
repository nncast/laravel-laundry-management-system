@extends('layouts.app')

@section('title', 'Manager Dashboard')
@section('page-title', 'Manager Dashboard')
@section('active-users-manager', 'active')

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
            <input type="text" placeholder="Search by Employee or Task">
        </div>
        <button class="add-btn"><i class="fas fa-user-plus"></i> Assign Task</button>
    </div>

    <h3 style="margin-bottom: 15px; color:#2c3e50;">Employee Performance Overview</h3>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Employee Name</th>
                <th>Role</th>
                <th>Tasks Completed</th>
                <th>Pending Tasks</th>
                <th>Status</th>
                <th>Last Updated</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Maria Dela Cruz</td>
                <td>Cashier</td>
                <td>18</td>
                <td>2</td>
                <td><span class="status-active">Active</span></td>
                <td>Nov 1, 2025</td>
            </tr>
            <tr>
                <td>2</td>
                <td>John Santos</td>
                <td>Cleaner</td>
                <td>10</td>
                <td>5</td>
                <td><span class="status-pending">On Break</span></td>
                <td>Nov 1, 2025</td>
            </tr>
            <tr>
                <td>3</td>
                <td>Ana Lopez</td>
                <td>Cashier</td>
                <td>14</td>
                <td>0</td>
                <td><span class="status-active">Active</span></td>
                <td>Oct 31, 2025</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="summary-cards">
    <div class="summary-card">
        <h4>Team Members</h4>
        <p style="color:#007bff;">8 Employees</p>
    </div>
    <div class="summary-card">
        <h4>Tasks Assigned Today</h4>
        <p style="color:#28a745;">15 Tasks</p>
    </div>
    <div class="summary-card">
        <h4>Pending Tasks</h4>
        <p style="color:#ffc107;">4 Tasks</p>
    </div>
    <div class="summary-card">
        <h4>Completed Tasks</h4>
        <p style="color:#007bff;">42 Tasks</p>
    </div>
</div>
@endsection
