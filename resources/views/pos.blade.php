<!DOCTYPE html>
<html lang="{{ config('locales.' . app()->getLocale() . '.html') }}" dir="{{ config('locales.' . app()->getLocale() . '.dir') }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#ffffff">
@php $editing = isset($order); @endphp
<title>{{ $editing ? __('Edit :number', ['number' => $order->order_number]) : __('New order') }} - {{ $system->business_name ?? 'Soap Opera' }}</title>
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
<link rel="stylesheet" href="{{ asset_v('css/theme.css') }}">
<link rel="stylesheet" href="{{ asset_v('css/app.css') }}">
<style>
/* ===== Point of sale: catalog | order | customer + payment. Touch first: 48px+ targets ===== */
:root { --pos-top: 64px; --pos-bar: 76px; }
body.pos-body { height: 100vh; height: 100dvh; overflow: hidden; background: var(--bg); }

.pos-topbar { display: flex; align-items: center; gap: var(--s-3); height: var(--pos-top); padding: 0 var(--s-4); background: var(--surface); border-bottom: 1px solid var(--border); }
.pos-title { min-width: 0; line-height: 1.2; }
.pos-title h1 { font-size: 18px; font-weight: 800; }
.pos-title span { font-size: var(--fs-sm); color: var(--text-muted); }
.pos-topbar-end { margin-inline-start: auto; display: flex; align-items: center; gap: var(--s-4); }
.pos-hints { font-size: var(--fs-xs); color: var(--text-muted); white-space: nowrap; }
.pos-staff { display: flex; align-items: center; gap: var(--s-2); font-weight: 700; }
.pos-staff .avatar { width: 36px; height: 36px; }

.pos { display: grid; grid-template-columns: minmax(0, 1fr) 380px 360px; height: calc(100dvh - var(--pos-top)); }
.pos-side { display: contents; }
.pos-side-head { display: none; }

