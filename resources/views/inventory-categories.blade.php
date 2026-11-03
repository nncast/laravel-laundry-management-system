@extends('layouts.app')

@section('title', __('Categories'))
@section('page-title', __('Categories'))
@section('page-subtitle', __('Groups for your inventory products'))

@section('page-actions')
    <button type="button" class="btn btn-primary" data-form-dialog="categoryDialog" data-title="{{ __('Add category') }}" data-submit="{{ __('Save category') }}"
            data-action="{{ route('categories.store') }}" data-method="POST"><i class="fas fa-plus" aria-hidden="true"></i> {{ __('Add category') }}</button>
@endsection

@section('content')
<div class="card card-flush" id="listCard" data-refresh>
    @if($categories->isEmpty())
        <div class="empty">
            <span class="empty-icon" aria-hidden="true"><i class="fas fa-folder-tree"></i></span>
            <p class="empty-title">{{ __('No categories yet') }}</p>
            <p>{{ __('For example Detergents, Softeners or Packaging.') }}</p>
        </div>
    @else
    <div class="table-wrap">
        <table class="table table-stack">
            <thead>
                <tr><th>{{ __('Category') }}</th><th class="num">{{ __('Products') }}</th><th>{{ __('Status') }}</th><th class="actions"><span class="sr-only">{{ __('Actions') }}</span></th></tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                <tr>
                    <td class="cell-primary"><span class="cell-title">{{ $category->name }}</span></td>
                    <td data-label="{{ __('Products') }}" class="num">{{ $category->products_count }}</td>
                    <td data-label="{{ __('Status') }}"><span class="badge {{ $category->status ? 'badge-active' : 'badge-inactive' }}">{{ $category->status ? __('Active') : __('Inactive') }}</span></td>
                    <td class="actions">
                        <div class="cell-actions">
                            <button type="button" class="icon-btn" aria-label="{{ __('Edit :name', ['name' => $category->name]) }}" title="{{ __('Edit') }}"
                                    data-form-dialog="categoryDialog" data-title="{{ __('Edit category') }}" data-submit="{{ __('Save changes') }}"
                                    data-action="{{ route('categories.update') }}" data-method="PUT"
                                    data-values="{{ json_encode(['category_id' => $category->id, 'name' => $category->name, 'status' => $category->status ? 1 : 0]) }}"><i class="fas fa-pen" aria-hidden="true"></i></button>
                            <form method="POST" action="{{ route('categories.destroy') }}" data-ajax data-success="{{ __('Category deleted.') }}"
                                  data-confirm="{{ __(':name will be removed.', ['name' => $category->name]) }}" data-confirm-title="{{ __('Delete this category?') }}">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="category_id" value="{{ $category->id }}">
                                <button type="submit" class="icon-btn danger" aria-label="{{ __('Delete :name', ['name' => $category->name]) }}" title="{{ __('Delete') }}"><i class="fas fa-trash" aria-hidden="true"></i></button>
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

<x-form-dialog id="categoryDialog" :title="__('Add category')" :action="route('categories.store')" :submit="__('Save category')" :success="__('Category saved.')">
    <input type="hidden" name="category_id" value="{{ old('category_id') }}">
    <x-field name="name" :label="__('Name')" required maxlength="255" :placeholder="__('e.g. Detergents')" />
    <x-field name="status" :label="__('Status')" type="segmented" :options="['1' => __('Active'), '0' => __('Inactive')]" data-default="1" />
</x-form-dialog>
@endsection
