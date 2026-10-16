@props([
    'variant' => 'primary', // 'primary', 'secondary', 'ghost', 'danger'
    'size' => 'md',        // 'sm', 'md', 'lg'
    'type' => 'button',
    'class' => '',
])

@php
    $baseClass = 'inline-flex items-center justify-center font-medium transition-colors cursor-pointer select-none disabled:opacity-50 disabled:cursor-not-allowed';

    $sizeClasses = [
        'sm' => 'h-7 px-2.5 text-[11px] gap-1.5 rounded-[6px]',
        'md' => 'h-8 px-3.5 text-xs gap-2 rounded-[6px]',
        'lg' => 'h-10 px-5 text-sm gap-2.5 rounded-[6px]',
    ][$size] ?? 'h-8 px-3.5 text-xs gap-2 rounded-[6px]';

    $variantClasses = [
        'primary' => 'bg-primary hover:bg-primary-hover active:bg-primary text-white shadow-xs',
        'secondary' => 'bg-surface hover:bg-canvas active:bg-tile text-text border border-line-strong',
        'ghost' => 'text-text-muted hover:text-text hover:bg-tile active:bg-line',
        'danger' => 'bg-danger hover:bg-danger/90 active:bg-danger text-white',
    ][$variant] ?? 'bg-primary hover:bg-primary-hover text-white';
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => "$baseClass $sizeClasses $variantClasses $class"]) }}
>
    {{ $slot }}
</button>
