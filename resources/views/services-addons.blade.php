@extends('layouts.app')

@section('title', __('Add-ons'))
@section('page-title', __('Add-ons'))
@section('page-subtitle', __('Extras added to a whole order, like fabric softener or rush service'))

@section('page-actions')
    <button type="button" class="btn btn-primary" data-form-dialog="addonDialog" data-title="{{ __('Add add-on') }}" data-submit="{{ __('Save add-on') }}"
            data-action="{{ route('services.addons.store') }}" data-method="POST"><i class="fas fa-plus" aria-hidden="true"></i> {{ __('Add add-on') }}</button>
@endsection

@section('content')
<div class="card card-flush" id="listCard" data-refresh>
    @if($addons->isEmpty())
        <div class="empty">
            <span class="empty-icon" aria-hidden="true"><i class="fas fa-wand-magic-sparkles"></i></span>
            <p class="empty-title">{{ __('No add-ons yet') }}</p>
            <p>{{ __('Add extras such as Fabric softener or Rush service; they show as one-tap chips at the point of sale.') }}</p>
        </div>
    @else
    <div class="table-wrap">
        <table class="table table-stack">
            <thead>
                <tr><th>{{ __('Add-on') }}</th><th class="num">{{ __('Price') }}</th><th>{{ __('Status') }}</th><th class="actions"><span class="sr-only">{{ __('Actions') }}</span></th></tr>
            </thead>
            <tbody>
                @foreach($addons as $addon)
                <tr>
                    <td class="cell-primary"><span class="cell-title">{{ $addon->name }}</span></td>
                    <td data-label="{{ __('Price') }}" class="num"><strong>{{ money($addon->price) }}</strong></td>
                    <td data-label="{{ __('Status') }}"><span class="badge {{ $addon->is_active ? 'badge-active' : 'badge-inactive' }}">{{ $addon->is_active ? __('Active') : __('Hidden from POS') }}</span></td>
                    <td class="actions">
                        <div class="cell-actions">
                            <button type="button" class="icon-btn" aria-label="{{ __('Edit :name', ['name' => $addon->name]) }}" title="{{ __('Edit') }}"
                                    data-form-dialog="addonDialog" data-title="{{ __('Edit add-on') }}" data-submit="{{ __('Save changes') }}"
                                    data-action="{{ route('services.addons.update', $addon->id) }}" data-method="PUT"
                                    data-values="{{ json_encode(['name' => $addon->name, 'price' => $addon->price, 'is_active' => $addon->is_active ? 1 : 0]) }}"><i class="fas fa-pen" aria-hidden="true"></i></button>
                            <form method="POST" action="{{ route('services.addons.destroy', $addon->id) }}" data-ajax data-success="{{ __('Add-on deleted.') }}"
                                  data-confirm="{{ __(':name will be removed.', ['name' => $addon->name]) }}" data-confirm-title="{{ __('Delete this add-on?') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icon-btn danger" aria-label="{{ __('Delete :name', ['name' => $addon->name]) }}" title="{{ __('Delete') }}"><i class="fas fa-trash" aria-hidden="true"></i></button>
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

<x-form-dialog id="addonDialog" :title="__('Add add-on')" :action="route('services.addons.store')" :submit="__('Save add-on')" :success="__('Add-on saved.')">
    <x-field name="name" :label="__('Name')" required maxlength="255" :placeholder="__('e.g. Fabric softener')" />
    <x-field name="price" :label="__('Price')" type="number" :prefix="currency_symbol()" required min="0" :step="money_step()" inputmode="decimal" />
    <x-field name="is_active" :label="__('Show at the point of sale')" type="segmented" :options="['1' => __('Active'), '0' => __('Hidden')]" data-default="1" />
</x-form-dialog>
@endsection
