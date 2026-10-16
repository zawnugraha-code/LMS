@props([
    'variant' => 'full',   // 'full' (icon+text), 'mark' (icon only), 'inverse' (white on navy)
    'class' => '',
])

@php
    $isInverse = $variant === 'inverse';
    $textColor = $isInverse ? 'text-white' : 'text-text';
    $subtitleColor = $isInverse ? 'text-white/70' : 'text-text-muted';
    $iconColor = $isInverse ? 'text-white' : 'text-primary';
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5 ' . $class]) }}>
    {{-- Landmark icon (pillared building) --}}
    <x-icon name="landmark" :size="32" class="{{ $iconColor }} shrink-0" />

    @if($variant !== 'mark')
        <div class="flex flex-col leading-none">
            <span class="font-semibold text-lg tracking-tight {{ $textColor }}">Arunika</span>
            <span class="text-[10px] uppercase tracking-[0.15em] {{ $subtitleColor }} mt-0.5 font-medium">Learning System</span>
        </div>
    @endif
</div>
