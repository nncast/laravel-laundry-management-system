@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('active-dashboard', 'active')

@section('content')

<style>
/* Dashboard CSS - Modern & Responsive */
:root {
    --primary-color: var(--accent);
    --primary-dark: var(--accent-hover);
    --secondary-color: var(--text-muted);
    --success-color: var(--success);
    --danger-color: var(--danger);
    --warning-color: var(--warning);
    --info-color: var(--info);
    --light-color: var(--surface-muted);
    --dark-color: var(--text);
    --white: #ffffff;
    --gray-light: var(--surface-sunken);
    --border-color: var(--border);
    --shadow-md: 0 4px 12px rgba(0,0,0,0.15);
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
    color: rgba(37, 99, 235, 0.1);
}

/* Main Content Grid */
.main-content-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr);
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
    background: rgba(37, 99, 235, 0.1);
    color: var(--primary-color);
}

.status-completed {
    background: rgba(22, 163, 74, 0.1);
    color: var(--success-color);
}

.status-cancelled {
    background: rgba(220, 38, 38, 0.1);
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

    .dashboard-container {
        padding: 0;
    }

    .chart-section {
        padding: 16px 12px;
    }

    .chart-section h2 {
        font-size: 18px;
    }

    .chart-legend {
        gap: 12px;
        flex-wrap: wrap;
        justify-content: center;
        font-size: 12px;
    }

    .chart-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 6px;
    }

    .chart-actions .chart-btn {
        flex: 1 1 0;
        min-width: 0;
        padding: 8px 6px;
        font-size: 12px;
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

/* Recent orders: keep the order number on one line */
.recent-orders .dashboard-table th,
.recent-orders .dashboard-table td {
    padding: 12px 10px;
    font-size: 13px;
}
.recent-orders .dashboard-table .order-no,
.recent-orders .dashboard-table td:last-child {
    white-space: nowrap;
    font-weight: 600;
}
.recent-orders .status-badge {
    padding: 4px 8px;
    font-size: 10px;
}

/* Wide tables scroll inside their card instead of stretching the page */
.recent-orders,
.services-section {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    min-width: 0;
}
.main-content-grid > *,
.bottom-grid > * {
    min-width: 0;
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

/* ---- Soap Opera dashboard ---- */
.welcome-banner {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 20px;
    padding: 28px 32px;
    min-height: 220px;
    border-radius: var(--radius-lg);
    background: var(--grad-brand);
    color: #fff;
    overflow: hidden;
    box-shadow: 0 18px 40px -20px rgba(12, 92, 224, 0.75);
}
.welcome-banner::before, .welcome-banner::after {
    content: ""; position: absolute; border-radius: 50%; pointer-events: none;
    background: radial-gradient(circle at 35% 30%, rgba(255,255,255,0.35), rgba(255,255,255,0.06) 60%, rgba(255,255,255,0) 70%);
}
.welcome-banner::before { width: 340px; height: 340px; right: -60px; top: -150px; }
.welcome-banner::after { width: 220px; height: 220px; left: 38%; bottom: -150px; }
.welcome-copy { position: relative; z-index: 1; max-width: 560px; }
.welcome-eyebrow {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 12px; border-radius: var(--radius-pill);
    background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.3);
    font-size: 12px; font-weight: 700; letter-spacing: 0.02em;
}
.welcome-banner h1 { margin: 12px 0 4px; font-size: 28px; font-weight: 800; letter-spacing: -0.02em; line-height: 1.2; }
.welcome-banner p { font-size: 14.5px; opacity: 0.9; }
.welcome-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; }
.welcome-chip {
    padding: 6px 12px; border-radius: var(--radius-pill);
    background: rgba(255,255,255,0.16); border: 1px solid rgba(255,255,255,0.28);
    font-size: 13px; -webkit-backdrop-filter: blur(6px); backdrop-filter: blur(6px);
}
.welcome-chip b { font-weight: 800; margin-right: 2px; }
.welcome-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 18px; }
.welcome-btn {
    display: inline-flex; align-items: center; gap: 8px;
    height: 42px; padding: 0 20px; border-radius: var(--radius-pill);
    font-size: 14px; font-weight: 700; text-decoration: none; transition: var(--transition);
}
.welcome-btn-light { background: #fff; color: var(--accent); box-shadow: 0 2px 8px -3px rgba(0,30,90,0.35); }
.welcome-btn-light:hover { transform: translateY(-1px); background: #f4f9ff; }
.welcome-btn-glass { color: #fff; background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.35); }
.welcome-btn-glass:hover { background: rgba(255,255,255,0.24); }
.welcome-art {
    position: relative; z-index: 1; flex-shrink: 0;
    width: 300px; height: auto; margin: -24px -8px -36px 0;
    animation: bubble-float 6s ease-in-out infinite;
}
@keyframes bubble-float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
@media (prefers-reduced-motion: reduce) { .welcome-art { animation: none; } }

.stats-grid { gap: 16px; margin-bottom: 20px; }
.stat-card {
    border: 1px solid var(--border); border-left: 1px solid var(--border) !important;
    border-radius: var(--radius-lg) !important;
    box-shadow: var(--shadow); padding: 22px 24px;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow), 0 16px 30px -18px rgba(16,64,140,0.35); }
.stat-card::before { display: none; }
.stat-card h2 { font-size: 13px; font-weight: 600; text-transform: none; letter-spacing: 0; color: var(--text-muted); margin-bottom: 6px; }
.stat-value { font-size: 30px; font-weight: 800; letter-spacing: -0.02em; color: var(--text) !important; }
.stat-trend { font-size: 12.5px; gap: 6px; }
.stat-icon.bubble-tile { position: absolute; top: 20px; right: 20px; width: 46px; height: 46px; border-radius: 15px; font-size: 18px; color: #fff; }

.main-content-grid { grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr); gap: 20px; }
.chart-section, .recent-orders, .services-section, .income-card {
    border: 1px solid var(--border); border-radius: var(--radius-lg) !important; box-shadow: var(--shadow); padding: 24px;
}
.chart-section h2, .recent-orders h2, .services-section h2 { font-size: 16px; font-weight: 800; letter-spacing: -0.01em; }
.chart-section h2 i, .recent-orders h2 i, .services-section h2 i { color: var(--accent-light); }

.chart-actions { gap: 4px; padding: 4px; border: 0; border-radius: var(--radius-pill); background: var(--surface-sunken); width: fit-content; margin-left: auto; margin-right: auto; }
.chart-btn { border: 0 !important; border-radius: var(--radius-pill) !important; background: transparent !important; color: var(--text-muted) !important; padding: 7px 16px !important; font-size: 13px; font-weight: 600; }
.chart-btn.active { background: var(--surface) !important; color: var(--accent) !important; box-shadow: 0 1px 2px rgba(16,64,140,0.12), 0 4px 10px -6px rgba(16,64,140,0.3); }

.recent-orders .dashboard-table th, .recent-orders .dashboard-table td { padding: 10px 8px; }
.recent-orders .dashboard-table td { font-size: 13px; }
.recent-orders .dashboard-table .order-no { font-size: 12.5px; }
.recent-orders .status-badge { font-size: 11px; padding: 2px 8px; }

@media (max-width: 900px) {
    .welcome-art { width: 200px; margin: -10px -10px -20px 0; }
}
@media (max-width: 640px) {
    .welcome-banner { padding: 22px 20px; min-height: 0; }
    .welcome-banner h1 { font-size: 23px; }
    .welcome-art { position: absolute; right: -36px; top: -22px; width: 150px; opacity: 0.55; margin: 0; }
    .welcome-copy { max-width: none; }
}
</style>

<div class="dashboard-container">
    @php
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
        $firstName = strtok(session('staff.name') ?? 'there', ' ');
    @endphp
    <section class="welcome-banner">
        <div class="welcome-copy">
            <span class="welcome-eyebrow"><i class="fas fa-soap"></i> Soap Opera</span>
            <h1>{{ $greeting }}, {{ $firstName }}!</h1>
            <p>Fresh loads, zero drama. Here's what's bubbling today.</p>
            <div class="welcome-chips">
                <span class="welcome-chip"><b>{{ $todayPending }}</b> pending</span>
                <span class="welcome-chip"><b>{{ $todayProcessing }}</b> in progress</span>
                <span class="welcome-chip"><b>{{ $todayCompleted }}</b> completed</span>
            </div>
            @if(in_array(session('staff.role'), ['cashier', 'manager', 'admin']))
            <div class="welcome-actions">
                <a href="{{ route('pos.index') }}" class="welcome-btn welcome-btn-light"><i class="fas fa-plus"></i> New order</a>
                <a href="{{ route('orders.index') }}" class="welcome-btn welcome-btn-glass">View orders <i class="fas fa-arrow-right"></i></a>
            </div>
            @endif
        </div>
        <img class="welcome-art" src="{{ asset('images/brand/bubbles.svg') }}" alt="" width="300" height="257">
    </section>
    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon bubble-tile tone-blue">
                <i class="fas fa-basket-shopping"></i>
            </div>
            <h2>Total Orders</h2>
            <div class="stat-value">{{ number_format($totalOrders) }}</div>
            <div class="stat-trend {{ $ordersTrend >= 0 ? 'positive' : 'negative' }}">
                <i class="fas fa-arrow-{{ $ordersTrend >= 0 ? 'up' : 'down' }}"></i>
                <span>{{ number_format(abs($ordersTrend), 1) }}% {{ $ordersTrend >= 0 ? 'more' : 'fewer' }} orders than last month</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bubble-tile tone-amber">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <h2>Pending Orders</h2>
            <div class="stat-value">{{ number_format($pendingOrders) }}</div>
            <div class="stat-trend {{ $pendingTrend >= 0 ? 'positive' : 'negative' }}">
                <i class="fas fa-arrow-{{ $pendingTrend >= 0 ? 'up' : 'down' }}"></i>
                <span>{{ number_format(abs($pendingTrend), 1) }}% from yesterday</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bubble-tile tone-aqua">
                <i class="fas fa-peso-sign"></i>
            </div>
            <h2>Total Revenue</h2>
            <div class="stat-value">₱{{ number_format($totalRevenue, 2) }}</div>
            <div class="stat-trend {{ $revenueTrend >= 0 ? 'positive' : 'negative' }}">
                <i class="fas fa-arrow-{{ $revenueTrend >= 0 ? 'up' : 'down' }}"></i>
                <span>{{ number_format(abs($revenueTrend), 1) }}% {{ $revenueTrend >= 0 ? 'increase' : 'decrease' }} vs. last month</span>
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
                    <div class="legend-color" style="background: var(--accent);"></div>
                    <span>Total Sales (₱)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: var(--info);"></div>
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
                        <td class="order-no">{{ $order->order_number }}</td>
                        <td>{{ $order->customer->name ?? 'Walk-in Customer' }}</td>
                        <td><span class="status-badge status-{{ $order->status }}">{{ ucfirst($order->status) }}</span></td>
                        <td>₱{{ number_format($order->total, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 20px; color: var(--text-faint);">No recent orders</td>
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
                        <th>Price</th>
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
                        <td colspan="4" style="text-align: center; padding: 20px; color: var(--text-faint);">No service data available</td>
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

@push('scripts')
<!-- Chart.js (pinned version, deferred so it never blocks page rendering) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js" defer></script>
<script>
window.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') {
        document.querySelector('.chart-container').innerHTML =
            '<p style="color:var(--text-faint);text-align:center;padding:40px 0;">Chart could not be loaded (offline?).</p>';
        return;
    }

    // Initial chart data from PHP
    const chartData = @json($chartData);
    
    // Get canvas context
    const ctx = document.getElementById('salesChart').getContext('2d');
    
    // Format currency
    const formatCurrency = (value) => {
        return '₱' + Number(value || 0).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    };
    
    const formatCompact = (value) => {
        const n = Number(value || 0);
        if (Math.abs(n) >= 1000000) return '₱' + +(n / 1000000).toFixed(1) + 'M';
        if (Math.abs(n) >= 1000) return '₱' + +(n / 1000).toFixed(1) + 'k';
        return '₱' + n;
    };

    // Phones have little room: shorter tick labels, no axis titles, no tilted labels
    const isSmallScreen = () => window.innerWidth < 576;

    function applyResponsiveOptions(chart) {
        const small = isSmallScreen();
        const { x, y, y1 } = chart.options.scales;

        y.title.display = !small;
        y1.title.display = !small;
        y.ticks.callback = (value) => small ? formatCompact(value) : formatCurrency(value);
        y.ticks.maxTicksLimit = small ? 5 : 8;
        y1.ticks.maxTicksLimit = small ? 5 : 8;

        [x, y, y1].forEach(axis => { axis.ticks.font = { size: small ? 10 : 12 }; });
        x.ticks.maxRotation = 0;
        x.ticks.autoSkip = true;
        x.ticks.autoSkipPadding = small ? 8 : 4;

        chart.data.datasets.forEach(ds => {
            ds.pointRadius = small ? 2 : 3;
            ds.borderWidth = small ? 1.5 : 2;
        });
    }

    // Create chart
    const chartConfig = {
        type: 'line',
        data: {
            labels: chartData.week.labels,
            datasets: [
                {
                    label: 'Total Sales (₱)',
                    data: chartData.week.sales,
                    borderColor: '#1f74f0',
                    backgroundColor: 'rgba(31, 116, 240, 0.10)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y'
                },
                {
                    label: 'Orders Count',
                    data: chartData.week.orders,
                    borderColor: '#12a6d8',
                    backgroundColor: 'rgba(18, 166, 216, 0.08)',
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
                        color: '#5f7391'
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Sales (₱)',
                        color: '#1f74f0'
                    },
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        color: '#1f74f0',
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
                        color: '#12a6d8'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                    beginAtZero: true,
                    ticks: {
                        color: '#12a6d8',
                        precision: 0
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.92)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#2563eb',
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
    };

    // Apply the phone/desktop settings BEFORE creating the chart: changing them
    // afterwards is overridden by the chart's opening animation, which left the
    // points out of line with the day labels on phones.
    applyResponsiveOptions(chartConfig);
    let salesChart = new Chart(ctx, chartConfig);

    let resizeTimer;
    let wasSmall = isSmallScreen();
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            if (isSmallScreen() === wasSmall) return;
            wasSmall = isSmallScreen();
            salesChart.stop(); // cancel any running animation first
            applyResponsiveOptions(salesChart);
            salesChart.update('none');
        }, 150);
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
            fetch(appUrl(`dashboard/chart-data/${period}`), { headers: { 'Accept': 'application/json' } })
                .then(response => { if (!response.ok) throw new Error('HTTP ' + response.status); return response.json(); })
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
        if (document.hidden) return; // don't poll while the tab is in the background
        fetch(appUrl('dashboard/stats'), { headers: { 'Accept': 'application/json' } })
            .then(response => { if (!response.ok) throw new Error('HTTP ' + response.status); return response.json(); })
            .then(data => {
                if (data.success) {
                    const stats = data.stats;
                    
                    // Update stat cards
                    document.querySelector('.stat-card:nth-child(1) .stat-value').textContent = 
                        Number(stats.totalOrders).toLocaleString();
                    
                    document.querySelector('.stat-card:nth-child(2) .stat-value').textContent = 
                        Number(stats.pendingOrders).toLocaleString();
                    
                    document.querySelector('.stat-card:nth-child(3) .stat-value').textContent = 
                        formatCurrency(stats.totalRevenue);
                    
                    // Update income card
                    document.querySelector('.income-card .income-value').textContent = 
                        formatCurrency(stats.todayRevenue);
                    
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
});
</script>
@endpush
@endsection