/* Catalog */
.pos-catalog { display: flex; flex-direction: column; min-width: 0; min-height: 0; }
.catalog-head { display: grid; gap: var(--s-3); padding: var(--s-4) var(--s-5) var(--s-3); }
.catalog-head .input { height: 52px; font-size: var(--fs-md); }
.tiles-scroll { flex: 1; min-height: 0; overflow-y: auto; overscroll-behavior: contain; padding: 0 var(--s-5) var(--s-6); }
.tile-group + .tile-group { margin-top: var(--s-5); }
.tile-group-title { margin-bottom: var(--s-3); font-size: var(--fs-sm); font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; color: var(--text-muted); }
.tiles { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: var(--s-3); }
.tile {
    position: relative;
    display: flex; flex-direction: column; align-items: flex-start; gap: var(--s-2);
    min-height: 124px; padding: var(--s-3) var(--s-4) var(--s-4);
    border: 1px solid var(--border); border-radius: var(--radius); background: var(--surface);
    color: var(--text); text-align: start; user-select: none; -webkit-user-select: none;
    transition: transform var(--fast), border-color var(--fast), background-color var(--fast);
}
.tile .bubble-tile, .tile-img { width: 40px; height: 40px; border-radius: 10px; object-fit: cover; }
.tile-name { font-weight: 700; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.tile-price { margin-top: auto; font-weight: 800; color: var(--accent); font-variant-numeric: tabular-nums; }
.tile-qty { position: absolute; top: 10px; inset-inline-end: 10px; min-width: 30px; height: 30px; padding: 0 8px; display: grid; place-items: center; border-radius: var(--radius-pill); background: var(--accent); color: #fff; font-weight: 800; }
.tile.in-cart { border-color: var(--accent); box-shadow: inset 0 0 0 1px var(--accent); background: #fbfdff; }
.tile:active { transform: scale(0.97); background: var(--accent-soft); }
.tile.pop { animation: tile-pop 220ms var(--ease); }
@keyframes tile-pop { 40% { transform: scale(0.95); background: var(--accent-soft); } }

/* Panels */
.pos-cart, .pos-checkout { display: flex; flex-direction: column; min-height: 0; background: var(--surface); border-inline-start: 1px solid var(--border); }
.panel-head { display: flex; align-items: center; justify-content: space-between; gap: var(--s-2); min-height: 60px; padding: var(--s-2) var(--s-3) var(--s-2) var(--s-5); border-bottom: 1px solid var(--border); }
.panel-head h2 { display: flex; align-items: center; gap: var(--s-2); font-size: var(--fs-md); font-weight: 800; }
.panel-scroll { flex: 1; min-height: 0; overflow-y: auto; overscroll-behavior: contain; }
.panel-block { padding: var(--s-4) var(--s-5); border-bottom: 1px solid var(--border); }
.panel-block:last-child { border-bottom: 0; }
.panel-block > .eyebrow { margin-bottom: var(--s-2); }

.cart-lines { list-style: none; padding: 0; }
.cart-line { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 4px var(--s-3); padding: var(--s-3) var(--s-5); border-bottom: 1px solid var(--border); }
.line-name { font-weight: 700; }
.line-unit { font-size: var(--fs-sm); color: var(--text-muted); }
.line-total { font-weight: 800; text-align: end; font-variant-numeric: tabular-nums; }
.line-controls { grid-column: 1 / -1; display: flex; align-items: center; justify-content: space-between; margin-top: 4px; }
.cart-line .stepper button, .cart-line .stepper input { height: 48px; }
.cart-line .stepper button { width: 48px; }
.cart-empty { display: grid; justify-items: center; gap: var(--s-1); padding: var(--s-8) var(--s-6); text-align: center; color: var(--text-muted); }
.cart-empty .mascot { width: 96px; margin-bottom: var(--s-2); }
.addon-chip .addon-price { color: var(--text-muted); font-weight: 600; }
.addon-chip[aria-pressed="true"] .addon-price { color: #dbe8ff; }
.addon-chip[aria-pressed="true"] .fa-plus::before { content: "\f00c"; }

/* Customer */
.customer-results { list-style: none; padding: 4px; margin-top: var(--s-2); border: 1px solid var(--border); border-radius: var(--radius); max-height: 260px; overflow-y: auto; }
.customer-results li[role="option"] { display: flex; align-items: center; gap: var(--s-3); min-height: 52px; padding: 0 var(--s-3); border-radius: var(--radius-sm); cursor: pointer; }
.customer-results li[aria-selected="true"], .customer-results li[role="option"]:active { background: var(--accent-soft); }
.customer-results .avatar, .customer-card .avatar { width: 36px; height: 36px; font-size: 14px; }
.customer-results .muted { font-size: var(--fs-sm); }
.customer-results .no-hit { padding: var(--s-3); color: var(--text-muted); }
.customer-results .skeleton-row { display: flex; align-items: center; gap: var(--s-3); min-height: 52px; padding: 0 var(--s-3); }
.recent { margin-top: var(--s-3); }
.recent .chip { min-height: 40px; padding: 0 var(--s-3); }
.customer-card { display: flex; align-items: center; gap: var(--s-3); padding: var(--s-3); border: 1px solid var(--accent-soft-strong); border-radius: var(--radius); background: var(--accent-soft); }
.customer-info { flex: 1; min-width: 0; display: flex; flex-direction: column; line-height: 1.35; }
.customer-info strong { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.customer-stats { font-size: var(--fs-sm); color: var(--text-secondary); }
.customer-stats .text-danger { font-weight: 700; }
#repeatLast, #newCustomerBtn { margin-top: var(--s-3); }

/* Totals + discount */
.discount-ctl { display: flex; gap: var(--s-2); margin: var(--s-2) 0 var(--s-1); }
.discount-ctl .input { flex: 1; min-width: 0; }
#percentChips { margin-top: var(--s-2); }
#percentChips .chip { min-height: 40px; }
.total-row { font-size: 24px; }
.checkout-actions { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.6fr); gap: var(--s-2); padding: var(--s-3) var(--s-4) calc(var(--s-3) + env(safe-area-inset-bottom)); border-top: 1px solid var(--border); background: var(--surface); }
.checkout-actions .btn { min-height: 60px; min-width: 0; flex-wrap: wrap; column-gap: var(--s-2); row-gap: 0; white-space: normal; line-height: 1.2; text-align: center; }
.checkout-actions .btn-primary { font-size: 17px; }

/* Payment dialog */
.pay-due { text-align: center; padding: var(--s-3); border-radius: var(--radius); background: var(--accent-soft); }
.pay-due strong { display: block; font-size: 32px; letter-spacing: -0.02em; color: var(--accent); font-variant-numeric: tabular-nums; }
.tendered { height: 60px; font-size: 26px !important; font-weight: 800; text-align: end; font-variant-numeric: tabular-nums; }
.quick-cash { display: grid; grid-template-columns: repeat(5, 1fr); gap: var(--s-2); }
.quick-cash .btn { padding: 0 4px; min-height: 48px; }
.keypad { display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--s-2); }
.keypad button { min-height: 56px; border: 1px solid var(--border-strong); border-radius: var(--radius-sm); background: var(--surface); font-size: 22px; font-weight: 700; }
.keypad button:active { background: var(--accent-soft); }
.pay-result { min-height: 26px; text-align: center; font-weight: 700; }
.pay-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 0.9fr); gap: var(--s-5); align-items: start; }
.pay-grid > * + * { margin-top: 0; }
.pay-grid .keypad button { min-height: 64px; }
@media (max-width: 719px) { .pay-grid { grid-template-columns: 1fr; gap: var(--s-3); } .pay-grid .keypad button { min-height: 52px; } }
.success-body { display: grid; justify-items: center; text-align: center; gap: var(--s-2); }
.success-body .mascot { width: 120px; }
.ok-change { display: grid; gap: 2px; padding: var(--s-3) var(--s-5); border-radius: var(--radius); background: var(--success-soft); color: var(--success-text); }
.ok-change strong { font-size: 30px; }
.ok-print { display: grid; gap: var(--s-2); width: 100%; margin-top: var(--s-2); }
.ok-print .cluster .btn { flex: 1 1 auto; }
.success-actions { flex-wrap: wrap; }
.success-actions .btn-primary { flex-basis: 100%; min-height: 56px; }

/* Bottom bar (phones and portrait tablets) */
.pos-bar { display: none; }

@media (hover: hover) {
    .tile:hover { border-color: var(--accent-soft-strong); background: var(--surface-muted); }
    .tile.in-cart:hover { border-color: var(--accent); }
    .customer-results li[role="option"]:hover { background: var(--surface-hover); }
    .keypad button:hover { background: var(--surface-hover); }
}
@media (pointer: coarse) { .pos-hints { display: none; } }

/* Landscape tablets: catalog | one column with order, customer and payment */
@media (max-width: 1279px) {
    .pos { grid-template-columns: minmax(0, 1fr) 400px; }
    .pos-side { display: flex; flex-direction: column; min-height: 0; overflow-y: auto; overscroll-behavior: contain; background: var(--surface); border-inline-start: 1px solid var(--border); }
    .pos-cart { border-inline-start: 0; flex: none; }
    .pos-cart .panel-scroll, .pos-checkout .panel-scroll { flex: none; overflow: visible; }
    /* Let the action bar stick to the bottom of the whole column, not just of its own panel */
    .pos-checkout { display: contents; }
    .pos-checkout .panel-scroll { border-top: 1px solid var(--border); }
    .checkout-actions { position: sticky; bottom: 0; z-index: 2; margin-top: auto; }
}
/* Phones and portrait tablets: full-width catalog, order slides up from the bottom bar */
@media (max-width: 1023px) {
    .pos { grid-template-columns: minmax(0, 1fr); }
    .tiles-scroll { padding-bottom: calc(var(--pos-bar) + var(--s-6) + env(safe-area-inset-bottom)); }
    .pos-side {
        position: fixed; inset: 0; z-index: 60;
        transform: translateY(102%); visibility: hidden;
        transition: transform var(--normal) var(--ease), visibility var(--normal);
    }
    .pos-side.is-open { transform: none; visibility: visible; }
    .pos-side-head { display: flex; position: sticky; top: 0; z-index: 3; align-items: center; gap: var(--s-2); height: var(--pos-top); padding: 0 var(--s-3); background: var(--surface); border-bottom: 1px solid var(--border); }
    .pos-side-head h2 { font-size: 18px; font-weight: 800; }
    .pos-bar { display: block; position: fixed; inset-inline-start: 0; inset-inline-end: 0; bottom: 0; z-index: 50; padding: var(--s-3) var(--s-4) calc(var(--s-3) + env(safe-area-inset-bottom)); background: var(--surface); border-top: 1px solid var(--border); }
    .pos-bar .btn { min-height: 56px; justify-content: space-between; }
    .toast-region { inset: auto 16px calc(var(--pos-bar) + 12px + env(safe-area-inset-bottom)) 16px !important; }
}
@media (max-width: 639px) {
    .catalog-head { padding: var(--s-3) var(--s-4) var(--s-2); }
    .tiles-scroll { padding-inline-start: var(--s-4); padding-inline-end: var(--s-4); }
    .tiles { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: var(--s-2); }
    .tile { min-height: 112px; }
    .pos-staff span:last-child, .pos-hints { display: none; }
    .quick-cash { grid-template-columns: repeat(3, 1fr); }
}
</style>
<script>
    window.APP_URL = @json(rtrim(url('/'), '/'));
    window.appUrl = (path = '') => window.APP_URL + '/' + String(path).replace(/^\/+/, '');
    window.CSRF_TOKEN = @json(csrf_token());
    window.FMT = @json(app(\App\Support\Format::class)->forScript());
</script>
@include('partials.i18n-script')
</head>
@php
    // Icon for a service type, picked from its name so it stays the same between visits
    $typeIcon = function (string $name): string {
        $icons = [
            'wash' => 'fa-soap', 'fold' => 'fa-soap', 'laundry' => 'fa-soap',
            'dry' => 'fa-wind', 'iron' => 'fa-shirt', 'press' => 'fa-shirt',
            'special' => 'fa-hand-sparkles', 'care' => 'fa-hand-sparkles', 'delicate' => 'fa-hand-sparkles',
            'blanket' => 'fa-bed', 'comforter' => 'fa-bed', 'bed' => 'fa-bed', 'shoe' => 'fa-shoe-prints',
        ];
        foreach ($icons as $word => $icon) {
            if (stripos($name, $word) !== false) return $icon;
        }
        return 'fa-basket-shopping';
    };
    $tones = ['tone-blue', 'tone-sky', 'tone-indigo', 'tone-aqua', 'tone-violet'];
    $categories = $serviceGroups->keys()->values();
    $staffName = session('staff.name') ?? 'Staff';

    // Add-on chips: the active add-ons, plus any inactive ones already on the order being edited (at the price sold)
    $addonChoices = $addons->mapWithKeys(fn ($a) => [$a->id => ['id' => $a->id, 'name' => $a->name, 'price' => (float) $a->price]]);
    if ($editing) {
        foreach ($order->addons as $addon) {
            $addonChoices[$addon->id] = ['id' => $addon->id, 'name' => $addon->name, 'price' => (float) $addon->pivot->price];
        }
    }

    // For the script below (@json takes a plain variable: it splits its argument on commas)
    $posServices = $services->map(fn ($s) => [$s->id, ['id' => $s->id, 'name' => $s->name, 'price' => (float) $s->price]])->values();
    $posAddons = $addonChoices->map(fn ($a) => [$a['id'], $a])->values();
    $customerJson = fn ($c) => $c ? ['id' => $c->id, 'name' => $c->name, 'contact' => $c->contact] : null;
    $posRecent = $recentCustomers->map($customerJson)->values();
    $turnaround = (int) $system->turnaround_days;   // days until the order is ready, by default

    $initial = [
        'orderId' => $editing ? $order->id : null,
        'items' => $editing ? $order->items->map(fn ($item) => ['id' => $item->service_id, 'name' => $item->service->name ?? __('Deleted service'), 'price' => (float) $item->price, 'qty' => $item->qty])->values() : [],
        'addons' => $editing ? $order->addons->pluck('id')->values() : [],
        'customer' => $customerJson($editing ? $order->customer : ($preselectedCustomer ?? null)),
        'date' => $editing ? $order->order_date?->toDateString() : null,
        'readyBy' => $editing ? $order->ready_by?->toDateString() : null,
        'discountType' => $editing && $order->discount_percent !== null ? 'percent' : 'amount',
        'discountValue' => $editing ? (float) ($order->discount_percent ?? $order->discount) : 0,
        'paid' => $editing ? (float) $order->paid_amount : 0,
    ];
@endphp
<body class="pos-body">
<a class="skip-link" href="#serviceSearch">{{ __('Skip to services') }}</a>

<header class="pos-topbar">
    <a href="{{ $editing ? route('orders.details', $order) : route('dashboard') }}" class="icon-btn outlined" id="leavePos" aria-label="{{ $editing ? __('Back to the order') : __('Leave the point of sale') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i></a>
    <div class="pos-title">
        <h1>{{ $editing ? __('Edit order') : __('New order') }}</h1>
        <span>{{ $editing ? $order->order_number : ($system->business_name ?? 'Soap Opera') }}</span>
    </div>
    <div class="pos-topbar-end">
        <span class="badge badge-info" title="{{ __('Business currency') }}">{{ currency_symbol() }} {{ app(\App\Support\Format::class)->currency }}</span>
        <span class="pos-hints"><kbd>/</kbd> {{ __('services') }} · <kbd>F2</kbd> {{ __('customer') }} · <kbd>Ctrl</kbd>+<kbd>Enter</kbd> {{ __('charge') }}</span>
        <span class="pos-staff"><span class="avatar" aria-hidden="true">{{ strtoupper(mb_substr($staffName, 0, 1)) }}</span><span>{{ $staffName }}</span></span>
    </div>
</header>

<main class="pos" id="main">
    {{-- ===== Services ===== --}}
    <section class="pos-catalog" aria-label="{{ __('Services') }}">
        <div class="catalog-head">
            <div class="search">
                <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                <input class="input" type="search" id="serviceSearch" placeholder="{{ __('Search services') }}" aria-label="{{ __('Search services') }}" autocomplete="off" data-search-shortcut>
            </div>
            @if($categories->count() > 1)
            <div class="chips scroll" role="group" aria-label="{{ __('Service types') }}">
                <button type="button" class="chip" aria-pressed="true" data-cat="all">{{ __('All') }} <span class="chip-count">{{ $services->count() }}</span></button>
                @foreach($categories as $i => $category)
                    <button type="button" class="chip" aria-pressed="false" data-cat="cat-{{ $i }}"><i class="fas {{ $typeIcon($category) }}" aria-hidden="true"></i> {{ $category }} <span class="chip-count">{{ $serviceGroups[$category]->count() }}</span></button>
                @endforeach
            </div>
            @endif
        </div>

        <div class="tiles-scroll" id="tilesScroll">
            @forelse($categories as $i => $category)
                <section class="tile-group" data-cat="cat-{{ $i }}" aria-labelledby="cat-title-{{ $i }}">
                    <h2 class="tile-group-title" id="cat-title-{{ $i }}">{{ $category }}</h2>
                    <div class="tiles">
                        @foreach($serviceGroups[$category] as $service)
                        <button type="button" class="tile" data-service="{{ $service->id }}" data-name="{{ strtolower($service->name) }}" aria-label="{{ __('Add :name, :price', ['name' => $service->name, 'price' => money($service->price)]) }}">
                            <span class="tile-qty" hidden></span>
                            @if($service->icon && !str_contains($service->icon, 'placeholder'))
                                <img class="tile-img" src="{{ $service->icon_url }}" alt="" loading="lazy" width="40" height="40">
                            @else
                                <span class="bubble-tile {{ $tones[$i % count($tones)] }}" aria-hidden="true"><i class="fas {{ $typeIcon($category) }}"></i></span>
                            @endif
                            <span class="tile-name">{{ $service->name }}</span>
                            <span class="tile-price">{{ money($service->price) }}</span>
                        </button>
                        @endforeach
                    </div>
                </section>
            @empty
                <div class="empty">
                    <span class="empty-icon" aria-hidden="true"><i class="fas fa-tags"></i></span>
                    <p class="empty-title">{{ __('No services to sell yet') }}</p>
                    <p>{{ __('A manager can add them under Services.') }}</p>
                    @if(in_array(session('staff.role'), ['manager', 'admin'], true))<a class="btn btn-primary" href="{{ route('services.list') }}">{{ __('Add services') }}</a>@endif
                </div>
            @endforelse
            <p class="empty" id="noMatch" hidden>{{ __('No services match your search.') }}</p>
        </div>
    </section>

    {{-- ===== Order, customer, payment ===== --}}
    <div class="pos-side" id="posSide">
        <header class="pos-side-head">
            <button type="button" class="icon-btn" id="closeSide" aria-label="{{ __('Back to services') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i></button>
            <h2>{{ __('Review order') }}</h2>
        </header>

        <section class="pos-cart" aria-labelledby="cartTitle">
            <header class="panel-head">
                <h2 id="cartTitle">{{ __('Order') }} <span class="chip-count" id="lineCount">0</span></h2>
                <button type="button" class="btn btn-sm btn-ghost" id="clearCart" disabled><i class="fas fa-trash" aria-hidden="true"></i> {{ __('Clear') }}</button>
            </header>
            <div class="panel-scroll">
                <ul class="cart-lines" id="cartLines" aria-label="{{ __('Services in this order') }}"></ul>
                <div class="cart-empty" id="cartEmpty">
                    <x-mascot mood="curious" class="mascot-ink" />
                    <p class="empty-title">{{ __('Tap a service to add it') }}</p>
                    <p>{{ __('Each tap adds one. Change amounts here.') }}</p>
                </div>
                @if($addonChoices->isNotEmpty())
                <div class="panel-block">
                    <h3 class="eyebrow">{{ __('Add-ons') }}</h3>
                    <div class="chips" id="addonChips">
                        @foreach($addonChoices as $addon)
                            <button type="button" class="chip addon-chip" aria-pressed="false" data-addon="{{ $addon['id'] }}"><i class="fas fa-plus" aria-hidden="true"></i> {{ $addon['name'] }} <span class="addon-price">{{ money($addon['price']) }}</span></button>
                        @endforeach
                    </div>
                </div>
                @endif
                <div class="panel-block form-grid">
                    <div class="form-row">
                        <div class="field">
                            <label class="label" for="orderDate">{{ __('Order date') }}</label>
                            <input class="input" type="date" id="orderDate">
                        </div>
                        <div class="field">
                            <label class="label" for="readyBy">{{ __('Ready by') }}</label>
                            <input class="input" type="date" id="readyBy">
                        </div>
                    </div>
                    <div class="chips" id="readyChips" role="group" aria-label="{{ __('Ready by') }}">
                        <button type="button" class="chip" data-days="0" aria-pressed="false">{{ __('Same day') }}</button>
                        <button type="button" class="chip" data-days="1" aria-pressed="false">{{ __('Next day') }}</button>
                        @if($turnaround > 1)<button type="button" class="chip" data-days="{{ $turnaround }}" aria-pressed="false">{{ __('In :count days', ['count' => $turnaround]) }}</button>@endif
                    </div>
                    <div class="field">
                        <label class="label" for="weight">{{ __('Weight in kg') }} <span class="muted">{{ __('(optional)') }}</span></label>
                        <input class="input" type="number" id="weight" min="0" max="9999" step="any" inputmode="decimal" value="{{ $editing && $order->weight !== null ? (float) $order->weight : '' }}">
                    </div>
                    <div class="field">
                        <label class="label" for="notes">{{ __('Notes') }}</label>
                        <textarea class="textarea" id="notes" rows="2" maxlength="500" placeholder="{{ __('Stains, special care…') }}">{{ $editing ? $order->notes : '' }}</textarea>
                    </div>
                </div>
            </div>
        </section>

        <section class="pos-checkout" aria-label="{{ __('Customer and payment') }}">
            <div class="panel-scroll">
                <div class="panel-block" id="customerBlock">
                    <h2 class="eyebrow" id="customerLabel">{{ __('Customer') }}</h2>
                    <div id="customerPicker">
                        <div class="search">
                            <i class="fas fa-user" aria-hidden="true"></i>
                            <input class="input" type="search" id="customerSearch" placeholder="{{ __('Name or phone') }}" autocomplete="off"
                                   role="combobox" aria-labelledby="customerLabel" aria-expanded="false" aria-controls="customerResults" aria-autocomplete="list" aria-describedby="customerError">
                        </div>
                        <ul class="customer-results" id="customerResults" role="listbox" aria-label="{{ __('Matching customers') }}" hidden></ul>
                        <div class="chips recent" id="recentCustomers" aria-label="{{ __('Recent customers') }}"></div>
                        <button type="button" class="btn btn-soft btn-block" id="newCustomerBtn" data-dialog-open="newCustomerDialog"><i class="fas fa-user-plus" aria-hidden="true"></i> {{ __('New customer') }}</button>
                    </div>
                    <div class="customer-card" id="customerCard" hidden>
                        <span class="avatar" id="customerInitial" aria-hidden="true"></span>
                        <div class="customer-info">
                            <strong id="customerName"></strong>
                            <span class="muted" id="customerContact"></span>
                            <span class="customer-stats" id="customerStats"></span>
                        </div>
                        <button type="button" class="btn btn-sm btn-ghost" id="changeCustomer">{{ __('Change') }}</button>
                    </div>
                    <button type="button" class="btn btn-secondary btn-block" id="repeatLast" hidden><i class="fas fa-rotate-right" aria-hidden="true"></i> <span>{{ __('Repeat last order') }}</span></button>
                    <p class="field-error" id="customerError"></p>
                </div>

                <div class="panel-block">
                    <div class="summary">
                        <div class="summary-row"><span>{{ __('Services') }}</span><span id="sumServices">{{ money(0) }}</span></div>
                        <div class="summary-row"><span>{{ __('Add-ons') }}</span><span id="sumAddons">{{ money(0) }}</span></div>
                        <div class="summary-row"><span id="discountLabel">{{ __('Discount') }}</span><span id="sumDiscount">{{ money(0) }}</span></div>
                    </div>
                    <div class="discount-ctl">
                        <div class="segmented" role="radiogroup" aria-label="{{ __('Discount type') }}">
                            <label><input type="radio" name="discountType" value="amount" @checked($initial['discountType'] === 'amount')><span>{{ currency_symbol() }}</span></label>
                            <label><input type="radio" name="discountType" value="percent" @checked($initial['discountType'] === 'percent')><span>%</span></label>
                        </div>
                        <input class="input" type="number" id="discountValue" min="0" step="{{ money_step() }}" inputmode="decimal" placeholder="{{ __('No discount') }}" aria-label="{{ __('Discount') }}" value="{{ $initial['discountValue'] ?: '' }}">
                    </div>
                    <div class="chips" id="percentChips" hidden>
                        @foreach([5, 10, 15, 20] as $pct)<button type="button" class="chip" data-pct="{{ $pct }}">{{ $pct }}%</button>@endforeach
                    </div>
                    <div class="summary" style="margin-top: var(--s-3)">
                        <div class="summary-row summary-total total-row"><span>{{ __('Total') }}</span><span id="sumTotal">{{ money(0) }}</span></div>
                        @if($editing)
                            <div class="summary-row"><span>{{ __('Paid so far') }}</span><span>{{ money($order->paid_amount) }}</span></div>
                            <div class="summary-row"><span>{{ __('Balance') }}</span><span id="sumBalance">{{ money(0) }}</span></div>
                        @endif
                    </div>
                </div>
            </div>
            <footer class="checkout-actions">
                <button type="button" class="btn btn-secondary btn-lg" id="saveOnly">{{ $editing ? __('Save changes') : __('Pay later') }}</button>
                <button type="button" class="btn btn-primary btn-lg" id="chargeBtn"><span id="chargeLabel">{{ $editing ? __('Collect') : __('Charge') }}</span> <span id="chargeAmount">{{ money(0) }}</span></button>
            </footer>
        </section>
    </div>
</main>

<div class="pos-bar">
    <button type="button" class="btn btn-primary btn-block" id="openSide"><span id="barCount">{{ __(':count items', ['count' => 0]) }}</span><span>{{ __('Review order') }} <i class="fas fa-chevron-up" aria-hidden="true"></i></span><span id="barTotal">{{ money(0) }}</span></button>
</div>

<div class="sr-only" aria-live="polite" id="announcer"></div>

{{-- ===== Dialogs ===== --}}
<dialog id="paymentDialog" class="dialog dialog-lg" aria-labelledby="payTitle">
    <form id="payForm">
        <div class="dialog-header">
            <h2 class="dialog-title" id="payTitle">{{ __('Payment') }}</h2>
            <button type="button" class="icon-btn" data-dialog-close aria-label="{{ __('Close') }}"><i class="fas fa-xmark" aria-hidden="true"></i></button>
        </div>
        <div class="dialog-body pay-grid">
            <div class="stack-sm">
            <div class="pay-due"><span class="muted">{{ __('Amount due') }}</span><strong id="payDue">{{ money(0) }}</strong></div>
            <div class="segmented block" role="radiogroup" aria-label="{{ __('Payment method') }}">
                @foreach(['cash' => ['Cash', 'fa-money-bill-wave'], 'card' => ['Card', 'fa-credit-card'], 'transfer' => ['Transfer', 'fa-building-columns'], 'other' => ['Other', 'fa-ellipsis']] as $value => [$label, $icon])
                    <label><input type="radio" name="payMethod" value="{{ $value }}" @checked($value === 'cash')><span><i class="fas {{ $icon }}" aria-hidden="true"></i> {{ $label }}</span></label>
                @endforeach
            </div>
            <div class="field">
                <label class="label" for="tendered">{{ __('Amount received') }}</label>
                <div class="input-affix"><span class="affix" aria-hidden="true">{{ currency_symbol() }}</span><input class="input tendered" type="text" id="tendered" inputmode="decimal" autocomplete="off" aria-describedby="payResult"></div>
            </div>
            <div class="quick-cash" aria-label="{{ __('Quick amounts') }}">
                <button type="button" class="btn btn-secondary" data-cash="exact">{{ __('Exact') }}</button>
                @foreach(array_slice(app(\App\Support\Format::class)->currencyInfo()['bills'], -4) as $bill)<button type="button" class="btn btn-secondary" data-cash="{{ $bill }}">{{ money($bill, whole: true) }}</button>@endforeach
            </div>
            <p class="pay-result" id="payResult" aria-live="polite"></p>
            </div>
            <div class="keypad" aria-label="{{ __('Number pad') }}">
                {{-- Currencies without decimals (yen, rupiah) get a 00 key instead of the point --}}
                @foreach(['1', '2', '3', '4', '5', '6', '7', '8', '9', app(\App\Support\Format::class)->decimals() > 0 ? '.' : '00', '0'] as $key)<button type="button" data-key="{{ $key }}">{{ $key }}</button>@endforeach
                <button type="button" data-key="back" aria-label="{{ __('Delete last digit') }}"><i class="fas fa-delete-left" aria-hidden="true"></i></button>
            </div>
        </div>
        <div class="dialog-footer">
            <button type="button" class="btn btn-secondary" data-dialog-close>{{ __('Back') }}</button>
            <button type="submit" class="btn btn-primary btn-lg" id="payConfirm">{{ __('Complete') }}</button>
        </div>
    </form>
</dialog>

<dialog id="newCustomerDialog" class="dialog dialog-sm" aria-labelledby="newCustomerTitle">
    <form id="newCustomerForm" novalidate>
        <div class="dialog-header">
            <h2 class="dialog-title" id="newCustomerTitle">{{ __('New customer') }}</h2>
            <button type="button" class="icon-btn" data-dialog-close aria-label="{{ __('Close') }}"><i class="fas fa-xmark" aria-hidden="true"></i></button>
        </div>
        <div class="dialog-body">
            <div class="field"><label class="label" for="nc-name">{{ __('Name') }} <span class="req" aria-hidden="true">*</span></label><input class="input" id="nc-name" name="name" maxlength="255" autocomplete="off" required><p class="field-error"></p></div>
            <div class="field"><label class="label" for="nc-contact">{{ __('Phone') }}</label><input class="input" id="nc-contact" name="contact" type="tel" inputmode="numeric" maxlength="15" placeholder="09123456789"><p class="help">{{ __('Optional. 10 to 15 digits.') }}</p><p class="field-error"></p></div>
            <div class="field"><label class="label" for="nc-address">{{ __('Address') }}</label><textarea class="textarea" id="nc-address" name="address" rows="2" maxlength="500"></textarea><p class="field-error"></p></div>
        </div>
        <div class="dialog-footer">
            <button type="button" class="btn btn-secondary" data-dialog-close>{{ __('Cancel') }}</button>
            <button type="submit" class="btn btn-primary">{{ __('Save customer') }}</button>
        </div>
    </form>
</dialog>

<dialog id="successDialog" class="dialog dialog-sm" aria-labelledby="okTitle" data-keep-open>
    <div class="dialog-body success-body">
        <x-mascot mood="proud" class="mascot-ink" />
        <h2 class="dialog-title" id="okTitle">{{ __('Order saved') }}</h2>
        <p class="muted" id="okNumber"></p>
        <div class="ok-change" id="okChange" hidden><span>{{ __('Change to give back') }}</span><strong id="okChangeAmount"></strong></div>
        <p id="okBalance"></p>
        <div class="ok-print">
            <span class="eyebrow"><i class="fas fa-print" aria-hidden="true"></i> {{ __('Print') }}</span>
            <div class="cluster">
                <a class="btn btn-secondary" id="okTicket" href="#" data-print><i class="fas fa-ticket" aria-hidden="true"></i> {{ __('Claim ticket') }}</a>
                <a class="btn btn-secondary" id="okJob" href="#" data-print><i class="fas fa-clipboard-list" aria-hidden="true"></i> {{ __('Job order') }}</a>
                <a class="btn btn-secondary" id="okReceipt" href="#" data-print><i class="fas fa-receipt" aria-hidden="true"></i> {{ __('Receipt') }}</a>
            </div>
        </div>
    </div>
    <div class="dialog-footer success-actions">
        <a class="btn btn-secondary" id="okView" href="#"><i class="fas fa-eye" aria-hidden="true"></i> {{ __('View order') }}</a>
        <a class="btn btn-primary" href="{{ route('pos.index') }}" id="okNew"><i class="fas fa-plus" aria-hidden="true"></i> {{ __('Next order') }}</a>
    </div>
</dialog>

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

<div id="toastRegion" class="toast-region" aria-live="polite"></div>

<script src="{{ asset_v('js/app.js') }}"></script>
<script>
(function () {
    'use strict';
    const $ = id => document.getElementById(id);
    const services = new Map(@json($posServices));
    const addonChoices = new Map(@json($posAddons));
    const recent = @json($posRecent);
    const initial = @json($initial);
    const editing = initial.orderId !== null;
    const ymd = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    const today = ymd(new Date());
    const TURNAROUND = @json($turnaround);
    const DRAFT = 'soapOpera.posDraft';

    // ---------------- State ----------------
    const state = {
        items: initial.items.map(i => ({ ...i })),       // [{id, name, price, qty}] in the order they were added
        addons: new Set(initial.addons),
        customer: null,
        summary: null,                                   // the picked customer's history (repeat last order)
    };
    const FMT = window.FMT;

    // ---------------- Totals (same rules as the server) ----------------
    function totals() {
        const servicesTotal = state.items.reduce((s, i) => s + i.price * i.qty, 0);
        const addonsTotal = [...state.addons].reduce((s, id) => s + (addonChoices.get(id)?.price || 0), 0);
        const gross = roundMoney(servicesTotal + addonsTotal);
        const value = Math.max(0, parseFloat($('discountValue').value) || 0);
        const percent = discountType() === 'percent';
        const discount = Math.min(percent ? roundMoney(gross * Math.min(value, 100) / 100) : value, gross);
        const total = roundMoney(gross - discount);
        return { services: servicesTotal, addons: addonsTotal, gross, discount, total, due: Math.max(0, roundMoney(total - initial.paid)), percent, value };
    }
    const discountType = () => document.querySelector('input[name="discountType"]:checked').value;
    const itemCount = () => state.items.reduce((s, i) => s + i.qty, 0);

    // ---------------- Rendering ----------------
    function render() {
        const list = $('cartLines');
        list.replaceChildren(...state.items.map(lineElement));
        $('cartEmpty').hidden = state.items.length > 0;
        $('lineCount').textContent = itemCount();
        $('clearCart').disabled = state.items.length === 0 && state.addons.size === 0;

        document.querySelectorAll('.tile').forEach(tile => {
            const line = state.items.find(i => i.id === Number(tile.dataset.service));
            tile.classList.toggle('in-cart', Boolean(line));
            const badge = tile.querySelector('.tile-qty');
            badge.hidden = !line;
            badge.textContent = line ? line.qty : '';
        });
        document.querySelectorAll('[data-addon]').forEach(chip => chip.setAttribute('aria-pressed', String(state.addons.has(Number(chip.dataset.addon)))));

        const sum = totals();
        $('sumServices').textContent = money(sum.services);
        $('sumAddons').textContent = money(sum.addons);
        $('sumDiscount').textContent = sum.discount > 0 ? '−' + money(sum.discount) : money(0);
        $('discountLabel').textContent = sum.percent && sum.value > 0 ? t('Discount (:percent%)', { percent: Math.min(sum.value, 100) }) : t('Discount');
        $('percentChips').hidden = !sum.percent;
        document.querySelectorAll('[data-pct]').forEach(c => c.setAttribute('aria-pressed', String(sum.percent && Number(c.dataset.pct) === sum.value)));
        $('sumTotal').textContent = money(sum.total);
        if (editing) $('sumBalance').textContent = money(sum.due);
        $('chargeAmount').textContent = money(editing ? sum.due : sum.total);
        $('chargeBtn').disabled = editing && sum.due <= 0;
        $('barCount').textContent = t(itemCount() === 1 ? ':count item' : ':count items', { count: itemCount() });
        $('barTotal').textContent = money(sum.total);
        saveDraft();
    }

    function lineElement(item) {
        const li = document.createElement('li');
        li.className = 'cart-line';
        li.innerHTML = `
            <div><div class="line-name"></div><div class="line-unit"></div></div>
            <div class="line-total"></div>
            <div class="line-controls">
                <div class="stepper">
                    <button type="button" data-step="-1"><i class="fas fa-minus" aria-hidden="true"></i></button>
                    <input type="number" inputmode="numeric" min="1" max="10000" step="1">
                    <button type="button" data-step="1"><i class="fas fa-plus" aria-hidden="true"></i></button>
                </div>
                <button type="button" class="icon-btn danger" data-remove><i class="fas fa-trash" aria-hidden="true"></i></button>
            </div>`;
        li.querySelector('.line-name').textContent = item.name;
        li.querySelector('.line-unit').textContent = t(':price each', { price: money(item.price) });
        li.querySelector('.line-total').textContent = money(item.price * item.qty);
        const input = li.querySelector('input');
        input.value = item.qty;
        input.setAttribute('aria-label', t('Quantity of :name', { name: item.name }));
        li.querySelector('[data-step="-1"]').setAttribute('aria-label', t('One less :name', { name: item.name }));
        li.querySelector('[data-step="1"]').setAttribute('aria-label', t('One more :name', { name: item.name }));
        li.querySelector('[data-remove]').setAttribute('aria-label', t('Remove :name', { name: item.name }));
        li.querySelectorAll('[data-step]').forEach(b => b.addEventListener('click', () => setQty(item.id, item.qty + Number(b.dataset.step))));
        input.addEventListener('change', () => setQty(item.id, Math.min(10000, Math.max(0, parseInt(input.value, 10) || 0))));
        li.querySelector('[data-remove]').addEventListener('click', () => setQty(item.id, 0));
        return li;
    }

    const announce = text => { $('announcer').textContent = text; };

    // ---------------- Cart actions ----------------
    function addService(id, qty = 1) {
        const service = services.get(id);
        if (!service) return false;
        const line = state.items.find(i => i.id === id);
        if (line) line.qty = Math.min(10000, line.qty + qty);
        else state.items.push({ ...service, qty });
        render();
        announce(`${service.name}: ${state.items.find(i => i.id === id).qty} in order`);
        return true;
    }

    function setQty(id, qty) {
        const index = state.items.findIndex(i => i.id === id);
        if (index === -1) return;
        if (qty >= 1) { state.items[index].qty = qty; render(); return; }
        const [removed] = state.items.splice(index, 1);
        render();
        showToast(t('Removed :name.', { name: removed.name }), 'info', 5000, { label: t('Undo'), onClick: () => { state.items.splice(index, 0, removed); render(); } });
    }

    document.querySelectorAll('.tile').forEach(tile => {
        tile.addEventListener('click', () => {
            addService(Number(tile.dataset.service));
            tile.classList.remove('pop');
            void tile.offsetWidth;   // restart the tap animation
            tile.classList.add('pop');
        });
    });

    document.querySelectorAll('[data-addon]').forEach(chip => {
        chip.addEventListener('click', () => {
            const id = Number(chip.dataset.addon);
            state.addons.has(id) ? state.addons.delete(id) : state.addons.add(id);
            render();
        });
    });

    $('clearCart').addEventListener('click', () => {
        const before = { items: state.items.map(i => ({ ...i })), addons: new Set(state.addons) };
        state.items = [];
        state.addons.clear();
        render();
        showToast(t('Order cleared.'), 'info', 6000, { label: t('Undo'), onClick: () => { state.items = before.items; state.addons = before.addons; render(); } });
    });

    // Discount: ₱ or %, with quick percentages
    document.querySelectorAll('input[name="discountType"]').forEach(r => r.addEventListener('change', render));
    $('discountValue').addEventListener('input', () => {
        if (discountType() === 'percent' && parseFloat($('discountValue').value) > 100) $('discountValue').value = 100;
        render();
    });
    document.querySelectorAll('[data-pct]').forEach(chip => chip.addEventListener('click', () => { $('discountValue').value = chip.dataset.pct; render(); }));

    // ---------------- Catalog search + type chips ----------------
    let activeCat = 'all';
    function filterCatalog() {
        const query = $('serviceSearch').value.trim().toLowerCase();
        let any = false;
        document.querySelectorAll('.tile-group').forEach(group => {
            let shown = 0;
            group.querySelectorAll('.tile').forEach(tile => {
                const visible = (activeCat === 'all' || group.dataset.cat === activeCat) && tile.dataset.name.includes(query);
                tile.hidden = !visible;
                if (visible) shown++;
            });
            group.hidden = shown === 0;
            if (shown) any = true;
        });
        $('noMatch').hidden = any || !document.querySelector('.tile-group');
    }
    $('serviceSearch').addEventListener('input', filterCatalog);
    // Enter in the search adds the only (or first) match
    $('serviceSearch').addEventListener('keydown', e => {
        if (e.key !== 'Enter') return;
        const first = document.querySelector('.tile:not([hidden])');
        if (first) { e.preventDefault(); first.click(); }
    });
    document.querySelectorAll('[data-cat]').forEach(chip => {
        if (!chip.classList.contains('chip')) return;
        chip.addEventListener('click', () => {
            activeCat = chip.dataset.cat;
            document.querySelectorAll('.chip[data-cat]').forEach(c => c.setAttribute('aria-pressed', String(c === chip)));
            filterCatalog();
            $('tilesScroll').scrollTo({ top: 0, behavior: 'smooth' });
        });
    });

    // ---------------- Customer ----------------
    const search = $('customerSearch');
    const results = $('customerResults');
    let highlighted = -1;

    // Type-ahead against the server: a short pause after typing, the older request
    // cancelled, and answers kept for the session so going back is instant
    const found = new Map();
    let pending = null;
    let timer = null;
    async function lookup(term) {
        if (found.has(term)) return found.get(term);
        pending?.abort();
        pending = new AbortController();
        const response = await fetch(appUrl('customers/search?q=' + encodeURIComponent(term)), { headers: { 'Accept': 'application/json' }, signal: pending.signal });
        if (!response.ok) throw new Error(await responseErrorMessage(response));
        const list = (await response.json()).customers;
        found.set(term, list);
        return list;
    }
    function showLoading() {
        results.replaceChildren(...[0, 1, 2].map(() => {
            const li = document.createElement('li');
            li.className = 'skeleton-row';
            li.innerHTML = '<span class="skeleton" style="width:36px;height:36px;border-radius:50%"></span><span class="skeleton" style="flex:1;height:14px"></span>';
            return li;
        }));
        results.hidden = false;
    }
    function onType() {
        clearTimeout(timer);
        const term = search.value.trim();
        if (!term) { results.hidden = true; search.setAttribute('aria-expanded', 'false'); return; }
        if (!found.has(term)) showLoading();
        timer = setTimeout(async () => {
            try { showResults(await lookup(term)); }
            catch (e) { if (e.name !== 'AbortError') showToast(e.message, 'error'); }
        }, found.has(term) ? 0 : 160);
    }
    function showResults(list) {
        if (search.value.trim() === '') return;
        const found = list;
        highlighted = found.length ? 0 : -1;
        results.replaceChildren();
        if (!found.length) {
            const none = document.createElement('li');
            none.className = 'no-hit';
            none.textContent = t('No customer found. Add them with New customer.');
            results.append(none);
        }
        found.forEach((c, i) => {
            const li = document.createElement('li');
            li.id = 'cust-' + c.id;
            li.setAttribute('role', 'option');
            li.setAttribute('aria-selected', String(i === highlighted));
            li.innerHTML = '<span class="avatar" aria-hidden="true"></span><span><strong></strong><br><span class="muted"></span></span>';
            li.querySelector('.avatar').textContent = c.name.charAt(0).toUpperCase();
            li.querySelector('strong').textContent = c.name;
            li.querySelector('.muted').textContent = c.contact || t('No phone');
            li.addEventListener('click', () => pickCustomer(c));
            results.append(li);
        });
        results.hidden = false;
        search.setAttribute('aria-expanded', 'true');
        search.setAttribute('aria-activedescendant', found.length ? 'cust-' + found[0].id : '');
    }
    search.addEventListener('input', onType);
    search.addEventListener('keydown', e => {
        const options = [...results.querySelectorAll('[role="option"]')];
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            if (!options.length) return;
            e.preventDefault();
            highlighted = (highlighted + (e.key === 'ArrowDown' ? 1 : -1) + options.length) % options.length;
            options.forEach((o, i) => o.setAttribute('aria-selected', String(i === highlighted)));
            search.setAttribute('aria-activedescendant', options[highlighted].id);
            options[highlighted].scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'Enter' && highlighted >= 0 && options[highlighted]) {
            e.preventDefault();
            options[highlighted].click();
        } else if (e.key === 'Escape') {
            results.hidden = true;
            search.setAttribute('aria-expanded', 'false');
        }
    });

    function renderRecent() {
        const box = $('recentCustomers');
        box.replaceChildren();
        recent.forEach(c => {
            const chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'chip';
            chip.textContent = c.name;
            chip.addEventListener('click', () => pickCustomer(c));
            box.append(chip);
        });
        box.hidden = box.children.length === 0;
    }

    async function pickCustomer(customer) {
        state.customer = customer;
        state.summary = null;
        $('customerError').textContent = '';
        $('customerPicker').hidden = true;
        $('customerCard').hidden = false;
        $('customerInitial').textContent = customer.name.charAt(0).toUpperCase();
        $('customerName').textContent = customer.name;
        $('customerContact').textContent = customer.contact || t('No phone');
        $('customerStats').innerHTML = '<span class="skeleton" style="display:inline-block;width:120px;height:12px"></span>';
        $('repeatLast').hidden = true;
        results.hidden = true;
        search.value = '';
        search.setAttribute('aria-expanded', 'false');
        saveDraft();
        try {
            const summary = await sendJson(appUrl(`pos/customers/${customer.id}/summary`), {}, 'GET');
            if (state.customer !== customer) return;
            state.summary = summary;
            $('customerStats').textContent = t(summary.orders_count === 1 ? ':count order' : ':count orders', { count: summary.orders_count });
            if (summary.balance_due > 0) {
                const due = document.createElement('span');
                due.className = 'text-danger';
                due.textContent = ' · ' + t(':amount due', { amount: money(summary.balance_due) });
                $('customerStats').append(due);
            }
            if (!editing && summary.last_order) {
                $('repeatLast').hidden = false;
                $('repeatLast').querySelector('span').textContent = t('Repeat last order (:date)', { date: summary.last_order.order_date });
            }
        } catch (e) { /* history is a nice-to-have */ }
    }

    $('changeCustomer').addEventListener('click', () => {
        state.customer = null;
        state.summary = null;
        $('customerCard').hidden = true;
        $('customerPicker').hidden = false;
        $('repeatLast').hidden = true;
        saveDraft();
        search.focus();
    });

    $('repeatLast').addEventListener('click', () => {
        const last = state.summary?.last_order;
        if (!last) return;
        let skipped = 0;
        last.items.forEach(item => { if (!item.available || !addService(item.service_id, item.qty)) skipped++; });
        last.addon_ids.forEach(id => { if (addonChoices.has(id)) state.addons.add(id); });
        render();
        showToast(skipped ? t(skipped > 1 ? 'Added the last order. :count services are no longer offered.' : 'Added the last order. 1 service is no longer offered.', { count: skipped }) : t('Added the last order.'), skipped ? 'warning' : 'success');
    });

    // New customer (saved right away, then picked)
    $('newCustomerForm').addEventListener('submit', async e => {
        e.preventDefault();
        const form = e.target;
        const button = form.querySelector('[type="submit"]');
        form.querySelectorAll('.field-error').forEach(el => { el.textContent = ''; });
        form.querySelectorAll('[aria-invalid]').forEach(el => el.removeAttribute('aria-invalid'));
        if (!form.name.value.trim()) { form.name.setAttribute('aria-invalid', 'true'); form.name.closest('.field').querySelector('.field-error').textContent = t('Enter the customer\'s name.'); form.name.focus(); return; }
        button.classList.add('is-loading');
        try {
            const data = await sendJson(appUrl('customers'), { name: form.name.value.trim(), contact: form.contact.value.trim(), address: form.address.value.trim() });
            const customer = { id: data.customer.id, name: data.customer.name, contact: data.customer.contact };
            found.clear();
            closeDialog('newCustomerDialog');
            form.reset();
            pickCustomer(customer);
            showToast(t(':name added.', { name: customer.name }), 'success');
        } catch (err) {
            Object.entries(err.errors || {}).forEach(([name, messages]) => {
                const field = form.querySelector(`[name="${name}"]`);
                if (field) { field.setAttribute('aria-invalid', 'true'); field.closest('.field').querySelector('.field-error').textContent = messages[0]; }
            });
            if (!err.errors) showToast(err.message, 'error');
        } finally {
            button.classList.remove('is-loading');
        }
    });
    $('newCustomerBtn').addEventListener('click', () => {
        const typed = search.value.trim();
        if (typed && /^\d{10,15}$/.test(typed)) $('nc-contact').value = typed;
        else if (typed) $('nc-name').value = typed;
    });

    // ---------------- Saving ----------------
    function problems() {
        if (!state.items.length) return t('Add at least one service.');
        if (!state.customer) {
            $('customerError').textContent = t('Pick or add a customer first.');
            openSide();
            search.focus();
            return t('Pick or add a customer first.');
        }
        return null;
    }

    async function save(paymentAmount = 0, method = 'cash') {
        const sum = totals();
        const payload = {
            customer_id: state.customer.id,
            order_date: $('orderDate').value || today,
            ready_by: $('readyBy').value || null,
            weight: $('weight').value || null,
            notes: $('notes').value,
            discount: sum.value,
            discount_type: discountType(),
            items: state.items.map(i => ({ service_id: i.id, qty: i.qty })),
            addons: [...state.addons].map(id => ({ addon_id: id })),
            payment_amount: paymentAmount,
            payment_method: method,
        };
        return editing
            ? sendJson(appUrl(`pos/${initial.orderId}`), payload, 'PUT')
            : sendJson(appUrl('pos/orders'), payload);
    }

    function finished(data, tendered) {
        clearDraft();
        const order = data.order;
        const due = totals().due;
        if (editing) {
            flashToast(tendered > 0 ? t('Order updated. Payment of :amount recorded.', { amount: money(Math.min(tendered, due)) }) : t('Order updated.'));
            location.href = appUrl(`orders/${order.id}/details`);
            return;
        }
        $('okNumber').textContent = order.order_number;
        const change = Math.max(0, roundMoney(tendered - due));
        $('okChange').hidden = change <= 0;
        $('okChangeAmount').textContent = money(change);
        $('okBalance').textContent = order.balance > 0 ? t(':amount to collect at pickup.', { amount: money(order.balance) }) : t('Paid in full.');
        $('okTicket').href = appUrl(`orders/${order.id}/print/ticket`);
        $('okJob').href = appUrl(`orders/${order.id}/print/job`);
        $('okReceipt').href = appUrl(`orders/${order.id}/print/receipt`);
        $('okReceipt').hidden = !(order.paid_amount > 0);   // a receipt is for a payment
        $('okView').href = appUrl(`orders/${order.id}/details`);
        openDialog('successDialog');
        $('okNew').focus();
    }

    $('saveOnly').addEventListener('click', async e => {
        const problem = problems();
        if (problem) { showToast(problem, 'warning'); return; }
        e.currentTarget.classList.add('is-loading');
        try { finished(await save(0), 0); }
        catch (err) { showToast(err.message, 'error', 8000); }
        finally { e.currentTarget.classList.remove('is-loading'); }
    });

    // ---------------- Payment sheet (keypad, quick cash, method) ----------------
    const tendered = $('tendered');
    if (matchMedia('(pointer: coarse)').matches) tendered.inputMode = 'none';   // our keypad is the keyboard
    function describePayment() {
        const due = totals().due;
        const value = parseFloat(tendered.value) || 0;
        const result = $('payResult');
        const confirm = $('payConfirm');
        if (value <= 0) { result.textContent = t('Enter the amount received.'); confirm.textContent = t('Complete'); confirm.disabled = true; return; }
        confirm.disabled = false;
        if (value >= due) {
            result.innerHTML = value > due ? t('Change: :amount', { amount: `<span class="text-success">${money(value - due)}</span>` }) : escapeHtml(t('Exact amount.'));
            confirm.textContent = t('Complete :amount', { amount: money(due) });
        } else {
            result.innerHTML = t('Partial payment · :amount due at pickup', { amount: `<span class="text-danger">${money(due - value)}</span>` });
            confirm.textContent = t('Record :amount', { amount: money(value) });
        }
    }
    function openPayment() {
        const problem = problems();
        if (problem) { showToast(problem, 'warning'); return; }
        const due = totals().due;
        if (due <= 0 && editing) { showToast('Nothing left to collect on this order.', 'info'); return; }
        $('payDue').textContent = money(due);
        tendered.value = due.toFixed(FMT.decimals);
        describePayment();
        openDialog('paymentDialog');
        tendered.focus();
        tendered.select();
    }
    $('chargeBtn').addEventListener('click', openPayment);
    tendered.addEventListener('input', () => { tendered.value = tendered.value.replace(/[^\d.]/g, ''); describePayment(); });
    document.querySelectorAll('[data-cash]').forEach(b => b.addEventListener('click', () => {
        tendered.value = (b.dataset.cash === 'exact' ? totals().due : Number(b.dataset.cash)).toFixed(FMT.decimals);
        fresh = true;
        describePayment();
    }));
    let fresh = true;   // the first key replaces the pre-filled amount
    tendered.addEventListener('focus', () => { fresh = true; });
    document.querySelectorAll('[data-key]').forEach(b => b.addEventListener('click', () => {
        let v = fresh ? '' : tendered.value;
        fresh = false;
        const key = b.dataset.key;
        if (key === 'back') v = v.slice(0, -1);
        else if (key === '.') { if (!v.includes('.')) v = (v || '0') + '.'; }
        else if (key === '00') { if (v && v !== '0' && v.length < 8) v += '00'; }
        else if (!new RegExp('\\.\\d{' + FMT.decimals + '}$').test(v) && v.replace('.', '').length < 9) v = (v === '0' ? '' : v) + key;
        tendered.value = v;
        describePayment();
    }));
    $('payForm').addEventListener('submit', async e => {
        e.preventDefault();
        const value = parseFloat(tendered.value) || 0;
        if (value <= 0) return;
        const button = $('payConfirm');
        button.classList.add('is-loading');
        try {
            const data = await save(value, document.querySelector('input[name="payMethod"]:checked').value);
            closeDialog('paymentDialog');
            finished(data, value);
        } catch (err) {
            showToast(err.message, 'error', 8000);
        } finally {
            button.classList.remove('is-loading');
        }
    });

    // ---------------- Phones: the order slides up ----------------
    const side = $('posSide');
    function openSide() { if (matchMedia('(max-width: 1023px)').matches) { side.classList.add('is-open'); $('closeSide').focus(); } }
    $('openSide').addEventListener('click', openSide);
    $('closeSide').addEventListener('click', () => { side.classList.remove('is-open'); $('openSide').focus(); });

    // ---------------- Leaving, keyboard shortcuts ----------------
    $('leavePos').addEventListener('click', async e => {
        if (!editing) return;   // a new order is kept as a draft on this device
        e.preventDefault();
        const ok = await confirmAction({ title: t('Leave without saving?'), message: t('Your changes to this order will be lost.'), confirmText: t('Leave'), cancelText: t('Keep editing') });
        if (ok) location.href = e.currentTarget.href;
    });
    document.addEventListener('keydown', e => {
        if (document.querySelector('dialog[open]')) return;
        if (e.key === 'F2') { e.preventDefault(); openSide(); (state.customer ? $('changeCustomer') : search).focus(); }
        if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) { e.preventDefault(); openPayment(); }
    });

    // ---------------- Draft (a new order survives an accidental refresh) ----------------
    function saveDraft() {
        if (editing) return;
        try {
            if (!state.items.length && !state.addons.size) { localStorage.removeItem(DRAFT); return; }
            localStorage.setItem(DRAFT, JSON.stringify({ items: state.items, addons: [...state.addons], customer: state.customer, notes: $('notes').value, at: Date.now() }));
        } catch (e) { /* storage blocked */ }
    }
    function clearDraft() { try { localStorage.removeItem(DRAFT); } catch (e) { /* storage blocked */ } }
    $('notes').addEventListener('input', saveDraft);

    // ---------------- Ready by: the shop's usual turnaround, or a tap for rush jobs ----------------
    // Kept as days after the order date, so changing the order date moves it along
    const shiftDate = (date, days) => { const d = new Date(`${date}T12:00:00`); d.setDate(d.getDate() + days); return ymd(d); };
    const daysBetween = (from, to) => Math.round((new Date(`${to}T12:00:00`) - new Date(`${from}T12:00:00`)) / 864e5);
    let readyDays = initial.readyBy ? daysBetween(initial.date, initial.readyBy) : TURNAROUND;
    function showReadyBy() {
        const start = $('orderDate').value || today;
        $('readyBy').min = start;
        $('readyBy').value = shiftDate(start, readyDays);
        document.querySelectorAll('#readyChips [data-days]').forEach(chip => chip.setAttribute('aria-pressed', String(Number(chip.dataset.days) === readyDays)));
    }
    $('readyChips').addEventListener('click', e => {
        const chip = e.target.closest('[data-days]');
        if (chip) { readyDays = Number(chip.dataset.days); showReadyBy(); }
    });
    $('readyBy').addEventListener('change', () => {
        if ($('readyBy').value) readyDays = Math.max(0, daysBetween($('orderDate').value || today, $('readyBy').value));
        showReadyBy();
    });
    $('orderDate').addEventListener('change', showReadyBy);

    // ---------------- Start ----------------
    $('orderDate').value = initial.date || today;
    showReadyBy();
    renderRecent();
    let preset = initial.customer;
    if (!editing && !preset) {
        try {
            const draft = JSON.parse(localStorage.getItem(DRAFT) || 'null');
            if (draft && Date.now() - draft.at < 12 * 3600 * 1000) {
                state.items = draft.items.filter(i => services.has(i.id)).map(i => ({ ...services.get(i.id), qty: i.qty }));
                draft.addons.filter(id => addonChoices.has(id)).forEach(id => state.addons.add(id));
                $('notes').value = draft.notes || '';
                preset = draft.customer || null;
                if (state.items.length) {
                    showToast(t('Picked up your unsaved order.'), 'info', 8000, { label: t('Start over'), onClick: () => { state.items = []; state.addons.clear(); $('notes').value = ''; $('changeCustomer').click(); render(); } });
                }
            }
        } catch (e) { /* storage blocked */ }
    }
    if (preset) pickCustomer(preset);
    render();
})();
</script>
</body>
</html>
