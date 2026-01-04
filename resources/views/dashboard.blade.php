@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('active-dashboard', 'active')

@section('content')

<style>
/* Dashboard CSS - Modern & Responsive */
:root {
    --primary-color: #007bff;
    --primary-dark: #0056b3;
    --secondary-color: #6c757d;
    --success-color: #28a745;
    --danger-color: #dc3545;
    --warning-color: #ffc107;
    --info-color: #17a2b8;
    --light-color: #f8f9fa;
    --dark-color: #343a40;
    --white: #ffffff;
    --gray-light: #e9ecef;
    --border-color: #dee2e6;
    --shadow-sm: 0 2px 4px rgba(0,0,0,0.1);
    --shadow-md: 0 4px 12px rgba(0,0,0,0.15);
    --shadow-lg: 0 8px 24px rgba(0,0,0,0.2);
    --radius-sm: 8px;
    --radius-md: 12px;
    --radius-lg: 16px;
    --transition: all 0.3s ease;
}

/* Dashboard Container */
.dashboard-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: var(--white);
    border-radius: var(--radius-md);
    padding: 25px;
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
    border-left: 4px solid var(--primary-color);
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
}

.stat-card:nth-child(2) {
    border-left-color: var(--warning-color);
}

.stat-card:nth-child(3) {
    border-left-color: var(--success-color);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-color), transparent);
    opacity: 0.3;
}

.stat-card h2 {
    font-size: 16px;
    font-weight: 600;
    color: var(--secondary-color);
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-value {
    font-size: 36px;
    font-weight: 700;
    color: var(--dark-color);
    margin-bottom: 5px;
    line-height: 1.2;
}

.stat-trend {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 500;
    color: var(--secondary-color);
}

.stat-trend.positive {
    color: var(--success-color);
}

.stat-trend.negative {
    color: var(--danger-color);
}

.stat-icon {
    position: absolute;
    top: 25px;
    right: 25px;
    font-size: 40px;
    color: rgba(0, 123, 255, 0.1);
}

/* Main Content Grid */
.main-content-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    margin-bottom: 40px;
}

/* Chart Section */
.chart-section {
    background: var(--white);
    border-radius: var(--radius-md);
    padding: 30px;
    box-shadow: var(--shadow-sm);
}

.chart-section h2 {
    font-size: 20px;
    font-weight: 600;
    color: var(--dark-color);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.chart-section h2 i {
    color: var(--primary-color);
}

.chart-container {
    height: 300px;
    position: relative;
    border-radius: var(--radius-sm);
    overflow: hidden;
}

.chart-legend {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 15px;
    flex-wrap: wrap;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: var(--secondary-color);
}

.legend-color {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.chart-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 15px;
}

.chart-btn {
    padding: 6px 12px;
    border: 1px solid var(--border-color);
    background: var(--white);
    border-radius: 4px;
    font-size: 12px;
    color: var(--secondary-color);
    cursor: pointer;
    transition: var(--transition);
}

.chart-btn:hover, .chart-btn.active {
    background: var(--primary-color);
    color: var(--white);
    border-color: var(--primary-color);
}

/* Recent Orders Section */
.recent-orders {
    background: var(--white);
    border-radius: var(--radius-md);
    padding: 30px;
    box-shadow: var(--shadow-sm);
}

.recent-orders h2 {
    font-size: 20px;
    font-weight: 600;
    color: var(--dark-color);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.recent-orders h2 i {
    color: var(--warning-color);
}

/* Tables */
.dashboard-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.dashboard-table thead {
    background: var(--light-color);
}

.dashboard-table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: var(--dark-color);
    border-bottom: 2px solid var(--border-color);
    white-space: nowrap;
}

.dashboard-table td {
    padding: 15px;
    border-bottom: 1px solid var(--border-color);
    color: var(--dark-color);
}

.dashboard-table tbody tr {
    transition: var(--transition);
}

.dashboard-table tbody tr:hover {
    background: var(--light-color);
}

/* Status Badges */
.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-pending {
    background: rgba(255, 193, 7, 0.1);
    color: var(--warning-color);
}

.status-processing {
    background: rgba(0, 123, 255, 0.1);
    color: var(--primary-color);
}

.status-completed {
    background: rgba(40, 167, 69, 0.1);
    color: var(--success-color);
}

.status-cancelled {
    background: rgba(220, 53, 69, 0.1);
    color: var(--danger-color);
}

/* Bottom Grid */
.bottom-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    margin-bottom: 40px;
}

/* Services Section */
.services-section {
    background: var(--white);
    border-radius: var(--radius-md);
    padding: 30px;
    box-shadow: var(--shadow-sm);
}

