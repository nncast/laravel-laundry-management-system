@extends('layouts.app')

@section('title', 'Customers')
@section('page-title', 'Customers')
@section('page-subtitle', number_format($customers->total()) . ' ' . \Illuminate\Support\Str::plural('customer', $customers->total()) . ($search ? ' matching "' . $search . '"' : ''))

@php $isManager = in_array(session('staff.role'), ['manager', 'admin'], true); @endphp

@section('page-actions')
    <button type="button" class="btn btn-primary" data-form-dialog="customerDialog" data-title="Add customer" data-submit="Save customer"
            data-action="{{ route('customers.store') }}" data-method="POST"><i class="fas fa-user-plus" aria-hidden="true"></i> Add customer</button>
@endsection

@section('content')
<form class="toolbar" method="GET" action="{{ route('customers.index') }}" role="search">
    <div class="search">
        <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
        <input class="input" type="search" name="search" value="{{ $search }}" placeholder="Name, phone or address" aria-label="Search customers" autocomplete="off" id="customerSearch">
    </div>
    @if($search)<a href="{{ route('customers.index') }}" class="btn btn-ghost">Clear</a>@endif
</form>

<div class="card card-flush">
    @if($customers->isEmpty())
        <div class="empty">
            @if($search)
                <span class="empty-icon" aria-hidden="true"><i class="fas fa-user-slash"></i></span>
                <p class="empty-title">No customers match "{{ $search }}"</p>
                <a href="{{ route('customers.index') }}" class="btn btn-secondary">Clear search</a>
            @else
                <x-mascot mood="curious" class="empty-art mascot-ink" />
                <p class="empty-title">No customers yet</p>
                <p>Add them here, or right from the point of sale.</p>
            @endif
        </div>
    @else
    <div class="table-wrap">
        <table class="table table-stack">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th class="num">Orders</th>
                    <th>Last order</th>
                    <th class="num">Balance due</th>
                    <th class="actions"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach($customers as $customer)
                <tr>
                    <td class="cell-primary">
                        <div class="cluster" style="flex-wrap:nowrap">
                            <span class="avatar" aria-hidden="true">{{ strtoupper(mb_substr($customer->name, 0, 1)) }}</span>
                            <div style="min-width:0">
                                <div class="cell-title">{{ $customer->name }}</div>
                                @if($customer->address)<div class="cell-sub truncate">{{ $customer->address }}</div>@endif
                            </div>
                        </div>
                    </td>
                    <td data-label="Phone">
                        @if($customer->contact)<a href="tel:{{ $customer->contact }}" class="nowrap">{{ $customer->contact }}</a>@else<span class="muted">—</span>@endif
                    </td>
                    <td data-label="Orders" class="num">
                        @if($customer->orders_count)
                            <a href="{{ route('orders.index', ['search' => $customer->contact ?: $customer->name]) }}">{{ $customer->orders_count }}</a>
                        @else
                            <span class="muted">0</span>
                        @endif
                    </td>
                    <td data-label="Last order">{{ $customer->last_order_date ? fmt_date($customer->last_order_date) : '—' }}</td>
                    <td data-label="Balance due" class="num">
                        @if($customer->balance_due > 0)
                            <span class="badge badge-unpaid money">{{ money($customer->balance_due) }}</span>
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td>
                    <td class="actions">
                        <div class="cell-actions">
                            <a href="{{ route('pos.index', ['customer' => $customer->id]) }}" class="btn btn-sm btn-soft"><i class="fas fa-plus" aria-hidden="true"></i> New order</a>
                            <button type="button" class="icon-btn" aria-label="Edit {{ $customer->name }}" title="Edit"
                                    data-form-dialog="customerDialog" data-title="Edit customer" data-submit="Save changes"
                                    data-action="{{ route('customers.update', $customer) }}" data-method="PUT"
                                    data-values="{{ json_encode($customer->only('name', 'contact', 'address')) }}"><i class="fas fa-pen" aria-hidden="true"></i></button>
                            @if($isManager)
                            <form method="POST" action="{{ route('customers.destroy', $customer) }}" data-confirm="{{ $customer->name }} will be removed. Their past orders stay, marked as a walk-in customer." data-confirm-title="Delete this customer?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icon-btn danger" aria-label="Delete {{ $customer->name }}" title="Delete"><i class="fas fa-trash" aria-hidden="true"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $customers->links() }}
    @endif
</div>

<x-form-dialog id="customerDialog" title="Add customer" :action="route('customers.store')" submit="Save customer">
    <x-field name="name" label="Name" required maxlength="255" autocomplete="off" />
    <x-field name="contact" label="Phone" type="tel" inputmode="numeric" pattern="[0-9]{10,15}" maxlength="15" placeholder="09123456789" help="Optional. 10 to 15 digits, no spaces." />
    <x-field name="address" label="Address" type="textarea" rows="3" maxlength="500" />
</x-form-dialog>
@endsection

@push('scripts')
<script>
(function () {
    // Search as you type, after a short pause
    const search = document.getElementById('customerSearch');
    let timer;
    search.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(() => search.form.submit(), 500); });

    // Dashboard's "Add customer" lands here with ?new=1
    @if(request()->boolean('new') && !$errors->any())
    document.querySelector('[data-form-dialog="customerDialog"]').click();
    @endif
})();
</script>
@endpush
