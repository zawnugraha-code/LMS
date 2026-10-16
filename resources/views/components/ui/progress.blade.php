@props([
    'value' => 0,          // percentage 0 to 100
    'height' => 'h-1.5',   // 'h-1.5' (6px) or 'h-2' (8px)
    'color' => 'bg-accent',
    'class' => '',
])

@php
    $clampedValue = max(0, min(100, (float) $value));
@endphp

<div {{ $attributes->merge(['class' => "w-full bg-line rounded-full overflow-hidden $height $class"]) }}>
    <div
        class="{{ $color }} {{ $height }} rounded-full transition-all duration-300"
        style="width: {{ $clampedValue }}%;"
        role="progressbar"
        aria-valuenow="{{ $clampedValue }}"
        aria-valuemin="0"
        aria-valuemax="100"
    ></div>
</div>
