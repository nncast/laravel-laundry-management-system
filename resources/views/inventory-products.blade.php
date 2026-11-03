@extends('layouts.app')

@section('title', __('Products'))
@section('page-title', __('Products'))
@php
    $lowCount = $products->filter(fn ($p) => $p->status === 'active' && $p->available_stock <= $p->minimum_stock_level)->count();
    // Inventory valuation: what the active stock cost to buy
    $stockValue = $products->where('status', 'active')->sum(fn ($p) => $p->available_stock * $p->purchase_price);
@endphp
@section('page-subtitle', __('Stock value :amount', ['amount' => money($stockValue)]) . ($lowCount ? ' · ' . __(':count running low', ['count' => $lowCount]) : ''))

@section('page-actions')
    <button type="button" class="btn btn-primary" data-form-dialog="productDialog" data-title="{{ __('Add product') }}" data-submit="{{ __('Save product') }}"
            data-action="{{ route('products.store') }}" data-method="POST" @disabled($categories->isEmpty() || $units->isEmpty())><i class="fas fa-plus" aria-hidden="true"></i> {{ __('Add product') }}</button>
@endsection

@section('content')
@if($categories->isEmpty() || $units->isEmpty())
    <div class="alert alert-info" style="margin-bottom: var(--s-4)"><i class="fas fa-circle-info" aria-hidden="true"></i><span>{!! __('Products need an active :category and :unit. Add those first.', ['category' => '<a href="' . e(route('categories.index')) . '">' . e(__('category')) . '</a>', 'unit' => '<a href="' . e(route('units.index')) . '">' . e(__('unit')) . '</a>']) !!}</span></div>
@endif

<div class="toolbar">
    <div class="search">
        <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
        <input class="input" type="search" placeholder="{{ __('Search products') }}" aria-label="{{ __('Search products') }}" data-table-filter="productsTable" autocomplete="off">
    </div>
    @if($lowCount)
        <button type="button" class="chip" aria-pressed="false" id="lowOnly"><i class="fas fa-triangle-exclamation" aria-hidden="true"></i> {{ __('Low stock only') }} <span class="chip-count">{{ $lowCount }}</span></button>
    @endif
</div>

<div class="card card-flush" id="listCard" data-refresh>
    @if($products->isEmpty())
        <div class="empty">
            <span class="empty-icon" aria-hidden="true"><i class="fas fa-boxes-stacked"></i></span>
            <p class="empty-title">{{ __('No products yet') }}</p>
            <p>{{ __('Track detergents, softeners and other supplies; the dashboard warns you when one runs low.') }}</p>
        </div>
    @else
    <div class="table-wrap">
        <table class="table table-stack" id="productsTable">
            <thead>
                <tr><th>{{ __('Product') }}</th><th>{{ __('Unit') }}</th><th class="num">{{ __('Purchase price') }}</th><th>{{ __('In stock') }}</th><th>{{ __('Status') }}</th><th class="actions"><span class="sr-only">{{ __('Actions') }}</span></th></tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                @php $low = $product->status === 'active' && $product->available_stock <= $product->minimum_stock_level; @endphp
                <tr data-low="{{ $low ? 1 : 0 }}">
                    <td class="cell-primary">
                        <div class="cell-title">{{ $product->name }}</div>
                        <div class="cell-sub">{{ $product->category->name ?? __('No category') }}</div>
                    </td>
                    <td data-label="{{ __('Unit') }}">{{ $product->unit->name ?? '—' }}</td>
                    <td data-label="{{ __('Purchase price') }}" class="num">{{ money($product->purchase_price) }}</td>
                    <td data-label="{{ __('In stock') }}">
                        <div>
                            <strong class="{{ $low ? 'text-danger' : '' }}">{{ $product->available_stock }}</strong>
                            <span class="cell-sub">· min {{ $product->minimum_stock_level }}</span>
                            @if($low)<span class="badge badge-warning">{{ __('Low') }}</span>@endif
                        </div>
                    </td>
                    <td data-label="{{ __('Status') }}"><span class="badge {{ $product->status === 'active' ? 'badge-active' : 'badge-inactive' }}">{{ $product->status === 'active' ? __('Active') : __('Inactive') }}</span></td>
                    <td class="actions">
                        <div class="cell-actions">
                            <button type="button" class="icon-btn" aria-label="{{ __('Edit :name', ['name' => $product->name]) }}" title="{{ __('Edit') }}"
                                    data-form-dialog="productDialog" data-title="{{ __('Edit product') }}" data-submit="{{ __('Save changes') }}"
                                    data-action="{{ route('products.update') }}" data-method="PUT"
                                    data-values="{{ json_encode(['product_id' => $product->id] + $product->only('name', 'category_id', 'unit_id', 'purchase_price', 'available_stock', 'minimum_stock_level', 'status')) }}"><i class="fas fa-pen" aria-hidden="true"></i></button>
                            <form method="POST" action="{{ route('products.destroy') }}" data-ajax data-success="{{ __('Product deleted.') }}"
                                  data-confirm="{{ __(':name will be removed.', ['name' => $product->name]) }}" data-confirm-title="{{ __('Delete this product?') }}">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <button type="submit" class="icon-btn danger" aria-label="{{ __('Delete :name', ['name' => $product->name]) }}" title="{{ __('Delete') }}"><i class="fas fa-trash" aria-hidden="true"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
                <tr class="no-match" hidden><td colspan="6" class="muted">{{ __('No products match your search.') }}</td></tr>
            </tbody>
        </table>
    </div>
    @endif
</div>

<x-form-dialog id="productDialog" :title="__('Add product')" :action="route('products.store')" :submit="__('Save product')" :success="__('Product saved.')">
    <input type="hidden" name="product_id" value="{{ old('product_id') }}">
    <x-field name="name" :label="__('Name')" required maxlength="255" :placeholder="__('e.g. Powder detergent 1 kg')" />
    <div class="form-row">
        <x-field name="category_id" :label="__('Category')" required :options="['' => __('Choose')] + $categories->pluck('name', 'id')->all()" />
        <x-field name="unit_id" :label="__('Unit')" required :options="['' => __('Choose')] + $units->pluck('name', 'id')->all()" />
    </div>
    <x-field name="purchase_price" :label="__('Purchase price')" type="number" :prefix="currency_symbol()" required min="0" :step="money_step()" inputmode="decimal" />
    <div class="form-row">
        <x-field name="available_stock" :label="__('In stock')" type="number" required min="0" step="1" inputmode="numeric" />
        <x-field name="minimum_stock_level" :label="__('Low-stock level')" type="number" required min="0" step="1" inputmode="numeric" :help="__('Warn when stock is at or below this')" />
    </div>
    <x-field name="status" :label="__('Status')" type="segmented" :options="['active' => __('Active'), 'inactive' => __('Inactive')]" data-default="active" />
</x-form-dialog>
@endsection

@push('scripts')
<script>
document.getElementById('lowOnly')?.addEventListener('click', e => {
    const on = e.currentTarget.getAttribute('aria-pressed') !== 'true';
    e.currentTarget.setAttribute('aria-pressed', String(on));
    document.querySelectorAll('#productsTable tbody tr[data-low]').forEach(row => { row.hidden = on && row.dataset.low !== '1'; });
});
</script>
@endpush
