{{-- This language's strings for t() in the page scripts (English needs none: its strings are the keys) --}}
@if(app()->getLocale() !== 'en')
@php($fallback = app('system.settings')->fallback_locale)
<script src="{{ route('language.script', ['locale' => app()->getLocale(), 'fallback' => $fallback, 'v' => @filemtime(lang_path(app()->getLocale() . '.json')) . '-' . @filemtime(lang_path($fallback . '.json'))]) }}"></script>
@endif
