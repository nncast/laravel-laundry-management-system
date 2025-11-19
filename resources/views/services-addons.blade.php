@extends('layouts.app')

@section('title', 'Addons')
@section('page-title', 'Addons')
@section('active-services-addons', 'active')

@section('content')
<style>
/* --- Addons Page Styles --- */
.addons-section {
    background: #fff;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.addons-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.addons-header input {
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    width: 220px;
    font-family: 'Poppins', sans-serif;
}

.addons-header button {
    background: var(--blue);
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 10px 18px;
    font-family: 'Poppins', sans-serif;
    font-weight: 500;
    cursor: pointer;
    transition: 0.2s;
}

.addons-header button i {
    margin-right: 6px;
}

.addons-header button:hover {
    background: #0056b3;
}

/* Table */
.addons-table {
    width: 100%;
    border-collapse: collapse;
}

.addons-table th, 
.addons-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #f1f1f1;
    font-size: 14px;
}

.addons-table th {
    background: #f8f9fa;
    color: var(--text-dark);
    font-weight: 600;
}

.status {
    background: #d4edda;
    color: #155724;
    font-weight: 500;
    padding: 5px 12px;
    border-radius: 6px;
    font-size: 13px;
    text-align: center;
    display: inline-block;
}

.action-btn {
    display: flex;
    gap: 8px;
}

.edit-btn {
    background: rgba(0,123,255,0.1);
    color: var(--blue);
    padding: 8px;
    border-radius: 6px;
}

.delete-btn {
    background: rgba(255,0,0,0.1);
    color: #d9534f;
    padding: 8px;
    border-radius: 6px;
}
</style>

<div class="addons-section">
    <div class="addons-header">
        <input type="text" placeholder="Search Here">
        <button><i class="fas fa-plus"></i> Add New Addon</button>
    </div>

    <table class="addons-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Addon</th>
                <th>Price</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Delivery services to Adenta</td>
                <td>10.00 USD</td>
                <td><span class="status">Active</span></td>
                <td class="action-btn">
                    <span class="edit-btn"><i class="fas fa-pen"></i></span>
                    <span class="delete-btn"><i class="fas fa-trash"></i></span>
                </td>
            </tr>
            <tr>
                <td>2</td>
                <td>XXX Mat</td>
                <td>15.00 USD</td>
                <td><span class="status">Active</span></td>
                <td class="action-btn">
                    <span class="edit-btn"><i class="fas fa-pen"></i></span>
                    <span class="delete-btn"><i class="fas fa-trash"></i></span>
                </td>
            </tr>
            <tr>
                <td>3</td>
                <td>Ølave</td>
                <td>20.00 USD</td>
                <td><span class="status">Active</span></td>
                <td class="action-btn">
                    <span class="edit-btn"><i class="fas fa-pen"></i></span>
                    <span class="delete-btn"><i class="fas fa-trash"></i></span>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
