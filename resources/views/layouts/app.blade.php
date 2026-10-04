<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Dashboard') - {{ $system->business_name ?? 'Soap Opera' }}</title>

<!-- Favicon -->
@if(!empty($system->favicon))
<link rel="icon" href="{{ asset($system->favicon) }}?v={{ optional($system->updated_at)->timestamp }}" type="image/x-icon">
@else
<link rel="icon" href="{{ asset('images/brand/mark.svg') }}" type="image/svg+xml">
@endif

<!-- Open connections to the CDNs early -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>

<!-- Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/theme.css') }}?v={{ @filemtime(public_path('css/theme.css')) }}">

<!-- Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">

<!-- Main CSS (version = file time so browsers re-download only after a change) -->
<link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ @filemtime(public_path('css/style.css')) }}">
@stack('styles')
<link rel="stylesheet" href="{{ asset('css/components.css') }}?v={{ @filemtime(public_path('css/components.css')) }}">

<script>
    // Base URL of the app – lets AJAX calls work even when the app runs in a sub-folder
    window.APP_URL = @json(rtrim(url('/'), '/'));
    window.appUrl = (path = '') => window.APP_URL + '/' + String(path).replace(/^\/+/, '');
    window.CSRF_TOKEN = @json(csrf_token());
</script>
</head>

