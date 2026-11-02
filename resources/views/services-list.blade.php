@extends('layouts.app')

@section('title', 'Services')
@section('page-title', 'Services')
@section('page-subtitle', 'What customers can order at the point of sale')

@section('page-actions')
    <button type="button" class="btn btn-primary" data-form-dialog="serviceDialog" data-title="Add service" data-submit="Save service"
            data-action="{{ route('services.store') }}" data-method="POST" @disabled($serviceTypes->isEmpty())><i class="fas fa-plus" aria-hidden="true"></i> Add service</button>
@endsection

@section('content')
@if($serviceTypes->isEmpty())
    <div class="alert alert-info" style="margin-bottom: var(--s-4)"><i class="fas fa-circle-info" aria-hidden="true"></i><span>Add a <a href="{{ route('services.type') }}">service type</a> first (e.g. Wash &amp; Fold); services are grouped by type at the point of sale.</span></div>
@endif

<div class="toolbar">
    <div class="search">
        <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
        <input class="input" type="search" placeholder="Search services" aria-label="Search services" data-table-filter="servicesTable" autocomplete="off">
    </div>
</div>

<div class="card card-flush">
    @if($services->isEmpty())
        <div class="empty">
            <span class="empty-icon" aria-hidden="true"><i class="fas fa-tags"></i></span>
            <p class="empty-title">No services yet</p>
            <p>Add the services you offer, like Wash &amp; Dry or Press, with their prices.</p>
        </div>
    @else
    <div class="table-wrap">
        <table class="table table-stack" id="servicesTable">
            <thead>
                <tr><th>Service</th><th>Type</th><th class="num">Price</th><th>Status</th><th class="actions"><span class="sr-only">Actions</span></th></tr>
            </thead>
            <tbody>
                @foreach($services as $service)
                <tr>
                    <td class="cell-primary">
                        <div class="cluster" style="flex-wrap:nowrap">
                            @if($service->icon && !str_contains($service->icon, 'placeholder'))
                                <img src="{{ $service->icon_url }}" alt="" width="40" height="40" loading="lazy" style="border-radius:10px; object-fit:cover; flex-shrink:0">
                            @else
                                <span class="bubble-tile tone-blue" style="width:40px;height:40px" aria-hidden="true"><i class="fas fa-shirt"></i></span>
                            @endif
                            <span class="cell-title">{{ $service->name }}</span>
                        </div>
                    </td>
                    <td data-label="Type"><span class="badge badge-info">{{ $service->serviceType->name ?? 'No type' }}</span></td>
                    <td data-label="Price" class="num"><strong>{{ money($service->price) }}</strong></td>
                    <td data-label="Status"><span class="badge {{ $service->is_active ? 'badge-active' : 'badge-inactive' }}">{{ $service->is_active ? 'Active' : 'Hidden from POS' }}</span></td>
                    <td class="actions">
                        <div class="cell-actions">
                            <button type="button" class="icon-btn" aria-label="Edit {{ $service->name }}" title="Edit"
                                    data-form-dialog="serviceDialog" data-title="Edit service" data-submit="Save changes"
                                    data-action="{{ route('services.update', $service->id) }}" data-method="PUT"
                                    data-values="{{ json_encode(['name' => $service->name, 'service_type_id' => $service->service_type_id, 'price' => $service->price, 'is_active' => $service->is_active ? 1 : 0]) }}"><i class="fas fa-pen" aria-hidden="true"></i></button>
                            <form method="POST" action="{{ route('services.destroy', $service->id) }}" data-ajax data-success="Service deleted."
                                  data-confirm="{{ $service->name }} will be removed from the catalog." data-confirm-title="Delete this service?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icon-btn danger" aria-label="Delete {{ $service->name }}" title="Delete"><i class="fas fa-trash" aria-hidden="true"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
                <tr class="no-match" hidden><td colspan="5" class="muted">No services match your search.</td></tr>
            </tbody>
        </table>
    </div>
    @endif
</div>

<x-form-dialog id="serviceDialog" title="Add service" :action="route('services.store')" submit="Save service" success="Service saved." files>
    <x-field name="name" label="Name" required maxlength="255" placeholder="e.g. Wash & Dry (8 kg)" />
    <div class="form-row">
        <x-field name="service_type_id" label="Type" required :options="['' => 'Choose a type'] + $serviceTypes->pluck('name', 'id')->all()" />
        <x-field name="price" label="Price" type="number" :prefix="currency_symbol()" required min="0" :step="money_step()" inputmode="decimal" />
    </div>
    <x-field name="is_active" label="Show at the point of sale" type="segmented" :options="['1' => 'Active', '0' => 'Hidden']" data-default="1" />
    <x-field name="icon_file" label="Picture" type="file" accept="image/*" help="Optional. JPG, PNG, GIF or WebP up to 2 MB. Leave empty to keep the current one." />
</x-form-dialog>
@endsection
