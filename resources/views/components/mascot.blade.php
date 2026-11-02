{{--
    Soap Opera's bubble mascot: a glossy soap-bubble body with line-drawn arms,
    legs and face. Usage: <x-mascot mood="wave" class="hero-art" />
    mood: happy (default) | wave | proud | curious
    Limbs use currentColor (white by default); add class "mascot-ink" on light backgrounds.
--}}
@props(['mood' => 'happy'])
@php
    $mood = in_array($mood, ['happy', 'wave', 'proud', 'curious'], true) ? $mood : 'happy';
    $id = 'mascot-' . \Illuminate\Support\Str::random(6);
    $face = '#1d4fc4';
@endphp
<svg {{ $attributes->merge(['class' => "mascot mascot-$mood"]) }} viewBox="0 0 320 330" aria-hidden="true" focusable="false">
    <defs>
        <radialGradient id="{{ $id }}-body" cx="0.42" cy="0.36" r="0.7">
            <stop offset="0" stop-color="#ffffff"/>
            <stop offset="0.55" stop-color="#eef7ff"/>
            <stop offset="1" stop-color="#b9defc"/>
        </radialGradient>
        <clipPath id="{{ $id }}-clip"><circle cx="160" cy="140" r="88"/></clipPath>
        <filter id="{{ $id }}-soft" x="-30%" y="-30%" width="160%" height="160%"><feGaussianBlur stdDeviation="13"/></filter>
        <filter id="{{ $id }}-shadow" x="-50%" y="-50%" width="200%" height="200%"><feGaussianBlur stdDeviation="5"/></filter>
        {{-- outlined mitten hand with a thumb, drawn around (0,0) --}}
        <path id="{{ $id }}-hand" d="M-8 -3 C-8 -12 7 -13 9 -4 C11 5 6 11 -1 11 C-7 11 -8 4 -8 -3 Z M-8 1 L-15 -5"/>
        <radialGradient id="{{ $id }}-film" cx="0.5" cy="0.5" r="0.5">
            <stop offset="0" stop-color="#fff" stop-opacity="0.05"/>
            <stop offset="0.75" stop-color="#e6f4ff" stop-opacity="0.18"/>
            <stop offset="1" stop-color="#fff" stop-opacity="0.9"/>
        </radialGradient>
    </defs>

    {{-- small floating bubbles --}}
    <g class="mascot-bubbles">
        <circle cx="54" cy="44" r="16" fill="url(#{{ $id }}-film)" stroke="#fff" stroke-opacity="0.8" stroke-width="2"/>
        <ellipse cx="48" cy="38" rx="5" ry="3" fill="#fff" opacity="0.85" transform="rotate(-38 48 38)"/>
        <circle cx="22" cy="112" r="9" fill="url(#{{ $id }}-film)" stroke="#fff" stroke-opacity="0.8" stroke-width="2"/>
        <circle cx="300" cy="196" r="10" fill="url(#{{ $id }}-film)" stroke="#fff" stroke-opacity="0.8" stroke-width="2"/>
        <ellipse cx="297" cy="192" rx="3" ry="2" fill="#fff" opacity="0.85" transform="rotate(-38 297 192)"/>
    </g>

    {{-- ground shadow --}}
    <ellipse cx="160" cy="316" rx="78" ry="8" fill="#0a3c96" opacity="0.18" filter="url(#{{ $id }}-shadow)"/>

    {{-- legs and shoes --}}
    <g class="mascot-line" fill="none" stroke="currentColor" stroke-width="7" stroke-linecap="round" stroke-linejoin="round">
        <path d="M140 224 C138 248 136 266 132 284"/>
        <path d="M180 224 C182 248 186 266 190 284"/>
        <path d="M102 304 C102 291 112 284 126 284 C138 284 143 292 143 304 Z"/>
        <path d="M177 304 C177 292 182 284 194 284 C208 284 218 291 218 304 Z"/>
    </g>

    {{-- arms behind the body --}}
    <g class="mascot-line" fill="none" stroke="currentColor" stroke-width="7" stroke-linecap="round" stroke-linejoin="round">
        @if ($mood === 'proud')
            <path d="M76 150 C42 146 34 190 64 206"/>
            <use href="#{{ $id }}-hand" transform="translate(72 210) rotate(-60)"/>
            <path d="M244 150 C278 146 286 190 256 206"/>
            <use href="#{{ $id }}-hand" transform="translate(248 210) rotate(60) scale(-1 1)"/>
        @elseif ($mood === 'curious')
            <path d="M76 150 C50 168 46 200 60 222"/>
            <use href="#{{ $id }}-hand" transform="translate(62 233) rotate(-10)"/>
        @else
            <path d="M76 150 C46 156 38 194 62 212"/>
            <use href="#{{ $id }}-hand" transform="translate(70 218) rotate(-50)"/>
            @if ($mood === 'happy')
                <path d="M244 152 C270 170 274 200 262 222"/>
                <use href="#{{ $id }}-hand" transform="translate(259 233) rotate(15) scale(-1 1)"/>
            @endif
        @endif
    </g>

    {{-- glossy bubble body (the 3D part) --}}
    <circle cx="160" cy="140" r="88" fill="url(#{{ $id }}-body)"/>
    <g clip-path="url(#{{ $id }}-clip)" filter="url(#{{ $id }}-soft)" opacity="0.85">
        <circle cx="121" cy="111" r="38" fill="#ffc8ea"/>
        <circle cx="191" cy="101" r="33" fill="#fff0b3"/>
        <circle cx="180" cy="181" r="42" fill="#a3f2e2"/>
        <circle cx="121" cy="182" r="36" fill="#9fd6ff"/>
    </g>
    <circle cx="160" cy="140" r="87" fill="none" stroke="#fff" stroke-width="3" opacity="0.85"/>
    <ellipse cx="121" cy="84" rx="27" ry="13" fill="#fff" opacity="0.9" transform="rotate(-34 121 84)"/>
    <circle cx="152" cy="66" r="5" fill="#fff" opacity="0.9"/>

    {{-- face --}}
    <g fill="none" stroke="{{ $face }}" stroke-width="6" stroke-linecap="round" stroke-linejoin="round">
        @switch($mood)
            @case('wave')
                <g class="mascot-eyes" fill="{{ $face }}" stroke="none">
                    <rect x="131" y="116" width="10" height="21" rx="5"/>
                    <rect x="179" y="116" width="10" height="21" rx="5"/>
                </g>
                <path d="M138 154 Q160 180 182 154 Z" fill="{{ $face }}"/>
                <path d="M150 166 Q160 174 170 166" stroke="#ff8fc6" stroke-width="5"/>
                @break
            @case('proud')
                <path d="M128 128 Q136 120 144 128"/>
                <g class="mascot-eyes" fill="{{ $face }}" stroke="none"><rect x="179" y="116" width="10" height="21" rx="5"/></g>
                <path d="M138 156 Q160 172 184 150"/>
                @break
            @case('curious')
                <path d="M126 106 Q136 100 146 104"/>
                <g class="mascot-eyes" fill="{{ $face }}" stroke="none">
                    <circle cx="140" cy="122" r="6"/>
                    <circle cx="186" cy="122" r="6"/>
                </g>
                <path d="M146 160 Q153 155 160 160 Q167 165 174 160"/>
                @break
            @default
                <path d="M126 128 Q136 116 146 128"/>
                <path d="M174 128 Q184 116 194 128"/>
                <path d="M136 152 Q160 176 184 152"/>
        @endswitch
    </g>
    <ellipse cx="117" cy="150" rx="10" ry="6" fill="#ff9fd0" opacity="0.5"/>
    <ellipse cx="203" cy="150" rx="10" ry="6" fill="#ff9fd0" opacity="0.5"/>

    {{-- arms in front of the body --}}
    <g class="mascot-line" fill="none" stroke="currentColor" stroke-width="7" stroke-linecap="round" stroke-linejoin="round">
        @if ($mood === 'wave')
            <g class="mascot-wave">
                <path d="M244 148 C266 132 276 108 272 82"/>
                <path d="M272 82 L262 64 M272 82 L272 60 M272 82 L283 66 M272 82 L290 76"/>
            </g>
        @elseif ($mood === 'curious')
            <path d="M244 156 C262 188 240 206 206 184"/>
            <path d="M206 184 L196 176 M206 184 L198 190"/>
        @endif
    </g>
</svg>
