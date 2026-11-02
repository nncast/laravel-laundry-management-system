@extends('layouts.app')

@section('title', 'Categories')
@section('page-title', 'Categories')
@section('page-subtitle', 'Groups for your inventory products')

@section('page-actions')
    <button type="button" class="btn btn-primary" data-form-dialog="categoryDialog" data-title="Add category" data-submit="Save category"
            data-action="{{ route('categories.store') }}" data-method="POST"><i class="fas fa-plus" aria-hidden="true"></i> Add category</button>
@endsection

@section('content')
<div class="card card-flush" id="listCard" data-refresh>
    @if($categories->isEmpty())
        <div class="empty">
            <span class="empty-icon" aria-hidden="true"><i class="fas fa-folder-tree"></i></span>
            <p class="empty-title">No categories yet</p>
            <p>For example Detergents, Softeners or Packaging.</p>
        </div>
    @else
    <div class="table-wrap">
        <table class="table table-stack">
            <thead>
                <tr><th>Category</th><th class="num">Products</th><th>Status</th><th class="actions"><span class="sr-only">Actions</span></th></tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                <tr>
                    <td class="cell-primary"><span class="cell-title">{{ $category->name }}</span></td>
                    <td data-label="Products" class="num">{{ $category->products_count }}</td>
                    <td data-label="Status"><span class="badge {{ $category->status ? 'badge-active' : 'badge-inactive' }}">{{ $category->status ? 'Active' : 'Inactive' }}</span></td>
                    <td class="actions">
                        <div class="cell-actions">
                            <button type="button" class="icon-btn" aria-label="Edit {{ $category->name }}" title="Edit"
                                    data-form-dialog="categoryDialog" data-title="Edit category" data-submit="Save changes"
                                    data-action="{{ route('categories.update') }}" data-method="PUT"
                                    data-values="{{ json_encode(['category_id' => $category->id, 'name' => $category->name, 'status' => $category->status ? 1 : 0]) }}"><i class="fas fa-pen" aria-hidden="true"></i></button>
                            <form method="POST" action="{{ route('categories.destroy') }}" data-ajax data-success="Category deleted."
                                  data-confirm="{{ $category->name }} will be removed." data-confirm-title="Delete this category?">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="category_id" value="{{ $category->id }}">
                                <button type="submit" class="icon-btn danger" aria-label="Delete {{ $category->name }}" title="Delete"><i class="fas fa-trash" aria-hidden="true"></i></button>
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

<x-form-dialog id="categoryDialog" title="Add category" :action="route('categories.store')" submit="Save category" success="Category saved.">
    <input type="hidden" name="category_id" value="{{ old('category_id') }}">
    <x-field name="name" label="Name" required maxlength="255" placeholder="e.g. Detergents" />
    <x-field name="status" label="Status" type="segmented" :options="['1' => 'Active', '0' => 'Inactive']" data-default="1" />
</x-form-dialog>
@endsection
