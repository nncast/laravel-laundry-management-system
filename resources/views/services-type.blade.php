@extends('layouts.app')

@section('title', __('Service types'))
@section('page-title', __('Service types'))
@section('page-subtitle', __('Groups for your services; each one is a tab at the point of sale'))

@section('page-actions')
    <button type="button" class="btn btn-primary" data-form-dialog="typeDialog" data-title="{{ __('Add service type') }}" data-submit="{{ __('Save type') }}"
            data-action="{{ route('services.type.store') }}" data-method="POST"><i class="fas fa-plus" aria-hidden="true"></i> {{ __('Add type') }}</button>
@endsection

@section('content')
<div class="card card-flush" id="listCard" data-refresh>
    @if($serviceTypes->isEmpty())
        <div class="empty">
            <span class="empty-icon" aria-hidden="true"><i class="fas fa-layer-group"></i></span>
            <p class="empty-title">{{ __('No service types yet') }}</p>
            <p>{{ __('Start with groups like Wash & Fold, Dry Clean or Ironing.') }}</p>
        </div>
    @else
    <div class="table-wrap">
        <table class="table table-stack">
            <thead>
                <tr><th>{{ __('Type') }}</th><th class="num">{{ __('Services') }}</th><th>{{ __('Status') }}</th><th class="actions"><span class="sr-only">{{ __('Actions') }}</span></th></tr>
            </thead>
            <tbody>
                @foreach($serviceTypes as $type)
                <tr>
                    <td class="cell-primary"><span class="cell-title">{{ $type->name }}</span></td>
                    <td data-label="{{ __('Services') }}" class="num">{{ $type->services_count }}</td>
                    <td data-label="{{ __('Status') }}"><span class="badge {{ $type->is_active ? 'badge-active' : 'badge-inactive' }}">{{ $type->is_active ? __('Active') : __('Inactive') }}</span></td>
                    <td class="actions">
                        <div class="cell-actions">
                            <button type="button" class="icon-btn" aria-label="{{ __('Edit :name', ['name' => $type->name]) }}" title="{{ __('Edit') }}"
                                    data-form-dialog="typeDialog" data-title="{{ __('Edit service type') }}" data-submit="{{ __('Save changes') }}"
                                    data-action="{{ route('services.type.update') }}" data-method="PUT"
                                    data-values="{{ json_encode(['id' => $type->id, 'name' => $type->name, 'is_active' => $type->is_active ? 1 : 0]) }}"><i class="fas fa-pen" aria-hidden="true"></i></button>
                            <form method="POST" action="{{ route('services.type.destroy') }}" data-ajax
                                  data-confirm="{{ __(':name will be removed.', ['name' => $type->name]) }}" data-confirm-title="{{ __('Delete this service type?') }}">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="id" value="{{ $type->id }}">
                                <button type="submit" class="icon-btn danger" aria-label="{{ __('Delete :name', ['name' => $type->name]) }}" title="{{ __('Delete') }}"><i class="fas fa-trash" aria-hidden="true"></i></button>
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

<x-form-dialog id="typeDialog" :title="__('Add service type')" :action="route('services.type.store')" :submit="__('Save type')" :success="__('Service type saved.')">
    <input type="hidden" name="id" value="{{ old('id') }}">
    <x-field name="name" :label="__('Name')" required maxlength="255" :placeholder="__('e.g. Wash & Fold')" />
    <x-field name="is_active" :label="__('Status')" type="segmented" :options="['1' => __('Active'), '0' => __('Inactive')]" data-default="1" />
</x-form-dialog>
@endsection
