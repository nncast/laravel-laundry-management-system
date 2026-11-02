@extends('layouts.app')

@section('title', 'Service types')
@section('page-title', 'Service types')
@section('page-subtitle', 'Groups for your services; each one is a tab at the point of sale')

@section('page-actions')
    <button type="button" class="btn btn-primary" data-form-dialog="typeDialog" data-title="Add service type" data-submit="Save type"
            data-action="{{ route('services.type.store') }}" data-method="POST"><i class="fas fa-plus" aria-hidden="true"></i> Add type</button>
@endsection

@section('content')
<div class="card card-flush">
    @if($serviceTypes->isEmpty())
        <div class="empty">
            <span class="empty-icon" aria-hidden="true"><i class="fas fa-layer-group"></i></span>
            <p class="empty-title">No service types yet</p>
            <p>Start with groups like Wash &amp; Fold, Dry Clean or Ironing.</p>
        </div>
    @else
    <div class="table-wrap">
        <table class="table table-stack">
            <thead>
                <tr><th>Type</th><th class="num">Services</th><th>Status</th><th class="actions"><span class="sr-only">Actions</span></th></tr>
            </thead>
            <tbody>
                @foreach($serviceTypes as $type)
                <tr>
                    <td class="cell-primary"><span class="cell-title">{{ $type->name }}</span></td>
                    <td data-label="Services" class="num">{{ $type->services_count }}</td>
                    <td data-label="Status"><span class="badge {{ $type->is_active ? 'badge-active' : 'badge-inactive' }}">{{ $type->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td class="actions">
                        <div class="cell-actions">
                            <button type="button" class="icon-btn" aria-label="Edit {{ $type->name }}" title="Edit"
                                    data-form-dialog="typeDialog" data-title="Edit service type" data-submit="Save changes"
                                    data-action="{{ route('services.type.update') }}" data-method="PUT"
                                    data-values="{{ json_encode(['id' => $type->id, 'name' => $type->name, 'is_active' => $type->is_active ? 1 : 0]) }}"><i class="fas fa-pen" aria-hidden="true"></i></button>
                            <form method="POST" action="{{ route('services.type.destroy') }}" data-ajax
                                  data-confirm="{{ $type->name }} will be removed." data-confirm-title="Delete this service type?">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="id" value="{{ $type->id }}">
                                <button type="submit" class="icon-btn danger" aria-label="Delete {{ $type->name }}" title="Delete"><i class="fas fa-trash" aria-hidden="true"></i></button>
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

<x-form-dialog id="typeDialog" title="Add service type" :action="route('services.type.store')" submit="Save type" success="Service type saved.">
    <input type="hidden" name="id" value="{{ old('id') }}">
    <x-field name="name" label="Name" required maxlength="255" placeholder="e.g. Wash & Fold" />
    <x-field name="is_active" label="Status" type="segmented" :options="['1' => 'Active', '0' => 'Inactive']" data-default="1" />
</x-form-dialog>
@endsection
