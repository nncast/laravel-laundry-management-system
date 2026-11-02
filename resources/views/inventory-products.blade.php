@extends('layouts.app')

@section('title', 'Products')
@section('page-title', 'Products')
@php $lowCount = $products->filter(fn ($p) => $p->status === 'active' && $p->available_stock <= $p->minimum_stock_level)->count(); @endphp
@section('page-subtitle', 'Supplies you use and restock' . ($lowCount ? " · {$lowCount} running low" : ''))

@section('page-actions')
    <button type="button" class="btn btn-primary" data-form-dialog="productDialog" data-title="Add product" data-submit="Save product"
            data-action="{{ route('products.store') }}" data-method="POST" @disabled($categories->isEmpty() || $units->isEmpty())><i class="fas fa-plus" aria-hidden="true"></i> Add product</button>
@endsection

@section('content')
@if($categories->isEmpty() || $units->isEmpty())
    <div class="alert alert-info" style="margin-bottom: var(--s-4)"><i class="fas fa-circle-info" aria-hidden="true"></i><span>Products need an active <a href="{{ route('categories.index') }}">category</a> and <a href="{{ route('units.index') }}">unit</a>. Add those first.</span></div>
@endif

<div class="toolbar">
    <div class="search">
        <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
        <input class="input" type="search" placeholder="Search products" aria-label="Search products" data-table-filter="productsTable" autocomplete="off">
    </div>
    @if($lowCount)
        <button type="button" class="chip" aria-pressed="false" id="lowOnly"><i class="fas fa-triangle-exclamation" aria-hidden="true"></i> Low stock only <span class="chip-count">{{ $lowCount }}</span></button>
    @endif
</div>

<div class="card card-flush">
    @if($products->isEmpty())
        <div class="empty">
            <span class="empty-icon" aria-hidden="true"><i class="fas fa-boxes-stacked"></i></span>
            <p class="empty-title">No products yet</p>
            <p>Track detergents, softeners and other supplies; the dashboard warns you when one runs low.</p>
        </div>
    @else
    <div class="table-wrap">
        <table class="table table-stack" id="productsTable">
            <thead>
                <tr><th>Product</th><th>Unit</th><th class="num">Purchase price</th><th>In stock</th><th>Status</th><th class="actions"><span class="sr-only">Actions</span></th></tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                @php $low = $product->status === 'active' && $product->available_stock <= $product->minimum_stock_level; @endphp
                <tr data-low="{{ $low ? 1 : 0 }}">
                    <td class="cell-primary">
                        <div class="cell-title">{{ $product->name }}</div>
                        <div class="cell-sub">{{ $product->category->name ?? 'No category' }}</div>
                    </td>
                    <td data-label="Unit">{{ $product->unit->name ?? '—' }}</td>
                    <td data-label="Purchase price" class="num">₱{{ number_format($product->purchase_price, 2) }}</td>
                    <td data-label="In stock">
                        <div>
                            <strong class="{{ $low ? 'text-danger' : '' }}">{{ $product->available_stock }}</strong>
                            <span class="cell-sub">· min {{ $product->minimum_stock_level }}</span>
                            @if($low)<span class="badge badge-warning">Low</span>@endif
                        </div>
                    </td>
                    <td data-label="Status"><span class="badge {{ $product->status === 'active' ? 'badge-active' : 'badge-inactive' }}">{{ ucfirst($product->status) }}</span></td>
                    <td class="actions">
                        <div class="cell-actions">
                            <button type="button" class="icon-btn" aria-label="Edit {{ $product->name }}" title="Edit"
                                    data-form-dialog="productDialog" data-title="Edit product" data-submit="Save changes"
                                    data-action="{{ route('products.update') }}" data-method="PUT"
                                    data-values="{{ json_encode(['product_id' => $product->id] + $product->only('name', 'category_id', 'unit_id', 'purchase_price', 'available_stock', 'minimum_stock_level', 'status')) }}"><i class="fas fa-pen" aria-hidden="true"></i></button>
                            <form method="POST" action="{{ route('products.destroy') }}" data-ajax data-success="Product deleted."
                                  data-confirm="{{ $product->name }} will be removed." data-confirm-title="Delete this product?">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <button type="submit" class="icon-btn danger" aria-label="Delete {{ $product->name }}" title="Delete"><i class="fas fa-trash" aria-hidden="true"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
                <tr class="no-match" hidden><td colspan="6" class="muted">No products match your search.</td></tr>
            </tbody>
        </table>
    </div>
    @endif
</div>

<x-form-dialog id="productDialog" title="Add product" :action="route('products.store')" submit="Save product" success="Product saved.">
    <input type="hidden" name="product_id" value="{{ old('product_id') }}">
    <x-field name="name" label="Name" required maxlength="255" placeholder="e.g. Powder detergent 1 kg" />
    <div class="form-row">
        <x-field name="category_id" label="Category" required :options="['' => 'Choose'] + $categories->pluck('name', 'id')->all()" />
        <x-field name="unit_id" label="Unit" required :options="['' => 'Choose'] + $units->pluck('name', 'id')->all()" />
    </div>
    <x-field name="purchase_price" label="Purchase price" type="number" prefix="₱" required min="0" step="0.01" inputmode="decimal" />
    <div class="form-row">
        <x-field name="available_stock" label="In stock" type="number" required min="0" step="1" inputmode="numeric" />
        <x-field name="minimum_stock_level" label="Low-stock level" type="number" required min="0" step="1" inputmode="numeric" help="Warn when stock is at or below this" />
    </div>
    <x-field name="status" label="Status" type="segmented" :options="['active' => 'Active', 'inactive' => 'Inactive']" data-default="active" />
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
