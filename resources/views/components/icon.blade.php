@props([
    'name',
    'class' => 'w-4 h-4',
    'size' => 16,
])

@php
    $classNames = $class;
@endphp

<svg 
    xmlns="http://www.w3.org/2000/svg" 
    viewBox="0 0 256 256" 
    fill="none" 
    stroke="currentColor" 
    stroke-width="16" 
    stroke-linecap="round" 
    stroke-linejoin="round" 
    {{ $attributes->merge(['class' => $classNames]) }}
    aria-hidden="true"
>
    @switch($name)
        @case('arrow-right')
            <line x1="40" y1="128" x2="216" y2="128" />
            <polyline points="144 56 216 128 144 200" />
            @break

        @case('arrow-up-right')
            <line x1="64" y1="192" x2="192" y2="64" />
            <polyline points="88 64 192 64 192 168" />
            @break

        @case('arrow-left')
            <line x1="216" y1="128" x2="40" y2="128" />
            <polyline points="112 56 40 128 112 200" />
            @break

        @case('check')
            <polyline points="40 144 96 200 216 80" />
            @break

        @case('x')
            <line x1="200" y1="56" x2="56" y2="200" />
            <line x1="200" y1="200" x2="56" y2="56" />
            @break

        @case('search')
        @case('magnifying-glass')
            <circle cx="112" cy="112" r="72" />
            <line x1="162.9" y1="162.9" x2="216" y2="216" />
            @break

        @case('calendar')
            <rect x="40" y="40" width="176" height="176" rx="6" />
            <line x1="176" y1="24" x2="176" y2="56" />
            <line x1="80" y1="24" x2="80" y2="56" />
            <line x1="40" y1="88" x2="216" y2="88" />
            @break

        @case('clock')
            <circle cx="128" cy="128" r="96" />
            <polyline points="128 72 128 128 168 152" />
            @break

        @case('book-open')
            <path d="M128,96 C144,48 200,48 216,56 L216,192 C200,184 144,184 128,216 C112,184 56,184 40,192 L40,56 C56,48 112,48 128,96 Z" />
            <line x1="128" y1="96" x2="128" y2="216" />
            @break

        @case('file-text')
            <path d="M200,224H56a8,8,0,0,1-8-8V40a8,8,0,0,1,8-8h96l56,56V216A8,8,0,0,1,200,224Z" />
            <polyline points="152 32 152 88 208 88" />
            <line x1="96" y1="136" x2="160" y2="136" />
            <line x1="96" y1="168" x2="160" y2="168" />
            @break

        @case('chart-bar')
            <line x1="224" y1="208" x2="32" y2="208" />
            <rect x="56" y="112" width="32" height="96" rx="2" />
            <rect x="112" y="64" width="32" height="144" rx="2" />
            <rect x="168" y="136" width="32" height="72" rx="2" />
            @break

        @case('qr-code')
            <rect x="48" y="48" width="64" height="64" rx="4" />
            <rect x="144" y="48" width="64" height="64" rx="4" />
            <rect x="48" y="144" width="64" height="64" rx="4" />
            <rect x="144" y="144" width="24" height="24" />
            <line x1="192" y1="144" x2="208" y2="144" />
            <line x1="144" y1="200" x2="144" y2="208" />
            <line x1="176" y1="192" x2="208" y2="192" />
            @break

        @case('chat-text')
        @case('note')
            <path d="M216,56H40a8,8,0,0,0-8,8V184a8,8,0,0,0,8,8H76v32a8,8,0,0,0,13.6,5.7L128,192h88a8,8,0,0,0,8-8V64A8,8,0,0,0,216,56Z" />
            <line x1="80" y1="112" x2="176" y2="112" />
            <line x1="80" y1="144" x2="144" y2="144" />
            @break

        @case('caret-down')
            <polyline points="208 96 128 176 48 96" />
            @break

        @case('caret-right')
            <polyline points="96 48 176 128 96 208" />
            @break

        @case('check-circle')
            <circle cx="128" cy="128" r="96" />
            <polyline points="88 128 116 156 168 104" />
            @break

        @case('warning-circle')
            <circle cx="128" cy="128" r="96" />
            <line x1="128" y1="80" x2="128" y2="136" />
            <circle cx="128" cy="168" r="6" fill="currentColor" stroke="none" />
            @break

        @case('info')
            <circle cx="128" cy="128" r="96" />
            <line x1="128" y1="120" x2="128" y2="168" />
            <circle cx="128" cy="88" r="6" fill="currentColor" stroke="none" />
            @break

        @case('user')
            <circle cx="128" cy="96" r="48" />
            <path d="M32,216 C48,168 88,152 128,152 C168,152 208,168 224,216" />
            @break

        @case('building')
            <path d="M40,216V40a8,8,0,0,1,8-8H160a8,8,0,0,1,8,8V216" />
            <path d="M168,88h40a8,8,0,0,1,8,8V216" />
            <line x1="24" y1="216" x2="232" y2="216" />
            @break

        @case('shield-check')
            <path d="M208,48a8,8,0,0,0-8-8H56a8,8,0,0,0-8,8v64c0,64,80,104,80,104s80-40,80-104Z" />
            <polyline points="96 112 120 136 160 96" />
            @break

        @case('target')
            <circle cx="128" cy="128" r="96" />
            <circle cx="128" cy="128" r="56" />
            <circle cx="128" cy="128" r="16" fill="currentColor" stroke="none" />
            @break

        @case('download')
            <path d="M216,144v64a8,8,0,0,1-8,8H48a8,8,0,0,1-8-8V144" />
            <polyline points="72 104 128 160 184 104" />
            <line x1="128" y1="32" x2="128" y2="160" />
            @break

        @case('sliders')
            <line x1="40" y1="88" x2="144" y2="88" />
            <line x1="184" y1="88" x2="216" y2="88" />
            <circle cx="164" cy="88" r="20" />
            <line x1="40" y1="168" x2="88" y2="168" />
            <line x1="128" y1="168" x2="216" y2="168" />
            <circle cx="108" cy="168" r="20" />
            @break

        @default
            <circle cx="128" cy="128" r="96" />
    @endswitch
</svg>
