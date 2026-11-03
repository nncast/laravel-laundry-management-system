{{-- Language switcher: saved on the staff member's account (or this browser before signing in) --}}
@php($pickable = app('system.settings')->enabledLocales())
@if(count($pickable) > 1)
<form class="language-picker" action="{{ route('language.update') }}" method="POST">
    @csrf
    <i class="fas fa-language" aria-hidden="true"></i>
    <label class="sr-only" for="languagePicker">{{ __('Language') }}</label>
    <select class="select" id="languagePicker" name="locale" onchange="this.form.requestSubmit()">
        @foreach($pickable as $code)
            <option value="{{ $code }}" lang="{{ config("locales.$code.html") }}" @selected($code === app()->getLocale())>{{ config("locales.$code.native") }}</option>
        @endforeach
    </select>
    <noscript><button type="submit" class="btn btn-sm btn-secondary">{{ __('Change') }}</button></noscript>
</form>
@endif
