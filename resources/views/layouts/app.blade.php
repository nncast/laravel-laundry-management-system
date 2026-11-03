@php $language = config('locales.' . app()->getLocale()); @endphp
<!DOCTYPE html>
<html lang="{{ $language['html'] }}" dir="{{ $language['dir'] }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#f4f7fb">
<title>@yield('title', __('Dashboard')) - {{ $system->business_name ?? 'Soap Opera' }}</title>

@if(!empty($system->favicon))
<link rel="icon" href="{{ asset($system->favicon) }}?v={{ optional($system->updated_at)->timestamp }}" type="image/x-icon">
@else
<link rel="icon" href="{{ asset('images/brand/mark.svg') }}" type="image/svg+xml">
@endif

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
{{-- asset_v(): the minified copy when it is up to date, versioned by file time (cached for a year) --}}
<link rel="stylesheet" href="{{ asset_v('css/theme.css') }}">
<link rel="stylesheet" href="{{ asset_v('css/app.css') }}">
@stack('styles')

<script>
    // Base URL of the app – lets AJAX calls work even when the app runs in a sub-folder
    window.APP_URL = @json(rtrim(url('/'), '/'));
    window.appUrl = (path = '') => window.APP_URL + '/' + String(path).replace(/^\/+/, '');
    window.CSRF_TOKEN = @json(csrf_token());
    window.FMT = @json(app(\App\Support\Format::class)->forScript());
</script>
@include('partials.i18n-script')
</head>

<body>
<a class="skip-link" href="#main">{{ __('Skip to content') }}</a>

@php
    $role = session('staff.role');
    $staffName = session('staff.name') ?? __('Staff');
    $isManager = in_array($role, ['manager', 'admin'], true);
    $isAdmin = $role === 'admin';

    // [label, route, icon, URL patterns that mark it current]
    $nav = [__('Operations') => [
        [__('Dashboard'), 'dashboard', 'fa-gauge-high', ['dashboard']],
        [__('Point of sale'), 'pos.index', 'fa-cash-register', ['pos', 'pos/*']],
        [__('Orders'), 'orders.index', 'fa-receipt', ['orders', 'orders/*']],
        [__('Customers'), 'customers.index', 'fa-users', ['customers', 'customers/*']],
    ]];
    if ($isManager) {
        $nav[__('Catalog & stock')] = [
            [__('Services'), 'services.list', 'fa-tags', ['services/*']],
            [__('Inventory'), 'products.index', 'fa-boxes-stacked', ['inventory/*']],
        ];
    }
    if ($isAdmin) {
        $nav[__('Administration')] = [
            [__('Reports'), 'reports.daily', 'fa-chart-column', ['reports/*']],
            [__('Staff'), 'staff.index', 'fa-user-shield', ['staff/*']],
            [__('Settings'), 'settings.mastersettings', 'fa-gear', ['settings/*']],
        ];
    }

    // Pages that belong together get tabs under the page title
    $sections = [
        'services/*' => [__('Catalog'), [[__('Services'), 'services.list', 'services/list*'], [__('Service types'), 'services.type', 'services/type*'], [__('Add-ons'), 'services.addons', 'services/addons*']]],
        'inventory/*' => [__('Inventory'), [[__('Products'), 'products.index', 'inventory/products*'], [__('Categories'), 'categories.index', 'inventory/categories*'], [__('Units'), 'units.index', 'inventory/units*']]],
        'reports/*' => [__('Reports'), [[__('Daily summary'), 'reports.daily', 'reports/daily*'], [__('Sales'), 'reports.sales', 'reports/sales*'], [__('Orders'), 'reports.orders', 'reports/order*']]],
    ];
    $section = collect($sections)->first(fn ($s, $pattern) => request()->is($pattern));

    // Messages from the last action (see #flashMessages)
    $flash = [
        'success' => session('success'),
        'error' => session('error'),
        'warning' => session('warning'),
        'errors' => $errors->any() && !old('_dialog') ? implode("\n", $errors->all()) : null,
    ];
@endphp

