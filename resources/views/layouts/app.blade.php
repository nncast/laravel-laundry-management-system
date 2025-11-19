<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') | Fitters Laundry</title>

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

    <!-- Sidebar -->
<aside class="sidebar">
    <div class="logo">
        <i class="fas fa-soap"></i> FITTERS LAUNDRY
    </div>

    <nav class="menu">
        @php
            $role = session('staff.role'); // get logged-in staff role
        @endphp

        <span class="menu-label">Dashboard</span>
        <a href="{{ url('home') }}" class="{{ request()->is('home') ? 'active' : '' }}">
            <i class="fas fa-gauge"></i> Dashboard
        </a>

        @if($role === 'cashier' || $role === 'manager' || $role === 'admin')
        <span class="menu-label">Orders</span>
        <a href="{{ url('orders') }}" class="{{ request()->is('orders*') ? 'active' : '' }}">
            <i class="fas fa-receipt"></i> Orders
        </a>
        @endif

        @if($role === 'cashier' || $role === 'admin')
        <a href="{{ url('pos') }}" class="{{ request()->is('pos*') ? 'active' : '' }}">
            <i class="fas fa-cash-register"></i> POS
        </a>
        @endif

        @if($role === 'cashier' || $role === 'manager' || $role === 'admin')
        <span class="menu-label">Application</span>
        <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">
            <i class="fas fa-users"></i> Customers
        </a>
        @endif

        @if($role === 'manager' || $role === 'admin')
        <!-- Inventory -->
        <a href="#" class="has-sub {{ request()->is('inventory-*') ? 'active' : '' }}">
            <i class="fas fa-boxes"></i> Inventory
            <i class="fas fa-chevron-right toggle-icon"></i>
        </a>
        <div class="submenu" style="{{ request()->is('inventory-*') ? 'max-height:500px;' : '' }}">
            <a href="{{ url('inventory/products') }}" class="{{ request()->is('inventory/products*') ? 'active-sub' : '' }}">Products</a>
            <a href="{{ url('inventory/categories') }}" class="{{ request()->is('inventory/categories*') ? 'active-sub' : '' }}">Categories</a>
            <a href="{{ url('inventory/units') }}" class="{{ request()->is('inventory/units*') ? 'active-sub' : '' }}">Units</a>
        </div>

        <!-- Services -->
        <a href="#" class="has-sub {{ request()->is('services-*') ? 'active' : '' }}">
            <i class="fas fa-tags"></i> Services
            <i class="fas fa-chevron-right toggle-icon"></i>
        </a>
        <div class="submenu" style="{{ request()->is('services-*') ? 'max-height:500px;' : '' }}">
            <a href="{{ url('services/list') }}" class="{{ request()->is('services-list*') ? 'active-sub' : '' }}">Service List</a>
            <a href="{{ url('services/type') }}" class="{{ request()->is('services-type*') ? 'active-sub' : '' }}">Service Type</a>
            <a href="{{ url('services/addons') }}" class="{{ request()->is('services-addons*') ? 'active-sub' : '' }}">Addons</a>
        </div>
        @endif

        @if($role === 'admin')
        <!-- Staff -->
        <!-- <a href="#" class="has-sub {{ request()->is('staff-*') ? 'active' : '' }}">
            <i class="fas fa-user-gear"></i> Staff
            <i class="fas fa-chevron-right toggle-icon"></i>
        </a>
        <div class="submenu" style="{{ request()->is('staff-*') ? 'max-height:500px;' : '' }}">
            <a href="{{ url('staff/cashier') }}" class="{{ request()->is('staff/cashier*') ? 'active-sub' : '' }}">Cashier</a>
            <a href="{{ url('staff/manager') }}" class="{{ request()->is('staff/manager*') ? 'active-sub' : '' }}">Manager</a>
            <a href="{{ url('staff/admin') }}" class="{{ request()->is('staff/admin*') ? 'active-sub' : '' }}">Administrator</a>
        </div> -->

        <!-- Reports -->
        <a href="#" class="has-sub {{ request()->is('reports-*') ? 'active' : '' }}">
            <i class="fas fa-chart-column"></i> Reports
            <i class="fas fa-chevron-right toggle-icon"></i>
        </a>
        <div class="submenu" style="{{ request()->is('reports-*') ? 'max-height:500px;' : '' }}">
            <a href="{{ url('reports/daily') }}" class="{{ request()->is('reports-daily*') ? 'active-sub' : '' }}">Daily Report</a>
            <a href="{{ url('reports/sales') }}" class="{{ request()->is('reports-sales*') ? 'active-sub' : '' }}">Sales Report</a>
            <a href="{{ url('reports/order') }}" class="{{ request()->is('reports-order*') ? 'active-sub' : '' }}">Order Report</a>
        </div>

        <!-- Settings -->
        <span class="menu-label">Account</span>
        <a href="#" class="has-sub {{ request()->is('settings-*') ? 'active' : '' }}">
            <i class="fas fa-gear"></i> Settings
            <i class="fas fa-chevron-right toggle-icon"></i>
        </a>
        <div class="submenu" style="{{ request()->is('settings-*') ? 'max-height:500px;' : '' }}">
            <a href="{{ url('settings/filetools') }}" class="{{ request()->is('settings-filetools*') ? 'active-sub' : '' }}">File Tools</a>
            <a href="{{ url('staff/admin') }}" class="{{ request()->is('staff/admin*') ? 'active-sub' : '' }}">Staff</a>
            <a href="{{ url('settings/mastersettings') }}" class="{{ request()->is('settings-mastersettings*') ? 'active-sub' : '' }}">Master Setting</a>
        </div>
        @endif

        <!-- Logout -->
        <form method="POST" action="{{ route('logout') }}" class="logout-form">
            @csrf
            <button type="submit" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </form>
    </nav>
</aside>


    <!-- Topbar -->
    <header class="topbar">
        <h2>@yield('page-title', 'Dashboard')</h2>
        <div class="top-icons">
            <a href="#" title="Add New Customer"><i class="fas fa-user-plus"></i></a>
            <a href="#" title="Manage Services"><i class="fas fa-concierge-bell"></i></a>
            <a href="#" title="Manage Customers"><i class="fas fa-users"></i></a>
            <a href="#" title="Account"><i class="fas fa-user-circle"></i></a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- JS -->
    <script src="{{ asset('js/sidebar.js') }}" defer></script>
</body>
</html>
