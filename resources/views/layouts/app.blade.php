<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $system->business_name ?? 'Dashboard' }}</title>

<!-- Favicon -->
@if(!empty($system->favicon))
<link rel="icon" href="{{ asset($system->favicon) }}" type="image/x-icon">
@endif

<!-- Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Main CSS -->
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>


<body>
<div class="app-container">

    @php $role = session('staff.role'); @endphp

    <div class="sidebar-overlay"></div>

    <aside class="sidebar">
            <div class="logo"><i class="fas fa-soap"></i> {{ $system->business_name ?? 'FITERS LAUNDRY' }}</div>

        <nav class="menu">
            <span class="menu-label">Dashboard</span>
            <a href="{{ url('home') }}" class="{{ request()->is('home') ? 'active' : '' }}"><i class="fas fa-gauge"></i><span>Dashboard</span></a>

            @if(in_array($role,['cashier','manager','admin']))
            <span class="menu-label">Orders</span>
            <a href="{{ url('orders') }}" class="{{ request()->is('orders*') ? 'active' : '' }}"><i class="fas fa-receipt"></i><span>Orders</span></a>
            <a href="{{ url('pos') }}" class="{{ request()->is('pos*') ? 'active' : '' }}"><i class="fas fa-cash-register"></i><span>POS</span></a>
            <span class="menu-label">Application</span>
            <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}"><i class="fas fa-users"></i><span>Customers</span></a>
            @endif

            @if(in_array($role,['manager','admin']))
            <a href="#" class="has-sub {{ request()->is('inventory-*') ? 'active' : '' }}"><i class="fas fa-boxes"></i><span>Inventory</span><i class="fas fa-chevron-right toggle-icon"></i></a>
            <div class="submenu" style="{{ request()->is('inventory-*') ? 'max-height:500px;' : '' }}">
                <a href="{{ url('inventory/products') }}" class="{{ request()->is('inventory/products*') ? 'active-sub' : '' }}">Products</a>
                <a href="{{ url('inventory/categories') }}" class="{{ request()->is('inventory/categories*') ? 'active-sub' : '' }}">Categories</a>
                <a href="{{ url('inventory/units') }}" class="{{ request()->is('inventory/units*') ? 'active-sub' : '' }}">Units</a>
            </div>
            <a href="#" class="has-sub {{ request()->is('services-*') ? 'active' : '' }}"><i class="fas fa-tags"></i><span>Services</span><i class="fas fa-chevron-right toggle-icon"></i></a>
            <div class="submenu" style="{{ request()->is('services-*') ? 'max-height:500px;' : '' }}">
                <a href="{{ url('services/list') }}" class="{{ request()->is('services/list*') ? 'active-sub' : '' }}">Service List</a>
                <a href="{{ url('services/type') }}" class="{{ request()->is('services/type*') ? 'active-sub' : '' }}">Service Type</a>
                <a href="{{ url('services/addons') }}" class="{{ request()->is('services/addons*') ? 'active-sub' : '' }}">Addons</a>
            </div>
            @endif

            @if($role === 'admin')
            <a href="#" class="has-sub {{ request()->is('reports-*') ? 'active' : '' }}"><i class="fas fa-chart-column"></i><span>Reports</span><i class="fas fa-chevron-right toggle-icon"></i></a>
            <div class="submenu" style="{{ request()->is('reports-*') ? 'max-height:500px;' : '' }}">
                <a href="{{ url('reports/daily') }}" class="{{ request()->is('reports/daily*') ? 'active-sub' : '' }}">Daily Report</a>
                <a href="{{ url('reports/sales') }}" class="{{ request()->is('reports/sales*') ? 'active-sub' : '' }}">Sales Report</a>
                <a href="{{ url('reports/order') }}" class="{{ request()->is('reports/order*') ? 'active-sub' : '' }}">Order Report</a>
            </div>
            <span class="menu-label">Account</span>
            <a href="#" class="has-sub {{ request()->is('settings-*') ? 'active' : '' }}"><i class="fas fa-gear"></i><span>Settings</span><i class="fas fa-chevron-right toggle-icon"></i></a>
            <div class="submenu" style="{{ request()->is('settings-*') ? 'max-height:500px;' : '' }}">
                <a href="{{ url('settings/filetools') }}" class="{{ request()->is('settings/filetools*') ? 'active-sub' : '' }}">File Tools</a>
                <a href="{{ url('staff/admin') }}" class="{{ request()->is('staff/admin*') ? 'active-sub' : '' }}">Staff</a>
                <a href="{{ url('settings/mastersettings') }}" class="{{ request()->is('settings/mastersettings*') ? 'active-sub' : '' }}">Master Setting</a>
            </div>
            @endif

            <form action="{{ route('logout') }}" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </form>
        </nav>
    </aside>

    <!-- Desktop Topbar -->
    <header class="desktop-topbar">
        <h2>@yield('page-title', 'Dashboard Overview')</h2>
        <div class="desktop-top-icons">
            <i class="fas fa-user-plus"></i>
            <i class="fas fa-concierge-bell"></i>
            <i class="fas fa-users"></i>
            <i class="fas fa-user-circle"></i>
        </div>
    </header>

    <!-- Mobile Topbar -->
    <header class="mobile-topbar">
        <div class="mobile-topbar-content">
            <button class="menu-toggle"><i class="fas fa-bars"></i></button>
            <div class="mobile-title">@yield('page-title', 'Dashboard Overview')</div>
            <div class="mobile-top-icons">
                <i class="fas fa-user-plus"></i>
                <i class="fas fa-concierge-bell"></i>
                <i class="fas fa-users"></i>
                <i class="fas fa-user-circle"></i>
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT --> <main class="main-content"> @yield('content') </main>
</div>

<script>
// Submenu toggle
const subMenus = document.querySelectorAll(".has-sub");
const menuToggle = document.querySelector(".menu-toggle");
const sidebar = document.querySelector(".sidebar");
const overlay = document.querySelector(".sidebar-overlay");

subMenus.forEach(menu => {
    menu.addEventListener("click", e => {
        e.preventDefault(); e.stopPropagation();
        const nextSubmenu = menu.nextElementSibling;
        subMenus.forEach(item => {
            if(item!==menu){ item.classList.remove("active"); item.nextElementSibling.style.maxHeight = null; }
        });
        menu.classList.toggle("active");
        nextSubmenu.style.maxHeight = menu.classList.contains("active") ? nextSubmenu.scrollHeight+"px" : null;
    });
});

menuToggle.addEventListener("click", () => { sidebar.classList.toggle("active"); overlay.classList.toggle("active"); });
overlay.addEventListener("click", () => { sidebar.classList.remove("active"); overlay.classList.remove("active"); });

document.addEventListener("click", e => {
    if(window.innerWidth<=768 && !sidebar.contains(e.target) && !menuToggle.contains(e.target)){
        sidebar.classList.remove("active"); overlay.classList.remove("active");
    }
});

window.addEventListener("resize", () => { if(window.innerWidth>768){ sidebar.classList.remove("active"); overlay.classList.remove("active"); }});
</script>
</body>
</html>
