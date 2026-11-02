@extends('layouts.app')

@section('title', 'Units')
@section('page-title', 'Units')
@section('page-subtitle', 'How products are counted: pack, bottle, kilo…')

@section('page-actions')
    <button type="button" class="btn btn-primary" data-form-dialog="unitDialog" data-title="Add unit" data-submit="Save unit"
            data-action="{{ route('units.store') }}" data-method="POST"><i class="fas fa-plus" aria-hidden="true"></i> Add unit</button>
@endsection

@section('content')
<div class="card card-flush">
    @if($units->isEmpty())
        <div class="empty">
            <span class="empty-icon" aria-hidden="true"><i class="fas fa-ruler-combined"></i></span>
            <p class="empty-title">No units yet</p>
            <p>For example Pack (pk), Bottle (btl) or Kilogram (kg).</p>
        </div>
    @else
    <div class="table-wrap">
        <table class="table table-stack">
            <thead>
                <tr><th>Unit</th><th>Short form</th><th>Description</th><th>Status</th><th class="actions"><span class="sr-only">Actions</span></th></tr>
            </thead>
            <tbody>
                @foreach($units as $unit)
                <tr>
                    <td class="cell-primary"><span class="cell-title">{{ $unit->name }}</span></td>
                    <td data-label="Short form">{{ $unit->short_form ?: '—' }}</td>
                    <td data-label="Description" class="cell-sub">{{ $unit->description ?: '—' }}</td>
                    <td data-label="Status"><span class="badge {{ $unit->status === 'active' ? 'badge-active' : 'badge-inactive' }}">{{ ucfirst($unit->status) }}</span></td>
                    <td class="actions">
                        <div class="cell-actions">
                            <button type="button" class="icon-btn" aria-label="Edit {{ $unit->name }}" title="Edit"
                                    data-form-dialog="unitDialog" data-title="Edit unit" data-submit="Save changes"
                                    data-action="{{ route('units.update') }}" data-method="PUT"
                                    data-values="{{ json_encode(['id' => $unit->id] + $unit->only('name', 'short_form', 'description', 'status')) }}"><i class="fas fa-pen" aria-hidden="true"></i></button>
                            <form method="POST" action="{{ route('units.destroy') }}" data-ajax data-success="Unit deleted."
                                  data-confirm="{{ $unit->name }} will be removed." data-confirm-title="Delete this unit?">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="id" value="{{ $unit->id }}">
                                <button type="submit" class="icon-btn danger" aria-label="Delete {{ $unit->name }}" title="Delete"><i class="fas fa-trash" aria-hidden="true"></i></button>
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

<x-form-dialog id="unitDialog" title="Add unit" :action="route('units.store')" submit="Save unit" success="Unit saved.">
    <input type="hidden" name="id" value="{{ old('id') }}">
    <div class="form-row">
        <x-field name="name" label="Name" required maxlength="255" placeholder="e.g. Pack" />
        <x-field name="short_form" label="Short form" maxlength="10" placeholder="e.g. pk" />
    </div>
    <x-field name="description" label="Description" type="textarea" rows="2" maxlength="1000" />
    <x-field name="status" label="Status" type="segmented" :options="['active' => 'Active', 'inactive' => 'Inactive']" data-default="active" />
</x-form-dialog>
@endsection