<aside class="sidebar" id="sidebar" aria-label="{{ __('Main menu') }}">
    <div class="sidebar-head">
        <a href="{{ route('dashboard') }}" class="brand">
            <img src="{{ asset('images/brand/mark.svg') }}" alt="" width="36" height="36">
            <span class="brand-text">
                <span class="brand-name">Soap <em>Opera</em></span>
                <span class="brand-sub">{{ !empty($system->business_name) && strcasecmp($system->business_name, 'Soap Opera') !== 0 ? $system->business_name : __('Laundry management') }}</span>
            </span>
        </a>
        <button type="button" class="icon-btn sidebar-close" data-nav-close aria-label="{{ __('Close menu') }}"><i class="fas fa-xmark" aria-hidden="true"></i></button>
    </div>

    <nav class="nav" aria-label="{{ __('Main') }}">
        @foreach($nav as $group => $items)
            <p class="nav-label" id="nav-group-{{ $loop->index }}">{{ $group }}</p>
            <ul aria-labelledby="nav-group-{{ $loop->index }}">
                @foreach($items as [$label, $routeName, $icon, $patterns])
                    <li>
                        <a href="{{ route($routeName) }}" class="nav-link" @if(request()->is(...$patterns)) aria-current="page" @endif>
                            <i class="fas {{ $icon }}" aria-hidden="true"></i><span>{{ $label }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endforeach
    </nav>

    <div class="sidebar-foot">
        <span class="avatar" aria-hidden="true">{{ strtoupper(mb_substr($staffName, 0, 1)) }}</span>
        <span class="account">
            <span class="account-name">{{ $staffName }}</span>
            <span class="account-role">{{ __(ucfirst((string) $role)) }}</span>
        </span>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="icon-btn" aria-label="{{ __('Log out') }}" title="{{ __('Log out') }}"><i class="fas fa-arrow-right-from-bracket" aria-hidden="true"></i></button>
        </form>
    </div>
    @include('partials.language-picker')
</aside>
<div class="scrim" data-nav-close></div>

<div class="app-main">
    <header class="topbar">
        <a href="{{ route('dashboard') }}" class="topbar-brand" aria-label="{{ __('Dashboard') }}"><img src="{{ asset('images/brand/mark.svg') }}" alt="" width="32" height="32"></a>
        <form class="global-search search" action="{{ route('orders.index') }}" method="GET" role="search">
            <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
            <input class="input" type="search" name="search" placeholder="{{ __('Search orders or scan a ticket') }}"
                   aria-label="{{ __('Search orders by number or customer') }}" autocomplete="off" data-search-shortcut
                   role="combobox" aria-expanded="false" aria-controls="searchSuggest" aria-autocomplete="list"
                   value="{{ request()->routeIs('orders.index') ? request('search') : '' }}">
            <kbd aria-hidden="true">/</kbd>
            <div class="search-suggest" id="searchSuggest" role="listbox" aria-label="{{ __('Matching orders') }}" hidden></div>
        </form>
        <div class="topbar-actions">
            <a href="{{ route('pos.index') }}" class="btn btn-primary topbar-new-order"><i class="fas fa-plus" aria-hidden="true"></i> {{ __('New order') }}</a>
        </div>
    </header>

    <main id="main" class="page" tabindex="-1">
        @hasSection('page-header')
            @yield('page-header')
        @else
        <header class="page-header" id="pageHeader" data-refresh>
            <div class="page-heading">
                <h1 class="page-title">@yield('page-title', __('Dashboard'))</h1>
                @hasSection('page-subtitle')<p class="page-subtitle">@yield('page-subtitle')</p>@endif
            </div>
            @hasSection('page-actions')<div class="page-actions">@yield('page-actions')</div>@endif
        </header>
        @endif

        @if($section)
        <nav class="section-tabs" aria-label="{{ $section[0] }}">
            @foreach($section[1] as [$label, $routeName, $pattern])
                <a href="{{ route($routeName) }}" @if(request()->is($pattern)) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>
        @endif

        @yield('content')
    </main>
</div>

{{-- Phones and tablets: the main places one thumb away --}}
<nav class="bottom-nav" aria-label="{{ __('Quick links') }}">
    <a href="{{ route('dashboard') }}" @if(request()->is('dashboard')) aria-current="page" @endif><i class="fas fa-gauge-high" aria-hidden="true"></i><span>{{ __('Home') }}</span></a>
    <a href="{{ route('orders.index') }}" @if(request()->is('orders', 'orders/*')) aria-current="page" @endif><i class="fas fa-receipt" aria-hidden="true"></i><span>{{ __('Orders') }}</span></a>
    <a href="{{ route('pos.index') }}" class="bottom-nav-cta"><i class="fas fa-plus" aria-hidden="true"></i><span>{{ __('New order') }}</span></a>
    <a href="{{ route('customers.index') }}" @if(request()->is('customers', 'customers/*')) aria-current="page" @endif><i class="fas fa-users" aria-hidden="true"></i><span>{{ __('Customers') }}</span></a>
    <button type="button" data-nav-open aria-controls="sidebar" aria-expanded="false"><i class="fas fa-bars" aria-hidden="true"></i><span>{{ __('More') }}</span></button>
</nav>

<div id="toastRegion" class="toast-region" aria-live="polite"></div>

<dialog id="confirmDialog" class="dialog dialog-sm" aria-labelledby="confirmTitle" aria-describedby="confirmMessage">
    <form method="dialog">
        <div class="dialog-body confirm-body">
            <span class="confirm-icon" aria-hidden="true"><i class="fas fa-triangle-exclamation"></i></span>
            <h2 id="confirmTitle" class="dialog-title">{{ __('Are you sure?') }}</h2>
            <p id="confirmMessage"></p>
        </div>
        <div class="dialog-footer">
            <button class="btn btn-secondary" value="cancel" data-confirm-cancel>{{ __('Cancel') }}</button>
            <button class="btn btn-danger" value="ok" data-confirm-ok>{{ __('Confirm') }}</button>
        </div>
    </form>
</dialog>

{{-- Messages from the last action, shown as toasts by app.js (also when only part of the page is refreshed) --}}
<script type="application/json" id="flashMessages">@json($flash)</script>
<script src="{{ asset_v('js/app.js') }}"></script>
@stack('scripts')
{{-- Chromium browsers render a page in the background as soon as a link is hovered or touched,
     so opening it feels instant. Downloads and print views are left out. --}}
<script type="speculationrules">
{"prerender": [{"where": {"and": [
    {"href_matches": "/*"},
    {"not": {"href_matches": "*/print/*"}},
    {"not": {"href_matches": "*/download*"}},
    {"not": {"selector_matches": "[data-no-prerender], [target=_blank]"}}
]}, "eagerness": "moderate"}]}
</script>
</body>
</html>
