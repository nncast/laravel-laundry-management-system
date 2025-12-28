@extends('layouts.app')

@section('title', 'Order Report')
@section('page-title', 'Order Report')

@section('content')
<style>
:root {
    --blue: #007bff;
    --green: #28a745;
    --text-dark: #2c3e50;
    --text-light: #6c757d;
    --shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    --transition: all 0.3s ease;
}

.report-section {
    background: #fff;
    border-radius: 10px;
    padding: 25px;
    box-shadow: var(--shadow);
    margin-bottom: 30px;
}

.filters {
    display: flex;
    align-items: flex-end;
    gap: 20px;
    flex-wrap: wrap;
    margin-bottom: 25px;
}

.filter-group label {
    display: block;
    font-weight: 500;
    margin-bottom: 6px;
    color: var(--text-dark);
}

.filter-group input,
.filter-group select {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-family: 'Poppins', sans-serif;
    width: 220px;
    transition: var(--transition);
}

.filter-group input:focus,
.filter-group select:focus {
    outline: none;
    border-color: var(--blue);
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.generate-btn {
    background: #fff3cd;
    color: #856404;
    padding: 10px 18px;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
}

.generate-btn:hover {
    opacity: 0.85;
}

.table-wrapper {
    width: 100%;
    overflow-x: auto;
    border-radius: 8px;
}

.report-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
    min-width: 800px;
}

.report-table th,
.report-table td {
    text-align: left;
    padding: 12px 15px;
    white-space: nowrap;
}

.report-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: var(--text-dark);
    border-bottom: 1px solid #dee2e6;
    position: sticky;
    top: 0;
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
    transition: var(--transition);
    font-family: 'Poppins', sans-serif;
    display: flex;
    align-items: center;
    gap: 6px;
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

@media (max-width: 768px) {
    .filters {
        flex-direction: column;
        align-items: stretch;
    }

    .filter-group input,
    .filter-group select,
    .generate-btn {
        width: 100%;
    }

    .buttons {
        flex-direction: column;
    }

    .buttons button {
        width: 100%;
        justify-content: center;
    }

    .report-table {
        min-width: 700px;
    }
}
</style>

<div class="report-section">
    <div class="filters">
        <div class="filter-group">
            <label>Start Date</label>
            <input type="date" value="{{ date('Y-m-01') }}">
        </div>
        <div class="filter-group">
            <label>End Date</label>
            <input type="date" value="{{ date('Y-m-t') }}">
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

    <div class="table-wrapper">
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
                @php
                    $sampleOrders = [
                        ['order_number'=>'ORD-1001','date'=>'2025-12-01','customer'=>'John Doe','service'=>'Wash & Fold','amount'=>25.50,'status'=>'Completed'],
                        ['order_number'=>'ORD-1002','date'=>'2025-12-02','customer'=>'Jane Smith','service'=>'Dry Cleaning','amount'=>40.00,'status'=>'Processing'],
                        ['order_number'=>'ORD-1003','date'=>'2025-12-03','customer'=>'Mike Johnson','service'=>'Ironing','amount'=>15.00,'status'=>'Pending'],
                        ['order_number'=>'ORD-1004','date'=>'2025-12-04','customer'=>'Alice Brown','service'=>'Wash & Fold','amount'=>30.25,'status'=>'Completed'],
                        ['order_number'=>'ORD-1005','date'=>'2025-12-05','customer'=>'Bob White','service'=>'Dry Cleaning','amount'=>50.00,'status'=>'Cancelled'],
                    ];
                @endphp

                @foreach($sampleOrders as $order)
                <tr>
                    <td>{{ $order['order_number'] }}</td>
                    <td>{{ $order['date'] }}</td>
                    <td>{{ $order['customer'] }}</td>
                    <td>{{ $order['service'] }}</td>
                    <td>{{ number_format($order['amount'],2) }}</td>
                    <td>{{ $order['status'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="report-footer">
        <div>Total Orders: {{ count($sampleOrders) }}</div>
        <div>Total Amount: <b>{{ number_format(array_sum(array_column($sampleOrders,'amount')),2) }} USD</b></div>
    </div>

    <div class="buttons">
        <button class="download"><i class="fas fa-download"></i> Download Report</button>
        <button class="print"><i class="fas fa-print"></i> Print Report</button>
    </div>
</div>
@endsection
