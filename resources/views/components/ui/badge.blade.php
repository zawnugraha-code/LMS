@props([
    'variant' => 'neutral', // 'success', 'danger', 'warning', 'neutral'
    'pill' => false,
    'class' => '',
])

@php
    $variantClasses = [
        'success' => 'bg-success-soft text-success border-success/20',
        'danger' => 'bg-danger-soft text-danger border-danger/20',
        'warning' => 'bg-warning-soft text-warning border-warning/20',
        'neutral' => 'bg-neutral-soft text-text-muted border-line',
    ][$variant] ?? 'bg-neutral-soft text-text-muted border-line';

    $radiusClass = $pill ? 'rounded-full px-2.5 py-0.5' : 'rounded-[4px] px-2 py-0.5';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 text-[11px] font-medium border $radiusClass $variantClasses $class"]) }}>
    {{ $slot }}
</span>
