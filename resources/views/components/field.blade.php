{{--
    A labelled form control with its help text and server-side error.
    <x-field name="price" label="Price" type="number" :prefix="currency_symbol()" required :step="money_step()" min="0" />
    type: any input type, "textarea", or pass :options="[value => label]" for a select.
    Extra attributes (min, step, inputmode, pattern, data-default…) go on the control.
--}}
@props(['name', 'label', 'type' => 'text', 'required' => false, 'help' => null, 'value' => null, 'options' => null, 'prefix' => null, 'placeholder' => null])
@php
    $id = 'field-' . preg_replace('/\W/', '', $name);
    $error = $errors->first($name);
    $current = old($name, $value);
    $describedBy = trim(($help ? "$id-help " : '') . "$id-error");
@endphp
<div class="field">
    @if($type === 'segmented')
    {{-- A few big choices (e.g. Active / Inactive) as radio buttons: one tap, no dropdown --}}
    <span class="label" id="{{ $id }}-label">{{ $label }}</span>
    <div class="segmented block" role="radiogroup" aria-labelledby="{{ $id }}-label" aria-describedby="{{ $describedBy }}">
        @foreach($options as $optionValue => $optionLabel)
            <label><input type="radio" name="{{ $name }}" value="{{ $optionValue }}" @checked((string) ($current ?? $attributes->get('data-default')) === (string) $optionValue) {{ $attributes }}><span>{{ $optionLabel }}</span></label>
        @endforeach
    </div>
    @else
    <label class="label" for="{{ $id }}">{{ $label }}@if($required) <span class="req" aria-hidden="true">*</span>@endif</label>
    @endif
    @if($type === 'segmented')
    @elseif($options !== null)
        <select class="select" id="{{ $id }}" name="{{ $name }}" @required($required) @if($error) aria-invalid="true" @endif aria-describedby="{{ $describedBy }}" {{ $attributes }}>
            @foreach($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected((string) $current === (string) $optionValue)>{{ $optionLabel }}</option>
            @endforeach
        </select>
    @elseif($type === 'textarea')
        <textarea class="textarea" id="{{ $id }}" name="{{ $name }}" placeholder="{{ $placeholder }}" @required($required) @if($error) aria-invalid="true" @endif aria-describedby="{{ $describedBy }}" {{ $attributes }}>{{ $current }}</textarea>
    @else
        @if($prefix)<div class="input-affix"><span class="affix" aria-hidden="true">{{ $prefix }}</span>@endif
        <input class="input" type="{{ $type }}" id="{{ $id }}" name="{{ $name }}" value="{{ $type === 'password' ? '' : $current }}" placeholder="{{ $placeholder }}" @required($required) @if($error) aria-invalid="true" @endif aria-describedby="{{ $describedBy }}" {{ $attributes }}>
        @if($prefix)</div>@endif
    @endif
    @if($help)<p class="help" id="{{ $id }}-help">{{ $help }}</p>@endif
    <p class="field-error" id="{{ $id }}-error">{{ $error }}</p>
</div>
