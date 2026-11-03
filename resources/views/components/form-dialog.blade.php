{{--
    Add/edit dialog for list pages, opened by [data-form-dialog="id"] buttons (public/js/app.js).
    It submits in the background (data-ajax): errors show under the fields, success reloads the
    list. Without JavaScript it posts normally and comes back open with the old values and errors.
    <x-form-dialog id="customerDialog" title="Add customer" :action="route('customers.store')" :submit="__('Save customer')"> fields </x-form-dialog>
--}}
@props(['id', 'title', 'action', 'submit' => null, 'success' => null, 'size' => '', 'files' => false])
@php
    $submit ??= __('Save');
    $success ??= __('Saved.');
    $reopen = $errors->any() && old('_dialog') === $id;
    $formAction = $reopen ? old('_action', $action) : $action;
    $formTitle = $reopen ? old('_title', $title) : $title;
@endphp
<dialog id="{{ $id }}" class="dialog {{ $size }}" aria-labelledby="{{ $id }}-title" data-reopen="{{ $reopen ? 1 : 0 }}">
    <form method="POST" action="{{ $formAction }}" data-ajax data-success="{{ $success }}" @if($files) enctype="multipart/form-data" @endif>
        @csrf
        <input type="hidden" name="_method" value="{{ $reopen ? old('_method', 'POST') : 'POST' }}">
        <input type="hidden" name="_dialog" value="{{ $id }}">
        <input type="hidden" name="_action" value="{{ $formAction }}">
        <input type="hidden" name="_title" value="{{ $formTitle }}">
        <div class="dialog-header">
            <h2 class="dialog-title" id="{{ $id }}-title">{{ $formTitle }}</h2>
            <button type="button" class="icon-btn" data-dialog-close aria-label="{{ __('Close') }}"><i class="fas fa-xmark" aria-hidden="true"></i></button>
        </div>
        <div class="dialog-body">
            {{ $slot }}
        </div>
        <div class="dialog-footer">
            <button type="button" class="btn btn-secondary" data-dialog-close>{{ __('Cancel') }}</button>
            <button type="submit" class="btn btn-primary">{{ $submit }}</button>
        </div>
    </form>
</dialog>