<body>
<div class="app-container">

    @php
        $role = session('staff.role');
        $staffName = session('staff.name');
    @endphp

    <div class="sidebar-overlay"></div>

    <aside class="sidebar" aria-label="Main navigation">
        <div class="logo">
            <a href="{{ route('dashboard') }}" class="brand" aria-label="Soap Opera home">
                <img class="brand-mark" src="{{ asset('images/brand/mark.svg') }}" alt="" width="40" height="40">
                <span class="brand-text">
                    <span class="brand-name">Soap <em>Opera</em></span>
                    @if(!empty($system->business_name) && strcasecmp($system->business_name, 'Soap Opera') !== 0)
                    <span class="brand-sub">{{ $system->business_name }}</span>
                    @else
                    <span class="brand-sub">Laundry management</span>
                    @endif
                </span>
            </a>
            <button type="button" class="sidebar-close" aria-label="Close menu"><i class="fas fa-times"></i></button>
        </div>

        <nav class="menu">
            <span class="menu-label">Dashboard</span>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="fas fa-gauge"></i><span>Dashboard</span></a>

            @if(in_array($role, ['cashier', 'manager', 'admin']))
            <span class="menu-label">Orders</span>
            <a href="{{ route('orders.index') }}" class="{{ request()->is('orders*') ? 'active' : '' }}"><i class="fas fa-receipt"></i><span>Orders</span></a>
            <a href="{{ route('pos.index') }}" class="{{ request()->is('pos*') ? 'active' : '' }}"><i class="fas fa-cash-register"></i><span>POS</span></a>
            <span class="menu-label">Application</span>
            <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}"><i class="fas fa-users"></i><span>Customers</span></a>
            @endif

            @if(in_array($role, ['manager', 'admin']))
            @php $inventoryOpen = request()->is('inventory/*'); @endphp
            <a href="#" class="has-sub {{ $inventoryOpen ? 'active open' : '' }}" aria-expanded="{{ $inventoryOpen ? 'true' : 'false' }}"><i class="fas fa-boxes"></i><span>Inventory</span><i class="fas fa-chevron-right toggle-icon"></i></a>
            <div class="submenu {{ $inventoryOpen ? 'open' : '' }}">
                <a href="{{ route('products.index') }}" class="{{ request()->is('inventory/products*') ? 'active-sub' : '' }}">Products</a>
                <a href="{{ route('categories.index') }}" class="{{ request()->is('inventory/categories*') ? 'active-sub' : '' }}">Categories</a>
                <a href="{{ route('units.index') }}" class="{{ request()->is('inventory/units*') ? 'active-sub' : '' }}">Units</a>
            </div>

            @php $servicesOpen = request()->is('services/*'); @endphp
            <a href="#" class="has-sub {{ $servicesOpen ? 'active open' : '' }}" aria-expanded="{{ $servicesOpen ? 'true' : 'false' }}"><i class="fas fa-tags"></i><span>Services</span><i class="fas fa-chevron-right toggle-icon"></i></a>
            <div class="submenu {{ $servicesOpen ? 'open' : '' }}">
                <a href="{{ route('services.list') }}" class="{{ request()->is('services/list*') ? 'active-sub' : '' }}">Service List</a>
                <a href="{{ route('services.type') }}" class="{{ request()->is('services/type*') ? 'active-sub' : '' }}">Service Type</a>
                <a href="{{ route('services.addons') }}" class="{{ request()->is('services/addons*') ? 'active-sub' : '' }}">Addons</a>
            </div>
            @endif

            @if($role === 'admin')
            @php $reportsOpen = request()->is('reports/*'); @endphp
            <a href="#" class="has-sub {{ $reportsOpen ? 'active open' : '' }}" aria-expanded="{{ $reportsOpen ? 'true' : 'false' }}"><i class="fas fa-chart-column"></i><span>Reports</span><i class="fas fa-chevron-right toggle-icon"></i></a>
            <div class="submenu {{ $reportsOpen ? 'open' : '' }}">
                <a href="{{ route('reports.daily') }}" class="{{ request()->is('reports/daily*') ? 'active-sub' : '' }}">Daily Report</a>
                <a href="{{ route('reports.sales') }}" class="{{ request()->is('reports/sales*') ? 'active-sub' : '' }}">Sales Report</a>
                <a href="{{ route('reports.orders') }}" class="{{ request()->is('reports/order*') ? 'active-sub' : '' }}">Order Report</a>
            </div>

            <span class="menu-label">Account</span>
            @php $settingsOpen = request()->is('settings/*') || request()->is('staff/*'); @endphp
            <a href="#" class="has-sub {{ $settingsOpen ? 'active open' : '' }}" aria-expanded="{{ $settingsOpen ? 'true' : 'false' }}"><i class="fas fa-gear"></i><span>Settings</span><i class="fas fa-chevron-right toggle-icon"></i></a>
            <div class="submenu {{ $settingsOpen ? 'open' : '' }}">
                <a href="{{ route('staff.index') }}" class="{{ request()->is('staff/*') ? 'active-sub' : '' }}">Staff</a>
                <a href="{{ route('settings.mastersettings') }}" class="{{ request()->is('settings/*') ? 'active-sub' : '' }}">Master Setting</a>
            </div>
            @endif

        </nav>

        <div class="sidebar-footer">
            <span class="avatar" aria-hidden="true">{{ strtoupper(mb_substr($staffName ?? '?', 0, 1)) }}</span>
            <span class="account">
                <span class="account-name">{{ $staffName }}</span>
                <span class="account-role">{{ ucfirst($role) }}</span>
            </span>
            <form action="{{ route('logout') }}" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn" title="Log out" aria-label="Log out"><i class="fas fa-arrow-right-from-bracket"></i></button>
            </form>
        </div>
    </aside>

    <!-- Desktop Topbar -->
    <header class="desktop-topbar">
        <div class="topbar-title">
            <h2>@yield('page-title', 'Dashboard Overview')</h2>
            <span class="topbar-date">{{ now()->format('l, F j, Y') }}</span>
        </div>
        @if(in_array($role, ['cashier', 'manager', 'admin']) && !request()->is('pos*'))
        <a href="{{ route('pos.index') }}" class="topbar-cta"><i class="fas fa-plus"></i> New Order</a>
        @endif
    </header>

    <!-- Mobile Topbar -->
    <header class="mobile-topbar">
        <div class="mobile-topbar-content">
            <button type="button" class="menu-toggle" aria-label="Open menu"><i class="fas fa-bars"></i></button>
            <div class="mobile-title">@yield('page-title', 'Dashboard Overview')</div>
            <a href="{{ route('pos.index') }}" class="mobile-pos-link" aria-label="New order (POS)"><i class="fas fa-cash-register"></i></a>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        @yield('content')
    </main>
</div>

<!-- Toast messages (session flashes + validation errors) -->
<div id="toastContainer" class="toast-container" aria-live="polite"></div>

