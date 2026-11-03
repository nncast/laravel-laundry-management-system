@extends('layouts.app')

@section('title', __('Units'))
@section('page-title', __('Units'))
@section('page-subtitle', __('How products are counted: pack, bottle, kilo…'))

@section('page-actions')
    <button type="button" class="btn btn-primary" data-form-dialog="unitDialog" data-title="{{ __('Add unit') }}" data-submit="{{ __('Save unit') }}"
            data-action="{{ route('units.store') }}" data-method="POST"><i class="fas fa-plus" aria-hidden="true"></i> {{ __('Add unit') }}</button>
@endsection

@section('content')
<div class="card card-flush" id="listCard" data-refresh>
    @if($units->isEmpty())
        <div class="empty">
            <span class="empty-icon" aria-hidden="true"><i class="fas fa-ruler-combined"></i></span>
            <p class="empty-title">{{ __('No units yet') }}</p>
            <p>{{ __('For example Pack (pk), Bottle (btl) or Kilogram (kg).') }}</p>
        </div>
    @else
    <div class="table-wrap">
        <table class="table table-stack">
            <thead>
                <tr><th>{{ __('Unit') }}</th><th>{{ __('Short form') }}</th><th>{{ __('Description') }}</th><th>{{ __('Status') }}</th><th class="actions"><span class="sr-only">{{ __('Actions') }}</span></th></tr>
            </thead>
            <tbody>
                @foreach($units as $unit)
                <tr>
                    <td class="cell-primary"><span class="cell-title">{{ $unit->name }}</span></td>
                    <td data-label="{{ __('Short form') }}">{{ $unit->short_form ?: '—' }}</td>
                    <td data-label="{{ __('Description') }}" class="cell-sub">{{ $unit->description ?: '—' }}</td>
                    <td data-label="{{ __('Status') }}"><span class="badge {{ $unit->status === 'active' ? 'badge-active' : 'badge-inactive' }}">{{ ucfirst($unit->status) }}</span></td>
                    <td class="actions">
                        <div class="cell-actions">
                            <button type="button" class="icon-btn" aria-label="{{ __('Edit :name', ['name' => $unit->name]) }}" title="{{ __('Edit') }}"
                                    data-form-dialog="unitDialog" data-title="{{ __('Edit unit') }}" data-submit="{{ __('Save changes') }}"
                                    data-action="{{ route('units.update') }}" data-method="PUT"
                                    data-values="{{ json_encode(['id' => $unit->id] + $unit->only('name', 'short_form', 'description', 'status')) }}"><i class="fas fa-pen" aria-hidden="true"></i></button>
                            <form method="POST" action="{{ route('units.destroy') }}" data-ajax data-success="{{ __('Unit deleted.') }}"
                                  data-confirm="{{ __(':name will be removed.', ['name' => $unit->name]) }}" data-confirm-title="{{ __('Delete this unit?') }}">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="id" value="{{ $unit->id }}">
                                <button type="submit" class="icon-btn danger" aria-label="{{ __('Delete :name', ['name' => $unit->name]) }}" title="{{ __('Delete') }}"><i class="fas fa-trash" aria-hidden="true"></i></button>
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

<x-form-dialog id="unitDialog" :title="__('Add unit')" :action="route('units.store')" :submit="__('Save unit')" :success="__('Unit saved.')">
    <input type="hidden" name="id" value="{{ old('id') }}">
    <div class="form-row">
        <x-field name="name" :label="__('Name')" required maxlength="255" :placeholder="__('e.g. Pack')" />
        <x-field name="short_form" :label="__('Short form')" maxlength="10" :placeholder="__('e.g. pk')" />
    </div>
    <x-field name="description" :label="__('Description')" type="textarea" rows="2" maxlength="1000" />
    <x-field name="status" :label="__('Status')" type="segmented" :options="['active' => __('Active'), 'inactive' => __('Inactive')]" data-default="active" />
</x-form-dialog>
@endsection
