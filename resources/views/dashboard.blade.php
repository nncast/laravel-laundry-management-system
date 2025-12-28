@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('active-dashboard', 'active')

@section('content')

<style>
:root {
    --blue: #007bff;
    --sidebar-bg: #ffffff;
    --hover-bg: #007bff;
    --hover-text: #ffffff;
    --text-dark: #2c3e50;
    --text-light: #6c757d;
    --shadow: 0 4px 12px rgba(0,0,0,0.05);
    --transition: all 0.3s ease;
}

/* Reset */
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Poppins', sans-serif; background:#f2f5f7; color:var(--text-dark); line-height:1.5; overflow-x:hidden; }

/* Layout */
.app-container { display:flex; min-height:100vh; }

/* Sidebar */
.sidebar {
    width:280px;
    background:var(--sidebar-bg);
    border-right:1px solid #e0e0e0;
    padding:20px 0;
    position:fixed;
    height:100vh;
    overflow-y:auto;
    z-index:100;
    transition:var(--transition);
}
.logo { font-size:22px; font-weight:700; padding:0 25px 25px; display:flex; align-items:center; gap:10px; color:var(--text-dark); }
.logo i { font-size:26px; color:var(--blue); }

.menu a { display:flex; align-items:center; gap:12px; padding:12px; margin:4px 20px; text-decoration:none; font-size:14px; border-radius:10px; color:var(--text-dark); transition:var(--transition); position:relative; }
.menu-label { display:block; padding:10px 25px 5px; font-size:12px; text-transform:uppercase; color:var(--text-light); font-weight:600; }
.menu a:hover, .menu a.active { background:var(--hover-bg); color:var(--hover-text); }

.submenu { display:flex; flex-direction:column; overflow:hidden; max-height:0; margin:0 25px; transition:max-height 0.35s ease; background:#f9f9f9; border-radius:6px; }
.submenu a { font-size:13px; padding:10px 15px 10px 40px; margin:3px 0; color:#333; border-radius:6px; transition:var(--transition); text-decoration:none; position:relative; }
.submenu a::before { content:"•"; color:var(--blue); position:absolute; left:25px; font-size:12px; }
.submenu a:hover { background:rgba(0,123,255,0.1); color:var(--blue); transform:translateX(5px); }
.submenu a.active-sub { background:rgba(0,123,255,0.15); color:var(--blue); font-weight:600; }
.has-sub.active + .submenu { max-height:400px; padding:5px 0; }

/* Topbars */
.desktop-topbar {
    position:fixed; left:280px; right:0; top:0; height:70px;
    background:#fff; border-bottom:1px solid #e0e0e0;
    display:flex; align-items:center; justify-content:space-between; padding:0 30px; z-index:99; transition:var(--transition);
}
.desktop-topbar h2 { font-size:20px; font-weight:600; color:var(--text-dark); white-space:nowrap; }
.desktop-top-icons { display:flex; align-items:center; gap:15px; }
.desktop-top-icons i { font-size:16px; background:var(--blue); color:#fff; padding:10px; border-radius:50%; cursor:pointer; transition:var(--transition); }
.desktop-top-icons i:hover { background:#0056b3; transform:translateY(-2px); }

.mobile-topbar { display:none; }

/* Main Content */
.main-content {
    margin-left:280px;
    width:calc(100% - 280px);
    padding:90px 30px 30px;
    transition:var(--transition);
}

/* Centered container */
.main-content .container {
    max-width:1200px;
    margin:0 auto;
    width:100%;
}

/* Cards layout */
.row { display:flex; gap:20px; margin-bottom:20px; flex-wrap:wrap; }
.card { background:white; border-radius:12px; padding:20px; flex:1; min-width:230px; box-shadow:var(--shadow); transition:var(--transition); }
.card:hover { transform:translateY(-2px); box-shadow:0 6px 16px rgba(0,0,0,0.1); }
.card h3 { font-size:14px; color:var(--text-light); margin-bottom:8px; }
.big { font-size:28px; font-weight:bold; color:var(--blue); }
.chart { height:200px; background:#f4f9ff; border-radius:10px; display:flex; align-items:center; justify-content:center; color:#aaa; border:1px dashed #ddd; }

table { width:100%; border-collapse:collapse; font-size:14px; }
th, td { padding:10px; border-bottom:1px solid #eee; }
th { color:var(--text-light); text-align:left; font-weight:600; }

/* Responsiveness */
@media (max-width: 992px) {
    .sidebar { width:220px; }
    .desktop-topbar, .main-content { margin-left:220px; width:calc(100% - 220px); }
}
@media (max-width: 768px) {
    .desktop-topbar { display:none; }
    .mobile-topbar { display:flex; position:fixed; left:0; right:0; top:0; height:70px; background:#fff; border-bottom:1px solid #e0e0e0; align-items:center; justify-content:space-between; padding:0 20px; z-index:95; }
    .menu-toggle { display:flex; }
    .sidebar { transform:translateX(-100%); }
    .sidebar.active { transform:translateX(0); }
    .main-content { margin-left:0; width:100%; padding:90px 20px 20px; }
    .sidebar-overlay.active { display:block; }
}
@media (max-width: 576px) { .main-content { padding:80px 15px 15px; } }

</style>

<div class="app-container">
    <div class="main-content">
        <div class="container">

            <div class="row">
                <div class="card">
                    <h3>Total Orders</h3>
                    <div class="big">1,248</div>
                </div>
                <div class="card">
                    <h3>Pending Orders</h3>
                    <div class="big">34</div>
                </div>
                <div class="card">
                    <h3>Total Revenue</h3>
                    <div class="big">₱124,560</div>
                </div>
            </div>

            <div class="row">
                <div class="card" style="flex:2">
                    <h3>Sales Overview</h3>
                    <div class="chart">Chart Area</div>
                </div>

                <div class="card" style="flex:1">
                    <h3>Recent Orders</h3>
                    <table>
                        <tr><th>Order</th><th>Client</th><th>Status</th></tr>
                        <tr><td>#1042</td><td>Anna</td><td>Done</td></tr>
                        <tr><td>#1041</td><td>Mark</td><td>Washing</td></tr>
                        <tr><td>#1040</td><td>Jane</td><td>Ready</td></tr>
                        <tr><td>#1039</td><td>Leo</td><td>Picked Up</td></tr>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="card" style="flex:2">
                    <h3>Top Services</h3>
                    <table>
                        <tr><th>Service</th><th>Duration</th><th>Price</th></tr>
                        <tr><td>Wash & Fold</td><td>24 hrs</td><td>₱80</td></tr>
                        <tr><td>Dry Clean</td><td>48 hrs</td><td>₱150</td></tr>
                        <tr><td>Shoe Wash</td><td>72 hrs</td><td>₱200</td></tr>
                    </table>
                </div>

                <div class="card" style="flex:1">
                    <h3>Today's Income</h3>
                    <div class="big">₱8,240</div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