<script>
// ---------------- Toasts ----------------
window.showToast = function (message, type = 'info', timeout = 5000) {
    const container = document.getElementById('toastContainer');
    if (!container || !message) return;

    const icons = { success: 'fa-check-circle', error: 'fa-circle-exclamation', warning: 'fa-triangle-exclamation', info: 'fa-circle-info' };
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.setAttribute('role', type === 'error' ? 'alert' : 'status');

    const icon = document.createElement('i');
    icon.className = `fas ${icons[type] || icons.info}`;
    const text = document.createElement('div');
    text.className = 'toast-text';
    text.textContent = message;
    const close = document.createElement('button');
    close.type = 'button';
    close.className = 'toast-close';
    close.setAttribute('aria-label', 'Dismiss');
    close.innerHTML = '&times;';

    const remove = () => { toast.classList.add('toast-hide'); setTimeout(() => toast.remove(), 250); };
    close.addEventListener('click', remove);

    toast.append(icon, text, close);
    container.appendChild(toast);
    if (timeout) setTimeout(remove, timeout);
};

// Read a JSON error response (Laravel validation or {message}) into one string
window.responseErrorMessage = async function (response, fallback = 'Something went wrong. Please try again.') {
    try {
        const data = await response.clone().json();
        if (data.errors) return Object.values(data.errors).flat().join('\n');
        if (data.message) return data.message;
    } catch (e) { /* not JSON */ }
    if (response.status === 419) return 'Your session expired. Please refresh the page.';
    if (response.status === 401) return 'Your session ended. Please log in again.';
    if (response.status === 403) return 'You do not have permission to do that.';
    return fallback;
};

document.addEventListener('DOMContentLoaded', () => {
    @if(session('success'))
        showToast(@json(session('success')), 'success');
    @endif
    @if(session('error'))
        showToast(@json(session('error')), 'error', 8000);
    @endif
    @if(session('warning'))
        showToast(@json(session('warning')), 'warning');
    @endif
    @if($errors->any())
        showToast(@json(implode("\n", $errors->all())), 'error', 9000);
    @endif
});

// ---------------- Sidebar ----------------
(function () {
    const subMenus = document.querySelectorAll('.has-sub');
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebarClose = document.querySelector('.sidebar-close');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');

    const setOpen = (menu, open) => {
        const submenu = menu.nextElementSibling;
        menu.classList.toggle('open', open);
        menu.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (submenu) submenu.classList.toggle('open', open);
    };

    subMenus.forEach(menu => {
        menu.addEventListener('click', e => {
            e.preventDefault();
            const willOpen = !menu.classList.contains('open');
            subMenus.forEach(item => { if (item !== menu) setOpen(item, false); });
            setOpen(menu, willOpen);
        });
    });

    const openSidebar = () => { sidebar.classList.add('active'); overlay.classList.add('active'); document.body.classList.add('sidebar-open'); };
    const closeSidebar = () => { sidebar.classList.remove('active'); overlay.classList.remove('active'); document.body.classList.remove('sidebar-open'); };

    menuToggle?.addEventListener('click', openSidebar);
    sidebarClose?.addEventListener('click', closeSidebar);
    overlay?.addEventListener('click', closeSidebar);
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSidebar(); });
    window.addEventListener('resize', () => { if (window.innerWidth > 768) closeSidebar(); });

    // Prevent double-submitting regular forms (slow connections)
    document.addEventListener('submit', e => {
        const form = e.target;
        if (e.defaultPrevented || form.method.toLowerCase() === 'get' || form.dataset.allowResubmit !== undefined) return;
        setTimeout(() => {
            if (e.defaultPrevented) return;
            form.querySelectorAll('button[type="submit"], button:not([type])').forEach(b => b.disabled = true);
        }, 0);
    });
    // Re-enable buttons when the page is restored with the browser's Back button
    window.addEventListener('pageshow', e => {
        if (e.persisted) document.querySelectorAll('form button[disabled]').forEach(b => b.disabled = false);
    });
})();
</script>
@stack('scripts')
</body>
</html>