.services-section h2 {
    font-size: 20px;
    font-weight: 600;
    color: var(--dark-color);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.services-section h2 i {
    color: var(--info-color);
}

/* Income Card */
.income-card {
    background: var(--white);
    border-radius: var(--radius-md);
    padding: 30px;
    box-shadow: var(--shadow-sm);
    text-align: center;
    background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
    color: var(--white);
}

.income-card h2 {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 20px;
    opacity: 0.9;
}

.income-card .income-value {
    font-size: 48px;
    font-weight: 700;
    margin-bottom: 10px;
}

.income-card .income-subtitle {
    font-size: 14px;
    opacity: 0.8;
    margin-bottom: 20px;
}

.income-stats {
    display: flex;
    justify-content: space-around;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.income-stat {
    text-align: center;
}

.income-stat .stat-label {
    font-size: 12px;
    opacity: 0.8;
    margin-bottom: 5px;
}

.income-stat .stat-value {
    font-size: 18px;
    font-weight: 600;
}

/* Divider */
hr {
    border: none;
    height: 1px;
    background: var(--border-color);
    margin: 30px 0;
    opacity: 0.5;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .main-content-grid,
    .bottom-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    }
}

@media (max-width: 768px) {
    .dashboard-container {
        padding: 15px;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .stat-card {
        padding: 20px;
    }
    
    .stat-value {
        font-size: 32px;
    }
    
    .chart-container {
        height: 250px;
    }
    
    .chart-section,
    .recent-orders,
    .services-section,
    .income-card {
        padding: 20px;
    }
    
    .dashboard-table th,
    .dashboard-table td {
        padding: 12px 10px;
    }
    
    .income-card .income-value {
        font-size: 40px;
    }
}

@media (max-width: 576px) {
    .stat-value {
        font-size: 28px;
    }
    
    .stat-icon {
        font-size: 32px;
        top: 20px;
        right: 20px;
    }
    
    .chart-container {
        height: 200px;
    }
    
    .income-card .income-value {
        font-size: 32px;
    }
    
    .income-stats {
        flex-direction: column;
        gap: 15px;
    }
    
    .dashboard-table {
        font-size: 13px;
    }
    
    .dashboard-table th,
    .dashboard-table td {
        padding: 10px 8px;
    }
}

/* Print Styles */
@media print {
    .dashboard-container {
        padding: 10px;
        max-width: 100%;
    }
    
    .stat-card,
    .chart-section,
    .recent-orders,
    .services-section,
    .income-card {
        box-shadow: none;
        border: 1px solid var(--border-color);
        page-break-inside: avoid;
    }
    
    .stat-card:hover {
        transform: none;
    }
}
</style>

<div class="dashboard-container">
    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <h2>Total Orders</h2>
            <div class="stat-value">{{ number_format($totalOrders) }}</div>
            <div class="stat-trend {{ $ordersTrend >= 0 ? 'positive' : 'negative' }}">
                <i class="fas fa-arrow-{{ $ordersTrend >= 0 ? 'up' : 'down' }}"></i>
                <span>{{ number_format(abs($ordersTrend), 1) }}% {{ $ordersTrend >= 0 ? 'increase' : 'decrease' }} from last month</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <h2>Pending Orders</h2>
            <div class="stat-value">{{ number_format($pendingOrders) }}</div>
            <div class="stat-trend {{ $pendingTrend >= 0 ? 'positive' : 'negative' }}">
                <i class="fas fa-arrow-{{ $pendingTrend >= 0 ? 'up' : 'down' }}"></i>
                <span>{{ number_format(abs($pendingTrend), 1) }}% from yesterday</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <h2>Total Revenue</h2>
            <div class="stat-value">₱{{ number_format($totalRevenue, 2) }}</div>
            <div class="stat-trend {{ $revenueTrend >= 0 ? 'positive' : 'negative' }}">
                <i class="fas fa-arrow-{{ $revenueTrend >= 0 ? 'up' : 'down' }}"></i>
                <span>{{ number_format(abs($revenueTrend), 1) }}% {{ $revenueTrend >= 0 ? 'increase' : 'decrease' }} from last month</span>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="main-content-grid">
        <!-- Chart Section -->
        <div class="chart-section">
            <h2><i class="fas fa-chart-line"></i> Sales Overview</h2>
            <div class="chart-container">
                <canvas id="salesChart"></canvas>
            </div>
            <div class="chart-legend">
                <div class="legend-item">
                    <div class="legend-color" style="background: #007bff;"></div>
                    <span>Total Sales (₱)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #28a745;"></div>
                    <span>Orders Count</span>
                </div>
            </div>
            <div class="chart-actions">
                <button class="chart-btn active" data-period="week">This Week</button>
                <button class="chart-btn" data-period="month">This Month</button>
                <button class="chart-btn" data-period="year">This Year</button>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="recent-orders">
            <h2><i class="fas fa-history"></i> Recent Orders</h2>
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Client</th>
                        <th>Status</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                    <tr>
                        <td>#{{ $order->order_number }}</td>
                        <td>{{ $order->customer->name ?? 'Walk-in Customer' }}</td>
                        <td><span class="status-badge status-{{ $order->status }}">{{ ucfirst($order->status) }}</span></td>
                        <td>₱{{ number_format($order->total, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 20px; color: #999;">No recent orders</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bottom Grid -->
    <div class="bottom-grid">
        <!-- Services Section -->
        <div class="services-section">
            <h2><i class="fas fa-concierge-bell"></i> Top Services</h2>
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Orders</th>
                        <th>Revenue</th>
                        <th>Avg Price</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topServices as $service)
                    <tr>
                        <td>{{ $service->name }}</td>
                        <td>{{ $service->order_count ?? 0 }}</td>
                        <td>₱{{ number_format($service->total_revenue ?? 0, 2) }}</td>
                        <td>₱{{ number_format($service->price ?? 0, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 20px; color: #999;">No service data available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Income Card -->
        <div class="income-card">
            <h2>Today's Income</h2>
            <div class="income-value">₱{{ number_format($todayRevenue, 2) }}</div>
            <div class="income-subtitle">Total revenue generated today</div>
            
            <div class="income-stats">
                <div class="income-stat">
                    <div class="stat-label">Completed</div>
                    <div class="stat-value">{{ $todayCompleted }}</div>
                </div>
                <div class="income-stat">
                    <div class="stat-label">In Progress</div>
                    <div class="stat-value">{{ $todayProcessing }}</div>
                </div>
                <div class="income-stat">
                    <div class="stat-label">Pending</div>
                    <div class="stat-value">{{ $todayPending }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Font Awesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initial chart data from PHP
    const chartData = @json($chartData);
    
    // Get canvas context
    const ctx = document.getElementById('salesChart').getContext('2d');
    
    // Format currency
    const formatCurrency = (value) => {
        return '₱' + value.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    };
    
    // Create chart
    let salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.week.labels,
            datasets: [
                {
                    label: 'Total Sales (₱)',
                    data: chartData.week.sales,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y'
                },
                {
                    label: 'Orders Count',
                    data: chartData.week.orders,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#6c757d'
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Sales (₱)',
                        color: '#007bff'
                    },
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        color: '#007bff',
                        callback: function(value) {
                            return formatCurrency(value);
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Orders',
                        color: '#28a745'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        color: '#28a745'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#007bff',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.datasetIndex === 0) {
                                label += formatCurrency(context.parsed.y);
                            } else {
                                label += context.parsed.y;
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
    
    // Chart period buttons
    const periodButtons = document.querySelectorAll('.chart-btn');
    periodButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            periodButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            const period = this.dataset.period;
            
            // Fetch new chart data via AJAX
            fetch(`/dashboard/chart-data/${period}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update chart data
                        salesChart.data.labels = data.data.labels;
                        salesChart.data.datasets[0].data = data.data.sales;
                        salesChart.data.datasets[1].data = data.data.orders;
                        salesChart.update('none');
                    }
                })
                .catch(error => {
                    console.error('Error fetching chart data:', error);
                });
        });
    });
    
    // Update dashboard stats every 30 seconds
    function updateDashboardStats() {
        fetch('/dashboard/stats')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const stats = data.stats;
                    
                    // Update stat cards
                    document.querySelector('.stat-card:nth-child(1) .stat-value').textContent = 
                        stats.totalOrders.toLocaleString();
                    
                    document.querySelector('.stat-card:nth-child(2) .stat-value').textContent = 
                        stats.pendingOrders.toLocaleString();
                    
                    document.querySelector('.stat-card:nth-child(3) .stat-value').textContent = 
                        '₱' + stats.totalRevenue.toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    
                    // Update income card
                    document.querySelector('.income-card .income-value').textContent = 
                        '₱' + stats.todayRevenue.toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    
                    document.querySelector('.income-stat:nth-child(1) .stat-value').textContent = 
                        stats.todayCompleted;
                    
                    document.querySelector('.income-stat:nth-child(2) .stat-value').textContent = 
                        stats.todayProcessing;
                    
                    document.querySelector('.income-stat:nth-child(3) .stat-value').textContent = 
                        stats.todayPending;
                }
            })
            .catch(error => {
                console.error('Error updating stats:', error);
            });
    }
    
    // Update stats every 30 seconds
    setInterval(updateDashboardStats, 30000);
    
    // Add hover effect to table rows
    const tableRows = document.querySelectorAll('.dashboard-table tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });
});
</script>
@endsection