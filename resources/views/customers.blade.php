@extends('layouts.app')

@section('title', __('Customers'))
@section('page-title', __('Customers'))
@section('page-subtitle', __($customers->total() === 1 ? '1 customer' : ':count customers', ['count' => fmt_number($customers->total())]) . ($search ? ' · ' . __('matching ":search"', ['search' => $search]) : ''))

@php $isManager = in_array(session('staff.role'), ['manager', 'admin'], true); @endphp

@section('page-actions')
    <button type="button" class="btn btn-primary" data-form-dialog="customerDialog" data-title="{{ __('Add customer') }}" data-submit="{{ __('Save customer') }}"
            data-action="{{ route('customers.store') }}" data-method="POST"><i class="fas fa-user-plus" aria-hidden="true"></i> {{ __('Add customer') }}</button>
@endsection

@section('content')
<form class="toolbar" method="GET" action="{{ route('customers.index') }}" role="search">
    <div class="search">
        <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
        <input class="input" type="search" name="search" value="{{ $search }}" placeholder="{{ __('Name, phone or address') }}" aria-label="{{ __('Search customers') }}" autocomplete="off" id="customerSearch">
    </div>
    @if($search)<a href="{{ route('customers.index') }}" class="btn btn-ghost">{{ __('Clear') }}</a>@endif
</form>

<div class="card card-flush" id="listCard" data-refresh>
    @if($customers->isEmpty())
        <div class="empty">
            @if($search)
                <span class="empty-icon" aria-hidden="true"><i class="fas fa-user-slash"></i></span>
                <p class="empty-title">No customers match "{{ $search }}"</p>
                <a href="{{ route('customers.index') }}" class="btn btn-secondary">{{ __('Clear search') }}</a>
            @else
                <x-mascot mood="curious" class="empty-art mascot-ink" />
                <p class="empty-title">{{ __('No customers yet') }}</p>
                <p>{{ __('Add them here, or right from the point of sale.') }}</p>
            @endif
        </div>
    @else
    <div class="table-wrap">
        <table class="table table-stack">
            <thead>
                <tr>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Phone') }}</th>
                    <th class="num">{{ __('Orders') }}</th>
                    <th>{{ __('Last order') }}</th>
                    <th class="num">{{ __('Balance due') }}</th>
                    <th class="actions"><span class="sr-only">{{ __('Actions') }}</span></th>
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
                    <td data-label="{{ __('Phone') }}">
                        @if($customer->contact)<a href="tel:{{ $customer->contact }}" class="nowrap">{{ $customer->contact }}</a>@else<span class="muted">—</span>@endif
                    </td>
                    <td data-label="{{ __('Orders') }}" class="num">
                        @if($customer->orders_count)
                            <a href="{{ route('orders.index', ['search' => $customer->contact ?: $customer->name]) }}">{{ $customer->orders_count }}</a>
                        @else
                            <span class="muted">0</span>
                        @endif
                    </td>
                    <td data-label="{{ __('Last order') }}">{{ $customer->last_order_date ? fmt_date($customer->last_order_date) : '—' }}</td>
                    <td data-label="{{ __('Balance due') }}" class="num">
                        @if($customer->balance_due > 0)
                            <span class="badge badge-unpaid money">{{ money($customer->balance_due) }}</span>
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td>
                    <td class="actions">
                        <div class="cell-actions">
                            <a href="{{ route('pos.index', ['customer' => $customer->id]) }}" class="btn btn-sm btn-soft"><i class="fas fa-plus" aria-hidden="true"></i> {{ __('New order') }}</a>
                            <button type="button" class="icon-btn" aria-label="{{ __('Edit :name', ['name' => $customer->name]) }}" title="{{ __('Edit') }}"
                                    data-form-dialog="customerDialog" data-title="{{ __('Edit customer') }}" data-submit="{{ __('Save changes') }}"
                                    data-action="{{ route('customers.update', $customer) }}" data-method="PUT"
                                    data-values="{{ json_encode($customer->only('name', 'contact', 'address')) }}"><i class="fas fa-pen" aria-hidden="true"></i></button>
                            @if($isManager)
                            <form method="POST" action="{{ route('customers.destroy', $customer) }}" data-ajax data-confirm="{{ __(':name will be removed. Their past orders stay, marked as a walk-in customer.', ['name' => $customer->name]) }}" data-confirm-title="{{ __('Delete this customer?') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icon-btn danger" aria-label="{{ __('Delete :name', ['name' => $customer->name]) }}" title="{{ __('Delete') }}"><i class="fas fa-trash" aria-hidden="true"></i></button>
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

<x-form-dialog id="customerDialog" :title="__('Add customer')" :action="route('customers.store')" :submit="__('Save customer')">
    <x-field name="name" :label="__('Name')" required maxlength="255" autocomplete="off" />
    <x-field name="contact" :label="__('Phone')" type="tel" inputmode="numeric" pattern="[0-9]{10,15}" maxlength="15" placeholder="09123456789" :help="__('Optional. 10 to 15 digits, no spaces.')" />
    <x-field name="address" :label="__('Address')" type="textarea" rows="3" maxlength="500" />
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
