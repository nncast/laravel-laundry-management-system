@extends('layouts.app')

@section('title', 'Add-ons')
@section('page-title', 'Add-ons')
@section('page-subtitle', 'Extras added to a whole order, like fabric softener or rush service')

@section('page-actions')
    <button type="button" class="btn btn-primary" data-form-dialog="addonDialog" data-title="Add add-on" data-submit="Save add-on"
            data-action="{{ route('services.addons.store') }}" data-method="POST"><i class="fas fa-plus" aria-hidden="true"></i> Add add-on</button>
@endsection

@section('content')
<div class="card card-flush">
    @if($addons->isEmpty())
        <div class="empty">
            <span class="empty-icon" aria-hidden="true"><i class="fas fa-wand-magic-sparkles"></i></span>
            <p class="empty-title">No add-ons yet</p>
            <p>Add extras such as Fabric softener or Rush service; they show as one-tap chips at the point of sale.</p>
        </div>
    @else
    <div class="table-wrap">
        <table class="table table-stack">
            <thead>
                <tr><th>Add-on</th><th class="num">Price</th><th>Status</th><th class="actions"><span class="sr-only">Actions</span></th></tr>
            </thead>
            <tbody>
                @foreach($addons as $addon)
                <tr>
                    <td class="cell-primary"><span class="cell-title">{{ $addon->name }}</span></td>
                    <td data-label="Price" class="num"><strong>₱{{ number_format($addon->price, 2) }}</strong></td>
                    <td data-label="Status"><span class="badge {{ $addon->is_active ? 'badge-active' : 'badge-inactive' }}">{{ $addon->is_active ? 'Active' : 'Hidden from POS' }}</span></td>
                    <td class="actions">
                        <div class="cell-actions">
                            <button type="button" class="icon-btn" aria-label="Edit {{ $addon->name }}" title="Edit"
                                    data-form-dialog="addonDialog" data-title="Edit add-on" data-submit="Save changes"
                                    data-action="{{ route('services.addons.update', $addon->id) }}" data-method="PUT"
                                    data-values="{{ json_encode(['name' => $addon->name, 'price' => $addon->price, 'is_active' => $addon->is_active ? 1 : 0]) }}"><i class="fas fa-pen" aria-hidden="true"></i></button>
                            <form method="POST" action="{{ route('services.addons.destroy', $addon->id) }}" data-ajax data-success="Add-on deleted."
                                  data-confirm="{{ $addon->name }} will be removed." data-confirm-title="Delete this add-on?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icon-btn danger" aria-label="Delete {{ $addon->name }}" title="Delete"><i class="fas fa-trash" aria-hidden="true"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

<x-form-dialog id="addonDialog" title="Add add-on" :action="route('services.addons.store')" submit="Save add-on" success="Add-on saved.">
    <x-field name="name" label="Name" required maxlength="255" placeholder="e.g. Fabric softener" />
    <x-field name="price" label="Price" type="number" prefix="₱" required min="0" step="0.01" inputmode="decimal" />
    <x-field name="is_active" label="Show at the point of sale" type="segmented" :options="['1' => 'Active', '0' => 'Hidden']" data-default="1" />
</x-form-dialog>
@endsection
